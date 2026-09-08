<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\FraganciaTamano;
use App\Models\User;
use App\Notifications\ImportacionFraganciasCompletada;
use App\Services\NotificacionSegura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FraganciaController extends Controller
{
    private const SLUGS_IMAGEN_GENERICA = ['floral','maderado','oriental','fresco','acuatico','oud','gourmand','nicho'];

    public function index(Request $request)
    {
        $query = Fragancia::with(['familia', 'tamanos']);

        $umbral = (int) \App\Models\Configuracion::obtener('stock_bajo_umbral', config('comercial.stock_bajo_umbral', 5));

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                  ->orWhere('casa_perfumista', 'like', '%' . $termino . '%');
            });
        }
        if ($request->filled('familia_id')) {
            $query->where('familia_id', $request->familia_id);
        }
        if ($request->filled('genero')) {
            $query->where('genero', $request->genero);
        }
        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        if ($request->filled('urgente')) {
            $query->whereHas('tamanos', function ($q) use ($umbral) {
                $q->where('stock', '<=', $umbral);
            });
        }

        $fragancias = $query->latest()->paginate(20)->withQueryString();
        $familias   = Familia::orderBy('nombre')->get();

        return view('app.back.fragancias.index', compact('fragancias', 'familias', 'umbral'));
    }

    /**
     * Listado de fragancias activas sin costo_por_ml configurado. Muestra,
     * para cada una, el costo ESTIMADO que resultaría de aplicar el margen
     * objetivo vigente (Admin > Configuración), y permite:
     *  - aplicar ese estimado a todas de un solo clic, o
     *  - editar manualmente el costo de una fragancia puntual.
     */
    public function sinCosto()
    {
        $fragancias = Fragancia::whereNull('costo_por_ml')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'casa_perfumista', 'genero', 'precio_por_ml']);

        $margenObjetivo = \App\Models\Configuracion::margenObjetivo();

        $fragancias->each(function ($f) {
            $f->costo_estimado = \App\Models\Configuracion::costoEstimadoPorMl((float) $f->precio_por_ml, $f->genero);
        });

        return view('app.back.fragancias.sin-costo', compact('fragancias', 'margenObjetivo'));
    }

    /**
     * Aplica el costo estimado (calculado a partir del margen objetivo
     * configurado en Admin > Configuración) a TODAS las fragancias que
     * todavía no tienen costo_por_ml. Es un valor ficticio de arranque,
     * pensado para no dejar el reporte de ganancias en $0 mientras el admin
     * carga los costos reales uno a uno.
     */
    public function aplicarCostoEstimado(Request $request)
    {
        $fragancias = Fragancia::whereNull('costo_por_ml')->get(['id', 'genero', 'precio_por_ml']);

        $actualizadas = 0;
        foreach ($fragancias as $f) {
            $estimado = \App\Models\Configuracion::costoEstimadoPorMl((float) $f->precio_por_ml, $f->genero);
            if ($estimado > 0) {
                $f->forceFill(['costo_por_ml' => $estimado])->save();
                $actualizadas++;
            }
        }

        if ($actualizadas === 0) {
            return redirect()->route('admin.fragancias.sin-costo')
                ->with('warning', 'No se pudo estimar costo para ninguna fragancia (revisa que tengan precio_por_ml configurado).');
        }

        return redirect()->route('admin.fragancias.sin-costo')->with(
            'success',
            "Se asignó un costo ESTIMADO (ficticio, calculado con el margen configurado) a {$actualizadas} fragancia(s). "
            . 'Recuerda reemplazarlo por el costo real cuando lo tengas: mientras tanto los reportes lo marcarán como estimado.'
        );
    }

    /**
     * Guarda un costo_por_ml puntual, escrito a mano por el admin, para una
     * sola fragancia (atajo desde el listado de "sin costo" cuando el admin
     * ya conoce el valor real y no quiere pasar por el formulario completo).
     */
    public function guardarCostoManual(Request $request, Fragancia $fragancia)
    {
        $data = $request->validate([
            'costo_por_ml' => 'required|numeric|min:0|max:20',
        ]);

        $fragancia->update(['costo_por_ml' => $data['costo_por_ml']]);

        return redirect()->route('admin.fragancias.sin-costo')
            ->with('success', "Costo de «{$fragancia->nombre}» guardado: \${$data['costo_por_ml']}/ml.");
    }

    public function create()
    {
        $familias = Familia::where('activo', true)->get();
        $casasExistentes = Fragancia::select('casa_perfumista')->distinct()->orderBy('casa_perfumista')->pluck('casa_perfumista');
        $recargos = \App\Models\Configuracion::recargosTamanos();
        $costosEnvase = \App\Models\PresentacionEnvase::costosIndexados();
        $margenObjetivo = \App\Models\Configuracion::margenObjetivo();
        $ivaPorcentaje = (float) \App\Models\Configuracion::obtener('iva_porcentaje', config('comercial.iva.porcentaje', 15));
        $ivaIncluido = \App\Models\Configuracion::booleano(\App\Models\Configuracion::obtener('iva_incluido', config('comercial.iva.incluido_en_precio', false)));
        return view('app.back.fragancias.create', compact('familias', 'casasExistentes', 'recargos', 'costosEnvase', 'margenObjetivo', 'ivaPorcentaje', 'ivaIncluido'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'precio_por_ml' => $this->normalizarPrecioPorMl($request->input('precio_por_ml')),
            'precio_especial_por_ml' => $this->normalizarPrecioPorMl($request->input('precio_especial_por_ml')),
            'costo_por_ml' => $this->normalizarPrecioPorMl($request->input('costo_por_ml')),
        ]);

        $data = $request->validate([
            'nombre'          => 'required|string|max:150',
            'descripcion'     => 'required|string',
            'casa_perfumista' => 'required|string|max:100',
            'genero'          => 'required|in:mujer,hombre,unisex',
            'notas_salida'    => 'nullable|string',
            'notas_corazon'   => 'nullable|string',
            'notas_fondo'     => 'nullable|string',
            'activo'          => 'boolean',
            'familia_id'      => 'required|exists:familias,id',
            'imagen_principal' => 'nullable|image|max:2048',
            'imagen_generica'  => 'nullable|string|in:' . implode(',', self::SLUGS_IMAGEN_GENERICA),
            'precio_por_ml'          => 'required|numeric|min:0|max:20',
            'precio_especial_por_ml' => 'nullable|numeric|min:0|max:20',
            'costo_por_ml'           => 'nullable|numeric|min:0|max:20',
            'stock_100' => 'required|integer|min:0',
            'stock_50'  => 'required|integer|min:0',
            'stock_30'  => 'required|integer|min:0',
        ]);

        $stocks = [100 => $data['stock_100'], 50 => $data['stock_50'], 30 => $data['stock_30']];
        unset($data['stock_100'], $data['stock_50'], $data['stock_30']);

        if ($request->hasFile('imagen_principal')) {
            $data['imagen_principal'] = $request->file('imagen_principal')
                ->store('fragancias', 'public');
        } elseif ($request->filled('imagen_generica')) {

            $data['imagen_principal'] = null;
        }
        unset($data['imagen_generica']);

        $data['activo'] = $request->boolean('activo', true);

        $fragancia = Fragancia::create($data);

        $this->sincronizarTamanos($fragancia, $stocks);

        return redirect()->route('admin.fragancias.index')
                         ->with('success', 'Fragancia creada correctamente.');
    }

    public function edit(string $id)
    {
        $fragancia = Fragancia::with('tamanos')->findOrFail($id);
        $familias  = Familia::where('activo', true)->get();
        $casasExistentes = Fragancia::select('casa_perfumista')->distinct()->orderBy('casa_perfumista')->pluck('casa_perfumista');
        $recargos = \App\Models\Configuracion::recargosTamanos();
        $costosEnvase = \App\Models\PresentacionEnvase::costosIndexados();
        $margenObjetivo = \App\Models\Configuracion::margenObjetivo();
        $ivaPorcentaje = (float) \App\Models\Configuracion::obtener('iva_porcentaje', config('comercial.iva.porcentaje', 15));
        $ivaIncluido = \App\Models\Configuracion::booleano(\App\Models\Configuracion::obtener('iva_incluido', config('comercial.iva.incluido_en_precio', false)));
        return view('app.back.fragancias.edit', compact('fragancia', 'familias', 'casasExistentes', 'recargos', 'costosEnvase', 'margenObjetivo', 'ivaPorcentaje', 'ivaIncluido'));
    }

    public function update(Request $request, string $id)
    {
        $fragancia = Fragancia::findOrFail($id);
        $request->merge([
            'precio_por_ml' => $this->normalizarPrecioPorMl($request->input('precio_por_ml')),
            'precio_especial_por_ml' => $this->normalizarPrecioPorMl($request->input('precio_especial_por_ml')),
            'costo_por_ml' => $this->normalizarPrecioPorMl($request->input('costo_por_ml')),
        ]);

        $data = $request->validate([
            'nombre'          => 'required|string|max:150',
            'descripcion'     => 'required|string',
            'casa_perfumista' => 'required|string|max:100',
            'genero'          => 'required|in:mujer,hombre,unisex',
            'notas_salida'    => 'nullable|string',
            'notas_corazon'   => 'nullable|string',
            'notas_fondo'     => 'nullable|string',
            'familia_id'      => 'required|exists:familias,id',
            'imagen_principal' => 'nullable|image|max:2048',
            'imagen_generica'  => 'nullable|string|in:' . implode(',', self::SLUGS_IMAGEN_GENERICA),
            'precio_por_ml'          => 'required|numeric|min:0|max:20',
            'precio_especial_por_ml' => 'nullable|numeric|min:0|max:20',
            'costo_por_ml'           => 'nullable|numeric|min:0|max:20',
            'stock_100' => 'required|integer|min:0',
            'stock_50'  => 'required|integer|min:0',
            'stock_30'  => 'required|integer|min:0',
        ]);

        $stocks = [100 => $data['stock_100'], 50 => $data['stock_50'], 30 => $data['stock_30']];
        unset($data['stock_100'], $data['stock_50'], $data['stock_30']);

        if ($request->hasFile('imagen_principal')) {
            if ($fragancia->imagen_principal) Storage::disk('public')->delete($fragancia->imagen_principal);
            $data['imagen_principal'] = $request->file('imagen_principal')->store('fragancias', 'public');
        } elseif ($request->filled('imagen_generica')) {
            if ($fragancia->imagen_principal && !str_starts_with($fragancia->imagen_principal, 'fragancias/generic/')) {
                Storage::disk('public')->delete($fragancia->imagen_principal);
            }

            $data['imagen_principal'] = null;
        }
        unset($data['imagen_generica']);

        $data['activo'] = $request->boolean('activo');

        $fragancia->update($data);

        $this->sincronizarTamanos($fragancia, $stocks);

        return redirect()->route('admin.fragancias.index')
                         ->with('success', 'Fragancia actualizada correctamente.');
    }

    private function normalizarPrecioPorMl(?string $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return round((float) $valor, 4);
    }

    private function sincronizarTamanos(Fragancia $fragancia, array $stocksPorMl): void
    {
        $recargos = \App\Models\Configuracion::recargosTamanos();

        foreach (config('comercial.tamanos') as $ml => $cfg) {
            $factor = 1 + ($recargos[$ml] ?? $cfg['recargo']);

            // Redondeo comercial: precio final en entero completo (ej.: $36.23 -> $36),
            // en vez de dejar el decimal exacto que sale de la fórmula.
            $precio = round($fragancia->precio_por_ml * $ml * $factor);
            $precioEspecial = $fragancia->precio_especial_por_ml
                ? round($fragancia->precio_especial_por_ml * $ml * $factor)
                : null;

            $fragancia->tamanos()->updateOrCreate(
                ['tamano' => $ml . ' ml'],
                [
                    'precio'          => $precio,
                    'precio_especial' => $precioEspecial,
                    'stock'           => $stocksPorMl[$ml] ?? 0,
                ]
            );
        }
    }

    public function importarForm()
    {
        return view('app.back.fragancias.importar');
    }

    public function importarPlantilla()
    {
        $columnas = [
            'nombre', 'descripcion', 'casa_perfumista', 'genero', 'familia',
            'notas_salida', 'notas_corazon', 'notas_fondo',
            'precio_por_ml', 'precio_especial_por_ml', 'costo_por_ml',
            'stock_100', 'stock_50', 'stock_30', 'activo',
        ];

        $ejemplo = [
            'Black Orchid',
            'Fragancia intensa y sensual con notas de orquídea negra y trufa.',
            'Tom Ford', 'unisex', 'Oriental / Especiado',
            'Trufa negra, Ylang Ylang, Bergamota',
            'Orquídea negra, Especias, Fruta',
            'Pachulí, Vainilla, Incienso',
            '1.20', '1.00', '0.55',
            '10', '15', '20', 'si',
        ];

        return response()->streamDownload(function () use ($columnas, $ejemplo) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $columnas, ';');
            fputcsv($out, $ejemplo, ';');
            fclose($out);
        }, 'plantilla_fragancias.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private const IMPORT_CAMPOS_REQUERIDOS = ['nombre', 'descripcion', 'casa_perfumista', 'genero', 'familia', 'precio_por_ml', 'stock_100', 'stock_50', 'stock_30'];
    private const IMPORT_CACHE_MINUTOS = 20;

    /**
     * Abre el archivo subido, detecta el delimitador (, o ;), quita el BOM
     * y devuelve encabezados + filas crudas. No valida contenido todavía.
     */
    private function leerFilasCsv(Request $request): array
    {
        $handle = fopen($request->file('archivo')->getRealPath(), 'r');
        if (!$handle) {
            return ['ok' => false, 'mensaje' => 'No se pudo leer el archivo. Intenta nuevamente.'];
        }

        $primeraLinea = fgets($handle);
        rewind($handle);
        if ($primeraLinea === false) {
            fclose($handle);
            return ['ok' => false, 'mensaje' => 'El archivo está vacío.'];
        }
        if (substr($primeraLinea, 0, 3) === "\xEF\xBB\xBF") {
            $primeraLinea = substr($primeraLinea, 3);
        }
        $delimitador = substr_count($primeraLinea, ';') >= substr_count($primeraLinea, ',') ? ';' : ',';

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $encabezados = fgetcsv($handle, 0, $delimitador);
        if (!$encabezados) {
            fclose($handle);
            return ['ok' => false, 'mensaje' => 'No se pudieron leer los encabezados del archivo.'];
        }
        $encabezados = array_map(fn ($h) => strtolower(trim((string) $h)), $encabezados);

        $faltantes = array_diff(self::IMPORT_CAMPOS_REQUERIDOS, $encabezados);
        if (!empty($faltantes)) {
            fclose($handle);
            return ['ok' => false, 'mensaje' => 'Faltan columnas obligatorias en el archivo: ' . implode(', ', $faltantes) . '. Descarga la plantilla e inténtalo de nuevo.'];
        }

        $filas = [];
        $numeroFila = 1;
        while (($datos = fgetcsv($handle, 0, $delimitador)) !== false) {
            $numeroFila++;
            if (count(array_filter($datos, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $registro = [];
            foreach ($encabezados as $i => $col) {
                $registro[$col] = trim((string) ($datos[$i] ?? ''));
            }
            $filas[] = ['fila' => $numeroFila, 'datos' => $registro];
        }
        fclose($handle);

        if (empty($filas)) {
            return ['ok' => false, 'mensaje' => 'El archivo no tiene filas de datos (solo encabezados).'];
        }

        return ['ok' => true, 'filas' => $filas];
    }

    public function importarPrevisualizar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser un .csv (guárdalo desde Excel como "CSV (delimitado por comas)" o "CSV UTF-8").',
            'archivo.max'      => 'El archivo es demasiado grande (máx. 5 MB).',
        ]);

        $lectura = $this->leerFilasCsv($request);
        if (!$lectura['ok']) {
            return back()->with('error', $lectura['mensaje']);
        }

        $validas = [];
        $invalidas = [];
        $vistosEnArchivo = []; // "nombre|casa" (en minúsculas) => número de fila donde apareció primero

        foreach ($lectura['filas'] as $item) {
            $fila = $item['fila'];
            $registro = $item['datos'];

            // Normalizamos antes de validar: así "Mujer", " unisex " o "HOMBRE"
            // no fallan por formato en vez de por contenido real.
            $registro['genero'] = strtolower(trim($registro['genero'] ?? ''));
            $registro['familia'] = trim($registro['familia'] ?? '');

            $validator = Validator::make($registro, [
                'nombre'                 => 'required|string|max:150',
                'descripcion'            => 'required|string',
                'casa_perfumista'        => 'required|string|max:100',
                'genero'                 => 'required|in:mujer,hombre,unisex',
                'familia'                => 'required|string|max:100',
                'precio_por_ml'          => 'required|numeric|min:0|max:20',
                'precio_especial_por_ml' => 'nullable|numeric|min:0|max:20',
                'costo_por_ml'           => 'nullable|numeric|min:0|max:20',
                'stock_100'              => 'required|integer|min:0',
                'stock_50'               => 'required|integer|min:0',
                'stock_30'               => 'required|integer|min:0',
            ], [
                'precio_por_ml.max' => 'precio_por_ml parece demasiado alto (máx. 20). Recuerda que aquí va el precio real por ml en decimales (ej.: 1.20), no el precio del frasco de 100 ml.',
                'precio_especial_por_ml.max' => 'precio_especial_por_ml parece demasiado alto (máx. 20). Recuerda que aquí va el precio real por ml en decimales.',
                'costo_por_ml.max' => 'costo_por_ml parece demasiado alto (máx. 20). Recuerda que aquí va el costo real por ml en decimales.',
            ]);

            if ($validator->fails()) {
                $invalidas[] = ['fila' => $fila, 'errores' => $validator->errors()->all(), 'datos' => $registro];
                continue;
            }

            // Duplicado dentro del propio archivo: si dos filas del mismo CSV
            // comparten nombre+casa, sólo procesamos la primera para evitar
            // crear dos fragancias distintas en la misma importación.
            $claveDuplicado = mb_strtolower($registro['nombre']) . '|' . mb_strtolower($registro['casa_perfumista']);
            if (isset($vistosEnArchivo[$claveDuplicado])) {
                $filaOriginal = $vistosEnArchivo[$claveDuplicado];
                $invalidas[] = [
                    'fila'     => $fila,
                    'errores'  => ["Fila duplicada dentro del archivo: mismo nombre y casa que la fila {$filaOriginal}. Sólo se procesará la primera aparición."],
                    'datos'    => $registro,
                ];
                continue;
            }
            $vistosEnArchivo[$claveDuplicado] = $fila;

            $existente = Fragancia::with(['familia', 'tamanos'])
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($registro['nombre'])])
                ->whereRaw('LOWER(casa_perfumista) = ?', [mb_strtolower($registro['casa_perfumista'])])
                ->first();

            $familiaNueva = !Familia::whereRaw('LOWER(nombre) = ?', [mb_strtolower($registro['familia'])])->exists();

            $activo = in_array(strtolower($registro['activo'] ?? 'si'), ['si', 'sí', '1', 'true', 'yes'], true);

            $payload = [
                'nombre'                 => $registro['nombre'],
                'descripcion'            => $registro['descripcion'],
                'casa_perfumista'        => $registro['casa_perfumista'],
                'genero'                 => $registro['genero'],
                'notas_salida'           => $registro['notas_salida'] ?? null,
                'notas_corazon'          => $registro['notas_corazon'] ?? null,
                'notas_fondo'            => $registro['notas_fondo'] ?? null,
                'familia'                => $registro['familia'],
                'precio_por_ml'          => $registro['precio_por_ml'],
                'precio_especial_por_ml' => $registro['precio_especial_por_ml'] !== '' ? $registro['precio_especial_por_ml'] : null,
                'costo_por_ml'           => ($registro['costo_por_ml'] ?? '') !== '' ? $registro['costo_por_ml'] : null,
                'activo'                 => $activo,
            ];

            $stocks = [
                100 => (int) $registro['stock_100'],
                50  => (int) $registro['stock_50'],
                30  => (int) $registro['stock_30'],
            ];

            $validas[] = [
                'fila'          => $fila,
                'accion'        => $existente ? 'actualizar' : 'crear',
                'fragancia_id'  => $existente?->id,
                'familia_nueva' => $familiaNueva,
                'payload'       => $payload,
                'stocks'        => $stocks,
                'cambios'       => $existente ? $this->calcularCambios($existente, $payload, $stocks) : [],
            ];
        }

        $token = (string) Str::uuid();
        $expiraEn = now()->addMinutes(self::IMPORT_CACHE_MINUTOS);
        Cache::put("import_fragancias_{$token}", [
            'validas'   => $validas,
            'invalidas' => $invalidas,
            'expira_en' => $expiraEn,
        ], $expiraEn);

        return view('app.back.fragancias.importar-preview', [
            'token'     => $token,
            'validas'   => $validas,
            'invalidas' => $invalidas,
            'expiraEn'  => $expiraEn,
        ]);
    }

    /**
     * Compara los datos nuevos de una fila "actualizar" contra la fragancia
     * existente y devuelve solo los campos que realmente van a cambiar, para
     * mostrarle al admin un diff en la previsualización en vez de un aviso
     * genérico de "se va a actualizar".
     */
    private function calcularCambios(Fragancia $existente, array $payload, array $stocksNuevos): array
    {
        $cambios = [];

        $etiquetas = [
            'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'casa_perfumista' => 'Casa perfumista',
            'genero' => 'Género', 'precio_por_ml' => 'Precio/ml', 'precio_especial_por_ml' => 'Precio especial/ml',
            'costo_por_ml' => 'Costo/ml', 'activo' => 'Activo',
        ];
        foreach ($etiquetas as $campo => $etiqueta) {
            $actual = $existente->{$campo};
            $nuevo = $payload[$campo] ?? null;
            if (is_bool($actual) || is_bool($nuevo)) {
                $iguales = (bool) $actual === (bool) $nuevo;
                $actual = $actual ? 'Sí' : 'No';
                $nuevo = $nuevo ? 'Sí' : 'No';
            } elseif (in_array($campo, ['precio_por_ml', 'precio_especial_por_ml', 'costo_por_ml'], true)) {
                // Comparación numérica: "1" y "1.00" son el mismo precio,
                // no queremos mostrarlo como un cambio real.
                if (is_null($actual) !== is_null($nuevo)) {
                    $iguales = false;
                } else {
                    $iguales = is_null($actual) || (float) $actual === (float) $nuevo;
                }
            } else {
                $iguales = (string) ($actual ?? '') === (string) ($nuevo ?? '');
            }
            if (!$iguales) {
                $cambios[] = ['campo' => $etiqueta, 'antes' => $actual ?: '—', 'despues' => $nuevo ?: '—'];
            }
        }

        $nombreFamiliaActual = $existente->familia?->nombre ?? '—';
        if (mb_strtolower($nombreFamiliaActual) !== mb_strtolower($payload['familia'])) {
            $cambios[] = ['campo' => 'Familia', 'antes' => $nombreFamiliaActual, 'despues' => $payload['familia']];
        }

        $stockActualPorMl = $existente->tamanos->mapWithKeys(fn ($t) => [$t->ml() => $t->stock]);
        foreach ([100, 50, 30] as $ml) {
            $actual = $stockActualPorMl[$ml] ?? null;
            $nuevo = $stocksNuevos[$ml];
            if ((int) $actual !== $nuevo) {
                $cambios[] = ['campo' => "Stock {$ml} ml", 'antes' => $actual ?? '—', 'despues' => $nuevo];
            }
        }

        return $cambios;
    }

    public function importarConfirmar(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $cacheKey = "import_fragancias_{$request->input('token')}";
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('admin.fragancias.importar.form')
                ->with('error', 'La previsualización expiró o ya fue confirmada. Sube el archivo de nuevo.');
        }

        $validas = $datos['validas'];
        if (empty($validas)) {
            Cache::forget($cacheKey);
            return redirect()->route('admin.fragancias.importar.form')
                ->with('error', 'No hay filas válidas para importar.');
        }

        $creadas = 0;
        $actualizadas = 0;

        DB::transaction(function () use ($validas, &$creadas, &$actualizadas) {
            foreach ($validas as $item) {
                $payload = $item['payload'];
                $nombreFamilia = $payload['familia'];
                unset($payload['familia']);

                // Búsqueda case-insensitive para que coincida con lo que se
                // mostró en la previsualización (que también es case-insensitive).
                // Si buscáramos por igualdad exacta aquí, "floral" con minúscula
                // crearía una familia duplicada aunque ya existiera "Floral".
                $familia = Familia::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreFamilia)])->first();
                if (!$familia) {
                    $familia = Familia::create(['nombre' => $nombreFamilia, 'activo' => true]);
                }
                $payload['familia_id'] = $familia->id;

                if ($item['accion'] === 'actualizar' && $item['fragancia_id']) {
                    $fragancia = Fragancia::findOrFail($item['fragancia_id']);
                    $fragancia->update($payload);
                    $actualizadas++;
                } else {
                    $fragancia = Fragancia::create($payload);
                    $creadas++;
                }

                $this->sincronizarTamanos($fragancia, $item['stocks']);
            }
        });

        Cache::forget("import_fragancias_" . $request->input('token'));

        // Auditoría: se notifica a los demás admins quién hizo la importación
        // y cuántas filas se crearon/actualizaron (no se le notifica a quien
        // la ejecutó, para no llenarle su propio panel de avisos obvios).
        $adminActual = $request->user();
        $otrosAdmins = User::where('role', 'admin')->where('id', '!=', $adminActual?->id)->get();
        foreach ($otrosAdmins as $admin) {
            NotificacionSegura::enviar(
                $admin,
                new ImportacionFraganciasCompletada($adminActual?->name ?? 'Un admin', $creadas, $actualizadas)
            );
        }

        $mensaje = [];
        if ($creadas > 0) $mensaje[] = "{$creadas} fragancia(s) nueva(s) creada(s)";
        if ($actualizadas > 0) $mensaje[] = "{$actualizadas} fragancia(s) actualizada(s)";

        return redirect()->route('admin.fragancias.index')
            ->with('success', 'Importación completada: ' . implode(' y ', $mensaje) . '.');
    }

    public function importarErrores(string $token)
    {
        $datos = Cache::get("import_fragancias_{$token}");
        if (!$datos || empty($datos['invalidas'])) {
            abort(404, 'No hay un reporte de errores disponible (puede que ya haya expirado).');
        }

        return response()->streamDownload(function () use ($datos) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['fila', 'errores'], ';');
            foreach ($datos['invalidas'] as $inv) {
                fputcsv($out, [$inv['fila'], implode(' | ', $inv['errores'])], ';');
            }
            fclose($out);
        }, 'errores_importacion_fragancias.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Redirige de vuelta a donde estaba el admin (con sus filtros y página),
     * usando el referer si viene del propio listado de fragancias; si no,
     * cae al índice sin filtros.
     */
    private function redirigirAlOrigen(Request $request)
    {
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, '/admin/fragancias')) {
            return redirect($referer);
        }
        return redirect()->route('admin.fragancias.index');
    }

    public function agregarStock(Request $request, string $tamanoId)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->redirigirAlOrigen($request)
                ->with('error', 'Ingresa una cantidad válida (número entero mayor a 0) para agregar al stock.');
        }

        $cantidad = (int) $request->input('cantidad');

        $tamano = DB::transaction(function () use ($tamanoId, $cantidad) {
            $tamano = FraganciaTamano::with('fragancia')->lockForUpdate()->findOrFail($tamanoId);

            $tamano->stock += $cantidad;
            $tamano->save();
            return $tamano;
        });

        return $this->redirigirAlOrigen($request)->with(
            'success',
            "Se agregaron {$cantidad} unidades a «{$tamano->fragancia->nombre} ({$tamano->tamano})». "
            . "Stock actual de ese tamaño: {$tamano->stock} unidades."
        );
    }

    public function destroy(string $id)
    {
        $f = Fragancia::findOrFail($id);

        if ($f->detalles()->exists()) {
            return redirect()->route('admin.fragancias.index')->with(
                'error',
                "No se puede eliminar «{$f->nombre}»: ya aparece en pedidos registrados. "
                . "Desactívala en lugar de eliminarla para conservar el historial de ventas."
            );
        }

        if ($f->imagen_principal) Storage::disk('public')->delete($f->imagen_principal);
        $f->delete();
        return redirect()->route('admin.fragancias.index')
                         ->with('success', 'Fragancia eliminada correctamente.');
    }
}

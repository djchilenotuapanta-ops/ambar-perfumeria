<?php
namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Familia;
use App\Models\Fragancia;
use App\Models\FraganciaTamano;
use App\Models\PedidoDetalle;
use App\Models\PresentacionEnvase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public function index()
    {
        // withCount con el filtro 'activo' evita dos problemas: 1) N+1 (antes se
        // hacía un COUNT por cada familia en la vista, vía $f->fragancias()->count()
        // como fallback porque nunca se cargaba fragancias_count), y 2) el conteo
        // mostrado en la home incluía fragancias inactivas, porque
        // Familia::fragancias() no filtra por activo.
        $familias = Familia::where('activo', true)
            ->withCount(['fragancias' => fn ($q) => $q->where('activo', true)])
            ->get();

        $envases = PresentacionEnvase::orderBy('tamano')->orderBy('categoria')->get();

        // Antes: una consulta COUNT por cada envase (N+1). Ahora: una sola
        // consulta que agrupa por tamaño+género y trae todos los conteos de
        // una vez; se mapean en memoria sobre la colección ya cargada.
        $conteosPorTamanoGenero = FraganciaTamano::query()
            ->join('fragancias', 'fragancias.id', '=', 'fragancia_tamanos.fragancia_id')
            ->where('fragancias.activo', true)
            ->selectRaw('fragancia_tamanos.tamano, fragancias.genero, COUNT(*) as total')
            ->groupBy('fragancia_tamanos.tamano', 'fragancias.genero')
            ->get();

        // Nota: en presentaciones_envases el tamaño se guarda sin unidad ("100"),
        // mientras que en fragancia_tamanos incluye "ml" ("100 ml"), por eso se normaliza.
        $envases->each(function ($e) use ($conteosPorTamanoGenero) {
            $tamanoNormalizado = trim($e->tamano) . ' ml';
            $esGeneroEspecifico = in_array($e->categoria, ['hombre', 'mujer', 'unisex'], true);

            $e->productos_count = $conteosPorTamanoGenero
                ->filter(function ($c) use ($tamanoNormalizado, $e, $esGeneroEspecifico) {
                    return $c->tamano === $tamanoNormalizado
                        && (!$esGeneroEspecifico || $c->genero === $e->categoria);
                })
                ->sum('total');
        });

        $topVentasCantidad = 3;
        // Mismo criterio que usa el reporte de admin (ReporteController): solo
        // cuentan unidades de pedidos con pago_estado = 'pagado'. Antes esta
        // subconsulta sumaba TODOS los pedido_detalles sin filtrar, así que un
        // pedido pendiente o cancelado podía inflar el ranking de "más vendidas"
        // de la portada aunque nunca se haya cobrado.
        $ventasSubquery = PedidoDetalle::selectRaw('SUM(pedido_detalles.cantidad)')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalles.pedido_id')
            ->where('pedidos.pago_estado', 'pagado')
            ->whereColumn('pedido_detalles.fragancia_id', 'fragancias.id');

        $masVendidas = Fragancia::where('activo', true)
            ->with(['familia', 'tamanos'])
            ->select('fragancias.*')
            ->selectSub($ventasSubquery, 'unidades_vendidas')
            ->orderByDesc('unidades_vendidas')
            ->limit($topVentasCantidad)
            ->get()
            ->filter(fn ($fragancia) => (int) ($fragancia->unidades_vendidas ?? 0) > 0)
            ->values();

        // Antes: se cargaba TODO el catálogo activo (con sus tamaños) en memoria
        // solo para filtrar cuáles tenían descuento. Ahora se filtra a nivel de
        // base de datos primero (mismo criterio que ya usa catalogo() para
        // ?oferta=1), y solo el subconjunto ya reducido se ordena en PHP por
        // porcentaje de descuento (ese cálculo sí requiere comparar precios ya
        // cargados, pero ahora opera sobre decenas de filas, no el catálogo entero).
        $ofertas = Fragancia::where('activo', true)
            ->with(['familia', 'tamanos'])
            ->whereHas('tamanos', function ($qt) {
                $qt->whereNotNull('precio_especial')->whereColumn('precio_especial', '<', 'precio');
            })
            ->get()
            ->sortByDesc(fn ($fragancia) => $fragancia->descuentoPorcentaje())
            ->take(8)
            ->values();

        return view('app.front.index', compact('familias', 'masVendidas', 'envases', 'ofertas'));
    }

    public function catalogo(Request $request)
    {
        $query = Fragancia::where('activo', true)->with(['familia', 'tamanos']);

        // Filtrar solo fragancias en oferta/promoción
        if ($request->boolean('oferta')) {
            $query->whereHas('tamanos', function ($qt) {
                $qt->whereNotNull('precio_especial')->whereColumn('precio_especial', '<', 'precio');
            });
        }

        // Filtrar por envase/tamaño (p.e. "50 ml"). El valor que llega por
        // querystring puede venir como "50" (así se guarda en
        // presentaciones_envases, ver envases-strip.blade.php y
        // envases-presentaciones.blade.php) o como "50 ml" (formato de
        // fragancia_tamanos.tamano). Antes se comparaba tal cual llegaba
        // contra fragancia_tamanos.tamano: como ese campo siempre incluye
        // "ml", el filtro nunca encontraba coincidencias y cualquier click
        // en un envase de la home devolvía "No se encontraron fragancias".
        if ($request->filled('envase')) {
            $numeroTamano = preg_replace('/[^0-9]/', '', (string) $request->envase);
            if ($numeroTamano !== '') {
                $query->whereHas('tamanos', function ($q) use ($numeroTamano) {
                    $q->where('tamano', $numeroTamano . ' ml');
                });
            }
        }

        if ($request->filled('familia')) {
            $query->where('familia_id', $request->familia);
        }
        if ($request->filled('genero')) {
            $query->where('genero', $request->genero);
        }
        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                  ->orWhere('casa_perfumista', 'like', '%' . $termino . '%')
                  ->orWhere('descripcion', 'like', '%' . $termino . '%')
                  ->orWhere('notas_salida', 'like', '%' . $termino . '%')
                  ->orWhere('notas_corazon', 'like', '%' . $termino . '%')
                  ->orWhere('notas_fondo', 'like', '%' . $termino . '%');
            });
        }
        if ($request->filled('orden')) {

            $precioFinalSub = FraganciaTamano::selectRaw(
                    'MIN(CASE WHEN precio_especial IS NOT NULL AND precio_especial < precio THEN precio_especial ELSE precio END)'
                )
                ->whereColumn('fragancia_id', 'fragancias.id');

            match($request->orden) {
                'precio_asc'  => $query->orderBy($precioFinalSub, 'asc'),
                'precio_desc' => $query->orderBy($precioFinalSub, 'desc'),
                default       => $query->orderBy('nombre', 'asc'),
            };
        }

        $fragancias = $query->paginate(12)->withQueryString();
        $familias   = Familia::where('activo', true)->get();
        $envases    = PresentacionEnvase::orderBy('tamano')->orderBy('categoria')->get();

        return view('app.front.catalogo', compact('fragancias', 'familias', 'envases'));
    }

    public function buscarAutocompletado(Request $request)
    {
        $termino = trim((string) $request->query('q', ''));

        if (mb_strlen($termino) < 2) {
            return response()->json([]);
        }

        $resultados = Fragancia::where('activo', true)
            ->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                  ->orWhere('casa_perfumista', 'like', '%' . $termino . '%');
            })
            ->orderBy('nombre')
            ->limit(6)
            ->with('tamanos')
            ->get(['id', 'nombre', 'casa_perfumista', 'slug', 'imagen_principal', 'precio']);

        return response()->json($resultados->map(fn($f) => [
            'nombre'  => $f->nombre,
            'casa'    => $f->casa_perfumista,
            'url'     => route('fragancia.show', $f->slug),
            'imagen'  => $f->imagen_principal ? asset('storage/' . $f->imagen_principal) : null,
            'precio'  => number_format($f->precioFinal(), 0, ',', '.'),
        ]));
    }

    public function fragancia(string $slug)
    {
        $fragancia  = Fragancia::where('slug', $slug)->where('activo', true)
                        ->with(['familia', 'imagenes', 'tamanos'])->firstOrFail();
        $relacionadas = Fragancia::where('familia_id', $fragancia->familia_id)
                        ->where('id', '!=', $fragancia->id)
                        ->where('activo', true)->with('tamanos')->take(4)->get();
        $mostrarStockBajoCliente = (bool) \App\Models\Configuracion::obtener(
            'mostrar_stock_bajo_cliente', config('comercial.mostrar_stock_bajo_cliente', false)
        );

        $resenas = \App\Models\Resena::with('user')
            ->where('fragancia_id', $fragancia->id)
            ->orderByDesc('created_at')
            ->get();

        $promedioResenas = $resenas->isNotEmpty() ? round($resenas->avg('calificacion'), 1) : 0;
        $miResena = Auth::check() ? \App\Models\Resena::where('fragancia_id', $fragancia->id)
            ->where('user_id', Auth::id())
            ->first() : null;

        return view('app.front.fragancia', compact('fragancia', 'relacionadas', 'mostrarStockBajoCliente', 'resenas', 'promedioResenas', 'miResena'));
    }

    public function familia(int $id)
    {
        $familia    = Familia::findOrFail($id);
        $fragancias = Fragancia::where('familia_id', $id)->where('activo', true)
                        ->with('tamanos')->paginate(12);
        $familias   = Familia::where('activo', true)->get();
        $envases    = PresentacionEnvase::orderBy('tamano')->orderBy('categoria')->get();

        return view('app.front.catalogo', compact('fragancias', 'familias', 'familia', 'envases'));
    }

    public function familias()
    {
        $familias = Familia::where('activo', true)->withCount('fragancias')->get();
        return view('app.front.familias', compact('familias'));
    }

    public function regalo(Request $request)
    {
        $familias = Familia::where('activo', true)->get();
        $regalo = session('regalo_config', [
            'ocasion' => '',
            'presentacion' => 'blanco',
            'mensaje' => '',
            'anonimo' => false,
        ]);
        $returnTo = 'checkout';

        return view('app.front.regalo', compact('familias', 'regalo', 'returnTo'));
    }

    public function guardarRegalo(Request $request)
    {
        $data = $request->validate([
            'ocasion' => 'nullable|in:Cumpleaños,Aniversario,San Valentín,Navidad,Graduación,Sin ocasión',
            'presentacion' => 'required|in:blanco,negro',
            'mensaje' => 'nullable|string|max:200',
            'anonimo' => 'nullable|boolean',
        ]);

        session([
            'regalo_config' => [
                'ocasion' => $data['ocasion'] ?? '',
                'presentacion' => $data['presentacion'],
                'mensaje' => trim((string) ($data['mensaje'] ?? '')),
                'anonimo' => (bool) ($data['anonimo'] ?? false),
            ],
        ]);

        // Sin esto, los ítems ya agregados al carrito antes de configurar el
        // regalo se quedan con regalo_presentacion = null, y CheckoutController
        // usa justo ese campo (no la sesión) para decidir si adjunta el bloque
        // "REGALO: ..." a las notas del pedido. Resultado sin este sync: el
        // usuario ve "Configuración guardada" en el checkout, pero al confirmar
        // el pedido la ocasión/mensaje/tarjeta se pierden sin ningún aviso.
        $user = Auth::user();
        if ($user instanceof User) {
            Carrito::where('user_id', $user->id)
                ->update(['regalo_presentacion' => $data['presentacion']]);
        }

        return redirect()->route('checkout.show')
            ->with('success', 'Opciones de regalo guardadas. Revisa tu pedido para confirmar.');
    }

    public function dashboard()
    {
        $user     = Auth::user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $pedidos  = $user->pedidos()->latest()->take(5)->get();

        return view('app.front.dashboard', compact('user', 'pedidos'));
    }

    public function pedidos()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $pedidos = $user->pedidos()
            ->latest()
            ->paginate(10);

        return view('app.front.pedidos', compact('pedidos'));
    }
}

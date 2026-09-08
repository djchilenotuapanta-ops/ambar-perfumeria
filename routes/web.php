<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\InformacionController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FamiliaController;
use App\Http\Controllers\Admin\FraganciaController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\PresentacionEnvaseController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\LimpiezaController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/catalogo', [HomeController::class, 'catalogo'])->name('catalogo');
Route::get('/catalogo/buscar', [HomeController::class, 'buscarAutocompletado'])
    ->middleware('throttle:30,1')
    ->name('catalogo.buscar');
Route::get('/fragancia/{slug}', [HomeController::class, 'fragancia'])->name('fragancia.show');
Route::get('/familias', [HomeController::class, 'familias'])->name('familias.index');
Route::get('/familia/{id}', [HomeController::class, 'familia'])->name('familia.show');
Route::get('/configurar-regalo', [HomeController::class, 'regalo'])->name('regalo');
Route::post('/configurar-regalo', [HomeController::class, 'guardarRegalo'])->name('regalo.guardar');

Route::get('/politica-de-envios', [InformacionController::class, 'envios'])->name('info.envios');
Route::get('/devoluciones', [InformacionController::class, 'devoluciones'])->name('info.devoluciones');
Route::get('/garantia-de-autenticidad', [InformacionController::class, 'garantia'])->name('info.garantia');
Route::get('/contacto', [InformacionController::class, 'contacto'])->name('info.contacto');

Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/mis-pedidos', [HomeController::class, 'pedidos'])->name('pedidos.historial');

    Route::prefix('carrito')->name('carrito.')->group(function () {
        Route::get('/',         [CarritoController::class, 'index'])   ->name('index');
        Route::post('/agregar', [CarritoController::class, 'agregar']) ->name('agregar');
        Route::patch('/{id}',   [CarritoController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{id}',  [CarritoController::class, 'quitar'])  ->name('quitar');
        Route::delete('/',      [CarritoController::class, 'vaciar'])  ->name('vaciar');
    });

    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',  [CheckoutController::class, 'show'])    ->name('show');
        Route::post('/', [CheckoutController::class, 'procesar'])->name('procesar');
    });
    Route::get('/pedido/{numeroPedido}/confirmacion', [CheckoutController::class, 'confirmacion'])
         ->name('pedidos.confirmacion');
    Route::post('/pedido/{numeroPedido}/comprobante', [CheckoutController::class, 'subirComprobante'])
         ->name('pedidos.comprobante');
    Route::get('/pedido/{numeroPedido}/comprobante', [CheckoutController::class, 'verComprobante'])
         ->name('pedidos.ver-comprobante');
    Route::post('/pedido/{numeroPedido}/cancelar', [CheckoutController::class, 'cancelar'])
         ->name('pedidos.cancelar');

    Route::get('/perfil',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/perfil',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/perfil/password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('/fragancia/{slug}/reseña', [ResenaController::class, 'store'])->name('reseñas.store');

    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/',                  [NotificacionController::class, 'index'])          ->name('index');
        Route::patch('/{id}/leida',      [NotificacionController::class, 'marcarLeida'])     ->name('leida');
        Route::patch('/{id}/no-leida',   [NotificacionController::class, 'marcarNoLeida'])   ->name('noLeida');
        Route::patch('/marcar-todas',    [NotificacionController::class, 'marcarTodasLeidas'])->name('marcarTodas');
        Route::delete('/leidas',         [NotificacionController::class, 'eliminarLeidas'])  ->name('eliminarLeidas');
        Route::delete('/{id}',           [NotificacionController::class, 'eliminar'])        ->name('eliminar');
        Route::get('/{id}/ir',           [NotificacionController::class, 'ir'])              ->name('ir');
    });
});

Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('familias', FamiliaController::class)
         ->except(['show'])
         ->names('familias');

    Route::patch('/fragancia-tamanos/{tamanoId}/agregar-stock', [FraganciaController::class, 'agregarStock'])->name('fragancias.agregar-stock');
    Route::get('/fragancias/crear', [FraganciaController::class, 'create'])->name('fragancias.crear');
    Route::get('/fragancias/importar', [FraganciaController::class, 'importarForm'])->name('fragancias.importar.form');
    Route::get('/fragancias/importar/plantilla', [FraganciaController::class, 'importarPlantilla'])->name('fragancias.importar.plantilla');
    Route::post('/fragancias/importar/previsualizar', [FraganciaController::class, 'importarPrevisualizar'])->name('fragancias.importar.previsualizar');
    Route::post('/fragancias/importar/confirmar', [FraganciaController::class, 'importarConfirmar'])->name('fragancias.importar.confirmar');
    Route::get('/fragancias/importar/errores/{token}', [FraganciaController::class, 'importarErrores'])->name('fragancias.importar.errores');
    Route::get('/fragancias/sin-costo', [FraganciaController::class, 'sinCosto'])->name('fragancias.sin-costo');
    Route::post('/fragancias/sin-costo/aplicar-estimado', [FraganciaController::class, 'aplicarCostoEstimado'])->name('fragancias.sin-costo.aplicar-estimado');
    Route::patch('/fragancias/{fragancia}/costo-manual', [FraganciaController::class, 'guardarCostoManual'])->name('fragancias.costo-manual');
    Route::resource('fragancias', FraganciaController::class)
         ->except(['show'])
         ->names('fragancias');

    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/',              [PedidoController::class, 'index'])           ->name('index');
        Route::get('/{id}',          [PedidoController::class, 'show'])            ->name('show');
        Route::patch('/{id}/estado', [PedidoController::class, 'actualizarEstado'])->name('estado');
    });

    Route::resource('usuarios', UsuarioController::class)
         ->only(['index', 'edit', 'update', 'destroy'])
         ->names('usuarios');

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas',       [ReporteController::class, 'ventas'])      ->name('ventas');
        Route::get('/ventas/pdf',   [ReporteController::class, 'ventasPdf'])   ->name('ventas.pdf');
        Route::get('/productos',     [ReporteController::class, 'productos'])    ->name('productos');
        Route::get('/productos/pdf', [ReporteController::class, 'productosPdf']) ->name('productos.pdf');
        Route::get('/clientes',      [ReporteController::class, 'clientes'])     ->name('clientes');
        Route::get('/clientes/pdf',  [ReporteController::class, 'clientesPdf'])  ->name('clientes.pdf');
    });

    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/recalcular-precios', [ConfiguracionController::class, 'recalcularPrecios'])->name('configuracion.recalcular_precios');

    Route::get('/envases', [PresentacionEnvaseController::class, 'index'])->name('envases.index');
    Route::post('/envases', [PresentacionEnvaseController::class, 'store'])->name('envases.store');
    Route::patch('/envases/{id}', [PresentacionEnvaseController::class, 'update'])->name('envases.update');
    Route::delete('/envases/{id}', [PresentacionEnvaseController::class, 'destroy'])->name('envases.destroy');

    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/',                  [NotificacionController::class, 'index'])          ->name('index');
        Route::patch('/{id}/leida',      [NotificacionController::class, 'marcarLeida'])     ->name('leida');
        Route::patch('/{id}/no-leida',   [NotificacionController::class, 'marcarNoLeida'])   ->name('noLeida');
        Route::patch('/marcar-todas',    [NotificacionController::class, 'marcarTodasLeidas'])->name('marcarTodas');
        Route::delete('/leidas',         [NotificacionController::class, 'eliminarLeidas'])  ->name('eliminarLeidas');
        Route::delete('/{id}',           [NotificacionController::class, 'eliminar'])        ->name('eliminar');
        Route::get('/{id}/ir',           [NotificacionController::class, 'ir'])              ->name('ir');
    });
});

// Ruta de limpieza (solo para admin)
Route::middleware(['auth', 'rol:admin'])->group(function () {
    Route::get('/admin/limpiar-tamanos-sin-stock', [LimpiezaController::class, 'eliminarTamanosSinStock'])
        ->name('admin.limpiar-tamanos');
});

// Ruta para servir imágenes de almacenamiento
Route::get('/imagen-almacenamiento/{path}', function ($path) {
    $filePath = base_path('storage/app/public/' . $path);

    // Validaciones de seguridad
    if (!file_exists($filePath) || !str_starts_with(realpath($filePath), realpath(base_path('storage/app/public')))) {
        abort(404);
    }

    return response()->file($filePath, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.imagen');

require __DIR__.'/auth.php';

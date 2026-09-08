<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ambar') — Fragancias de Lujo</title>

    {{--
        SEO base: cada vista puede sobreescribir estas secciones con @section(...).
        Si una vista no define nada, se usan estos valores por defecto — así
        cada página tiene SIEMPRE una meta description y Open Graph válidos,
        aunque nadie se acuerde de configurarlos en la vista puntual.
    --}}
    @php
        // Reutiliza el mismo título que ya define cada vista con @section('title', ...)
        // en vez de duplicar lógica — si la vista no define nada, cae en 'Ambar'.
        $tituloCompleto = trim($__env->yieldContent('title', 'Ambar')) . ' — Fragancias de Lujo';
        $descripcionCompleta = trim($__env->yieldContent('meta_description', 'Fragancias originales de las mejores casas perfumistas. Descubre tu aroma ideal en Ambar Parfums, con envío a nivel nacional en Ecuador.'));
    @endphp
    <meta name="description" content="{{ $descripcionCompleta }}">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Ambar Parfums">
    <meta property="og:title" content="@yield('og_title', $tituloCompleto)">
    <meta property="og:description" content="{{ $descripcionCompleta }}">
    <meta property="og:image" content="@yield('og_image', asset('storage/fragancias/generic/frasco_unisex.jpg'))">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $tituloCompleto)">
    <meta name="twitter:description" content="{{ $descripcionCompleta }}">
    <meta name="twitter:image" content="@yield('og_image', asset('storage/fragancias/generic/frasco_unisex.jpg'))">

    <style>html, body { overscroll-behavior: none !important; overscroll-behavior-x: none !important; }</style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @yield('styles')
</head>
<body>

<header>
    <!-- Barra superior: redes + acceso -->
    <div class="header-top d-flex justify-content-between align-items-center px-4">
        <div class="social-icons">
            <a href="{{ config('comercial.social.instagram') }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="{{ config('comercial.social.facebook') }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="{{ config('comercial.social.tiktok') }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
        </div>
        <div class="header-top-offer text-center d-none d-md-block">
            🌸 Envío gratis desde ${{ number_format((float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)), 0, '.', ',') }} USD
        </div>
        <div class="d-flex gap-2 align-items-center">
            @auth
                <x-notificaciones-campanita />
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-circle" title="Mi cuenta">
                    <i class="fas fa-user"></i>
                </a>
                <a href="{{ route('carrito.index') }}" class="btn btn-light btn-circle" title="Carrito">
                    <i class="fas fa-shopping-bag"></i>
                    @php $totalCarrito = auth()->user()->carrito()->sum('cantidad') @endphp
                    @if($totalCarrito > 0)
                        <span class="badge-carrito">{{ $totalCarrito }}</span>
                    @endif
                </a>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-light btn-circle" title="Cerrar sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-light btn-circle" title="Acceder">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
                <a href="{{ route('register') }}" class="btn btn-light btn-circle" title="Registrarse">
                    <i class="fas fa-user-plus"></i>
                </a>
            @endauth
        </div>
    </div>

    <!-- Navegación principal -->
    @include('partials.menu')
</header>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show m-0 rounded-0" role="alert">
    <div class="container">{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show m-0 rounded-0" role="alert">
    <div class="container">{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@yield('contenido')

<footer class="footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="footer-brand">
                    <div class="footer-logo">AMBAR</div>
                    <div class="footer-logo-sub">PARFUMS</div>
                </div>
                <p class="mt-2" style="font-size:0.85rem;color:#C4A882;">
                    Fragancias de lujo auténticas.<br>Tu aroma, tu historia.
                </p>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Explorar</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('catalogo') }}">Catálogo completo</a></li>
                    <li><a href="{{ route('regalo') }}">Configurar regalo</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Ayuda</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('info.envios') }}">Política de envíos</a></li>
                    <li><a href="{{ route('info.devoluciones') }}">Devoluciones</a></li>
                    <li><a href="{{ route('info.garantia') }}">Garantía de autenticidad</a></li>
                    <li><a href="{{ route('info.contacto') }}">Contacto</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Contáctenos</h5>
                <p><i class="fas fa-envelope me-2"></i>perfumes30060@gmail.com</p>
                <p>
                    <i class="fab fa-whatsapp me-2"></i>
                    <a href="https://api.whatsapp.com/send?phone=593959787097&text=Hola%2C%20quisiera%20informacion%20sobre%20sus%20fragancias" target="_blank" rel="noopener noreferrer">
                        +593 95 978 7097
                    </a>
                </p>
                <p><i class="fas fa-map-marker-alt me-2"></i>Ecuador</p>
            </div>
        </div>
    </div>
</footer>
<section class="copyright text-center py-3">
    <p class="mb-0">© {{ date('Y') }} Ambar. Todos los derechos reservados.</p>
</section>

<button class="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fas fa-chevron-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@include('components.modal-confirmacion')

<script>
// Botón mostrar/ocultar contraseña en todos los campos type="password" del sitio.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.dataset.toggleAplicado) return;
        input.dataset.toggleAplicado = '1';

        var wrapper = document.createElement('div');
        wrapper.className = 'password-toggle-wrap';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-toggle-btn';
        btn.setAttribute('aria-label', 'Mostrar contraseña');
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        wrapper.appendChild(btn);

        btn.addEventListener('click', function () {
            var visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.innerHTML = visible ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });

    // --- Loading guard global (mismo patrón que el panel admin) ---
    // Deshabilita el botón de envío y muestra un spinner al enviar cualquier
    // formulario del sitio que escriba datos (no aplica a los de método GET,
    // como filtros de búsqueda). Evita doble clic / doble envío accidental.
    // Para saltarlo puntualmente, agrega data-no-loading-guard al <form>.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.method.toLowerCase() === 'get') return;
        if (form.hasAttribute('data-no-loading-guard')) return;

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
            if (btn.disabled) return;

            const textoCargando = btn.dataset.textoCargando || 'Enviando...';

            if (btn.tagName === 'INPUT') {
                btn.disabled = true;
                btn.value = textoCargando;
            } else {
                btn.dataset.htmlOriginal = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + textoCargando;
            }
        });
    });
});
</script>
@yield('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Ambar</title>

    <style>html, body { overscroll-behavior: none !important; overscroll-behavior-x: none !important; }</style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    @yield('styles')
</head>
<body>

    @include('partials.topbar')
    @include('partials.sidebar')

    @if(session('success') || session('error'))
    <div style="margin-left:var(--sidebar-w); margin-top:var(--topbar-h); padding:1rem 2rem 0;">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>
    @endif

    @yield('contenido')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('components.modal-confirmacion')

    <script>
        // Toggle submenús del sidebar
        document.querySelectorAll('.submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                submenu.classList.toggle('show');
                this.classList.toggle('active');
            });
        });
        // Abrir submenú si contiene la ruta activa
        document.querySelectorAll('.submenu a.active').forEach(a => {
            const submenu = a.closest('.submenu');
            if (submenu) {
                submenu.classList.add('show');
                submenu.previousElementSibling?.classList.add('active');
            }
        });

        // --- Mostrar/ocultar contraseña ---
        // Genérico: cualquier <button class="toggle-password-btn"> dentro de un
        // .input-group con un <input type="password"> hermano queda cubierto,
        // sin JS por vista. Nació en usuarios/edit.blade.php, pero sirve para
        // cualquier formulario admin que agregue este mismo patrón.
        document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = btn.closest('.input-group')?.querySelector('input[type="password"], input[type="text"].toggle-password-target');
                if (!input) return;

                const icono = btn.querySelector('i');
                const mostrando = input.type === 'text';

                input.type = mostrando ? 'password' : 'text';
                if (!mostrando) input.classList.add('toggle-password-target');
                icono?.classList.toggle('fa-eye', mostrando);
                icono?.classList.toggle('fa-eye-slash', !mostrando);
                btn.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        });

        // --- Loading guard global ---
        // Evita doble clic/doble envío en CUALQUIER formulario del panel admin:
        // al enviar, deshabilita el botón y muestra un spinner con "Guardando...".
        // Se aplica solo a formularios que realmente escriben datos (no a los
        // de búsqueda/filtro, que van por GET). Para saltarlo en un caso puntual,
        // agrega el atributo data-no-loading-guard al <form>.
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.method.toLowerCase() === 'get') return;
            if (form.hasAttribute('data-no-loading-guard')) return;

            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (btn) {
                if (btn.disabled) return;

                const textoCargando = btn.dataset.textoCargando || 'Guardando...';

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
    </script>
    @yield('scripts')
</body>
</html>

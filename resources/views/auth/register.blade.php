@extends('layouts.template')
@section('title', 'Crear Cuenta')

@section('contenido')
<div class="auth-page">
    <div class="row g-0" style="max-width:900px;margin:0 auto;min-height:80vh;align-items:center;">

        <div class="col-md-5 d-none d-md-flex flex-column justify-content-center pe-4">
            <div class="auth-logo mb-2">AMBAR</div>
            <div class="auth-logo-sub mb-4">PARFUMS</div>
            <h3 style="font-family:'Cormorant Garamond',serif;color:var(--ep-primary);font-size:1.5rem;">
                Únete al mundo de las fragancias de lujo
            </h3>
            <div class="mt-3 d-flex flex-column gap-2">
                @foreach(['🎁 Muestras gratuitas con tus pedidos','✦ Preventas de nuevas fragancias','📖 Consultoría olfativa experta'] as $b)
                <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;color:var(--ep-text);">
                    {{ $b }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-7">
            <div class="auth-card">
                <h2 class="auth-title">Crear cuenta gratis</h2>
                <p class="auth-sub">Bienvenido a Ambar</p>

                @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" id="registerPassword" class="form-control" required>
                        <small class="text-muted">Mínimo 8 caracteres</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="registerPasswordConfirm" class="form-control" required>
                        <small id="registerPasswordMatchError" class="text-danger d-none">Las contraseñas no coinciden.</small>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 py-2">
                        Crear mi cuenta
                    </button>
                </form>

                <div class="auth-footer">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pass = document.getElementById('registerPassword');
    const confirmar = document.getElementById('registerPasswordConfirm');
    const error = document.getElementById('registerPasswordMatchError');
    if (!pass || !confirmar || !error) return;

    function validar() {
        if (!confirmar.value) {
            confirmar.classList.remove('is-invalid');
            error.classList.add('d-none');
            return;
        }
        const coincide = pass.value === confirmar.value;
        confirmar.classList.toggle('is-invalid', !coincide);
        error.classList.toggle('d-none', coincide);
    }

    pass.addEventListener('input', validar);
    confirmar.addEventListener('input', validar);
});
</script>
@endsection

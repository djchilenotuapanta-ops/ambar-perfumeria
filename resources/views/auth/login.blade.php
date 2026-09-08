@extends('layouts.template')
@section('title', 'Iniciar Sesión')

@section('contenido')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo">AMBAR</div>
            <div class="auth-logo-sub">PARFUMS</div>
        </div>
        <h2 class="auth-title">Bienvenido de vuelta</h2>
        <p class="auth-sub">Accede a tu cuenta Ambar</p>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif
        @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label d-flex justify-content-between">
                    Contraseña
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:.8rem;color:var(--ep-muted);">
                        ¿Olvidaste la contraseña?
                    </a>
                    @endif
                </label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
            </div>
            <button type="submit" class="btn btn-gold w-100 py-2">Acceder a mi cuenta</button>
        </form>

        <div class="auth-footer">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}">Crear cuenta gratis</a>
        </div>
    </div>
</div>
@endsection

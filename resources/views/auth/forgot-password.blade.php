@extends('layouts.template')
@section('title', 'Recuperar Contraseña')

@section('contenido')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo">AMBAR</div>
            <div class="auth-logo-sub">PARFUMS</div>
        </div>
        <h2 class="auth-title">Recuperar contraseña</h2>
        <p class="auth-sub">Ingresa tu correo y te enviamos un enlace para restablecer tu contraseña.</p>

        @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-gold w-100 py-2">Enviar enlace de recuperación</button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">← Volver al inicio de sesión</a>
        </div>
    </div>
</div>
@endsection

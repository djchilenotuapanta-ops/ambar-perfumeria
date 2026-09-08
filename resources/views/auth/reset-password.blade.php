@extends('layouts.template')
@section('title', 'Nueva Contraseña')

@section('contenido')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo">AMBAR</div>
            <div class="auth-logo-sub">PARFUMS</div>
        </div>
        <h2 class="auth-title">Nueva contraseña</h2>

        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email', $request->email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-gold w-100 py-2">Restablecer contraseña</button>
        </form>
    </div>
</div>
@endsection

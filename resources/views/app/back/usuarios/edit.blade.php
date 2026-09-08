@extends('layouts.template-back')
@section('title', 'Editar Usuario')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Editar Usuario</h3>
            <p class="text-muted mb-0">{{ $usuario->email }}</p>
        </div>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">← Regresar al panel</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.usuarios.update', $usuario->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card mb-3">
                    <div class="admin-card-header"><h6 class="mb-0">Datos del Usuario</h6></div>
                    <div class="admin-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Nombre *</label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name', $usuario->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Correo electrónico *</label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $usuario->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1"
                                            aria-label="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Dejar vacío para mantener la actual</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" tabindex="-1"
                                            aria-label="Mostrar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-card mb-3">
                    <div class="admin-card-header"><h6 class="mb-0">Rol del Sistema</h6></div>
                    <div class="admin-card-body">
                        <label class="form-label fw-600">Rol *</label>
                        <select name="role" class="form-select" required>
                            @foreach(\App\Enums\UserRole::cases() as $rol)
                            <option value="{{ $rol->value }}" {{ old('role', $usuario->role) === $rol->value ? 'selected' : '' }}>
                                {{ $rol->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-save me-1"></i> Guardar Cambios
            </button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

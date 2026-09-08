@extends('layouts.template')
@section('title', 'Mi Perfil')

@section('contenido')
<div class="container py-5" style="max-width:700px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="mb-0" style="font-family:'Cormorant Garamond',serif;color:#2C1810;">Mi Perfil</h2>
            <p class="text-muted small mb-0">Desde aquí puedes actualizar tus datos, cambiar la contraseña o cerrar sesión.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">← Volver a mi cuenta</a>
        </div>
    </div>

    @if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show">
        Perfil actualizado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="mb-3" style="font-family:'Cormorant Garamond',serif;color:#2C1810;">Información Personal</h5>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Foto de perfil</label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="rounded-circle border d-flex align-items-center justify-content-center overflow-hidden" style="width:84px;height:84px;background:#f7efe9;">
                                @php($fotoPerfil = optional($user->perfil)->foto)
                                @if($fotoPerfil)
                                    <img src="{{ asset('storage/' . $fotoPerfil) }}" alt="Foto de perfil" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <span class="text-muted" style="font-size:1.6rem;">👤</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                <div class="form-text">Sube una foto JPG, PNG o WEBP. Máximo 2 MB.</div>
                                @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-dark-perfume mt-3">Guardar cambios</button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h5 class="mb-3" style="font-family:'Cormorant Garamond',serif;color:#2C1810;">Cambiar Contraseña</h5>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control @error('current_password','updatePassword') is-invalid @enderror">
                        @error('current_password','updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control @error('password','updatePassword') is-invalid @enderror">
                        @error('password','updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-dark-perfume mt-3">Actualizar contraseña</button>
            </form>
        </div>
    </div>

    <div class="card border-0 border-danger shadow-sm rounded-3">
        <div class="card-body p-4">
            <h5 class="mb-2 text-danger">Zona de peligro</h5>
            <p class="text-muted small">Una vez eliminada tu cuenta, todos tus datos serán borrados permanentemente.</p>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                Eliminar mi cuenta
            </button>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">¿Eliminar cuenta?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esta acción es permanente e irreversible. Ingresa tu contraseña para confirmar.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" id="deleteForm">
                        @csrf @method('DELETE')
                        <input type="password" name="password" class="form-control" placeholder="Tu contraseña" required>
                        @error('password','userDeletion')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="deleteForm" class="btn btn-danger btn-sm">Eliminar cuenta</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.template-back')
@section('title', 'Usuarios')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Usuarios del Sistema</h3>
            <p class="text-muted mb-0">Administra clientes y personal de Ambar</p>
        </div>
    </div>

    <!-- Guía de roles -->
    <div class="admin-guide-banner mb-4">
        <h6 class="text-center" style="color:#D4AF37;margin-bottom:.75rem;">✦ Roles del Sistema</h6>
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-3" style="font-size:.8rem;">
            @foreach([
                ['admin','Administrador General','Acceso total'],
                ['cliente','Cliente','Cuenta de comprador'],
            ] as [$code,$nombre,$desc])
            <div class="d-flex align-items-center justify-content-center gap-2 text-center">
                <span class="role-badge role-{{ $code }}">{{ $nombre }}</span>
                <span class="text-muted">{{ $desc }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="row g-2 mb-3">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscar ?? '' }}"
                            class="form-control"
                            placeholder="Buscar por nombre, correo o rol"
                            aria-label="Buscar usuarios"
                        >
                        <button type="submit" class="btn btn-outline-primary">Buscar</button>
                    </div>
                </div>
                @if(!empty($buscar))
                <div class="col-12 col-md-auto">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle table-mobile-stack">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $u)
                        <tr>
                            <td data-label="Usuario">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle border d-flex align-items-center justify-content-center overflow-hidden" style="width:42px;height:42px;background:#f7efe9;">
                                        @php($fotoPerfil = optional($u->perfil)->foto)
                                        @if($fotoPerfil)
                                            <img src="{{ asset('storage/' . $fotoPerfil) }}" alt="Foto de {{ $u->name }}" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <span style="font-size:1rem;">👤</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-600">{{ $u->name }}</div>
                                        <div class="text-muted small">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Rol">
                                <span class="role-badge role-{{ $u->role }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td data-label="Registro">
                                {{ $u->created_at->format('d/m/Y') }}
                            </td>
                            <td data-label="Acciones">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.usuarios.edit', $u->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($u->id !== auth()->id())
                                    <button onclick="eliminarUsuario({{ $u->id }},'{{ $u->name }}')"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                @if(!empty($buscar))
                                    No se encontraron usuarios para "{{ $buscar }}".
                                @else
                                    No hay usuarios.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $usuarios->links() }}
        </div>
    </div>
</div>

<form id="fEliminarU" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection
@section('scripts')
<script>
function eliminarUsuario(id, nombre) {
    confirmarAccion({
        titulo: 'Eliminar usuario',
        mensaje: '¿Eliminar al usuario "' + nombre + '"? Esta acción no se puede deshacer.',
        onConfirmar: function () {
            const f = document.getElementById('fEliminarU');
            f.action = '/admin/usuarios/' + id;
            f.submit();
        }
    });
}
</script>
@endsection

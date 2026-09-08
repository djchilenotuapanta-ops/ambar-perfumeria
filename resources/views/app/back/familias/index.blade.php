@extends('layouts.template-back')
@section('title', 'Familias Olfativas')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Familias Olfativas</h3>
            <p class="text-muted mb-0">Categorías de fragancias del catálogo</p>
        </div>
        <a href="{{ route('admin.familias.create') }}" class="btn btn-gold">
            <i class="fas fa-plus me-1"></i> Nueva Familia
        </a>
    </div>

    <!-- Guía de familias -->
    <div class="admin-guide-banner mb-4">
        <div class="row g-2">
            @foreach([
                ['fas fa-spa','Floral','Rosa, jazmín, peonía'],
                ['fas fa-tree','Maderado','Cedro, sándalo, vetiver'],
                ['fas fa-star-and-crescent','Oriental','Ámbar, vainilla, especias'],
                ['fas fa-lemon','Fresco / Cítrico','Bergamota, limón, menta'],
                ['fas fa-water','Acuático','Sal marina, brisa, ozono'],
                ['fas fa-fire','Oud','Agarwood, cuero, azafrán'],
                ['fas fa-candy-cane','Gourmand','Vainilla, caramelo, café'],
                ['fas fa-gem','Nicho','Composiciones únicas de autor'],
            ] as [$ico,$nom,$desc])
            <div class="col-6 col-md-3">
                <div class="familia-guide-item">
                    <i class="{{ $ico }} me-1" style="color:#D4AF37;"></i>
                    <strong>{{ $nom }}</strong>
                    <span class="familia-guide-desc small d-block">{{ $desc }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-mobile-stack">
                    <thead>
                        <tr>
                            <th>Icono</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Fragancias</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($familias as $f)
                        <tr>
                            <td data-label="Icono">
                                <div class="familia-icon-preview" style="background:{{ $f->color() }}20;">
                                    <i class="{{ $f->icono ?? 'fas fa-spa' }}" style="color:{{ $f->color() }};"></i>
                                </div>
                            </td>
                            <td data-label="Nombre"><strong style="color:{{ $f->color() }};">{{ $f->nombre }}</strong></td>
                            <td data-label="Descripción"><span class="text-muted small">{{ Str::limit($f->descripcion, 70) }}</span></td>
                            <td data-label="Fragancias"><span class="badge bg-secondary">{{ $f->fragancias_count }}</span></td>
                            <td data-label="Estado">
                                @if($f->activo)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-secondary">Inactiva</span>
                                @endif
                            </td>
                            <td data-label="Acciones">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.familias.edit', $f->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($f->fragancias_count == 0)
                                    <button onclick="eliminarFamilia({{ $f->id }},'{{ $f->nombre }}')"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <button class="btn btn-sm btn-outline-danger" disabled
                                            title="Tiene fragancias asociadas">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay familias. ¡Crea la primera!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form id="fEliminarFam" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection
@section('scripts')
<script>
function eliminarFamilia(id, nombre) {
    confirmarAccion({
        titulo: 'Eliminar familia',
        mensaje: '¿Eliminar la familia "' + nombre + '"?',
        onConfirmar: function () {
            const f = document.getElementById('fEliminarFam');
            f.action = '/admin/familias/' + id;
            f.submit();
        }
    });
}
</script>
@endsection

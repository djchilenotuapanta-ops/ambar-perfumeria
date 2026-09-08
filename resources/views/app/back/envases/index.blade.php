@extends('layouts.template-back')
@section('title', 'Presentaciones de Envases')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header mb-4">
        <div>
            <h3>Presentaciones de Envases</h3>
            <p class="text-muted">Administra las imágenes de envases para cada tamaño y categoría.</p>
        </div>
    </div>

    <div class="admin-card mb-4">
        <div class="admin-card-header"><h5>Cómo usar esta sección</h5></div>
        <div class="admin-card-body">
            <p class="mb-2">Cada tarjeta representa una combinación de tamaño y categoría. Puedes cargar una nueva imagen, reemplazar una existente o eliminarla.</p>
            <p class="mb-0">Los tamaños y categorías se construyen desde la configuración, por lo que se pueden ampliar sin rehacer el diseño.</p>
            <p class="mb-0 mt-2"><strong>Costo del envase:</strong> es el costo del frasco vacío (vidrio, tapa, atomizador, caja) para esa combinación específica de tamaño y categoría — se usa junto con el costo de elaboración de cada fragancia para calcular el margen real.</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h5>Galería de envases</h5></div>
        <div class="admin-card-body">
            <ul class="nav nav-tabs mb-4" role="tablist">
                @foreach($sizes as $size)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $size }}" data-bs-toggle="tab" data-bs-target="#size-{{ $size }}" type="button" role="tab" aria-controls="size-{{ $size }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $size }} ml
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach($sizes as $size)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="size-{{ $size }}" role="tabpanel" aria-labelledby="tab-{{ $size }}">
                    <div class="row g-3">
                        @foreach($categories as $key => $label)
                        @php($presentacion = $presentaciones->get((string) $size)?->firstWhere('categoria', $key))
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <div class="ratio ratio-1x1 mb-3 rounded-4" style="background:#FAF6F0;border:1px solid #E8DDD5;overflow:hidden;">
                                        @if($presentacion?->imagen_url)
                                        <img src="{{ $presentacion->imagen_url }}" alt="{{ $label }} {{ $size }} ml" class="w-100 h-100" style="object-fit:cover;" />
                                        @else
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted px-3">
                                            <i class="fas fa-image fa-2x mb-2"></i>
                                            <div class="fw-600">Sin imagen</div>
                                            <div class="small">{{ $label }} {{ $size }} ml</div>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <div class="fw-600">{{ $label }}</div>
                                        <div class="text-muted small">{{ $size }} ml</div>
                                    </div>

                                    <form action="{{ $presentacion ? route('admin.envases.update', $presentacion->id) : route('admin.envases.store') }}" method="POST" enctype="multipart/form-data" class="mb-2">
                                        @csrf
                                        @if($presentacion)
                                        @method('PATCH')
                                        @endif
                                        <input type="hidden" name="tamano" value="{{ $size }}">
                                        <input type="hidden" name="categoria" value="{{ $key }}">

                                        <label class="form-label small mb-1">Costo del envase vacío (USD)</label>
                                        <input type="number" name="costo" step="0.01" min="0"
                                               class="form-control form-control-sm mb-2"
                                               value="{{ old('costo', $presentacion->costo ?? 0) }}" required>

                                        <label class="form-label small mb-1">Cargar imagen</label>
                                        <input type="file" name="imagen" accept="image/*" class="form-control form-control-sm mb-2">
                                        <button type="submit" class="btn btn-sm btn-gold w-100">{{ $presentacion ? 'Guardar cambios' : 'Agregar envase' }}</button>
                                    </form>

                                    @if($presentacion)
                                    <form action="{{ route('admin.envases.destroy', $presentacion->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar imagen</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const reader = new FileReader();
            const card = this.closest('.card');
            if (!card) return;
            const preview = card.querySelector('.ratio img');

            if (this.files && this.files[0]) {
                reader.onload = function (e) {
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
})();
</script>
@endsection

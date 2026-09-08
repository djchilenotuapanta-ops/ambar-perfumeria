@extends('layouts.template-back')
@section('title', 'Editar Fragancia')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Editar: {{ $fragancia->nombre }}</h3>
            <p class="text-muted mb-0">{{ $fragancia->casa_perfumista }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fragancia.show', $fragancia->slug) }}" target="_blank"
               class="btn btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> Ver en tienda
            </a>
            <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">← Regresar al panel</a>
        </div>
    </div>

    <form action="{{ route('admin.fragancias.update', $fragancia->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('app.back.fragancias._form')
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-save me-1"></i> Actualizar Fragancia
            </button>
            <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

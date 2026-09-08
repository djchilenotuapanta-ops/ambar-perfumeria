@extends('layouts.template-back')
@section('title', 'Nueva Fragancia')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Nueva Fragancia</h3>
            <p class="text-muted mb-0">Añade un nuevo perfume al catálogo</p>
        </div>
        <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">
            ← Regresar al panel
        </a>
    </div>

    <form action="{{ route('admin.fragancias.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('app.back.fragancias._form')
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-save me-1"></i> Guardar Fragancia
            </button>
            <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

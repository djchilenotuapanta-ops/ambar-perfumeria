@extends('layouts.template-back')
@section('title','Editar Familia')
@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <h3>Editar: {{ $familia->nombre }}</h3>
        <a href="{{ route('admin.familias.index') }}" class="btn btn-outline-secondary">← Regresar al panel</a>
    </div>
    <form action="{{ route('admin.familias.update', $familia->id) }}" method="POST">
        @csrf @method('PUT')
        @include('app.back.familias._form')
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-gold">Actualizar</button>
            <a href="{{ route('admin.familias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

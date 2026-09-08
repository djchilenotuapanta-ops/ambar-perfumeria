@extends('layouts.template-back')
@section('title','Nueva Familia')
@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <h3>Nueva Familia Olfativa</h3>
        <a href="{{ route('admin.familias.index') }}" class="btn btn-outline-secondary">← Regresar al panel</a>
    </div>
    <form action="{{ route('admin.familias.store') }}" method="POST">
        @csrf
        @include('app.back.familias._form')
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-gold">Guardar Familia</button>
            <a href="{{ route('admin.familias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

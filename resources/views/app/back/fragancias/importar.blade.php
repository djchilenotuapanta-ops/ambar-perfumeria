@extends('layouts.template-back')
@section('title', 'Carga masiva de fragancias')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Carga masiva de fragancias</h3>
            <p class="text-muted mb-0">Sube varios perfumes de una sola vez desde un archivo Excel/CSV</p>
        </div>
        <a href="{{ route('admin.fragancias.index') }}" class="btn btn-outline-secondary">
            ← Regresar al panel
        </a>
    </div>

    <div class="admin-card mb-3">
        <div class="admin-card-body">
            <h5 class="mb-3"><i class="fas fa-list-ol me-1"></i> Cómo hacerlo</h5>
            <ol class="mb-3">
                <li>Descarga la plantilla de ejemplo (ya trae los encabezados correctos).</li>
                <li>Ábrela en Excel y agrega una fila por cada fragancia que quieras subir.</li>
                <li>Guarda el archivo como <strong>CSV (delimitado por comas)</strong> o <strong>CSV UTF-8</strong>
                    (Archivo → Guardar como → elige el tipo "CSV").</li>
                <li>Sube ese archivo .csv aquí abajo.</li>
            </ol>

            <a href="{{ route('admin.fragancias.importar.plantilla') }}" class="btn btn-outline-primary mb-2">
                <i class="fas fa-download me-1"></i> Descargar plantilla de ejemplo (.csv)
            </a>

            <div class="small text-muted mt-2">
                <strong>Columnas obligatorias:</strong>
                nombre, descripcion, casa_perfumista, genero (mujer/hombre/unisex), familia,
                precio_por_ml, stock_100, stock_50, stock_30.
                <br>
                <strong>Columnas opcionales:</strong>
                notas_salida, notas_corazon, notas_fondo, precio_especial_por_ml, costo_por_ml, activo (si/no).
                <br>
                Si el nombre de la <strong>familia</strong> no existe todavía, se crea automáticamente.
                Los precios de los tamaños (100/50/30 ml) se calculan solos a partir del
                <strong>precio_por_ml</strong>.
                <br>
                Si ya existe una fragancia con el mismo <strong>nombre</strong> y <strong>casa_perfumista</strong>,
                se <strong>actualiza</strong> en vez de duplicarse. Antes de guardar nada, te mostramos una
                previsualización para que confirmes.
            </div>

            <div class="alert alert-warning small mt-3 mb-0">
                <i class="fas fa-triangle-exclamation me-1"></i>
                <strong>Ojo con el precio:</strong> en este archivo <code>precio_por_ml</code> es el precio
                real por mililitro, en decimales (ej.: <code>1.20</code> = $1,20/ml). Es el mismo formato
                que usa el formulario manual de "Crear fragancia": ahí también escribes el precio real por
                ml (ej.: <code>1.20</code>), no el precio del frasco completo. Si pones
                <code>63</code> en esta columna del CSV, el sistema lo tomará como $63 por mililitro.
                La columna opcional <code>costo_por_ml</code> usa el mismo formato (costo real por ml, en
                decimales) y se usa solo como referencia interna de margen; si la dejas vacía, la fragancia
                queda sin costo cargado.
            </div>

            <div class="alert alert-warning small mt-3 mb-0">
                <i class="fas fa-triangle-exclamation me-1"></i>
                <strong>Importante:</strong> al actualizar una fragancia existente, el
                <strong>stock se reemplaza</strong> por el valor que traiga el archivo (no se suma al actual).
                Si vas a reimportar el catálogo, asegúrate de que las columnas
                <code>stock_100</code>, <code>stock_50</code> y <code>stock_30</code> tengan el stock real
                y actualizado, o podrías dejar en 0 productos que sí tenían existencias.
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-body">
            <h5 class="mb-3"><i class="fas fa-upload me-1"></i> Subir archivo</h5>
            <form action="{{ route('admin.fragancias.importar.previsualizar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Archivo CSV</label>
                    <input type="file" name="archivo" accept=".csv,.txt" class="form-control @error('archivo') is-invalid @enderror" required>
                    @error('archivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Tamaño máximo: 5 MB.</div>
                </div>
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-eye me-1"></i> Previsualizar antes de importar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

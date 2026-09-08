@extends('layouts.template-back')
@section('title', 'Reporte de Clientes')

@section('contenido')
<div class="admin-content">
    <div class="admin-page-header">
        <div>
            <h3>Reporte de Clientes</h3>
            <p class="text-muted mb-0">Distribución y comportamiento de la base de clientes</p>
        </div>
        @if(auth()->user()->role === 'admin')
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reportes.ventas') }}" class="btn btn-outline-secondary btn-sm">Ver Ventas</a>
            <a href="{{ route('admin.reportes.productos') }}" class="btn btn-outline-secondary btn-sm">Ver Productos</a>
            <a href="{{ route('admin.reportes.clientes.pdf') }}" class="btn btn-outline-dark btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
            </a>
        </div>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#8B6F5E20;color:#8B6F5E;"><i class="fas fa-users"></i></div>
                <div class="kpi-valor">{{ $totalClientes }}</div>
                <div class="kpi-label">Clientes registrados</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#8A6D1F20;color:#8A6D1F;"><i class="fas fa-user-plus"></i></div>
                <div class="kpi-valor">{{ $clientesNuevos }}</div>
                <div class="kpi-label">Nuevos en los últimos 30 días</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#2C181020;color:#2C1810;"><i class="fas fa-shopping-bag"></i></div>
                <div class="kpi-valor">{{ $clientesConCompras }}</div>
                <div class="kpi-label">Clientes con compras</div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-12">
            <div class="admin-card">
                <div class="admin-card-header"><h5>🏆 Mejores Clientes (gasto histórico)</h5></div>
                <div class="admin-card-body">
                    @if($mejoresClientes->count())
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Cliente</th><th>Correo</th><th>Pedidos</th><th>Total gastado</th></tr></thead>
                        <tbody>
                            @foreach($mejoresClientes as $cliente)
                            <tr>
                                <td>{{ $cliente->name }}</td>
                                <td>{{ $cliente->email }}</td>
                                <td>{{ $cliente->pedidos_count }}</td>
                                <td>${{ number_format((float) ($cliente->total_gastado ?? 0), 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted text-center py-3 mb-0">Aún no hay clientes con compras registradas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

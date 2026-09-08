@extends('layouts.template-back')
@section('title', 'Panel Principal')

@section('contenido')
<div class="admin-content">

    <div class="row g-3 mb-4">
        @php
        $kpis = [
            ['icon'=>'fas fa-spray-can',    'valor'=>$stats['total_fragancias'], 'label'=>'Fragancias',       'color'=>'#8A6D1F'],
            ['icon'=>'fas fa-box',           'valor'=>$stats['total_pedidos'],    'label'=>'Pedidos totales',  'color'=>'#C4847A'],
            ['icon'=>'fas fa-chart-line',    'valor'=>'$'.number_format($stats['ventas_mes'],0,',','.'), 'label'=>'Ventas del mes', 'color'=>'#2C1810'],
            ['icon'=>'fas fa-users',         'valor'=>$stats['total_usuarios'],   'label'=>'Clientes',         'color'=>'#8B6F5E'],
            ['icon'=>'fas fa-calendar-day',  'valor'=>$stats['pedidos_hoy'],      'label'=>'Pedidos hoy',      'color'=>'#4A2520'],
            ['icon'=>'fas fa-exclamation-triangle','valor'=>$stats['stock_bajo'], 'label'=>'Stock bajo',       'color'=>'#dc3545'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col-6 col-md-4 col-xl-2">
            @if($kpi['label'] === 'Stock bajo')
            <a href="{{ route('admin.fragancias.index', ['urgente' => 1]) }}" class="d-block text-decoration-none">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:{{ $kpi['color'] }}20;color:{{ $kpi['color'] }};">
                        <i class="{{ $kpi['icon'] }}"></i>
                    </div>
                    <div class="kpi-valor">{{ $kpi['valor'] }}</div>
                    <div class="kpi-label">{{ $kpi['label'] }}</div>
                </div>
            </a>
            @else
            <div class="kpi-card">
                <div class="kpi-icon" style="background:{{ $kpi['color'] }}20;color:{{ $kpi['color'] }};">
                    <i class="{{ $kpi['icon'] }}"></i>
                </div>
                <div class="kpi-valor">{{ $kpi['valor'] }}</div>
                <div class="kpi-label">{{ $kpi['label'] }}</div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5>Pedidos Recientes</h5>
                    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                </div>
                <div class="admin-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-mobile-stack">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pedidos_recientes as $p)
                                <tr>
                                    <td data-label="Pedido"><strong>{{ $p->numero_pedido }}</strong></td>
                                    <td data-label="Cliente">{{ $p->user->name }}</td>
                                    <td data-label="Total">${{ number_format($p->total,0,',','.') }}</td>
                                    <td data-label="Estado"><x-pedido-estado-badge :estado="$p->estado" /></td>
                                    <td data-label="Acciones">
                                        <a href="{{ route('admin.pedidos.show', $p->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No hay pedidos aún.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5>Acciones Rápidas</h5></div>
                <div class="admin-card-body p-0">
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.fragancias.create') }}" class="quick-action">
                        <i class="fas fa-plus-circle"></i> Nueva Fragancia
                    </a>
                    <a href="{{ route('admin.familias.create') }}" class="quick-action">
                        <i class="fas fa-spa"></i> Nueva Familia Olfativa
                    </a>
                    <a href="{{ route('admin.pedidos.index') }}" class="quick-action">
                        <i class="fas fa-box"></i> Gestionar Pedidos
                    </a>
                    <a href="{{ route('admin.usuarios.index') }}" class="quick-action">
                        <i class="fas fa-users"></i> Ver Usuarios
                    </a>
                    @endif
                    @php
                        $destinoReportes = 'admin.reportes.ventas';
                    @endphp
                    @if($destinoReportes)
                    <a href="{{ route($destinoReportes) }}" class="quick-action">
                        <i class="fas fa-chart-bar"></i> Ver Reportes
                    </a>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header"><h5>{{ $tituloNuevas }}</h5></div>
                <div class="admin-card-body p-0">
                    @forelse($fragancias_top as $f)
                    <div class="d-flex align-items-center gap-2 p-2 border-bottom">
                        <div style="width:36px;height:36px;background:#FAF6F0;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-spray-can" style="color:#D4AF37;font-size:.9rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-size:.85rem;font-weight:600;color:#2C1810;">{{ $f->nombre }}</div>
                            <div style="font-size:.75rem;color:#8B6F5E;">Stock: {{ $f->stockTotal() }}</div>
                        </div>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.fragancias.edit', $f->id) }}" class="btn btn-xs">
                            <i class="fas fa-edit text-muted"></i>
                        </a>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted p-3 mb-0 small">No hay fragancias destacadas.</p>
                    @endforelse
                </div>
            </div>
            <div class="admin-card mt-3">
                <div class="admin-card-header"><h5>Stock crítico / Urgente</h5></div>
                <div class="admin-card-body p-0">
                    @if(isset($stockCritico) && $stockCritico->count())
                        @foreach($stockCritico as $s)
                        <a href="{{ route('admin.fragancias.index', ['urgente' => 1, 'buscar' => $s->fragancia->nombre]) }}"
                           class="d-flex align-items-center gap-2 p-2 border-bottom text-decoration-none text-reset">
                            <div style="width:36px;height:36px;background:#FFF5F5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-exclamation-triangle" style="color:#dc3545;font-size:.9rem;"></i>
                            </div>
                            <div class="flex-grow-1 small">
                                <div style="font-weight:600;color:#2C1810;">{{ $s->fragancia->nombre }} <small class="text-muted">({{ $s->tamano }})</small></div>
                                <div class="text-muted">Stock: <strong class="text-danger">{{ $s->stock }}</strong></div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">Ver inventario</span>
                            </div>
                        </a>
                        @endforeach
                        <div class="p-2 text-center">
                            <a href="{{ route('admin.fragancias.index', ['urgente' => 1]) }}" class="small">Ver todo el inventario urgente</a>
                        </div>
                    @else
                        <p class="text-muted p-3 mb-0 small">No hay artículos en estado urgente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

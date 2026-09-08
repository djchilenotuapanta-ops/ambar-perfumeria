<div class="sidebar">

    <a href="{{ route('admin.dashboard') }}"
       class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-chart-line me-2"></i> Panel Principal
    </a>

    @if(auth()->user()->role === 'admin')
    <a href="#" class="submenu-toggle {{ request()->routeIs('admin.fragancias.*','admin.familias.*') ? 'active' : '' }}">
        <span><i class="fas fa-spray-can me-2"></i>Catálogo</span>
        <i class="fas fa-chevron-down"></i>
    </a>
    <div class="submenu {{ request()->routeIs('admin.fragancias.*','admin.familias.*') ? 'show' : '' }}">
        <a href="{{ route('admin.fragancias.index') }}"
           class="{{ request()->routeIs('admin.fragancias.*') ? 'active' : '' }}">
            Fragancias
        </a>
        <a href="{{ route('admin.familias.index') }}"
           class="{{ request()->routeIs('admin.familias.*') ? 'active' : '' }}">
            Familias Olfativas
        </a>
        <a href="{{ route('admin.envases.index') }}"
           class="{{ request()->routeIs('admin.envases.*') ? 'active' : '' }}">
            Envases
        </a>
    </div>
    @endif

    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.pedidos.index') }}"
       class="sidebar-link {{ request()->routeIs('admin.pedidos.*') ? 'active' : '' }}">
        <i class="fas fa-box me-2"></i> Pedidos
    </a>
    @endif

    @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.usuarios.index') }}"
       class="sidebar-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
        <i class="fas fa-users me-2"></i> Usuarios
    </a>
    @endif

     @if(auth()->user()->role === 'admin')
    <a href="#" class="submenu-toggle {{ request()->routeIs('admin.reportes.*') ? 'active' : '' }}">
        <span><i class="fas fa-chart-bar me-2"></i>Reportes</span>
        <i class="fas fa-chevron-down"></i>
    </a>
    <div class="submenu {{ request()->routeIs('admin.reportes.*') ? 'show' : '' }}">
        <a href="{{ route('admin.reportes.ventas') }}"
           class="{{ request()->routeIs('admin.reportes.ventas') ? 'active' : '' }}">Ventas</a>
        <a href="{{ route('admin.reportes.productos') }}"
           class="{{ request()->routeIs('admin.reportes.productos') ? 'active' : '' }}">Productos</a>
        <a href="{{ route('admin.reportes.clientes') }}"
           class="{{ request()->routeIs('admin.reportes.clientes') ? 'active' : '' }}">Clientes</a>
    </div>
    @endif

     @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.notificaciones.index') }}"
       class="sidebar-link {{ request()->routeIs('admin.notificaciones.*') ? 'active' : '' }}">
        <i class="fas fa-bell me-2"></i> Notificaciones
        @php $noLeidasAdmin = auth()->user()->unreadNotifications()->count() @endphp
        @if($noLeidasAdmin > 0)
            <span class="badge bg-danger ms-1">{{ $noLeidasAdmin > 9 ? '9+' : $noLeidasAdmin }}</span>
        @endif
    </a>
    @endif

     @if(auth()->user()->role === 'admin')
    <a href="{{ route('admin.configuracion.index') }}"
       class="sidebar-link {{ request()->routeIs('admin.configuracion.*') ? 'active' : '' }}">
        <i class="fas fa-sliders-h me-2"></i> Configuración
    </a>
    @endif

    <div class="sidebar-footer">
        <small>© {{ date('Y') }} Ambar</small>
    </div>
</div>

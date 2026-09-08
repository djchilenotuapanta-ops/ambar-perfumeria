<div class="topbar">
    <div class="topbar-brand">
        <i class="fas fa-gem me-2"></i>
        <strong>Ambar</strong>
        @php
            $rolesEtiqueta = [
                'admin'      => 'Administrador',
                'cliente'    => 'Cliente',
            ];
            $rolLabel = $rolesEtiqueta[auth()->user()->role ?? ''] ?? ucfirst(auth()->user()->role ?? '');
        @endphp
        <span class="topbar-role ms-2">— {{ $rolLabel }}</span>
    </div>
    <div class="topbar-actions">
        <a href="{{ route('index') }}" class="circle-btn" title="Ver tienda" target="_blank">
            <i class="fas fa-store"></i>
        </a>
        <x-notificaciones-campanita prefix="admin." />
        <span class="topbar-user">{{ auth()->user()->name ?? '' }}</span>
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="circle-btn" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</div>

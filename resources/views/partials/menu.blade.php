<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('index') }}">
            <span class="brand-main">AMBAR</span>
            <span class="brand-sub">PARFUMS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('index') ? 'active' : '' }}"
                       href="{{ route('index') }}">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalogo') ? 'active' : '' }}"
                       href="{{ route('catalogo') }}">Catálogo</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        Colecciones
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('catalogo', ['genero'=>'mujer']) }}">
                            🌸 Para Mujer</a></li>
                        <li><a class="dropdown-item" href="{{ route('catalogo', ['genero'=>'hombre']) }}">
                            🌲 Para Hombre</a></li>
                        <li><a class="dropdown-item" href="{{ route('catalogo', ['genero'=>'unisex']) }}">
                            ✦ Unisex</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('regalo') ? 'active' : '' }}"
                       href="{{ route('regalo') }}">🎁 Regalos</a>
                </li>
            </ul>
            <!-- Búsqueda rápida -->
            <form class="d-flex" action="{{ route('catalogo') }}" method="GET">
                <input class="form-control me-2 search-input" type="search" name="buscar"
                       placeholder="Buscar fragancias..." value="{{ request('buscar') }}">
                <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</nav>

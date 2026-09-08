@php
    // Mapea cada estado de pedido a su color y etiqueta visible.
    // Esta es lógica puramente de presentación: por eso vive en la vista
    // (componente Blade) y no en el modelo Pedido.
    $mapa = [
        'pendiente'  => ['clase' => 'bg-warning text-dark', 'texto' => 'Pendiente'],
        'entregado'  => ['clase' => 'bg-success',           'texto' => 'Entregado'],
        'cancelado'  => ['clase' => 'bg-danger',            'texto' => 'Cancelado'],
    ];

    $info = $mapa[$estado] ?? null;
@endphp

@if ($info)
    <span class="badge {{ $info['clase'] }}">{{ $info['texto'] }}</span>
@else
    {{ $estado }}
@endif

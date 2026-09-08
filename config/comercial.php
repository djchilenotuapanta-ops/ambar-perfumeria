<?php

return [
    'envio_gratis_desde' => (float) env('ENVIO_GRATIS_DESDE', 80),
    'costo_envio_nacional' => (float) env('COSTO_ENVIO_NACIONAL', 5),
    // Lo que la tienda paga de verdad al courier por cada pedido despachado
    // (puede diferir de lo que se cobra al cliente, ej. cuando el envío es
    // "gratis" por superar el monto mínimo, el gasto real sigue existiendo).
    // Se usa SOLO para descontarlo de la ganancia en los reportes, nunca
    // se cobra al cliente.
    'costo_envio_real' => (float) env('COSTO_ENVIO_REAL', 5),

    // Si el cliente (no el admin) ve avisos de "¡solo quedan X!" / "agotado"
    // en la tienda. Por defecto apagado: mostrar cuánto stock queda puede
    // filtrar información del negocio a la competencia. El umbral de stock
    // bajo (arriba) se sigue usando igual para las alertas internas del
    // equipo (panel admin + correo), esto solo controla lo que ve el cliente.
    'mostrar_stock_bajo_cliente' => (bool) env('MOSTRAR_STOCK_BAJO_CLIENTE', false),
    'stock_bajo_umbral' => (int) env('STOCK_BAJO_UMBRAL', 5),

    // Configuración del IVA (impuesto al valor agregado).
    // `porcentaje` puede cambiarse desde el archivo .env con la clave `IVA_PORCENTAJE`.
    // `incluido_en_precio` indica si los precios ya incluyen IVA.
    'iva' => [
        'porcentaje' => (float) env('IVA_PORCENTAJE', 15),
        // En Ecuador el precio que ve el cliente normalmente ya trae el IVA
        // incluido, por eso el default es true (el admin lo puede apagar
        // desde Admin > Configuración si su caso es distinto).
        'incluido_en_precio' => (bool) env('IVA_INCLUIDO', true),
    ],
    // La cantidad de "Más Vendidas" es fija (3) y no es configurable desde el panel admin.
    'top_ventas' => [
        'cantidad' => 3,
        'titulo'   => env('TOP_VENTAS_TITULO', 'Las Más Vendidas'),
    ],

    'nuevas_adquisiciones' => [
        'cantidad' => (int) env('NUEVAS_ADQUISICIONES_CANTIDAD', 5),
        'titulo'   => env('NUEVAS_ADQUISICIONES_TITULO', 'Nuevas Adquisiciones'),
    ],

    'tamanos' => [
        100 => ['recargo' => 0],
        50  => ['recargo' => 0.15],
        30  => ['recargo' => 0.30],
    ],

    // Rangos usados por el formulario de fragancias para "sugerir precio por ml"
    // según si la familia es premium o normal.
    'sugerencia_precio' => [
        'normal'  => ['min' => 0.22, 'max' => 0.68],
        'premium' => ['min' => 0.55, 'max' => 1.45],
    ],

    // Margen de ganancia objetivo usado para calcular el "precio recomendado"
    // a partir del costo (elaboración + envase). Estos son solo los valores
    // por defecto / semilla; el admin los edita de verdad desde el panel
    // Admin > Configuración (tabla `configuraciones`, igual que el recargo
    // por tamaño), sin tocar el .env.
    'margen_objetivo' => [
        // 'porcentaje' = ganancia como % sobre el precio de venta (costo / (1 - margen)).
        // 'valor_fijo' = monto fijo en USD que se suma al costo del frasco de 100 ml.
        'modo'         => env('MARGEN_MODO', 'porcentaje'),
        'porcentaje'   => (float) env('MARGEN_PORCENTAJE', 50), // 50%
        'valor_fijo'   => (float) env('MARGEN_VALOR_FIJO', 8),  // USD sobre el frasco de 100 ml
        // A qué múltiplo se redondea el precio final sugerido (frasco de
        // 100 ml), para que salga en un "valor cerrado" (ej. $35, $40)
        // en vez de $34.83. 1 = dólar entero.
        'redondeo'     => (float) env('MARGEN_REDONDEO', 5),
    ],

    'envases' => [
        'categorias' => [
            'hombre' => 'Hombre',
            'mujer'   => 'Mujer',
            'unisex'  => 'Unisex',
        ],
    ],

    'social' => [
        'instagram' => env('SOCIAL_INSTAGRAM', 'https://www.instagram.com/'),
        'facebook' => env('SOCIAL_FACEBOOK', 'https://www.facebook.com/'),
        'tiktok' => env('SOCIAL_TIKTOK', 'https://www.tiktok.com/'),
    ],

    'cuenta_bancaria' => [
        'banco'       => env('BANCO_NOMBRE', 'Banco Pichincha'),
        'tipo'        => env('BANCO_TIPO_CUENTA', 'Cuenta Corriente'),
        'numero'      => env('BANCO_NUMERO_CUENTA', '2201234567'),
        'titular'     => env('BANCO_TITULAR', 'Ambar Perfumería S.A.S.'),
        'ruc'         => env('BANCO_RUC', '1792345678001'),
        'email_comprobante' => env('BANCO_EMAIL_COMPROBANTE', 'perfumes30060@gmail.com'),
    ],

    'provincias' => [
        'Azuay', 'Bolívar', 'Cañar', 'Carchi', 'Chimborazo', 'Cotopaxi',
        'El Oro', 'Esmeraldas', 'Galápagos', 'Guayas', 'Imbabura', 'Loja',
        'Los Ríos', 'Manabí', 'Morona Santiago', 'Napo', 'Orellana',
        'Pastaza', 'Pichincha', 'Santa Elena', 'Santo Domingo de los Tsáchilas',
        'Sucumbíos', 'Tungurahua', 'Zamora Chinchipe',
    ],
];

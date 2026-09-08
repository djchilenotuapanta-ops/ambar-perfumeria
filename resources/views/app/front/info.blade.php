@extends('layouts.template')
@section('title', $titulo)
@section('meta_description', $descripcionSeo ?? $titulo)

@section('contenido')
<div class="container py-5" style="max-width:820px;">
    <h2 class="mb-4" style="color:#3B2A20;">{{ $titulo }}</h2>

    @if($vista === 'envios')
        <p>
            Realizamos envíos a nivel nacional dentro de Ecuador. El costo y tiempo de entrega
            varían según tu ubicación.
        </p>
        <ul>
            <li>
                Envío gratuito en compras desde
                ${{ number_format((float) \App\Models\Configuracion::obtener('envio_gratis_desde', config('comercial.envio_gratis_desde', 80)), 0, '.', ',') }} USD.
            </li>
            <li>
                Por debajo de ese monto, el costo de envío es de
                ${{ number_format((float) \App\Models\Configuracion::obtener('costo_envio_nacional', config('comercial.costo_envio_nacional', 5)), 0, '.', ',') }} USD.
            </li>
            <li>Tiempo estimado de entrega: 2 a 5 días hábiles según la provincia.</li>
            <li>Recibirás una notificación cuando tu pedido cambie de estado.</li>
        </ul>

    @elseif($vista === 'devoluciones')
        <p>
            Si tu fragancia llega en mal estado o no corresponde a lo que pediste, contáctanos
            dentro de las 48 horas posteriores a la entrega a través de
            <a href="mailto:perfumes30060@gmail.com">perfumes30060@gmail.com</a> o por WhatsApp,
            adjuntando fotos del producto y tu número de pedido.
        </p>
        <p>
            Por tratarse de productos de perfumería, solo se aceptan devoluciones de frascos
            sellados y sin uso. Una vez validado el caso, coordinamos el cambio o la devolución
            del pago según corresponda.
        </p>

    @elseif($vista === 'garantia')
        <p>
            Todas las fragancias de nuestro catálogo son 100% originales, adquiridas directamente
            con las casas perfumistas o distribuidores autorizados. No comercializamos réplicas,
            imitaciones ni productos «tester» sin su empaque original.
        </p>
        <p>
            Si tienes dudas sobre la autenticidad de un producto recibido, escríbenos con tu
            número de pedido y con gusto lo revisamos contigo.
        </p>

    @elseif($vista === 'contacto')
        <p>Estamos para ayudarte con cualquier consulta sobre tu pedido o nuestras fragancias.</p>
        <ul class="list-unstyled">
            <li class="mb-2">
                <i class="fas fa-envelope me-2"></i>
                <a href="mailto:perfumes30060@gmail.com">perfumes30060@gmail.com</a>
            </li>
            <li class="mb-2">
                <i class="fab fa-whatsapp me-2"></i>
                <a href="https://api.whatsapp.com/send?phone=593959787097&text=Hola%2C%20quisiera%20informacion%20sobre%20sus%20fragancias" target="_blank" rel="noopener noreferrer">
                    +593 95 978 7097
                </a>
            </li>
            <li><i class="fas fa-map-marker-alt me-2"></i>Ecuador</li>
        </ul>
    @endif

    <a href="{{ route('catalogo') }}" class="btn btn-outline-secondary mt-3">← Volver al catálogo</a>
</div>
@endsection

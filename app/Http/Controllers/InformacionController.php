<?php

namespace App\Http\Controllers;

class InformacionController extends Controller
{
    /**
     * Páginas informativas estáticas enlazadas desde el footer
     * (envíos, devoluciones, garantía, contacto). No dependen de la
     * base de datos, así que no necesitan su propio controlador por página.
     */
    public function envios()
    {
        return view('app.front.info', [
            'titulo' => 'Política de Envíos',
            'vista'  => 'envios',
            'descripcionSeo' => 'Conoce nuestra política de envíos: costos, tiempos de entrega y envío gratis desde cierto monto de compra en Ambar Parfums.',
        ]);
    }

    public function devoluciones()
    {
        return view('app.front.info', [
            'titulo' => 'Devoluciones',
            'vista'  => 'devoluciones',
            'descripcionSeo' => 'Información sobre cómo proceder si tu pedido llega dañado o incorrecto. Cambios y devoluciones en Ambar Parfums.',
        ]);
    }

    public function garantia()
    {
        return view('app.front.info', [
            'titulo' => 'Garantía de Autenticidad',
            'vista'  => 'garantia',
            'descripcionSeo' => 'Todas nuestras fragancias son 100% originales, garantizadas por las casas perfumistas. Conoce nuestra garantía de autenticidad.',
        ]);
    }

    public function contacto()
    {
        return view('app.front.info', [
            'titulo' => 'Contáctenos',
            'vista'  => 'contacto',
            'descripcionSeo' => '¿Tienes dudas sobre tu pedido o nuestros productos? Contáctanos y te ayudamos con gusto.',
        ]);
    }
}

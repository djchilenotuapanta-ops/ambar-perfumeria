<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\Fragancia;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * robots.txt dinámico: usa siempre la URL real configurada en APP_URL
     * (config/app.php), en vez de un archivo estático con un dominio
     * hardcodeado que quedaría desactualizado o incorrecto al desplegar
     * en un dominio distinto al de desarrollo.
     */
    public function robots(): Response
    {
        $lineas = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /mi-cuenta',
            'Disallow: /mis-pedidos',
            'Disallow: /carrito',
            'Disallow: /checkout',
            'Disallow: /pedido/',
            'Disallow: /perfil',
            'Disallow: /notificaciones',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /password',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lineas), 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Sitemap XML dinámico: páginas estáticas + todas las fragancias activas.
     * Cacheado 1 hora para no recalcular en cada visita de un crawler
     * (los buscadores rastrean el sitemap con frecuencia).
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap:xml', 3600, function () {
            $urls = [];

            // Páginas estáticas principales
            foreach ([
                ['loc' => route('index'), 'changefreq' => 'daily', 'priority' => '1.0'],
                ['loc' => route('catalogo'), 'changefreq' => 'daily', 'priority' => '0.9'],
                ['loc' => route('regalo'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ] as $pagina) {
                $urls[] = $pagina;
            }

            // Una entrada por cada familia (categoría)
            foreach (Familia::where('activo', true)->get() as $familia) {
                $urls[] = [
                    'loc' => route('familia.show', $familia->id),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }

            // Una entrada por cada fragancia activa
            Fragancia::where('activo', true)->select('slug', 'updated_at')
                ->orderByDesc('updated_at')
                ->chunk(200, function ($fragancias) use (&$urls) {
                    foreach ($fragancias as $f) {
                        $urls[] = [
                            'loc' => route('fragancia.show', $f->slug),
                            'lastmod' => $f->updated_at->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority' => '0.8',
                        ];
                    }
                });

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($urls as $u) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
                if (!empty($u['lastmod'])) {
                    $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
                }
                $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
                $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
                $xml .= "  </url>\n";
            }
            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}

@extends('layouts.template')
@section('title', 'Configurar Regalo')
@section('meta_description', 'Arma tu regalo perfecto: elige tu fragancia favorita y personaliza la presentación en caja blanca o negra. Envío a nivel nacional en Ecuador.')

@section('contenido')
<div class="container py-5">
    <div class="text-center mb-5">
        <p class="eyebrow">Para momentos especiales</p>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;color:#2C1810;">
            Configurador de Regalos
        </h1>
        <div style="width:60px;height:2px;background:#D4AF37;margin:1rem auto 0;"></div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <div x-data="regalo()" class="row g-4">

        <div class="col-lg-7">
            <form id="formRegalo" action="{{ route('regalo.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="ocasion" x-model="g.ocasion">
                <input type="hidden" name="presentacion" x-model="g.presentacion">

            <div class="config-section mb-3">
                <h5 class="config-step-title"><span class="config-num">1</span> Ocasión</h5>
                <div class="row g-2">
                    @foreach(['Cumpleaños','Aniversario','San Valentín','Navidad','Graduación','Sin ocasión'] as $oc)
                    <div class="col-6 col-md-4">
                        <button type="button" class="config-btn w-100" :class="{'active': g.ocasion==='{{ $oc }}'}"
                                @click="g.ocasion='{{ $oc }}'">{{ $oc }}</button>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="config-section mb-3">
                <h5 class="config-step-title"><span class="config-num">2</span> Presentación</h5>

                @foreach([
                    ['blanco','Bolsa de regalo blanca','Funda Ámbar en tono marfil con detalles dorados','Gratis','blanco'],
                    ['negro','Bolsa de regalo negra','Funda Ámbar en negro mate con detalles dorados','Gratis','negro'],
                ] as [$val,$nom,$desc,$precio,$imagen])
                <button type="button" class="config-btn-row w-100 mb-2" :class="{'active': g.presentacion==='{{ $val }}'}"
                        @click="g.presentacion='{{ $val }}'">
                    <div class="config-pack config-pack-{{ $val }}">
                        <img src="{{ file_exists(public_path('assets/images/regalo/'.$imagen.'.png')) ? asset('assets/images/regalo/'.$imagen.'.png') : asset('assets/images/regalo/'.$imagen.'.svg') }}"
                             alt="{{ $nom }}" class="config-pack-img">
                    </div>
                    <div class="text-start flex-grow-1">
                        <div class="fw-600" style="font-size:.9rem;">{{ $nom }}</div>
                        <div style="font-size:.78rem;color:#8B6F5E;">{{ $desc }}</div>
                    </div>
                    <span class="config-precio" :class="{'active': g.presentacion==='{{ $val }}'}">{{ $precio }}</span>
                </button>
                @endforeach
            </div>

            <div class="config-section">
                <h5 class="config-step-title"><span class="config-num">3</span> Mensaje para Tarjeta de Regalo</h5>
                <textarea name="mensaje" x-model="g.mensaje" maxlength="200" rows="3"
                          class="form-control" placeholder="Ej: Para ti, que llevas el aroma de mi vida..."></textarea>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted"><span x-text="(g.mensaje||'').length"></span>/200</small>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="anonimo" value="1" x-model="g.anonimo" id="anonimo">
                        <label class="form-check-label small" for="anonimo">Envío anónimo</label>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Este mensaje se imprime en la tarjeta del regalo.</small>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-gold">
                    Guardar
                </button>
            </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div style="background:linear-gradient(135deg,#2C1810,#4A2520);border-radius:20px;padding:2rem;color:#FAF6F0;position:sticky;top:2rem;">
                <div style="font-size:.68rem;letter-spacing:4px;text-transform:uppercase;color:#D4AF37;margin-bottom:1.5rem;text-align:center;">
                    PREVIEW DE TU REGALO
                </div>
                <div class="gift-mockup-wrap" :class="g.presentacion || 'blanco'">
                    <div class="gift-mockup" :class="g.presentacion || 'blanco'">
                        <div class="gift-lid"></div>
                        <div class="gift-base"></div>
                        <div class="gift-ribbon-vertical"></div>
                        <div class="gift-ribbon-horizontal"></div>
                        <div class="gift-bow">
                            <span></span>
                            <span></span>
                            <i></i>
                        </div>
                        <div class="gift-card">REGALO</div>
                    </div>
                </div>
                <div style="font-size:1.1rem;color:#D4AF37;font-family:'Cormorant Garamond',serif;text-align:center;margin-bottom:.5rem;" x-text="g.ocasion || 'Elige la ocasión'"></div>
                <div x-show="g.mensaje" style="background:rgba(255,255,255,.08);border-radius:10px;padding:.75rem;margin:1rem 0;font-size:.82rem;color:#E8DDD5;font-style:italic;line-height:1.6;">
                    "<span x-text="g.mensaje"></span>"
                    <div x-show="!g.anonimo" style="margin-top:.4rem;font-style:normal;font-size:.75rem;color:#C4A882;">— Con cariño</div>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,.15);padding-top:1rem;margin-top:1rem;font-size:.82rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color:#C4A882;">Presentación</span>
                        <span x-text="{'blanco':'Bolsa de regalo blanca (Gratis)','negro':'Bolsa de regalo negra (Gratis)'}[g.presentacion] || '—'"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:#C4A882;">Mensaje</span>
                        <span x-text="g.mensaje ? '✅ Incluido' : '—'"></span>
                    </div>
                </div>
                <p style="font-size:.72rem;color:#8B6F5E;text-align:center;margin-top:.75rem;">
                    Guarda tus cambios para volver al checkout con tu regalo configurado.
                </p>
            </div>

            <div style="background:#F0FFF4;border:1px solid #A8D5B5;border-radius:12px;padding:1rem;margin-top:1rem;text-align:center;">
                <p style="color:#2D6A4F;font-size:.82rem;margin:0;">
                    🎁 ¿Necesitas ayuda?
                    <a href="https://api.whatsapp.com/send?phone=593959787097&text=Hola%2C%20quiero%20ayuda%20para%20configurar%20un%20regalo" target="_blank" rel="noopener noreferrer" style="color:#25D366;font-weight:700;">
                        Escríbenos por WhatsApp
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function regalo() {
    const storageKey = 'giftFormState';
    const initial = {
        ocasion: @json($regalo['ocasion'] ?? ''),
        presentacion: @json($regalo['presentacion'] ?? 'blanco'),
        mensaje: @json($regalo['mensaje'] ?? ''),
        anonimo: @json((bool) ($regalo['anonimo'] ?? false)),
    };

    function loadState() {
        try {
            const saved = sessionStorage.getItem(storageKey);
            if (!saved) {
                return initial;
            }
            const data = JSON.parse(saved);
            return {
                ocasion: data.ocasion ?? initial.ocasion,
                presentacion: data.presentacion ?? initial.presentacion,
                mensaje: data.mensaje ?? initial.mensaje,
                anonimo: data.anonimo ?? initial.anonimo,
            };
        } catch (error) {
            console.warn('Error al cargar el estado del regalo:', error);
            return initial;
        }
    }

    function saveState(state) {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify(state));
        } catch (error) {
            console.warn('Error al guardar el estado del regalo:', error);
        }
    }

    return {
        g: loadState(),

        init() {
            this.$watch('g', (value) => {
                saveState(value);
            }, { deep: true });
        },

        actualizarRegalo() {
            saveState(this.g);
        },

        guardarYSalir() {
            saveState(this.g);
        },
    };
}
</script>
@section('styles')
<style>
.config-section { background:#fff; border:1px solid #E8DDD5; border-radius:16px; padding:1.5rem; }
.config-step-title { font-size:1rem; color:#2C1810; font-weight:600; margin-bottom:1rem; display:flex; align-items:center; gap:.6rem; }
.config-num { background:#D4AF37; color:#2C1810; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
.config-btn { padding:.5rem; border:1.5px solid #E8DDD5; border-radius:8px; background:#fff; font-size:.82rem; cursor:pointer; transition:.15s; }
.config-btn:hover, .config-btn.active { border-color:#D4AF37; background:#FAF6F0; font-weight:600; color:#2C1810; }
.config-btn-row { display:flex; align-items:center; gap:1rem; padding:.875rem 1rem; border:1.5px solid #E8DDD5; border-radius:10px; background:#fff; cursor:pointer; transition:.15s; text-align:left; }
.config-btn-row:hover, .config-btn-row.active { border-color:#D4AF37; background:#FAF6F0; }
.config-pack {
    width:72px;
    height:72px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    position:relative;
    overflow:hidden;
    border:1px solid rgba(44,24,16,.08);
    background:#FAF6F0;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.5);
}
.config-pack-img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}
.config-pack-blanco { background:linear-gradient(135deg,#FBF8F2,#F0E4D3); }
.config-pack-negro {
    background:linear-gradient(135deg,#2C1810,#4A2520);
    border-color:rgba(212,175,55,.35);
}
.config-precio { font-size:.78rem; font-weight:600; padding:.25rem .6rem; border-radius:99px; background:#F5EDE3; color:#8B6F5E; white-space:nowrap; }
.config-precio.active { background:#D4AF37; color:#2C1810; }

.gift-mockup-wrap {
    width:min(100%, 310px);
    margin:0 auto 1.5rem;
    padding:1rem 0 .75rem;
    perspective:1000px;
}
.gift-mockup {
    position:relative;
    width:190px;
    height:170px;
    margin:0 auto;
    transform-style:preserve-3d;
    filter: drop-shadow(0 18px 24px rgba(0,0,0,.32));
}
.gift-mockup::before {
    content:'';
    position:absolute;
    inset:auto 18px -8px 18px;
    height:18px;
    border-radius:50%;
    background:rgba(0,0,0,.35);
    filter:blur(10px);
}
.gift-lid,
.gift-base {
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    border-radius:16px;
    overflow:hidden;
}
.gift-lid {
    top:8px;
    width:162px;
    height:50px;
    transform:translateX(-50%) perspective(600px) rotateX(16deg);
    border:1px solid rgba(255,255,255,.18);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.28);
}
.gift-base {
    bottom:0;
    width:176px;
    height:98px;
    border:1px solid rgba(255,255,255,.12);
}
.gift-ribbon-vertical,
.gift-ribbon-horizontal {
    position:absolute;
    background:linear-gradient(180deg,rgba(255,255,255,.22),rgba(0,0,0,.04));
}
.gift-ribbon-vertical {
    left:50%;
    top:5px;
    width:18px;
    height:145px;
    transform:translateX(-50%);
    border-radius:10px;
}
.gift-ribbon-horizontal {
    left:50%;
    top:64px;
    width:182px;
    height:18px;
    transform:translateX(-50%);
    border-radius:10px;
}
.gift-bow {
    position:absolute;
    left:50%;
    top:-4px;
    width:62px;
    height:42px;
    transform:translateX(-50%);
}
.gift-bow span,
.gift-bow i {
    position:absolute;
    display:block;
}
.gift-bow span {
    top:5px;
    width:30px;
    height:22px;
    border-radius:70% 30% 70% 30%;
    border:1px solid rgba(255,255,255,.12);
    background:inherit;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.22);
}
.gift-bow span:first-child { left:2px; transform:rotate(-18deg); }
.gift-bow span:nth-child(2) { right:2px; transform:scaleX(-1) rotate(-18deg); }
.gift-bow i {
    left:50%;
    top:12px;
    width:14px;
    height:14px;
    transform:translateX(-50%);
    border-radius:50%;
}
.gift-card {
    position:absolute;
    right:12px;
    bottom:12px;
    padding:.28rem .55rem;
    border-radius:999px;
    font-size:.62rem;
    letter-spacing:2px;
    font-weight:700;
    text-transform:uppercase;
    background:rgba(250,246,240,.88);
    color:#2C1810;
    box-shadow:0 5px 14px rgba(0,0,0,.14);
}

.gift-mockup.blanco .gift-lid,
.gift-mockup.blanco .gift-base,
.gift-mockup.blanco .gift-bow span,
.gift-mockup.blanco .gift-bow i,
.gift-mockup.blanco .gift-ribbon-vertical,
.gift-mockup.blanco .gift-ribbon-horizontal {
    background:linear-gradient(145deg,#fbf8f2,#efe0c4);
}
.gift-mockup.blanco .gift-lid { background:linear-gradient(145deg,#fbf6e9,#ead9b9); }
.gift-mockup.blanco .gift-base { background:linear-gradient(145deg,#f7efe1,#e5d1ac); }
.gift-mockup.blanco .gift-ribbon-vertical,
.gift-mockup.blanco .gift-ribbon-horizontal { background:linear-gradient(180deg,#d7c1a2,#9f7f61); }
.gift-mockup.blanco .gift-bow i { background:#a68868; }

.gift-mockup.negro .gift-lid,
.gift-mockup.negro .gift-base,
.gift-mockup.negro .gift-bow span,
.gift-mockup.negro .gift-bow i,
.gift-mockup.negro .gift-ribbon-vertical,
.gift-mockup.negro .gift-ribbon-horizontal {
    background:linear-gradient(145deg,#4b241d,#2c1810);
}
.gift-mockup.negro .gift-lid { background:linear-gradient(145deg,#4a211a,#24120e); }
.gift-mockup.negro .gift-base { background:linear-gradient(145deg,#3c1d17,#22110d); }
.gift-mockup.negro .gift-ribbon-vertical,
.gift-mockup.negro .gift-ribbon-horizontal { background:linear-gradient(180deg,#f0d36a,#d4af37); }
.gift-mockup.negro .gift-bow i { background:#d4af37; }
.gift-mockup.negro .gift-card { background:rgba(212,175,55,.14); color:#f3d97d; border:1px solid rgba(212,175,55,.22); }
</style>
@endsection
@endsection

@extends('layouts.template')
@section('title', 'Familias Olfativas — Fragancias de Lujo')
@section('meta_description', 'Explora todas nuestras familias olfativas: Floral, Maderado, Oriental, Fresco, Acuático, Oud, Gourmand y Nicho. Encuentra tu fragancia ideal.')
@section('canonical_url', route('familias.index'))

@section('styles')
<style>
.familias-page-hero {
    background: linear-gradient(135deg, var(--ep-primary) 0%, #4A2520 100%);
    color: #fff;
    padding: 3rem 0;
    margin-bottom: 3rem;
}

.familias-page-hero-content {
    text-align: center;
}

.familias-page-hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.8rem;
    margin-bottom: 1rem;
}

.familias-page-hero p {
    font-size: 1.05rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
}

.familias-page-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.familia-card {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2.2rem 1.5rem; background: var(--ep-ivory); border: 1.5px solid var(--ep-border);
    border-radius: 18px; transition: all .25s; cursor: pointer; height: 100%;
    box-shadow: 0 4px 14px rgba(44,24,16,.05);
    text-decoration: none; color: inherit;
}

.familia-card:hover {
    border-color: var(--ep-gold); background: var(--ep-cream); transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(44,24,16,.15);
}

.familia-icon { font-size: 2.4rem; color: var(--ep-gold-a11y); margin-bottom: 1rem; }
.familia-nombre { font-size: 1.05rem; font-weight: 600; color: var(--ep-primary); text-align: center; }
.familia-count { font-size: .78rem; color: var(--ep-muted); margin-top: .3rem; }

@media (max-width: 768px) {
    .familias-page-hero h1 { font-size: 2rem; }
    .familias-page-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.5rem; }
    .familia-card { padding: 1.6rem 1.2rem; }
    .familia-icon { font-size: 2rem; }
}
</style>
@endsection

@section('contenido')

<div class="familias-page-hero">
    <div class="container">
        <div class="familias-page-hero-content">
            <h1>Familias Olfativas</h1>
            <p>Descubre todas nuestras categorías de fragancias. Cada familia tiene su propio carácter y personalidad.</p>
        </div>
    </div>
</div>

<section class="container py-4">
    <div class="familias-page-grid">
        @foreach($familias as $familia)
        <a href="{{ route('familia.show', $familia->id) }}" class="familia-card">
            <div class="familia-icon"><i class="{{ $familia->icono ?? 'fas fa-spa' }}"></i></div>
            <div class="familia-nombre">{{ $familia->nombre }}</div>
            <div class="familia-count">{{ $familia->fragancias_count ?? 0 }} {{ $familia->fragancias_count === 1 ? 'fragancia' : 'fragancias' }}</div>
        </a>
        @endforeach
    </div>
</section>

@endsection

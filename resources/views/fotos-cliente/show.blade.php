@extends('layouts.app')

@section('htmlheader_title')
Fotos Adicionales
@endsection

@section('contentheader_title')

@endsection

@section('main-content')
<div class="container-fluid spark-screen">

    {{-- Alertas del sistema --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Tarjeta Principal --}}
    <div class="card card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0">
                <i class="fas fa-images text-primary mr-2"></i> Galería de Registros
            </h3>
        </div>

        <div class="card-body py-2">
            @forelse($fotos as $foto)
                @if($loop->first)
                    <div class="row g-4">
                @endif

                {{-- Tarjeta de Imagen --}}
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="photo-card">
                        <div class="photo-wrapper">
                            <span class="badge badge-type">{{ $foto->RecTipo }}</span>
                            <img src="{{ asset('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc) }}"
                                 alt="Foto {{ $foto->RecTipo }}"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('img/defaultimage.png') }}'">

                            <div class="photo-overlay">
                                <a href="{{ asset('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc) }}"
                                   target="_blank"
                                   class="btn btn-light btn-circle mr-2"
                                   title="Ver imagen completa">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('fotos-cliente.download', $foto->ID_Rec) }}"
                                   class="btn btn-light btn-circle"
                                   title="Descargar">
                                    <i class="fas fa-download text-success"></i>
                                </a>
                            </div>
                        </div>

                        <div class="photo-details">
                            <div class="detail-item">
                                <i class="far fa-calendar-alt text-muted"></i>
                                <span>{{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/Y') }}</span>
                            </div>
                            <div class="detail-item text-muted small">
                                <i class="far fa-clock"></i>
                                <span>{{ \Carbon\Carbon::parse($foto->created_at)->format('H:i') }} hrs</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($loop->last)
                    </div>
                @endif
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h4>No hay fotos disponibles</h4>
                    <p class="text-muted">No se encontraron archivos cargados para esta categoría.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* Header Estilizado */
.content-header-custom {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: #ffffff;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.content-header-custom h2 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

/* Card Principal */
.card-custom {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    background: #ffffff;
    overflow: hidden;
}

.card-custom .card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
}

/* Tarjeta de Foto */
.photo-card {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fff;
    transition: all 0.25s ease-in-out;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.photo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

.photo-wrapper {
    position: relative;
    width: 100%;
    padding-top: 66.66%; /* Relación de aspecto 3:2 */
    background-color: #f8fafc;
    overflow: hidden;
}

.photo-wrapper img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-card:hover .photo-wrapper img {
    transform: scale(1.05);
}

/* Overlay de Acciones */
.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s ease;
}

.photo-wrapper:hover .photo-overlay {
    opacity: 1;
}

.btn-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
}

.btn-circle:hover {
    transform: scale(1.1);
}

/* Badges y Detalle */
.badge-type {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
    background-color: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.photo-details {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #475569;
}

/* Estado Vacío */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}
</style>
@endsection

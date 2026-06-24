@if(Auth::check() && Auth::user()->UsRol === 'Cliente' && isset($calificacionesPendientes) && $calificacionesPendientes->count() > 0)
<style>
    .modal-calificaciones {
        display: none;
        position: fixed;
        z-index: 99999 !important;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.7);
        animation: fadeIn 0.3s;
    }
    
    .modal-calificaciones.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content-calificaciones {
        background-color: #fefefe;
        margin: auto;
        padding: 0;
        border: none;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        animation: slideDown 0.4s;
        position: relative;
        overflow: visible;
        z-index: 100000 !important;
    }
    
    .modal-header-calificaciones {
        background: linear-gradient(40deg, #d4fc79, #00C851);
        padding: 20px 25px;
        color: white;
        border-radius: 15px 15px 0 0;
        position: relative;
    }
    
    .modal-header-calificaciones h4 {
        margin: 0;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .modal-header-calificaciones .close-modal {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 24px;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .modal-header-calificaciones .close-modal:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-50%) rotate(90deg);
    }
    
    .modal-body-calificaciones {
        padding: 25px;
        max-height: 60vh;
        overflow-y: auto;
        position: relative;
        z-index: 1;
    }
    
    .calificacion-item {
        background: #f8f9fa;
        border-left: 4px solid #00C851;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        transition: all 0.3s;
        position: relative;
        z-index: 1;
    }
    
    .calificacion-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .calificacion-item a {
        position: relative;
        z-index: 2;
        text-decoration: none !important;
    }
    
    .calificacion-item h5 {
        margin: 0 0 10px 0;
        color: #333;
        font-weight: 600;
    }
    
    .calificacion-item p {
        margin: 5px 0;
        color: #666;
        font-size: 14px;
    }
    
    .btn-calificar {
        background: linear-gradient(40deg, #d4fc79, #00C851) !important;
        border: none !important;
        color: white !important;
        padding: 12px 20px !important;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        margin-top: 10px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        box-sizing: border-box;
        position: relative;
        z-index: 10;
        line-height: 1.6 !important;
        min-height: 44px;
        gap: 8px;
    }
    
    .btn-calificar i {
        display: inline-block;
        font-size: 16px;
    }
    
    .btn-calificar span {
        display: inline-block;
    }
    
    .btn-calificar:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(0,200,81,0.3);
        text-decoration: none !important;
        color: white !important;
    }
    
    .btn-calificar:focus,
    .btn-calificar:active,
    .btn-calificar:visited {
        outline: none !important;
        text-decoration: none !important;
        color: white !important;
        border: none !important;
    }
    
    .btn-ver-todas {
        background: #33b5e5;
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 15px;
        width: 100%;
    }
    
    .btn-ver-todas:hover {
        background: #1e9bc4;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(51,181,229,0.3);
    }
    
    .emoji-alert {
        font-size: 50px;
        text-align: center;
        margin-bottom: 15px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideDown {
        from { 
            opacity: 0;
            transform: translateY(-50px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .recordatorio-texto {
        text-align: center;
        margin-bottom: 20px;
        color: #555;
        line-height: 1.6;
    }
</style>

<div id="modalCalificaciones" class="modal-calificaciones">
    <div class="modal-content-calificaciones">
        <div class="modal-header-calificaciones">
            <h4>
                <i class="fa fa-star"></i>
                Recordatorio de Calificación
            </h4>
            <button type="button" class="close-modal" onclick="cerrarModalCalificaciones()">&times;</button>
        </div>
        <div class="modal-body-calificaciones">
            <div class="emoji-alert">⭐</div>
            <div class="recordatorio-texto">
                <p style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">
                    Tienes <strong style="color: #00C851; font-size: 20px;">{{ $calificacionesPendientes->count() }}</strong> 
                    servicio(s) pendiente(s) de calificar
                </p>
                <p>Tu opinión es muy importante para nosotros. Por favor, tómate un momento para calificar nuestros servicios.</p>
            </div>
            
            @foreach($calificacionesPendientes->take(3) as $calificacion)
            <div class="calificacion-item">
                <h5>
                    <i class="fa fa-file-text"></i>
                    Solicitud #{{ $calificacion->servicio ? $calificacion->servicio->ID_SolSer : 'N/A' }}
                </h5>
                @if($calificacion->servicio && $calificacion->servicio->cliente)
                <p><strong>Cliente:</strong> {{ $calificacion->servicio->cliente->CliName }}</p>
                @endif
                @if($calificacion->created_at)
                <p><strong>Fecha:</strong> {{ $calificacion->created_at->format('d/m/Y') }}</p>
                @endif
                @if($calificacion->signed_hash)
                    <a href="{{ route('calificaciones.create', $calificacion->signed_hash) }}" 
                       class="btn-calificar">
                        <i class="fa fa-star"></i> <span>Calificar Este Servicio</span>
                    </a>
                @endif
            </div>
            @endforeach
            
            @if($calificacionesPendientes->count() > 3)
            <p class="text-center" style="margin-top: 15px; color: #666;">
                Y {{ $calificacionesPendientes->count() - 3 }} más...
            </p>
            @endif
            
            <a href="{{ route('calificaciones.pendientes') }}" class="btn-ver-todas">
                <i class="fa fa-list"></i> Ver Todas las Calificaciones Pendientes
            </a>
            
            <div style="text-align: center; margin-top: 15px;">
                <button type="button" onclick="recordarMasTarde()" 
                        style="background: none; border: none; color: #999; cursor: pointer; text-decoration: underline;">
                    Recordarme más tarde
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function cerrarModalCalificaciones() {
        document.getElementById('modalCalificaciones').classList.remove('show');
        // Guardar en localStorage que el usuario cerró el modal
        localStorage.setItem('calificacionesModalCerrado', new Date().getTime());
    }
    
    function recordarMasTarde() {
        cerrarModalCalificaciones();
        // Recordar por 24 horas
        const tiempoRecordar = 24 * 60 * 60 * 1000; // 24 horas en milisegundos
        localStorage.setItem('calificacionesRecordarMasTarde', new Date().getTime() + tiempoRecordar);
    }
    
    // Mostrar el modal automáticamente si no se ha cerrado recientemente
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalCalificaciones');
        if (!modal) return;
        
        // Verificar si el modal fue cerrado recientemente (últimos 5 minutos)
        const modalCerrado = localStorage.getItem('calificacionesModalCerrado');
        const ahora = new Date().getTime();
        const tiempoLimite = 5 * 60 * 1000; // 5 minutos
        
        // Verificar si se pidió recordar más tarde
        const recordarMasTarde = localStorage.getItem('calificacionesRecordarMasTarde');
        
        if (recordarMasTarde && ahora < parseInt(recordarMasTarde)) {
            // Todavía está en el período de "recordar más tarde"
            return;
        }
        
        if (!modalCerrado || (ahora - parseInt(modalCerrado)) > tiempoLimite) {
            // Mostrar el modal después de 2 segundos
            setTimeout(function() {
                modal.classList.add('show');
            }, 2000);
        }
    });
</script>
@endif


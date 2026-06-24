<!-- Modal de Recordatorios -->
<div class="modal fade" id="recordatoriosModal" tabindex="-1" role="dialog" aria-labelledby="recordatoriosModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(40deg, #667eea 0%, #764ba2 100%); color: white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="recordatoriosModalLabel">
                    <i class="fas fa-bell"></i> Recordatorios Importantes
                </h4>
            </div>
            <div class="modal-body" id="recordatoriosContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted">Cargando recordatorios...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="marcarRecordatoriosVistos()">
                    <i class="fas fa-check"></i> Marcar como vistos
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Asegurar que el modal de recordatorios esté por encima de otros modales */
    #recordatoriosModal {
        z-index: 9999 !important;
    }
    #recordatoriosModal .modal-backdrop {
        z-index: 9998 !important;
    }
    
    .recordatorio-item {
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid #ddd;
        border-radius: 4px;
        background: #f9f9f9;
        transition: all 0.3s;
    }
    .recordatorio-item:hover {
        background: #f0f0f0;
        transform: translateX(5px);
    }
    .recordatorio-item.urgencia-alta {
        border-left-color: #dc3545;
        background: #fff5f5;
    }
    .recordatorio-item.urgencia-media {
        border-left-color: #ffc107;
        background: #fffbf0;
    }
    .recordatorio-item.urgencia-baja {
        border-left-color: #28a745;
        background: #f0fff4;
    }
    .recordatorio-icon {
        font-size: 24px;
        margin-right: 10px;
        vertical-align: middle;
    }
    .recordatorio-titulo {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .recordatorio-mensaje {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .recordatorio-fecha {
        color: #999;
        font-size: 12px;
    }
    .sin-recordatorios {
        text-align: center;
        padding: 40px;
        color: #999;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Verificar si es un comercial
    @if(Auth::check() && (in_array(Auth::user()->UsRol, ['Comercial', 'Comercialap', 'Ejecutivo Comercial']) || in_array(Auth::user()->UsRol2, ['Comercial', 'Comercialap', 'Ejecutivo Comercial'])))
        // Esperar un momento para asegurar que todo esté cargado
        setTimeout(function() {
            cargarRecordatorios();
        }, 500);
        
        // Verificar recordatorios cada 30 minutos
        setInterval(cargarRecordatorios, 30 * 60 * 1000);
    @endif
});

function cargarRecordatorios() {
    // Mostrar loading
    var contenido = $('#recordatoriosContent');
    contenido.html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
            <p class="text-muted">Cargando recordatorios...</p>
        </div>
    `);
    
    $.ajax({
        url: '{{ route("crm.recordatorios") }}',
        method: 'GET',
        timeout: 10000, // 10 segundos de timeout
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response && response.recordatorios !== undefined) {
                mostrarRecordatorios(response.recordatorios);
            } else {
                mostrarError('Respuesta inválida del servidor');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar recordatorios:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            if (status === 'timeout') {
                mostrarError('La petición tardó demasiado. Por favor, verifica tu conexión.');
            } else if (xhr.status === 500) {
                mostrarError('Error del servidor. Por favor, contacta al administrador.');
            } else {
                mostrarError('Error al cargar los recordatorios. Por favor, recarga la página.');
            }
        }
    });
}

function mostrarError(mensaje) {
    var contenido = $('#recordatoriosContent');
    contenido.html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${mensaje}
            <br><br>
            <button class="btn btn-sm btn-primary" onclick="cargarRecordatorios()">
                <i class="fas fa-redo"></i> Reintentar
            </button>
        </div>
    `);
}

function mostrarRecordatorios(recordatorios) {
    var contenido = $('#recordatoriosContent');
    
    if (recordatorios.length === 0) {
        contenido.html(`
            <div class="sin-recordatorios">
                <i class="fas fa-check-circle fa-3x text-success"></i>
                <h4 style="margin-top: 20px; color: #999;">No hay recordatorios pendientes</h4>
                <p>¡Todo está al día!</p>
            </div>
        `);
        return;
    }
    
    // Ordenar por urgencia
    recordatorios.sort(function(a, b) {
        var urgenciaOrder = {'alta': 3, 'media': 2, 'baja': 1};
        return urgenciaOrder[b.urgencia] - urgenciaOrder[a.urgencia];
    });
    
    var html = '<div class="list-group">';
    
    recordatorios.forEach(function(recordatorio) {
        var icono = '';
        var colorIcono = '';
        
        if (recordatorio.tipo === 'actividad') {
            switch(recordatorio.titulo.split(' ')[0]) {
                case 'Llamada':
                    icono = 'fa-phone';
                    colorIcono = 'text-primary';
                    break;
                case 'Visita':
                    icono = 'fa-map-marker-alt';
                    colorIcono = 'text-success';
                    break;
                case 'Email':
                    icono = 'fa-envelope';
                    colorIcono = 'text-info';
                    break;
                case 'Reunión':
                    icono = 'fa-users';
                    colorIcono = 'text-warning';
                    break;
                default:
                    icono = 'fa-tasks';
                    colorIcono = 'text-secondary';
            }
        } else if (recordatorio.tipo === 'oportunidad_vencimiento') {
            icono = 'fa-lightbulb';
            colorIcono = 'text-warning';
        } else {
            icono = 'fa-calendar';
            colorIcono = 'text-info';
        }
        
        html += `
            <div class="recordatorio-item urgencia-${recordatorio.urgencia}" onclick="window.location.href='${recordatorio.url}'" style="cursor: pointer;">
                <div class="row">
                    <div class="col-md-1 text-center">
                        <i class="fas ${icono} ${colorIcono} recordatorio-icon"></i>
                    </div>
                    <div class="col-md-11">
                        <div class="recordatorio-titulo">${recordatorio.titulo}</div>
                        <div class="recordatorio-mensaje">${recordatorio.mensaje}</div>
                        <div class="recordatorio-fecha">
                            <i class="far fa-clock"></i> ${recordatorio.fecha}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    contenido.html(html);
    
    // Actualizar badge en el header
    var badge = $('#recordatoriosBadge');
    if (recordatorios.length > 0) {
        badge.text(recordatorios.length).show();
    } else {
        badge.hide();
    }
    
    // Solo mostrar automáticamente si hay recordatorios de urgencia alta o media
    // y el modal no está abierto manualmente
    var tieneUrgencia = recordatorios.some(function(r) {
        return r.urgencia === 'alta' || r.urgencia === 'media';
    });
    
    // Solo mostrar automáticamente si no se ha visto hoy y hay recordatorios de urgencia
    var recordatoriosVistos = localStorage.getItem('recordatoriosVistos_' + new Date().toDateString());
    if (tieneUrgencia && !recordatoriosVistos && recordatorios.length > 0 && !$('#recordatoriosModal').hasClass('in')) {
        $('#recordatoriosModal').modal('show');
    }
}

function marcarRecordatoriosVistos() {
    localStorage.setItem('recordatoriosVistos_' + new Date().toDateString(), 'true');
    $('#recordatoriosModal').modal('hide');
}
</script>
@endpush


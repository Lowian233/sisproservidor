<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border" style="padding: 10px 15px;">
                <h3 class="box-title" style="font-size: 18px; margin: 0;">
                    <i class="fas fa-calendar-alt"></i> Calendario de Actividades y Oportunidades
                </h3>
                <div class="box-tools pull-right" style="margin-top: 0;">
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-xs btn-info" style="margin-left: 5px;">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                    <a href="{{ route('crm.actividades.create') }}" class="btn btn-xs btn-success" style="margin-left: 5px;">
                        <i class="fa fa-plus"></i> Nueva Actividad
                    </a>
                </div>
            </div>
            <div class="box-body" style="padding: 10px;">
                <div id="calendarioCRM"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del evento -->
<div class="modal fade" id="eventoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="eventoTitulo"></h4>
            </div>
            <div class="modal-body" id="eventoContenido">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <a href="#" id="eventoUrl" class="btn btn-primary">Ver Detalles</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<style>
    #calendarioCRM {
        min-height: 500px;
        max-height: 650px;
    }
    
    /* Hacer el calendario más compacto */
    .fc {
        font-size: 13px;
    }
    
    .fc-header-toolbar {
        margin-bottom: 0.5em;
        padding: 0.5em;
    }
    
    .fc-toolbar-title {
        font-size: 1.2em !important;
    }
    
    .fc-button {
        padding: 0.3em 0.6em !important;
        font-size: 0.85em !important;
    }
    
    .fc-daygrid-day {
        min-height: 80px !important;
    }
    
    .fc-daygrid-day-frame {
        min-height: 60px !important;
    }
    
    .fc-col-header-cell {
        padding: 0.5em 0.25em !important;
        font-size: 0.85em;
        font-weight: 600;
    }
    
    .fc-daygrid-day-number {
        padding: 4px !important;
        font-size: 0.9em;
    }
    
    .fc-event {
        cursor: pointer;
        margin: 1px 2px !important;
        padding: 2px 4px !important;
        font-size: 0.75em !important;
        line-height: 1.3 !important;
        border-radius: 3px;
    }
    
    .fc-event-title {
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .fc-event-time {
        font-weight: bold;
        margin-right: 3px;
    }
    
    /* Mejorar visibilidad de eventos */
    .fc-event:hover {
        opacity: 0.9;
        transform: scale(1.02);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Hacer más compacta la vista de semana */
    .fc-timeGridWeek-view .fc-timegrid-slot {
        height: 2em !important;
    }
    
    .fc-timeGridDay-view .fc-timegrid-slot {
        height: 2em !important;
    }
    
    /* Ajustar altura de las celdas del mes */
    .fc-daygrid-day-top {
        padding: 2px 4px !important;
    }
    
    /* Mejorar contraste de texto en eventos */
    .fc-event-main {
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    
    /* Compactar más los eventos en el mes */
    .fc-daygrid-event {
        margin: 1px 0 !important;
    }
    
    /* Ajustar el popover cuando hay muchos eventos */
    .fc-more-link {
        font-size: 0.8em;
        padding: 2px 4px;
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/es.js'></script>
<script>
$(document).ready(function() {
    console.log('Inicializando calendario CRM...');
    var calendarEl = document.getElementById('calendarioCRM');
    
    if (!calendarEl) {
        console.error('No se encontró el elemento #calendarioCRM');
        return;
    }
    
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar no está cargado');
        return;
    }
    
    console.log('FullCalendar cargado correctamente');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        contentHeight: 550,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Agenda'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            console.log('Cargando eventos desde:', fetchInfo.startStr, 'hasta', fetchInfo.endStr);
            $.ajax({
                url: '{{ route("home.eventos-calendario") }}',
                method: 'GET',
                data: {
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                },
                success: function(response) {
                    console.log('Eventos cargados:', response.length);
                    successCallback(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar eventos:', error);
                    console.error('Respuesta:', xhr.responseText);
                    failureCallback();
                }
            });
        },
        eventClick: function(info) {
            var props = info.event.extendedProps;
            // Usar el título completo del extendedProps si está disponible
            var titulo = props.titulo ? (props.tipoActividad ? props.tipoActividad + ': ' : '') + props.titulo : info.event.title;
            var fecha = info.event.start;
            
            $('#eventoTitulo').html('<i class="fas fa-' + (props.tipo === 'actividad' ? 'tasks' : 'lightbulb') + '"></i> ' + titulo);
            
            var contenido = '<div class="row">';
            contenido += '<div class="col-md-12">';
            contenido += '<p><strong><i class="far fa-calendar"></i> Fecha:</strong> ' + 
                fecha.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            
            if (info.event.allDay) {
                contenido += ' <span class="label label-info">Todo el día</span>';
            } else {
                contenido += ' <strong><i class="far fa-clock"></i> Hora:</strong> ' + 
                    fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            }
            contenido += '</p></div></div>';
            
            contenido += '<div class="row" style="margin-top: 15px;">';
            contenido += '<div class="col-md-12">';
            contenido += '<p><strong><i class="fas fa-building"></i> Cliente:</strong> ' + props.cliente + '</p>';
            contenido += '</div></div>';
            
            if (props.tipo === 'oportunidad') {
                contenido += '<div class="row" style="margin-top: 10px;">';
                contenido += '<div class="col-md-6">';
                contenido += '<p><strong><i class="fas fa-dollar-sign"></i> Valor Estimado:</strong><br>';
                contenido += '<span style="font-size: 18px; color: #28a745; font-weight: bold;">$' + 
                    parseFloat(props.valor).toLocaleString('es-CO') + '</span></p>';
                contenido += '</div>';
                contenido += '<div class="col-md-6">';
                contenido += '<p><strong><i class="fas fa-percentage"></i> Probabilidad:</strong><br>';
                contenido += '<div class="progress" style="margin-top: 5px;">';
                contenido += '<div class="progress-bar" role="progressbar" style="width: ' + props.probabilidad + '%;">';
                contenido += props.probabilidad + '%</div></div></p>';
                contenido += '</div></div>';
            } else {
                contenido += '<div class="row" style="margin-top: 10px;">';
                contenido += '<div class="col-md-12">';
                contenido += '<p><strong><i class="fas fa-info-circle"></i> Estado:</strong> ';
                contenido += '<span class="label label-warning">' + props.estado + '</span></p>';
                contenido += '</div></div>';
            }
            
            contenido += '<div class="row" style="margin-top: 15px;">';
            contenido += '<div class="col-md-12">';
            contenido += '<span class="label" style="background-color: ' + info.event.backgroundColor + '; color: white; padding: 5px 10px; font-size: 12px;">';
            contenido += '<i class="fas fa-' + (props.tipo === 'actividad' ? 'tasks' : 'lightbulb') + '"></i> ';
            contenido += props.tipo === 'actividad' ? 'Actividad' : 'Oportunidad';
            contenido += '</span>';
            contenido += '</div></div>';
            
            $('#eventoContenido').html(contenido);
            $('#eventoUrl').attr('href', props.url);
            $('#eventoModal').modal('show');
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover'
    });
    
    calendar.render();
    console.log('Calendario renderizado correctamente');
});
</script>
@endpush


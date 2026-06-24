<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calificar Servicio - Prosarc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;

        }
        .rating-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        .emoji-rating {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
            gap: 20px;
        }
        .emoji-option {
            cursor: pointer;
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            transition: all 0.3s;
            border: 4px solid transparent;
            flex: 1;
            margin: 0 10px;
        }
        .emoji-option:hover {
            background: #f8f9fa;
            transform: scale(1.05);
        }
        .emoji-option.selected {
            border-color: currentColor;
            border-width: 5px;
            background: rgba(0,0,0,0.08);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            transform: scale(1.08);
        }
        .emoji-option.selected .emoji-circle {
            transform: scale(1.15);
            box-shadow: 0 6px 25px rgba(0,0,0,0.4), 0 0 20px rgba(255,255,255,0.5);
            border: 4px solid rgba(255,255,255,0.9);
            animation: pulse-glow 2s ease-in-out infinite;
        }
        .emoji-option.selected .emoji {
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.8));
        }
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 6px 25px rgba(0,0,0,0.4), 0 0 20px rgba(255,255,255,0.5);
            }
            50% {
                box-shadow: 0 6px 30px rgba(0,0,0,0.5), 0 0 30px rgba(255,255,255,0.7);
            }
        }
        .emoji-option input[type="radio"] {
            display: none;
        }
        .emoji-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .emoji-option .emoji {
            font-size: 50px;
            display: block;
            transition: all 0.3s ease;
        }
        .emoji-option .label {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 25px;
            color: white;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .service-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .guide-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .guide-header {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-weight: bold;
            color: #667eea;
        }
        .guide-header:hover {
            color: #764ba2;
        }
        .guide-content {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .guide-content.show {
            display: block;
        }
        .guide-content h5 {
            color: #495057;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 600;
        }
        .guide-content h5:first-child {
            margin-top: 0;
        }
        .guide-content ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        .guide-content li {
            margin-bottom: 5px;
            color: #6c757d;
        }
        .guide-summary {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
        .guide-summary p {
            margin-bottom: 8px;
            font-weight: 600;
            color: #1976D2;
        }
        .guide-summary ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .guide-summary li {
            color: #424242;
        }
        .toggle-icon {
            transition: transform 0.3s;
        }
        .toggle-icon.rotated {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="rating-container">
        <div class="text-center mb-4">
            <img src="{{ asset('img/LogoProsarcCompleto.png') }}" alt="Logo" class="img-fluid" style="width: 300px; height: 100px;">
            <h2 class="mb-2">Califica nuestro Servicio</h2>
            <p class="text-muted">Tu opinión es muy importante para nosotros</p>
        </div>

        @if($calificacion->servicio)
        <div class="service-info">
            <h5>Información del Servicio</h5>
            <p class="mb-1"><strong>Solicitud #:</strong> {{ $calificacion->servicio->ID_SolSer }}</p>
            @if($calificacion->servicio->cliente)
                <p class="mb-0"><strong>Cliente:</strong> {{ $calificacion->servicio->cliente->CliName }}</p>
            @endif
        </div>
        @endif

        <div class="guide-section">
            <div class="guide-header" onclick="toggleGuide()">
                <span>📋 Guía para Calificar el Servicio</span>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="guide-content" id="guideContent">
                <p class="mb-3"><strong>Califica la satisfacción general del servicio teniendo presentes los siguientes parámetros:</strong></p>
                
                <h5>1. Vehículo</h5>
                <ul>
                    <li>Estado y limpieza del vehículo.</li>
                    <li>Capacidad adecuada según el tipo de residuo.</li>
                    <li>Cumplimiento de normas (rotulación, cerramiento, contención).</li>
                    <li>Accesibilidad y manipulación segura.</li>
                </ul>

                <h5>2. Personal</h5>
                <ul>
                    <li>Uso adecuado de EPP.</li>
                    <li>Presentación personal.</li>
                    <li>Profesionalismo y actitud.</li>
                    <li>Cumplimiento de protocolos de seguridad y bioseguridad.</li>
                    <li>Forma correcta de manipular los residuos.</li>
                    <li>Aplicación de procedimientos de cargue y descargue.</li>
                    <li>Prevención de derrames o incidentes.</li>
                    <li>Señalización y orden durante la recolección.</li>
                </ul>

                <h5>3. Cumplimiento del servicio</h5>
                <ul>
                    <li>Llegada en la fecha programada.</li>
                    <li>Realización completa del servicio (no dejar residuos pendientes).</li>
                    <li>Frecuencia acordada cumplida.</li>
                </ul>

                <h5>4. Documentación y registros</h5>
                <ul>
                    <li>Entrega oportuna de manifiestos, planillas o soportes.</li>
                    <li>Exactitud de la información reportada.</li>
                    <li>Accesibilidad y claridad en la plataforma o medio digital.</li>
                    <li>Cumplimiento de requisitos legales.</li>
                </ul>

                <h5>5. Comunicación</h5>
                <ul>
                    <li>Atención por parte del asesor comercial.</li>
                    <li>Respuesta rápida a solicitudes o dudas.</li>
                    <li>Seguimiento después del servicio.</li>
                    <li>Claridad en la información.</li>
                </ul>

                <div class="guide-summary">
                    <p>Interpretación de las calificaciones:</p>
                    <ul>
                        <li><strong>Excelente:</strong> Cumple las expectativas</li>
                        <li><strong>Regular:</strong> Cumple algunos parámetros, pero no todos</li>
                        <li><strong>Deficiente:</strong> El servicio global no cumple</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ route('calificaciones.store') }}" method="POST" id="ratingForm">
            @csrf
            <input type="hidden" name="signed_hash" value="{{ $calificacion->signed_hash }}">

            <div class="mb-4">
                <label class="form-label fw-bold">¿Cómo calificarías el servicio de recolección?</label>
                <div class="emoji-rating">
                    <label class="emoji-option" data-score="1" style="border-color: #dc3545;">
                        <input type="radio" name="score" value="1" required>
                        <div class="emoji-circle" style="background-color: #dc3545;">
                            <span class="emoji">😞</span>
                        </div>
                        <span class="label">Deficiente</span>
                    </label>
                    <label class="emoji-option" data-score="2" style="border-color: #ffc107;">
                        <input type="radio" name="score" value="2" required>
                        <div class="emoji-circle" style="background-color: #ffc107;">
                            <span class="emoji">😐</span>
                        </div>
                        <span class="label">Regular</span>
                    </label>
                    <label class="emoji-option" data-score="3" style="border-color: #28a745;">
                        <input type="radio" name="score" value="3" required>
                        <div class="emoji-circle" style="background-color: #28a745;">
                            <span class="emoji">😊</span>
                        </div>
                        <span class="label">Excelente</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label for="comment" class="form-label fw-bold">Comentarios</label>
                <span id="comment-required" class="text-danger" style="display: none;">*El comentario es obligatorio</span>
                
                <textarea class="form-control" id="comment" name="comment" rows="4" 
                    placeholder="Comparte tu experiencia con nosotros..."></textarea>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="acepta_politicas" name="acepta_politicas" value="1" required>
                    <label class="form-check-label" for="acepta_politicas">
                        Acepto las políticas de tratamiento de datos personales de Prosarc S.A. ESP
                    </label>
                    <span class="text-danger" style="display: none;" id="politicas-required">*Debe aceptar las políticas de tratamiento de datos</span>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-submit">
                    Enviar Calificación
                </button>
            </div>
        </form>
    </>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleGuide() {
            var content = document.getElementById('guideContent');
            var icon = document.querySelector('.toggle-icon');
            content.classList.toggle('show');
            icon.classList.toggle('rotated');
        }

        $(document).ready(function() {
            $('.emoji-option').click(function() {
                $('.emoji-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
                
                // Si se selecciona "Deficiente" (score = 1), hacer el comentario obligatorio
                var selectedScore = $(this).find('input[type="radio"]').val();
                if (selectedScore == '1') {
                    $('#comment').prop('required', true);
                    $('#comment-required').show();
                } else {
                    $('#comment').prop('required', false);
                    $('#comment-required').hide();
                }
            });

            // Validar checkbox de políticas
            $('#acepta_politicas').change(function() {
                if ($(this).is(':checked')) {
                    $('#politicas-required').hide();
                } else {
                    $('#politicas-required').show();
                }
            });

            $('#ratingForm').submit(function(e) {
                if (!$('input[name="score"]:checked').length) {
                    e.preventDefault();
                    alert('Por favor selecciona una calificación');
                    return false;
                }
                
                // Validar comentario si es "Deficiente"
                var selectedScore = $('input[name="score"]:checked').val();
                if (selectedScore == '1' && !$('#comment').val().trim()) {
                    e.preventDefault();
                    alert('El comentario es obligatorio cuando la calificación es Deficiente');
                    $('#comment').focus();
                    return false;
                }
                
                // Validar aceptación de políticas
                if (!$('#acepta_politicas').is(':checked')) {
                    e.preventDefault();
                    alert('Debe aceptar las políticas de tratamiento de datos personales para continuar');
                    $('#politicas-required').show();
                    $('#acepta_politicas').focus();
                    return false;
                }
            });
        });
    </script>
</body>
</html>


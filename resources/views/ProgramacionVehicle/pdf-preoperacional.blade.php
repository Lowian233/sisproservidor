<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Preoperacional vehículo - {{ date('d/m/Y', strtotime($programacion->ProgVehFecha)) }}</title>
	<style>
		@page {
			size: 612pt 792pt;
			margin: 20mm 15mm;
		}

		body {
			font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #000;
		}

		.preop-sheet { border: 1px solid #333; background: #fff; padding: 10px; }
		.preop-table { width: 100%; border-collapse: collapse; }
		.preop-table th, .preop-table td { border: 1px solid #333; padding: 4px; font-size: 10px; vertical-align: top; }
		.preop-table th { background: #f5f5f5; font-weight: bold; text-transform: uppercase; font-size: 9px; }
		.preop-small { font-size: 9px; }
		.preop-center { text-align: center; }
		.preop-muted { color: #555; }
		.preop-input-line { border-bottom: 1px solid #999; display: inline-block; min-width: 80px; }
		.preop-checkbox-cell { text-align: center; width: 22px; }
		.preop-checkbox-box {
			display: inline-block;
			width: 9px;
			height: 9px;
			border: 1px solid #333;
			font-size: 8px;
			line-height: 9px;
			text-align: center;
		}
		.preop-section-title {
			font-weight: bold;
			text-align: center;
			text-transform: uppercase;
		}
		.observaciones-box {
			min-height: 70px;
		}
		.firma-box {
			min-height: 60px;
			border: 1px solid #333;
			text-align: center;
			padding: 4px;
		}
		.firma-imagen {
			max-width: 260px;
			max-height: 120px;
		}
	</style>
</head>
<body>
@php
	$checks = $programacion->ProgVehCheckPreoperacional ? json_decode($programacion->ProgVehCheckPreoperacional, true) : [];
	$set = function ($path, $default = null) use ($checks) {
		$cur = $checks;
		foreach (explode('.', $path) as $p) {
			if (!is_array($cur) || !array_key_exists($p, $cur)) return $default;
			$cur = $cur[$p];
		}
		return $cur;
	};

	$docs = [
		'tarjeta_propiedad' => 'Tarjeta de propiedad',
		'licencia' => 'Licencia de conducción del conductor',
		'soat' => 'SOAT vigente',
		'tecnomecanica' => 'Certificado de revisión técnico mecánica vigente',
		'poliza_rc' => 'Póliza de responsabilidad civil extracontractual',
	];

	$equipo = [
		'gato' => 'Gato con capacidad para elevar el vehículo',
		'cruceta' => 'Cruceta',
		'senales' => 'Dos señales de carretera en forma de triángulo y con soporte para colocación vertical',
		'botiquin' => 'Botiquín de primeros auxilios',
		'extintor' => 'Extintor con recarga vigente',
		'tacos' => 'Dos tacos para bloquear el vehículo',
		'herramientas' => 'Caja de herramientas que contenga mínimo: alicate, destornilladores, llave de expansión y llaves fijas',
		'llanta' => 'Llanta de repuesto en buen estado',
	];

	$estadoVeh = [
		'llantas' => 'Condiciones óptimas de las llantas',
		'luces' => 'Correcto funcionamiento de las luces',
		'frenos' => 'Estado adecuado de frenos',
		'fugas' => 'Presencia de fugas',
		'direccion' => 'Correcto funcionamiento de la dirección',
		'refrigeracion' => 'El vehículo posee sistema para refrigeración de la carga',
	];

	$mp = [
		'transporta_mp' => '¿Se transportarán mercancías peligrosas? (En caso afirmativo, verificar los elementos listados a continuación).',
		'numeros_un' => 'Números UN en todas las caras visibles de la unidad de transporte y rombos de sustancias peligrosas',
		'hojas_seguridad' => 'Hojas de Seguridad de los residuos transportados',
		'kit_multiproposito' => 'Kit multipropósito (con recarga vigente) en la cabina y de fácil acceso',
		'extintor_mp' => 'Extintor multipropósito (con recarga vigente) cerca de la carga y de fácil acceso',
		'dispositivo_sonoro' => 'Dispositivo sonoro o pito para movimiento de reversa',
		'tarjetas_emergencia' => 'Tarjetas de emergencia en idioma castellano acordes a la NTC 4532',
		'cert_conductor' => 'Certificación o carnet del curso básico obligatorio del conductor para transporte de mercancías peligrosas',
		'plan_ruta' => 'Plan de transporte con horas y ruta seleccionada',
		'listado_nos' => 'Listado con los teléfonos para la atención de emergencias',
		'comunicacion' => 'Sistema de comunicación (celular, radio, etc.)',
		'recibo_rm' => 'Recibo de material',
		'carga_etiquetada' => 'La carga está debidamente clasificada y etiquetada (Decreto 1609 de 2002)',
		'embalajes' => 'Embalajes y envases cumplen con NTC 4702',
		'sustancias_incompatibles' => 'Se evita mezclar sustancias químicas incompatibles',
		'dispositivo_descont' => 'El vehículo cuenta con dispositivo para ejecución de la carga',
	];

	$emg = [
		'protectoras' => 'Ropa protectora',
		'linterna' => 'Linterna (verificar que funcione)',
		'equipo_recoleccion' => 'Equipo para recolección y limpieza',
		'absorbente' => 'Material absorbente',
	];

	$danos = [
		'frente' => 'Frente',
		'costado_izquierdo' => 'Costado izquierdo',
		'costado_derecho' => 'Costado derecho',
		'trasera' => 'Trasera',
		'techo' => 'Techo',
	];
@endphp

<div class="preop-sheet">
	<table class="preop-table">
		<tr>
			<td style="width: 22%;">
				<b>PROSARC</b><br>
				<span class="preop-small">FORMATO INSPECCIÓN PREOPERACIONAL DE VEHÍCULOS</span>
			</td>
			<td style="width: 48%;" class="preop-center">
				<b>FORMATO INSPECCIÓN PREOPERACIONAL DE VEHÍCULOS</b><br>
				<span class="preop-small">SISTEMA INTEGRADO DE GESTIÓN</span>
			</td>
			<td style="width: 30%;" class="preop-small">
				<div><b>FORMATO:</b> FR-LG-003</div>
				<div><b>VERSIÓN:</b> 02</div>
				<div><b>FECHA:</b> ene-22</div>
			</td>
		</tr>
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<td style="width: 20%;">
				<b>FECHA</b><br>
				<span class="preop-small preop-muted">DD/MM/AAAA</span><br>
				<span class="preop-input-line">{{ date('d/m/Y', strtotime($programacion->ProgVehFecha)) }}</span>
			</td>
			<td style="width: 15%;">
				<b>HORA</b><br>
				<span class="preop-input-line">
					{{ $programacion->ProgVehFechaPreoperacional
						? date('H:i', strtotime($programacion->ProgVehFechaPreoperacional))
						: date('H:i') }}
				</span>
			</td>
			<td style="width: 25%;">
				<b>PLACA DEL VEHÍCULO</b><br>
				<span class="preop-input-line">{{ $vehiculo ? $vehiculo->VehicPlaca : 'N/A' }}</span>
			</td>
			<td style="width: 20%;">
				<b>CONDUCTOR</b><br>
				<span class="preop-input-line">
					@if($conductor)
						{{ $conductor->PersFirstName }} {{ $conductor->PersLastName }}
					@else
						{{ \Illuminate\Support\Facades\Auth::user()->name ?? '' }}
					@endif
				</span>
			</td>
			<td style="width: 20%;">
				<b>AYUDANTE</b><br>
				<span class="preop-input-line">
					@if($programacion->FK_ProgAyudante && $programacion->ayudante)
						{{ $programacion->ayudante->PersFirstName }} {{ $programacion->ayudante->PersLastName }}
					@endif
				</span>
			</td>
		</tr>
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<th class="preop-center" style="width: 50%;">DOCUMENTACIÓN GENERAL</th>
			<th class="preop-center" style="width: 50%;">TRANSPORTE DE MERCANCÍAS PELIGROSAS</th>
		</tr>
		<tr>
			<td>
				<table class="preop-table">
					<tr>
						<th>Ítem</th>
						<th class="preop-center" style="width: 30px;">SI</th>
						<th class="preop-center" style="width: 30px;">NO</th>
					</tr>
					@foreach($docs as $k => $label)
						@php $v = $set("documentacion.$k"); @endphp
						<tr>
							<td>{{ $label }}</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'SI' ? 'X' : '' }}</span>
							</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'NO' ? 'X' : '' }}</span>
							</td>
						</tr>
					@endforeach
				</table>

				<table class="preop-table" style="margin-top: 4px;">
					<tr>
						<th colspan="3" class="preop-center">EQUIPO DE CARRETERA</th>
					</tr>
					<tr>
						<th>Elemento</th>
						<th class="preop-center" style="width: 30px;">SI</th>
						<th class="preop-center" style="width: 30px;">NO</th>
					</tr>
					@foreach($equipo as $k => $label)
						@php $v = $set("equipo.$k"); @endphp
						<tr>
							<td>{{ $label }}</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'SI' ? 'X' : '' }}</span>
							</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'NO' ? 'X' : '' }}</span>
							</td>
						</tr>
					@endforeach
				</table>

				<table class="preop-table" style="margin-top: 4px;">
					<tr>
						<th colspan="3" class="preop-center">ESTADO DEL VEHÍCULO</th>
					</tr>
					<tr>
						<th>Condición</th>
						<th class="preop-center" style="width: 30px;">SI</th>
						<th class="preop-center" style="width: 30px;">NO</th>
					</tr>
					@foreach($estadoVeh as $k => $label)
						@php $v = $set("estado.$k"); @endphp
						<tr>
							<td>{{ $label }}</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'SI' ? 'X' : '' }}</span>
							</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'NO' ? 'X' : '' }}</span>
							</td>
						</tr>
					@endforeach
				</table>
			</td>
			<td>
				<table class="preop-table">
					<tr>
						<th>Pregunta / Verificación</th>
						<th class="preop-center" style="width: 30px;">SI</th>
						<th class="preop-center" style="width: 30px;">NO</th>
					</tr>
					@foreach($mp as $k => $label)
						@php $v = $set("mercancias_peligrosas.$k"); @endphp
						<tr>
							<td>{{ $label }}</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'SI' ? 'X' : '' }}</span>
							</td>
							<td class="preop-checkbox-cell">
								<span class="preop-checkbox-box">{{ $v === 'NO' ? 'X' : '' }}</span>
							</td>
						</tr>
					@endforeach
				</table>
			</td>
		</tr>
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<th colspan="3" class="preop-center">ELEMENTOS BÁSICOS PARA ATENCIÓN DE EMERGENCIAS</th>
		</tr>
		<tr>
			<th>Elemento</th>
			<th class="preop-center" style="width: 30px;">SI</th>
			<th class="preop-center" style="width: 30px;">NO</th>
		</tr>
		@foreach($emg as $k => $label)
			@php $v = $set("emergencias.$k"); @endphp
			<tr>
				<td>{{ $label }}</td>
				<td class="preop-checkbox-cell">
					<span class="preop-checkbox-box">{{ $v === 'SI' ? 'X' : '' }}</span>
				</td>
				<td class="preop-checkbox-cell">
					<span class="preop-checkbox-box">{{ $v === 'NO' ? 'X' : '' }}</span>
				</td>
			</tr>
		@endforeach
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<th colspan="2" class="preop-center">MAPA DEL VEHÍCULO (OBSERVACIONES VISUALES)</th>
		</tr>
		<tr>
			<td colspan="2">
				<table class="preop-table">
					<tr>
						<th style="width: 35%;">Zona</th>
						<th>Observación</th>
					</tr>
					@foreach($danos as $k => $label)
						@php
							$tipo = $set("danos.$k.tipo", '');
							$detalle = $set("danos.$k.detalle", '');
							$texto = trim(($tipo ? $tipo.': ' : '').($detalle ?? ''));
						@endphp
						<tr>
							<td>{{ $label }}</td>
							<td>{{ $texto !== '' ? $texto : 'Sin observaciones' }}</td>
						</tr>
					@endforeach
				</table>
			</td>
		</tr>
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<th style="width: 50%;">OBSERVACIONES</th>
			<th style="width: 25%;">KILOMETRAJE</th>
			<th style="width: 25%;">ESTADO GENERAL</th>
		</tr>
		<tr>
			<td class="observaciones-box">
				@if($programacion->ProgVehObsPreoperacional)
					{{ $programacion->ProgVehObsPreoperacional }}
				@endif
			</td>
			<td>
				<table class="preop-table">
					<tr>
						<td><b>Km inicial</b></td>
						<td class="preop-center">{{ $programacion->ProgVehKmInicial ?? 'N/A' }}</td>
					</tr>
					<tr>
						<td><b>Km final</b></td>
						<td class="preop-center">{{ $programacion->ProgVehKmFinal ?? 'N/A' }}</td>
					</tr>
					<tr>
						<td><b>Km recorrido</b></td>
						<td class="preop-center">
							@if($programacion->ProgVehKmInicial && $programacion->ProgVehKmFinal)
								{{ $programacion->ProgVehKmFinal - $programacion->ProgVehKmInicial }}
							@else
								N/A
							@endif
						</td>
					</tr>
				</table>
			</td>
			<td class="preop-center">
				{{ $programacion->ProgVehEstadoVehiculo ?? 'N/A' }}
			</td>
		</tr>
	</table>

	<table class="preop-table" style="margin-top: 4px;">
		<tr>
			<th colspan="3" class="preop-center">INSPECCIÓN REALIZADA POR / INSPECCIÓN ACOMPAÑADA POR</th>
		</tr>
		<tr>
			<td style="width: 50%;">
				<b>FIRMA (CONDUCTOR)</b>
				<div class="firma-box">
					@if($firmaBase64)
						<img src="{{ $firmaBase64 }}" alt="Firma del Conductor" class="firma-imagen" />
					@endif
				</div>
			</td>
			<td style="width: 25%;">
				<b>NOMBRE</b><br>
				<span class="preop-input-line">
					@if($conductor)
						{{ $conductor->PersFirstName }} {{ $conductor->PersLastName }}
					@else
						{{ \Illuminate\Support\Facades\Auth::user()->name ?? '' }}
					@endif
				</span>
				<br><br>
				<b>CARGO</b><br>
				<span class="preop-input-line">CONDUCTOR</span>
			</td>
			<td style="width: 25%;">
				<b>AYUDANTE</b><br>
				<span class="preop-input-line">
					@if($programacion->FK_ProgAyudante && $programacion->ayudante)
						{{ $programacion->ayudante->PersFirstName }} {{ $programacion->ayudante->PersLastName }}
					@endif
				</span>
				<br><br>
				<b>FIRMA</b><br>
				<span class="preop-input-line">&nbsp;</span>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
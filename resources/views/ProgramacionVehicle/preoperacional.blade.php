@extends('layouts.app')

@section('htmlheader_title','Preoperacional')

@section('main-content')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

@php
	// Prellenados para mostrar en el formato
	$fechaProg = $programacion->ProgVehFecha ? \Illuminate\Support\Carbon::parse($programacion->ProgVehFecha) : \Illuminate\Support\Carbon::today();
	$horaAhora = \Illuminate\Support\Carbon::now()->format('H:i');
	$placa = $vehiculo ? $vehiculo->VehicPlaca : ($programacion->SolSerVehiculo ?? '');
	$nombreConductor = $conductor ? trim(($conductor->PersFirstName ?? '').' '.($conductor->PersLastName ?? '')) : (Auth::user()->name ?? '');
	$nombreAyudante = $ayudante ? trim(($ayudante->PersFirstName ?? '').' '.($ayudante->PersLastName ?? '')) : '';
	$vehiculoSeleccionado = old('FK_ProgVehiculo', $programacion->FK_ProgVehiculo ?? null);
	$ayudanteSeleccionado = old('FK_ProgAyudante', $programacion->FK_ProgAyudante ?? null);

	$checks = $programacion->ProgVehCheckPreoperacional ? json_decode($programacion->ProgVehCheckPreoperacional, true) : [];
	$set = function ($path, $default = null) use ($checks) {
		$cur = $checks;
		foreach (explode('.', $path) as $p) {
			if (!is_array($cur) || !array_key_exists($p, $cur)) return $default;
			$cur = $cur[$p];
		}
		return $cur;
	};
	// Prefill desde datos del vehículo (documentación y extintor vigentes) y del conductor (licencia, cert. MP)
	$prefillDocumentacion = $prefillDocumentacion ?? [];
	$prefillEquipo = $prefillEquipo ?? [];
	$prefillMercanciasPeligrosas = $prefillMercanciasPeligrosas ?? [];
@endphp

<div class="container-fluid spark-screen">
	@if(session('warning'))
		<div class="alert alert-warning" style="margin-bottom: 15px;">
			{{ session('warning') }}
		</div>
	@endif
	@if(count($prefillDocumentacion) > 0 || count($prefillEquipo) > 0 || count($prefillMercanciasPeligrosas) > 0)
		<div class="alert alert-info" style="margin-bottom: 15px;">
			<i class="fa fa-info-circle"></i> <strong>Autollenado:</strong> Documentación, extintor y certificaciones se han prellenado según datos del vehículo y del conductor. Verifique que corresponda antes de guardar.
		</div>
	@endif
	<form id="preopForm" method="POST" action="{{ route('vehicle-programacion.store-preoperacional', $programacion->ID_ProgVeh) }}">
		@csrf

		<style>
			.preop-sheet { border: 1px solid #333; background: #fff; padding: 12px; }
			.preop-table { width: 100%; border-collapse: collapse; }
			.preop-table th, .preop-table td { border: 1px solid #333; padding: 6px; font-size: 12px; vertical-align: top; }
			.preop-table th { background: #f5f5f5; font-weight: 700; text-transform: uppercase; font-size: 11px; }
			.preop-head td { vertical-align: middle; }
			.preop-small { font-size: 11px; }
			.preop-center { text-align: center; }
			.preop-input { width: 100%; border: 0; border-bottom: 1px solid #999; outline: none; background: transparent; }
			.preop-select { width: 100%; border: 0; border-bottom: 1px solid #999; outline: none; background: transparent; padding: 0; height: 22px; }
			.preop-radio { transform: scale(1.1); }
			.preop-sign { border: 1px solid #333; width: 100%; height: 140px; touch-action: none; }
			.preop-muted { color: #666; }
		</style>

		<div class="preop-sheet">
			<table class="preop-table preop-head">
				<tr>
					<td style="width: 22%;">
						<b>PROSARC</b><br>
						<span class="preop-small">FORMATO INSPECCI&Oacute;N PREOPERACIONAL DE VEH&Iacute;CULOS</span>
					</td>
					<td style="width: 48%;" class="preop-center">
						<b>FORMATO INSPECCI&Oacute;N PREOPERACIONAL DE VEH&Iacute;CULOS</b><br>
						<span class="preop-small">SISTEMA INTEGRADO DE GESTI&Oacute;N</span>
					</td>
					<td style="width: 30%;" class="preop-small">
						<div><b>FORMATO:</b> FR-LG-003</div>
						<div><b>VERSI&Oacute;N:</b> 02</div>
						<div><b>FECHA:</b> ene-22</div>
					</td>
				</tr>
			</table>

			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<td style="width: 20%;"><b>FECHA</b><br><span class="preop-muted">DD/MM/AAAA</span>
						<input class="preop-input" type="text" value="{{ $fechaProg->format('d/m/Y') }}" readonly>
					</td>
					<td style="width: 15%;"><b>HORA</b><br>
						<input class="preop-input" type="text" value="{{ $horaAhora }}" readonly>
					</td>
					<td style="width: 25%;"><b>PLACA DEL VEH&Iacute;CULO</b><br>
						<select class="preop-select" name="FK_ProgVehiculo" id="FK_ProgVehiculo">
							<option value="">Seleccione...</option>
							@if(isset($vehiculosDisponibles) && count($vehiculosDisponibles))
								@foreach($vehiculosDisponibles as $v)
									@php
										$hoy = \Illuminate\Support\Carbon::today();
										$docTarj = $v->VehicTarjetaPropiedadVencimiento ? (\Carbon\Carbon::parse($v->VehicTarjetaPropiedadVencimiento)->greaterThanOrEqualTo($hoy) ? 'SI' : 'NO') : '';
										$docSoat = $v->VehicSoatVencimiento ? (\Carbon\Carbon::parse($v->VehicSoatVencimiento)->greaterThanOrEqualTo($hoy) ? 'SI' : 'NO') : '';
										$docTec = $v->VehicTecnomecanicaVencimiento ? (\Carbon\Carbon::parse($v->VehicTecnomecanicaVencimiento)->greaterThanOrEqualTo($hoy) ? 'SI' : 'NO') : '';
										$docPol = $v->VehicPolizaVencimiento ? (\Carbon\Carbon::parse($v->VehicPolizaVencimiento)->greaterThanOrEqualTo($hoy) ? 'SI' : 'NO') : '';
										$ext1V = $v->VehicExt1FechaRecarga ? \Carbon\Carbon::parse($v->VehicExt1FechaRecarga)->greaterThanOrEqualTo($hoy) : false;
										$ext2V = $v->VehicExt2FechaRecarga ? \Carbon\Carbon::parse($v->VehicExt2FechaRecarga)->greaterThanOrEqualTo($hoy) : false;
										$extVig = ($ext1V || $ext2V) ? 'SI' : (($v->VehicExt1FechaRecarga || $v->VehicExt2FechaRecarga) ? 'NO' : '');
									@endphp
									<option value="{{ $v->ID_Vehic }}" data-km="{{ $v->VehicKmActual ?? '' }}"
										data-doc-tarjeta="{{ $docTarj }}" data-doc-soat="{{ $docSoat }}" data-doc-tecnomecanica="{{ $docTec }}" data-doc-poliza="{{ $docPol }}"
										data-equipo-extintor="{{ $extVig }}"
										{{ (string)$vehiculoSeleccionado === (string)$v->ID_Vehic ? 'selected' : '' }}>
										{{ $v->VehicPlaca }}
									</option>
								@endforeach
							@endif
						</select>
						<div class="preop-small preop-muted" style="margin-top: 3px;">
							Km actual: <b id="vehiculoKmActual">N/A</b>
						</div>
						@if(!isset($vehiculosDisponibles) || !count($vehiculosDisponibles))
							<input class="preop-input" type="text" value="{{ $placa }}" readonly>
						@endif
					</td>
					<td style="width: 20%;"><b>CONDUCTOR</b><br>
						<input class="preop-input" type="text" value="{{ $nombreConductor }}" readonly>
					</td>
					<td style="width: 20%;"><b>AYUDANTE</b><br>
						<select class="preop-select" name="FK_ProgAyudante" id="FK_ProgAyudante">
							<option value="">Seleccione...</option>
							@if(isset($ayudantesDisponibles) && count($ayudantesDisponibles))
								@foreach($ayudantesDisponibles as $a)
									<option value="{{ $a->ID_Pers }}"
										{{ (string)$ayudanteSeleccionado === (string)$a->ID_Pers ? 'selected' : '' }}>
										{{ $a->PersFirstName }} {{ $a->PersLastName }}
									</option>
								@endforeach
							@endif
						</select>
					</td>
				</tr>
			</table>

			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<th class="preop-center" style="width: 50%;">DOCUMENTACI&Oacute;N GENERAL</th>
					<th class="preop-center" style="width: 50%;">TRANSPORTE DE MERCANC&Iacute;AS PELIGROSAS</th>
				</tr>
				<tr>
					<td>
						<table class="preop-table">
							<tr>
								<th>&Iacute;tem</th>
								<th class="preop-center" style="width: 45px;">SI</th>
								<th class="preop-center" style="width: 45px;">NO</th>
							</tr>
							@php
								$docs = [
									'tarjeta_propiedad' => 'Tarjeta de propiedad',
									'licencia' => 'Licencia de conducci&oacute;n del conductor',
									'soat' => 'SOAT vigente',
									'tecnomecanica' => 'Certificado de revisi&oacute;n t&eacute;cnico mec&aacute;nica vigente',
									'poliza_rc' => 'P&oacute;liza de responsabilidad civil extracontractual',
								];
							@endphp
							@foreach($docs as $k => $label)
								@php $v = $set("documentacion.$k") ?? ($prefillDocumentacion[$k] ?? null); @endphp
								<tr>
									<td>{!! $label !!}</td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[documentacion][{{ $k }}]" value="SI" {{ $v === 'SI' ? 'checked' : '' }} required></td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[documentacion][{{ $k }}]" value="NO" {{ $v === 'NO' ? 'checked' : '' }}></td>
								</tr>
							@endforeach
						</table>

						<table class="preop-table" style="margin-top: 8px;">
							<tr>
								<th colspan="3" class="preop-center">EQUIPO DE CARRETERA</th>
							</tr>
							<tr>
								<th>Elemento</th>
								<th class="preop-center" style="width: 45px;">SI</th>
								<th class="preop-center" style="width: 45px;">NO</th>
							</tr>
							@php
								$equipo = [
									'gato' => 'Gato con capacidad para elevar el veh&iacute;culo',
									'cruceta' => 'Cruceta',
									'senales' => 'Dos se&ntilde;ales de carretera en forma de tri&aacute;ngulo y con soporte para colocaci&oacute;n vertical',
									'botiquin' => 'Botiqu&iacute;n de primeros auxilios',
									'extintor' => 'Extintor con recarga vigente',
									'tacos' => 'Dos tacos para bloquear el veh&iacute;culo',
									'herramientas' => 'Caja de herramientas que contenga m&iacute;nimo: alicate, destornilladores, llave de expansion y llaves fijas',
									'llanta' => 'Llanta de repuesto en buen estado',
								];
							@endphp
							@foreach($equipo as $k => $label)
								@php $v = $set("equipo.$k") ?? ($prefillEquipo[$k] ?? null); @endphp
								<tr>
									<td>{!! $label !!}</td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[equipo][{{ $k }}]" value="SI" {{ $v === 'SI' ? 'checked' : '' }} required></td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[equipo][{{ $k }}]" value="NO" {{ $v === 'NO' ? 'checked' : '' }}></td>
								</tr>
							@endforeach
						</table>

						<table class="preop-table" style="margin-top: 8px;">
							<tr>
								<th colspan="3" class="preop-center">ESTADO DEL VEH&Iacute;CULO</th>
							</tr>
							<tr>
								<th>Condici&oacute;n</th>
								<th class="preop-center" style="width: 45px;">SI</th>
								<th class="preop-center" style="width: 45px;">NO</th>
							</tr>
							@php
								$estadoVeh = [
									'llantas' => 'Condiciones &oacute;ptimas de las llantas',
									'luces' => 'Correcto funcionamiento de las luces',
									'frenos' => 'Estado adecuado de frenos',
									'fugas' => 'Presencia de fugas',
									'direccion' => 'Correcto funcionamiento de la direcci&oacute;n',
									'refrigeracion' => 'El veh&iacute;culo posee sistema para refrigeraci&oacute;n de la carga',
								];
							@endphp
							@foreach($estadoVeh as $k => $label)
								@php $v = $set("estado.$k"); @endphp
								<tr>
									<td>{!! $label !!}</td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[estado][{{ $k }}]" value="SI" {{ $v === 'SI' ? 'checked' : '' }} required></td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[estado][{{ $k }}]" value="NO" {{ $v === 'NO' ? 'checked' : '' }}></td>
								</tr>
							@endforeach
						</table>
					</td>
					<td>
						<table class="preop-table">
							<tr>
								<th>Pregunta / Verificaci&oacute;n</th>
								<th class="preop-center" style="width: 45px;">SI</th>
								<th class="preop-center" style="width: 45px;">NO</th>
							</tr>
							@php
								$mp = [
									'transporta_mp' => '¿Se transportar&aacute;n mercanc&iacute;as peligrosas? <br><span class="preop-small">En caso afirmativo, verificar los elementos listados a continuaci&oacute;n.</span>',
									'numeros_un' => 'N&uacute;meros UN en todas las caras visibles de la unidad de transporte y rombos de sustancias peligrosas',
									'hojas_seguridad' => 'Hojas de Seguridad de los residuos transportados',
									'kit_multiproposito' => 'Kit multiprop&oacute;sito (con recarga vigente) en la cabina y de f&aacute;cil acceso',
									'extintor_mp' => 'Extintor multiprop&oacute;sito (con recarga vigente) cerca de la carga y de f&aacute;cil acceso',
									'dispositivo_sonoro' => 'Dispositivo sonoro o pito para movimiento de reversa',
									'tarjetas_emergencia' => 'Tarjetas de emergencia en idioma castellano acordes a los par&aacute;metros establecidos en la NTC 4532',
									'cert_conductor' => 'Certificaci&oacute;n o carnet del curso b&aacute;sico obligatorio de capacitaci&oacute;n del conductor para transporte de mercanc&iacute;as peligrosas',
									'plan_ruta' => 'Plan de transporte que contenga hora de salida del origen, hora de llegada al destino y ruta seleccionada',
									'listado_nos' => 'Listado con los tel&eacute;fonos para la atenci&oacute;n de emergencias',
									'comunicacion' => 'Sistema de comunicaci&oacute;n tal como: tel&eacute;fono celular, radiotel&eacute;fono, radio, entre otros',
									'recibo_rm' => 'Recibo de material',
									'carga_etiquetada' => 'La carga est&aacute; debidamente clasificada y etiquetada de acuerdo al decreto 1609 de 2002',
									'embalajes' => 'Los embalajes y envases cumplen con lo establecido en la NTC 4702 correspondiente a la clase de peligro de la sustancia a transportar',
									'sustancias_incompatibles' => 'Se transportan las sustancias qu&iacute;micas peligrosas agrupando las que tienen caracter&iacute;sticas y propiedades similares evitando incompatibilidades?',
									'dispositivo_descont' => 'El veh&iacute;culo cuenta con alg&uacute;n dispositivo para la ejecuci&oacute;n de la carga?',
								];
							@endphp
							@foreach($mp as $k => $label)
								@php $v = $set("mercancias_peligrosas.$k") ?? ($prefillMercanciasPeligrosas[$k] ?? null); @endphp
								<tr>
									<td>{!! $label !!}</td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[mercancias_peligrosas][{{ $k }}]" value="SI" {{ $v === 'SI' ? 'checked' : '' }} required></td>
									<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[mercancias_peligrosas][{{ $k }}]" value="NO" {{ $v === 'NO' ? 'checked' : '' }}></td>
								</tr>
							@endforeach
						</table>
					</td>
				</tr>
			</table>

			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<th colspan="3" class="preop-center">ELEMENTOS B&Aacute;SICOS PARA ATENCI&Oacute;N DE EMERGENCIAS</th>
				</tr>
				@php
					$emg = [
						'protectoras' => 'Ropa protectora',
						'linterna' => 'Linterna (verificar que funcione)',
						'equipo_recoleccion' => 'Equipo para recolecci&oacute;n y limpieza',
						'absorbente' => 'Material absorbente',
					];
				@endphp
				<tr>
					<th>Elemento</th>
					<th class="preop-center" style="width: 45px;">SI</th>
					<th class="preop-center" style="width: 45px;">NO</th>
				</tr>
				@foreach($emg as $k => $label)
					@php $v = $set("emergencias.$k"); @endphp
					<tr>
						<td>{!! $label !!}</td>
						<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[emergencias][{{ $k }}]" value="SI" {{ $v === 'SI' ? 'checked' : '' }} required></td>
						<td class="preop-center"><input class="preop-radio" type="radio" name="ProgVehCheckPreoperacional[emergencias][{{ $k }}]" value="NO" {{ $v === 'NO' ? 'checked' : '' }}></td>
					</tr>
				@endforeach
			</table>

			{{-- ================= MAPA DEL VEHÍCULO (OBSERVACIONES VISUALES) ================= --}}
			@php
				$danos = [
					'frente' => 'Frente',
					'costado_izquierdo' => 'Costado izquierdo',
					'costado_derecho' => 'Costado derecho',
					'trasera' => 'Trasera',
					'techo' => 'Techo',
				];
			@endphp
			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<th class="preop-center">MAPA DEL VEH&Iacute;CULO (OBSERVACIONES VISUALES)</th>
				</tr>
				<tr>
					<td>
						<div class="row">
							<div class="col-md-7">
								<style>
									.car-map { max-width: 520px; margin: 0 auto; }
									.car-zone { cursor: pointer; fill: #f7f7f7; stroke: #333; stroke-width: 2; }
									.car-zone:hover { fill: #e8f4ff; }
									.car-zone.has-dano { fill: #ffe5e5; stroke: #b30000; }
									.car-label { font-size: 11px; fill: #333; font-family: Arial, sans-serif; }
									.car-legend { font-size: 12px; }
								</style>

								<div class="car-map">
									<svg id="carMapSvg" viewBox="0 0 520 220" width="100%" height="220" aria-label="Mapa del camión">
										<!-- Silueta base (camión visto de arriba): cabina + caja -->
										<!-- Caja / furgón -->
										<rect x="155" y="45" width="290" height="130" rx="18" ry="18" fill="#fff" stroke="#333" stroke-width="3"></rect>
										<!-- Cabina -->
										<rect x="85" y="65" width="80" height="90" rx="18" ry="18" fill="#fff" stroke="#333" stroke-width="3"></rect>
										<!-- Parabrisas -->
										<rect x="95" y="75" width="60" height="22" rx="8" ry="8" fill="#eaf4ff" stroke="#333" stroke-width="2"></rect>
										<!-- Detalle puertas cabina -->
										<line x1="125" y1="105" x2="125" y2="150" stroke="#333" stroke-width="2"></line>

										<!-- Zonas clicables -->
										<!-- Frente (cabina/parachoques) -->
										<rect class="car-zone" data-zone="frente" x="85" y="65" width="80" height="28" rx="14"></rect>
										<!-- Trasera (puertas de la caja) -->
										<rect class="car-zone" data-zone="trasera" x="415" y="45" width="30" height="130" rx="10"></rect>
										<!-- Costado izquierdo (lateral caja) -->
										<rect class="car-zone" data-zone="costado_izquierdo" x="155" y="45" width="290" height="26" rx="12"></rect>
										<!-- Costado derecho (lateral caja) -->
										<rect class="car-zone" data-zone="costado_derecho" x="155" y="149" width="290" height="26" rx="12"></rect>
										<!-- Techo (superficie caja) -->
										<rect class="car-zone" data-zone="techo" x="175" y="75" width="220" height="70" rx="14"></rect>

										<!-- Labels -->
										<text class="car-label" x="125" y="85" text-anchor="middle">CABINA</text>
										<text class="car-label" x="300" y="62" text-anchor="middle">IZQ</text>
										<text class="car-label" x="300" y="166" text-anchor="middle">DER</text>
										<text class="car-label" x="300" y="115" text-anchor="middle">CAJA/TECHO</text>
										<text class="car-label" x="430" y="115" text-anchor="middle" transform="rotate(90 430 115)">TRASERA</text>
									</svg>
								</div>
							</div>
							<div class="col-md-5">
								<p class="car-legend" style="margin-bottom: 6px;">
									<b>Instrucciones:</b> toque una zona del veh&iacute;culo y registre la observaci&oacute;n (ej: <i>ray&oacute;n costado derecho</i>).
								</p>
								<ul style="margin-bottom: 0;">
									@foreach($danos as $k => $label)
										@php
											$tipo = $set("danos.$k.tipo", '');
											$detalle = $set("danos.$k.detalle", '');
											$texto = trim(($tipo ? $tipo.': ' : '').($detalle ?? ''));
										@endphp
										<li>
											<b>{{ $label }}:</b>
											<span id="danoResumen_{{ $k }}">{{ $texto !== '' ? $texto : 'Sin observaciones' }}</span>
										</li>
									@endforeach
								</ul>
							</div>
						</div>

						{{-- Hidden inputs para persistir por zona --}}
						@foreach($danos as $k => $label)
							<input type="hidden" id="dano_tipo_{{ $k }}" name="ProgVehCheckPreoperacional[danos][{{ $k }}][tipo]" value="{{ $set("danos.$k.tipo", '') }}">
							<input type="hidden" id="dano_detalle_{{ $k }}" name="ProgVehCheckPreoperacional[danos][{{ $k }}][detalle]" value="{{ $set("danos.$k.detalle", '') }}">
						@endforeach
					</td>
				</tr>
			</table>

			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<th style="width: 50%;">OBSERVACIONES</th>
					<th style="width: 25%;">KILOMETRAJE</th>
					<th style="width: 25%;">ESTADO GENERAL</th>
				</tr>
				<tr>
					<td>
						<textarea name="ProgVehObsPreoperacional" class="form-control" rows="5" maxlength="2000" placeholder="Observaciones...">{{ old('ProgVehObsPreoperacional', $programacion->ProgVehObsPreoperacional) }}</textarea>
					</td>
					<td>
						<div class="form-group">
							<label>Km inicial <span class="text-danger">*</span></label>
							@php
								$kmInicialPref = old('ProgVehKmInicial', $programacion->ProgVehKmInicial ?? ($vehiculo->VehicKmActual ?? ''));
							@endphp
							<input type="number" name="ProgVehKmInicial" id="ProgVehKmInicial" class="form-control" min="0" required value="{{ $kmInicialPref }}">
						</div>
						<div class="form-group" style="margin-bottom: 0;">
							<label>Km final</label>
							<input type="number" name="ProgVehKmFinal" class="form-control" min="0" value="{{ old('ProgVehKmFinal', $programacion->ProgVehKmFinal) }}">
						</div>
					</td>
					<td>
						<div class="form-group" style="margin-bottom: 0;">
							<label>Estado del veh&iacute;culo <span class="text-danger">*</span></label>
							<select name="ProgVehEstadoVehiculo" class="form-control" required>
								@php $estadoSel = old('ProgVehEstadoVehiculo', $programacion->ProgVehEstadoVehiculo ?: 'Funcional'); @endphp
								<option value="Funcional" {{ $estadoSel === 'Funcional' ? 'selected' : '' }}>Funcional</option>
								<option value="Requiere Mantenimiento" {{ $estadoSel === 'Requiere Mantenimiento' ? 'selected' : '' }}>Requiere Mantenimiento</option>
								<option value="No Funcional" {{ $estadoSel === 'No Funcional' ? 'selected' : '' }}>No Funcional</option>
							</select>
						</div>
					</td>
				</tr>
			</table>

			<table class="preop-table" style="margin-top: 8px;">
				<tr>
					<th colspan="3" class="preop-center">INSPECCI&Oacute;N REALIZADA POR / INSPECCI&Oacute;N ACOMPA&Ntilde;ADA POR</th>
				</tr>
				<tr>
					<td style="width: 50%;">
						<b>FIRMA (CONDUCTOR)</b>
						<input type="hidden" name="ProgVehFirmaConductor" id="ProgVehFirmaConductor" value="">
						<canvas id="sigCanvas" class="preop-sign"></canvas>
						<div style="margin-top: 6px;">
							<button type="button" class="btn btn-default btn-sm" id="sigClear"><i class="fas fa-eraser"></i> Limpiar</button>
							<span class="preop-muted preop-small" style="margin-left: 8px;">Debe firmar para guardar.</span>
						</div>
					</td>
					<td style="width: 25%;">
						<b>NOMBRE</b><br>
						<input class="preop-input" type="text" value="{{ $nombreConductor }}" readonly>
						<br><br>
						<b>CARGO</b><br>
						<input class="preop-input" type="text" value="CONDUCTOR" readonly>
					</td>
					<td style="width: 25%;">
						<b>AYUDANTE</b><br>
						<input class="preop-input" id="AyudanteNombrePreview" type="text" value="{{ $nombreAyudante }}" readonly>
						<br><br>
						<b>FIRMA</b><br>
						<input class="preop-input" type="text" value="" placeholder="(Opcional)">
					</td>
				</tr>
			</table>

			<div style="margin-top: 10px;" class="text-center">
				<button type="submit" class="btn btn-primary btn-lg">
					<i class="fas fa-save"></i> Guardar preoperacional
				</button>
				<a href="{{ route('vehicle-programacion.index') }}" class="btn btn-default btn-lg">
					<i class="fas fa-arrow-left"></i> Volver
				</a>
			</div>
		</div>
	</form>
</div>

{{-- Modal observación por zona del vehículo --}}
<div class="modal fade" id="danoVehiculoModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><b>Observaci&oacute;n veh&iacute;culo</b></h4>
			</div>
			<div class="modal-body">
				<input type="hidden" id="danoZonaKey" value="">
				<div class="form-group">
					<label>Zona</label>
					<input type="text" class="form-control" id="danoZonaLabel" readonly>
				</div>
				<div class="form-group">
					<label>Tipo</label>
					<select class="form-control" id="danoTipoSelect">
						<option value="">Seleccione...</option>
						<option value="Ray&oacute;n">Ray&oacute;n</option>
						<option value="Golpe">Golpe</option>
						<option value="Abolladura">Abolladura</option>
						<option value="Fisura">Fisura</option>
						<option value="Otro">Otro</option>
					</select>
				</div>
				<div class="form-group">
					<label>Detalle</label>
					<textarea class="form-control" id="danoDetalleText" rows="3" maxlength="300" placeholder="Ej: Ray&oacute;n leve en la puerta derecha."></textarea>
					<small class="text-muted">M&aacute;x 300 caracteres.</small>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" id="danoLimpiar"> Marcar como “sin observaciones” (limpiar)
					</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				<button type="button" class="btn btn-primary" id="danoGuardarBtn"><i class="fas fa-save"></i> Guardar</button>
			</div>
		</div>
	</div>
</div>

@endsection

@section('NewScript')
<script>
	(function () {
		var canvas = document.getElementById('sigCanvas');
		if (!canvas) return;
		// Evitar scroll/zoom al firmar en tablet
		canvas.style.touchAction = 'none';

		// Autollenado: Km inicial y documentación desde datos del vehículo seleccionado
		function updateKmActualUI() {
			var selectVeh = document.getElementById('FK_ProgVehiculo');
			var kmLabel = document.getElementById('vehiculoKmActual');
			if (!selectVeh || !kmLabel) return;

			var opt = selectVeh.options[selectVeh.selectedIndex];
			var km = opt && opt.getAttribute('data-km') ? opt.getAttribute('data-km') : '';
			kmLabel.textContent = km !== '' ? km : 'N/A';

			var kmInicialInput = document.getElementById('ProgVehKmInicial');
			if (kmInicialInput && (kmInicialInput.value === '' || kmInicialInput.value === null)) {
				if (km !== '') kmInicialInput.value = km;
			}

			// Actualizar documentación y extintor según vehículo seleccionado
			if (opt && opt.value) {
				var docs = ['tarjeta_propiedad','soat','tecnomecanica','poliza_rc'];
				var docAttrs = {'tarjeta_propiedad':'data-doc-tarjeta','soat':'data-doc-soat','tecnomecanica':'data-doc-tecnomecanica','poliza_rc':'data-doc-poliza'};
				docs.forEach(function(k){
					var val = opt.getAttribute(docAttrs[k]);
					if (val) {
						var radio = document.querySelector('input[name="ProgVehCheckPreoperacional[documentacion]['+k+']"][value="'+val+'"]');
						if (radio) radio.checked = true;
					}
				});
				var extVal = opt.getAttribute('data-equipo-extintor');
				if (extVal) {
					var extRadio = document.querySelector('input[name="ProgVehCheckPreoperacional[equipo][extintor]"][value="'+extVal+'"]');
					if (extRadio) extRadio.checked = true;
				}
			}
		}
		var selectVehInit = document.getElementById('FK_ProgVehiculo');
		if (selectVehInit) {
			selectVehInit.addEventListener('change', updateKmActualUI);
			// Inicial
			setTimeout(updateKmActualUI, 50);
		}

		// Ajustar tamaño real del canvas
		function resizeCanvas() {
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			var rect = canvas.getBoundingClientRect();
			canvas.width = rect.width * ratio;
			canvas.height = rect.height * ratio;
			var ctx = canvas.getContext('2d');
			ctx.setTransform(1, 0, 0, 1, 0, 0);
			ctx.scale(ratio, ratio);
		}
		resizeCanvas();
		window.addEventListener('resize', function(){ setTimeout(resizeCanvas, 150); });

		// signature_pad UMD expone "SignaturePad" como global
		if (!window.SignaturePad) {
			console.error('SignaturePad no está disponible');
			return;
		}
		var signaturePad = new window.SignaturePad(canvas, {
			backgroundColor: 'rgb(255,255,255)'
		});

		// En tablets algunos navegadores hacen scroll en vez de firmar
		['touchstart','touchmove','touchend'].forEach(function(evt){
			canvas.addEventListener(evt, function(e){ e.preventDefault(); }, { passive: false });
		});

		var btnClear = document.getElementById('sigClear');
		if (btnClear) {
			btnClear.addEventListener('click', function () {
				signaturePad.clear();
			});
		}

		var form = document.getElementById('preopForm');
		form.addEventListener('submit', function (e) {
			if (signaturePad.isEmpty()) {
				e.preventDefault();
				alert('Debe firmar el formulario para poder guardarlo.');
				return false;
			}
			document.getElementById('ProgVehFirmaConductor').value = signaturePad.toDataURL('image/png');
		});

		// Autollenado/preview del ayudante seleccionado
		function updateAyudantePreview() {
			var sel = document.getElementById('FK_ProgAyudante');
			var preview = document.getElementById('AyudanteNombrePreview');
			if (!sel || !preview) return;
			var text = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
			preview.value = (sel.value && text) ? text : '';
		}
		var selAy = document.getElementById('FK_ProgAyudante');
		if (selAy) {
			selAy.addEventListener('change', updateAyudantePreview);
			setTimeout(updateAyudantePreview, 50);
		}

		// ===== Mapa del vehículo (daños/observaciones por zona) =====
		var zoneLabels = {
			frente: 'Frente',
			costado_izquierdo: 'Costado izquierdo',
			costado_derecho: 'Costado derecho',
			trasera: 'Trasera',
			techo: 'Techo'
		};

		function getVal(id) {
			var el = document.getElementById(id);
			return el ? (el.value || '') : '';
		}
		function setVal(id, v) {
			var el = document.getElementById(id);
			if (el) el.value = v || '';
		}
		function updateZoneUI(zoneKey) {
			var tipo = getVal('dano_tipo_' + zoneKey);
			var detalle = getVal('dano_detalle_' + zoneKey);
			var text = (tipo ? (tipo + ': ') : '') + (detalle || '');
			text = text.trim();

			var resumen = document.getElementById('danoResumen_' + zoneKey);
			if (resumen) resumen.textContent = text !== '' ? text : 'Sin observaciones';

			var zoneEl = document.querySelector('.car-zone[data-zone="' + zoneKey + '"]');
			if (zoneEl) {
				if (text !== '') zoneEl.classList.add('has-dano');
				else zoneEl.classList.remove('has-dano');
			}
		}

		// Inicial: marcar zonas que ya tengan texto
		Object.keys(zoneLabels).forEach(function(k){ updateZoneUI(k); });

		function openDanoModal(zoneKey) {
			document.getElementById('danoZonaKey').value = zoneKey;
			document.getElementById('danoZonaLabel').value = zoneLabels[zoneKey] || zoneKey;
			document.getElementById('danoTipoSelect').value = getVal('dano_tipo_' + zoneKey);
			document.getElementById('danoDetalleText').value = getVal('dano_detalle_' + zoneKey);
			document.getElementById('danoLimpiar').checked = false;
			$('#danoVehiculoModal').modal('show');
		}

		var carSvg = document.getElementById('carMapSvg');
		if (carSvg) {
			carSvg.querySelectorAll('.car-zone').forEach(function(el){
				el.addEventListener('click', function(){
					var zoneKey = el.getAttribute('data-zone');
					if (zoneKey) openDanoModal(zoneKey);
				});
			});
		}

		var btnGuardar = document.getElementById('danoGuardarBtn');
		if (btnGuardar) {
			btnGuardar.addEventListener('click', function(){
				var zoneKey = document.getElementById('danoZonaKey').value;
				if (!zoneKey) return;

				if (document.getElementById('danoLimpiar').checked) {
					setVal('dano_tipo_' + zoneKey, '');
					setVal('dano_detalle_' + zoneKey, '');
				} else {
					setVal('dano_tipo_' + zoneKey, document.getElementById('danoTipoSelect').value);
					setVal('dano_detalle_' + zoneKey, document.getElementById('danoDetalleText').value);
				}

				updateZoneUI(zoneKey);
				$('#danoVehiculoModal').modal('hide');
			});
		}

	})();
</script>
@endsection

@extends('layouts.app')

@section('htmlheader_title')
{{__('adminlte::message.vehicletitle')}} - Informe Gerencial
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	<i class="fas fa-truck"></i> {{ __('adminlte::message.vehicletitle') }} - Informe Gerencial
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
	<!-- Filtros de Fecha -->
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title"><i class="fa fa-filter"></i> Filtros de Fecha</h3>
				</div>
				<div class="box-body">
					<form method="GET" action="/vehicle" class="form-inline">
						<div class="form-group">
							<label for="fecha_inicio">Fecha Inicio:</label>
							<input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}" required>
						</div>
						<div class="form-group" style="margin-left: 15px;">
							<label for="fecha_fin">Fecha Fin:</label>
							<input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}" required>
						</div>
						<button type="submit" class="btn btn-primary" style="margin-left: 15px;">
							<i class="fa fa-search"></i> Filtrar
						</button>
						<a href="/vehicle" class="btn btn-default" style="margin-left: 10px;">
							<i class="fa fa-refresh"></i> Limpiar
						</a>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fas fa-truck"></i> Informe Gerencial de Vehículos
					</h3>
					<div class="box-tools pull-right">
						@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1))
						<a href="/vehicle/create" class="btn btn-success btn-sm">
							<i class="fa fa-plus"></i> {{__('adminlte::message.create')}}
						</a>
						@endif
						<div class="has-feedback" style="margin-left: 10px; display: inline-block;">
							<input type="text" class="form-control input-sm" placeholder="Buscar vehículo por placa..." id="searchVehiculos">
							<span class="fa fa-search form-control-feedback"></span>
						</div>
					</div>
				</div>
				<div class="box-body">
					<!-- Gráficos Resumen -->
					<div class="row" style="margin-bottom: 20px;">
						<div class="col-md-6">
							<div class="box box-info">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-chart-bar"></i> Total Kilos Transportados por Vehículo</h3>
								</div>
								<div class="box-body">
									<canvas id="chartKilos" style="height: 300px;"></canvas>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="box box-success">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-chart-pie"></i> Total Servicios por Vehículo</h3>
								</div>
								<div class="box-body">
									<canvas id="chartServicios" style="height: 300px;"></canvas>
								</div>
							</div>
						</div>
					</div>
					<div class="row" style="margin-bottom: 20px;">
						<div class="col-md-6">
							<div class="box box-warning">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-route"></i> Kilómetros Recorridos por Vehículo</h3>
								</div>
								<div class="box-body">
									<canvas id="chartKmRecorridos" style="height: 300px;"></canvas>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="box box-danger">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-gas-pump"></i> Combustible Consumido por Vehículo (gal)</h3>
								</div>
								<div class="box-body">
									<canvas id="chartCombustible" style="height: 300px;"></canvas>
								</div>
							</div>
						</div>
					</div>

					<!-- Botón Exportar Excel -->
					<div class="row" style="margin-bottom: 15px;">
						<div class="col-md-12">
							<a href="/vehicle/export-excel?fecha_inicio={{ $fechaInicio }}&fecha_fin={{ $fechaFin }}" class="btn btn-success">
								<i class="fa fa-file-excel"></i> Exportar a Excel
							</a>
						</div>
					</div>

					<div class="row" id="vehiculosContainer">
						@php
							$colores = [
								['#667eea', '#764ba2'], // Morado
								['#f093fb', '#f5576c'], // Rosa-Rojo
								['#4facfe', '#00f2fe'], // Azul claro
								['#43e97b', '#38f9d7'], // Verde-Azul
								['#fa709a', '#fee140'], // Rosa-Amarillo
								['#30cfd0', '#330867'], // Cian-Oscuro
								['#a8edea', '#fed6e3'], // Verde claro-Rosa
								['#ff9a9e', '#fecfef'], // Rosa claro
								['#ffecd2', '#fcb69f'], // Naranja claro
								['#ff6e7f', '#bfe9ff'], // Rojo-Azul
							];
							$colorIndex = 0;
						@endphp
						@foreach($Vehicles as $Vehicle)
							@if($Vehicle->VehicDelete == 0)
							@php
								$colorActual = $colores[$colorIndex % count($colores)];
								$colorIndex++;
							@endphp
							<div class="col-md-4 col-sm-6 col-xs-12 vehiculo-card" data-placa="{{ strtolower($Vehicle->VehicPlaca) }}" style="margin-bottom: 20px;">
								<div class="box box-widget widget-user-2">
									<div class="widget-user-header vehiculo-header"
										 data-color-start="{{ $colorActual[0] }}"
										 data-color-end="{{ $colorActual[1] }}">
										<div class="widget-user-image">
											<span class="fa-stack fa-2x">
												<i class="fa fa-circle fa-stack-2x" style="color: rgba(255,255,255,0.3);"></i>
												<i class="fa fa-truck fa-stack-1x"></i>
											</span>
										</div>
										<h3 class="widget-user-username" style="margin: 0; font-size: 18px; font-weight: bold;">
											{{ $Vehicle->VehicPlaca }}
										</h3>
										<h5 class="widget-user-desc" style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.9;">
											{{ $Vehicle->VehicTipo }} - {{ $Vehicle->VehicCapacidad }} kg
										</h5>
									</div>
									<div class="box-footer no-padding">
										<ul class="nav nav-stacked">
											<li>
												<a href="#">
													<i class="fas fa-tachometer-alt text-info"></i> KM Actual
													<span class="pull-right badge bg-blue">{{ number_format($Vehicle->VehicKmActual, 0, ',', '.') }} km</span>
												</a>
											</li>
											<li>
												<a href="#">
													<i class="fas fa-clipboard-list text-success"></i> Total Servicios
													<span class="pull-right badge bg-green">{{ $Vehicle->total_servicios ?? 0 }}</span>
												</a>
											</li>
											<li>
												<a href="#">
													<i class="fas fa-weight text-warning"></i> Total Kilos Transportados
													<span class="pull-right badge bg-yellow">{{ number_format($Vehicle->total_kilos ?? 0, 2, ',', '.') }} kg</span>
												</a>
											</li>
											<li>
												<a href="#">
													<i class="fas fa-route text-danger"></i> Total KM Recorridos
													<span class="pull-right badge bg-red">{{ number_format($Vehicle->total_km_recorridos ?? 0, 0, ',', '.') }} km</span>
												</a>
											</li>
											<li>
												<a href="#">
													<i class="fa fa-gas-pump text-muted"></i> Combustible (gal)
													<span class="pull-right badge bg-gray">{{ number_format($Vehicle->total_combustible ?? 0, 2, ',', '.') }}</span>
												</a>
											</li>
											@if(isset($Vehicle->VehicSoatVencimiento) || isset($Vehicle->VehicTecnomecanicaVencimiento) || isset($Vehicle->VehicPolizaVencimiento) || isset($Vehicle->VehicTarjetaPropiedadVencimiento) || isset($Vehicle->VehicExtintor1Vencimiento) || isset($Vehicle->VehicExtintor2Vencimiento))
											<li class="padding-top-10" style="border-top: 1px solid #f4f4f4; margin-top: 5px; padding-top: 8px;">
												<small class="text-muted"><i class="fa fa-file-alt"></i> Documentos</small>
												@if(!empty($Vehicle->VehicSoatVencimiento))
													@php $v = strtotime($Vehicle->VehicSoatVencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">SOAT</span> {{ date('d/m/Y', $v) }}</div>
												@endif
												@if(!empty($Vehicle->VehicTecnomecanicaVencimiento))
													@php $v = strtotime($Vehicle->VehicTecnomecanicaVencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">Tecnom.</span> {{ date('d/m/Y', $v) }}</div>
												@endif
												@if(!empty($Vehicle->VehicPolizaVencimiento))
													@php $v = strtotime($Vehicle->VehicPolizaVencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">Póliza</span> {{ date('d/m/Y', $v) }}</div>
												@endif
												@if(!empty($Vehicle->VehicTarjetaPropiedadVencimiento))
													@php $v = strtotime($Vehicle->VehicTarjetaPropiedadVencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">Tarj. prop.</span> {{ date('d/m/Y', $v) }}</div>
												@endif
												@if(!empty($Vehicle->VehicExtintor1Vencimiento))
													@php $v = strtotime($Vehicle->VehicExtintor1Vencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">Ext. 1</span> {{ date('d/m/Y', $v) }}</div>
												@endif
												@if(!empty($Vehicle->VehicExtintor2Vencimiento))
													@php $v = strtotime($Vehicle->VehicExtintor2Vencimiento); $hoy = strtotime('today'); $clase = $v < $hoy ? 'danger' : ($v < $hoy + 30*86400 ? 'warning' : 'default'); @endphp
													<div class="small"><span class="badge bg-{{ $clase }}">Ext. 2</span> {{ date('d/m/Y', $v) }}</div>
												@endif
											</li>
											@endif
										</ul>
									</div>
									<!-- Detalles Diarios -->
									<div class="box-body" style="padding: 10px; background-color: #f9f9f9;">
										<h5 style="margin-top: 0; color: #666;"><i class="fa fa-calendar-day"></i> Resumen Diario</h5>
										
										<!-- Servicios por Transportador -->
										@if(isset($Vehicle->servicios_por_transportador) && count($Vehicle->servicios_por_transportador) > 0)
										<div style="margin-bottom: 10px;">
											<strong><i class="fa fa-users text-blue"></i> Transportadores:</strong>
											<ul style="margin: 5px 0; padding-left: 20px;">
												@foreach($Vehicle->servicios_por_transportador->take(3) as $trans)
													<li style="font-size: 11px;">
														{{ $trans->nombre_conductor }} {{ $trans->apellido_conductor }}
														@if($trans->tipo_transportador == 'Interno')
															<span class="badge bg-green">{{ $trans->cantidad_servicios }} servicios</span>
														@else
															<span class="badge bg-orange">{{ $trans->cantidad_servicios }} servicios</span>
														@endif
													</li>
												@endforeach
												@if(count($Vehicle->servicios_por_transportador) > 3)
													<li style="font-size: 11px; color: #999;">+{{ count($Vehicle->servicios_por_transportador) - 3 }} más...</li>
												@endif
											</ul>
										</div>
										@endif
										
										<!-- Kilos por Día (últimos 5 días) -->
										@if(isset($Vehicle->kilos_por_dia) && count($Vehicle->kilos_por_dia) > 0)
										<div style="margin-bottom: 10px;">
											<strong><i class="fa fa-weight text-green"></i> Kilos Diarios (últimos días):</strong>
											<ul style="margin: 5px 0; padding-left: 20px;">
												@foreach($Vehicle->kilos_por_dia->take(5) as $kilos)
													<li style="font-size: 11px;">
														{{ date('d/m/Y', strtotime($kilos->fecha)) }}: 
														<strong>{{ number_format($kilos->total_kilos, 2, ',', '.') }} kg</strong>
													</li>
												@endforeach
											</ul>
										</div>
										@endif
										
										<!-- Kilometraje Diario (últimos 5 días) -->
										@if(isset($Vehicle->kilometraje_diario) && count($Vehicle->kilometraje_diario) > 0)
										<div>
											<strong><i class="fa fa-road text-yellow"></i> KM Diarios (últimos días):</strong>
											<ul style="margin: 5px 0; padding-left: 20px;">
												@foreach($Vehicle->kilometraje_diario->take(5) as $km)
													<li style="font-size: 11px;">
														{{ date('d/m/Y', strtotime($km->fecha)) }}: 
														<strong>{{ number_format($km->km_recorridos, 0, ',', '.') }} km</strong>
													</li>
												@endforeach
											</ul>
										</div>
										@endif
										
										@if((!isset($Vehicle->servicios_por_transportador) || count($Vehicle->servicios_por_transportador) == 0) && 
											(!isset($Vehicle->kilos_por_dia) || count($Vehicle->kilos_por_dia) == 0) && 
											(!isset($Vehicle->kilometraje_diario) || count($Vehicle->kilometraje_diario) == 0))
											<p class="text-muted" style="font-size: 11px; margin: 0;">
												<i class="fa fa-info-circle"></i> No hay datos registrados en el período seleccionado.
											</p>
										@endif
									</div>
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) || in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo))
									<div class="box-footer">
										@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1))
										<a href='/vehicle/{{$Vehicle->VehicPlaca}}/edit' class='btn btn-warning btn-block btn-sm'>
											<i class="fas fa-edit"></i> Editar Vehículo
										</a>
										<a href="{{ route('vehicle.elementos-ley.edit', $Vehicle->VehicPlaca) }}" class='btn btn-info btn-block btn-sm' style="margin-top: 5px;">
											<i class="fa fa-gavel"></i> Elementos de ley
										</a>
										@endif
										@if(in_array(Auth::user()->UsRol, Permisos::CombustibleVehiculo) || in_array(Auth::user()->UsRol2, Permisos::CombustibleVehiculo))
										<a href="{{ route('vehicle.combustible.create', $Vehicle->VehicPlaca) }}" class='btn btn-default btn-block btn-sm' style="margin-top: 5px;">
											<i class="fa fa-gas-pump"></i> Registrar combustible
										</a>
										<a href="{{ route('vehicle.combustible.index', $Vehicle->VehicPlaca) }}" class='btn btn-default btn-block btn-sm' style="margin-top: 5px;">
											<i class="fa fa-list"></i> Historial combustible
										</a>
										@endif
									</div>
									@endif
								</div>
							</div>
							@endif
						@endforeach
					</div>
					
					@if($Vehicles->where('VehicDelete', 0)->count() == 0)
						<div class="text-center" style="padding: 60px;">
							<i class="fas fa-truck fa-4x text-muted"></i>
							<h3 style="color: #999; margin-top: 20px;">No hay vehículos disponibles</h3>
							<p style="color: #999;">No se encontraron vehículos activos en el período seleccionado</p>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

@php
	$vehiculosDataArray = $Vehicles->where('VehicDelete', 0)->map(function($v) {
		return [
			'placa' => $v->VehicPlaca,
			'kilos' => $v->total_kilos ?? 0,
			'servicios' => $v->total_servicios ?? 0,
			'km_recorridos' => $v->total_km_recorridos ?? 0,
			'combustible' => $v->total_combustible ?? 0
		];
	})->values();
	$vehiculosDataJson = json_encode($vehiculosDataArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
var vehiculosDataRaw = "<?php echo addslashes($vehiculosDataJson); ?>";
$(document).ready(function() {
	// Aplicar gradientes dinámicos a los headers de los vehículos
	$('.vehiculo-header').each(function() {
		var start = $(this).data('color-start');
		var end = $(this).data('color-end');
		if (start && end) {
			$(this).css({
				'background-image': 'linear-gradient(135deg, ' + start + ' 0%, ' + end + ' 100%)',
				'color': '#ffffff'
			});
		}
	});

	$('#searchVehiculos').on('keyup', function() {
		var value = $(this).val().toLowerCase();
		$('.vehiculo-card').filter(function() {
			$(this).toggle($(this).data('placa').indexOf(value) > -1);
		});
	});

	// Datos para gráficos
	var vehiculosData = JSON.parse(vehiculosDataRaw);
	
	if (!vehiculosData || vehiculosData.length === 0) {
		vehiculosData = [];
	}

	// Gráfico de Kilos
	var ctxKilos = document.getElementById('chartKilos');
	var totalKilos = vehiculosData.reduce(function(acc, v) {
		return acc + parseFloat(v.kilos || 0);
	}, 0);
	if (ctxKilos && totalKilos > 0) {
		ctxKilos = ctxKilos.getContext('2d');
		var chartKilos = new Chart(ctxKilos, {
			type: 'bar',
			data: {
				labels: vehiculosData.map(function(v) { return v.placa; }),
				datasets: [{
					label: 'Kilos Transportados (kg)',
					data: vehiculosData.map(function(v) { return parseFloat(v.kilos); }),
					backgroundColor: 'rgba(54, 162, 235, 0.6)',
					borderColor: 'rgba(54, 162, 235, 1)',
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return value.toLocaleString('es-CO') + ' kg';
							}
						}
					}
				},
				plugins: {
					legend: {
						display: true
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Kilos: ' + parseFloat(context.parsed.y).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' kg';
							}
						}
					}
				}
			}
		});
	}

	// Gráfico de Servicios
	var ctxServicios = document.getElementById('chartServicios');
	var totalServicios = vehiculosData.reduce(function(acc, v) {
		return acc + parseInt(v.servicios || 0);
	}, 0);
	if (ctxServicios && totalServicios > 0) {
		ctxServicios = ctxServicios.getContext('2d');
		var chartServicios = new Chart(ctxServicios, {
			type: 'doughnut',
			data: {
				labels: vehiculosData.map(function(v) { return v.placa; }),
				datasets: [{
					label: 'Servicios',
					data: vehiculosData.map(function(v) { return parseInt(v.servicios); }),
					backgroundColor: [
						'rgba(255, 99, 132, 0.6)',
						'rgba(54, 162, 235, 0.6)',
						'rgba(255, 206, 86, 0.6)',
						'rgba(75, 192, 192, 0.6)',
						'rgba(153, 102, 255, 0.6)',
						'rgba(255, 159, 64, 0.6)',
						'rgba(199, 199, 199, 0.6)',
						'rgba(83, 102, 255, 0.6)',
						'rgba(255, 99, 255, 0.6)',
						'rgba(99, 255, 132, 0.6)'
					],
					borderColor: [
						'rgba(255, 99, 132, 1)',
						'rgba(54, 162, 235, 1)',
						'rgba(255, 206, 86, 1)',
						'rgba(75, 192, 192, 1)',
						'rgba(153, 102, 255, 1)',
						'rgba(255, 159, 64, 1)',
						'rgba(199, 199, 199, 1)',
						'rgba(83, 102, 255, 1)',
						'rgba(255, 99, 255, 1)',
						'rgba(99, 255, 132, 1)'
					],
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							font: {
								size: 10
							}
						}
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								var label = context.label || '';
								var value = context.parsed || 0;
								var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
								var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
								return label + ': ' + value + ' servicios (' + percentage + '%)';
							}
						}
					}
				}
			}
		});	
	}

	// Gráfico Kilómetros Recorridos
	var ctxKm = document.getElementById('chartKmRecorridos');
	var totalKm = vehiculosData.reduce(function(acc, v) {
		return acc + parseFloat(v.km_recorridos || 0);
	}, 0);
	if (ctxKm) {
		ctxKm = ctxKm.getContext('2d');
		new Chart(ctxKm, {
			type: 'bar',
			data: {
				labels: vehiculosData.map(function(v) { return v.placa; }),
				datasets: [{
					label: 'KM Recorridos',
					data: vehiculosData.map(function(v) { return parseFloat(v.km_recorridos || 0); }),
					backgroundColor: 'rgba(255, 159, 64, 0.6)',
					borderColor: 'rgba(255, 159, 64, 1)',
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return value.toLocaleString('es-CO') + ' km';
							}
						}
					}
				},
				plugins: {
					legend: { display: true },
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'KM: ' + parseFloat(context.parsed.y).toLocaleString('es-CO', {maximumFractionDigits: 0}) + ' km';
							}
						}
					}
				}
			}
		});
	}

	// Gráfico Combustible Consumido
	var ctxCombustible = document.getElementById('chartCombustible');
	if (ctxCombustible) {
		ctxCombustible = ctxCombustible.getContext('2d');
		new Chart(ctxCombustible, {
			type: 'bar',
			data: {
				labels: vehiculosData.map(function(v) { return v.placa; }),
				datasets: [{
					label: 'Combustible (galones)',
					data: vehiculosData.map(function(v) { return parseFloat(v.combustible || 0); }),
					backgroundColor: 'rgba(220, 53, 69, 0.6)',
					borderColor: 'rgba(220, 53, 69, 1)',
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return value.toLocaleString('es-CO') + ' gal';
							}
						}
					}
				},
				plugins: {
					legend: { display: true },
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Combustible: ' + parseFloat(context.parsed.y).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' gal';
							}
						}
					}
				}
			}
		});
	}
});
</script>
@endpush
@endsection
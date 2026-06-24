@extends('layouts.app')
@section('htmlheader_title')
	Combustible — {{ $Vehicle->VehicPlaca }}
@endsection
@section('contentheader_title')
	<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
		<i class="fa fa-gas-pump"></i> Historial de combustible — {{ $Vehicle->VehicPlaca }}
		<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Registros de carga de combustible</h3>
					<div class="box-tools pull-right">
						<a href="{{ route('vehicle.combustible.create', $Vehicle->VehicPlaca) }}" class="btn btn-success btn-sm">
							<i class="fa fa-plus"></i> Nuevo registro
						</a>
						<a href="{{ url('/vehicle/' . $Vehicle->VehicPlaca . '/edit') }}" class="btn btn-default btn-sm">
							<i class="fa fa-arrow-left"></i> Volver al vehículo
						</a>
						<a href="{{ url('/vehicle') }}" class="btn btn-default btn-sm">
							<i class="fa fa-truck"></i> Lista de vehículos
						</a>
					</div>
				</div>
				<div class="box-body">
					@if(session('success'))
						<div class="alert alert-success">{{ session('success') }}</div>
					@endif
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Fecha</th>
								<th>Tipo</th>
								<th>Cantidad (gal)</th>
								<th>Valor</th>
								<th>Kilometraje</th>
								<th>Ticket</th>
								<th>Observaciones</th>
							</tr>
						</thead>
						<tbody>
							@forelse($registros as $r)
								<tr>
									<td>{{ $r->fecha->format('d/m/Y') }}</td>
									<td>{{ \App\VehiculoCombustible::getNombreTipo($r->tipo_combustible) }}</td>
									<td>{{ number_format($r->cantidad, 2, ',', '.') }}</td>
									<td>@if($r->valor) ${{ number_format($r->valor, 0, ',', '.') }} @else — @endif</td>
									<td>@if($r->kilometraje) {{ number_format($r->kilometraje, 0, ',', '.') }} km @else — @endif</td>
									<td>
										@if($r->ruta_ticket)
											<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($r->ruta_ticket) }}" target="_blank" class="btn btn-default btn-xs">
												<i class="fa fa-external-link-alt"></i> Ver
											</a>
										@else
											—
										@endif
									</td>
									<td>{{ $r->observaciones ?: '—' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center text-muted">No hay registros de combustible.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
					@if($registros->hasPages())
						<div class="text-center">{{ $registros->links() }}</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

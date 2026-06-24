@extends('layouts.app')
@section('htmlheader_title')
	Registrar combustible{{ $Vehicle ? ' — ' . $Vehicle->VehicPlaca : '' }}
@endsection
@section('contentheader_title')
	<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
		<i class="fa fa-gas-pump"></i> Registrar combustible{{ $Vehicle ? ' — ' . $Vehicle->VehicPlaca : '' }}
		<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Carga de combustible</h3>
					<div class="box-tools pull-right">
						@if($Vehicle)
						<a href="{{ route('vehicle.combustible.index', $Vehicle->VehicPlaca) }}" class="btn btn-default btn-sm">
							<i class="fa fa-list"></i> Historial
						</a>
						<a href="{{ url('/vehicle/' . $Vehicle->VehicPlaca . '/edit') }}" class="btn btn-default btn-sm">
							<i class="fa fa-arrow-left"></i> Volver al vehículo
						</a>
						@endif
						<a href="{{ url('/vehicle') }}" class="btn btn-default btn-sm">
							<i class="fa fa-truck"></i> Lista de vehículos
						</a>
					</div>
				</div>
				<div class="box-body">
					<form action="{{ route('vehicle.combustible.store.standalone') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="row">
							<div class="col-md-6 form-group">
								<label for="placa">Vehículo (placa) <span class="text-danger">*</span></label>
								<select class="form-control" id="placa" name="placa" required>
									<option value="">Seleccione el vehículo...</option>
									@foreach($Vehiculos as $v)
										<option value="{{ $v->VehicPlaca }}" {{ old('placa', $Vehicle?->VehicPlaca) == $v->VehicPlaca ? 'selected' : '' }}>{{ $v->VehicPlaca }} — {{ $v->VehicTipo }}</option>
									@endforeach
								</select>
								<small class="text-muted">Seleccione el vehículo al que se le realizó el tanqueo.</small>
								@error('placa')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
							<div class="col-md-6 form-group">
								<label for="fecha">Fecha de carga <span class="text-danger">*</span></label>
								<input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
								@error('fecha')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 form-group">
								<label for="tipo_combustible">Tipo de combustible <span class="text-danger">*</span></label>
								<select class="form-control" id="tipo_combustible" name="tipo_combustible" required>
									<option value="">Seleccione...</option>
									@foreach(\App\VehiculoCombustible::TIPOS as $cod => $nombre)
										<option value="{{ $cod }}" {{ old('tipo_combustible') == $cod ? 'selected' : '' }}>{{ $nombre }}</option>
									@endforeach
								</select>
								@error('tipo_combustible')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
							<div class="col-md-6 form-group"></div>
						</div>
						<div class="row">
							<div class="col-md-4 form-group">
								<label for="cantidad">Cantidad (galones) <span class="text-danger">*</span></label>
								<input type="number" class="form-control" id="cantidad" name="cantidad" step="0.01" min="0.01" placeholder="Ej: 25.5" value="{{ old('cantidad') }}" required>
								@error('cantidad')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
							<div class="col-md-4 form-group">
								<label for="valor">Valor ($) — opcional</label>
								<input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0" placeholder="Ej: 150000" value="{{ old('valor') }}">
								@error('valor')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
							<div class="col-md-4 form-group">
								<label for="kilometraje">Kilometraje al tanquear — opcional</label>
								<input type="number" class="form-control" id="kilometraje" name="kilometraje" min="0" placeholder="Ej: 125000" value="{{ old('kilometraje', $Vehicle?->VehicKmActual ?? '') }}">
								@error('kilometraje')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 form-group">
								<label for="ticket"><i class="fa fa-image"></i> Imagen del ticket (opcional)</label>
								<input type="file" class="form-control" id="ticket" name="ticket" accept=".jpg,.jpeg,.png,.pdf">
								<small class="text-muted">JPG, PNG o PDF. Máx. 5 MB.</small>
								@error('ticket')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
							<div class="col-md-6 form-group">
								<label for="observaciones">Observaciones</label>
								<input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Ej: Estación Terpel" value="{{ old('observaciones') }}">
								@error('observaciones')<span class="help-block text-danger">{{ $message }}</span>@enderror
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<button type="submit" class="btn btn-success">
									<i class="fa fa-save"></i> Guardar registro
								</button>
								<a href="{{ route('vehicle.index') }}" class="btn btn-default">Cancelar</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

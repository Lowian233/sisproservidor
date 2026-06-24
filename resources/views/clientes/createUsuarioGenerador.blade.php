@extends('layouts.app')
@section('htmlheader_title')
Crear Usuario y Generador
@endsection
@section('contentheader_title')
Crear Usuario y Generador para {{ $cliente->CliName }}
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-12 col-md-offset-0">
			<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">
						<i class="fa fa-user-plus"></i> Crear Usuario y Generador
					</h3>
				</div>
				<div class="box-body">
					@if (session('success'))
						<div class="alert alert-success">
							{{ session('success') }}
						</div>
					@endif

					@if (session('error'))
						<div class="alert alert-danger">
							{{ session('error') }}
						</div>
					@endif

					<div class="alert alert-info">
						<h4><i class="icon fa fa-info"></i> Información del Cliente</h4>
						<p><strong>Cliente:</strong> {{ $cliente->CliName }}</p>
						<p><strong>NIT:</strong> {{ $cliente->CliNit }}</p>
						@if($personal)
							<p><strong>Contacto:</strong> {{ $personal->PersFirstName }} {{ $personal->PersLastName }}</p>
						@endif
					</div>

					<form role="form" action="{{ route('clientes.storeUsuarioGenerador', $cliente->CliSlug) }}" method="POST" data-toggle="validator">
						@csrf
						@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						@endif

						<div class="box box-info">
							<div class="box-header with-border">
								<h3 class="box-title">Datos del Usuario</h3>
							</div>
							<div class="box-body">
								<div class="form-group col-md-6">
									<label for="email">Correo Electrónico del Usuario</label>
									<small class="help-block with-errors">*</small>
									<input type="email" class="form-control" id="email" name="email" 
										value="{{ old('email', $personal ? $personal->PersEmail : '') }}" 
										required maxlength="255" placeholder="usuario@ejemplo.com">
									@error('email')
										<span class="text-danger">{{ $message }}</span>
									@enderror
								</div>

								<div class="form-group col-md-6">
									<label for="password">Contraseña</label>
									<small class="help-block with-errors">*</small>
									<input type="password" class="form-control" id="password" name="password" 
										required minlength="8" placeholder="Mínimo 8 caracteres">
									@error('password')
										<span class="text-danger">{{ $message }}</span>
									@enderror
								</div>

								<div class="form-group col-md-6">
									<label for="password_confirmation">Confirmar Contraseña</label>
									<small class="help-block with-errors">*</small>
									<input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
										required minlength="8" placeholder="Repita la contraseña">
								</div>
							</div>
						</div>

						<div class="box box-success">
							<div class="box-header with-border">
								<h3 class="box-title">Información del Generador</h3>
							</div>
							<div class="box-body">
								<div class="alert alert-warning">
									<i class="fa fa-info-circle"></i> El generador y la sede del generador se crearán automáticamente con los datos del cliente y la sede principal.
								</div>
								<div class="form-group col-md-12">
									<p><strong>Nombre del Generador:</strong> {{ $cliente->CliName }}</p>
									<p><strong>NIT del Generador:</strong> {{ $cliente->CliNit }}</p>
									@if($sede)
										<p><strong>Sede del Generador:</strong> {{ $sede->SedeName }}</p>
										<p><strong>Dirección:</strong> {{ $sede->SedeAddress }}</p>
									@endif
								</div>
							</div>
						</div>

						<div class="box-footer">
							<button type="submit" class="btn btn-success pull-right">
								<i class="fa fa-save"></i> Crear Usuario y Generador
							</button>
							<a href="{{ route('cliente-show', $cliente->CliSlug) }}" class="btn btn-default">
								<i class="fa fa-arrow-left"></i> Cancelar
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

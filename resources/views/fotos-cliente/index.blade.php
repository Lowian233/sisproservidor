@extends('layouts.app')
@section('htmlheader_title')
Fotos Adicionales
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #ff6b6b, #fbc2eb); padding-right:30vw; position:relative; overflow:hidden;">
	Fotos Adicionales - Descargue/Pesaje
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
    <div class="row">
							<div class="col-md-6">
								<h3 class="box-title">
									<i class="fas fa-images"></i> Fotos de Descargue/Pesaje
                    </h3>
							</div>
							<div class="col-md-6">
								<div class="box-tools pull-right">
									<a href="{{ route('fotos-cliente.download-all') }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Descargar Todas
                        </a>
                    </div>
                </div>
						</div>
					</div>
					
					<!-- Filtros de b??squeda -->
					<div class="box-body">
						<div class="row">
							<div class="col-md-12">
								<form method="GET" action="{{ route('fotos-cliente.index') }}" class="form-inline">
									<div class="form-group" style="margin-right: 10px;">
										<label for="search" class="sr-only">Buscar</label>
										<input type="text" 
											   class="form-control" 
											   id="search" 
											   name="search" 
											   placeholder="Buscar por ID (ej: 12345) o cliente..." 
											   value="{{ request('search') }}"
											   style="width: 280px;">
									</div>
									
									@if(in_array(Auth::user()->UsRol, App\Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, App\Permisos::TODOPROSARC))
									<div class="form-group" style="margin-right: 10px;">
										<label for="cliente" class="sr-only">Cliente</label>
										<select class="form-control" name="cliente" id="cliente" style="width: 200px;">
											<option value="">Todos los clientes ({{ $clientes->count() }} disponibles)</option>
											@forelse($clientes as $cliente)
												<option value="{{ $cliente->ID_Cli }}" {{ request('cliente') == $cliente->ID_Cli ? 'selected' : '' }}>
													{{ $cliente->CliName }}
												</option>
											@empty
												<option value="" disabled>No hay clientes disponibles</option>
											@endforelse
										</select>
									</div>
									@endif
									
									<div class="form-group" style="margin-right: 10px;">
										<label for="fecha_desde" class="sr-only">Desde</label>
										<input type="date" 
											   class="form-control" 
											   id="fecha_desde" 
											   name="fecha_desde" 
											   value="{{ request('fecha_desde') }}"
											   style="width: 150px;">
									</div>
									
									<div class="form-group" style="margin-right: 10px;">
										<label for="fecha_hasta" class="sr-only">Hasta</label>
										<input type="date" 
											   class="form-control" 
											   id="fecha_hasta" 
											   name="fecha_hasta" 
											   value="{{ request('fecha_hasta') }}"
											   style="width: 150px;">
									</div>
									
									<button type="submit" class="btn btn-primary" style="margin-right: 5px;">
										<i class="fas fa-search"></i> Buscar
									</button>
									
									<a href="{{ route('fotos-cliente.index') }}" class="btn btn-default">
										<i class="fas fa-times"></i> Limpiar
									</a>
								</form>
							</div>
						</div>
						
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

						<br>
						
						<table class="table table-compact table-bordered table-striped">
							<thead>
								<th>Fecha</th>
								@if(in_array(Auth::user()->UsRol, App\Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, App\Permisos::TODOPROSARC))
									<th>Cliente</th>
								@endif
								<th>Solicitud</th>
								<th>Estado</th>
								<th>Tipo</th>
								<th>Miniatura</th>
								<th>Acciones</th>
								<th>Creado el</th>
							</thead>
							<tbody>
								@forelse($fotos as $foto)
								<tr>
									<td>{{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/Y') }}</td>
									@if(in_array(Auth::user()->UsRol, App\Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, App\Permisos::TODOPROSARC))
										<td>{{ $foto->CliName }}</td>
									@endif
									<td>
										<div style="font-size: 12px; line-height: 1.2;">
											<strong>ID: {{ $foto->ID_SolSer }}</strong><br>
											<a href="/solicitud-servicio/{{ $foto->SolSerSlug }}" target="_blank" class="text-primary" style="text-decoration: none;">
												{{ $foto->SolSerSlug }}
											</a>
										</div>
									</td>
									<td>
										<span class="label 
											@switch($foto->SolSerStatus)
												@case('Pendiente')
													label-warning
													@break
												@case('Programado')
													label-info
													@break
												@case('En Ruta')
													label-primary
													@break
												@case('Recolectado')
													label-success
													@break
												@case('Certificado')
													label-success
													@break
												@default
													label-default
											@endswitch
										">
											{{ $foto->SolSerStatus }}
										</span>
									</td>
									<td>
										<span class="badge badge-info">{{ $foto->RecTipo }}</span>
									</td>
									<td class="text-center">
										<div style="width: 80px; height: 60px; overflow: hidden; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">
                                                <img src="{{ asset('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc) }}" 
                                                     alt="Foto {{ $foto->RecTipo }}"
												 style="width: 100%; height: 100%; object-fit: cover;"
                                                     onerror="this.src='{{ asset('img/defaultimage.png') }}'">
                                            </div>
									</td>
									<td class="text-center">
										<div class="btn-group">
                                                <a href="{{ asset('img/Recursos/' . $foto->RecSrc . '/' . $foto->RecRmSrc) }}" 
                                                   target="_blank" 
											   class="btn btn-primary btn-sm"
											   title="Ver imagen completa">
												<i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('fotos-cliente.download', $foto->ID_Rec) }}" 
											   class="btn btn-success btn-sm"
											   title="Descargar imagen">
												<i class="fas fa-download"></i>
                                                </a>
                                            </div>
									</td>
									<td>{{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/Y H:i') }}</td>
								</tr>
								@empty
								<tr>
									<td colspan="{{ in_array(Auth::user()->UsRol, App\Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, App\Permisos::TODOPROSARC) ? '8' : '7' }}" class="text-center">
										<div class="py-4">
											<i class="fas fa-images fa-3x text-muted mb-3"></i>
											<h4 class="text-muted">No hay fotos disponibles</h4>
											<p class="text-muted">No se encontraron fotos que coincidan con los criterios de b??squeda.</p>
										</div>
									</td>
								</tr>
								@endforelse
							</tbody>
						</table>
						
						<!-- Informaci??n de resultados y paginaci??n -->
						@if($fotos->count() > 0)
						<div class="row">
							<div class="col-md-6">
								<p class="text-muted">
									Mostrando {{ $fotos->firstItem() }} a {{ $fotos->lastItem() }} de {{ $fotos->total() }} resultados
								</p>
							</div>
							<div class="col-md-6">
								<div class="pull-right">
									{{ $fotos->appends(request()->query())->links() }}
                                        </div>
                                    </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table > tbody > tr > td {
    vertical-align: middle;
}

.btn-group .btn {
    margin-right: 2px;
}

.form-inline .form-group {
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .form-inline .form-group {
        display: block;
        margin-bottom: 10px;
    }
    
    .form-inline .form-control {
        width: 100% !important;
    }
}

.label {
    display: inline-block;
    padding: .25em .4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: .25rem;
}

.label-default {
    background-color: #6c757d;
    color: #fff;
}

.label-primary {
    background-color: #007bff;
    color: #fff;
}

.label-success {
    background-color: #28a745;
    color: #fff;
}

.label-info {
    background-color: #17a2b8;
    color: #fff;
}

.label-warning {
    background-color: #ffc107;
    color: #212529;
}
</style>
@endsection 
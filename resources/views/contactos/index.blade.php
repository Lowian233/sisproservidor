@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.clientcontacto') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.clientcontacto') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.MenuContactos') }}</h3>
					@if (in_array(Auth::user()->UsRol, Permisos::Jefes) || in_array(Auth::user()->UsRol2, Permisos::Jefes))
						<a href="/contactos/create" class="btn btn-primary pull-right">{{ __('adminlte::message.create') }}</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="contactosTable" class="table table-bordered table-striped" width="100%">
							<thead>
							<tr>
								<th>N°</th>
								<th>{{ __('adminlte::LangTratamiento.type') }}</th>
								<th>{{ __('adminlte::message.clirazonsoc') }}</th>
								<th>{{ __('adminlte::message.clientnombrecorto') }}</th>
								<th>{{ __('adminlte::message.clientNIT') }}</th>
								<th>Sede</th>
								<th>{{ __('adminlte::LangTratamiento.tratMenu') }}</th>
								<th>{{ __('adminlte::LangTratamiento.pretrat') }}s</th>
								<th>Tarifas</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody id="readyTable">
							@foreach($proveedoresConTratamientos as $proveedor)
								@foreach($proveedor['tratamientos'] as $tratamiento)
								<tr @if($proveedor['cliente']->CliDelete === 1)
										style="color: red;" 
									@endif
								>
									<td>#{{$tratamiento->ID_Trat}}</td>
									@if($tratamiento->TratTipo == 0)
									<td>Interno</td>
									@else
									<td>Externo</td>
									@endif
									<td>{{$proveedor['cliente']->CliName}}</td>
									<td>{{$proveedor['cliente']->CliShortname}}</td>
									<td>{{$proveedor['cliente']->CliNit}}</td>
									<td>{{$proveedor['sede']->SedeName}}</td>
									<td>{{$tratamiento->TratName}}</td>
									<td>
										<ul>
											@foreach($tratamiento->pretratamientos as $pretratamiento)
												@if($pretratamiento->PreTratDelete == 0)
													<li>{{$pretratamiento->PreTratName}}</li>
												@endif
											@endforeach
										</ul>
									</td>
									<td>
										@php
											// Contar tarifas de proveedor para este tratamiento
											$tarifasProveedor = $tratamiento->tarifas_proveedor->where('FK_Proveedor', $proveedor['cliente']->ID_Cli)->count();
										@endphp
										@if($tarifasProveedor > 0)
											<span class="badge bg-green">{{$tarifasProveedor}} tarifa(s)</span>
										@else
											<span class="badge bg-red">Sin tarifas</span>
										@endif
									</td>
									<td>
										<a method='get' href='/contactos/{{$proveedor['cliente']->CliSlug}}' class='btn btn-info btn-sm' title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a>
										@if(in_array(Auth::user()->UsRol, Permisos::Jefes) || in_array(Auth::user()->UsRol2, Permisos::Jefes))
										<a href='{{ route("proveedor-tarifas.create", ["slug" => $proveedor["cliente"]->CliSlug]) }}?tratamiento={{$tratamiento->ID_Trat}}' class='btn btn-success btn-sm' title="Agregar Tarifa de Proveedor"><i class="fas fa-dollar-sign"></i></a>
										@endif
									</td>
								</tr>
								@endforeach
							@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
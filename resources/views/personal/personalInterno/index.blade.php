@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.personalhtmlheader_title') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.personalhtmlheader_title') }}
	<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.personaltitlelist') }}</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
					<a href="personalInterno/create" class="btn btn-primary pull-right">{{ __('adminlte::message.create') }}</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="PersonalsInternoTable" class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									@if(in_array(Auth::user()->UsRol, Permisos::PERSONAL) || in_array(Auth::user()->UsRol2, Permisos::PERSONAL))
									<th>{{ __('adminlte::message.persdocument') }}</th>
									@endif
									<th>{{ __('adminlte::message.persname') }}</th>
									<th>{{ __('adminlte::message.emailaddress') }}</th>
									<th>{{ __('adminlte::message.mobile') }}</th>
									<th>parafiscales</th>
									<th>vencimiento</th>
									<th>Cargo</th>
									<th>Área</th>
									@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
									<th>Acciones</th>
									@endif

								</tr>
							</thead>
							<tbody id="readyTable">
								@php
									$rolesPuedenEliminar = Permisos::Jefes;
									$puedeEliminar = in_array(Auth::user()->UsRol, $rolesPuedenEliminar) || in_array(Auth::user()->UsRol2, $rolesPuedenEliminar);
									$puedeRestaurar = Auth::user()->UsRol === 'Programador' || Auth::user()->UsRol2 === 'Programador';
								@endphp
								@foreach($Personals as $Personal)
								<tr style="{{$Personal->PersDelete === 1 ? 'color: red' : ''}}">
									@if(in_array(Auth::user()->UsRol, Permisos::PERSONAL) || in_array(Auth::user()->UsRol2, Permisos::PERSONAL))
									<td>{{$Personal->PersDocType." ".$Personal->PersDocNumber}}</td>
									@endif
									<td>{{$Personal->PersFirstName." ".$Personal->PersSecondName." ".$Personal->PersLastName}}</td>
									<td>{{$Personal->PersEmail}}</td>
									<td>{{$Personal->PersCellphone}}</td>
									@if($Personal->PersParafiscales !== null)
										@if ($Personal->PersParafiscalesExpire >= today()) 
										<td class="text-center"><a method='get' href='{{Storage::url($Personal->PersParafiscales)}}' target='_blank' class='btn btn-primary'><i class='far fa-file-alt fa-lg'></a></td>
										@else
										<td class="text-center"><a method='get' href='{{Storage::url($Personal->PersParafiscales)}}' target='_blank' class='btn btn-danger'><i class='far fa-file-alt fa-lg'></a></td>
										@endif
									@else
									<td class="text-center"><a disabled method='get' href='/img/CertificadoDefault.pdf' target='_blank' class='btn btn-default'><i class='far fa-file-alt fa-lg'></a></td>
									@endif
									<td>{{$Personal->PersParafiscalesExpire != null ? date('Y-m-d', strtotime($Personal->PersParafiscalesExpire)) : ""}}</td>
									<td>{{$Personal->CargName}}</td>
									<td>{{$Personal->AreaName}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
									<td>
										<a method='get' href='/personalInterno/{{$Personal->PersSlug}}' class='btn btn-info btn-sm' title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a>
										@if($Personal->PersDelete == 1 && $puedeRestaurar)
											<button type="button" class="btn btn-danger btn-sm" onclick="confirmDeletePersonalInterno('{{ $Personal->PersSlug }}', '{{ addslashes($Personal->PersFirstName." ".$Personal->PersSecondName." ".$Personal->PersLastName) }}', 1)" title="Restaurar personal">
												<i class="fas fa-undo"></i>
											</button>
										@elseif($Personal->PersDelete == 0 && $puedeEliminar)
											<button type="button" class="btn btn-danger btn-sm" onclick="confirmDeletePersonalInterno('{{ $Personal->PersSlug }}', '{{ addslashes($Personal->PersFirstName." ".$Personal->PersSecondName." ".$Personal->PersLastName) }}', 0)" title="Eliminar personal">
												<i class="fas fa-trash"></i>
											</button>
										@endif
									</td>
									@endif

								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
<!-- Modal confirmación eliminar personal interno -->
<div class="modal fade" id="modalDeletePersonalInterno" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Confirmar eliminación</h4>
			</div>
			<div class="modal-body">
				<p><strong id="modalDeletePersonalInternoText"></strong></p>
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i>
					<strong id="personalInternoNombreModal"></strong>
				</div>
				<p class="text-muted"><small>La eliminación es lógica; el registro puede restaurarse después.</small></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				<form id="formDeletePersonalInterno" method="POST" style="display: inline;">
					@csrf
					@method('DELETE')
					<button type="submit" class="btn btn-danger" id="btnConfirmDeletePersonalInterno">
						<i class="fas fa-trash"></i> <span id="btnConfirmDeletePersonalInternoText">Confirmar</span>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
function confirmDeletePersonalInterno(slug, nombre, persDelete) {
	document.getElementById('personalInternoNombreModal').textContent = nombre;
	document.getElementById('formDeletePersonalInterno').action = '{{ url("personalInterno") }}/' + slug;
	var esRestaurar = persDelete == 1;
	document.getElementById('modalDeletePersonalInternoText').textContent = esRestaurar
		? '¿Está seguro que desea restaurar el siguiente personal interno?'
		: '¿Está seguro que desea eliminar el siguiente personal interno?';
	document.getElementById('btnConfirmDeletePersonalInternoText').textContent = esRestaurar ? 'Restaurar' : 'Eliminar';
	$('#modalDeletePersonalInterno').modal('show');
}
</script>
@endif
@endsection
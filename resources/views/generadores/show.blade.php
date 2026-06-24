@extends('layouts.app')
@section('htmlheader_title')
	{{ __('adminlte::message.gener') }}
@endsection
@section('contentheader_title')
@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE))
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@else
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endif
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-6">
			<div class="box box-primary">
				<div class="box-body box-profile">
					<div class="col-md-12 col-xs-12">
						@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) ||in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR))
							<a href="/generadores/{{$Generador->GenerSlug}}/edit" class="btn btn-warning pull-right"> <i class="fas fa-edit"></i> <b>{{ __('adminlte::message.edit') }}</b></a>
						@endif
						@component('layouts.partials.modal')
							@slot('slug')
								{{$Generador->GenerSlug}}
							@endslot
							@slot('textModal')
								el generador <b>{{$Generador->GenerName}}</b>
							@endslot
						@endcomponent
						@if($Generador->GenerDelete == 0)
							@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) ||in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR))
								<a method='get' href='#' data-toggle='modal' data-target='#myModal{{$Generador->GenerSlug}}' class='btn btn-danger pull-left'><i class="fas fa-trash-alt"></i><b> {{ __('adminlte::message.delete') }}</b></a>
								<form action='/generadores/{{$Generador->GenerSlug}}' method='POST'  class="col-12 pull-right">
									@method('DELETE')
									@csrf
									<input type="submit" id="Eliminar{{$Generador->GenerSlug}}" style="display: none;">
								</form>
							@endif
						@else
							@if (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
								<form action='/generadores/{{$Generador->GenerSlug}}' method='POST' class="pull-left">
									@method('DELETE')
									@csrf
									<button type="submit" class='btn btn-success btn-block'>
										<i class="fas fa-plus-square"></i><b> {{ __('adminlte::message.add') }}</b>
									</button>
								</form>
							@endif
						@endif
					</div>
					<h3 class="profile-username text-center textolargo">{{$Generador->GenerName}}</h3>
					<ul class="list-group list-group-unbordered">
						@if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC))
							<li class="list-group-item">
								<b>{{ __('adminlte::message.clientcliente') }}</b>
								<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.clientcliente') }}" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Cliente->CliName}}</p>">{{$Cliente->CliName}}</a>
							</li>
						@endif
						<li class="list-group-item">
							<b>{{ __('adminlte::message.sclientsede') }}</b>
							<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.sclientsede') }}" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Sede->SedeName}}</p>">{{$Sede->SedeName}}</a>
						</li>
						<li class="list-group-item">
							<b>{{ __('adminlte::message.clientNIT') }}</b>
							<a href="#" class="pull-right">{{$Generador->GenerNit}}</a>
						</li>
						<li class="list-group-item">
							<b>{{ __('adminlte::message.clirazonsoc') }}</b>
							<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.clirazonsoc') }}" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Generador->GenerName}}</p>">{{$Generador->GenerName}}</a>
						</li>
						{{-- <li class="list-group-item">
							<b>{{ __('adminlte::message.clientnombrecorto') }}</b>
							<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.clientnombrecorto') }}" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Generador->GenerShortname}}</p>">{{$Generador->GenerShortname}}</a>
						</li> --}}
						<li class="list-group-item">
							<b>{{ __('adminlte::message.genercode') }}</b>
							<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.genercode') }}" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Generador->GenerCode}}</p>">{{$Generador->GenerCode}}</a>
						</li>
						{{-- @if (in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) ||in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC)) --}}
						<h4 class="text-center"><i>{{ __('adminlte::message.generaddresssgener') }}</i></h4>
						<div style='overflow-y:auto; max-height:200px;'>
							@php
								$i = 0;
							@endphp
							@foreach ($GenerSedes as $GenerSede)
								<li class="list-group-item col-md-12 col-xs-12">
									<div class="col-md-6 col-xs-6">
										<b class="textolargo" style="{{in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) && $GenerSede->GSedeDelete == 1 ? 'color:red;': ''}}">{{$GenerSede->GSedeName}}</b>
										<a title="{{ __('adminlte::message.copy') }}" onclick="copiarAlPortapapeles('SGeneraddress{{$i}}')"><i class="far fa-copy"></i></a>
									</div>
									<div>
										<p href="#" class="pull-right textpopoveraddress" id="SGeneraddress{{$i}}" title="<b>{{ __('adminlte::message.address') }}</b>" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$GenerSede->GSedeAddress}} ({{$GenerSede->MunName}}, {{$GenerSede->DepartName}})</p>">{{$GenerSede->GSedeAddress}} ({{$GenerSede->MunName}}, {{$GenerSede->DepartName}})</p>
									</div>
								</li>
								@php
									$i++;
								@endphp
							@endforeach
						</div>
						{{-- @endif --}}
					</ul>
				</div>
			</div>
		</div>
		@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol, Permisos::PUEDE_SOLICITAR_PLANTA) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC))
		{{--  Modal Agregar un Residuo a una SedeGener--}}
			<form role="form" action="/respelGener" method="POST" enctype="multipart/form-data" data-toggle="validator">
				@csrf
				<div class="modal modal-default fade in" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<div style="font-size: 5em; color: green; text-align: center; margin: auto;">
									<i class="fas fa-plus-circle"></i>
									<span style="font-size: 0.3em; color: black;"><p>{{ __('adminlte::message.assignrrespelssedegener') }}</p></span>
								</div>
							</div>
							@if ($errors->any())
								<div class="alert alert-danger" role="alert">
									<ul>
										@foreach ($errors->all() as $error)
											<p>{{$error}}</p>
										@endforeach
									</ul>
								</div>
							@endif
							<div class="modal-header">
								<div class="col-md-12 form-group">
									<label for="FK_SGener">{{ __('adminlte::message.sedesgener') }}</label><small class="help-block with-errors">*</small>
									<select class="form-control select" id="FK_SGener" name="FK_SGener" required>
										<option value="">{{ __('adminlte::message.select') }}</option>
										@foreach ($GenerSedes as $GenerSede)
											<option value="{{$GenerSede->GSedeSlug}}">{{$GenerSede->GSedeName}}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-12 form-group select-multiple-contenedor">
									<label for="FK_Respel">{{ __('adminlte::message.MenuRespel') }} <a id="load"></a></label><small class="help-block with-errors">*</small>
									<select class="form-control select-multiple" id="FK_Respel" name="FK_Respel[]" multiple required>
										@if(isset($Residuos))
											@foreach ($Residuos as $Residuo)
												<option value="{{$Residuo->RespelSlug}}">{{$Residuo->RespelName}}</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-success pull-right"><b>{{ __('adminlte::message.add') }}</b></button>
							</div>
						</div>
					</div>
				</div>
			</form>
			{{-- END Modal --}}
		@endif
		<div class="col-md-6">
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					{{-- Barra de navegaci車n --}}
					<li class="active box-info" ><a href="#residuos" data-toggle="tab">{{ __('adminlte::message.MenuRespel') }}</a></li>
					<li><a href="#sedes" data-toggle="tab">{{ __('adminlte::message.sclientsedes') }}</a></li>
				</ul>
				<div class="tab-content">
					<div class="active tab-pane" id="residuos">
						<div class="d-flex justify-content-between mb-2">
					@if (in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA))
								<a href="/respels/create" class="btn btn-primary">
									<i class="fas fa-plus-square"></i> <b>{{ __('adminlte::message.respelscreate') }}</b>
								</a>
							@endif
							@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC))
								<a method='get' href='#' data-toggle='modal' data-target='#add' class="btn btn-success">
									<i class="fas fa-plus-circle"></i><b> {{ __('adminlte::message.assignrespels') }}</b>
								</a>
							@endif
						</div>
						<div style='overflow-y:auto; max-height:503px;'>
							@foreach ($Respels as $Respel)
								<ul class="list-group" style="list-style:none; margin-top:10px;">
									<li class="col-md-11 col-xs-12 col-12">
										@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR))
											<a method='get' href='#' data-toggle='modal' data-target='#eliminar{{$Respel->SlugSGenerRes}}' onclick="deleteRespelGener(`{{$Respel->SlugSGenerRes}}`, `{{$Respel->RespelName}}`, `{{$Generador->GenerName}}`)" style="font-size: 1.5em; color: red; margin-bottom:-2px;" class="pull-right" ><i class="fas fa-times-circle"></i></a>
										@endif
										<h4><a href="/respels/{{$Respel->RespelSlug}}" class="list-group-item list-group-item-action list-group-item-light textolargo col-md-offset-1" style="display:flex; justify-content:center;" target="_blank">{{$Respel->RespelName}}</a></h4>
									</li>
									<li class="col-md-12 col-xs-12 col-12">
										@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) ||in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO) && $Generador->GenerDelete == 0)
										{{--  Modal Eliminar un Residuo de una SedeGener--}}
											<div class="deleterespelgener"></div>
										{{-- END Modal --}}
										@endif
									</li>
								</ul>
							@endforeach
						</div>
					</div>
					<div class="tab-pane" id="sedes">
						<div class="text-center">
							@if (in_array(Auth::user()->UsRol, Permisos::CLIENTE) ||in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA))
								<a href="/sgeneradores/create" class="btn btn-primary"><i class="fas fa-plus-square"></i><b> {{ __('adminlte::message.addsedegener') }}</b></a>
							@endif
						</div>
						<div style='overflow-y:auto; max-height:503px;'>
							@foreach ($GenerSedes as $GenerSede)
								<ul class="list-group" style="list-style:none; margin-top:10px; ">
									<li class="col-md-11 col-xs-12 col-12">
										<h4><a href="/sgeneradores/{{$GenerSede->GSedeSlug}}" class="list-group-item list-group-item-action list-group-item-light textolargo col-md-offset-1" style="display:flex; justify-content:center; {{in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) && $GenerSede->GSedeDelete == 1 ? 'color:red;': ''}}" target="_blank" >{{$GenerSede->GSedeName}}</a></h4>
									</li>
								</ul>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('NewScript')
<script>
	$(document).ready(function() {
		$("#FK_SGener").change(function(e) {
			id = $("#FK_SGener").val();
			if (!id) {
				$("#FK_Respel").empty();
				return;
			}
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				}
			});
			$.ajax({
				url: "/sedegener-respel/" + id,
				method: 'GET',
				data: {},
				timeout: 30000, // 30 segundos de timeout
				beforeSend: function(){
					$("#load").html('<i class="fas fa-sync-alt fa-spin"></i>');
					$("#FK_Respel").prop('disabled', true);
					$("#FK_Respel").empty();
				},
				success: function(res) {
					$("#FK_Respel").empty();
					if (res && Array.isArray(res) && res.length > 0) {
						var respel = new Array();
						for (var i = res.length - 1; i >= 0; i--) {
							// Corregido: usar ID_Respel en lugar de ID_Mun para evitar duplicados
							if ($.inArray(res[i].ID_Respel, respel) < 0) {
								$("#FK_Respel").append(`<option value="${res[i].RespelSlug}">${res[i].RespelName}</option>`);
								respel.push(res[i].ID_Respel);
							}
						}
					} else {
						$("#FK_Respel").append(`<option value="">{{ __('adminlte::message.select') }}</option>`);
					}
				},
				error: function(xhr, status, error) {
					console.error("Error al cargar residuos:", error);
					$("#FK_Respel").empty();
					$("#FK_Respel").append(`<option value="">{{ __('adminlte::message.select') }}</option>`);
					if (status === 'timeout') {
						alert("La solicitud tardó demasiado. Por favor, intente nuevamente.");
					} else {
						alert("Error al cargar los residuos. Por favor, intente nuevamente.");
					}
				},
				complete: function(){
					$("#load").empty();
					$("#FK_Respel").prop('disabled', false);
				}
			})
		});
	});
</script>
@endsection

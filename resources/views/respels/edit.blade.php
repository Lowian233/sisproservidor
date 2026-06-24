@extends('layouts.app')
{{-- vista de edición para el cliente --}}
@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE))
	@section('htmlheader_title')
	{{ __('adminlte::LangRespel.Respeledittag') }}
	@endsection

	@section('contentheader_title')
	  <span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	  	{{ __('adminlte::LangRespel.Respeleditmenu') }}
	    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	  </span>
	@endsection

	@section('main-content')
		@component('layouts.partials.modal')
			@slot('slug')
				{{$Respels->ID_Respel}}
			@endslot
			@slot('textModal')
				la solicitud <b>N° {{$Respels->ID_Respel}}</b>
			@endslot
		@endcomponent
		<div class="container-fluid spark-screen">
			<div class="row">
				<div class="col-md-12 col-md-offset-0">
					<!-- Default box -->
					<div class="box">
						<form role="form" action="/respels/{{$Respels->RespelSlug}}" method="POST" id="myform" enctype="multipart/form-data" data-toggle="validator">
							@method('PUT')
							@csrf
							<div class="box-header">
								<h3 class="box-title">{{ __('adminlte::LangRespel.Respeleditmenu') }}</h3>
							</div>
								<!-- left column -->
								<!-- general form elements -->
							<div class="box box-info">
								<div class="box-body">
									<!-- /.box-header -->
									@if ($errors->any())
										<div class="alert alert-danger" role="alert">
											<ul>
												@foreach ($errors->all() as $error)
													<li>{{$error}}</li>
												@endforeach
											</ul>
										</div>
									@endif
									<input type="text" name="Sede" style="display: none;" value="{{$Sede}}">
									@include('layouts.RespelPartials.respelform1Edit')
								</div>
								{{-- Campo de comentario temporalmente deshabilitado mientras se realiza mejora --}}
								{{-- <div class="box box-info">
								<!-- Campo de comentario -->
								<div class="box-body">
									<div class="form-group col-md-12">
										<label>
											<i class="fas fa-comments"></i> Comentario sobre los cambios realizados <span class="text-danger">*</span>
										</label>
										<small class="help-block">
											<strong>Obligatorio:</strong> Explique qué cambios está realizando y por qué son necesarios.
										</small>
										<textarea id="respel_comentario" name="respel_comentario" class="form-control" rows="3" maxlength="1000" placeholder="Ejemplo: Actualizando clasificación según nueva normativa, corrigiendo estado físico, etc." required></textarea>
										<small class="text-muted">
											<span id="char-count">0</span>/1000 caracteres
										</small>
									</div>
								</div> --}}
								{{-- Campo oculto para mantener compatibilidad --}}
								<input type="hidden" name="respel_comentario" value="">

								<div class="box-footer">
									<button type="button" id="btn-actualizar-respel" class="btn btn-success pull-right">
										<i class="fa fa-check"></i> {{ __('adminlte::LangRespel.updaterespelButton') }}
									</button>
								</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	@endsection
@else
{{-- VISTA PARA PROSARC --}}
@php
	// Variable para verificar si el usuario puede cambiar el estado de aprobación (Dirección Técnica, Programador o Gerente de Planta)
	$esDireccionTecnica = in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) ||
						  in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA) ||
						  in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) ||
						  in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR) ||
						  in_array(Auth::user()->UsRol, Permisos::ADMINISTRADORPLANTA) ||
						  in_array(Auth::user()->UsRol2, Permisos::ADMINISTRADORPLANTA);
@endphp
@section('htmlheader_title')
	{{ __('adminlte::LangRespel.Respelevaluatetag') }}
@endsection
@section('contentheader_title')
	<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
		{{ __('adminlte::LangRespel.Respelevaluetemenu') }}
	  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	</span>
@endsection
@section('main-content')
@component('layouts.partials.modal')
	@slot('slug')
		{{$Respels->ID_Respel}}
	@endslot
	@slot('textModal')
		El residuo <b>N° {{$Respels->ID_Respel}}</b>
	@endslot
@endcomponent
<div class="container-fluid spark-screen">
	<!-- form start -->
	<form id="evaluacioncomercial" role="form" action="/respels/{{$Respels->RespelSlug}}/updateStatusRespel" method="POST" enctype="multipart/form-data">
		@method('PUT')
		@csrf
		{{-- <input hidden type="text" name="updated_by" value="{{Auth::user()->email}}"> --}}
			<!-- col md3 -->
			<div class="col-md-3">
				<!-- box -->
				<div class="box box-primary">
					<!-- box body -->
					<div class="box-body box-profile">
						{{-- <img class="profile-user-img img-responsive img-circle" src="../../dist/img/user4-128x128.jpg" alt="User profile picture"> --}}
						<h3 class="profile-username text-center">{{$Respels->RespelName}}</h3>
						<p class="text-muted text-center">{{$Respels->RespelDescrip}}</p>
						<ul class="list-group list-group-unbordered">
							@if ($Respels->RespelIgrosidad != 'No peligroso')
							<li class="list-group-item">
								<b>Clasificación</b> <a class="pull-right">
									{{($Respels->YRespelClasf4741 <> null ? $Respels->YRespelClasf4741 : ($Respels->ARespelClasf4741 <> null ? $Respels->ARespelClasf4741 : "N/D"))}}
								</a>
							</li>
							@else
							<li class="list-group-item">
								<b>Clasificación</b> <a class="pull-right">N/A</a>
							</li>
							@endif
							<li class="list-group-item">
								<b>Peligrosidad</b> <a href="#" title="" data-toggle="popover" id="correocopy" data-trigger="focus" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Respels->RespelIgrosidad}}</p>" class="pull-right textpopover" data-original-title="Peligrosidad" style="width: 50%;">{{$Respels->RespelIgrosidad}}</a>
							</li>
							<li class="list-group-item">
								<b>Estado Físico</b> <a class="pull-right">{{$Respels->RespelEstado}}</a>
							</li>
							<li class="list-group-item">
								<b>Estado de aprobación</b>
									<select name="RespelStatus" class="form-control">
										@php
											// Usar la variable global $esDireccionTecnica definida al inicio
											$puedeCambiarEstado = $esDireccionTecnica;
										@endphp
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Pendiente') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Pendiente' ? 'selected' : '' }}>{{ __('adminlte::LangRespel.respelstatuspendiente') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Aprobado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Aprobado' ? 'selected' : '' }}>{{ 'Aprobado'}}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Evaluado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Evaluado' ? 'selected' : '' }}>{{ __('adminlte::LangRespel.respelstatusevaluated') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Cotizado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Cotizado' ? 'selected' : '' }}>{{ __('adminlte::LangRespel.respelstatuscotizado') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Aprobado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Aprobado' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusaprovado') }}</option>
										{{-- <option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Aceptado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Aceptado' ? 'selected' : '' }}>{{ trans('adminlte_lang::LangRespel.respelstatusaceptado') }}</option> --}}
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Revisado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Revisado' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusrevisado') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Rechazado') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Rechazado' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusrechazado') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Falta TDE') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Falta TDE' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusfaltatde') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Incompleto') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Incompleto' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusincompleto') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'Vencido') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'Vencido' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatusvencido') }}</option>
										<option {{$puedeCambiarEstado || ($Respels->RespelStatus == 'TDE actualizada') ? '' : 'disabled'}} {{$Respels->RespelStatus == 'TDE actualizada' ? 'selected' : '' }}>{{  __('adminlte::LangRespel.respelstatustdeupdated') }}</option>
									</select>
							</li>
							<li class="list-group-item">
								<label>Observaciones</label>
								<textarea style="resize: vertical;" maxlength="250" name="RespelStatusDescription" id="taid" class="form-control" rows ="5">{{$Respels->RespelStatusDescription}}</textarea>
							</li>

							<li class="list-group-item" style="display: block; overflow: auto";>
								{{-- hoja de seguridad --}}
								@if($Respels->RespelHojaSeguridad!=='RespelHojaDefault.pdf')
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.hojadeseguridad') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="Ver Documento" disabled>
											<div class="input-group-btn">
												<a method='get' href='/img/HojaSeguridad/{{$Respels->RespelHojaSeguridad}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
											</div>
										</div>
									</div>
								@else
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.hojadeseguridad') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="No Adjuntado" disabled>
											<div class="input-group-btn">
												<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
											</div>
										</div>
									</div>
								@endif
								{{-- tarjeta de emergencia --}}
								@if($Respels->RespelTarj!=='RespelTarjetaDefault.pdf')
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="Ver Documento" disabled>
											<div class="input-group-btn">
												<a method='get' href='/img/TarjetaEmergencia/{{$Respels->RespelTarj}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
											</div>
										</div>
									</div>
								@else
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.tarjetaemergencia') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="No Adjuntado" disabled>
											<div class="input-group-btn">
												<a target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
											</div>
										</div>
									</div>
								@endif
								{{-- fotografia del residuo --}}
								@if($Respels->RespelFoto!=='RespelFotoDefault.png')
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.foto') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="Ver Documento" disabled>
											<div class="input-group-btn">
												<a method='get' href='/img/fotoRespelCreate/{{$Respels->RespelFoto}}' target='_blank' class='btn btn-success'><i class='fas fa-image fa-lg'></i></a>
											</div>
										</div>
									</div>
								@else
									<div class="col-md-12 form-group">
										<label>{{ __('adminlte::LangRespel.foto') }}</label>
										<div class="input-group">
											<input type="text" class="form-control" value="No Adjuntado" disabled>
											<div class="input-group-btn">
												<a target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
											</div>
										</div>
									</div>
								@endif
							</li>
							@if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA))
							<li><!-- Botón para Historial de Cambios - Solo Dirección Técnica -->
							<div class="col-md-12" style="margin: 10px 0;">
								<div class="pull-right">
									<a type="button" href="#" data-toggle="modal" data-target="#ModalHistorial" class="btn btn-info">
										<i class="fas fa-history"></i> <b>Historial de Cambios</b>
									</a>
								</div>
							</div>
							<!-- /.Botón Historial -->
						</li>
						@endif
						</ul>
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box body -->
			</div>
			<!-- /.col md3 -->
			<!-- col md9 -->
			<div class="col-md-9">
				<!-- box -->
				<div class="box">
					<!-- box header -->
					<div class="box-header with-border">
						<h3 class="box-title">{{ __('adminlte::LangRespel.Respelevaluetemenu') }}</h3>
						<div class="box-tools pull-right">
							@if($esDireccionTecnica || in_array(Auth::user()->UsRol, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones)||Auth::user()->UsRol == 'usaquen')
							<button onclick="AgregarOption()" class="btn btn-primary pull-right" id="addOptionButton"> <i class="fa fa-plus"></i> {{ __('adminlte::LangTratamiento.optionadd') }}</button>
							@endif
							@if($esDireccionTecnica || in_array(Auth::user()->UsRol, Permisos::JefeOperaciones)||in_array(Auth::user()->UsRol2, Permisos::JefeOperaciones) ||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP)||in_array(Auth::user()->UsRol2, Permisos::COMERCIALAP)||Auth::user()->UsRol == 'usaquen')
								@switch($Respels->RespelStatus)
									@case('Revisado')
									@case('Evaluado')
									@case('Cotizado')
									@case('Aprobado')
									@case('Vencido')
										<a method='get' style="margin-right: 1em;" href='/clientToRp/{{$Respels->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Copiar información del residuo</b>" data-content="<p style='width: 50%'>Haga click en este boton para copiar la información de este residuo y crear uno nuevo, el cual quedara disponible en la lista de residuos comunes para que otros clientes puedan utilizarlo </p>" class='btn btn-primary'><i class='fas fa-lg fa-copy'></i> Copiar</a>
										@break
									@case('Falta TDE')
									@case('Pendiente')
									@case('Incompleto')
									@case('Rechazado')
										<a disabled method='get' style="margin-right: 1em;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Copiar información del residuo</b>" data-content="<p style='width: 50%'>Este residuo aun no cumple con las condiciones para incluirlo en la lista de residuos comunes </p>" class='btn btn-default'><i class='fas fa-lg fa-copy'></i> Copiar</a>
										@break
									@default
										<a disabled method='get' style="margin-right: 1em;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Copiar información del residuo</b>" data-content="<p style='width: 50%'>Este residuo aun no cumple con las condiciones para incluirlo en la lsta de residuos comunes </p>" class='btn btn-default'><i class='fas fa-lg fa-copy'></i> Copiar</a>
								@endswitch
							@endif
						</div>
						<div class="box-tools pull-right">
								<a href="/respels/{{$Respels->RespelSlug}}/editADP" class="btn btn-warning">{{ __('adminlte::message.edit') }}</a>
						</div>
					</div>
					<!-- /.box header -->
					<!-- box body -->
					<div class="box-body">
						<!-- nav-tabs-custom -->
						<div class="nav-tabs-custom" style="box-shadow:3px 3px 5px grey; margin-bottom: 0px;">
							<ul class="nav nav-tabs">
								<li class="nav-item active">
									<a class="nav-link" href="#Residuopane" data-toggle="tab">{{ __('adminlte::LangRespel.respeltabtittle') }}</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Tratamientospane" data-toggle="tab">{{ __('adminlte::LangRespel.trattabtittle') }}</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Pretratamientospane" data-toggle="tab">{{ __('adminlte::LangRespel.pretrattabtittle') }}</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Requerimientospane" data-toggle="tab">{{ __('adminlte::LangRespel.requertabtittle') }}</a>
								</li>
								@if($esDireccionTecnica || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL) ||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP)||in_array(Auth::user()->UsRol2, Permisos::COMERCIALAP)||Auth::user()->UsRol == 'usaquen')
								<li class="nav-item">
									<a class="nav-link" href="#Tarifaspane" data-toggle="tab">{{ __('adminlte::LangRespel.tarifatabtittle') }}</a>
								</li>
								@endif
							</ul>
							<!-- nav-content -->
							<div class="tab-content" style="display: block; overflow: auto;">
								<!-- tab-pane fade -->
								<div class="tab-pane fade in active" id="Residuopane">
									@include('layouts.respel-cliente.respel-residuo')
								</div>
								<!-- /.tab-pane fade -->
								<!-- tab-pane fade -->
								<div class="tab-pane fade" id="Tratamientospane">
									@php
									$contadorphp = 0;
									@endphp
									@foreach($requerimientos as $opcion)
										@include('layouts.respel-comercial.respel-tratamiento-edit')
										@php
											$contadorphp = $contadorphp+1;
										@endphp
									@endforeach
									{{-- @include('layouts.respel-comercial.respel-tratamiento') --}}
								</div>
								<!-- tab-pane fade -->
								<!-- tab-pane fade -->
								<div class="tab-pane fade" id="Pretratamientospane">
									@php
									$contadorphp = 0;
									@endphp
									@foreach($requerimientos as $opcion)
										@include('layouts.respel-comercial.respel-pretratEvaluacion-edit')
										@php
											$contadorphp = $contadorphp+1;
										@endphp
									@endforeach
									{{-- @include('layouts.respel-comercial.respel-pretrat') --}}
								</div>
								<!-- tab-pane fade -->
								<!-- /.tab-pane fade -->
								<div class="tab-pane fade" id="Requerimientospane">
									@php
									$contadorphp = 0;
									@endphp
									@foreach($requerimientos as $opcion)
										@include('layouts.respel-comercial.respel-requerimiento-edit')
										@php
											$contadorphp = $contadorphp+1;
										@endphp
									@endforeach
									{{-- @include('layouts.respel-comercial.respel-requerimiento') --}}
								</div>
								<!-- /.tab-pane fade -->
								<!-- tab-pane fade -->
								@if($esDireccionTecnica || in_array(Auth::user()->UsRol, Permisos::SEDECOMERCIAL) || in_array(Auth::user()->UsRol2, Permisos::SEDECOMERCIAL) ||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP)||in_array(Auth::user()->UsRol2, Permisos::COMERCIALAP)||Auth::user()->UsRol == 'usaquen')
								<div class="tab-pane fade" id="Tarifaspane">
									<script type="text/javascript">
										var contadorRango = [];
									</script>
									@php
										$contadorphp = 0;
									@endphp
									@foreach($requerimientos as $opcion)
										@php
										$contadorRango = [];
										$last = 0;
										@endphp

										@include('layouts.respel-comercial.respel-tarifas-edit')
										@php
											$contadorphp = $contadorphp+1;
										@endphp
									@endforeach
									{{-- @include('layouts.respel-comercial.respel-tarifas') --}}
								</div>
								@endif
								<div id="modalrango"></div>
								<!-- /.tab-pane fade -->
							</div>
							<!-- /.tab-content -->
						</div>
					</div>
					<!-- /.box body -->

					{{-- Campo de comentario para Prosarc temporalmente deshabilitado mientras se realiza mejora --}}
					{{-- <div class="box-footer" style="background-color: #f4f4f4; border-top: 1px solid #ddd; padding: 20px;">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label>
										<i class="fas fa-comments"></i> Comentario sobre los cambios realizados <span class="text-danger">*</span>
									</label>
									<small class="help-block">
										<strong>Obligatorio:</strong> Explique qué cambios está realizando y por qué son necesarios.
									</small>
									<textarea id="respel_comentario_prosarc" name="respel_comentario" class="form-control" rows="3" maxlength="1000" placeholder="Ejemplo: Actualizando clasificación según nueva normativa, corrigiendo estado físico, etc." required></textarea>
									<small class="text-muted">
										<span id="char-count-prosarc">0</span>/1000 caracteres
									</small>
								</div>
							</div>
						</div> --}}
						{{-- Campo oculto para mantener compatibilidad --}}
						<input type="hidden" name="respel_comentario" value="">
						<div class="box-footer" style="background-color: #f4f4f4; border-top: 1px solid #ddd; padding: 20px;">
						<div class="row">
							<div class="col-md-12">
								<button class="btn btn-success" type="button" id="btn-actualizar-prosarc" style="margin-right:5em"><i class="fa fa-check"></i>{{ __('adminlte::LangRespel.updaterespelButton') }}</button>
								<a class="btn btn-danger btn-close pull-right" style="margin-right: 2rem;" href="{{ route('respels.index') }}"><i class="fas fa-times"></i> {{ __('adminlte::LangTratamiento.cancel') }}</a>
							</div>
						</div>
					</div>
					{{-- @php
					foreach ($contadorRango as $key => $value) {
						foreach ($value as $key2 => $value2) {
							echo $value2;
						}
					}
					@endphp --}}
					<!-- /.nav-tabs-custom -->
				</div>
				<!-- /.box -->
			</div>
			<!-- /.col md9 -->


		<!-- /.row -->
	</form>
	<!-- /.form  -->
</div>

@if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA))
<!-- Modal Historial de Cambios - Solo visible para Dirección Técnica -->
<div class="modal modal-default fade" id="ModalHistorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<i class="fas fa-history"></i> Historial de Cambios del Residuo
				</h4>
			</div>
			<div class="modal-body">
				<!-- Contenido del Historial -->
				<div>
						@if(isset($auditHistory) && $auditHistory->count() > 0)
							@foreach($auditHistory as $audit)
								<div class="media" style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
									<div class="media-left">
										@switch($audit->AuditType)
											@case('Creado')
											@case('Nuevo respel')
												<div class="btn btn-success btn-circle"><i class="fa fa-plus"></i></div>
												@break
											@case('Modificado')
												<div class="btn btn-warning btn-circle"><i class="fa fa-edit"></i></div>
												@break
											@case('Estado Evaluado')
												<div class="btn btn-info btn-circle"><i class="fa fa-check"></i></div>
												@break
											@case('Eliminado')
												<div class="btn btn-danger btn-circle"><i class="fa fa-trash"></i></div>
												@break
											@default
												<div class="btn btn-default btn-circle"><i class="fa fa-info"></i></div>
										@endswitch
									</div>
									<div class="media-body">
										<h5 class="media-heading">
											<strong>{{ $audit->AuditType }}</strong>
											<small class="text-muted pull-right">
												<i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') }}
											</small>
										</h5>
										<p class="text-muted">
											<i class="fa fa-user"></i> {{ $audit->AuditUser }}
											<span class="text-muted"> • {{ \Carbon\Carbon::parse($audit->created_at)->diffForHumans() }}</span>
										</p>

										@php
											$auditData = is_string($audit->Auditlog) ? json_decode($audit->Auditlog, true) : $audit->Auditlog;
										@endphp

										@if($audit->AuditType == 'Creado' || $audit->AuditType == 'Nuevo respel')
											<div class="alert alert-success" style="margin-top: 10px;">
												@if(is_array($auditData) && isset($auditData['respel_name']))
													<strong><i class="fa fa-plus-circle"></i> Residuo creado:</strong> {{ $auditData['respel_name'] }}<br>
													<strong>Estado inicial:</strong> {{ $auditData['respel_status'] ?? 'N/A' }}
												@else
													<strong><i class="fa fa-plus-circle"></i> Residuo creado</strong><br>
													<small style="color: white;">Registro de creación del residuo en el sistema</small>
													@if(is_string($audit->Auditlog) && !is_array($auditData))
														<br><small style="color: rgba(255,255,255,0.8);">Datos legacy del sistema</small>
													@endif
												@endif
											</div>
										@elseif($audit->AuditType == 'Estado Evaluado')
											<div class="alert alert-info" style="margin-top: 10px;">
												<strong><i class="fa fa-check-circle"></i> Evaluación de estado:</strong><br>
												<strong>Estado anterior:</strong>
												<span class="label label-default">{{ $auditData['previous_status'] ?? 'N/A' }}</span>
												→
												<span class="label label-primary">{{ $auditData['new_status'] ?? 'N/A' }}</span><br>
												@if(isset($auditData['status_description']) && $auditData['status_description'])
													<strong>Descripción:</strong> {{ $auditData['status_description'] }}<br>
												@endif
												<strong>Rol evaluador:</strong> {{ $auditData['user_role'] ?? 'N/A' }}
											</div>
										@elseif($audit->AuditType == 'Modificado')
											@if(is_array($auditData))
												@php
													$ignoreFields = ['_token', '_method', 'Sede', 'action', 'user_role', 'user_role2', 'ip_address', 'user_agent', 'comentario_agregado'];
													$friendlyNames = [
														'RespelName' => 'Nombre del residuo',
														'RespelDescrip' => 'Descripción',
														'RespelIgrosidad' => 'Peligrosidad',
														'RespelEstado' => 'Estado físico',
														'RespelStatus' => 'Estado de aprobación',
														'RespelStatusDescription' => 'Descripción del estado',
														'YRespelClasf4741' => 'Clasificación Y-4741',
														'ARespelClasf4741' => 'Clasificación A-4741',
														'SustanciaControlada' => 'Sustancia controlada',
														'SustanciaControladaTipo' => 'Tipo de sustancia controlada',
														'SustanciaControladaNombre' => 'Nombre de sustancia controlada',
														'AceiteUsado' => 'Aceite usado',
														'RespelDeclaracion' => 'Declaración',
														'RespelTratamiento' => 'Tratamiento asignado'
													];
													$hasChanges = false;
												@endphp
												<div class="alert alert-warning" style="margin-top: 10px;">
													<strong><i class="fa fa-edit"></i> Cambios realizados:</strong>
													<div style="margin-top: 10px;">
														@foreach($auditData as $key => $value)
															@if(!in_array($key, $ignoreFields) && is_array($value) && isset($value['old']) && isset($value['new']))
																@php $hasChanges = true; @endphp
																<div class="row" style="margin-bottom: 5px;">
																	<div class="col-md-4"><strong>{{ $friendlyNames[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}:</strong></div>
																	<div class="col-md-4">
																		<span class="text-muted">{{ $value['old'] ?: 'Sin valor' }}</span>
																	</div>
																	<div class="col-md-4">
																		<span class="text-success"><strong>{{ $value['new'] ?: 'Sin valor' }}</strong></span>
																	</div>
																</div>
															@endif
														@endforeach
														@if(!$hasChanges)
															<span class="text-muted">Sin cambios específicos registrados.</span>
														@endif
														@if(isset($auditData['comentario_agregado']) && $auditData['comentario_agregado'])
															<div class="text-info" style="margin-top: 10px;">
																<i class="fa fa-comment"></i> <strong>Se agregó un comentario</strong>
															</div>
														@endif
													</div>
												</div>
											@endif
										@elseif($audit->AuditType == 'Eliminado')
											<div class="alert alert-danger" style="margin-top: 10px;">
												<strong><i class="fa fa-trash"></i> Residuo eliminado</strong><br>
												@if(is_string($audit->Auditlog))
													<strong>Motivo:</strong> {{ $audit->Auditlog }}
												@endif
											</div>
										@else
											<div class="alert alert-info" style="margin-top: 10px;">
												<strong><i class="fa fa-info-circle"></i> {{ $audit->AuditType }}</strong><br>
												@if(is_array($auditData))
													@if(count($auditData) > 0)
														<small class="text-muted">Detalles de la operación:</small>
														<ul style="margin-top: 5px; margin-bottom: 0;">
															@foreach($auditData as $key => $value)
																@if(!in_array($key, ['_token', '_method']) && is_string($value) && strlen($value) < 100)
																	<li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
																@endif
															@endforeach
														</ul>
													@else
														<small class="text-muted">Operación realizada en el sistema</small>
													@endif
												@elseif(is_string($audit->Auditlog) && strlen($audit->Auditlog) < 200)
													<small class="text-muted">{{ $audit->Auditlog }}</small>
												@else
													<small class="text-muted">Registro de actividad en el sistema</small>
												@endif
											</div>
										@endif
									</div>
								</div>
							@endforeach
						@else
							<div class="callout callout-info">
								<h4><i class="icon fa fa-info"></i> Sin historial</h4>
								<p>No hay cambios registrados para este residuo.</p>
							</div>
						@endif
				</div>
				<!-- /.Contenido del Historial -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
@endif
<!-- /.Modal Historial de Cambios - Solo Dirección Técnica -->

<!-- Modal de Confirmación de Cambios -->
<div class="modal modal-default fade" id="ModalConfirmarCambios" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i> Confirmar Actualización del Residuo
				</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="callout callout-warning">
							<h4><i class="icon fa fa-warning"></i> ¡Importante!</h4>
							<p>Está a punto de actualizar la información del residuo. Esta acción quedará registrada en el historial del sistema.</p>
						</div>

						{{-- Sección de comentario temporalmente deshabilitada mientras se realiza mejora --}}
						{{-- <div class="form-group">
							<label><strong>Motivo del cambio que documentó:</strong></label>
							<div class="well well-sm" id="comentario-preview" style="background-color: #f8f9fa; border-left: 3px solid #007bff;">
								<!-- El comentario se mostrará aquí -->
							</div>
						</div> --}}

						<div class="alert alert-info">
							<i class="fas fa-info-circle"></i>
							<strong>¿Está seguro de que desea continuar?</strong><br>
							<small>Verifique que toda la información sea correcta antes de confirmar.</small>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<i class="fa fa-times"></i> Cancelar
				</button>
				<button type="button" id="btn-confirmar-definitivo" class="btn btn-success">
					<i class="fa fa-check"></i> Sí, Actualizar Residuo
				</button>
			</div>
		</div>
	</div>
</div>
<!-- /.Modal Confirmación -->

@endsection

@section('NewStyle')
<style>
.btn-circle {
	width: 45px;
	height: 45px;
	border-radius: 50%;
	text-align: center;
	font-size: 16px;
	line-height: 1.42857;
	display: flex;
	align-items: center;
	justify-content: center;
}

.media-left .btn-circle {
	margin-right: 10px;
}

.tab-content {
	max-height: 500px;
	overflow-y: auto;
}

.media {
	border-left: 3px solid transparent;
	padding-left: 10px;
}

.media:hover {
	background-color: #f9f9f9;
	border-left-color: #3c8dbc;
}

.modal-lg {
	width: 90%;
	max-width: 900px;
}

.alert {
	border-left: 4px solid;
}

.alert-success {
	border-left-color: #5cb85c;
}

.alert-warning {
	border-left-color: #f0ad4e;
}

.alert-info {
	border-left-color: #5bc0de;
}

.alert-danger {
	border-left-color: #d9534f;
}

.well-sm {
	border-left: 3px solid #ddd;
	background-color: #f8f9fa;
}
</style>
@endsection

@section('NewScript')
	<!-- SweetAlert2 (fallback) -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script type="text/javascript">
		var contador = <?php echo isset($contadorphp) ? $contadorphp : 0; ?>;
		var contadorRango = [];

		// Variables para el control de comentarios
		var formOriginalData = {};
		var currentFormData = {};

		$(document).ready(function() {
			console.log('Document ready - edit.blade.php'); // Debug
			// Capturar datos originales del formulario
			captureOriginalFormData();

			{{-- Contador de caracteres temporalmente deshabilitado mientras se realiza mejora --}}
			{{-- // Contador de caracteres para el comentario (Cliente)
			$('#respel_comentario').on('input', function() {
				var length = $(this).val().length;
				$('#char-count').text(length);

				if (length > 950) {
					$('#char-count').addClass('text-danger');
				} else if (length > 800) {
					$('#char-count').addClass('text-warning').removeClass('text-danger');
				} else {
					$('#char-count').removeClass('text-warning text-danger');
				}
			});

			// Contador de caracteres para el comentario (Prosarc)
			$('#respel_comentario_prosarc').on('input', function() {
				var length = $(this).val().length;
				$('#char-count-prosarc').text(length);

				if (length > 950) {
					$('#char-count-prosarc').addClass('text-danger');
				} else if (length > 800) {
					$('#char-count-prosarc').addClass('text-warning').removeClass('text-danger');
				} else {
					$('#char-count-prosarc').removeClass('text-warning text-danger');
				}
			}); --}}

			// Verificar si los elementos existen
			console.log('Formulario #myform encontrado:', $('#myform').length); // Debug
			console.log('Elemento respel_comentario encontrado:', $('#respel_comentario').length); // Debug
			console.log('Botón #btn-actualizar-respel encontrado:', $('#btn-actualizar-respel').length); // Debug

			// Interceptar específicamente el botón con ID único (Cliente)
			$('#btn-actualizar-respel').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				{{-- Validación de comentario temporalmente deshabilitada mientras se realiza mejora --}}
				{{-- // Validar comentario cliente si existe el campo
				var comentarioEl = $('#respel_comentario');
				if (comentarioEl.length) {
					var cmt = (comentarioEl.val() || '').trim();
					if (cmt.length < 10) {
						Swal.fire({
							icon: 'error',
							title: 'Comentario requerido',
							text: 'Debe proporcionar un comentario de al menos 10 caracteres.',
							confirmButtonText: 'Entendido'
						});
						comentarioEl.focus();
						return false;
					}
					$('#comentario-preview').html(nl2br(cmt));
				} --}}
				$('#ModalConfirmarCambios').modal('show');

				return false; // Prevenir envío hasta confirmación
			});

			// Interceptar específicamente el botón de Prosarc
			$('#btn-actualizar-prosarc').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				{{-- Validación de comentario temporalmente deshabilitada mientras se realiza mejora --}}
				{{-- // Verificar que el comentario no esté vacío
				var comentarioElement = $('#respel_comentario_prosarc');
				if (comentarioElement.length === 0) {
					console.error('Elemento respel_comentario_prosarc no encontrado');
					return false;
				}

				var comentario = comentarioElement.val();
				if (!comentario) {
					comentario = '';
				}
				comentario = comentario.trim();

				if (comentario.length < 10) {
					Swal.fire({
						icon: 'error',
						title: 'Comentario requerido',
						text: 'Debe proporcionar un comentario descriptivo de al menos 10 caracteres explicando los cambios realizados.',
						confirmButtonText: 'Entendido'
					});
					comentarioElement.focus();
					return false;
				}

				// Mostrar el comentario en el modal de confirmación
				$('#comentario-preview').html(nl2br(comentario)); --}}

				// Mostrar modal de confirmación
				$('#ModalConfirmarCambios').modal('show');

				return false; // Prevenir envío hasta confirmación
			});

			// Manejar confirmación definitiva
			$('#btn-confirmar-definitivo').click(function() {
				// Cerrar modal
				$('#ModalConfirmarCambios').modal('hide');

				// Mostrar loading
				Swal.fire({
					title: 'Actualizando residuo...',
					text: 'Por favor espere mientras se procesan los cambios.',
					allowOutsideClick: false,
					allowEscapeKey: false,
					showConfirmButton: false,
					willOpen: () => {
						Swal.showLoading();
					}
				});

				// Determinar qué formulario enviar (usar submit nativo para evitar handlers que dependan de e.target.id)
				if ($('#myform').length > 0) {
					// Vista Cliente
					var formCliente = document.getElementById('myform');
					if (formCliente && typeof formCliente.submit === 'function') {
						formCliente.submit();
					}
				} else if ($('#evaluacioncomercial').length > 0) {
					// Vista Prosarc
					var formProsarc = document.getElementById('evaluacioncomercial');
					if (formProsarc && typeof formProsarc.submit === 'function') {
						formProsarc.submit();
					}
				}
			});
		});

		// Función para capturar datos originales
		function captureOriginalFormData() {
			formOriginalData = {};
			$('form input, form select, form textarea').each(function() {
				var $element = $(this);
				var name = $element.attr('name');
				if (name && name !== 'respel_comentario') {
					if ($element.is(':checkbox') || $element.is(':radio')) {
						formOriginalData[name] = $element.is(':checked');
					} else {
						formOriginalData[name] = $element.val();
					}
				}
			});
		}

		// Función para verificar si hay cambios
		function hasFormChanges() {
			var hasChanges = false;
			$('form input, form select, form textarea').each(function() {
				var $element = $(this);
				var name = $element.attr('name');
				if (name && name !== 'respel_comentario' && name !== '_token' && name !== '_method') {
					var currentValue;
					if ($element.is(':checkbox') || $element.is(':radio')) {
						currentValue = $element.is(':checked');
					} else {
						currentValue = $element.val();
					}

					if (formOriginalData[name] !== currentValue) {
						hasChanges = true;
						return false; // break
					}
				}
			});
			return hasChanges;
		}

		// Función para convertir saltos de línea a <br>
		function nl2br(str) {
			return str.replace(/\n/g, '<br>');
		}


		function SelectsRangoTipo(id){
			$('#typerangeSelect'+id).select2({
				allowClear: true,
				tags: true,
				width: 'resolve',
				width: '100%',
				theme: "classic",
			});
		}
		/*desactivar el envio de formulario al usar el boton de agregar opcion*/
		$("#addOptionButton").click(function(event) {
		  event.preventDefault();
		});
		function validarprevent(id){
			$("#droOptionButton"+id).click(function(event) {
			  event.preventDefault();
			});
			$("#addrangeButton"+id).click(function(event) {
			  event.preventDefault();
			});
		}
		function recargarAjaxTratamiento(contador){
			selector = $("#opciontratamiento"+contador);
			id = selector.val();
			if (!id) {
				return;
			}
				$.ajaxSetup({
				  headers: {
					  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				  }
				});
				$.ajax({
					url: "{{url('/preTratamientoDinamico')}}/"+id,
					method: 'GET',
					data:{},
					beforeSend: function(){
						$(".load").append('<i class="fas fa-sync-alt fa-spin"></i>');
						$("#pretratamiento").prop('disabled', true);
					},
					success: function(res){
						$("#pretratamiento"+contador).empty();
						var pretrataOption = new Array();
						for(var i = res.pretratamientos.length -1; i >= 0; i--){
							if ($.inArray(res.pretratamientos[i].ID_PreTrat, pretrataOption) < 0) {
								$("#pretratamiento"+contador).append(`<option value="${res.pretratamientos[i].ID_PreTrat}">${res.pretratamientos[i].PreTratName}</option>`);
								pretrataOption.push(res.pretratamientos[i].ID_PreTrat);
							}
						}
						if (pretrataOption.length === 0) {
							$("#pretratamiento"+contador).append('<option value="" disabled>El tratamiento elegido no tiene pretratamientos relacionados</option>');
						}
						$("#pretratamiento"+contador+"TratName").empty();
						$("#tarifa"+contador+"TratName").empty();
						$("#requerimiento"+contador+"TratName").empty();
						$("#pretratamiento"+contador+"TratName").append(" "+res.TratName);
						$("#tarifa"+contador+"TratName").append(" "+res.TratName);
						$("#requerimiento"+contador+"TratName").append(" "+res.TratName);

					},
					complete: function(){
						$(".load").empty();
						$("#pretratamiento").prop('disabled', false);
					},
					error: function (jqXHR, textStatus, errorThrown) {
						NotifiFalse("No se pudo conectar a la base de datos");
					}
				});

		}

		$(document).on('change', 'select[id^="opciontratamiento"]', function(){
			var contador = this.id.replace('opciontratamiento', '');
			recargarAjaxTratamiento(contador);
		});
		function AgregarOption(){
			if (typeof contadorRango === "undefined") {
				contadorRango = [];
			}
			if (typeof contador === "undefined") {
				contador = 0;
			}
			contadorRango[contador] = [];
			contadorRango[contador][0] = 0;

			// Usar AJAX para cargar los templates dinámicamente
			$.ajaxSetup({
				headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
			});

			// Cargar templates via AJAX o crear contenido directamente
			var tratamientoHtml = '<div id="tratamiento' + contador + 'Container">Contenido del tratamiento ' + contador + '</div>';
			var pretratamientoHtml = '<div id="pretratamiento' + contador + 'Container">Contenido del pretratamiento ' + contador + '</div>';
			var requerimientoHtml = '<div id="requerimiento' + contador + 'Container">Contenido del requerimiento ' + contador + '</div>';
			var tarifasHtml = '<div id="tarifa' + contador + 'Container">Contenido de tarifas ' + contador + '</div>';

			$("#Tratamientospane").append(tratamientoHtml);
			$("#Pretratamientospane").append(pretratamientoHtml);
			$("#Requerimientospane").append(requerimientoHtml);
			$("#Tarifaspane").append(tarifasHtml);

			if (typeof $("#evaluacioncomercial").validator === 'function') {
				$("#evaluacioncomercial").validator('update');
			}

			if (typeof popover === 'function') popover();
			if (typeof ChangeSelect === 'function') ChangeSelect();
			if (typeof SelectsRangoTipo === 'function') SelectsRangoTipo(contador);
			if (typeof Selects === 'function') Selects();
			if (typeof SwitchMain === 'function') SwitchMain();
			if (typeof SwitchAuto === 'function') SwitchAuto();
			if (typeof Switch7 === 'function') Switch7();
			validarprevent(contador);
			contador = parseInt(contador)+1;

		}
		function EliminarOption(contador){
			$("#tratamiento"+contador+"Container").remove();
			$("#pretratamiento"+contador+"Container").remove();
			$("#requerimiento"+contador+"Container").remove();
			$("#tarifa"+contador+"Container").remove();
			$("#evaluacioncomercial").validator('update');
		}
		function AgregarRango(opcion){
			if (contadorRango[opcion].length>1) {
				last=contadorRango[opcion].length-1;
			}else{
				last=1;
			}

			// Crear modal dinámicamente en lugar de usar includes
			var modalrangoHtml = '<div class="modal fade" id="createrank"><div class="modal-dialog"><div class="modal-content">Modal de rango</div></div></div>';
			$("#modalrango").empty();
			$("#modalrango").append(modalrangoHtml);

			window.addEventListener("keypress", function(event){
				if (event.keyCode == 13){
					event.preventDefault();
				}
			}, false);

			if (typeof popover === 'function') popover();
			$("#createrank").modal();
			$("#createrank").on("hidden.bs.modal", function () {
				var rango = $("#ranktarifa").val();
				if(rango != ''){
					var tarifaHtml = '<div id="rango' + opcion + last + '">Rango de tarifa</div>';
					$("#rango"+opcion+"row").append(tarifaHtml);
					if (typeof $("#evaluacioncomercial").validator === 'function') {
						$("#evaluacioncomercial").validator('update');
					}
					last=last+1;
					contadorRango[opcion][last] = last;
				}else{
					$("#modalrango").empty();
					if (typeof $("#evaluacioncomercial").validator === 'function') {
						$("#evaluacioncomercial").validator('update');
					}
				}
			});
		}
		function EliminarRango(opcion,rango){
			$("#rango"+opcion+rango).remove();
			$("#rangodefault"+opcion+rango).append('<input hidden  type="text" name="Opcion['+opcion+'][TarifaDesde][]" value=""><input hidden  type="text" name="Opcion['+opcion+'][TarifaPrecio][]" value="">');
			$("#evaluacioncomercial").validator('update');
			validarprevent(opcion);
		}
		$(document).ready(function(){
			ChangeSelect();
			Selects();
		});
	</script>
	<script type="text/javascript">
		function SwitchAuto() {
			$(".autoswitch").bootstrapSwitch({
				animate: true,
				labelText: '<i class="fas fa-power-off"></i>',
				onText: 'A',
				offText: 'M',
				onColor: 'success',
				offColor: 'danger',
				onSwitchChange: function () {
					updateMain($(this).data("switch"));
				}
			});
		}
		function updateMain(id) {
			main = $('#main_'+id);
			auto = $('#auto_'+id);
			if (auto.prop("checked")) {
				if (!main.prop("checked")) {
					main.bootstrapSwitch('state', true);
				}
			}
		}
		function SwitchMain() {
			$(".fotoswitchedit").bootstrapSwitch({
				animate: true,
				labelText: '<i class="fas fa-camera"></i>',
				onText: '<i class="fas fa-check"></i>',
				offText: '<i class="fas fa-times"></i>',
				onSwitchChange: function () {
					updateAuto($(this).data("switch"));
				}
			});
			$(".videoswitchedit").bootstrapSwitch({
				animate: true,
				labelText: '<i class="fas fa-video"></i>',
				onText: '<i class="fas fa-check"></i>',
				offText: '<i class="fas fa-times"></i>',
				onSwitchChange: function () {
					updateAuto($(this).data("switch"));
				}
			});
			$(".embalajeswitchedit").bootstrapSwitch({
				animate: true,
				labelText: '<i class="fas fa-trash"></i>',
				onText: '<i class="fas fa-check"></i>',
				offText: '<i class="fas fa-times"></i>',
				onSwitchChange: function () {
					updateAuto($(this).data("switch"));
				}
			});
			$(".auditoriaswitchedit").bootstrapSwitch({
				animate: true,
				labelText: '<i class="fas fa-eye"></i>',
				onText: '<i class="fas fa-check"></i>',
				offText: '<i class="fas fa-times"></i>',
				onSwitchChange: function () {
					updateAuto($(this).data("switch"));
				}
			});
		}
		function updateAuto(name) {
			var main = $('#main_'+name);
			var auto = $('#auto_'+name);
			if (!main.prop("checked")) {
				if (auto.prop("checked")) {
					auto.bootstrapSwitch('state', false);
				}
			}
		}
		$(document).ready(function() {
			SwitchAuto();
			SwitchMain();
		});
	</script>
@endsection
@endif

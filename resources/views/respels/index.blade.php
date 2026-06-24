@extends('layouts.app')
@section('htmlheader_title', __('adminlte::LangRespel.Respellist'))
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::LangRespel.respelmenu') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<!-- /.box -->
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::LangRespel.Respellist') }}</h3 class="pull-left">
				@if(
							in_array(Auth::user()->UsRol, array_merge(
								Permisos::RESPELPUBLIC,
								Permisos::INGDETURNO,
								Permisos::PROGRAMADOR,
								Permisos::JEFELOGISTICA,
								Permisos::ASISTENTELOGISTICA,
								Permisos::COMERCIALEINGRURNO,
								Permisos::ProgVehic1
							)) ||
							in_array(Auth::user()->UsRol2, array_merge(
								Permisos::RESPELPUBLIC,
								Permisos::INGDETURNO,
								Permisos::PROGRAMADOR,
								Permisos::JEFELOGISTICA,
								Permisos::ASISTENTELOGISTICA,
								Permisos::COMERCIALEINGRURNO,
								Permisos::ProgVehic1
							))
						)
						<div class="pull-right">
							
							<a href="respels/create" class="btn btn-primary">{{__('adminlte::LangRespel.CreaterespelButton')}}</a>
						</div>
				@endif
				@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP))
					<a href="vencidos" class="btn btn-primary pull-right"  style="float: right; margin-right: 0.5em;">Vencidos</a>
				@endif

				</div>
				<!-- /.box-header -->
				<div class="box box-info">
					<div class="box-body">
						{{-- Filtros de optimización --}}
						<form method="GET" action="{{ url('respels') }}" class="form-inline well well-sm" style="margin-bottom: 15px;">
							<input type="hidden" name="buscar" value="1">
							<div class="form-group" style="margin-right: 10px;">
								<label for="nombre" class="control-label" style="margin-right: 5px;">Nombre:</label>
								<input type="text" name="nombre" id="nombre" class="form-control input-sm" placeholder="Buscar por nombre..." value="{{ request('nombre') }}" style="min-width: 180px;">
							</div>
							@if(isset($clientesFiltro) && $clientesFiltro->isNotEmpty())
							<div class="form-group" style="margin-right: 10px;">
								<label for="cliente" class="control-label" style="margin-right: 5px;">Cliente:</label>
								<select name="cliente" id="cliente" class="form-control input-sm" style="min-width: 200px;">
									<option value="">Todos</option>
									@foreach($clientesFiltro as $c)
										<option value="{{ $c->ID_Cli }}" {{ request('cliente') == $c->ID_Cli ? 'selected' : '' }}>{{ $c->CliName }}</option>
									@endforeach
								</select>
							</div>
							@endif
							<div class="form-group" style="margin-right: 10px;">
								<label for="anio" class="control-label" style="margin-right: 5px;">Año:</label>
								<select name="anio" id="anio" class="form-control input-sm" style="min-width: 100px;">
									<option value="">Todos</option>
									@if(isset($aniosFiltro))
										@foreach($aniosFiltro as $a)
											<option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
										@endforeach
									@endif
								</select>
							</div>
							<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Buscar</button>
							<a href="{{ url('respels') }}?buscar=1" class="btn btn-default btn-sm" style="margin-left: 5px;" title="Ver todos los residuos (puede tardar más)"><i class="fas fa-list"></i> Ver todos</a>
							@if(request()->hasAny(['nombre','cliente','anio','ver','buscar']))
								<a href="{{ url('respels') }}" class="btn btn-default btn-sm" style="margin-left: 5px;"><i class="fas fa-times"></i> Limpiar</a>
							@endif
						</form>
						@if(!request()->hasAny(['nombre','cliente','anio','ver','buscar']) && $Respels->isEmpty())
							<div class="alert alert-info" style="margin-bottom: 15px;">
								<i class="fas fa-info-circle"></i>
								<strong>Búsqueda rápida:</strong> Seleccione <b>nombre</b>, <b>cliente</b> y/o <b>año</b> y haga clic en <b>Buscar</b>. O deje <b>Todos</b> y haga clic en <b>Buscar</b> para ver todos los residuos.
							</div>
						@endif
						<table class="table table-bordered table-striped" id="respelsTable">
							<thead>
								<tr>
									<th>Creado</th>
									<th>Actualizado</th>
									<th>{{__('adminlte::LangRespel.RespelName')}}</th>
									<th>Tratamiento Ofertado</th>
									<th>{{__('adminlte::LangRespel.Respelclas')}}</th>
									<th>{{__('adminlte::LangRespel.Respelhoja')}}</th>
									<th>{{__('adminlte::LangRespel.Respeltarj')}}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<th>{{__('adminlte::LangRespel.Respelcliente')}}</th>
									@endif
									<th>{{__('adminlte::LangRespel.RespelStatus')}}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<th nowrap><span><i style="color: Dodgerblue;" class="fas fa-info-circle fa-spin"></i></span>{{__('adminlte::LangRespel.Respelevaluar')}}</th>
									@else
										<th nowrap><span data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 100}' title="Status del Residuo" data-content="
									<p class='row'>
										<div class='col-md-6 col-sd-12 col-xs-12'>
											<ul>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-default'><i class='fas fa-lg fa-hourglass-start'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Pendiente</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-primary'><i class='fas fa-lg fa-list'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Evaluado</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-primary'><i class='fas fa-lg fa-comments-dollar'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Cotizado</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-success'><i class='fas fa-lg fa-thumbs-up'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Aprobado</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-success'><i class='fas fa-lg fa-check-double'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Revisado</b> </li>
											</ul>
										</div>
										<div class='col-md-6 col-sd-12 col-xs-12'>
											<ul>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-warning'><i class='fas fa-lg fa-tasks'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Incompleto</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-danger'><i class='fas fa-lg fa-ban'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Rechazado</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-warning'><i class='fas fa-lg fa-file-pdf'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Falta TDE</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-primary'><i class='fas fa-lg fa-file-pdf'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>TDE actualizada</b> </li>
												<li class='text-nowrap'><a class='fixed_widthbtn btn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a><i class='fas fa-lg fa-arrow-right'></i> <b>Vencido</b> </li>
											</ul>
										</div>
									</p>"><i style="color: Dodgerblue;" class="fas fa-info-circle fa-spin"></i></span>{{__('adminlte::LangRespel.Respelver')}}</th>
									@endif
									<th>{{__('adminlte::LangRespel.Respeligro')}}</th>
									<th>{{__('adminlte::LangRespel.Respelestado')}}</th>
									@if(in_array(Auth::user()->UsRol, ['Programador', 'DireccionTecnica']) || in_array(Auth::user()->UsRol2, ['Programador', 'DireccionTecnica']))
										<th>Acciones</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($Respels as $respel)
									@if($respel->RespelDelete == 1)
										<tr style="color: red;">
									@else
										<tr>
									@endif
									{{-- <td>{{ \Carbon\Carbon::parse($respel->updated_at)->diffForHumans() }}</td> --}}
									<td>{{ $respel->created_at }}</td>
									<td>{{ $respel->updated_at }}</td>
									<td class="text-center">
										@if($respel->SustanciaControlada == 1)
											@if ($respel->SustanciaControladaTipo == 0)
												<a title="Sustancia Controlada"><i class="fas fa-flask" style="color: green"></i></a>
											@endif
											@if ($respel->SustanciaControladaTipo == 1)
												<a title="Sustancia de uso masivo"><i class="fas fa-flask" style="color: blue"></i></a>
											@endif
										@endif
										{{$respel->RespelName}}</td>
									<td class="text-center">{{$respel->TratName}}</td>

									@if ($respel->RespelIgrosidad)
										@if($respel->YRespelClasf4741 <> null)
											<td class="text-center">{{$respel->YRespelClasf4741}}</td>
										@elseif($respel->ARespelClasf4741 <> null)
											<td class="text-center">{{$respel->ARespelClasf4741}}</td>
										@else
											<td class="text-center">N/D</td>
										@endif
									@else
										<td class="text-center">N/A</td>
									@endif
									


									@if($respel->RespelHojaSeguridad!=="RespelHojaDefault.pdf")
										<td class="text-center"><a method='get' href='/img/HojaSeguridad/{{$respel->RespelHojaSeguridad}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></a></td>
									@else
										<td class="text-center"><a disabled method='get' href='/img/{{$respel->RespelHojaSeguridad}}' class='btn btn-default'><i class='fas fa-file-pdf fa-lg'></a></td>
									@endif

									@if($respel->RespelTarj!=="RespelTarjetaDefault.pdf")
										<td class="text-center"><a method='get' href='/img/TarjetaEmergencia/{{$respel->RespelTarj}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></a></td>
									@else
										<td class="text-center"><a disabled method='get' href='/img/{{$respel->RespelTarj}}' class='btn btn-default'><i class='fas fa-file-pdf fa-lg'></a></td>
									@endif

									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) ||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP))
										<td class="text-center">{{$respel->CliName}}</td>
									@endif
									<td class="text-center">{{$respel->RespelStatus}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE))
										@switch($respel->RespelStatus)
											{{-- evaluación pendiente --}}
											@case('Pendiente')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Pendiente</b>" data-content="<p style='width: 50%'>La información de su residuo debe ser analizada para asignarle un tratamiento adecuado y las tarifas que les corresponden segun el tratamiento... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-default'><i class='fas fa-lg fa-hourglass-start'></i></a></td>
												@break
											{{-- residuo Rechazado --}}
											@case('Rechazado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Rechazado</b>" data-content="<p style='width: 50%'>La viabilización de su residuo ha sido rechazada y/o no se disponen de tratamientos acordes a sus necesidades... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b></p>" class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-ban'></i></a></td>
												@break
											{{-- residuo Evaluado --}}
											@case('Evaluado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Evaluado</b>" data-content="<p style='width: 50%'>El Residuo ya posee un tratamiento viable asignados, sin embargo, debe esperar a que se le asignen las tarifas de acuerdo al tratamiento... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b></p>" class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-list'></i></a></td>
												@break
											{{-- residuo Cotizado --}}
											@case('Cotizado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Cotizado</b>" data-content="<p style='width: 50%'>El Residuo ya posee tratamiento asignado, sin embargo, se debe esperar a que las tarifas sean aprobadas <br>Para mas detalles comuníquese con la <b>Asesor Comercial</b></p>" class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-comments-dollar'></i></a></td>
												@break
											{{-- residuo Aprobado --}}
											@case('Aprobado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Aprobado</b>" data-content="<p style='width: 50%'>La evaluacion de su residuo a sido aprobada y puede comenzar a ralacionar el residuo con los generadores para realizar solicitudes de servicio... recuerde revisar la información del tratamiento y los requerimientos aprobados <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-thumbs-up'></i></a></td>
												@break
											{{-- cotización vencida --}}
											@case('Vencido')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Vencido</b>" data-content="<p style='width: 50%'>Las tarifas asignadas a su residuo exceden de la fecha aprobada por lo cual podrían ser facturadas a un precio diferente... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a></td>
												@break
											{{-- Información Incompleta --}}
											@case('Incompleto')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Incompleto</b>" data-content="<p style='width: 50%'>La información suministrada en el registro de su residuo no es suficiente para poder asignar un tratamiento viable... <br>Por favor comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-tasks'></i></a></td>
												@break
											{{-- Información Revisado --}}
											@case('Revisado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Revisado</b>" data-content="<p style='width: 50%'>Su residuo ha sido revisado por el área de logística y cuenta con la documentación necesaria para ser transportado por nuestros vehículos... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-check-double'></i></a></td>
												@break
											{{-- Información Revisado --}}
											@case('Falta TDE')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Tarjeta de emergencia no valida</b>" data-content="<p style='width: 50%'>La tarjeta de emergencia adjuntada no corresponde con la información de su residuo, debe adjuntar la tarjeta de emergencia correcta para que sus solicitudes de servicio puedan ser programadas con los vehículos de <b>Prosarc S.A. ESP.</b> ... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- TDE actualizada --}}
											@case('TDE actualizada')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Tarjeta de emergencia actualizada</b>" data-content="<p style='width: 50%'>La tarjeta de emergencia adjuntada debe ser revisada por <b>Prosarc S.A. ESP.</b>... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- opción default --}}
											@default
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Status Pendiente</b>" data-content="<p style='width: 50%'>La información de su residuo debe ser analizada para asignarle un tratamiento adecuado y las tarifas que les corresponden segun el tratamiento... <br>Para mas detalles comuníquese con su <b>Asesor Comercial</b> </p>" class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-ban'></i></a></td>
										@endswitch
									@elseif(in_array(Auth::user()->UsRol, Permisos::GrupoEvaluacionRespel)||in_array(Auth::user()->UsRol2, Permisos::GrupoEvaluacionRespel)||in_array(Auth::user()->UsRol, Permisos::COMERCIALAP))
										@switch($respel->RespelStatus)
											{{-- evaluación pendiente --}}
											@case('Pendiente')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-default'><i class='fas fa-lg fa-hourglass-start'></i></a></td>
												@break
											{{-- residuo Rechazado --}}
											@case('Rechazado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-ban'></i></a></td>
												@break
											{{-- residuo Evaluado --}}
											@case('Evaluado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-list'></i></a></td>
												@break
											{{-- residuo Cotizado --}}
											@case('Cotizado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-comments-dollar'></i></a></td>
												@break
											{{-- residuo Aprobado --}}
											@case('Aprobado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-thumbs-up'></i></a></td>
												@break
											{{-- cotización vencida --}}
											@case('Vencido')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a></td>
												@break
											{{-- información del residuo incompleta --}}
											@case('Incompleto')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-tasks'></i></a></td>
												@break
											{{-- Residuo Revisado --}}
											@case('Revisado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-check-double'></i></a></td>
												@break
											{{-- falta la TDE --}}
											@case('Falta TDE')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- TDE actualizada --}}
											@case('TDE actualizada')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- opción default --}}
											@default
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}/edit' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-search'></i></a></td>
										@endswitch
									@else
										@switch($respel->RespelStatus)
											{{-- evaluación pendiente --}}
											@case('Pendiente')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-default'><i class='fas fa-lg fa-hourglass-start'></i></a></td>
												@break
											{{-- residuo Rechazado --}}
											@case('Rechazado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-ban'></i></a></td>
												@break
											{{-- residuo Evaluado --}}
											@case('Evaluado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-list'></i></a></td>
												@break
											{{-- residuo Cotizado --}}
											@case('Cotizado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-comments-dollar'></i></a></td>
												@break
											{{-- residuo Aprobado --}}
											@case('Aprobado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-thumbs-up'></i></a></td>
												@break
											{{-- cotización vencida --}}
											@case('Vencido')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a></td>
												@break
											{{-- información del residuo incompleta --}}
											@case('Incompleto')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-tasks'></i></a></td>
												@break
											{{-- Residuo Revisado --}}
											@case('Revisado')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-check-double'></i></a></td>
												@break
											{{-- falta la TDE --}}
											@case('Falta TDE')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- TDE actualizada --}}
											@case('TDE actualizada')
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-file-pdf'></i></a></td>
												@break
											{{-- opción default --}}
											@default
												<td class="text-center"><a method='get' href='/respels/{{$respel->RespelSlug}}' class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-search'></i></a></td>
										@endswitch
									@endif
									<td class="text-center">{{$respel->RespelIgrosidad}}</td>
									<td class="text-center">{{$respel->RespelEstado}}</td>
									@if(in_array(Auth::user()->UsRol, ['Programador', 'DireccionTecnica']) || in_array(Auth::user()->UsRol2, ['Programador', 'DireccionTecnica']))
										<td class="text-center">
											@if($respel->RespelStatus == 'Pendiente' || $respel->RespelStatus == 'Incompleto' || $respel->RespelStatus == 'Rechazado')
												<button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{$respel->RespelSlug}}', '{{$respel->RespelName}}')" title="Eliminar residuo">
													<i class="fas fa-trash"></i>
												</button>
											@else
												<button type="button" class="btn btn-secondary btn-sm" disabled title="No se puede eliminar en este estado">
													<i class="fas fa-trash"></i>
												</button>
											@endif
										</td>
									@endif
								</tr>
								@endforeach
							</tbody>
							{{-- <tfoot>
							<tr>
								<th>Nombre</th>
								<th>Clasificación 4741 Y</th>
								<th>Clasificación 4741 A</th>
								<th>Peligrosidad</th>
								<th>Estado del residuo</th>
								<th>Hoja de Seguridad</th>
								<th>Tarj. de Emergencia</th>
								<th>Estado</th>
								<th>Generado por</th>
								<th>Ver Más...</th>
							</tr>
							</tfoot> --}}
						</table>
					</div>
				</div>
				<!-- /.box-body -->
			</div>
			<!-- /.box -->
		</div>
	</div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="fas fa-exclamation-triangle text-warning"></i> 
					Confirmar Eliminación
				</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p><strong>¿Está seguro que desea eliminar el siguiente residuo?</strong></p>
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i>
					<strong>Residuo:</strong> <span id="residuoName"></span>
				</div>
				<p class="text-muted">
					<small>Esta acción marcará el residuo como eliminado y no será visible en las listas principales.</small>
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<i class="fas fa-times"></i> Cancelar
				</button>
				<form id="deleteForm" method="POST" style="display: inline;">
					@csrf
					@method('DELETE')
					<button type="submit" class="btn btn-danger">
						<i class="fas fa-trash"></i> Confirmar Eliminación
					</button>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection

@section('NewScript')
<script>
$(document).ready(function() {
	// Evitar "Cannot reinitialise DataTable": el layout ya inicializa .table
	if ($.fn.DataTable && $.fn.DataTable.isDataTable('#respelsTable')) {
		$('#respelsTable').DataTable().destroy();
	}
	$('#respelsTable').DataTable({
		"dom": "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
			"<'row'<'col-md-12'tr>>" +
			"<'row'<'col-md-6'i><'col-md-6'p>>",
		"scrollX": false,
		"autoWidth": true,
		"colReorder": true,
		"ordering": true,
		"order": [0, 'desc'],
		"searchHighlight": true,
		"responsive": true,
		"keys": true,
		"lengthChange": true,
		"searching": true,
		"buttons": [
			{extend: 'colvis', text: 'Columnas Visibles'},
			{extend: 'copy', text: 'Copiar'},
			{extend: 'excel', text: 'Excel'}
		],
		"language": {
			"sProcessing": "Procesando...",
			"sLengthMenu": "Mostrar _MENU_ registros",
			"sZeroRecords": "No se encontraron resultados",
			"sEmptyTable": "Ningún dato disponible en esta tabla",
			"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
			"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix": "",
			"sSearch": "Buscar:",
			"sUrl": "",
			"sInfoThousands": ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate": {
				"sFirst": "Primero",
				"sLast": "Último",
				"sNext": "Siguiente",
				"sPrevious": "Anterior"
			},
			"oAria": {
				"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		}
	});
});

function confirmDelete(slug, name) {
	// Actualizar el contenido del modal
	document.getElementById('residuoName').textContent = name;
	
	// Configurar la acción del formulario
	document.getElementById('deleteForm').action = '/respels/' + slug;
	
	// Mostrar el modal
	$('#confirmDeleteModal').modal('show');
}

// Manejar el envío del formulario
document.getElementById('deleteForm').addEventListener('submit', function(e) {
	// Agregar spinner al botón
	const submitBtn = this.querySelector('button[type="submit"]');
	const originalText = submitBtn.innerHTML;
	submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
	submitBtn.disabled = true;
	
	// El formulario se enviará normalmente
});
</script>
@endsection
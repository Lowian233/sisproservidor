@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::LangRespel.Respelcreate') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::LangRespel.Respelcreate') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<!-- Default box -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::LangRespel.Respelcreate') }}</h3>
				</div>
					<div class="box box-info">
						<form role="form" action="/respels" method="POST" id="myform" enctype="multipart/form-data" data-toggle="validator" >
							@csrf
							@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
									<li>{{$error}}</li>
									@endforeach
								</ul>
							</div>
							@endif
							<div class="box-body">
								@if(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::RESPEL) || in_array(Auth::user()->UsRol, Permisos::INGDETURNO) || in_array(Auth::user()->UsRol2, Permisos::INGDETURNO) || in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA))
									<div class="col-md-12 form-group">
										<label for="Sede">{{ __('adminlte::LangRespel.createcliente') }}</label>
										<small class="help-block with-errors">*</small>
										<select name="Sede" id="Sede" class="form-control" required>
											<option value="">{{ __('adminlte::LangRespel.selecthem') }}</option>
											@foreach($Generadores as $Generador)
												<option value="{{$Generador->ID_Sede}}">
													{{$Generador->GenerName}} - {{$Generador->CliName}} 
													@if($Generador->SedeName)
														({{$Generador->SedeName}})
													@endif
												</option>
											@endforeach
										</select>
									</div>
								<!-- {{-- Botón para agregar múltiples residuos para el mismo generador --}}
									@if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::RESPEL))
										<div class="col-md-12" style="margin-bottom: 15px;">
											<div class="alert alert-info" style="margin-bottom: 10px;">
												<i class="fa fa-info-circle"></i> <strong>Tip:</strong> Puede agregar múltiples residuos para este mismo generador haciendo clic en el botón "+ Agregar otro residuo" debajo del formulario.
											</div>
										</div> 
									@endif-->
									{{-- Campo de comentario para Dirección Técnica (aprobación directa) --}}
									@if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA))
										<div class="col-md-12 form-group">
											<!-- <div class="alert alert-success" style="margin-bottom: 10px;">
												<i class="fa fa-check-circle"></i> <strong>Aprobación Automática:</strong> Como usuario de Dirección Técnica, los residuos que cree serán aprobados automáticamente.
											</div> -->
											<label for="respel_comentario_aprobacion">
												<i class="fa fa-comment"></i> Comentario de Aprobación <small class="help-block with-errors">*</small>
											</label>
											<textarea 
												name="respel_comentario_aprobacion" 
												id="respel_comentario_aprobacion" 
												class="form-control" 
												rows="3" 
												required
												placeholder="REQUERIDO: Indique el motivo de aprobación, consideraciones técnicas, protocolo aplicado, etc."></textarea>
											<small class="text-danger">
												<i class="fa fa-exclamation-triangle"></i> 
												<strong>Este campo es obligatorio</strong> para dejar evidencia de la aprobación automática en el historial del residuo.
											</small>
										</div>
									@endif
								@elseif(in_array(Auth::user()->UsRol, Permisos::CLIENTE)|| in_array(Auth::user()->UsRol, Permisos::INGDETURNO))
									<input type="text" name="Sede" style="display: none;" value="{{$Sede}}">
								@endif
								@if(in_array(Auth::user()->UsRol, Permisos::RESPELPUBLIC)||in_array(Auth::user()->UsRol2, Permisos::RESPELPUBLIC))
								{{-- Categoria --}}
								<div class="col-md-6 form-group has-feedback">
									<label>Categoría</label><small class="help-block with-errors">*</small>
									<select id="selectCategory" class="form-control" data-dependent="FK_SubCategoryRP">
										<option value="">Seleccione una categoría...</option>
										@foreach($categories as $category)
											@if($category->CategoryRpName !== 'Residuo Común')
												<option value="{{$category->ID_CategoryRP}}">{{$category->CategoryRpName}}</option>
											@endif
										@endforeach
									</select>
								</div>

								{{-- SubCategoria --}} 
								<div class="col-md-6 form-group has-feedback">
									<label>SubCategoría</label><a class="load"></a><small class="help-block with-errors">*</small>
									<select id="subcategorycontainer" name="FK_SubCategoryRP" class="form-control" required>
									</select>
								</div>
								@endif
								@include('layouts.RespelPartials.respelform1')
							</div>
							<!-- /.box-body -->
							<div class="box box-info">
								<div class="box-footer">
									@if(in_array(Auth::user()->UsRol, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol2, Permisos::DIRECCIONTECNICA) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::RESPEL))
										<a onclick="AgregarRes()" class="btn btn-primary"><i class="fa fa-plus"></i> Agregar otro residuo para este generador</a>
									@endif
									<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::LangRespel.registerrespelButton') }}</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

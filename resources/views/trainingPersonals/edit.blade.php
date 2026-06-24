@extends('layouts.app')
@section('htmlheader_title','Documentos del personal')
@section('contentheader_title','Editar documento')
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<!-- Default box -->
			<div class="box">
				<div class="box-header">
					@component('layouts.partials.modal')
						@slot('slug')
							{{$CapaPer->ID_CapPers}}
						@endslot
						@slot('textModal')
							la capacitacion <b>{{$CapaPer->ID_CapPers}}</b> a la persona <b>{{$CapaPer->FK_Pers}}</b>
						@endslot
					@endcomponent
					<h3 class="box-title">Editar documento del personal</h3>
					@if($CapaPer->CapaPersDelete === 0)
						<a method='get' href='#' data-toggle='modal' data-target='#myModal{{$CapaPer->ID_CapPers}}' class='btn btn-danger pull-right'><i class="fas fa-trash-alt"></i><b> {{ __('adminlte::message.delete') }}</b></a>
						<form action='/capacitacion-personal/{{$CapaPer->ID_CapPers}}' method='POST'>
							@method('DELETE')
							@csrf
							<input  type="submit" id="Eliminar{{$CapaPer->ID_CapPers}}" style="display: none;">
						</form>
					@else
						<form action='/capacitacion-personal/{{$CapaPer->ID_CapPers}}' method='POST' style="float: right;">
							@method('DELETE')
							@csrf
							<button type="submit" class='btn btn-success btn-block'>{{ __('adminlte::message.add') }}</button>
						</form>
					@endif
				</div>
				<div class="row">
					<!-- left column -->
					<div class="col-md-12">
						<!-- general form elements -->
						<div class="box box-primary">
							<form role="form" action="/capacitacion-personal/{{$CapaPer->ID_CapPers}}" method="POST" enctype="multipart/form-data">
								@method('PATCH')
								@csrf
								@if(isset($returnUrl))
									<input type="hidden" name="return_url" value="{{ $returnUrl }}">
								@endif
								<div class="col-xs-6">
										<label for="CapaPersDate">Fecha de aprobación</label>
										<input required="true" name="CapaPersDate" autofocus="true" type="date" class="form-control" id="CapaPersDate" value="{{$CapaPer->CapaPersDate}}">
									</div>
								<div class="col-xs-6">
									<label for="CapaPersExpire">Fecha de vencimiento</label>
									<input required="true" name="CapaPersExpire" autofocus="true" type="date" class="form-control" id="CapaPersExpire" value="{{$CapaPer->CapaPersExpire}}">
								</div>
								<div class="col-xs-12">
									<label for="CapaPersPdf">Certificado PDF</label>
									<input type="file" name="CapaPersPdf" id="CapaPersPdf" class="form-control" accept=".pdf">
									@if($CapaPer->CapaPersPdf)
										<small class="text-success"><i class="fas fa-file-pdf"></i> Hay un documento cargado. Suba uno nuevo para reemplazarlo.</small>
									@else
										<small class="text-muted">Máximo 10 MB. Formato PDF.</small>
									@endif
								</div>
									<div class="col-xs-6">
										<label for="FK_Pers">Persona</label>
										<select name="FK_Pers" id="FK_Pers" class="form-control">
											<option value="{{$CapaPer->FK_Pers}}">Seleccione...</option>
											@foreach($Personals as $Personal)
												<option value="{{$Personal->ID_Pers}}">{{$Personal->PersFirstName. ' ' .$Personal->PersLastName}}</option>
											@endforeach
										</select>
									</div>
									<div class="col-xs-6">
										<label for="FK_Capa">Tipo de documento</label>
										<select name="FK_Capa" id="FK_Capa" class="form-control">
											<option value="{{$CapaPer->FK_Capa}}">Seleccione...</option>
											@foreach($Trainings as $Training)
												<option value="{{$Training->ID_Capa}}">{{$Training->CapaName}}</option>
											@endforeach
										</select>
									</div>
								<div class="box-body">
									<div class="col-xs-6" style="padding-left: 0; ">
										<label for="FK_Sede">Sede</label>
										<select name="FK_Sede" id="FK_Sede" class="form-control">
											<option value="{{$CapaPer->FK_Sede}}">Seleccione...</option>
											@foreach($Sedes as $Sede)
												<option value="{{$Sede->ID_Sede}}">{{$Sede->SedeName}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="box-footer" style="float:right; margin-right:5%">
									<button type="submit" class="btn btn-success">Actualizar</button>
								</div>
							</form>
						</div>
						<!-- /.box -->
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
			<!--/.col (right) -->
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
</div>
@endsection
@extends('layouts.app')
@section('htmlheader_title','Documentos del personal')
@section('contentheader_title','Registrar documento')
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<!-- Default box -->
			<div class="box">
				<div class="row">
					<!-- left column -->
					<div class="col-md-12">
						<!-- general form elements -->
						<div class="box box-primary">
							<form role="form" action="/capacitacion-personal" method="POST" enctype="multipart/form-data">
								@csrf
								@if(isset($returnUrl))
									<input type="hidden" name="return_url" value="{{ $returnUrl }}">
								@endif
								<div class="col-xs-6">
										<label for="CapaPersDate">Fecha de aprobación</label>
										<input required="true" name="CapaPersDate" autofocus="true" type="date" class="form-control" id="CapaPersDate" >
									</div>
								<div class="col-xs-6">
									<label for="CapaPersExpire">Fecha de vencimiento</label>
									<input required="true" name="CapaPersExpire" autofocus="true" type="date" class="form-control" id="CapaPersExpire" >
								</div>
								<div class="col-xs-12">
									<label for="CapaPersPdf">Documento PDF (opcional)</label>
									<input type="file" name="CapaPersPdf" id="CapaPersPdf" class="form-control" accept=".pdf">
									<small class="text-muted">Máximo 10 MB. Formato PDF.</small>
								</div>
									<div class="col-xs-6">
										<label for="FK_Pers">Persona</label>
										<select name="FK_Pers" id="FK_Pers" class="form-control" required>
											<option value="">Seleccione...</option>
											@foreach($Personals as $Personal)
												<option value="{{$Personal->ID_Pers}}" @if(isset($Persona) && $Persona->ID_Pers == $Personal->ID_Pers) selected @endif>{{$Personal->PersFirstName. ' ' .$Personal->PersLastName}}</option>
											@endforeach
										</select>
									</div>
									<div class="col-xs-6">
										<label for="FK_Capa">Tipo de documento</label>
										<select name="FK_Capa" id="FK_Capa" class="form-control" required>
											<option value="">Seleccione tipo...</option>
											@foreach($Trainings as $Training)
												<option value="{{$Training->ID_Capa}}">{{$Training->CapaName}}</option>
											@endforeach
										</select>
									</div>
								<div class="box-body">
									<div class="col-xs-6" style="padding-left: 0; ">
										<label for="FK_Sede">Sede</label>
										<select name="FK_Sede" id="FK_Sede" class="form-control">
											<option value="">Seleccione...</option>
											@foreach($Sedes as $Sede)
												<option value="{{$Sede->ID_Sede}}" @if(isset($sedePersona) && $sedePersona == $Sede->ID_Sede) selected @endif>{{$Sede->SedeName}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="box-footer" style="float:right; margin-right:5%">
									<button type="submit" class="btn btn-success">Registrar</button>
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
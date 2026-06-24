@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.solser') }}
@endsection
@section('contentheader_title')
{{ __('adminlte::message.solser') }}
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::message.solresedit') }}</h3>
				</div>
				<div class="box box-info">
					<form role="form" action="/solicitud-residuo/{{$SolRes->SolResSlug}}" method="POST" enctype="multipart/form-data" data-toggle="validator" id="FormSolRes">
						@method('PUT')
						@csrf
						@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
										<p>{{$error}}</p>
									@endforeach
								</ul>
							</div>
						@endif
						@php
							switch ($SolRes->SolResTypeUnidad) {
								case 'Unidad':
									$TypeUnidad = 'Unidad(es)';
									break;
								case 'Litros':
									$TypeUnidad = 'Litro(s)';
									break;
								default:
									$TypeUnidad = 'Kilogramos';
									break;
							}
						@endphp
						<div class="box-body">
							<div class="form-group col-md-12">
								<label>{{ __('adminlte::message.solserrespel') }}</label>
								<small class="help-block with-errors">*</small>
								<select name="FK_SolResSolSer" id="FK_SolResSolSer" disabled class="form-control" required>
									<option value="{{$Respel->RespelSlug}}" {{ $SolRes->FK_SolResSolSer == $Respel->ID_Respel ? 'selected' : '' }}>{{$Respel->RespelName}}</option>
								</select>
							</div>
							@if($TypeUnidad != 'Kilogramos')
							<div class="form-group col-md-6">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsertypeunidad') }}</b>" data-content="{{ __('adminlte::message.solsertypeunidaddescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsertypeunidad') }}</label>
								<select name="SolResTypeUnidad" id="SolResTypeUnidad" class="form-control">
									<option value="" onclick="NoSolResCantiUnidad()">{{ __('adminlte::message.select') }}</option>
									@if($TypeUnidad == 'Unidad(es)')
									<option value="Unidad" {{$SolRes->SolResTypeUnidad  === "Unidad" ? 'selected' : '' }} onclick="SolResCantiUnidad()">{{ __('adminlte::message.solserunidad1') }}</option>
									@else
									<option value="Litros" {{$SolRes->SolResTypeUnidad  === "Litros" ? 'selected' : '' }} onclick="SolResCantiUnidad()">{{ __('adminlte::message.solserunidad2') }}</option>
									@endif
								</select>
							</div>
							<div class="form-group col-md-6">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsercantidad') }}</b>" data-content="{{ __('adminlte::message.solsercantidaddescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsercantidad') }}</label>
								<small class="help-block with-errors"></small>
								<input type="number" step=".1" min="0" class="form-control numberKg" id="SolResCantiUnidad" name="SolResCantiUnidad" value="{{$SolRes->SolResCantiUnidad}}" disabled="">
							</div>
							@endif
							<div class="form-group col-md-6">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsercantidadkg') }}</b>" data-content="{{ __('adminlte::message.solsercantidadkgdescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsercantidadkg') }}</label>
								<small class="help-block with-errors">*</small>
								<input type="number" step=".01" min="0" class="form-control numberKg" id="SolResKgEnviado" name="SolResKgEnviado" value="{{$SolRes->SolResKgEnviado}}" required>
							</div>
							<div id="embalaje" class="form-group col-md-6">
								<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserembaja') }}</b>" data-content="{{ __('adminlte::message.solserembajadescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserembaja') }}</label>
								<small class="help-block with-errors">*</small>
								<select name="SolResEmbalaje" id="SolResEmbalaje" class="form-control" required>
									<option value="">{{ __('adminlte::message.select') }}</option>
									<option value="99" {{$SolRes->SolResEmbalaje  === "Sacos/Bolsas" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja1') }}</option>
									<option value="98" {{$SolRes->SolResEmbalaje  === "Bidones Pequeños" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja2') }}</option>
									<option value="97" {{$SolRes->SolResEmbalaje  === "Bidones Grandes" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja3') }}</option>
									<option value="96" {{$SolRes->SolResEmbalaje  === "Estibas" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja4') }}</option>
									<option value="95" {{$SolRes->SolResEmbalaje  === "Garrafones/Jerricanes" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja5') }}</option>
									<option value="94" {{$SolRes->SolResEmbalaje  === "Cajas" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja6') }}</option>
									<option value="93" {{$SolRes->SolResEmbalaje  === "Cuñetes" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja7') }}</option>
									<option value="92" {{$SolRes->SolResEmbalaje  === "Big Bags" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja8') }}</option>
									<option value="91" {{$SolRes->SolResEmbalaje  === "Isotanques" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja9') }}</option>
									<option value="90" {{$SolRes->SolResEmbalaje  === "Tachos" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja10') }}</option>
									<option value="89" {{$SolRes->SolResEmbalaje  === "Embalajes Compuestos" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja11') }}</option>
									<option value="88" {{$SolRes->SolResEmbalaje  === "Granel" ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja12') }}</option>
									<option value="87" {{$SolRes->SolResEmbalaje  === "Canecas 55 gal." ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja13') }}</option>
									<option value="86" {{$SolRes->SolResEmbalaje  === "Canecas 05 gal." ? 'selected' : '' }}>{{ __('adminlte::message.solserembaja14') }}</option>
								</select>
							</div>

							{{-- @if(Auth::user()->UsRol !== __('adminlte::message.Cliente'))
								<div id="divSolResKgRecibido" class="form-group col-md-6">
								</div>
								@if($SolRes->SolResTypeUnidad === 'Litros' || $SolRes->SolResTypeUnidad === 'Unidad')
								<div id="divSolResCantiUnidadRecibida" class="form-group col-md-6">
								</div>
								@endif
								<div id="divSolResKgConciliado" class="form-group col-md-6">
								</div>
								@if (Auth::user()->UsRol !== __('adminlte::message.JefeLogistica'))
									<div id="divSolResKgTratado" class="form-group col-md-12">
									</div>
								@endif 
							@endif --}}
							<div class="form-group col-md-16" style="text-align: center;">
								<div class="form-group col-md-12">
									<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserdimension') }}</b>" data-content="{{ __('adminlte::message.solserdimensiondescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserdimension') }}</label>
								</div>
								<div class="form-group col-md-4">
									<label for="SolResAlto">{{ __('adminlte::message.solserdimension1') }}</label>
									<input type="text" class="form-control numberDimension" id="SolResAlto" maxlength="2" name="SolResAlto" value="{{$SolRes->SolResAlto}}">
								</div>
								<div class="form-group col-md-4">
									<label for="SolResAncho">{{ __('adminlte::message.solserdimension2') }}</label>
									<input type="text" class="form-control numberDimension" id="SolResAncho" maxlength="2" name="SolResAncho" value="{{$SolRes->SolResAncho}}">
								</div>
								<div class="form-group col-md-4">
									<label for="SolResProfundo">{{ __('adminlte::message.solserdimension3') }}</label>
									<input type="text" class="form-control numberDimension" id="SolResProfundo" maxlength="2" name="SolResProfundo" value="{{$SolRes->SolResProfundo}}">
								</div>
							</div>
							<div class="form-group col-md-12" style="text-align: center;">
								<div class="form-group col-md-12">
									<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requirements') }}</b>" data-content="{{ __('adminlte::message.requirementsdescript') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.requirements') }}</label>
								</div>
								<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
									<div class="form-group col-md-6">
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiredescarguephoto') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiredescarguephotodescrit') }}</p>">
											<label for="SolResFotoDescargue_Pesaje">{{ __('adminlte::message.requiredescarguephoto') }}</label>
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqFotoDescargue === 1) ? "" : "disabled"}} {{ $SolRes->SolResFotoDescargue_Pesaje == 1 ? 'checked' : '' }} type="checkbox" class="fotoswitch" id="SolResFotoDescargue_Pesaje" data-name="SolResFotoDescargue_Pesaje1" value="1"/>
												<input type="text" id="SolResFotoDescargue_Pesaje1" name="SolResFotoDescargue_Pesaje" hidden value="{{ $SolRes->SolResFotoDescargue_Pesaje == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
									<div class="form-group col-md-6">
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiretratamientophoto') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiretratamientophotodescrit') }}</p>">
											<label for="SolResFotoTratamiento">{{ __('adminlte::message.requiretratamientophoto') }}</label>
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqFotoDestruccion === 1) ? "" : "disabled"}} {{ $SolRes->SolResFotoTratamiento == 1 ? 'checked' : '' }} type="checkbox" class="fotoswitch" id="SolResFotoTratamiento" value="1" data-name="SolResFotoTratamiento1"/>
												<input type="text" id="SolResFotoTratamiento1" name="SolResFotoTratamiento" hidden value="{{ $SolRes->SolResFotoTratamiento == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
								</div> 
								<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
									<div class="form-group col-md-6">
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiredescarguevideo') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiredescarguevideodescrit') }}</p>">
											<label for="SolResVideoDescargue_Pesaje">{{ __('adminlte::message.requiredescarguevideo') }}</label>
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqVideoDescargue === 1) ? "" : "disabled"}} {{ $SolRes->SolResVideoDescargue_Pesaje == 1 ? 'checked' : '' }} type="checkbox" class="videoswitch" id="SolResVideoDescargue_Pesaje" value="1" data-name="SolResVideoDescargue_Pesaje1"/>
												<input type="text" id="SolResVideoDescargue_Pesaje1" name="SolResVideoDescargue_Pesaje" hidden value="{{ $SolRes->SolResVideoDescargue_Pesaje == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
									<div class="form-group col-md-6">
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiretratamientovideo') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiretratamientovideodescrit') }}</p>">
											<label for="SolResVideoTratamiento">{{ __('adminlte::message.requiretratamientovideo') }}</label>
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqVideoDestruccion === 1) ? "" : "disabled"}} {{ $SolRes->SolResVideoTratamiento == 1 ? 'checked' : '' }} type="checkbox" class="videoswitch" id="SolResVideoTratamiento" value="1" data-name="SolResVideoTratamiento1"/>
												<input type="text" id="SolResVideoTratamiento1" name="SolResVideoTratamiento" hidden value="{{ $SolRes->SolResVideoTratamiento == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
								</div>
								<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
									<div class="form-group col-md-6">
										<label for="SolResDevolucion" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Devolución de Elementos</b>" data-content="<p style='width: 50%'> Se requiere que los embalajes sean devueltos por <b>Prosarc S.A. ESP.</b> al Cliente/Generador</p>">
											Devolución Embalaje
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqDevolucion === 1) ? "" : "disabled"}} {{ $SolRes->SolResDevolucion == 1 ? 'checked' : '' }} type="checkbox" class="embalajeswitch" id="SolResDevolucion" value="1" data-name="SolResDevolucion1"/>
												<input type="text" id="SolResDevolucion1" name="SolResDevolucion" hidden value="{{ $SolRes->SolResDevolucion == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
									<div class="form-group col-md-6">
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Requiere Auditoria</b>" data-content="<p style='width: 50%'> Se requiere que el tratamiento del residuo sea auditado por personal del Cliente/Generador " for="SolResAuditoria">
											Requiere Auditoria
											<div style="width: 100%; height: 34px;">
												<input {{(isset($Requerimientos))&&($Requerimientos->ReqAuditoria === 1) ? "" : "disabled"}} {{ $SolRes->SolResAuditoria == 1 ? 'checked' : '' }} type="checkbox" class="auditoriaswitch" id="SolResAuditoria" value="1" data-name="SolResAuditoria1"/>
												<input type="text" id="SolResAuditoria1" name="SolResAuditoria" hidden value="{{ $SolRes->SolResAuditoria == 1 ? 1 : 0 }}">
											</div>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div id="ModalSupport"></div>
						<div class="box box-info">
							<div class="box-footer">
								<a href="#" onclick="$('#Submit').hasClass('disabled') ? $('#Submit').click() : submitverify()" id="Submit2" class="btn btn-success pull-right">{{ __('adminlte::message.update') }}</a>
								<button type="submit" id="Submit" style="display: none;"></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('NewScript')
	<script>
		$(document).ready(function (){
			numeroKg();
			numeroDimension();
			if('{{$SolRes->SolResTypeUnidad !== null}}'){
				SolResCantiUnidad();
			}
		});
	</script>
	<script>
		function Checkboxs(){
			$('input[type="checkbox"]').on('switchChange.bootstrapSwitch', function(event, state) {
				if(state == true){
					$("#"+this.dataset.name).val(1);
				}
				else{
					$("#"+this.dataset.name).val(0);
				}
			});
		}
		Checkboxs();

		function SolResCantiUnidad(){
			$('#SolResCantiUnidad').prop('required', true);
			$('#SolResCantiUnidad').prop('disabled', false);
			$('#FormSolRes').validator('update');
		}
		function NoSolResCantiUnidad(){
			$('#SolResCantiUnidad').prop('required', false);
			$('#SolResCantiUnidad').prop('disabled', true);
			$('#SolResCantiUnidad').val('');
			$('#FormSolRes').validator('validate');
		}
		function submitverify(){
			var CantidadTotalkg = {{$totalenviado}};
			CantidadTotalkg = parseInt(CantidadTotalkg)+parseInt($("#SolResKgEnviado").val());
			if(CantidadTotalkg != 0){
				if(CantidadTotalkg >= 500){
					$("#Submit2").empty();
					$("#Submit2").append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
					$("#Submit2").attr('disabled', true);
					$('#Submit').click();
				}
				else{
					@if($SolSer->SolSerSupport == null)
					$('#ModalSupport').empty();
					$('#ModalSupport').append(`
						<div class="modal modal-default fade in" id="SupportPay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<div style="font-size: 5em; color: #f39c12; text-align: center; margin: auto;">
											<i class="fas fa-exclamation-triangle"></i>
											<span style="font-size: 0.3em; color: black;"><p>Su solicitud es inferior a 500kg adjunte el soporte de pago</p></span>
											<span style="font-size: 0.3em; color: black;"><p>Su solicitud es de <b>`+CantidadTotalkg+` kg</b></p></span>
										</div>
									</div>
									<div class="modal-header">
										<div class="form-group col-md-12">
											<label style="color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsersupportpay') }}</b>" data-content="{{ __('adminlte::message.solsersupportpaydescript') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{__('adminlte::message.solsersupportpay')}}</label>
											<small class="help-block with-errors"></small>
											<input name="SupportPay" type="file" data-filesize="5120" class="form-control" data-accept="pdf" accept=".pdf">
										</div>
									</div> 
									<div class="modal-footer">
										<button type="button" class="btn btn-danger pull-left" data-dismiss="modal">No, salir</button>
										<label for="Submit" class='btn btn-success'>Enviar</label>
									</div>
								</div>
							</div>
						</div>
					`);
					popover();
					$('#CreateSolSer').validator('update');
					envsubmit();
					$('#SupportPay').modal();
					@else
					$("#Submit2").empty();
					$("#Submit2").append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
					$("#Submit2").attr('disabled', true);
					$('#Submit').click();
					@endif
				}
			}
		}
	</script>
@endsection
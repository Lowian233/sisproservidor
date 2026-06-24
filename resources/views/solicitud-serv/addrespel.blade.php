@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.solsertitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	Servicios-Solicitudes
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Residuos adicionales</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL) || in_array(Auth::user()->UsRol, Permisos::RECIBOMATERIAL))
					<a href="/solicitud-serv/{{$Solicitud->SolSerSlug}}/AnadirRespel" class="btn btn-primary pull-right"><i class="fas fa-plus-square"></i> <b>Nuevo Residuo</b></a>
					@else
					<a href="{{ route('solicitud-serv.Createrespel')}}" class="btn btn-primary pull-right"><i class="fas fa-plus-square"></i> <b>{{ __('adminlte::message.respelscreate') }}</b></a>
					@endif
				</div>
				<div class="box box-info">
					<form role="form" id="EditSolSer" action="/solicitud-servicio/{{$Solicitud->SolSerSlug}}/update-respel" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@method('PUT')
						@csrf
						<div class="box-body">
							<div class="col-md-12" style="margin-bottom: 1.5em;">
								<div>
									<center>
										<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="Observaciones <b>Opcional</b>" data-content="En este campo puede redactar sus observaciones con relaci??n a esta solicitud de servicio"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Observaciones</label>
										<button type="button" class="btn btn-box-tool boton" style="color: black;" data-toggle="collapse" data-target=".Observaciones" onclick="AnimationMenusForm('.Observaciones')" title="Reducir/Ampliar"><i class="fa fa-plus"></i></button>
									</center>
									<div class="form-group col-md-12 collapse Observaciones" style="margin-bottom: 1em;">
										<small id="caracteresrestantes" class="help-block with-errors"></small>
										<textarea onchange="updatecaracteres()" id="textDescription" rows ="5" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" name="SolSerDescript"></textarea>
									</div>
								</div>

							</div>
							<div id="AddGenerador" class="col-md-16">
								<a onclick="AgregarGenerador()" id="Agregar" class="btn btn-primary" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b> {{ __('adminlte::message.solseraddgener') }}</b>" data-content="{{ __('adminlte::message.solseraddgenerdescrit2') }}"><i class="fas fa-plus-circle"></i> {{ __('adminlte::message.solseraddgener') }}</a>
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
$(document).ready(function(){
	var area = document.getElementById("textDescription");
	var message = document.getElementById("caracteresrestantes");
	var maxLength = 4000;
	$('#textDescription').keyup(function updatecaracteres() {
		message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
	});
})

@switch($Solicitud->SolSerStatus)
	@case('Aprobado')
	$("#requirimientos").remove();
	$("#AddGenerador").remove();
	$("#CrearResiduo").remove();
	$('form[data-toggle="validator"]').validator('update');
		@break
	@default
@endswitch
function submitverify(){
	var CantidadTotalkg = {{$totalenviado}};
	for (var i = 0; i < contadorGenerador; i++) {
		for (var y = 0; y <= contadorRespel[i]; y++) {
			if($("#SolResKgEnviado"+i+y).val() != null){
				CantidadTotalkg = parseInt(CantidadTotalkg)+parseInt($("#SolResKgEnviado"+i+y).val());
			}
		}
	}
	if(CantidadTotalkg != 0){
		if(CantidadTotalkg >= 500){
			$("#Submit2").empty();
			$("#Submit2").append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
			$("#Submit2").attr('disabled', true);
			$('#Submit').click();
		}
		else{
			@if($Solicitud->SolSerSupport == null)
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
{{-- 1. primero las funciones --}}
@include('solicitud-serv.layaoutsSolSer.functionsSolSer')

{{-- 2. luego tu script que usa esas funciones --}}
<script>
$(document).ready(function () {
    const tipo = @json($Solicitud->SolSerTipo);

    switch (tipo) {
        case 'Interno':
            TransportadorProsarc();
            break;
        case 'Cliente':
            TransportadorCliente();
            break;
        case 'Generador':
            TransportadorGeneradores();
            break;
        case 'Externo':
            OtraTransportadora();
            break;
        default:
            break;
    }
});
</script>
@endsection


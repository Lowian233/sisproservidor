<script>
// Variable traducida - usa window.trans que ya está disponible globalmente
var selectTextTranslation = (window.trans && window.trans.adminlte_message && window.trans.adminlte_message.select) ? window.trans.adminlte_message.select : 'Seleccione...';
function TransportadorProsarc() {
	checkSolServRequirements();
	$("#transportador").attr('hidden', true);
	inputsize('typeaditable', '6');
	// $("#SolSerDevolucion").bootstrapSwitch('disabled',false);
	$("#SolSerTransportador").removeAttr('required');
	$("#typecollect").attr('hidden', false);
	$("#typecollect").attr('required', true);
	inputsize('typecollect', '12');
	// $("#SolSerNameTrans").val(null);
	// $("#SolSerNitTrans").val(null);
	// $("#SolSerAdressTrans").val(null);
	// $("#SolSerConductor").val(null);
	// $("#SolSerVehiculo").val(null);
	// $("#AddressCollect").val(null);
	$("#SolSerTypeCollect").val(null).trigger("change");
	$("#SolSerTypeCollect").attr('required', true);
	$("#SolSerTransportador").val(null).trigger("change");
	$("#departamento").val(null).trigger("change");
	$("#municipio").empty();
	$("#municipio2").empty();
	$("#municipio2").attr('required', false);
	$("#departamento2").val(null).trigger("change");
	$("#SedeCollect").val(null).trigger("change");
	// HiddenTypeCollect();
	hideconductorInputs();
	hideTransportExternalInputs();
	hidedateInput();
	$("#transportadorContainer").css("background-color", "#d9edf7");
}
function checkSolServRequirements() {
	var SolSerBascula = {{(isset($Requerimientos[0]))&&($Requerimientos[0]['RequeCliBascula'] === 1) ? "true" : "false"}};
	var SolSerCapacitacion = {{(isset($Requerimientos[0]))&&($Requerimientos[0]['RequeCliCapacitacion'] === 1) ? "true" : "false"}};
	var SolSerMasPerson = {{(isset($Requerimientos[0]))&&($Requerimientos[0]['RequeCliMasPerson'] === 1) ? "true" : "false"}};
	var SolSerVehicExclusive = {{(isset($Requerimientos[0]))&&($Requerimientos[0]['RequeCliVehicExclusive'] === 1) ? "true" : "false"}};
	var SolSerPlatform = {{(isset($Requerimientos[0]))&&($Requerimientos[0]['RequeCliPlatform'] === 1) ? "true" : "false"}};

	if (SolSerBascula) {
		$("#SolSerBascula").bootstrapSwitch('disabled',false);
	}else{
		$("#SolSerBascula").bootstrapSwitch('disabled',true);
	}
	if (SolSerCapacitacion) {
		$("#SolSerCapacitacion").bootstrapSwitch('disabled',false);
	}else{
		$("#SolSerCapacitacion").bootstrapSwitch('disabled',true);
	}
	if (SolSerMasPerson) {
		$("#SolSerMasPerson").bootstrapSwitch('disabled',false);
	}else{
		$("#SolSerMasPerson").bootstrapSwitch('disabled',true);
	}
	if (SolSerVehicExclusive) {
		$("#SolSerVehicExclusive").bootstrapSwitch('disabled',false);
	}else{
		$("#SolSerVehicExclusive").bootstrapSwitch('disabled',true);
	}
	if (SolSerPlatform) {
		$("#SolSerPlatform").bootstrapSwitch('disabled',false);
	}else{
		$("#SolSerPlatform").bootstrapSwitch('disabled',true);
	}
}
function disableSolServRequirements() {
	$("#SolSerBascula").bootstrapSwitch('state',false);
	$("#SolSerBascula").bootstrapSwitch('disabled',true);
	$("#SolSerCapacitacion").bootstrapSwitch('state',false);
	$("#SolSerCapacitacion").bootstrapSwitch('disabled',true);
	$("#SolSerMasPerson").bootstrapSwitch('state',false);
	$("#SolSerMasPerson").bootstrapSwitch('disabled',true);
	$("#SolSerVehicExclusive").bootstrapSwitch('state',false);
	$("#SolSerVehicExclusive").bootstrapSwitch('disabled',true);
	$("#SolSerPlatform").bootstrapSwitch('state',false);
	$("#SolSerPlatform").bootstrapSwitch('disabled',true);
}
function HiddenTypeCollect(){
	$("#sedecollect").attr('hidden', true);
	$("#SedeCollect").attr('required', false);
	$("#SedeCollect").val(null).trigger("change");
	$(".addresscollect").attr('hidden', true);
	$("#AddressCollect").attr('required', false);
	$("#AddressCollect").val(null);
	$("#municipio2").empty();
	$("#municipio2").attr('required', false);
	$("#departamento2").val(null).trigger("change");
	inputsize('typecollect', '12');

}
function TypeCollectSede(){
	$("#sedecollect").attr('hidden', false);
	$("#SedeCollect").attr('required', true);
	$(".addresscollect").attr('hidden', true);
	$("#AddressCollect").attr('required', false);
	$("#AddressCollect").val(null);
	$("#municipio2").empty();
	$("#municipio2").attr('required', false);
	$("#departamento2").val(null).trigger("change");
	inputsize('typecollect', '6');
}
function TypeCollectOther(){
	$("#sedecollect").attr('hidden', true);
	$("#SedeCollect").attr('required', false);
	$("#SedeCollect").val(null).trigger("change");
	$(".addresscollect").attr('hidden', false);
	$("#AddressCollect").attr('required', true);
	$("#AddressCollect").val(null);
	$("#municipio2").empty();
	$("#municipio2").attr('required', true);
	$("#departamento2").val(null).trigger("change");
	inputsize('typecollect', '6');
}
function inputsize(id, size){
	$("#"+id).removeClass('col-md-12');
	$("#"+id).removeClass('col-md-6');
	$("#"+id).addClass('col-md-'+size);
}
function TransportadorExtr() {
	$("#transportador").attr('hidden', false);
	$("#Conductor").attr('hidden', false);
	$("#Vehiculo").attr('hidden', false);
	inputsize('typeaditable', '12');
	$("#SolSerBascula").bootstrapSwitch('state',false);
	$("#SolSerBascula").bootstrapSwitch('disabled',true);
	$("#SolSerCapacitacion").bootstrapSwitch('state',false);
	$("#SolSerCapacitacion").bootstrapSwitch('disabled',true);
	$("#SolSerMasPerson").bootstrapSwitch('state',false);
	$("#SolSerMasPerson").bootstrapSwitch('disabled',true);
	$("#SolSerVehicExclusive").bootstrapSwitch('state',false);
	$("#SolSerVehicExclusive").bootstrapSwitch('disabled',true);
	$("#SolSerPlatform").bootstrapSwitch('state',false);
	$("#SolSerPlatform").bootstrapSwitch('disabled',true);
	// $("#SolSerDevolucion").bootstrapSwitch('disabled',false);
	$("#typecollect").attr('hidden', true);
	$("#SolSerTypeCollect").attr('required', false);
	$("#municipio2").attr('required', false);
	$("#SolSerConductor").val(null);
	$("#SolSerVehiculo").val(null);
	$("#SolSerNameTrans").val(null);
	$("#SolSerNitTrans").val(null);
	$("#SolSerAdressTrans").val(null);
	$("#AddressCollect").val(null);
	$("#SolSerTypeCollect").val(null).trigger("change");
	$("#SolSerTransportador").val(null).trigger("change");
	$("#SolSerTransportador").attr('required', true);
	$("#departamento").val(null).trigger("change");
	$("#municipio").empty();
	$("#municipio2").empty();
	$("#departamento2").val(null).trigger("change");
	$("#SedeCollect").val(null).trigger("change");
	TypeCollectOther();
}

function TransportadorCliente() {
	$("#transportadorLabel").empty();
	$("#transportadorLabel").append(`<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Sede del Cliente`);
	$("#SolSerTransportador").empty();
	$("#SolSerTransportador").append(`
	<option value="">Seleccione...</option>
	@foreach ($Sedes as $Sede)
	<option onclick="selectSede()" value="{{$Sede->SedeSlug}}">{{$Sede->SedeName}}</option>
	@endforeach
	`);
	$("#SolSerTransportador").attr('required', true);
	$("#transportador").attr('hidden', false);
	$("#nametransportadora").attr('hidden', true);
	$("#nittransportadora").attr('hidden', true);
	$("#addresstransportadora").attr('hidden', true);
	$(".citytransportadora").attr('hidden', true);
	$("#SolSerNameTrans").attr('hidden', true);
	$("#SolSerNameTrans").removeAttr('required');
	$("#SolSerNameTrans").val(null);
	$("#SolSerAdressTrans").attr('hidden', true);
	$("#SolSerAdressTrans").removeAttr('required');
	$("#SolSerAdressTrans").val(null);
	$("#SolSerNitTrans").attr('hidden', true);
	$("#SolSerNitTrans").removeAttr('required');
	$("#SolSerNitTrans").val(null);
	$("#municipio").removeAttr('required');
	$("#municipio").empty();
	$("#departamento").val(null).trigger("change");
	disableSolServRequirements();
	hideconductorInputs();
	hidedateInput();
	inputsize('typeaditable', '12');
	$("#typecollect").attr('hidden', true);
	$("#SolSerTypeCollect").attr('required', false);
	$("#sedecollect").attr('hidden', true);
	$(".addresscollect").attr('hidden', true);
	$("#transportadorContainer").css("background-color", "#dff0d8");

}

function TransportadorGeneradores() {
	$("#transportadorLabel").empty();
	$("#transportadorLabel").append(`<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Sede del Generador`);
	$("#transportador").attr('hidden', false);
	$("#nametransportadora").attr('hidden', true);
	$("#nittransportadora").attr('hidden', true);
	$("#addresstransportadora").attr('hidden', true);
	$(".citytransportadora").attr('hidden', true);
	$("#SolSerNameTrans").attr('hidden', true);
	$("#SolSerNameTrans").removeAttr('required');
	$("#SolSerNameTrans").val(null);
	$("#SolSerAdressTrans").attr('hidden', true);
	$("#SolSerAdressTrans").removeAttr('required');
	$("#SolSerAdressTrans").val(null);
	$("#SolSerNitTrans").attr('hidden', true);
	$("#SolSerNitTrans").removeAttr('required');
	$("#SolSerNitTrans").val(null);
	$("#municipio").removeAttr('required');
	$("#municipio").empty();
	$("#departamento").val(null).trigger("change");
	$("#SolSerTransportador").val(null).trigger("change");
	$("#SolSerTransportador").attr('required', true);
	$("#SolSerTransportador").empty();
	$("#SolSerTransportador").append(`
	<option value="">Seleccione...</option>
	@foreach ($SGeneradors as $SGenerador)
	<option onclick="selectGenerSede()" value="{{$SGenerador->GSedeSlug}}">{{$SGenerador->GenerName}} ({{$SGenerador->GSedeName}})</option>
	@endforeach
	`);
	inputsize('typeaditable', '12');
	$("#typecollect").attr('hidden', true);
	$("#SolSerTypeCollect").attr('required', false);
	$("#sedecollect").attr('hidden', true);
	$(".addresscollect").attr('hidden', true);
	disableSolServRequirements();
	hideconductorInputs();
	hidedateInput();
	$("#transportadorContainer").css("background-color", "#dff0d8");
}

function OtraTransportadora() {
	$("#transportador").attr('hidden', true);
	$("#SolSerTransportador").attr('required', false);
	showTransportExternalInputs();
	showconductorInputs();
	showdateInput();
	$("#SolSerTypeCollect").attr('required', false);
	inputsize('typeaditable', '12');
	$("#typecollect").attr('hidden', true);
	$("#sedecollect").attr('hidden', true);
	$(".addresscollect").attr('hidden', true);
	$("#transportadorContainer").css("background-color", "#dff0d8");
}

function selectSede() {
	hideTransportExternalInputs();
	showconductorInputs();
	showdateInput();
}

function selectGenerSede() {
	hideTransportExternalInputs();
	showconductorInputs();
}

function showTransportExternalInputs() {
	$("#nametransportadora").attr('hidden', false);
	$("#nittransportadora").attr('hidden', false);
	$("#addresstransportadora").attr('hidden', false);
	$(".citytransportadora").attr('hidden', false);
	$("#SolSerNameTrans").attr('required', true);
	$("#SolSerNitTrans").attr('required', true);
	$("#SolSerAdressTrans").attr('required', true);
	// $("#SolSerNameTrans").val(null);
	// $("#SolSerNitTrans").val(null);
	// $("#SolSerAdressTrans").val(null);
	$("#municipio").empty();
	$("#municipio").attr('required', true);
	$("#departamento").val(null).trigger("change");
}

function hideTransportExternalInputs() {
	$("#nametransportadora").attr('hidden', true);
	$("#nittransportadora").attr('hidden', true);
	$("#addresstransportadora").attr('hidden', true);
	$(".citytransportadora").attr('hidden', true);
	$("#SolSerNameTrans").attr('required', false);
	$("#SolSerNitTrans").attr('required', false);
	$("#SolSerAdressTrans").attr('required', false);
	// $("#SolSerNameTrans").val(null);
	// $("#SolSerNitTrans").val(null);
	// $("#SolSerAdressTrans").val(null);
	$("#municipio").empty();
	$("#municipio").attr('required', false);
	$("#departamento").val(null).trigger("change");
}

var contadorGenerador = 1;
var contadorRespel = [];
var icon = '';

// Función auxiliar para verificar y manipular switches de forma segura
function safeSwitchSet(element, property, value, retries) {
	retries = (retries === undefined) ? 3 : retries;
	if(!element.length || retries <= 0) return;
	if(element.data('bootstrap-switch')){
		try {
			element.bootstrapSwitch(property, value);
		} catch(e) {
			console.warn('Error al manipular switch:', e);
		}
	} else {
		// Si el switch aún no está inicializado, reintentar tras un breve delay
		setTimeout(function(){ safeSwitchSet(element, property, value, retries - 1); }, 150);
	}
}

function HiddenResiduosGener(id_div){
	icon = $('button[data-target=".Respel'+id_div+'"]').find('svg');
	$(icon).removeClass('fa-minus');
	$(icon).addClass('fa-plus');
	$("#DivRepel"+id_div).empty();
}
// $("#SolSerDevolucion").on('switchChange.bootstrapSwitch', function(event, state) {
// 	if(state == true){
// 		$("#SolSerDevolucionTipo").parent().parent().attr('hidden', false);
// 		$("#SolSerDevolucionTipo").attr('disabled', false);
// 		$("#SolSerDevolucionTipo").attr('required', true);
// 	}
// 	else{
// 		$("#SolSerDevolucionTipo").parent().parent().attr('hidden', true);
// 		$("#SolSerDevolucionTipo").attr('disabled', true);
// 		$("#SolSerDevolucionTipo").attr('required', false);
// 		$("#SolSerDevolucionTipo").val(null);
// 	}
// });
function ResiduosGener(id_div, ID_Gener){
	contadorRespel[id_div] = 0;
	$("#DivRepel"+id_div).empty();
	$("#DivRepel"+id_div).append(`@include('solicitud-serv.layaoutsSolSer.OneRespel')`);
	$('form[data-toggle="validator"]').validator('update');
	Switch2();
	Switch3();
	Checkboxs();
	numeroDimension();
	numeroKg();
	popover();
	ChangeSelect();
	Selects();
	icon = $('button[data-target=".Respel'+id_div+'"]').find('svg');
	$(icon).removeClass('fa-plus');
	$(icon).addClass('fa-minus');
	// Esperar un momento para que los switches se inicialicen completamente antes de desactivarlos
	setTimeout(function() {
		HiddenRequeRespel(id_div, contadorRespel[id_div]);
	}, 100);
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	$.ajax({
		url: "/RespelGener/"+ID_Gener,
		method: 'GET',
		data:{},
		beforeSend: function(){
			$(".loadrespelone"+id_div+contadorRespel[id_div]).append('<i class="fas fa-sync-alt fa-spin"></i>');
			$("#FK_SolResRg"+id_div+contadorRespel[id_div]).prop('disabled', true);
		},
		success: function(res){
			if(res != ''){
				var residuos = new Array();
				var $select = $("#FK_SolResRg"+id_div+contadorRespel[id_div]);
				$select.empty();
				$select.append($('<option>').val('').text(selectTextTranslation).attr('onclick', 'HiddenRequeRespel('+id_div+','+contadorRespel[id_div]+')'));
				for(var i = res.length -1; i >= 0; i--){
					var slug = res[i].SlugSGenerRes || '';
					if (slug && $.inArray(slug, residuos) < 0) {
						var clasf = (res[i].YRespelClasf4741 != null && res[i].YRespelClasf4741 !== '') ? res[i].YRespelClasf4741 : (res[i].ARespelClasf4741 || '');
						clasf = (clasf && clasf !== 'undefined') ? clasf : '';
						var name = (res[i].RespelName || '').toString();
						var trat = (res[i].TratName || '').toString();
						var label = name + ' (' + trat + ')' + (clasf ? ' ' + clasf : '');
						var $opt = $('<option>')
							.val(slug)
							.text(label)
							.attr('onclick', "RequeRespel("+id_div+","+contadorRespel[id_div]+",'"+(res[i].RespelSlug||'')+"')");
						$select.append($opt);
						residuos.push(slug);
					}
				}
			}
			else{
				$("#DivRepel"+id_div).empty();
				NotifiFalse("Lo sentimos esta sede de generador no tiene residuos asignados");
			}
		},
		complete: function(){
			$(".loadrespelone"+id_div+contadorRespel[id_div]).empty();
			$("#FK_SolResRg"+id_div+contadorRespel[id_div]).prop('disabled', false);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			NotifiFalse("No se pudo conectar a la base de datos");
		}
	});
}

function SustanciaControlada(id_div) {
    $("#Controlada" + id_div).append(
        '<div id="controlada-' + id_div + '" class="form-group col-md-16">' +
            '<i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i><b> Certificado de Carencia<b>' +
            '<small class="help-block with-errors">*</small>' +
			'<p style="color: Red;">Por favor cargue el certificado de carencia de la sustancia seleccionada actualizado</p>'+
            '<input name="SustanciaControlada[]" type="file" data-filesize="10240" class="form-control" accept=".pdf">' +
        '</div>'
    ).removeAttr('hidden');
}

function RequeRespel(id_div, contador, Id_Respel){
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	$.ajax({
		url: "/RequeRespel/"+Id_Respel,
		method: 'GET',
		data:{},
		beforeSend: function(){
			$(".loadrequired"+id_div+contadorRespel[id_div]).append('<i class="fas fa-sync-alt fa-spin"></i>');
		},
		success: function(res){
			if(res != '' && res != null){
				// console.log(res);
				if (res.SustanciaControlada == 1) {
					SustanciaControlada(id_div)
				} else {
					$("#controlada-" + id_div).remove();
				}
				var fotoDescargueEl = $('#SolResFotoDescargue_Pesaje'+id_div+contador);
				var fotoTratamientoEl = $('#SolResFotoTratamiento'+id_div+contador);
				var videoDescargueEl = $('#SolResVideoDescargue_Pesaje'+id_div+contador);
				var videoTratamientoEl = $('#SolResVideoTratamiento'+id_div+contador);
				// Foto y video: siempre habilitados para que el usuario pueda activarlos y subir archivos
				// Solo se controla el estado inicial (marcado/desmarcado) según el requerimiento
				safeSwitchSet(fotoDescargueEl, 'disabled', false);
				safeSwitchSet(fotoTratamientoEl, 'disabled', false);
				safeSwitchSet(videoDescargueEl, 'disabled', false);
				safeSwitchSet(videoTratamientoEl, 'disabled', false);
				if(res.ReqFotoDescargue == 1 && res.auto_ReqFotoDescargue == 1){
					safeSwitchSet(fotoDescargueEl, 'state', true);
				}else{
					safeSwitchSet(fotoDescargueEl, 'state', false);
				}
				if(res.ReqFotoDestruccion == 1 && res.auto_ReqFotoDestruccion == 1){
					safeSwitchSet(fotoTratamientoEl, 'state', true);
				}else{
					safeSwitchSet(fotoTratamientoEl, 'state', false);
				}
				if(res.ReqVideoDescargue == 1 && res.auto_ReqVideoDescargue == 1){
					safeSwitchSet(videoDescargueEl, 'state', true);
				}else{
					safeSwitchSet(videoDescargueEl, 'state', false);
				}
				if(res.ReqVideoDestruccion == 1 && res.auto_ReqVideoDestruccion == 1){
					safeSwitchSet(videoTratamientoEl, 'state', true);
				}else{
					safeSwitchSet(videoTratamientoEl, 'state', false);
				}
				var devolucionEl = $('#SolResDevolucion'+id_div+contador);
				var auditoriaEl = $('#SolResAuditoria'+id_div+contador);

				if(res.ReqDevolucion == 1){
					safeSwitchSet(devolucionEl, 'disabled', false);
					if(res.auto_ReqDevolucion == 1){
						safeSwitchSet(devolucionEl, 'state', true);
					}else{
						safeSwitchSet(devolucionEl, 'state', false);
					}
					safeSwitchSet(devolucionEl, 'labelText', '<i class="fas fa-trash"></i>');
					safeSwitchSet(devolucionEl, 'onText', '<i class="fas fa-check"></i>');
					safeSwitchSet(devolucionEl, 'offText', '<i class="fas fa-times"></i>');
				}
				else{
					safeSwitchSet(devolucionEl, 'state', false);
					safeSwitchSet(devolucionEl, 'disabled', true);
					safeSwitchSet(devolucionEl, 'labelText', '<i class="fas fa-trash"></i>');
					safeSwitchSet(devolucionEl, 'onText', '<i class="fas fa-check"></i>');
					safeSwitchSet(devolucionEl, 'offText', '<i class="fas fa-times"></i>');
				}
				if(res.ReqAuditoria == 1){
					safeSwitchSet(auditoriaEl, 'disabled', false);
					if(res.auto_ReqAuditoria == 1){
						safeSwitchSet(auditoriaEl, 'state', true);
					}else{
						safeSwitchSet(auditoriaEl, 'state', false);
					}
					safeSwitchSet(auditoriaEl, 'labelText', '<i class="fas fa-eye"></i>');
					safeSwitchSet(auditoriaEl, 'onText', '<i class="fas fa-check"></i>');
					safeSwitchSet(auditoriaEl, 'offText', '<i class="fas fa-times"></i>');
				}
				else{
					safeSwitchSet(auditoriaEl, 'state', false);
					safeSwitchSet(auditoriaEl, 'disabled', true);
					safeSwitchSet(auditoriaEl, 'labelText', '<i class="fas fa-eye"></i>');
					safeSwitchSet(auditoriaEl, 'onText', '<i class="fas fa-check"></i>');
					safeSwitchSet(auditoriaEl, 'offText', '<i class="fas fa-times"></i>');
				}
				switch (res.Tarifatipo) {
					case 'Kg':
						$('#SolResTypeUnidad'+id_div+contador).prop('required',false);
						$('#SolResTypeUnidad'+id_div+contador).val('');
						$('#RespelCantidadTipo'+id_div+contador).hide();
						break;
					case 'Unid':
						$('#SolResTypeUnidad'+id_div+contador).prop('required',true);
						$('#RespelCantidadTipo'+id_div+contador).hide();
						$('#SolResTypeUnidad'+id_div+contador).select2("destroy");
						$('#SolResTypeUnidad'+id_div+contador).empty();
						$('#SolResTypeUnidad'+id_div+contador).append('<option value="99">Unidad</option>');
						Selects();
						$('#RespelCantidadTipo'+id_div+contador).show();
						break;
					case 'Lt':
						$('#SolResTypeUnidad'+id_div+contador).prop('required',true);
						$('#RespelCantidadTipo'+id_div+contador).hide();
						$('#SolResTypeUnidad'+id_div+contador).select2("destroy");
						$('#SolResTypeUnidad'+id_div+contador).empty();
						$('#SolResTypeUnidad'+id_div+contador).append('<option value="98">Litros</option>');
						Selects();
						$('#RespelCantidadTipo'+id_div+contador).show();

						break;
					default:
						$('#SolResTypeUnidad'+id_div+contador).prop('required',false);
						$('#SolResTypeUnidad'+id_div+contador).val('');
						$('#RespelCantidadTipo'+id_div+contador).hide();
						$('#SolResTypeUnidad'+id_div+contador).select2("destroy");
						$('#SolResTypeUnidad'+id_div+contador).empty();
						$('#SolResTypeUnidad'+id_div+contador).append('<option>Seleccione...</option>');
						$('#SolResTypeUnidad'+id_div+contador).append('<option value="98">Litros</option>');
						$('#SolResTypeUnidad'+id_div+contador).append('<option value="99">Unidad</option>');
						Selects();
						$('#RespelCantidadTipo'+id_div+contador).show();
				}
			}
			else{
				HiddenRequeRespel(id_div, contador);
				NotifiFalse('No se encontró requerimiento para este residuo. Verifique que el residuo tenga un tratamiento con tarifa configurada (ofertado y aprobado).');
			}
		},
		complete: function(){
			$(".loadrequired"+id_div+contadorRespel[id_div]).empty();
		},
		error: function (jqXHR, textStatus, errorThrown) {
			NotifiFalse('Falla en la consulta de requerimientos. ' + (errorThrown || ''));
			HiddenRequeRespel(id_div, contador);
		},
	});
}

function HiddenRequeRespel(id_div, contador){
	var fotoDescargue = $('#SolResFotoDescargue_Pesaje'+id_div+contador);
	var fotoTratamiento = $('#SolResFotoTratamiento'+id_div+contador);
	var videoDescargue = $('#SolResVideoDescargue_Pesaje'+id_div+contador);
	var videoTratamiento = $('#SolResVideoTratamiento'+id_div+contador);
	var devolucion = $('#SolResDevolucion'+id_div+contador);
	var auditoria = $('#SolResAuditoria'+id_div+contador);
	// Foto y video: resetear estado pero mantener habilitados (sin cursor prohibido)
	safeSwitchSet(fotoDescargue, 'state', false);
	safeSwitchSet(fotoDescargue, 'disabled', false);
	safeSwitchSet(fotoTratamiento, 'state', false);
	safeSwitchSet(fotoTratamiento, 'disabled', false);
	safeSwitchSet(videoDescargue, 'state', false);
	safeSwitchSet(videoDescargue, 'disabled', false);
	safeSwitchSet(videoTratamiento, 'state', false);
	safeSwitchSet(videoTratamiento, 'disabled', false);
	safeSwitchSet(devolucion, 'state', false);
	safeSwitchSet(devolucion, 'disabled', true);
	safeSwitchSet(auditoria, 'state', false);
	safeSwitchSet(auditoria, 'disabled', true);
}
function AgregarGenerador() {
	$("#AddGenerador").before(`@include('solicitud-serv.layaoutsSolSer.NewGener')`);
	popover();
	ChangeSelect();
	Selects();
	$('form[data-toggle="validator"]').validator('update');
	contadorGenerador = contadorGenerador + 1;
}

function AgregarResPel(id_div,ID_Gener) {
	contadorRespel[id_div] = contadorRespel[id_div]+1;
	$("#AddRespel"+id_div).before(`@include('solicitud-serv.layaoutsSolSer.NewRespel')`);
	Switch2();
	Switch3();
	Checkboxs();
	numeroDimension();
	numeroKg();
	popover();
	ChangeSelect();
	Selects();
	// Esperar un momento para que los switches se inicialicen completamente antes de desactivarlos
	setTimeout(function() {
		HiddenRequeRespel(id_div, contadorRespel[id_div]);
	}, 100);
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
	});
	$.ajax({
		url: "/RespelGener/"+ID_Gener,
		method: 'GET',
		data:{},
		beforeSend: function(){
			$(".loadrespelnew"+id_div+contadorRespel[id_div]).append('<i class="fas fa-sync-alt fa-spin"></i>');
			$("#FK_SolResRg"+id_div+contadorRespel[id_div]).prop('disabled', true);
		},
		success: function(res){
			if(res != ''){
				var residuos = new Array();
				var $select = $("#FK_SolResRg"+id_div+contadorRespel[id_div]);
				$select.empty();
				$select.append($('<option>').val('').text(selectTextTranslation).attr('onclick', 'HiddenRequeRespel('+id_div+','+contadorRespel[id_div]+')'));
				for(var i = res.length -1; i >= 0; i--){
					var slug = res[i].SlugSGenerRes || '';
					if (slug && $.inArray(slug, residuos) < 0) {
						var name = (res[i].RespelName || '').toString();
						var trat = (res[i].TratName || '').toString();
						var label = name + ' (' + trat + ')';
						var $opt = $('<option>')
							.val(slug)
							.text(label)
							.attr('onclick', "RequeRespel("+id_div+","+contadorRespel[id_div]+",'"+(res[i].RespelSlug||'')+"')");
						$select.append($opt);
						residuos.push(slug);
					}
				}
			}
			else{
				$("#DivRepel"+id_div).empty();
				NotifiFalse("Lo sentimos esta sede de generador no tiene residuos asignados");
			}
		},
		complete: function(){
			$(".loadrespelnew"+id_div+contadorRespel[id_div]).empty();
			$("#FK_SolResRg"+id_div+contadorRespel[id_div]).prop('disabled', false);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			NotifiFalse("No se pudo conectar a la base de datos");
		}
	})
	$('form[data-toggle="validator"]').validator('update');
}
function RemoveRespel(id_div, contador) {
	$("#Repel"+id_div+contador).prev().remove();
	$("#Repel"+id_div+contador).remove();
	$('form[data-toggle="validator"]').validator('update');
}


function RemoveGenerador(id) {
	$("#Generador"+id).prev().remove();
	$("#Generador"+id).remove();
	$('form[data-toggle="validator"]').validator('update');
}

function showconductorInputs() {
	$("#Conductor").attr('hidden', false);
	$("#Vehiculo").attr('hidden', false);
}

function hidedateInput(){
	$("#Fecha").attr('hidden', true);
}

function showdateInput(){
	$("#Fecha").attr('hidden', false);
}

function hideconductorInputs() {
	$("#Conductor").attr('hidden', true);
	$("#Vehiculo").attr('hidden', true);
}

$("#departamento2").change(function(e){
	id=$("#departamento2").val();
	e.preventDefault();
	if (id) {
		$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
		}
		});
		$.ajax({
			url: "/muni-depart/"+id,
			method: 'GET',
			data:{},
			beforeSend: function(){
				$(".load").append('<i class="fas fa-sync-alt fa-spin"></i>');
				$("#municipio2").prop('disabled', true);
			},
			success: function(res){
				$("#municipio2").empty();
				$("#municipio2").append($('<option>').val('').text('Seleccione...'));
				var municipio2 = new Array();
				for(var i = res.length -1; i >= 0; i--){
					if ($.inArray(res[i].ID_Mun, municipio2) < 0) {
						$("#municipio2").append($('<option>').val(res[i].ID_Mun).text(res[i].MunName));
						municipio2.push(res[i].ID_Mun);
					}
				}
				// Refresca select2 y evita falso "seleccionado" cuando el campo es requerido.
				$("#municipio2").val('').trigger('change');
			},
			complete: function(){
				$(".load").empty();
				$("#municipio2").prop('disabled', false);
			}
		})
	}

});
</script>

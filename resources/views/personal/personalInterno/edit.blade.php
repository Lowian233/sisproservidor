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
			<!-- /.box -->
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.personaltitleedit') }}</h3>
				</div>
				<!-- /.box-header -->
				<!-- form start -->
				<form role="form" action="/personalInterno/{{$Persona->PersSlug}}" method="POST" enctype="multipart/form-data" data-toggle="validator">
					@method('PATCH')
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
					<div class="box box-info">
						<div class="box-body" id="readyTable">
							<div class="tab-pane" id="addRowWizz">
								<p>{{ __('adminlte::message.smartwizzardtitle') }}</p>
								<div class="smartwizard">
									<ul>
										<li><a href="#step-1"><b>{{ __('adminlte::message.Paso 1') }}</b><br /><small>{{ __('adminlte::message.personalpaso1smart-wizzard') }}</small></a></li>
										<li><a href="#step-2"><b>{{ __('adminlte::message.Paso 2') }}</b><br /><small>{{ __('adminlte::message.personalpaso2smart-wizzard') }}</small></a></li>
										<li><a href="#step-3"><b>{{ __('adminlte::message.Paso 3') }}</b><br /><small>{{ __('adminlte::message.personalpaso3smart-wizzard') }}</small></a></li>
										<input name="PersType" id="PersType" type="text" hidden value="0">
									</ul>
									<div>
										<div id="step-1" class="">
											<div class="col-md-12">
												<div id="form-step-0" role="form" data-toggle="validator">
													<div class="form-group col-md-6">
														<label for="Sede" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.sclientsede') }}</b>" data-content="{{ __('adminlte::message.persinfosede') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.sclientsede') }}</label>
														<small class="help-block with-errors">*</small>
														<select name="Sede" id="Sede" class="form-control select" required>
															<option value="">{{ __('adminlte::message.select') }}</option>
															@foreach($Sedes as $Sede1)
															<option value="{{$Sede1->SedeSlug}}" {{$Sede->ID_Sede == $Sede1->ID_Sede ? 'selected' : ''}}>{{$Sede1->SedeName}}</option>
															@endforeach
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="CargArea" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.areaname') }}</b>" data-content="{{ __('adminlte::message.persinfoarea') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.areaname') }}</label><a class="loadCargArea"></a>
														<small class="help-block with-errors">*</small>
														<select name="CargArea" id="CargArea" class="form-control" required>
															<option onclick="HiddenNewInputA()" value="">{{ __('adminlte::message.select') }}</option>
															@foreach($Areas as $Area)
															<option value="{{$Area->AreaSlug}}" {{$Sede->ID_Area == $Area->ID_Area ? 'selected' : ''}} onclick="HiddenNewInputA()">{{$Area->AreaName}}</option>
															@endforeach
															<option onclick="NewInputA()" value="NewArea">{{ __('adminlte::message.newarea') }}</option>
														</select>
													</div>
													<div class="form-group col-md-6" id="divFK_PersCargo">
														<label for="FK_PersCargo" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.cargoname') }}</b>" data-content="{{ __('adminlte::message.persinfocargo') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.cargoname') }}</label><a class="loadFK_PersCargo"></a>
														<small class="help-block with-errors">*</small>
														<select name="FK_PersCargo" id="FK_PersCargo" class="form-control" required>
															<option onclick="HiddenNewInputC()" value="">{{ __('adminlte::message.select') }}</option>
															@foreach($Cargos as $Cargo)
															<option value="{{$Cargo->CargSlug}}" {{$Sede->ID_Carg == $Cargo->ID_Carg ? 'selected' : ''}} onclick="HiddenNewInputC()">{{$Cargo->CargName}}</option>
															@endforeach
															<option onclick="NewInputC()" value="NewCargo">{{ __('adminlte::message.newcargo') }}</option>
														</select>
													</div>
													<div class="form-group col-md-6" id="NewArea" style="display: none;">
														<label for="NewInputA" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.namenewarea') }}</b>" data-content="{{ __('adminlte::message.persinfonewarea') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.namenewarea') }}</label>
														<small class="help-block with-errors">*</small>
														<input data-minlength="4" name="NewArea" type="text" id="NewInputA" class="form-control inputText" placeholder="{{ __('adminlte::message.newarea') }}">
													</div>
													<div class="form-group col-md-6" id="NewCargo" style="display: none;">
														<label for="NewInputC" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.namenewcargo') }}</b>" data-content="{{ __('adminlte::message.persinfonewcarg') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.namenewcargo') }}</label>
														<small class="help-block with-errors">*</small>
														<input data-minlength="4" name="NewCargo" type="text" id="NewInputC" class="form-control inputText" placeholder="{{ __('adminlte::message.newcargo') }}">
													</div>
												</div>
											</div>
										</div>
										<div id="step-2" class="">
											<div class="col-md-12">
												<div id="form-step-1" role="form" data-toggle="validator">
													<div class="form-group col-md-6">
														<label for="PersDocType" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persdoctype') }}</b>" data-content="{{ __('adminlte::message.persinfotypedoc') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persdoctype') }}</label>
														<small class="help-block with-errors">*</small>
														<select name="PersDocType" id="PersDocType" class="form-control" required>
															<option {{$Persona->PersDocType == 'CC' ? 'select' : ''}} value="CC">{{ __('adminlte::message.persdoctypecc') }}</option>
															<option {{$Persona->PersDocType == 'CE' ? 'select' : ''}} value="CE">{{ __('adminlte::message.persdoctypece') }}</option>
															<option {{$Persona->PersDocType == 'NIT' ? 'select' : ''}} value="NIT">{{ __('adminlte::message.persdoctypenit') }}</option>
															<option {{$Persona->PersDocType == 'RUT' ? 'select' : ''}} value="RUT">{{ __('adminlte::message.persdoctyperut') }}</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="PersDocNumber" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persdocument') }}</b>" data-content="{{ __('adminlte::message.persinfodoc') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persdocument') }}</label>
														<small class="help-block with-errors errorsdoc">*</small>
														<input data-minlength="6" maxlength="11" required name="PersDocNumber" type="text" class="form-control document" id="PersDocNumber" value="{{$Persona->PersDocNumber}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersFirstName" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persfirstname') }}</b>" data-content="{{ __('adminlte::message.persinfofirstname') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persfirstname') }}</label>
														<small class="help-block with-errors">*</small>
														<input required name="PersFirstName" type="text" class="form-control nombres" id="PersFirstName" value="{{$Persona->PersFirstName}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersSecondName" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perssecondtname') }}</b>" data-content="{{ __('adminlte::message.persinfosecondname') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perssecondtname') }}</label>
														<input name="PersSecondName" type="text" class="form-control nombres" id="PersSecondName" value="{{$Persona->PersSecondName}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersLastName" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perslastname') }}</b>" data-content="{{ __('adminlte::message.persinfolastname') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perslastname') }}</label>
														<small class="help-block with-errors">*</small>
														<input required name="PersLastName" type="text" class="form-control nombres" id="PersLastName" value="{{$Persona->PersLastName}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersEmail" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.emailaddress') }}</b>" data-content="{{ __('adminlte::message.persinfoemailprosarc') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.emailaddress') }}</label>
														<small class="help-block with-errors">*</small>
														<input type="email" name="PersEmail" id="PersEmail" class="form-control" required placeholder="{{ __('adminlte::message.emailplaceholder') }}" value="{{$Persona->PersEmail}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersCellphone" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.mobile') }}</b>" data-content="{{ __('adminlte::message.persinfotel') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.mobile') }}</label>
														<small class="help-block with-errors">*</small>
														<div class="input-group">
															<span class="input-group-addon">(+57)</span>
															<input data-minlength="12" required name="PersCellphone" type="tel" class="form-control mobile" id="PersCellphone" placeholder="{{ __('adminlte::message.mobileplaceholder') }}" value="{{$Persona->PersCellphone}}">
														</div>
													</div>
													<div class="form-group col-md-6">
														<label for="PersAddress" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.address') }}</b>" data-content="{{ __('adminlte::message.persinfodir') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.address') }}</label>
														<input name="PersAddress" type="text" class="form-control" id="PersAddress" placeholder="{{ __('adminlte::message.addressplaceholder') }}" value="{{$Persona->PersAddress}}">
													</div>
												</div>
											</div>
										</div>
										<div id="step-3" class="">
											<div class="col-md-12">
												<div id="form-step-2" role="form" data-toggle="validator">
													<div class="form-group col-md-6">
														<label for="PersBirthday" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persbirthday') }}</b>" data-content="{{ __('adminlte::message.persinfobirthday') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persbirthday') }}</label>
														<input name="PersBirthday" type="date" class="form-control fechas" id="PersBirthday" value="{{$Persona->PersBirthday <> null ? date('Y-m-d', strtotime($Persona->PersBirthday)) : ''}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersPhoneNumber" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persphone') }}</b>" data-content="{{ __('adminlte::message.persinfotelloc') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persphone') }}</label>
														<input name="PersPhoneNumber" type="text" class="form-control phone" id="PersPhoneNumber" value="{{$Persona->PersPhoneNumber}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersEPS" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perseps') }}</b>" data-content="{{ __('adminlte::message.persinfoeps') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perseps') }}</label>
														<small class="help-block with-errors dir">*</small>
														<input data-minlength="5" name="PersEPS" type="text" class="form-control" id="PersEPS" required value="{{$Persona->PersEPS}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersARL" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persarl') }}</b>" data-content="{{ __('adminlte::message.persinfoarl') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persarl') }}</label>
														<small class="help-block with-errors dir">*</small>
														<input data-minlength="4" name="PersARL" type="text" class="form-control" id="PersARL" required value="{{$Persona->PersARL}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersLibreta" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perslibreta') }}</b>" data-content="{{ __('adminlte::message.persinfolibreta') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perslibreta') }}</label>
														<input name="PersLibreta" type="text" class="form-control" id="PersLibreta" value="{{$Persona->PersLibreta}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersPase" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perspase') }}</b>" data-content="{{ __('adminlte::message.persinfopase') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perspase') }}</label>
														<input name="PersPase" type="text" class="form-control" id="PersPase" value="{{$Persona->PersPase}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersBank" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persbank') }}</b>" data-content="{{ __('adminlte::message.persinfobank') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persbank') }}</label>
														<input name="PersBank" type="text" class="form-control" id="PersBank" value="{{$Persona->PersBank}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersBankAccaunt" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persbankaccaunt') }}</b>" data-content="{{ __('adminlte::message.persinfobankaccaunt') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persbankaccaunt') }}</label>
														<small class="help-block with-errors"></small>
														<input data-minlength="19" name="PersBankAccaunt" type="text" class="form-control bank" id="PersBankAccaunt" value="{{$Persona->PersBankAccaunt}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersIngreso" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.persingreso') }}</b>" data-content="{{ __('adminlte::message.persinfoingrso') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.persingreso') }}</label>
														<small class="help-block with-errors dir">*</small>
														<input name="PersIngreso" type="date" class="form-control" id="PersIngreso" required value="{{$Persona->PersIngreso <> null ? date('Y-m-d', strtotime($Persona->PersIngreso)) : ''}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersSalida" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.perssalida') }}</b>" data-content="{{ __('adminlte::message.persinfosalida') }}"><i style="font-size: 1.7rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.perssalida') }}</label>
														<input name="PersSalida" type="date" class="form-control" id="PersSalida" value="{{$Persona->PersSalida <> null ? date('Y-m-d', strtotime($Persona->PersSalida)) : ''}}">
													</div>
													<div class="form-group col-md-6">
														<label for="PersParafiscales">Parafiscales (PDF)</label>
														<small class="help-block with-errors">*</small>
														<div class="input-group">
															<input id="PersParafiscales" name="PersParafiscales" type="file" data-filesize="1024" class="form-control" data-accept="pdf" accept=".pdf">
															<div class="input-group-btn">
																@if($Persona->PersParafiscales !== null)
																<a method='get' href='{{Storage::url($Persona->PersParafiscales)}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
																@else
																<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
																@endif
															</div>
														</div>
													</div>
													<div class="form-group col-md-6">
														<label for="PersDocOpcional">Documento Opcional (PDF)</label>
														<small class="help-block with-errors"></small>
														<div class="input-group">
															<input id="PersDocOpcional" name="PersDocOpcional" type="file" data-filesize="2048" class="form-control" data-accept="pdf" accept=".pdf">
															<div class="input-group-btn">
																@if($Persona->PersDocOpcional !== null)
																<a method='get' href='{{Storage::url($Persona->PersDocOpcional)}}' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
																@else
																<a method='get' target='_blank' class='btn btn-default'><i class='fas fa-ban fa-lg'></i></a>
																@endif
															</div>
														</div>
													</div>
													<div class="form-group col-md-6">
														<label for="PersParafiscalesExpire">Parafiscales (vencimiento)</label>
														<small class="help-block with-errors"></small>
														<input name="PersParafiscalesExpire" type="date" min="{{date('Y-m-d', strtotime(today()))}}" class="form-control" id="PersParafiscalesExpire" value="{{$Persona->PersParafiscalesExpire <> null ? date('Y-m-d', strtotime($Persona->PersParafiscalesExpire)) : ''}}">
													</div>
												</div>
												<div class="box-footer">
													<button type="submit" class="btn btn-success pull-right">{{ __('adminlte::message.update') }}</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input hidden type="text" name="updated_by" value="{{Auth::user()->email}}">
				</form>
			</div>
			<!-- /.box -->
		</div>
	</div>
</div>
@endsection
@section('NewScript')
<script>
	$(document).ready(function(){
			$('#Sede').on('change', function() { 
				var id = $('#Sede').val();
				if(id != 0){
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
						}
					});
					$.ajax({
						url: "/area-sede/"+id,
						method: 'GET',
						data:{},
						beforeSend: function(){
							$(".loadCargArea").append('<i class="fas fa-sync-alt fa-spin"></i>');
							$("#CargArea").prop('disabled', true);
						},
						success: function(res){
							if(res != ''){
								$("#CargArea").empty();
								var areas = new Array();
								$("#CargArea").append(`<option onclick="HiddenNewInputA()" value="">Seleccione...</option>`);
								for(var i = res.length -1; i >= 0; i--){
									if ($.inArray(res[i].ID_Area, areas) < 0) {
										$("#CargArea").append(`<option onclick="HiddenNewInputA()" value="${res[i].ID_Area}">${res[i].AreaName}</option>`);
										areas.push(res[i].ID_Area);
									}
								}
								$("#CargArea").append(`<option onclick="NewInputA()" value="NewArea">Nuevo Area</option>`);
							}
							else{
								$("#CargArea").empty();
								$("#CargArea").append(`<option onclick="NewInputA()" value="NewArea">Nueva Area</option>`);
								document.getElementById("NewArea").style.display = 'block';
								document.getElementById("NewInputA").required = true;
								$("#FK_PersCargo").empty();
								document.getElementById("divFK_PersCargo").style.display = 'none';
								document.getElementById("FK_PersCargo").required = false;
								document.getElementById("FK_PersCargo").value = "NewCargo";
								document.getElementById("NewCargo").style.display = 'block';
								document.getElementById("NewInputC").required = true;
							}
						},
						complete: function(){
							$(".loadCargArea").empty();
							$("#CargArea").prop('disabled', false);
						}
					})
				}
			});

			$('#CargArea').on('change', function() { 
				var id = $('#CargArea').val();
				if(id != 0){
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
						}
					});
					$.ajax({
						url: "/cargo-area/"+id,
						method: 'GET',
						data:{},
						beforeSend: function(){
							$(".loadFK_PersCargo").append('<i class="fas fa-sync-alt fa-spin"></i>');
							$("#FK_PersCargo").prop('disabled', true);
						},
						success: function(res){
							if(res != ''){
								$("#FK_PersCargo").empty();
								var cargos = new Array();
								$("#FK_PersCargo").append(`<option onclick="HiddenNewInputA()" value="">Seleccione...</option>`);
								for(var i = res.length -1; i >= 0; i--){
									if ($.inArray(res[i].CargSlug, cargos) < 0) {
										$("#FK_PersCargo").append(`<option onclick="HiddenNewInputC()" value="${res[i].CargSlug}">${res[i].CargName}</option>`);
										cargos.push(res[i].CargSlug);
									}
								}
								$("#FK_PersCargo").append(`<option onclick="NewInputC()" value="NewCargo">Nuevo Cargo</option>`);
							}
							else{
								$("#FK_PersCargo").empty();
								$("#FK_PersCargo").append(`<option onclick="NewInputC()" value="NewCargo">Nuevo Cargo</option>`);
								document.getElementById("NewCargo").style.display = 'block';
								document.getElementById("NewInputC").required = true;
							}
						},
						complete: function(){
							$(".loadFK_PersCargo").empty();
							$("#FK_PersCargo").prop('disabled', false);
						}
					})
				}
			});

		});
		function NewInputA(){
			document.getElementById("NewArea").style.display = 'block';
			document.getElementById("NewInputA").required = true;
			document.getElementById("divFK_PersCargo").style.display = 'none';
			document.getElementById("FK_PersCargo").required = false;
			document.getElementById("FK_PersCargo").value = "NewCargo";
			document.getElementById("NewCargo").style.display = 'block';
			document.getElementById("NewInputC").required = true;
		}
		function HiddenNewInputA(){
			document.getElementById("NewArea").style.display = 'none';
			document.getElementById("NewInputA").required = false;
			document.getElementById("divFK_PersCargo").style.display = 'block';
			document.getElementById("FK_PersCargo").required = true;
			document.getElementById("NewCargo").style.display = 'none';
			document.getElementById("NewInputC").required = false;
		}
		function NewInputC(){
			document.getElementById("NewCargo").style.display = 'block';
			document.getElementById("NewInputC").required = true;
		}
		function HiddenNewInputC(){
			document.getElementById("NewCargo").style.display = 'none';
			document.getElementById("NewInputC").required = false;
		}

		$(document).ready(function(){
			var type = $("#PersType").val();
			if(type == 0){
				$("#PersAddress").prop('required', true);
				$("#PersAddress").before('<small class="help-block with-errors dir">*</small>');
			}
		});
</script>
@endsection
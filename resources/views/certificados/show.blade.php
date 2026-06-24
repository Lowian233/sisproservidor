@extends('layouts.app')
@section('htmlheader_title')
	Certificado
@endsection
@section('contentheader_title')
	<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
		Certificado
	  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
	</span>
@endsection
@section('main-content')
@component('layouts.partials.modal')
	@slot('slug')

	@endslot
	@slot('textModal')
		El certificado <b>N° </b>
	@endslot
@endcomponent
<div class="container-fluid spark-screen">
	<!-- form start -->
		{{-- <input hidden type="text" name="updated_by" value="{{Auth::user()->email}}"> --}}
			<!-- col md3 -->
			<div class="col-md-3">
				<!-- box -->
				<div class="box box-primary">
					<!-- box body -->
					<div class="box-body box-profile">
						{{-- <img class="profile-user-img img-responsive img-circle" src="../../dist/img/user4-128x128.jpg" alt="User profile picture"> --}}
						<h3 class="profile-username text-center">{{$certificado->sedegenerador->generadors->GenerName}}</h3>
						<p class="text-muted text-center">{{$certificado->tratamiento->TratName}}</p>
						<ul class="list-group list-group-unbordered">
							<li class="list-group-item">
								<b>Servicio #</b> <a class="pull-right">{{$certificado->FK_CertSolser}}</a>
							</li>
							@if (in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) ||in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
								<li class="list-group-item">
									<b>certificado #</b> <a class="pull-right">{{$certificado->ID_Cert}}</a>
								</li>
							@endif
							<li class="list-group-item">
								<label>Observaciones</label>
								<textarea style="resize: vertical;" maxlength="250" name="RespelStatusDescription" id="taid" class="form-control" rows ="5">{{$certificado->CertObservacion}}</textarea>
							</li>
							<li class="list-group-item">
								<b>Firma HSEQ</b> <a class="pull-right">@if($certificado->CertAuthHseq === 1)<i class='fas fa-signature'></i>@endif</a>
							</li>


							{{-- <li class="list-group-item">
								<b>Firma JO</b> <a class="pull-right">{{ $certificado->CertAuthJo === 1 ? "<i class='fas fa-signature'></i>" : "" }}</a>
							</li> --}}
							<li class="list-group-item">
								<b>Firma JL</b> <a class="pull-right">@if($certificado->CertAuthJl === 1)<i class='fas fa-signature'></i>@endif</a>
							</li>
							<li class="list-group-item">
								<b>Firma DP</b> <a class="pull-right">@if($certificado->CertAuthDp === 1)<i class='fas fa-signature'></i>@endif</a>
							</li>
							@switch($certificado->CertType)
								@case(0)
									<li class="list-group-item" style="display: block; overflow: auto" ;>
										<div class="col-md-12 form-group">
											<label>Certificado</label>
											<div class="input-group">
												<input type="text" class="form-control" value="Ver Certificado" disabled>
												<div class="input-group-btn">
													@if($certificado->CertSrc == 'CertificadoDefault.pdf')
													<a class='btn btn-default'><i class='fas fa-file-contract fa-lg'></i></a>
													@else
													@php
														$certificadoPath = storage_path('app/public/certificadoRegular/' . $certificado->CertSrc);
													@endphp
													<a method='get' href='/storage/certificadoRegular/{{$certificado->CertSrc}}?v={{ file_exists($certificadoPath) ? filemtime($certificadoPath) : time() }}' target='_blank' class='btn btn-success'><i class='fas fa-file-contract fa-lg'></i></a>
													@endif
												</div>
											</div>
											{{-- Botón para actualizar archivo (AREALOGISTICA) --}}
											@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
												<button type="button" class="btn btn-warning btn-sm" style="margin-top: 5px;" data-toggle="modal" data-target="#updateCertFileModal" onclick="setCertType(0)">
													<i class="fas fa-upload"></i> Actualizar Archivo
												</button>
											@endif
											{{-- Botón para aprobar (JefeLogistica o AdministradorPlanta) --}}
											@if(($certificado->CertAuthJl == 0 || $certificado->CertAuthDp == 0) && ($certificado->CertSrc != 'CertificadoDefault.pdf'))
												@if((Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') || (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta'))
													<a href="{{ route('certificados.approveFile', $certificado->CertSlug) }}" class="btn btn-success btn-sm" style="margin-top: 5px;" onclick="return confirm('¿Desea aprobar este archivo?')">
														<i class="fas fa-check"></i> Aprobar Archivo
													</a>
												@endif
											@endif
										</div>
									</li>
									@break
								@case(1)
									@php
										$archivoManif = ($certificado->CertSrc != 'CertificadoDefault.pdf') ? $certificado->CertSrc : $certificado->CertSrcManif;
										$tieneManifiesto = ($archivoManif != 'CertificadoDefault.pdf');
									@endphp
									<li class="list-group-item" style="display: block; overflow: auto" ;>
										<div class="col-md-12 form-group">
											<label>Manifiesto</label>
											<div class="input-group">
												<input type="text" class="form-control" value="Ver Manifiesto" disabled>
												<div class="input-group-btn">
													@if(!$tieneManifiesto)
													<a class='btn btn-default'><i class='fas fa-file-pdf fa-lg'></i></a>
													@else
													@php
														$manifiestoPath = storage_path('app/public/manifiestosRegular/' . $archivoManif);
													@endphp
													<a method='get' href='/storage/manifiestosRegular/{{$archivoManif}}?v={{ file_exists($manifiestoPath) ? filemtime($manifiestoPath) : time() }}' target='_blank' class='btn btn-primary'><i class='far fa-file-alt fa-lg'></i></a>
													@endif
												</div>
											</div>
											{{-- Botón para actualizar archivo (AREALOGISTICA) --}}
											@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
												<button type="button" class="btn btn-warning btn-sm" style="margin-top: 5px;" data-toggle="modal" data-target="#updateCertFileModal" onclick="setCertType(1)">
													<i class="fas fa-upload"></i> Actualizar Archivo
												</button>
											@endif
											{{-- Botón para aprobar (JefeLogistica o AdministradorPlanta) --}}
											@if(($certificado->CertAuthJl == 0 || $certificado->CertAuthDp == 0) && $tieneManifiesto)
												@if((Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') || (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta'))
													<a href="{{ route('certificados.approveFile', $certificado->CertSlug) }}" class="btn btn-success btn-sm" style="margin-top: 5px;" onclick="return confirm('¿Desea aprobar este archivo?')">
														<i class="fas fa-check"></i> Aprobar Archivo
													</a>
												@endif
											@endif
										</div>
									</li>
									@break
								@case(2)
									<li class="list-group-item" style="display: block; overflow: auto" ;>
										<div class="col-md-12 form-group">
											<label>documento</label>
											<div class="input-group">
												<input type="text" class="form-control" value="Ver Certificado" disabled>
												<div class="input-group-btn">
													@if($certificado->CertSrcExt!=='CertificadoDefault.pdf')
													<a class='btn btn-default'><i class='fas fa-file-pdf fa-lg'></i></a>
													@else
													<a method='get' href="{{ asset('storage/certificadoExt/'.$certificado->CertSrcExtPdf) }}" target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
													@endif
												</div>
											</div>
											{{-- Botón para actualizar archivo (AREALOGISTICA) --}}
											@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
												<button type="button" class="btn btn-warning btn-sm" style="margin-top: 5px;" data-toggle="modal" data-target="#updateCertFileModal" onclick="setCertType(2)">
													<i class="fas fa-upload"></i> Actualizar Archivo
												</button>
											@endif
											{{-- Botón para aprobar (JefeLogistica o AdministradorPlanta) --}}
											@if(($certificado->CertAuthJl == 0 || $certificado->CertAuthDp == 0) && ($certificado->CertSrcExt != 'CertificadoDefault.pdf'))
												@if((Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') || (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta'))
													<a href="{{ route('certificados.approveFile', $certificado->CertSlug) }}" class="btn btn-success btn-sm" style="margin-top: 5px;" onclick="return confirm('¿Desea aprobar este archivo?')">
														<i class="fas fa-check"></i> Aprobar Archivo
													</a>
												@endif
											@endif
										</div>
									</li>
									@break
								@default
							@endswitch
							<li class="list-group-item" style="display: block; overflow: auto";>
								<div class="col-md-12 form-group">
									<label>Anexos</label>
									<div class="input-group">
										<input type="text" class="form-control" value="Ver Documento" disabled>
										<div class="input-group-btn">
											<a method='get' href='/img/HojaSeguridad/' target='_blank' class='btn btn-success'><i class='fas fa-file-pdf fa-lg'></i></a>
										</div>
									</div>
								</div>
							</li>

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
						<h3 class="box-title">Información para generar Certificado</h3>
						<div class="box-tools pull-right">
							@if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) ||in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)
							|| in_array(Auth::user()->UsRol, Permisos::INGDETURNO)
							)
								<a href="/certificados/{{$certificado->CertSlug}}/edit" class="btn btn-warning pull-right"> <i class="fas fa-edit"></i> <b>{{ __('adminlte::message.edit') }}</b></a>
							@endif
							@if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) ||in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) || in_array(Auth::user()->UsRol, Permisos::TODOPROSARC)
							|| in_array(Auth::user()->UsRol, Permisos::INGDETURNO)
							)
								<a target="_blank" href="/certificados/{{$certificado->CertSlug}}/wordtemplate" class="btn btn-primary pull-right" style="margin-right: 1em"> <i class="fas fa-file-word"></i> <b>Plantilla</b></a>
							@endif
							@if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT) ||in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT)
							)
								@if ($certificado->SolicitudServicio->SolSerStatus == 'Conciliado' || $certificado->SolicitudServicio->SolSerStatus == 'Tratado'
								|| in_array(Auth::user()->UsRol, Permisos::INGDETURNO)
								 )
									<a data-toggle='modal' data-target='#ModalIndependiente' class="btn btn-success pull-right" style="margin-right: 1em"><i class="fas fa-file-import"></i><b>Independiente</b></a>
								@endif
							@endif
						</div>
					</div>

					<!-- /.box header -->
					<!-- box body -->
					<div class="box-body">
						<!-- nav-tabs-custom -->
						<div class="nav-tabs-custom" style="box-shadow:3px 3px 5px grey; margin-bottom: 0px;">
							<ul class="nav nav-tabs">
								<li class="nav-item">
									<a class="nav-link" href="#Generadorpane" data-toggle="tab">Generador</a>
								</li>
								<li class="nav-item active">
									<a class="nav-link" href="#Residuospane" data-toggle="tab">Residuos</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Transportadorpane" data-toggle="tab">Transportador</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Clientepane" data-toggle="tab">Cliente</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Gestorpane" data-toggle="tab">Gestor-Tratamiento</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#Anexospane" data-toggle="tab">Anexos</a>
								</li>
							</ul>
							<!-- nav-content -->
							<div class="tab-content" style="display: block; overflow: auto;">
								<!-- tab-pane fade -->
								<div class="tab-pane fade" id="Generadorpane">
									@include('layouts.CertificadoPartials.certiGenerador')
								</div>
								<!-- /.tab-pane fade -->
								<!-- tab-pane fade -->
								<div class="tab-pane fade in active" id="Residuospane">
									@include('layouts.CertificadoPartials.certiResiduos')
								</div>
								<!-- tab-pane fade -->
								<!-- tab-pane fade -->
								<div class="tab-pane fade" id="Transportadorpane">
									@include('layouts.CertificadoPartials.certiTransportador')
								</div>
								<!-- tab-pane fade -->
								<!-- /.tab-pane fade -->
								<div class="tab-pane fade" id="Clientepane">
									@include('layouts.CertificadoPartials.certiCliente')
								</div>
								<!-- /.tab-pane fade -->
								<!-- tab-pane fade -->
								<div class="tab-pane fade" id="Gestorpane">
									@include('layouts.CertificadoPartials.certiGestorTratamiento')
								</div>
								<div class="tab-pane fade" id="Anexospane">
									{{-- @include('layouts.CertificadoPartials.respel-tarifas') --}}
								</div>

								<div id="modalrango"></div>
								<div id="modal2">
									{{--  Modal --}}
									@if (in_array(Auth::user()->UsRol, Permisos::EDITMANIFCERT))
										<div class="modal modal-default fade in" id="ModalIndependiente" tabindex="-1" role="dialog"
											aria-labelledby="myModalLabel">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
													<form action="/certificados/{{$certificado->ID_Cert}}/independiente" method="POST" id="certindependienteForm">
														@csrf
														<div class="modal-body">
															<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
																	aria-hidden="true">&times;</span></button>
															<div style="font-size: 1.5em; text-align: center; margin: auto;">
																<span>
																	<p>Escoja los residuos que desea <b>mover</b><br> hacia un <i>certificado independiente</i></p>
																</span>
															</div>
															<div class="form-group col-md-12 select-multiple-contenedor">
																<label style="color: black; text-align: left;">Residuos</label>
																<select multiple name="residuos[]" id="residuos">
																	@foreach($certificado->SolicitudServicio->SolicitudResiduo as $Residuo)
																		@foreach ($certificado->certdato as $certdato)
																			@if($Residuo->ID_SolRes == $certdato->FK_DatoCertSolRes)
																				<option value="{{ $certdato->ID_CertDato }}">{{ $Residuo->generespel->respels->RespelName }}</option>
																			@endif
																		@endforeach
																	@endforeach
																</select>
																<input type="hidden" name="ID_Cert" id="ID_Cert" value="{{$certificado->ID_Cert}}">
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Cancelar</button>
															<button form="certindependienteForm" type="submit" class="btn btn-success">enviar</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									@endif
									{{-- END Modal --}}
								</div>
								<!-- /.tab-pane fade -->
							</div>
							<!-- /.tab-content -->
						</div>
					</div>
					<!-- /.box body -->
					<div class="box-footer">

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
</div>

{{-- Modal para actualizar archivo de certificado --}}
@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
<div class="modal fade" id="updateCertFileModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="updateCertFileModalTitle"><i class="fas fa-upload"></i> Actualizar Archivo</h4>
			</div>
			<form action="{{ route('certificados.updateFile', $certificado->CertSlug) }}" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-body">
					<div class="alert alert-info" id="updateCertFileModalHint">
						<i class="fas fa-info-circle"></i> <strong>Nota:</strong> El archivo será actualizado pero requerirá aprobación del Jefe de Logística o Gerente de Planta. El slug y el estado no cambiarán.
					</div>
					<div class="form-group">
						<label>Tipo de Documento</label>
						<select name="CertType" id="CertType" class="form-control" required>
							<option value="0">Certificado Regular</option>
							<option value="1">Manifiesto</option>
							<option value="2">Certificado de tercero</option>
						</select>
					</div>
					<div class="form-group">
						<label>Archivo PDF <span class="text-danger">*</span></label>
						<input type="file" name="CertSrc" class="form-control" accept=".pdf" required>
						<small class="text-muted">Solo archivos PDF, máximo 10MB</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-warning">
						<i class="fas fa-upload"></i> Subir Archivo
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif

@endsection
@section('NewScript')
<script>
	function setCertType(type) {
		const selectEl = document.getElementById('CertType');
		if (selectEl) {
			// Setear valor como string para que coincida con los <option value="...">
			selectEl.value = String(type);
		}
		// Si el select está inicializado con algún plugin (select2/bootstrap-select),
		// se debe disparar el change para que actualice el texto visible.
		if (window.jQuery) {
			const $sel = window.jQuery('#CertType');
			if ($sel.length) {
				$sel.val(String(type)).trigger('change');
				// Por compatibilidad con Select2
				$sel.trigger('change.select2');
			}
		}

		// Ajustar el título del modal según el tipo del documento
		const titleEl = document.getElementById('updateCertFileModalTitle');
		const hintEl = document.getElementById('updateCertFileModalHint');
		const label =
			type === 1 ? 'Manifiesto' :
			type === 2 ? 'Certificado de tercero' :
			'Certificado';

		if (titleEl) titleEl.innerHTML = `<i class="fas fa-upload"></i> Actualizar Archivo de ${label}`;
		if (hintEl) hintEl.innerHTML = `<i class="fas fa-info-circle"></i> <strong>Nota:</strong> Va a actualizar el archivo de <b>${label}</b>. El archivo quedará pendiente de aprobación. El slug y el estado no cambiarán.`;
	}
</script>
@endsection



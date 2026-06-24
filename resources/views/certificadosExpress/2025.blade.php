@extends('layouts.app')
@section('htmlheader_title')
Lista de Certificados
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Certificados 2025
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
						{{-- <a href="/solicitud-servicio/documentos/create" class="btn btn-success"><i class="fas fa-file-contract"></i> Añadir Certificado</a> --}}
						{{-- <a disabled href="" class="btn btn-success"><i class="fas fa-file-invoice"></i> Añadir Manifiesto</a> --}}
					</div>
					<div class="box-body">
						<div id="ModalStatus"></div>
						<table class="table table-compact table-bordered table-striped">
							<thead>
								<th>Fecha recepción</th>
								@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
									<th>Cliente</th>
									@endif
								<th># RM</th>
								<th>Servicio</th>
								<th>Tratamiento</th>
								<th># Documento</th>
								<th>Observación</th>
								<th>Archivo</th>
								@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
									<th>Aprobación Dirección</th>
									<th>Aprobación Logística</th>
									<th>Aprobación Operaciones</th>
								@endif


								@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
									<th>Ver</th>
								@endif
								@if(in_array(Auth::user()->UsRol, Permisos::SIGNMANIFCERT))
									<th>Aprobar</th>
								@endif
								@if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
									<th>{{'Facturar'}}</th>
								@endif
								@if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
									<th>{{__('adminlte::message.solserstatuscertifi')}}</th>
								@endif
								<th>Actualizado el:</th>
							</thead>
							<tbody>
								@foreach($certificados as $certificado)
								<tr>
									<td>{{date('Y/m/d', strtotime($certificado->recepcion))}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<td class="text-center"><b>{{$certificado->cliente}}</b></td>
										@endif
									<td class="text-center">{{$certificado->CertNumRm}}</td>
									<td class="text-center">#{{$certificado->FK_CertSolser}}</br>
									({{$certificado->SolSerStatus}})
									</td>
									<td class="text-center">{{$certificado->tratamiento->TratName}}</td>
									<td class="text-center">
									@switch($certificado->CertType)
										@case(0)
											{{$certificado->CertNumero}}
											@break
										@case(1)
											{{$certificado->CertManifNumero}}
											@break
										@case(2)
											{{$certificado->CertNumeroExt}}
											@break
										@default
											{{$certificado->ID_Cert}}
									@endswitch
									</td>
								<td>{{$certificado->CertObservacion}}</td>
									@switch($certificado->CertType)
										@case(0)
											@if($certificado->CertSrc!=="CertificadoDefault.pdf")
												@php($_exCertPath = storage_path('app/public/certificadoExpress/'.$certificado->CertSrc))
												<td class="text-center"><a method='get' href='/storage/certificadoExpress/{{$certificado->CertSrc}}?v={{ file_exists($_exCertPath) ? filemtime($_exCertPath) : time() }}' target='_blank' class='btn btn-success'><i class='fas fa-file-contract fa-lg'></a></td>
											@else
												<td class="text-center"><a disabled method='get' href='/img/CertificadoDefault.pdf' class='btn btn-default'><i class='fas fa-file-contract fa-lg'></a></td>
											@endif
											@break
										@case(1)
											@if($certificado->CertSrcManif!=="CertificadoDefault.pdf")
												@php($_exManif = $certificado->CertSrc !== 'CertificadoDefault.pdf' ? $certificado->CertSrc : $certificado->CertSrcManif)
												@php($_exManifPath = storage_path('app/public/manifiestosExpress/'.$_exManif))
												<td class="text-center"><a method='get' href='/storage/manifiestosExpress/{{$_exManif}}?v={{ file_exists($_exManifPath) ? filemtime($_exManifPath) : time() }}' target='_blank' class='btn btn-primary'><i class='far fa-file-alt fa-lg'></a></td>
											@else
												<td class="text-center"><a disabled method='get' href='/img/CertificadoDefault.pdf' target='_blank' class='btn btn-default'><i class='far fa-file-alt fa-lg'></a></td>
											@endif
											@break
										@case(2)
											@if($certificado->CertSrcExt!=="CertificadoDefault.pdf")
												@php($_exExtImg = public_path('img/CertificadosEXT/'.$certificado->CertSrcExt))
												<td class="text-center"><a method='get' href='/img/CertificadosEXT/{{$certificado->CertSrcExt}}?v={{ file_exists($_exExtImg) ? filemtime($_exExtImg) : time() }}' target='_blank' class='btn btn-warning'><i class='far fa-file-alt fa-lg'></a></td>
											@else
												<td class="text-center"><a disabled method='get' href='/img/CertificadoDefault.pdf' target='_blank' class='btn btn-default'><i class='far fa-file-alt fa-lg'></a></td>
											@endif
											@break
										@default

									@endswitch


									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<td class="text-center" id="AD{{$certificado->CertSlug}}">
											@switch($certificado->CertAuthDp)
												@case(0)
													<p>Pendiente</p>
													@break

												@case(1)
													<i class='fas fa-signature fa-lg'></i>
													<p>Director de Planta</p>
													@break

												@case(2)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Logística</p>
													@break

												@case(3)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Operaciones</p>
													@break

												@case(4)
													<i class='fas fa-signature fa-lg'></i>
													<p>Supervisor de Turno</p>
													@break

												@case(5)
													<i class='fas fa-signature fa-lg'></i>
													<p>Ingeniero HSEQ</p>
													@break

												@case(6)
													<i class='fas fa-signature fa-lg'></i>
													<p>Asistente de Logística</p>
													@break

												@case(7)
													<i class='fas fa-signature fa-lg'></i>
													<p>Programador</p>
													@break

												@default
												<p>Error en Firma Digital</p>
											@endswitch
										</td>
										<td class="text-center" id="AL{{$certificado->CertSlug}}">
											@switch($certificado->CertAuthJl)
												@case(0)
													<p>Pendiente</p>
													@break

												@case(1)
													<i class='fas fa-signature fa-lg'></i>
													<p>Director de Planta</p>
													@break

												@case(2)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Logística</p>
													@break

												@case(3)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Operaciones</p>
													@break

												@case(4)
													<i class='fas fa-signature fa-lg'></i>
													<p>Supervisor de Turno</p>
													@break

												@case(5)
													<i class='fas fa-signature fa-lg'></i>
													<p>Ingeniero HSEQ</p>
													@break

												@case(6)
													<i class='fas fa-signature fa-lg'></i>
													<p>Asistente de Logística</p>
													@break

												@case(7)
													<i class='fas fa-signature fa-lg'></i>
													<p>Programador</p>
													@break

												@default
												<p>Error en Firma Digital</p>
											@endswitch
										</td>

										<td class="text-center" id="AO{{$certificado->CertSlug}}">
											@switch($certificado->CertAuthJo)
												@case(0)
													<p>Pendiente</p>
													@break

												@case(1)
													<i class='fas fa-signature fa-lg'></i>
													<p>Director de Planta</p>
													@break

												@case(2)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Logística</p>
													@break

												@case(3)
													<i class='fas fa-signature fa-lg'></i>
													<p>Jefe de Operaciones</p>
													@break

												@case(4)
													<i class='fas fa-signature fa-lg'></i>
													<p>Supervisor de Turno</p>
													@break

												@case(5)
													<i class='fas fa-signature fa-lg'></i>
													<p>Ingeniero HSEQ</p>
													@break

												@case(6)
													<i class='fas fa-signature fa-lg'></i>
													<p>Asistente de Logística</p>
													@break

												@case(7)
													<i class='fas fa-signature fa-lg'></i>
													<p>Programador</p>
													@break

												@default
												<p>Error en Firma Digital</p>
											@endswitch
										</td>
									@endif

									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
									<td class="text-center"><a method='get' href='/certificadosexpress/{{$certificado->CertSlug}}' class='btn fixed_widthbtn btn-info'><i class='fas fa-lg fa-search'></i></a></td>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::SIGNMANIFCERT))
									<td class="text-center"><a method='get' href='/certificadosexpress/{{$certificado->CertSlug}}/firmar' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-signature'></i></a></td>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
									<td class="text-center"><a method='get' href='/certificadosexpress/{{$certificado->CertSlug}}/firmar' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-signature'></i></a></td>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
									<td class="text-center"><a method='get' href='/certificadosexpress/{{$certificado->CertSlug}}/firmar' class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-signature'></i></a></td>
									@endif
									<td>{{date('Y/m/d', strtotime($certificado->updated_at))}}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection 
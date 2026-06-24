@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.personalhtmlheader_title') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.personaltitleshow') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		@foreach($Personas as $Persona)
			<div class="row">
				<div class="col-md-6 col-xs-12">
					<div class="box box-primary">
						<div class="box-body box-profile">
							<div class="col-md-12 col-xs-12">
								@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
									<a href="/personalInterno/{{$Persona->PersSlug}}/edit" class="btn btn-warning pull-right"><i class="fas fa-edit"></i><b> {{ __('adminlte::message.edit') }}</b></a>
									@if(Auth::user()->FK_UserPers <> $Persona->ID_Pers)
										@component('layouts.partials.modal')
											@slot('slug')
												{{$Persona->ID_Pers}}
											@endslot
											@slot('textModal')
												a <b>{{$Persona->PersFirstName."  ".$Persona->PersLastName}}</b>
											@endslot
										@endcomponent
										@if($Persona->PersDelete == 0)
											<a method='get' href='#' data-toggle='modal' data-target='#myModal{{$Persona->ID_Pers}}'  class='btn btn-danger pull-left'><i class="fas fa-trash-alt"></i><b> {{ __('adminlte::message.delete') }}</b></a>
											<form action='/personalInterno/{{$Persona->PersSlug}}' method='POST'>
												@method('DELETE')
												@csrf
												<input  type="submit" id="Eliminar{{$Persona->ID_Pers}}" style="display: none;">
											</form>
										@else
											<form action='/personalInterno/{{$Persona->PersSlug}}' method='POST' class="pull-left">
												@method('DELETE')
												@csrf
												<button type="submit" class='btn btn-success btn-block'>{{ __('adminlte::message.add') }}</button>
											</form>
										@endif
									@endif
								@endif
							</div>
							<h3 class="profile-username text-center">{{$Persona->PersFirstName."  ".$Persona->PersLastName}}</h3>
							<p class="text-muted text-center">{{$Persona->SedeName}}</p>
							<ul class="list-group list-group-unbordered">
								<li class="list-group-item">
									<b>{{ __('adminlte::message.persdocument') }}</b> <a class="pull-right textpopover">{{$Persona->PersDocType." ".$Persona->PersDocNumber}}</a>
								</li>
								<li class="list-group-item">
									<b>{{ __('adminlte::message.mobile') }}</b> <a class="pull-right textpopover">{{$Persona->PersCellphone}}</a>
								</li>
								<li class="list-group-item">
									<b>{{ __('adminlte::message.emailaddress') }}</b> <a title="Copiar" onclick="copiarAlPortapapeles('correocopy')"><i class="far fa-copy"></i></a>
									<a href="#" class="pull-right textpopover" title="{{ __('adminlte::message.emailaddress') }}" data-toggle="popover" id="correocopy" data-trigger="focus" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Persona->PersEmail}}</p>">{{$Persona->PersEmail}}</a>
								</li>
							</ul>
						</div>
						<!-- /.box-body -->
					</div>
				</div>
				<div class="col-md-6 col-xs-12">
					<div class="nav-tabs-custom">
						<ul class="nav nav-tabs">
							<li class="active"><a href="#activity" data-toggle="tab">{{ __('adminlte::message.persdataof').$Persona->PersFirstName}}</a></li>
							<li><a href="#cursos" data-toggle="tab">Documentación</a></li>
						</ul>
						<div class="tab-content">
							<div class="active tab-pane" id="activity">
								<div class="post">
									<div class="row">
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.persingreso') }}</label><h5><a>{{$Persona->PersIngreso <> null ? $Persona->PersIngreso : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.perssalida') }}</label><h5><a>{{$Persona->PersSalida <> null ? $Persona->PersSalida : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.address') }}</label> <a title="Copiar" onclick="copiarAlPortapapeles('addresscopy')"><i class="far fa-copy"></i></a>
											<h5><a href="#" class="textpopover" title='{{ __('adminlte::message.address') }} ' data-toggle="popover" id="addresscopy" data-trigger="focus" data-html="true" data-placement="bottom" data-content="<p class='textolargo'>{{$Persona->PersAddress}}</p>">{{$Persona->PersAddress <> null ? $Persona->PersAddress : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.personalcarg') }}</label><h5><a>{{$Persona->CargName}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.phone') }}</label><h5><a>{{$Persona->PersPhoneNumber <> null ? $Persona->PersPhoneNumber : 'N/A' }}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.persbirthday') }}</label><h5><a>{{$Persona->PersBirthday <> null ? $Persona->PersBirthday : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.perseps') }}</label><h5><a>{{$Persona->PersEPS <> null ? $Persona->PersEPS : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.persarl') }}</label><h5><a>{{$Persona->PersARL <> null ? $Persona->PersARL : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.persbank') }}</label><h5><a>{{$Persona->PersBank <> null ? $Persona->PersBank : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.persbankaccaunt') }}</label><h5><a>{{$Persona->PersBankAccaunt <> null ? $Persona->PersBankAccaunt : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.perslibreta') }}</label><h5><a>{{$Persona->PersLibreta <> null ? $Persona->PersLibreta : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>{{ __('adminlte::message.perspase') }}</label><h5><a>{{$Persona->PersPase <> null ? $Persona->PersPase : 'N/A'}}</a></h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>parafiscales</label>
											<h5>
											@if($Persona->PersParafiscales !== null)
												@if ($Persona->PersParafiscalesExpire >= today())
													<a method='get' href='{{Storage::url($Persona->PersParafiscales)}}' target='_blank'>{{$Persona->PersParafiscales}} <i class='far fa-file-alt fa-lg'></i> </a>
												@else
													<a style="color:red;"method='get' href='{{Storage::url($Persona->PersParafiscales)}}' target='_blank'>{{$Persona->PersParafiscales}} <i class='far fa-file fa-lg'></i> </a>
												@endif
											@else
												<a>N/A</a>
											@endif
											</h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>documentos opcionales</label>
											<h5>
												@if($Persona->PersDocOpcional !== null)
													<a method='get' href='{{Storage::url($Persona->PersDocOpcional)}}' target='_blank'>{{$Persona->PersDocOpcional}} <i class='far fa-file-alt fa-lg'></i></a>
												@else
													<a>N/A</a>
												@endif
											</h5>
										</div>
										<div class="col-md-6 col-xs-12">
											<label>vencimiento de parafiscales</label>
											<h5><a>{{$Persona->PersParafiscalesExpire !== null ? date('Y-m-d', strtotime($Persona->PersParafiscalesExpire)) : 'N/A'}}</a></h5>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="cursos">
								<div class="box">
									<div class="box-header with-border">
										<h4 class="box-title">Documentación (certificados, licencias)</h4>
										@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
											<a href="{{ route('personalInterno.capacitacion.create', $Persona->PersSlug) }}" class="btn btn-primary btn-sm pull-right"><i class="fas fa-plus"></i> Agregar documento</a>
										@endif
									</div>
									<div class="box-body">
										@php $cursosPersona = $cursosPorPersona[$Persona->ID_Pers] ?? collect(); @endphp
										@if($cursosPersona->isEmpty())
											<p class="text-muted">No hay documentos registrados (certificados, licencias de conducción, manipulación de residuos, etc.). Haga clic en "Agregar documento" para subir uno.</p>
										@else
											<table class="table table-bordered table-striped table-sm">
												<thead>
													<tr>
														<th>Tipo de documento</th>
														<th>Aprobación</th>
														<th>Vencimiento</th>
														<th>Sede</th>
														<th>Archivo PDF</th>
														@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
															<th>Acciones</th>
														@endif
													</tr>
												</thead>
												<tbody>
													@foreach($cursosPersona as $curso)
													<tr @if($curso->CapaPersExpire && \Carbon\Carbon::parse($curso->CapaPersExpire)->isPast()) class="danger" @endif>
														<td>{{ $curso->training->CapaName ?? 'N/A' }}</td>
														<td>{{ $curso->CapaPersDate ? date('Y-m-d', strtotime($curso->CapaPersDate)) : 'N/A' }}</td>
														<td>{{ $curso->CapaPersExpire ? date('Y-m-d', strtotime($curso->CapaPersExpire)) : 'N/A' }}</td>
														<td>{{ $curso->sede->SedeName ?? 'N/A' }}</td>
														<td>
															@if($curso->CapaPersPdf)
																<a href="{{ route('capacitacion-personal.downloadPdf', $curso->ID_CapPers) }}" target="_blank" class="btn btn-default btn-xs" title="Descargar PDF"><i class="fas fa-file-pdf text-danger"></i> PDF</a>
															@else
																<span class="text-muted">—</span>
															@endif
														</td>
														@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
															<td>
																<a href="{{ url('/capacitacion-personal/'.$curso->ID_CapPers.'/edit') }}?return_url={{ urlencode(route('personalInterno.show', ['personalInterno' => $Persona->PersSlug])) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
																<form action="{{ url('/capacitacion-personal/'.$curso->ID_CapPers) }}" method="POST" style="display:inline;">
																	@method('DELETE')
																	@csrf
																	<input type="hidden" name="return_url" value="{{ route('personalInterno.show', ['personalInterno' => $Persona->PersSlug]) }}">
																	<button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('¿Eliminar esta capacitación?');"><i class="fas fa-trash-alt"></i></button>
																</form>
															</td>
														@endif
													</tr>
													@endforeach
												</tbody>
											</table>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@endsection
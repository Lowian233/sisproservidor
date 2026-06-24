@extends('layouts.app')
@section('htmlheader_title')
Lista de Recibos de Material{{ isset($year) ? ' ' . $year : '' }}
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Recibos de Material{{ isset($year) ? ' ' . $year : '' }}
  	<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-12 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
					@if(isset($year))
						<a href="{{ route('recibomaterial.index') }}" class="btn btn-info"><i class="fas fa-arrow-left"></i> Volver a a&ntilde;os</a>
					@endif
						{{-- Botones comentados para añadir nuevos documentos --}}
						{{-- <a href="/solicitud-servicio/documentos/create" class="btn btn-success"><i class="fas fa-file-contract"></i> Añadir Certificado</a> --}}
						{{-- <a disabled href="" class="btn btn-success"><i class="fas fa-file-invoice"></i> Añadir Manifiesto</a> --}}
					</div>
					<div class="box-body">
						<div id="ModalStatus"></div>
						<table class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									<th>Fecha recepci&oacute;n</th>
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<th>Cliente</th>
									@endif
									<th>Servicio</th>
									<th>Archivo</th>
									<th>Ver Solicitud</th>
									<th>Actualizado el:</th>
								</tr>
							</thead>
							<tbody>
                                @foreach($rms as $rm)

                                    <tr>
                                        <td>{{$rm->ProgVehFecha}}</td>
                                        @if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
                                            <td>{{$rm->CliName}}</td>
                                        @endif
                                        <td>{{$rm->FK_SolSer}}</td>
                                        <td class="text-center">
											@php
												$slugFirmas = $rm->SlugFirmas ?? null;
											@endphp
											@if(!empty($slugFirmas))
												@php
													$reciboPath = storage_path('app/public/RecibosdeMaterial/' . $slugFirmas . '.pdf');
													$reciboVersion = file_exists($reciboPath) ? filemtime($reciboPath) : time();
												@endphp
												<a method='get' href="{{ route('recibomaterial.file', ['slugFirmas' => $slugFirmas]) }}?v={{ $reciboVersion }}" target='_blank' class='btn btn-success'>
													<i class='fas fa-file-contract fa-lg'></i>
												</a>
											@else
												<button type="button" class="btn btn-default" disabled title="Sin slug de firmas">
													<i class="fas fa-file-contract fa-lg"></i>
												</button>
											@endif
											{{-- Botón para actualizar archivo (AREALOGISTICA) --}}
											@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
												<button type="button" class="btn btn-warning btn-sm" style="margin-left: 5px;" data-toggle="modal" data-target="#updateReciboFileModal" onclick="setSlugFirmas('{{ $slugFirmas }}')" title="Actualizar archivo" {{ empty($slugFirmas) ? 'disabled' : '' }}>
													<i class="fas fa-upload"></i>
												</button>
											@endif
											{{-- Botón para aprobar (JefeLogistica o AdministradorPlanta) --}}
											@if((Auth::user()->UsRol == 'JefeLogistica' || Auth::user()->UsRol2 == 'JefeLogistica') || (Auth::user()->UsRol == 'AdministradorPlanta' || Auth::user()->UsRol2 == 'AdministradorPlanta'))
												@if(!empty($slugFirmas))
													<a href="{{ route('recibomaterial.approveFile', ['slugFirmas' => $slugFirmas]) }}" class="btn btn-success btn-sm" style="margin-left: 5px;" onclick="return confirm('¿Desea aprobar este archivo?')" title="Aprobar archivo">
														<i class="fas fa-check"></i>
													</a>
												@else
													<button type="button" class="btn btn-default btn-sm" style="margin-left: 5px;" disabled title="Sin slug de firmas">
														<i class="fas fa-check"></i>
													</button>
												@endif
											@endif
										</td>
										<td style="text-align: center;"><a href='/solicitud-servicio/{{$rm->SolSerSlug}}' class="btn btn-info" title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a>		</td>
                                        <td>{{$rm->updated_at}}</td>
                                    </tr>

                                @endforeach
							</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Modal para actualizar archivo de recibo de material --}}
@if(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA) || in_array(Auth::user()->UsRol2, Permisos::AREALOGISTICA))
<div class="modal fade" id="updateReciboFileModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><i class="fas fa-upload"></i> Actualizar Archivo de Recibo de Material</h4>
			</div>
			<form id="updateReciboFileForm" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-body">
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> <strong>Nota:</strong> El archivo ser&aacute; actualizado pero requerir&aacute; aprobaci&oacute;n del Jefe de Log&iacute;stica o Gerente de Planta. El slug no cambiar&aacute;.
					</div>
					<input type="hidden" name="SlugFirmas" id="SlugFirmas">
					<div class="form-group">
						<label>Archivo PDF <span class="text-danger">*</span></label>
						<input type="file" name="ReciboPdf" class="form-control" accept=".pdf" required>
						<small class="text-muted">Solo archivos PDF, m&aacute;ximo 10MB</small>
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
	function setSlugFirmas(slug) {
		document.getElementById('SlugFirmas').value = slug;
		document.getElementById('updateReciboFileForm').action = '/recibomaterial/' + slug + '/updateFile';
	}
</script>
@endsection

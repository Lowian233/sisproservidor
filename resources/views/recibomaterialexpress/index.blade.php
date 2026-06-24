@extends('layouts.app')
@section('htmlheader_title')
Lista de Recibos de Material
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Recibos de Material
  	<div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-12 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
						{{-- Botones comentados para a???adir nuevos documentos --}}
						{{-- <a href="/solicitud-servicio/documentos/create" class="btn btn-success"><i class="fas fa-file-contract"></i> A???adir Certificado</a> --}}
						{{-- <a disabled href="" class="btn btn-success"><i class="fas fa-file-invoice"></i> A???adir Manifiesto</a> --}}
					</div>
					<div class="box-body">
						<div id="ModalStatus"></div>
						<table class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									<th>Fecha recepci???n</th>
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
											@php($slugFirmas = $rm->SlugFirmas ?? null)
											@if(!empty($slugFirmas))
												@php($pathExpress = storage_path('app/public/RecibosMaterialExpress/' . $slugFirmas . '.pdf'))
												@php($pathLegacy = storage_path('app/public/RecibosdeMaterialExpress/' . $slugFirmas . '.pdf'))
												@php($urlPdf = file_exists($pathExpress) ? asset('storage/RecibosMaterialExpress/' . $slugFirmas . '.pdf') : (file_exists($pathLegacy) ? asset('storage/RecibosdeMaterialExpress/' . $slugFirmas . '.pdf') : null))
												@if($urlPdf)
													<a href="{{ $urlPdf }}" target="_blank" class="btn btn-success"><i class='fas fa-file-contract fa-lg'></i></a>
												@else
													<button type="button" class="btn btn-default" disabled title="Archivo no encontrado"><i class='fas fa-file-contract fa-lg'></i></button>
												@endif
											@else
												<button type="button" class="btn btn-default" disabled title="Sin slug de firmas"><i class='fas fa-file-contract fa-lg'></i></button>
											@endif
										</td>
										<td style="text-align: center;"><a href='/serviciosexpress/{{$rm->SolSerSlug}}' class="btn btn-info" title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a>		</td>
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
@endsection

@extends('layouts.app')
@section('htmlheader_title')
Lista de Certificados {{ $anio }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Certificados {{ $anio }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
						<a href="{{ route('certificados.index') }}" class="btn btn-default btn-sm pull-right" style="margin-left: 5px;" title="Volver al selector de años"><i class="fas fa-arrow-left"></i> Volver</a>
					</div>
					<div class="box-body">
						@php $esCliente = in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2 ?? '', Permisos::CLIENTE); @endphp
						@if(!$esCliente)
						{{-- Filtros: mes, cliente y tipo de documento (solo para personal Prosarc) --}}
						<form method="GET" action="{{ route('certificados.' . $anio) }}" class="form-inline well well-sm" style="margin-bottom: 15px;">
							<input type="hidden" name="buscar" value="1">
							@if(isset($mesesFiltro) && $mesesFiltro->isNotEmpty())
							<div class="form-group" style="margin-right: 10px;">
								<label for="mes" class="control-label" style="margin-right: 5px;">Mes:</label>
								<select name="mes" id="mes" class="form-control input-sm" style="min-width: 150px;">
									<option value="">Todos</option>
									@foreach($mesesFiltro as $m)
										<option value="{{ $m->valor }}" {{ request('mes') == $m->valor ? 'selected' : '' }}>{{ $m->label }}</option>
									@endforeach
								</select>
							</div>
							@endif
							@if(isset($clientesFiltro) && $clientesFiltro->isNotEmpty())
							<div class="form-group" style="margin-right: 10px;">
								<label for="cliente" class="control-label" style="margin-right: 5px;">Cliente:</label>
								<select name="cliente" id="cliente" class="form-control input-sm" style="min-width: 200px;">
									<option value="">Todos</option>
									@foreach($clientesFiltro as $c)
										<option value="{{ $c->ID_Cli }}" {{ request('cliente') == $c->ID_Cli ? 'selected' : '' }}>{{ $c->CliName }}</option>
									@endforeach
								</select>
							</div>
							@endif
							@if(isset($tiposFiltro) && $tiposFiltro->isNotEmpty())
							<div class="form-group" style="margin-right: 10px;">
								<label for="tipo" class="control-label" style="margin-right: 5px;">Tipo documento:</label>
								<select name="tipo" id="tipo" class="form-control input-sm" style="min-width: 180px;">
									<option value="">Todos</option>
									@foreach($tiposFiltro as $t)
										<option value="{{ $t->valor }}" {{ request('tipo') == $t->valor ? 'selected' : '' }}>{{ $t->label }}</option>
									@endforeach
								</select>
							</div>
							@endif
							<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Buscar</button>
							<a href="{{ route('certificados.' . $anio) }}?ver=todos" class="btn btn-default btn-sm" style="margin-left: 5px;" title="Ver todos los certificados de {{ $anio }} (puede tardar más)"><i class="fas fa-list"></i> Ver todos</a>
							@if(request()->hasAny(['mes','cliente','tipo','ver','buscar']))
								<a href="{{ route('certificados.' . $anio) }}" class="btn btn-default btn-sm" style="margin-left: 5px;"><i class="fas fa-times"></i> Limpiar</a>
							@endif
						</form>
						@if(!request()->hasAny(['mes','cliente','tipo','ver','buscar']) && $certificados->isEmpty())
							<div class="alert alert-info" style="margin-bottom: 15px;">
								<i class="fas fa-info-circle"></i>
								<strong>Búsqueda rápida:</strong> Seleccione <b>mes</b>, <b>cliente</b> y/o <b>tipo de documento</b> (Certificado, Manifiesto, Certificado de terceros) y haga clic en <b>Buscar</b> para cargar los certificados de {{ $anio }}. O use <b>Ver todos</b> para cargar todos los certificados del año (puede tardar más).
							</div>
						@endif
						@endif
						@if($esCliente && $certificados->isEmpty())
							<div class="alert alert-info" style="margin-bottom: 15px;">
								<i class="fas fa-info-circle"></i> No tiene certificados para el año {{ $anio }}.
							</div>
						@endif
						<div id="ModalStatus"></div>
						<table class="table table-compact table-bordered table-striped {{ $certificados->isEmpty() ? 'd-none' : '' }}">
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
									<td>{{ $certificado->recepcion ? date('Y/m/d', strtotime($certificado->recepcion)) : '' }}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<td class="text-center"><b>{{ $certificado->cliente }}</b></td>
									@endif
									<td class="text-center">{{ $certificado->CertNumRm }}</td>
									<td class="text-center">#{{ $certificado->FK_CertSolser }}</br>({{ $certificado->SolSerStatus }})</td>
									<td class="text-center">{{ optional($certificado->tratamiento)->TratName ?? 'N/A' }}</td>
									<td class="text-center">{{ $certificado->ID_Cert }}</td>
									<td>{{ $certificado->CertObservacion }}</td>
									@switch($certificado->CertType)
										@case(0)
											@if($certificado->CertSrc != "CertificadoDefault.pdf")
												@php
													$_afCertPath = storage_path('app/public/certificadoRegular/'.$certificado->CertSrc);
													$_afCertImg = public_path('img/Certificados/'.$certificado->CertSrc);
													$_afCertHref = file_exists($_afCertPath)
														? asset('storage/certificadoRegular/'.$certificado->CertSrc).'?v='.filemtime($_afCertPath)
														: asset('img/Certificados/'.$certificado->CertSrc).'?v='.(file_exists($_afCertImg) ? filemtime($_afCertImg) : time());
												@endphp
												<td class="text-center"><a href="{{ $_afCertHref }}" target='_blank' class='btn btn-success'><i class='fas fa-file-contract fa-lg'></i></a></td>
											@else
												<td class="text-center"><a disabled href='/img/CertificadoDefault.pdf' class='btn btn-default'><i class='fas fa-file-contract fa-lg'></i></a></td>
											@endif
											@break
										@case(1)
											@if($certificado->CertSrc != "CertificadoDefault.pdf")
												@php
													$_afManif = ($certificado->CertSrc !== 'CertificadoDefault.pdf') ? $certificado->CertSrc : $certificado->CertSrcManif;
													$_afManifPath = storage_path('app/public/manifiestosRegular/'.$_afManif);
													$_afManifImg = public_path('img/Manifiestos/'.$_afManif);
													$_afManifHref = file_exists($_afManifPath)
														? asset('storage/manifiestosRegular/'.$_afManif).'?v='.filemtime($_afManifPath)
														: asset('img/Manifiestos/'.$_afManif).'?v='.(file_exists($_afManifImg) ? filemtime($_afManifImg) : time());
												@endphp
												<td class="text-center"><a href="{{ $_afManifHref }}" target='_blank' class='btn btn-primary'><i class='far fa-file-alt fa-lg'></i></a></td>
											@else
												<td class="text-center"><a disabled href='/img/CertificadoDefault.pdf' target='_blank' class='btn btn-default'><i class='far fa-file-alt fa-lg'></i></a></td>
											@endif
											@break
										@case(2)
											@if($certificado->CertSrc != "CertificadoDefault.pdf")
												@php
													$_afExtPath = storage_path('app/public/certificadoExt/'.$certificado->CertSrc);
													$_afExtHref = asset('storage/certificadoExt/'.$certificado->CertSrc).'?v='.(file_exists($_afExtPath) ? filemtime($_afExtPath) : time());
												@endphp
												<td class="text-center"><a href="{{ $_afExtHref }}" target='_blank' class='btn btn-warning'><i class='far fa-file-alt fa-lg'></i></a></td>
											@else
												<td class="text-center"><a disabled href='/img/CertificadoDefault.pdf' target='_blank' class='btn btn-default'><i class='far fa-file-alt fa-lg'></i></a></td>
											@endif
											@break
										@default
											<td class="text-center">-</td>
											@break
									@endswitch
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<td class="text-center" id="AD{{ $certificado->CertSlug }}">
											@switch($certificado->CertAuthDp)
												@case(0)<p>Pendiente</p>@break
												@case(1)<i class='fas fa-signature fa-lg'></i><p>Director de Planta</p>@break
												@case(2)<i class='fas fa-signature fa-lg'></i><p>Jefe de Logística</p>@break
												@case(3)<i class='fas fa-signature fa-lg'></i><p>Jefe de Operaciones</p>@break
												@case(4)<i class='fas fa-signature fa-lg'></i><p>Supervisor de Turno</p>@break
												@case(5)<i class='fas fa-signature fa-lg'></i><p>Ingeniero HSEQ</p>@break
												@case(6)<i class='fas fa-signature fa-lg'></i><p>Asistente de Logística</p>@break
												@case(7)<i class='fas fa-signature fa-lg'></i><p>Programador</p>@break
												@default<p>Error en Firma Digital</p>
											@endswitch
										</td>
										<td class="text-center" id="AL{{ $certificado->CertSlug }}">
											@switch($certificado->CertAuthJl)
												@case(0)<p>Pendiente</p>@break
												@case(1)<i class='fas fa-signature fa-lg'></i><p>Director de Planta</p>@break
												@case(2)<i class='fas fa-signature fa-lg'></i><p>Jefe de Logística</p>@break
												@case(3)<i class='fas fa-signature fa-lg'></i><p>Jefe de Operaciones</p>@break
												@case(4)<i class='fas fa-signature fa-lg'></i><p>Supervisor de Turno</p>@break
												@case(5)<i class='fas fa-signature fa-lg'></i><p>Ingeniero HSEQ</p>@break
												@case(6)<i class='fas fa-signature fa-lg'></i><p>Asistente de Logística</p>@break
												@case(7)<i class='fas fa-signature fa-lg'></i><p>Programador</p>@break
												@default<p>Error en Firma Digital</p>
											@endswitch
										</td>
										<td class="text-center" id="AO{{ $certificado->CertSlug }}">
											@switch($certificado->CertAuthJo)
												@case(0)<p>Pendiente</p>@break
												@case(1)<i class='fas fa-signature fa-lg'></i><p>Director de Planta</p>@break
												@case(2)<i class='fas fa-signature fa-lg'></i><p>Jefe de Logística</p>@break
												@case(3)<i class='fas fa-signature fa-lg'></i><p>Jefe de Operaciones</p>@break
												@case(4)<i class='fas fa-signature fa-lg'></i><p>Supervisor de Turno</p>@break
												@case(5)<i class='fas fa-signature fa-lg'></i><p>Ingeniero HSEQ</p>@break
												@case(6)<i class='fas fa-signature fa-lg'></i><p>Asistente de Logística</p>@break
												@case(7)<i class='fas fa-signature fa-lg'></i><p>Programador</p>@break
												@default<p>Error en Firma Digital</p>
											@endswitch
										</td>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC))
										<td class="text-center"><a href='/certificados/{{ $certificado->CertSlug }}' class='btn fixed_widthbtn btn-info'><i class='fas fa-lg fa-search'></i></a></td>
									@endif
									@php
										$Status = ['Conciliado', 'Tratado', 'Facturado'];
										$Status2 = ['Conciliado', 'Tratado'];
									@endphp
									@if(in_array(Auth::user()->UsRol, Permisos::SIGNMANIFCERT))
										@if (in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status))
											<td class="text-center"><button id="buttonfirmarDoc{{ $certificado->CertSlug }}" class='btn fixed_widthbtn btn-warning' onclick="firmarDocumento('{{ $certificado->CertSlug }}')"><i class='fas fa-lg fa-file-signature'></i></button></td>
										@else
											<td class="text-center"><button id="buttonfirmarDoc{{ $certificado->CertSlug }}" class='btn fixed_widthbtn btn-default' disabled><i class='fas fa-lg fa-file-signature'></i></button></td>
										@endif
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
										<td>
											<button onclick="ModalFacturar('{{ $certificado->SolicitudServicio->SolSerSlug }}', '{{ $certificado->SolicitudServicio->ID_SolSer }}', '{{ in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status2) }}', 'Facturado', 'Facturar')" {{ in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status2) ? '' : 'disabled' }} class="classFacturarStatus{{ $certificado->SolicitudServicio->SolSerSlug }} btn btn-{{ in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status2) ? 'info' : 'default' }}"><i class="fas fa-receipt"></i> Facturar</button>
										</td>
									@endif
									@if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
										<td>
											<button onclick="ModalStatus('{{ $certificado->SolicitudServicio->SolSerSlug }}', '{{ $certificado->SolicitudServicio->ID_SolSer }}', '{{ in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status) }}', 'Certificada', 'certificar')" {{ (in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status) && $certificado->CertAuthJo == '3' && $certificado->CertAuthJl == '2' && $certificado->CertAuthDp == '1') ? '' : 'disabled' }} class="classCertStatus{{ $certificado->SolicitudServicio->SolSerSlug }} btn btn-{{ in_array($certificado->SolicitudServicio->SolSerStatus ?? '', $Status) ? 'success' : 'default' }}"><i class="fas fa-certificate"></i> {{ __('adminlte::message.solserstatuscertifi') }}</button>
										</td>
									@endif
									<td>{{ $certificado->updated_at }}</td>
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
@section('NewScript')
<script>
    function renewtoken(token) {
        $('meta[name="csrf-token"]').attr('content', token);
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
    }
    function renewTokenAfterError() {
        $.ajax({ url: "{{ url('/renewtokenaftererror') }}", method: 'GET', data: {},
            success: function(r){ renewtoken(r); },
            error: function(){ renewtoken('invalid Token'); }
        });
    }
</script>
<script>
    function ModalStatus(slug, id, boolean, value, text){
        if(boolean == 1){
            $('#ModalStatus').empty().append(`
                <div class="modal modal-default fade in" id="myModal" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <div style="font-size: 5em; color: #f39c12; text-align: center;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span style="font-size: 0.3em; color: black;"><p>¿Seguro(a) quiere `+text+` la solicitud <b>N° `+id+`</b>?</p></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">No, salir</button>
                            <button type="button" id="buttonCertStatusOK`+slug+`" data-dismiss="modal" class='btn btn-success'>Si, acepto</button>
                        </div>
                    </div></div>
                </div>
            `);
            envsubmit();
            $('#myModal').modal();
            $('#buttonCertStatusOK'+slug).on("click", function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') } });
                $.ajax({
                    url: "/certificarservicio/"+slug, method: 'GET',
                    beforeSend: function(){ let b = $('.classCertStatus'+slug); b.prop('disabled', true).empty().append(`<i class="fas fa-sync fa-spin"></i> Actualizando...`); },
                    success: function(res){
                        let b = $('.classCertStatus'+slug);
                        if (res['code'] == 200) { b.prop('class', 'btn btn-default').empty().append(`<i class="fas fa-certificate"></i> Certificado`); toastr.success(res['message']); }
                        else { b.prop('disabled', false).prop('class', 'btn btn-success classCertStatus'+slug).empty().append(`<i class="fas fa-certificate"></i> Certificar`); toastr.error(res['error']); }
                    },
                    error: function(e){ let b = $('.classCertStatus'+slug); b.prop('disabled', false).prop('class', 'btn btn-success classCertStatus'+slug).empty().append(`<i class="fas fa-certificate"></i> Certificar`); toastr.error(e['responseJSON'] && e['responseJSON']['message'] ? e['responseJSON']['message'] : 'Error'); }
                });
            });
        }
    }
    function ModalFacturar(slug, id, boolean, value, text){
        if(boolean == 1){
            $('#ModalStatus').empty().append(`
                <div class="modal modal-default fade in" id="myModal" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <div style="font-size: 5em; color: #f39c12; text-align: center;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span style="font-size: 0.3em; color: black;"><p>¿Seguro(a) quiere `+text+` la solicitud <b>N° `+id+`</b>?</p></span>
                            </div>
                            <form id="facturarservicio`+slug+`" class="row">
                                <div class="form-group col-md-6"><label>Costo Transporte</label><input type="number" name="Costo_transporte" id="Costo_transporte" class="form-control" min="0" step="0.01"></div>
                                <div class="form-group col-md-6"><label>Orden de Compra</label><input type="text" name="orden_compra" id="orden_compra" class="form-control" maxlength="20"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">No, salir</button>
                            <button type="button" id="buttonFacturarStatusOK`+slug+`" data-dismiss="modal" class='btn btn-success'>Si, acepto</button>
                        </div>
                    </div></div>
                </div>
            `);
            envsubmit();
            $('#myModal').modal();
            $('#buttonFacturarStatusOK'+slug).on("click", function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                $.ajax({
                    url: "{{ url('/facturarservicio') }}/"+slug, method: 'POST',
                    data: { ordenCompra: $('#orden_compra').val(), costoTransporte: $('#Costo_transporte').val() },
                    beforeSend: function(){ $('.classFacturarStatus'+slug).prop('disabled', true).empty().append(`<i class="fas fa-sync fa-spin"></i> Actualizando...`); },
                    success: function(res){
                        if (res['code'] == 200) { $('.classFacturarStatus'+slug).prop('class', 'btn btn-default').empty().append(`<i class="fas fa-receipt"></i> Facturado`); toastr.success(res['message']); }
                        else { $('.classFacturarStatus'+slug).prop('class', 'btn btn-info classFacturarStatus'+slug).prop('disabled', false).empty().append(`<i class="fas fa-receipt"></i> Facturar`); toastr.error(res['message'] || res['error']); }
                        if (res['new_token']) renewtoken(res['new_token']);
                    },
                    error: function(){ $('.classFacturarStatus'+slug).prop('class', 'btn btn-info classFacturarStatus'+slug).prop('disabled', false).empty().append(`<i class="fas fa-receipt"></i> Facturar`); toastr.error('Error'); renewTokenAfterError(); }
                });
            });
        }
    }
</script>
@if(in_array(Auth::user()->UsRol, Permisos::SIGNMANIFCERT))
<script>
    function firmarDocumento(CertSlug){
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $.ajax({
			url: "/firmarCertificado/"+CertSlug, method: 'PUT', data: {},
            beforeSend: function(){ $('#buttonfirmarDoc'+CertSlug).prop('disabled', true).empty().append(`<i class="fas fa-sync fa-spin"></i>`); },
            success: function(data){
                renewtoken(data.newtoken);
                $('#buttonfirmarDoc'+CertSlug).prop('disabled', false).prop('class', 'btn btn-primary').empty().append(`<i class="fas fa-lg fa-file-signature"></i>`);
                $('#AD'+CertSlug).empty().append(data.Documento['CertAuthDp']==0?'<p>Pendiente</p>':`<i class='fas fa-signature fa-lg'></i><p>${['','Director de Planta','Jefe de Logística','Jefe de Operaciones','Supervisor de Turno','Ingeniero HSEQ','Asistente de Logística','Programador'][data.Documento['CertAuthDp']]||'Error'}</p>`);
                $('#AL'+CertSlug).empty().append(data.Documento['CertAuthJl']==0?'<p>Pendiente</p>':`<i class='fas fa-signature fa-lg'></i><p>${['','Director de Planta','Jefe de Logística','Jefe de Operaciones','Supervisor de Turno','Ingeniero HSEQ','Asistente de Logística','Programador'][data.Documento['CertAuthJl']]||'Error'}</p>`);
                $('#AO'+CertSlug).empty().append(data.Documento['CertAuthJo']==0?'<p>Pendiente</p>':`<i class='fas fa-signature fa-lg'></i><p>${['','Director de Planta','Jefe de Logística','Jefe de Operaciones','Supervisor de Turno','Ingeniero HSEQ','Asistente de Logística','Programador'][data.Documento['CertAuthJo']]||'Error'}</p>`);
                toastr.success(data['message']);
            },
            error: function(xhr){ renewtoken(xhr.newtoken); $('#buttonfirmarDoc'+CertSlug).prop('disabled', false).prop('class', 'btn btn-default').empty().append(`<i class="fas fa-lg fa-file-signature"></i>`); toastr.error(xhr['responseJSON'] && xhr['responseJSON']['message']); }
        });
    }
</script>
@endif
@endsection

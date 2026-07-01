@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.solsertitle') }} {{ $anio }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
    Servicios-Solicitudes {{ $anio }}
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;">
    </div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ __('adminlte::message.solsertitleindex') }} - {{ $anio }}</h3>
                    <a href="{{ url('solicitud-servicio') }}" class="btn btn-default btn-sm pull-right" style="margin-left: 5px;" title="Volver al selector de años"><i class="fas fa-arrow-left"></i> Volver</a>
                    @include('solicitud-serv.partials.botones-crear-solicitud', ['margenPlanta' => true])
                </div>
                <div class="box box-info">
                    <div class="box-body">
                        {{-- Filtros: mes y cliente dentro del año {{ $anio }} --}}
                        <form method="GET" action="{{ route('solicitud-serv.' . $anio) }}" class="form-inline well well-sm" style="margin-bottom: 15px;">
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
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('solicitud-serv.' . $anio) }}?ver=todos" class="btn btn-default btn-sm" style="margin-left: 5px;" title="Ver todas las solicitudes de {{ $anio }} (puede tardar más)"><i class="fas fa-list"></i> Ver todos</a>
                            @if(request()->hasAny(['mes','cliente','ver','buscar']))
                                <a href="{{ route('solicitud-serv.' . $anio) }}" class="btn btn-default btn-sm" style="margin-left: 5px;"><i class="fas fa-times"></i> Limpiar</a>
                            @endif
                        </form>
                        @if(!request()->hasAny(['mes','cliente','ver','buscar']) && $Servicios->isEmpty())
                            <div class="alert alert-info" style="margin-bottom: 15px;">
                                <i class="fas fa-info-circle"></i>
                                <strong>Búsqueda rápida:</strong> Seleccione <b>mes</b> y/o <b>cliente</b> y haga clic en <b>Buscar</b> para cargar las solicitudes de {{ $anio }}. O use <b>Ver todos</b> para cargar todas las solicitudes del año (puede tardar más).
                            </div>
                        @endif
                        <div id="ModalStatus"></div>
                        <div id="ModalFacturar"></div>
                        <table id="SolicitudservicioTable" class="table table-compact table-bordered table-striped {{ $Servicios->isEmpty() ? 'd-none' : '' }}">
                            <thead>
                                <tr>
                                    <th>{{__('adminlte::message.solsershowdate')}}</th>
                                    <th>{{__('adminlte::message.solsershowdateRPDA')}}</th>
                                    <th>N°</th>
                                    <th nowrap>Status</th>
                                    <th>Factura</th>
                                    <th>{{__('adminlte::message.clientcliente')}}</th>
                                    <th>Contacto</th>
                                    <th>Comercial Asignado</th>
                                    <th>{{__('adminlte::message.solserindextrans')}}</th>
                                    <th>{{__('adminlte::message.solseraddrescollect')}}</th>
                                    <th>{{__('adminlte::message.seemore')}}</th>
                                    @if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
                                    <th>{{__('adminlte::message.solserstatuscertifi')}}</th>
                                    @endif
                                    @if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
                                    <th>{{'Facturar'}}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Servicios as $Servicio)
                                <tr style="{{$Servicio->SolSerDelete == 1 ? 'color: red' : ''}}">
                                    <td style="text-align: center;">{{date('Y/m/d', strtotime($Servicio->created_at))}}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($Servicio->recepcion == null)
                                        {{null}}
                                        @else
                                        {{date('Y/m/d', strtotime($Servicio->recepcion))}}
                                        @endif
                                    </td>
                                    <td style="text-align: center;">#{{$Servicio->ID_SolSer}}</td>
                                    @switch($Servicio->SolSerStatus)
                                    @case('Pendiente')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-default'><i class='fas fa-lg fa-hourglass-start'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Aceptado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-info'><i class='fas fa-lg fa-thumbs-up'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Aprobado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-info'><i class='fas fa-lg fa-tasks'></i></a><br>{{$Servicio->SolSerStatus}}</td>
                                    @break
                                    @case('Programado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-calendar-alt'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Notificado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-primary'><i class='far fa-lg fa-envelope'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Cancelado')
                                    @case('Fallido')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Recibido')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-danger'><i class='fas fa-lg fa-calendar-times'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Recepcionado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-primary'><i class='fas fa-check-circle'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Completado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-truck-loading'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Conciliado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-balance-scale'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('No Conciliado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-warning'><i class='fas fa-lg fa-balance-scale-right'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Corregido')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-success'><i class='fas fa-lg fa-weight'></i></a><br>{{$Servicio->SolSerStatus}}</td>
                                    @break
                                    @case('Tratado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-dumpster-fire'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Facturado')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-default'><i class='fas fas fa-lg fa-receipt'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @case('Certificacion')
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-success'><i class='fas fas fa-lg fa-certificate'></i></a><br>{{$Servicio->SolSerStatus}}
                                    </td>
                                    @break
                                    @default
                                    <td class="text-center"><a class='btn fixed_widthbtn btn-primary'><i class='fas fa-lg fa-ban'></i></a><br>{{$Servicio->SolSerStatus}}</td>
                                    @endswitch
                                    <td>{{$Servicio->SolNumeroFactura}}</td>
                                    <td>{{$Servicio->CliName}}</td>
                                    <td>
                                        <ul>
                                            <li>{{$Servicio->PersFirstName}} {{$Servicio->PersLastName}}</li>
                                            <li>{{$Servicio->PersEmail}}</li>
                                            <li>{{$Servicio->PersCellphone}}</li>
                                        </ul>
                                    </td>
                                    <td>{{$Servicio->ComercialPersFirstName.' '.$Servicio->ComercialPersLastName}}</td>
                                    <td>{{$Servicio->SolSerNameTrans}}</td>
                                    <td>{{$Servicio->SolSerCollectAddress == null ? 'N/A' : $Servicio->SolSerCollectAddress}}
                                    </td>
                                    <td style="text-align: center;"><a href='/solicitud-servicio/{{$Servicio->SolSerSlug}}' class="btn btn-info" title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a>
                                    </td>
                                    @if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
                                    @php
                                    $Status = ['Conciliado', 'Tratado'];
                                    @endphp
                                    <td>
                                        <button id="{{'buttonCertStatus'.$Servicio->SolSerSlug}}" onclick="ModalFacturacion('{{$Servicio->SolSerSlug}}', '{{$Servicio->ID_SolSer}}', '{{in_array($Servicio->SolSerStatus, $Status)}}', 'Facturada', 'facturar')" {{in_array($Servicio->SolSerStatus, $Status) ? '' :  'disabled'}} style="text-align: center;" class="{{'classFacturarStatus'.$Servicio->SolSerSlug}} btn btn-{{$Servicio->SolSerStatus == 'Facturado' ? 'default' : 'info'}}"><i class="fas fa-certificate"></i>
                                            {{'Facturar'}}</button>
                                    </td>
                                    @endif
                                    @if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
                                    @php
                                    $Status = ['Conciliado', 'Tratado', 'Facturado'];
                                    @endphp
                                    <td>
                                        <button id="{{'buttonCertStatus'.$Servicio->SolSerSlug}}" onclick="ModalCertificacion('{{$Servicio->SolSerSlug}}', '{{$Servicio->ID_SolSer}}', '{{in_array($Servicio->SolSerStatus, $Status)}}', 'Certificada', 'certificar')" {{in_array($Servicio->SolSerStatus, $Status) ? '' :  'disabled'}} style="text-align: center;" class="{{'classCertStatus'.$Servicio->SolSerSlug}} btn btn-{{$Servicio->SolSerStatus == 'Certificacion' ? 'default' : 'success'}}"><i class="fas fa-certificate"></i>
                                            {{__('adminlte::message.solserstatuscertifi')}}</button>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
        $.ajax({
            url: "{{url('/renewtokenaftererror')}}",
            method: 'GET',
            data:{},
            success: function(response){ renewtoken(response); },
            error: function(){ renewtoken('invalid Token'); },
        });
    }
    function updatecaracteresObs() {
        var area = document.getElementById("textDescriptionObs");
        var message = document.getElementById("caracteresrestantesObs");
        if (area && message) {
            message.innerHTML = (4000-area.value.length) + " caracteres restantes";
        }
    }
    $(document).ready(function(){
        var area = document.getElementById("textDescriptionObs");
        var message = document.getElementById("caracteresrestantesObs");
        if (area && message) {
            $('#textDescriptionObs').keyup(function() {
                message.innerHTML = (4000-area.value.length) + " caracteres restantes";
            });
        }
    });
</script>
@if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
<script>
    function ModalCertificacion(slug, id, boolean, value, text){
        if(boolean == 1){
            $('#ModalStatus').empty();
            $('#ModalStatus').append(`
                <div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
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
                        </div>
                    </div>
                </div>
            `);
            envsubmit();
            $('#myModal').modal();
            $('#buttonCertStatusOK'+slug).on("click", function() {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') } });
                $.ajax({
                    url: "/certificarservicio/"+slug,
                    method: 'GET',
                    beforeSend: function(){
                        let b = $('.classCertStatus'+slug);
                        b.prop('disabled', true).empty().append(`<i class="fas fa-sync fa-spin"></i> Actualizando...`);
                    },
                    success: function(res){
                        let b = $('.classCertStatus'+slug);
                        if (res['code'] == 200) {
                            b.prop('class', 'btn btn-default').empty().append(`<i class="fas fa-certificate"></i> Certificado`);
                            toastr.success(res['message']);
                        } else {
                            b.prop('disabled', false).prop('class', 'btn btn-success classCertStatus'+slug).empty().append(`<i class="fas fa-certificate"></i> Certificar`);
                            toastr.error(res['error']);
                        }
                    },
                    error: function(e){
                        let b = $('.classCertStatus'+slug);
                        b.prop('disabled', false).prop('class', 'btn btn-success classCertStatus'+slug).empty().append(`<i class="fas fa-certificate"></i> Certificar`);
                        toastr.error(e['responseJSON'] && e['responseJSON']['message'] ? e['responseJSON']['message'] : 'Error');
                    }
                });
            });
        }
    }
</script>
@endif
@if(in_array(Auth::user()->UsRol, Permisos::COMERCIALES) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALES))
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
<script>
    var inicio = "{{date('Y/m/d')}}", fin = "{{date('Y/m/d')}}";
    function checkFacturacionTipo() {
        var tipo = document.getElementById("selectTipoFact");
        if (tipo && tipo.value == 'Mensual') {
            $('#rangoContainer').append(`<div class="form-group col-md-6"><label for="RangoFechas">Fecha Inicial</label><input type="text" name="RangoFechas" id="RangoFechas" class="form-control" required minlength="23"></div>`);
            $('#tipoFactContainer').addClass('col-md-6').removeClass('col-md-12');
            $('input[name="RangoFechas"]').daterangepicker({
                "autoApply": true,
                ranges: { 'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()], 'Este Mes': [moment().startOf('month'), moment().endOf('month')], 'Ultimo mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')] },
                "locale": { "format": "YYYY/MM/DD", "separator": " - ", "applyLabel": "Aplicar", "cancelLabel": "Cancelar", "monthNames": ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"], "firstDay": 1 },
                "minDate": "{{date('Y/m/d', strtotime('1 year ago'))}}", "drops": "auto"
            }, function(s, e) { inicio = s.format('YYYY/MM/DD'); fin = e.format('YYYY/MM/DD'); });
        } else {
            $('#rangoContainer').empty();
            $('#tipoFactContainer').addClass('col-md-12').removeClass('col-md-6');
            inicio = fin = "{{date('Y/m/d')}}";
        }
    }
</script>
<script>
    function ModalFacturacion(slug, id, boolean, value, text){
        if(boolean == 1){
            $('#ModalFacturar').empty();
            $('#ModalFacturar').append(`
                <div class="modal fade in" id="myModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                <div style="font-size: 5em; color: #f39c12; text-align: center;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span style="font-size: 0.3em; color: black;"><p>¿Seguro(a) quiere `+text+` la solicitud <b>N° `+id+`</b>?</p></span>
                                </div>
                                <form id="facturarservicio`+slug+`" class="row">
                                    <div class="form-group col-md-12" id="tipoFactContainer">
                                        <label>Tipo de Facturacion</label>
                                        <select onchange="checkFacturacionTipo()" id="selectTipoFact" name="FacturacionTipo" class="form-control" required>
                                            <option value="Servicio" selected>Servicio</option>
                                            <option value="Mensual">Rango de fechas</option>
                                        </select>
                                    </div>
                                    <div id="rangoContainer"></div>
                                    <div class="form-group col-md-6">
                                        <label>Costo Transporte</label>
                                        <input required type="number" name="Costo_transporte" class="form-control" min="0" step="0.01">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Orden de Compra</label>
                                        <input type="text" name="orden_compra" class="form-control" maxlength="20">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Observaciones</label>
                                        <small id="caracteresrestantesObs" class="help-block"></small>
                                        <textarea id="textDescriptionObs" rows="5" maxlength="4000" class="form-control" name="solserdescript"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">No, salir</button>
                                <button type="button" id="buttonFacturarStatusOK`+slug+`" data-dismiss="modal" class='btn btn-success'>Si, acepto</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            var area = document.getElementById("textDescriptionObs"), message = document.getElementById("caracteresrestantesObs");
            if (area && message) $('#textDescriptionObs').keyup(function() { message.innerHTML = (4000-area.value.length) + " caracteres restantes"; });
            envsubmit();
            $('#myModal').modal();
            $('#buttonFacturarStatusOK'+slug).on("click", function() {
                $("#facturarservicio"+slug).validate({
                    rules: { FacturacionTipo: "required", Costo_transporte: "required" },
                    messages: { FacturacionTipo: "Requerido", Costo_transporte: "Requerido" }
                });
                if (!$("#facturarservicio"+slug).valid()) { toastr.error('Verifique los campos obligatorios'); return false; }
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                $.ajax({
                    url: "{{url('/facturarservicio')}}/"+slug,
                    method: 'POST',
                    data: { FacturacionTipo:$('#selectTipoFact').val(), ordenCompra:$('#orden_compra').val(), costoTransporte:$('#Costo_transporte').val(), FechaInicial:inicio, FechaFinal:fin },
                    beforeSend: function(){ $('.classFacturarStatus'+slug).prop('disabled', true).empty().append(`<i class="fas fa-sync fa-spin"></i> Actualizando...`); },
                    success: function(res){
                        if (res['code'] == 200) {
                            $('.classFacturarStatus'+slug).prop('class', 'btn btn-default').empty().append(`<i class="fas fa-receipt"></i> Facturado`);
                            toastr.success(res['message']);
                        } else {
                            $('.classFacturarStatus'+slug).prop('class', 'btn btn-info classFacturarStatus'+slug).prop('disabled', false).empty().append(`<i class="fas fa-receipt"></i> Facturar`);
                            toastr.error(res['message'] || res['error']);
                        }
                        if (res['new_token']) renewtoken(res['new_token']);
                    },
                    error: function(){ $('.classFacturarStatus'+slug).prop('class', 'btn btn-info classFacturarStatus'+slug).prop('disabled', false).empty().append(`<i class="fas fa-receipt"></i> Facturar`); toastr.error('Error'); renewTokenAfterError(); }
                });
            });
        }
    }
</script>
@endif
@endsection


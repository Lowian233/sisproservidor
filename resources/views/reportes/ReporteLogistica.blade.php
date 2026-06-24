@extends('layouts.appReportes')
@section('htmlheader_title','Reportes')
{{-- @endsection --}}
@section('contentheader_title', '')
{{-- @endsection --}}
@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <div class="box">
                <div class="box-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <h3 class="box-title">reporte de cantidades</h3>
                                <a href="{{ route('reportes.ReportLogistica') }}" class="btn btn-success pull-right" style="margin-left: 5px;">
                                    <i class="fas fa-plus"></i> Nueva Consulta
                                </a>
                                <button class="btn btn-primary pull-right" type="button" data-toggle="collapse" data-target=".panels" aria-expanded="false" aria-controls="collapseExample">
                                    <div class="text-nowrap bd-highlight">
                                        <i class="fas fa-filter"></i> Segmentacion
                                    </div>
                                </button>
                                <button  style="margin-right: 5px; color:#3c8dbc;" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target=".filters" aria-expanded="false" aria-controls="collapseExample">
                                    <div class="text-nowrap bd-highlight">
                                        <i class="fas fa-filter"></i> Filtros
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table id="reporteTable" class=" table-compact table-bordered">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th># Solicitud</th>
                                <th>Fecha de Recepción</th>
                                <th>Cliente</th>
                                <th>Tipo Cliente</th>
                                <th>NIT Cliente</th>
                                <th>Generador</th>
                                <th>NIT Generador</th>
                                <th>Telefono</th>
                                <th>Dirección de servicio</th>
                                <th>Municipio</th>
                                <th>Nombre de residuo</th>
                                <th>Estado</th>
                                <th>Corriente</th>
                                <th>Peligrosidad</th>
                                <th>Tratamiento</th>
                                <th>Gestor</th>
                                <th>Cantidad Declarada</th>
                                <th>Cantidad Recibida</th>
                                <th>Cantidad Conciliada</th>
                                <th>No. Certificado</th>
                                <th>Empresa Transportadora</th>
                                <th>Dirección Empresa Transportadora</th>
                                <th>Municipio Empresa Transportadora</th>
                                <th>Status</th>
                                <th>TIPO DE SERVICIO</th>
                                <th>CONDUCTOR</th>
                                <th>PLACA</th>
                                <th>AYUDANTE</th>
                                <th>TIPO DE TRANSPORTE</th>      
                            </tr>
                        </thead>
                        <tbody id="readyTable">
                            @foreach ($servicios as $servicio)
                                @foreach ($servicio->SolicitudResiduo as $solres)
                                    <tr>
                        {{-- 1 RM --}}
                        <td>
                            @if (isset($solres->SolResRM) && is_array($solres->SolResRM))
                            @foreach ($solres->SolResRM as $rm) {{ $rm }}<br> @endforeach
                            @endif
                        </td>

                        {{-- 2 # Solicitud --}}
                        <td>{{ $servicio->ID_SolSer }}</td>

                        {{-- 3 Fecha de Recepción (lógica priorizada) --}}
                        <td>
                            @php
                            $fechaMostrar = null;
                            if (isset($servicio->programacionesrecibidas) && $servicio->programacionesrecibidas->count() > 0 && $servicio->programacionesrecibidas->first()->ProgVehSalida) {
                                $fechaMostrar = $servicio->programacionesrecibidas->first()->ProgVehSalida;
                            } elseif (isset($servicio->programacionesrealizadas) && $servicio->programacionesrealizadas->count() > 0 && $servicio->programacionesrealizadas->first()->ProgVehSalida) {
                                $fechaMostrar = $servicio->programacionesrealizadas->first()->ProgVehSalida;
                            } elseif (!empty($servicio->ProgVehSalida) && $servicio->ProgVehSalida != '0000-00-00 00:00:00') {
                                $fechaMostrar = $servicio->ProgVehSalida;
                            } elseif (!empty($servicio->created_at)) {
                                $fechaMostrar = $servicio->created_at;
                            }
                            @endphp
                            {{ $fechaMostrar ? date('d/m/Y', strtotime($fechaMostrar)) : 'N/A' }}
                        </td>

                        {{-- 4 Cliente --}}
                        <td>{{ optional($servicio->cliente)->CliName }}</td>

                        {{-- 5 Tipo Cliente --}}
                        <td>
                            @if(optional($servicio->cliente)->CliCategoria == 'Cliente')
                            <span class="label label-primary">Regular</span>
                            @elseif(optional($servicio->cliente)->CliCategoria == 'ClientePrepago')
                            <span class="label label-warning">Express</span>
                            @else
                            <span class="label label-default">{{ optional($servicio->cliente)->CliCategoria ?? 'N/A' }}</span>
                            @endif
                        </td>

                        {{-- 6 NIT Cliente --}}
                        <td>{{ optional($servicio->cliente)->CliNit }}</td>

                        {{-- 7 Generador (Nombre + Sede) --}}
                        <td>
                            {{ optional(optional(optional($solres->generespel)->gener_sedes)->generadors)->GenerName }}
                            @php $gsede = optional(optional($solres->generespel)->gener_sedes)->GSedeName; @endphp
                            @if($gsede) <br> ({{ $gsede }}) @endif
                        </td>

                        {{-- 8 NIT Generador --}}
                        <td>{{ optional(optional(optional($solres->generespel)->gener_sedes)->generadors)->GenerNit }}</td>

                        {{-- 9 Teléfono --}}
                        <td>{{ optional(optional($solres->generespel)->gener_sedes)->GSedeCelular }}</td>

                        {{-- 10 Dirección de servicio --}}
                        <td>{{ optional(optional($solres->generespel)->gener_sedes)->GSedeAddress }}</td>

                        {{-- 11 Municipio --}}
                        <td>{{ optional(optional(optional($solres->generespel)->gener_sedes)->municipio)->MunName }}</td>

                        {{-- 12 Nombre de residuo --}}
                        <td>{{ optional(optional($solres->generespel)->respels)->RespelName }}</td>

                        {{-- 13 Estado --}}
                        <td>{{ optional(optional($solres->generespel)->respels)->RespelEstado }}</td>

                        {{-- 14 Corriente --}}
                        <td>
                            @php
                            $r = optional(optional($solres->generespel)->respels);
                            $corr = $r->YRespelClasf4741 ?? $r->ARespelClasf4741 ?? null;
                            @endphp
                            {{ $corr ?? 'N/A' }}
                        </td>

                        {{-- 15 Peligrosidad --}}
                        <td>{{ optional(optional($solres->generespel)->respels)->RespelIgrosidad }}</td>

                        {{-- 16 Tratamiento --}}
                        <td>{{ optional(optional($solres->requerimiento)->tratamiento)->TratName }}</td>

                        {{-- 17 Gestor --}}
                        <td>{{ optional(optional(optional($solres->requerimiento)->tratamiento)->gestor)->clientes->CliShortname ?? null }}</td>

                        {{-- 18 Cantidad Declarada --}}
                        <td>{{ $solres->SolResKgEnviado }}</td>

                        {{-- 19 Cantidad Recibida --}}
                        <td>{{ $solres->SolResKgRecibido }}</td>

                        {{-- 20 Cantidad Conciliada --}}
                        <td>{{ $solres->SolResKgConciliado }}</td>

                        {{-- 21 No. Certificado --}}
                        <td>
                            @if ($solres->certdato)
                            @if($solres->certdato->certificado->CertType == 0)
                                {{ $solres->certdato->certificado->ID_Cert }}
                            @else
                                M{{ $solres->certdato->certificado->ID_Cert }}
                            @endif
                            @else
                            Certificado no encontrado
                            @endif
                        </td>

                        {{-- 22 Empresa Transportadora --}}
                        <td>{{ $servicio->SolSerNameTrans }}</td>

                        {{-- 23 Dirección Empresa Transportadora --}}
                        <td>{{ $servicio->SolSerAdressTrans }}</td>

                        {{-- 24 Municipio Empresa Transportadora --}}
                        <td>{{ optional($servicio->Municipio)->MunName }}</td>

                        {{-- 25 Status --}}
                        <td>{{ $servicio->SolSerStatus }}</td>

                        {{-- 26 TIPO DE SERVICIO (Exclusivo/Externo/Recorrido) --}}
                        <td>
                            @if($servicio->ProgVehExclusive == 1)
                            <span class="label label-primary">Exclusivo</span>
                            @elseif($servicio->SolSerTipo == 'Externo')
                            <span class="label label-warning">Externo</span>
                            @else
                            <span class="label label-info">Recorrido</span>
                            @endif
                        </td>

                        {{-- 27 CONDUCTOR --}}
                        <td>
                            @if($servicio->conductor_nombre)
                            {{ $servicio->conductor_nombre }} {{ $servicio->conductor_apellido }}
                            @elseif($servicio->ProgVehNameConductorEXT)
                            {{ $servicio->ProgVehNameConductorEXT }}
                            @else
                            N/A
                            @endif
                        </td>

                        {{-- 28 PLACA --}}
                        <td>
                            @if($servicio->vehiculo_placa)
                            {{ $servicio->vehiculo_placa }}
                            @elseif($servicio->ProgVehPlacaEXT)
                            {{ $servicio->ProgVehPlacaEXT }}
                            @else
                            N/A
                            @endif
                        </td>

                        {{-- 29 AYUDANTE --}}
                        <td>
                            @if($servicio->ayudante_nombre)
                            {{ $servicio->ayudante_nombre }} {{ $servicio->ayudante_apellido }}
                            @elseif($servicio->ProgVehNameAuxiliarEXT)
                            {{ $servicio->ProgVehNameAuxiliarEXT }}
                            @else
                            N/A
                            @endif
                        </td>

                           {{-- 30 TIPO DE TRANSPORTE (Interno/Externo/alquilado) --}}
                            <td>
                                @php
                                    $tipoTransporte = null;
                                    // 1) Priorizar razón social de Prosarc en empresa transportadora
                                    $transName = $servicio->SolSerNameTrans ?? '';
                                    if(!empty($transName)){
                                        $upperName = function_exists('mb_strtoupper') ? mb_strtoupper($transName, 'UTF-8') : strtoupper($transName);
                                        if(strpos($upperName, 'PROTECCIÓN SERVICIOS AMBIENTALES RESPEL') !== false
                                           || strpos($upperName, 'PROTECCION SERVICIOS AMBIENTALES RESPEL') !== false
                                           || strpos($upperName, 'PROSARC') !== false){
                                            $tipoTransporte = 'Prosarc';
                                        }
                                    }

                                    // 2) Si la empresa NO es Prosarc pero ProgVehtipo viene como 1 (interno), corregir a Alquilado/Externo
                                    if(!$tipoTransporte && !empty($transName) && isset($servicio->ProgVehtipo) && $servicio->ProgVehtipo == 1){
                                        $isProsarcName = isset($upperName) && (
                                            strpos($upperName, 'PROTECCIÓN SERVICIOS AMBIENTALES RESPEL') !== false ||
                                            strpos($upperName, 'PROTECCION SERVICIOS AMBIENTALES RESPEL') !== false ||
                                            strpos($upperName, 'PROSARC') !== false
                                        );
                                        if(!$isProsarcName){
                                            // Si el tipo de servicio es Externo/Cliente/Generador, marcar Externo, si no, Alquilado
                                            if(!empty($servicio->SolSerTipo) && in_array($servicio->SolSerTipo, ['Externo','Cliente','Generador'])){
                                                $tipoTransporte = 'Externo';
                                            } else {
                                                $tipoTransporte = 'Alquilado';
                                            }
                                        }
                                    }

                                    // 3) Si no se determinó por empresa ni por corrección, usar ProgVehtipo
                                    if(!$tipoTransporte && isset($servicio->ProgVehtipo)){
                                        if($servicio->ProgVehtipo == 1){
                                            $tipoTransporte = 'Prosarc';
                                        } elseif($servicio->ProgVehtipo == 2){
                                            $tipoTransporte = 'Alquilado';
                                        } elseif($servicio->ProgVehtipo == 0){
                                            $tipoTransporte = 'Externo';
                                        }
                                    }

                                    // 4) Fallback según SolSerTipo de la solicitud
                                    if(!$tipoTransporte && !empty($servicio->SolSerTipo)){
                                        if($servicio->SolSerTipo === 'Interno'){
                                            $tipoTransporte = 'Prosarc';
                                        } elseif(in_array($servicio->SolSerTipo, ['Externo','Cliente','Generador'])){
                                            $tipoTransporte = 'Externo';
                                        }
                                    }
                                @endphp
                                {{ $tipoTransporte ?? 'No Especificado' }}
                            </td>
                        </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection
@extends('layouts.appReportes')

@section('htmlheader_title','Reportes de Cliente')
{{-- @endsection --}}
@section('contentheader_title', '')
{{-- @endsection --}}

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <!-- /.box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Generar Reporte de Servicios</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('reportes.cliente.generar') }}" method="POST" class="form-horizontal">
                        @csrf
                        <div class="box box-info">
                            <div class="box-body">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Fecha de inicio</label>
                                            <input required type="date" name="Fecha_Inicio" class="form-control" value="{{ old('Fecha_Inicio', isset($request) ? $request->Fecha_Inicio : date('Y-m-01')) }}">
                                            @error('Fecha_Inicio')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label>Fecha Final</label>
                                            <input required type="date" name="Fecha_Fin" class="form-control" value="{{ old('Fecha_Fin', isset($request) ? $request->Fecha_Fin : date('Y-m-t')) }}">
                                            @error('Fecha_Fin')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success pull-right">
                                        <i class="fa fa-search"></i> Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(isset($servicios) && $servicios->count() > 0)
                    <div class="box box-info">
                        <div class="box-header">
                            <h3 class="box-title">Resultados de la búsqueda</h3>
                            <div class="box-tools pull-right">
                                <a href="{{ route('reportes.cliente') }}" class="btn btn-info">
                                    <i class="fa fa-plus"></i> Nueva Consulta
                                </a>
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <table id="serviciosTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>RM</th>
                                        <th>Fecha de Servicio</th>
                                        <th>N° de Servicio</th>
                                        <th>Generador</th>
                                        <th>Dirección de Servicio</th>
                                        <th>Municipio</th>
                                        <th>Residuo</th>
                                        <th>Estado Residuo</th>
                                        <th>Corriente</th>
                                        <th>Peligrosidad</th>
                                        <th>Tratamiento</th>
                                        <th>Cantidad Declarada</th>
                                        <th>Cantidad Recibida</th>
                                        <th>Cantidad Conciliada</th>
                                        <th>Unidad</th>
                                        <th>No. Certificado</th>
                                        <th>Status</th>
                                        <th>Conductor</th>
                                        <th>Placa</th>
                                        <th>Ayudante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($servicios as $servicio)
                                        @foreach($servicio->SolicitudResiduo as $residuo)
                                        <tr>
                                            {{-- RM --}}
                                            <td>
                                                @if(isset($residuo->SolResRM) && is_array($residuo->SolResRM))
                                                    @foreach($residuo->SolResRM as $rm)
                                                        {{ $rm }}<br>
                                                    @endforeach
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            
                                            {{-- Fecha de Servicio --}}
                                            <td>
                                                @php
                                                    $fechaMostrar = null;
                                                    
                                                    // 1. PRIORIDAD: Fecha de recepción de la solicitud (ProgVehSalida de programaciones recibidas)
                                                    if (isset($servicio->programacionesrecibidas) && $servicio->programacionesrecibidas->count() > 0 && $servicio->programacionesrecibidas->first()->ProgVehSalida) {
                                                        $fechaMostrar = $servicio->programacionesrecibidas->first()->ProgVehSalida;
                                                    }
                                                    // 2. Segunda opción: Fecha de programación realizada
                                                    elseif (isset($servicio->programacionesrealizadas) && $servicio->programacionesrealizadas->count() > 0 && $servicio->programacionesrealizadas->first()->ProgVehSalida) {
                                                        $fechaMostrar = $servicio->programacionesrealizadas->first()->ProgVehSalida;
                                                    }
                                                    // 3. Tercera opción: Fecha de salida de la programación
                                                    elseif ($servicio->ProgVehSalida && $servicio->ProgVehSalida != '0000-00-00 00:00:00') {
                                                        $fechaMostrar = $servicio->ProgVehSalida;
                                                    }
                                                    // 4. Cuarta opción: Fecha de creación de la solicitud
                                                    elseif ($servicio->created_at) {
                                                        $fechaMostrar = $servicio->created_at;
                                                    }
                                                @endphp
                                                
                                                @if($fechaMostrar)
                                                    {{ date('d/m/Y', strtotime($fechaMostrar)) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            
                                            {{-- N° de Servicio --}}
                                            <td>{{$servicio->ID_SolSer}}</td>
                                            
                                            {{-- Generador --}}
                                            <td>
                                                {{ optional(optional(optional($residuo->generespel)->gener_sedes)->generadors)->GenerName ?? 'N/A' }}
                                                @php $gsede = optional(optional($residuo->generespel)->gener_sedes)->GSedeName; @endphp
                                                @if($gsede) <br><small>({{ $gsede }})</small> @endif
                                            </td>
                                            
                                            {{-- Dirección de Servicio --}}
                                            <td>{{ optional(optional($residuo->generespel)->gener_sedes)->GSedeAddress ?? 'N/A' }}</td>
                                            
                                            {{-- Municipio --}}
                                            <td>{{ optional(optional(optional($residuo->generespel)->gener_sedes)->municipio)->MunName ?? 'N/A' }}</td>
                                            
                                            {{-- Residuo --}}
                                            <td>{{ optional(optional($residuo->generespel)->respels)->RespelName ?? 'N/A' }}</td>
                                            
                                            {{-- Estado Residuo --}}
                                            <td>{{ optional(optional($residuo->generespel)->respels)->RespelEstado ?? 'N/A' }}</td>
                                            
                                            {{-- Corriente --}}
                                            <td>
                                                @php
                                                    $r = optional(optional($residuo->generespel)->respels);
                                                    $corr = $r->YRespelClasf4741 ?? $r->ARespelClasf4741 ?? null;
                                                @endphp
                                                {{ $corr ?? 'N/A' }}
                                            </td>
                                            
                                            {{-- Peligrosidad --}}
                                            <td>{{ optional(optional($residuo->generespel)->respels)->RespelIgrosidad ?? 'N/A' }}</td>
                                            
                                            {{-- Tratamiento --}}
                                            <td>{{ optional(optional($residuo->requerimiento)->tratamiento)->TratName ?? 'N/A' }}</td>
                                            
                                            {{-- Cantidad Declarada --}}
                                            <td>{{ $residuo->SolResKgEnviado ?? 'N/A' }}</td>
                                            
                                            {{-- Cantidad Recibida --}}
                                            <td>{{ $residuo->SolResKgRecibido ?? 'N/A' }}</td>
                                            
                                            {{-- Cantidad Conciliada --}}
                                            <td>{{ $residuo->SolResKgConciliado ?? 'N/A' }}</td>
                                            
                                            {{-- Unidad --}}
                                            <td>{{ $residuo->SolResTypeUnidad ?? 'N/A' }}</td>
                                            
                                            {{-- No. Certificado --}}
                                            <td>
                                                @if($residuo->certdato && $residuo->certdato->certificado)
                                                    @if($residuo->certdato->certificado->CertType == 0)
                                                        {{ $residuo->certdato->certificado->ID_Cert }}
                                                    @else
                                                        M{{ $residuo->certdato->certificado->ID_Cert }}
                                                    @endif
                                                @elseif($residuo->certdatoexpress && $residuo->certdatoexpress->certificado)
                                                    @if($residuo->certdatoexpress->certificado->CertType == 0)
                                                        {{ $residuo->certdatoexpress->certificado->ID_Cert }}
                                                    @else
                                                        M{{ $residuo->certdatoexpress->certificado->ID_Cert }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            
                                            {{-- Status --}}
                                            <td>{{ $servicio->SolSerStatus ?? 'N/A' }}</td>

                                            
                                            {{-- Conductor --}}
                                            <td>
                                                @if($servicio->ProgVehtipo == 1 && $servicio->conductor_nombre)
                                                    {{ $servicio->conductor_nombre }} {{ $servicio->conductor_apellido }}
                                                @elseif($servicio->ProgVehtipo == 2 && $servicio->ProgVehNameConductorEXT)
                                                    {{ $servicio->ProgVehNameConductorEXT }}
                                                @elseif($servicio->ProgVehtipo == 0 && !empty($servicio->SolSerConductor))
                                                    {{ is_array($servicio->SolSerConductor) ? ($servicio->SolSerConductor[0] ?? 'N/A') : $servicio->SolSerConductor }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            
                                            {{-- Placa --}}
                                            <td>
                                                @if($servicio->ProgVehtipo == 1 && $servicio->vehiculo_placa)
                                                    {{ $servicio->vehiculo_placa }}
                                                @elseif($servicio->ProgVehtipo == 2 && $servicio->ProgVehPlacaEXT)
                                                    {{ $servicio->ProgVehPlacaEXT }}
                                                @elseif($servicio->ProgVehtipo == 0 && !empty($servicio->SolSerVehiculo))
                                                    {{ is_array($servicio->SolSerVehiculo) ? ($servicio->SolSerVehiculo[0] ?? 'N/A') : $servicio->SolSerVehiculo }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            
                                            {{-- Ayudante --}}
                                            <td>
                                                @if($servicio->ayudante_nombre)
                                                    {{ $servicio->ayudante_nombre }} {{ $servicio->ayudante_apellido }}
                                                @elseif($servicio->ProgVehNameAuxiliarEXT)
                                                    {{ $servicio->ProgVehNameAuxiliarEXT }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @elseif(isset($servicios))
                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> No se encontraron resultados</h4>
                        No hay servicios registrados para el período seleccionado.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(isset($servicios) && $servicios->count() > 0)
<script>
$(document).ready(function() {
    // El layout appReportes ya inicializa automáticamente #serviciosTable con botones de Excel
    // Solo ajustamos la ordenación si es necesario
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('#serviciosTable')) {
            $('#serviciosTable').DataTable().order([0, 'desc']).draw();
        }
    }, 400);
});
</script>
@endif
@endsection 
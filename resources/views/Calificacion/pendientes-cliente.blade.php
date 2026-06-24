@extends('layouts.app')

@section('htmlheader_title')
    Mis Calificaciones Pendientes
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #d4fc79, #00C851); padding-right:30vw; position:relative; overflow:hidden;">
    Mis Calificaciones Pendientes
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Servicios Pendientes de Calificar</h3>
                </div>
                <div class="box-body table-responsive">
                    @if($calificaciones->count() > 0)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            Tienes <strong>{{ $calificaciones->count() }}</strong> servicio(s) pendiente(s) de calificar.
                            Tu opinión es muy importante para nosotros.
                        </div>
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Solicitud #</th>
                                    <th>Cliente</th>
                                    <th>Fecha de Servicio</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calificaciones as $calificacion)
                                <tr>
                                    <td>
                                        @if($calificacion->servicio)
                                            #{{ $calificacion->servicio->ID_SolSer }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($calificacion->servicio && $calificacion->servicio->cliente)
                                            {{ $calificacion->servicio->cliente->CliName }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($calificacion->created_at)
                                            {{ $calificacion->created_at->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label label-warning">
                                            <i class="fa fa-clock-o"></i> Pendiente
                                        </span>
                                    </td>
                                    <td>
                                        @if($calificacion->signed_hash)
                                            <a href="{{ route('calificaciones.create', $calificacion->signed_hash) }}" 
                                               class="btn btn-success btn-sm">
                                                <i class="fa fa-star"></i> Calificar Ahora
                                            </a>
                                        @else
                                            <span class="text-muted">No disponible</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> 
                            ¡Excelente! No tienes servicios pendientes de calificar. 
                            Gracias por tu participación.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


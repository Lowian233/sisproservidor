@extends('layouts.app')
@section('htmlheader_title')
Detalle de Calificación
@endsection
@section('contentheader_title')
Detalle de Calificación #{{ $calificacion->ID_Calificacion }}
@endsection
@section('main-content')
<div class="container-fluid sparck-screen">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Información de la Calificación</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>ID Calificación:</dt>
                        <dd>{{ $calificacion->ID_Calificacion }}</dd>

                        <dt>Solicitud de Servicio:</dt>
                        <dd>
                            @if($calificacion->servicio)
                                #{{ $calificacion->servicio->ID_SolSer }}
                            @else
                                N/A
                            @endif
                        </dd>

                        <dt>Cliente:</dt>
                        <dd>
                            @if($calificacion->cliente)
                                {{ $calificacion->cliente->name }}
                            @else
                                N/A
                            @endif
                        </dd>

                        <dt>Calificación:</dt>
                        <dd>
                            @if($calificacion->score)
                                @if($calificacion->score == 1)
                                    <span style="font-size: 50px; background-color: #dc3545; border-radius: 50%; padding: 10px 15px; display: inline-block;">😞</span>
                                    <strong class="label label-danger" style="font-size: 16px; padding: 8px 15px;">Malo</strong>
                                @elseif($calificacion->score == 2)
                                    <span style="font-size: 50px; background-color: #ffc107; border-radius: 50%; padding: 10px 15px; display: inline-block;">😐</span>
                                    <strong class="label label-warning" style="font-size: 16px; padding: 8px 15px;">Regular</strong>
                                @elseif($calificacion->score == 3)
                                    <span style="font-size: 50px; background-color: #28a745; border-radius: 50%; padding: 10px 15px; display: inline-block;">😊</span>
                                    <strong class="label label-success" style="font-size: 16px; padding: 8px 15px;">Bueno</strong>
                                @endif
                            @else
                                <span class="label label-warning">Pendiente</span>
                            @endif
                        </dd>

                        <dt>Comentario:</dt>
                        <dd>{{ $calificacion->comment ?? 'Sin comentario' }}</dd>

                        <dt>Estado:</dt>
                        <dd>
                            @if($calificacion->status == 'completed')
                                <span class="label label-success">Completada</span>
                            @elseif($calificacion->status == 'pending')
                                <span class="label label-warning">Pendiente</span>
                            @else
                                <span class="label label-default">{{ $calificacion->status }}</span>
                            @endif
                        </dd>

                        <dt>Fecha de Completación:</dt>
                        <dd>{{ $calificacion->completed_at ? $calificacion->completed_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>

                        @if(isset($calificacion->meta['respuesta']))
                        <dt>Respuesta:</dt>
                        <dd>
                            <div class="alert alert-info">
                                {{ $calificacion->meta['respuesta'] }}
                                @if(isset($calificacion->meta['respondido_at']))
                                    <br><small>Respondido el: {{ \Carbon\Carbon::parse($calificacion->meta['respondido_at'])->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </dd>
                        @endif
                    </dl>
                </div>
                <div class="box-footer">
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


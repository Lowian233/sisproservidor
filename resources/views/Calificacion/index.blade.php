@extends('layouts.app')
@section('htmlheader_title')
Lista de Calificaciones
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #d4fc79, #00C851); padding-right:30vw; position:relative; overflow:hidden;">
    Calificaciones
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid sparck-screen">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Calificaciones de Servicios</h3>
                    <div class="box-tools">
                        <a href="{{ route('Calificaciones.index') }}" class="btn btn-primary">
                            <i class="fa fa-list"></i> Vista Consolidada
                        </a>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitud</th>
                                <th>Cliente</th>
                                <th>Calificación</th>
                                <th>Comentario</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($calificaciones as $calificacion)
                            <tr>
                                <td>{{ $calificacion->ID_Calificacion }}</td>
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
                                    @if($calificacion->score)
                                        @if($calificacion->score == 1)
                                            <span style="font-size: 30px; background-color: #dc3545; border-radius: 50%; padding: 5px 10px; display: inline-block;">😞</span>
                                            <span class="label label-danger">Deficiente</span>
                                        @elseif($calificacion->score == 2)
                                            <span style="font-size: 30px; background-color: #ffc107; border-radius: 50%; padding: 5px 10px; display: inline-block;">😐</span>
                                            <span class="label label-warning">Regular</span>
                                        @elseif($calificacion->score == 3)
                                            <span style="font-size: 30px; background-color: #28a745; border-radius: 50%; padding: 5px 10px; display: inline-block;">😊</span>
                                            <span class="label label-success">Excelente</span>
                                        @endif
                                    @else
                                        <span class="label label-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $calificacion->comment ?? 'Sin comentario' }}</td>
                                <td>
                                    @if($calificacion->status == 'completed')
                                        <span class="label label-success">Completada</span>
                                    @elseif($calificacion->status == 'pending')
                                        <span class="label label-warning">Pendiente</span>
                                    @else
                                        <span class="label label-default">{{ $calificacion->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $calificacion->completed_at ? $calificacion->completed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('calificaciones.show', $calificacion->ID_Calificacion) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay calificaciones registradas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

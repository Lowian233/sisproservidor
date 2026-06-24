@extends('layouts.app')
@section('htmlheader_title')
Vista Consolidada de Calificaciones
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #d4fc79, #00C851); padding-right:30vw; position:relative; overflow:hidden;">
    Vista Consolidada de Calificaciones
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid sparck-screen">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Todas las Calificaciones por Número de Solicitud</h3>
                    <div class="box-tools">
                        <a href="{{ route('calificaciones.index') }}" class="btn btn-primary">
                            <i class="fa fa-list"></i> Vista Detallada
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    @forelse($calificaciones as $idSolSer => $calificacionesGrupo)
                        <div class="panel panel-default" style="margin-bottom: 20px;">
                            <div class="panel-heading" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <h4 class="panel-title">
                                    <strong>Solicitud #{{ $idSolSer }}</strong>
                                    @if($calificacionesGrupo->first()->servicio && $calificacionesGrupo->first()->servicio->cliente)
                                        - Cliente: {{ $calificacionesGrupo->first()->servicio->cliente->CliName }}
                                    @endif
                                    <span class="pull-right">Total: {{ $calificacionesGrupo->count() }} calificación(es)</span>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Calificación</th>
                                                <th>Comentario</th>
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Respuesta</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($calificacionesGrupo as $calificacion)
                                            <tr>
                                                <td>
                                                    @if($calificacion->score)
                                                        @if($calificacion->score == 1)
                                                            <span style="font-size: 30px; background-color: #dc3545; border-radius: 50%; padding: 5px 10px; display: inline-block;">😞</span>
                                                            <strong class="label label-danger">Deficiente</strong>
                                                        @elseif($calificacion->score == 2)
                                                            <span style="font-size: 30px; background-color: #ffc107; border-radius: 50%; padding: 5px 10px; display: inline-block;">😐</span>
                                                            <strong class="label label-warning">Regular</strong>
                                                        @elseif($calificacion->score == 3)
                                                            <span style="font-size: 30px; background-color: #28a745; border-radius: 50%; padding: 5px 10px; display: inline-block;">😊</span>
                                                            <strong class="label label-success">Excelente</strong>
                                                        @endif
                                                    @else
                                                        <span class="label label-warning">Pendiente</span>
                                                    @endif
                                                </td>
                                                <td>{{ $calificacion->comment ?? 'Sin comentario' }}</td>
                                                <td>
                                                    @if($calificacion->cliente)
                                                        {{ $calificacion->cliente->name }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $calificacion->completed_at ? $calificacion->completed_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                                <td>
                                                    @if(isset($calificacion->meta['respuesta']))
                                                        <div class="alert alert-info" style="margin: 0; padding: 8px;">
                                                            <strong>Respuesta:</strong><br>
                                                            {{ $calificacion->meta['respuesta'] }}
                                                            @if(isset($calificacion->meta['respondido_at']))
                                                                <br><small>{{ \Carbon\Carbon::parse($calificacion->meta['respondido_at'])->format('d/m/Y H:i') }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <form action="{{ route('calificaciones.responder', $calificacion->ID_Calificacion) }}" method="POST" style="margin: 0;">
                                                            @csrf
                                                            <div class="input-group">
                                                                <input type="text" name="respuesta" class="form-control" 
                                                                    placeholder="Escribe tu respuesta..." required>
                                                                <span class="input-group-btn">
                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="fa fa-reply"></i> Responder
                                                                    </button>
                                                                </span>
                                                            </div>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('calificaciones.show', $calificacion->ID_Calificacion) }}" 
                                                        class="btn btn-info btn-xs">
                                                        <i class="fa fa-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No hay calificaciones completadas para mostrar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.cotizaciones')

@section('htmlheader_title')
    {{ __('adminlte::message.MenuCotizacionesTitle') }}
@endsection

@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #469cfd, #a1ccfc); padding-right:30vw; position:relative; overflow:hidden;">
    {{ __('adminlte::message.MenuCotizacionesTitle') }}
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection

@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">{{ __('adminlte::message.MenuCotizaciones') }}</h3>
                    @if(in_array(Auth::user()->UsRol, Permisos::COTIZACION) || in_array(Auth::user()->UsRol2, Permisos::COTIZACION))
                        <a href="/cotizacion/create" class="btn btn-primary" style="float: right;">{{ __('adminlte::message.create') }}</a>
                    @else
                        <a href="#" disabled class="btn btn-default pull-right" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Crear Cotizacion</b>" data-content="<p style='width: 50%'> Unicamente comercial cuenta con la autorizacion para crear cotizacions">{{ __('adminlte::message.create') }}</a>
                    @endif
                </div>
                <!-- /.box-header -->
                <div class="box box-info">
                    <div class="box-body">
                    @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <table id="cotizacionTable" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Fecha de Cotizacion</th>
                                    <th>{{ __('adminlte::langCotizacion.nit') }}</th>
                                    <th>Razon Social</th>
                                    <th>{{ __('adminlte::message.email') }}</th>
                                    <th>Residuos</th>
                                    <th>Tratamientos</th>
                                    <th>{{ __('Clasificacion 4741') }}</th>
                                    <th>{{ __('Sede') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                    <th>{{ __('Acciones') }}</th> <!-- Nueva columna para acciones -->
                                </tr>
                                </thead>
                                <tbody id="readyTable">
                                    @foreach($cotizaciones as $cotizacion)
                                    <tr>
                                        <td>{{ $cotizacion->id_cotizacion }}</td>
                                        <td>{{ $cotizacion->FechaCotizacion }}</td>
                                        <td>{{ $cotizacion->Nit }}</td>
                                        <td>{{ $cotizacion->Razon_Social }}</td>
                                        <td>{{ $cotizacion->Correo }}</td>

                                        <!-- Columna de residuos -->
                                        <td>
                                            <ul>
                                                @if($cotizacion->coti_respel->isNotEmpty())
                                                @foreach($cotizacion->coti_respel as $residuo)
                                                    <li>{{ $residuo->respel->RespelName ?? 'N/A' }}</li>
                                                @endforeach
                                                @else
                                                    <li>No hay residuos asociados</li>
                                                @endif
                                            </ul>
                                        </td>

                                        <!-- Columna de tratamientos -->
                                        <td>
                                            <ul>
                                                @if($cotizacion->coti_respel->isNotEmpty())
                                                @foreach($cotizacion->coti_respel as $residuo)
                                                    <li>{{ $residuo->tratamiento->TratName ?? 'N/A' }}</li>
                                                @endforeach
                                                @else
                                                    <li>No hay tratamientos asociados</li>
                                                @endif
                                            </ul>
                                        </td>

                                        <!-- Columna de clasificaci??n 4741 -->
                                        <td>
                                            <ul>
                                                @if($cotizacion->coti_respel->isNotEmpty())
                                                    @foreach($cotizacion->coti_respel as $residuo)
                                                        <li>{{ $residuo->clasf4741 ?? 'N/A' }}</li>
                                                    @endforeach
                                                @else
                                                    <li>No hay clasificaciones asociadas</li>
                                                @endif
                                            </ul>
                                        </td>

                                        <!-- Columna de sede -->
                                        <td>{{ $cotizacion->Sede }}</td>

                                        

                                        <!-- Columna de estado -->
                                        <td>{{ $cotizacion->CoStatus }}</td>

                                        <!-- Columna de acciones -->
                                        <td>
                                            <a href="{{ route('cotizacion.edit', $cotizacion->id_cotizacion) }}" class="btn btn-primary btn-sm">Editar</a>
                                            <a href="{{ route('cotizacion.show', $cotizacion->id_cotizacion) }}" class="btn btn-info btn-sm">Ver</a>
                                        </td>
                                    @endforeach
                                </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#cotizacionTable').DataTable();
        });
    </script>
@endsection
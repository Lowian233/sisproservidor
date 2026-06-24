@section('main-content')
	{{-- {{$Km}} --}}
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-12">
				<div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ __('adminlte::message.progvehiclist') }} - <b>Hoy y Mañana</b></h3>
                        @if(in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) || in_array(Auth::user()->UsRol2, Permisos::TODOPROSARC))
                            <a href="/vehicle-programacion/create" class="btn btn-info pull-right"><i class="fas fa-calendar-alt"></i> {{ __('adminlte::message.progvehiccreatetext') }}</a>
                        @endif
                    </div>
                    <div class="box box-info">
                        <div class="box-body">
                            <table id="ProgVehicleTable" class="table table-compact table-bordered table-striped" data-order='[[ 1, "desc"]]'>
                                <thead>
                                    <tr>
                                        <th>{{ __('adminlte::message.progvehicclient') }}</th>
                                        <th>{{ __('adminlte::message.progvehicfech') }}</th>
                                        <th>{{ __('adminlte::message.progvehicvehic') }}</th>
                                        <th>{{ __('adminlte::message.progvehicsalida') }}</th>
                                        <th>{{ __('adminlte::message.progvehicayudan') }}</th>
                                        {{-- @if(Auth::user()->UsRol <> __('adminlte::message.Conductor') || Auth::user()->UsRol2 <> __('adminlte::message.Conductor')) --}}
                                        <th>{{ __('adminlte::message.progvehicconduc') }}</th>
                                        <th>Puntos de recolección</th>
                                        <th>{{ __('adminlte::message.progvehicllegada') }}</th>
                                        <th>{{ __('adminlte::message.progvehictype') }}</th>
                                        <th>Autorización</th>
                                        {{-- @endif --}}
                                        @if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
                                        <th>ver programación</th>
                                        @endif
                                        <th>{{ __('adminlte::message.progvehicservi2') }}</th>
                                        @if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2))
                                        <th>{{ __('adminlte::message.edit') }}</th>
                                        @endif
                                        @if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
                                        <th>{{ __('adminlte::message.progvehicserauth') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="readyTable">
                                    @foreach($programacions as $programacion)
                                    @php
                                        $ayudante = 'No aplica';
                                        $conductor = 'No aplica';
                                        $vehiculoPlaca = 'No aplica';
                                        if($programacion->ProgVehtipo == 1){
                                            foreach($personals as $personal){
                                                if($programacion->FK_ProgAyudante == $personal->ID_Pers){
                                                    $ayudante = $personal->PersFirstName.' '.$personal->PersLastName;
                                                }
                                            }
                                            foreach($personals as $personal){
                                                if($programacion->FK_ProgConductor == $personal->ID_Pers){
                                                    $conductor = $personal->PersFirstName.' '.$personal->PersLastName;
                                                }
                                            }
                                            foreach ($vehiculos as $vehiculo) {
                                                if($programacion->FK_ProgVehiculo == $vehiculo->ID_Vehic){
                                                    $vehiculoPlaca = $vehiculo->VehicPlaca;
                                                }
                                            }
                                        }
                                        elseif($programacion->ProgVehtipo == 2){
                                            foreach($personals as $personal){
                                                if($programacion->FK_ProgAyudante == $personal->ID_Pers){
                                                    $ayudante = $personal->PersFirstName.' '.$personal->PersLastName;
                                                }
                                            }
                                            $conductor = 'No aplica';
                                            foreach ($vehiculos as $vehiculo) {
                                                if($programacion->FK_ProgVehiculo == $vehiculo->ID_Vehic){
                                                    $vehiculoPlaca = $vehiculo->VehicPlaca;
                                                }
                                            }
                                        }
                                        else{
                                            $ayudante = 'No aplica';
                                            $conductor = $programacion->SolSerConductor;
                                            $vehiculoPlaca = $programacion->SolSerVehiculo;
                                        }
                                    @endphp
                                    <tr style="{{$programacion->ProgVehDelete === 1 ? 'color: red' : ''}}">
                                        <td>{{$programacion->CliName}}</td>
                                        <td>{{$programacion->ProgVehFecha}}</td>
                                        <td>{{$vehiculoPlaca}}</td>
                                        <td>{{date('h:i A', strtotime($programacion->ProgVehSalida))}}</td>
                                        <td>{{$ayudante}}</td>
                                        {{-- @if(Auth::user()->UsRol <> __('adminlte::message.Conductor')) --}}
                                            <td>{{$conductor}}</td>
                                            <td><ul class="list-group">
                                                @foreach($programacion->puntosderecoleccion as $Punto)
                                                <li data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Dirección de los Puntos</b>" data-content="<p style='width: 50%'>
                                                    <ul class='list-group'>
                                                        <li class='list-group-item'><b>Generador:</b>{{$Punto->generadors->GenerName}}<br><b>Sede:</b>{{$Punto->GSedeName}}<br><b>Dirección:</b>{{$Punto->GSedeAddress}}<br><b>Cel:</b>{{$Punto->GSedeCelular}}</li>
                                                    </ul>
                                                    <br>Para mas detalles comuníquese con su <b>Jefe de Logistica</b> </p>" class="list-group-item">{{$Punto->GSedeName}}</li>
                                                @endforeach
                                            </ul></td>
                                            <td>{{$programacion->ProgVehEntrada <> null ? date('h:i A', strtotime($programacion->ProgVehEntrada)) : ''}}</td>
                                            <td>{{$programacion->ProgVehtipo == 1 ? 'Interno' : ($programacion->ProgVehtipo == 2 ? 'Alquilado': 'Externo')}}</td>
                                            <td>{{$programacion->ProgVehStatus}}</td>
                                        {{-- @endif --}}
                                        @if(in_array(Auth::user()->UsRol, Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, Permisos::CONDUCTOR))
                                            <td><a method='get' href='/vehicle-programacion/{{$programacion->ID_ProgVeh}}' class='btn btn-info btn-block'><i class="fas fa-search"></i> <b>Datos</b></a></td>
                                        @endif
                                        <td><a href="/solicitud-servicio/{{$programacion->SolSerSlug}}"class='btn btn-info btn-block' title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i> #{{$programacion->ID_SolSer}}</a></td>
                                        @if(in_array(Auth::user()->UsRol, Permisos::ProgVehic2) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic2))
                                            <td><a method='get' href='/vehicle-programacion/{{$programacion->ID_ProgVeh}}/edit' class='btn btn-warning btn-block'><i class="fas fa-edit"></i> <b>{{__('adminlte::message.edit')}}</b></a></td>
                                        @endif
                                        @if(in_array(Auth::user()->UsRol, Permisos::SolSerCertifi) || in_array(Auth::user()->UsRol2, Permisos::SolSerCertifi))
                                        <td><a href="/vehicle-programacion/{{$programacion->ID_ProgVeh}}/updateStatus" class='btn btn-success btn-block' title="{{ 
                                        'adminlte::message.progvehicserauth'}}"><i class="fas fa-sign-out-alt"></i></a></td>
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
	
@endsection
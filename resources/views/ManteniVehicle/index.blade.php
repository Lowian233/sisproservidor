@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.mantvehititle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.vehicletitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.mantvehititlelist') }}</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1))
					<a href="/vehicle-programacion/create" class="btn btn-info pull-right"><i class="fas fa-calendar-alt"></i> {{ __('adminlte::message.progvehiccreatetext') }}</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="MantVehicleTable" class="table table-compact table-bordered table-striped"  data-order='[[ 6, "desc"]]'>
							<thead>
								<tr>
									<th>{{ __('adminlte::message.mantvehivehic') }}</th>
									{{-- <th>{{ __('adminlte::message.mantvehikm') }}</th> --}}
									<th>{{ __('adminlte::message.mantvehistatus') }}</th>
									<th>{{ __('adminlte::message.mantvehitype') }}</th>
									<th>{{ __('adminlte::message.mantvehiinicio1') }}</th>
									<th>{{ __('adminlte::message.mantvehiinicio') }}</th>
									<th>{{ __('adminlte::message.mantvehifin1') }}</th>
									<th>{{ __('adminlte::message.mantvehifin') }}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1))
									<th>{{ __('adminlte::message.edit') }}</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach ($MantVehicles as $MantVehicle)
								<tr style="{{$MantVehicle->MvDelete === 1 ? 'color: red' : ''}}">
									<td>{{$MantVehicle->VehicPlaca}}</td>
									{{-- <td>{{$MantVehicle->MvKm}}</td> --}}
									@if($MantVehicle->HoraMavFin >= now())
									<td>{{ __('adminlte::message.mantvehistatustrue') }}</td>
									@else
									<td>{{ __('adminlte::message.mantvehistatusfalse') }}</td>
									@endif
									<td>{{$MantVehicle->MvType}}</td>
									<td>{{date('Y/m/d', strtotime($MantVehicle->HoraMavInicio))}}</td>
									<td>{{date('h:i A', strtotime($MantVehicle->HoraMavInicio))}}</td>
									<td>{{date('Y/m/d', strtotime($MantVehicle->HoraMavFin))}}</td>
									<td>{{date('h:i A', strtotime($MantVehicle->HoraMavFin))}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::ProgVehic1) || in_array(Auth::user()->UsRol2, Permisos::ProgVehic1))
									<td><a href='/vehicle-mantenimiento/{{$MantVehicle->ID_Mv}}/edit' class='btn btn-block btn-warning'><i class="fas fa-edit"></i> <b>{{__('adminlte::message.edit')}}</b></a></td>
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
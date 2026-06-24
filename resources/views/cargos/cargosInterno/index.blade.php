@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.cargotitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.cargotitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{__('adminlte::message.listcargo')}}</h3>
					@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
					<a href="/cargosInterno/create" class="btn btn-primary pull-right">{{__('adminlte::message.create')}}</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="CargosTable" class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									<th>{{__('adminlte::message.cargoname')}}</th>
									<th>{{__('adminlte::message.areaname')}}</th>
									<th>{{__('adminlte::message.cargograde')}}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
									<th>{{__('adminlte::message.edit')}}</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($Cargos as $Cargo)
								<tr style="{{$Cargo->CargDelete === 1 ? 'color: red' : ''}}">
									<td>{{$Cargo->CargName}}</td>
									<td>{{$Cargo->AreaName}}</td>
									<td>{{$Cargo->CargGrade}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::PersInter1) || in_array(Auth::user()->UsRol2, Permisos::PersInter1))
									<td><a href='/cargosInterno/{{$Cargo->CargSlug}}/edit' class='btn btn-warning btn-block'><i class="fas fa-edit"></i> <b>{{__('adminlte::message.edit')}}</b></a></td>
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

@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.contracttitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.contracttitle') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{__('adminlte::message.contractindex')}} </h3>
					@if(in_array(Auth::user()->UsRol, Permisos::CONTRATOSCRUD) || in_array(Auth::user()->UsRol2, Permisos::CONTRATOSCRUD))
					<a href="/contratos/create" class="btn btn-primary pull-right">{{__('adminlte::message.create')}}</a>
					@endif
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="ContratosTable" class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									<th>{{__('adminlte::message.contractclien')}}</th>
									<th>{{__('adminlte::message.contractpdf')}}</th>
									<th>{{__('adminlte::message.contractvigencia')}}</th>
									<th>{{__('adminlte::message.contractvigencia2')}}</th>
									@if(in_array(Auth::user()->UsRol, Permisos::CONTRATOSCRUD) || in_array(Auth::user()->UsRol2, Permisos::CONTRATOSCRUD))
									<th>{{__('adminlte::message.edit')}}</th>
									@endif
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($Contratos as $Contrato)
								<tr style="{{$Contrato->ContraDelete === 1 ? 'color: red' : ''}}">
									<td>{{$Contrato->CliShortname}}</td>
									<td style="text-align: center;"><a href="/img/Contratos/{{$Contrato->ContraPdf}}" class="btn btn-info"> <i class="fas fa-file-pdf fa-lg"></i> </a></td>
									<td style="text-align: center;">{{$Contrato->ContraVigencia}}</td>
									<td>{{$Contrato->ContraVigencia < now() ? 'Vencida' : ($Contrato->ContraNotifiVigencia <= now() ? 'Pronto a Vencer' : 'Vigente')}}</td>
									@if(in_array(Auth::user()->UsRol, Permisos::CONTRATOSCRUD) || in_array(Auth::user()->UsRol2, Permisos::CONTRATOSCRUD))
									<td><a href='/contratos/{{$Contrato->ContraSlug}}/edit' class='btn btn-warning btn-block'><i class="fas fa-edit"></i> <b>{{__('adminlte::message.edit')}}</b></a></td>
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

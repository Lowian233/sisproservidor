@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.personalhtmlheader_title') }}
@endsection
@section('contentheader_title')
@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE))
<span style="background-image: linear-gradient(40deg, #FFFFFF, #A3A2AE); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.personalhtmlheader_title') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@else
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.personalhtmlheader_title') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endif
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ __('adminlte::message.personaltitlelist') }}</h3>
					

					@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR))
						<a href="personal/create" class="btn btn-primary pull-right">Crear Personal</a>
					@endif

				@php
                    $admins = collect($IdPersonaAdmin ?? [])->pluck('ID_Pers'); // lista de ID_Pers admins
                    $isCliente = in_array(Auth::user()->UsRol, Permisos::CLIENTE); // o el arreglo correcto
                    $esAdminCliente = $admins->contains(Auth::user()->FK_UserPers);
                @endphp
                
                @if($isCliente && $esAdminCliente)
                    <a href="{{ url('UsuariosCliente') }}" class="btn btn-info pull-right" style="margin-right: 1em;">
                        Lista de Usuarios
                    </a>
                @endif

					
				</div>
				<div class="box box-info">
					<div class="box-body">
						<table id="PersonalsTable" class="table table-compact table-bordered table-striped">
							<thead>
								<tr>
									<th>{{ __('adminlte::message.persdocument') }}</th>
									<th>{{ __('adminlte::message.persname') }}</th>
									<th>{{ __('adminlte::message.emailaddress') }}</th>
									<th>{{ __('adminlte::message.mobile') }}</th>
									<th>{{ __('adminlte::message.cargoname') }}</th>
									<th>{{ __('adminlte::message.areaname') }}</th>
									@if(Auth::user()->UsRol <> __('adminlte::message.Cliente'))
										<th>{{ __('adminlte::message.clientmenu') }}</th>
									@endif
									{{-- <th>Contacto de facturación</th> --}}
									<th>{{ __('adminlte::message.see') }}</th>
								</tr>
							</thead>
							<tbody id="readyTable">
								@foreach($Personals as $Personal)
								<tr style="{{$Personal->PersDelete === 1 ? 'color: red' : ''}}">
									<td>{{$Personal->PersDocType." ".$Personal->PersDocNumber}}</td>
									<td>{{$Personal->PersFirstName." ".$Personal->PersSecondName." ".$Personal->PersLastName}}</td>
									<td>{{$Personal->PersEmail}}</td>
									<td>{{$Personal->PersCellphone}}</td>
									<td>{{$Personal->CargName}}</td>
									<td>{{$Personal->AreaName}}</td>
									@if(Auth::user()->UsRol <> __('adminlte::message.Cliente'))
										<td>{{$Personal->CliName}}</td>
									@endif
									{{-- <td>{{$Personal->PersFactura==1 ? "Si" : "No"}}</td> --}}
									<td><a method='get' href='/personal/{{$Personal->PersSlug}}' class='btn btn-info btn-block' title="{{ __('adminlte::message.seemoredetails')}}"><i class="fas fa-search"></i></a></td>
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
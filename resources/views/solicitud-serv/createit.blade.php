@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.solsertitle') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	Servicios-Solicitudes
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::message.solsertitlecreate') }}</h3>
				</div>
				<div class="box box-info">
					<!-- form start -->
					<form role="form" action="/solicitud-servicio/create" method="POST" enctype="multipart/form-data" data-toggle="validator">
						@csrf
						@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
										<p>{{$error}}</p>
									@endforeach
								</ul>
							</div>
						@endif
						<div class="box-body" id="readyTable">
							<div class="tab-pane" id="addRowWizz">
								<div>
                                    <form role="form" action="{{ route('solicitud-servicio.create') }}" method="GET" enctype="multipart/form-data" data-toggle="validator">
                                        @csrf
                                        <div>
                                            <div id="step-1" class="tab-pane step-content">
                                                    
                                                            <label for="Cliente">{{ __('Cliente') }}</label>
                                                            <small class="help-block with-errors">*</small>
                                                            <select name="ID_Cli" id="Cliente" class="form-control" required>
                                                                <option value="">{{ __('adminlte::LangRespel.selecthem') }}</option>
                                                                @foreach($ID_Cli as $cliente)
                                                                    <option value="{{ $cliente->ID_Cli }}" {{ (isset($clientePreseleccionado) && $clientePreseleccionado == $cliente->ID_Cli) ? 'selected' : '' }}>
                                                                        {{ $cliente->CliName }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                        <div class="text-center">
                                                            <button type="submit" href="/solicitud-serv/create"  class="btn btn-info" style="margin: 10px 30px;" id="btn-generar">{{ __('Enviar') }}</button>
                                                        </div>
                                                    
                                            </div>
                                        </div>
                                    </form>
                                    
								</div>
							</div>						
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
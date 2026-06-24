@extends('layouts.app')
@section('htmlheader_title')

@endsection
@section('contentheader_title')
@if(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::CLIENTE))
<span style="background-image: linear-gradient(40deg, rgb(69, 202, 252), rgb(48, 63, 159)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@else
<span style="background-image: linear-gradient(40deg, rgb(255, 216, 111), rgb(252, 98, 98)); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::message.gener') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endif
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::message.Generregistertittle') }}</h3>
				</div>
				<div class="box box-info">
					<!-- form start -->
					<form role="form" action="/generadores/create" method="POST" enctype="multipart/form-data" data-toggle="validator">
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

                                    <form role="form" action="{{ route('generadores.create') }}" method="POST" enctype="multipart/form-data" data-toggle="validator">
                                        @csrf
                                        <div>
                                            <div id="step-1" class="tab-pane step-content">
                                                    
                                                            <label for="Cliente">{{ __('Cliente') }}</label>
                                                            <small class="help-block with-errors">*</small>
                                                            <select name="ID_Cli" id="Cliente" class="form-control" required>
                                                                <option value="">{{ __('adminlte::LangRespel.selecthem') }}</option>
                                                                @foreach($ID_Clit as $cliente)
                                                                    <option value="{{ $cliente->ID_Cli }}">{{ $cliente->CliName }}</option>
                                                                @endforeach
                                                            </select>

                                                        <div class="text-center">
                                                            <button type="submit" href="/generadores/create"  class="btn btn-info" style="margin: 10px 30px;" id="btn-generar">{{ __('Enviar') }}</button>
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

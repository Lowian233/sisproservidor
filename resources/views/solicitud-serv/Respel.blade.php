@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::LangRespel.Respelcreate') }}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	{{ __('adminlte::LangRespel.Respelcreate') }}
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<!-- Default box -->
			<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">{{ __('adminlte::LangRespel.Respelcreate') }}</h3>
				</div>
					<div class="box box-info">
						<form role="form" action="{{ route('respela')}}" method="POST" id="myform" enctype="multipart/form-data" data-toggle="validator" >
							@csrf
							@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
									<li>{{$error}}</li>
									@endforeach
								</ul>
							</div>
							@endif
                            <div class="box-body">
                                @if(in_array(Auth::user()->UsRol, Permisos::PROGRAMADOR) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR))
                                <div class="col-md-12 form-group">
                                    <label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" data-delay='{"show": 500}' title="Seleccione la sede">
                                       Seleccione la sede
                                    </label>
                                    <small class="help-block with-errors">*</small>
                                    <select id="ControlSelect0" name="Cliente[]" class="form-control" required> 
                                        <option value="">{{ __('adminlte::LangRespel.selecthem') }}</option>
                                        @foreach($clientes as $Cliente)
												<option>{{$Cliente->CliName}}</option>
                                        @endforeach
                                    </select>                        
                                </div>
								@elseif(in_array(Auth::user()->UsRol, Permisos::CLIENTE) || in_array(Auth::user()->UsRol2, Permisos::PROGRAMADOR))
									<input type="text" name="Sede" style="display: none;" value="{{$Sede}}">
								@endif
								
								@include('layouts.RespelPartials.respelform1')
                                    
                            
							</div>
							<!-- /.box-body -->
							<div class="box box-info">
								<div class="box-footer">
									{{-- <a onclick="AgregarRes()" class="btn btn-primary"><i class="fa fa-plus"></i>{{ __('adminlte::LangRespel.addrespelButton') }}</a>	 --}}
									<button type="submit" class="btn btn-success pull-right">Registar</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
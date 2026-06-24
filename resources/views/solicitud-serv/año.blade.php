@extends('layouts.app')
@section('htmlheader_title')
Lista de Servicios
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Servicios por año
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
				<div class="box-header with-border">
					@if(in_array(Auth::user()->UsRol, Permisos::COMERCIALEINGRURNO) || in_array(Auth::user()->UsRol2, Permisos::COMERCIALEINGRURNO))
						<a href="{{ route('solicitudprueba') }}" class="btn btn-success pull-right">
							<i class="fas fa-plus"></i> Crear Solicitud
						</a>
					@endif
				</div>
                    <div>
                        <center>
                        <h3>Seleccione el año en el que se encuentra el servicio
                        </h3>
                        </center>
                    </div>
					<div class="box-body">
						<div id="ModalStatus"></div>
                        <center>
                        <a href="{{ route('solicitud-serv.2020')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2020</a>
                        <a href="{{ route('solicitud-serv.2021')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2021</a>
                        <a href="{{ route('solicitud-serv.2022')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2022</a>
                        <a href="{{ route('solicitud-serv.2023')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2023</a>
						<a href="{{ route('solicitud-serv.2024')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2024</a>
						<a href="{{ route('solicitud-serv.2025')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2025</a>
						<a href="{{ route('solicitud-serv.2026')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2026</a>
					    </center>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
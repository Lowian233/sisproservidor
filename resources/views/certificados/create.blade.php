@extends('layouts.app')
@section('htmlheader_title')
Lista de Certificados
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Certificados por año
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border">
					
					</div>
                    <div>
                        <center>
                        <h3>Seleccione el año en el que se encuentra el certificado
                        </h3>
                        </center>
                    </div>
					<div class="box-body">
						<div id="ModalStatus"></div>
                        <center>
                        <a href="{{ route('certificados.2020')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2020</a>
                        <a href="{{ route('certificados.2021')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2021</a>
                        <a href="{{ route('certificados.2022')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2022</a>
                        <a href="{{ route('certificados.2023')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2023</a>
						<a href="{{ route('certificados.2024')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2024</a>
						<a href="{{ route('certificados.2025')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2025</a>
						<a href="{{ route('certificados.2026')}}" class="btn btn-primary btn-lg btn-block" style="float: right;">2026</a>
					    </center>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection


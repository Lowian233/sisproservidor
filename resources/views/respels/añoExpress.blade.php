@extends('layouts.app')
@section('htmlheader_title')
Lista de Residuos Express
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #d4fc79, #00C851); padding-right:30vw; position:relative; overflow:hidden;">
	Residuos Express por año
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
                        <h3>Seleccione el año en el que se encuentra el residuo Express
                        </h3>
                        </center>
                    </div>
					<div class="box-body">
						<div id="ModalStatus"></div>
                        <center>
                        <a href="{{ route('respelsexpress.2020')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2020</a>
						<a href="{{ route('respelsexpress.2021')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2021</a>
						<a href="{{ route('respelsexpress.2022')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2022</a>
						<a href="{{ route('respelsexpress.2023')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2023</a>
						<a href="{{ route('respelsexpress.2024')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2024</a>
						<a href="{{ route('respelsexpress.2025')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2025</a>
						<a href="{{ route('respelsexpress.2026')}}" class="btn btn-success btn-lg btn-block" style="float: right; margin: 5px;">2026</a>
					    </center>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
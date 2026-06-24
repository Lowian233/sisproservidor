@extends('layouts.app')
@section('htmlheader_title')
Recibos de Material
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #F1B378, #D66841); padding-right:30vw; position:relative; overflow:hidden;">
	Recibos de Material por a&ntilde;o
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<div class="box">
					<div class="box-header with-border"></div>
                    <div>
                        <center>
                        <h3>Seleccione el a&ntilde;o de recepci&oacute;n del recibo</h3>
                        </center>
                    </div>
					<div class="box-body">
						<center>
							@foreach($years as $y)
								<a href="{{ route('recibomaterial.year', $y) }}" class="btn btn-primary btn-lg btn-block" style="float: right;">{{ $y }}</a>
							@endforeach
					    </center>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@extends('layouts.app')
@section('htmlheader_title')
{{ __('adminlte::message.MenuCotizacionesTitle') }}
@endsection
@section('htmlheader_title')
Cotizacion N° {{$cotizacion->ID}}
@endsection
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg,rgb(194, 240, 251), #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	Cotizaciones
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
<div class="container-fluid spark-screen">
	@component('layouts.partials.modal')
		@slot('slug')
			{{$cotizacion->id_cotizacion}}
		@endslot
		@slot('textModal')
			la cotizacion <b>N° {{$cotizacion->id_cotizacion}}</b>
		@endslot
	@endcomponent
	<div class="row">
		<div class="col-md-16 col-md-offset-0">
			<div class="box">
				<div class="box-header with-border">
					<form action='/cotizacion/{{$cotizacion->ID}}' method='POST'>
						@method('DELETE')
						@csrf
						<input type="submit" id="Eliminar{{$cotizacion->ID}}" style="display: none;">
					</form>
					<div class="col-md-12" id="titulo" style="font-size: 1.2em; text-align:center;">
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 ">
						@if ($errors->any())
							<div class="alert alert-danger" role="alert">
								<ul>
									@foreach ($errors->all() as $error)
										<p>{{$error}}</p>
									@endforeach
								</ul>
							</div>
						@endif
						<div class="box box-info">
						<div class="container">
						<h1>Detalle de Cotización N° {{$cotizacion->id_cotizacion}}</h1>
					    <div style="text-align: right; margin-bottom: 20px;">
						<button type="button" class="btn btn-primary" onclick= window.location="{{ route('cotizacion.edit', $cotizacion->id_cotizacion) }}">
                        <i class="fas f-edit"></i> Editar Cotización
                        </button>
						</div>

						@if(in_array(Auth::user()->UsRol, ['Comercial','Comercialap','Ejecutivo Comercial']) || in_array(Auth::user()->UsRol2, ['Comercial','Comercialap','Ejecutivo Comercial']))
						<div class="panel panel-default" style="margin-bottom: 20px;">
							<div class="panel-heading">
								<strong>Mi firma (para PDF de cotización)</strong>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-md-4">
										<img
											src="{{ asset('img/' . Auth::user()->id . '.png') }}"
											onerror="this.onerror=null;this.src='{{ asset('img/5.png') }}';"
											alt="Firma"
											style="max-width: 100%; max-height: 140px; background: #fff; border: 1px solid #eee; padding: 8px;"
										/>
										<p style="margin-top: 8px; color: #777; font-size: 12px;">
											Sube una imagen <strong>PNG</strong> (ideal con fondo transparente).
										</p>
									</div>
									<div class="col-md-8">
										<form action="{{ route('cotizacion.firma') }}" method="POST" enctype="multipart/form-data">
											@csrf
											<div class="form-group">
												<label>Actualizar firma</label>
												<input type="file" name="firma" class="form-control" accept="image/png">
											</div>
											<button type="submit" class="btn btn-info">
												<i class="fa fa-upload"></i> Guardar nueva firma
											</button>
										</form>
									</div>
								</div>
							</div>
						</div>
						@endif
						<div class="row">
							<div class="col-md-3">
								<label>Fecha de Cotización:</label>
								<span>{{$cotizacion->FechaCotizacion->format('Y-m-d') }}</span>
							</div>
							<div class="col-md-3">
								<label>NIT:</label>
								<span>{{ $cotizacion->Nit }}</span>
							</div>
							<div class="col-md-3">
								<label>Cliente:</label>
								<span>{{ $cotizacion->Razon_Social }}</span>
							</div>							
							<div>
								<div class="col-md-3">
								<label>Sede:</label>
								<span>{{ $cotizacion->sede }}</span>
							</div>
							<div>
								<div class="col-md-3">
									<label>Correo:</label>
									<span>{{ $cotizacion->Correo }}</span>
								</div>
							</div>	
							<div class="col-md-3">
								<label>Telefono:</label>
								<span>{{ $cotizacion->Telefono }}</span>
							</div>
							<div class="col-md-3">
									<label>Direccion:</label>
									<span>{{ $cotizacion->Direccion }}</span>
							</div>
							
							<div class="col-md-3">
								<label>Frecuencia de Recoleccion:</label>
								<span>{{ $cotizacion->frecuencia_recoleccion }}</span>
							</div>
							<div class="col-md-3">
                              <label>Aprobacion del cliente:</label>
							  <span>{{ $cotizacion->CoStatus }}</span>
							</div>
							<div class="col-md-3">
								<label>Estado:</label>
								<span>{{ $cotizacion->Status }}</span>
							</div>
							<div class="col-md-3">
								<label>Tipo:</label>
								<span>{{ $cotizacion->tipo_cotizacion}}</span>
							</div>
							<div class="col-md-3">
								<label>Observaciones:</label>
								<span>{{ $cotizacion->Observaciones }}</span>
							</div>	
						</div>
						<hr>
						<hr>
						<hr>
						<hr>
						<hr>
    
    <div class="panel panel-primary">
        <div class="panel-heading">Residuos Cotizados</div>
        <div class="panel-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Residuo</th>
						<th>Peligrosidad</th>
						<th>Corriente</th>
                        <th>Tratamiento</th>
                        <th>Cantidad (kg)</th>
                        <th>Precio/kg</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cotizacion->coti_respel as $residuo)
                    <tr>
                        <td>{{ $residuo->respel->RespelName}}</td>
						<td>{{ $residuo->peligrosidad}}</td>
						<td>{{ $residuo->clasf4741}}</td>						
                        <td>{{ $residuo->tratamiento->TratName }}</td>
                        <td>{{ number_format($residuo->cantidad_kilos,1,'.',) }}</td>
                        <td>${{ number_format($residuo->precio_kg, 0, ',', '.') }}</td>
                        <td>${{ number_format($residuo->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
					<tr>
						<th colspan="6" style="text-align:right;">Transporte</th>
						<th>${{ number_format($cotizacion->Transporte, 0, ',', '.') }}</th>
					</tr>
                    <tr>
                        <th colspan="6" style="text-align:right;">Total:</th>
                        <th>${{ number_format($cotizacion->Total, 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
	<div>
	 <div>
			<!-- Botón para abrir el modal -->
			@if(in_array(Auth::user()->UsRol,['Programador','AdministradorBogota']))
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#aprobarModal">
				Aprobar Cotización 
			</button>
			@endif
			
			<a href="{{ route('cotizacion.pdf', $cotizacion->id_cotizacion) }}" target="_blank" class="btn btn-success btn-sm">Vista previa PDF</a>
			
	 </div>
	 <div style="text-align: right; margin-bottom: 20px;margin-top: -36px;">
	    @if($cotizacion->Status == 'Aprobado')
	       <a href="{{asset('storage/cotizacion/'.$cotizacion->id_cotizacion.'.pdf')}}" class="btn btn-success btn-md3">Descargar PDF</a>
	     @else
            <button class="btn btn-secondary btn-sm" disabled>PDF no disponible</button>
         @endif   
	 </div>
	</div>
    <!-- Aquí podrías incluir más secciones, formularios o modales -->
	 <!-- Modal -->
<div class="modal fade" id="aprobarModal" tabindex="-1" role="dialog" aria-labelledby="aprobarModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('cotizacion.aprobar', $cotizacion->id_cotizacion) }}" method="POST">
        @csrf
        @method('PUT')
		<input type="hidden" name="status" value="Aprobado">
        <div class="modal-header">
          <h5 class="modal-title" id="aprobarModalLabel">Aprobar Cotización #{{ $cotizacion->id_cotizacion }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ¿Está seguro de aprobar esta cotización?
          <input type="hidden" name="status" value="Aprobado">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Aprobar</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
<script>
	$(document).ready(function() {
		$('#aprobarModal').on('show.bs.modal', function(event) {
			var button = $(event.relatedTarget);
			var modal = $(this);
			modal.find('form').attr('action', button.data('action'));
		});
	});

</script>
@endsection
@extends('layouts.appReportes')
@section('htmlheader_title','Reportes')
{{-- @endsection --}}
@section('contentheader_title', '')
{{-- @endsection --}}
@section('main-content')
<div class="container-fluid spark-screen">
    <div class="row">
        <div class="col-md-16 col-md-offset-0">
            <div class="box">
                <div class="box-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <h1><center>Reporte Servicios Express</center></h1>
                                <form action="/reportes/express" method="POST">
                                    @csrf
                                    
                                    <div class="form-group col-md-12">
                                        <label color: black; text-align: left;" >Fecha de inicio</label>
                                        <input required type="text" name="Fecha_Inicio" class="form-control col-xs-12 datepicker" placeholder="DD/MM/YYYY" readonly>
                                    </div>
                                     
                                    <div class="form-group col-md-12">
                                        <label color: black; text-align: left;" >Fecha Final</label>
                                        <input required type="text" name="Fecha_Fin" class="form-control col-xs-12 datepicker" placeholder="DD/MM/YYYY" readonly>
                                    </div>
                                        <button type="submit" href="/reportes/express" class="btn btn-info" style="margin: 10px 30px;" id="btn-buscar">Generar</button>
                                </form> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

@section('scripts')
<!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>

<script>
    $(document).ready(function() {
        // Configurar datepicker con formato DD/MM/YYYY
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom auto'
        });
    });
</script>
@endsection
@endsection


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
                                <h3 class="box-title">reporte de cantidades</h3>
                                <a href="{{ route('reportes.ReporteExpress') }}" class="btn btn-success pull-right" style="margin-left: 5px;">
                                    <i class="fas fa-plus"></i> Nueva Consulta
                                </a>
                                <button class="btn btn-primary pull-right" type="button" data-toggle="collapse" data-target=".panels" aria-expanded="false" aria-controls="collapseExample">
                                    <div class="text-nowrap bd-highlight">
                                        <i class="fas fa-filter"></i> Segmentacion
                                    </div>
                                </button>
                                <button  style="margin-right: 5px; color:#3c8dbc;" class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target=".filters" aria-expanded="false" aria-controls="collapseExample">
                                    <div class="text-nowrap bd-highlight">
                                        <i class="fas fa-filter"></i> Filtros
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table id="reporteTable" class=" table-compact table-bordered">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th># Solicitud</th>
                                <th>Fecha de Recepción</th>
                                <th>Cliente</th>
                                <th>Tipo Cliente</th>
                                <th>NIT Cliente</th>
                                <th>Generador</th>
                                <th>NIT Generador</th>
                                <th>Telefono</th>
                                <th>Dirección de servicio</th>
                                <th>Municipio</th>
                                <th>Localidad</th>
                                <th>Nombre de residuo</th>
                                <th>Estado</th>
                                <th>Corriente</th>
                                <th>Peligrosidad</th>
                                <th>Tratamiento</th>
                                <th>Gestor</th>
                                <th>Cantidad Declarada</th>
                                <th>Cantidad Recibida</th>
                                <th>Cantidad Conciliada</th>
                                <th>No. Certificado</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="readyTable">
                            @foreach ($servicios as $servicio)
                                @foreach ($servicio->SolicitudResiduo as $solres)
                                    <tr>
                                        <td>
                                            @if (isset($solres->SolResRM)&&is_array($solres->SolResRM))
                                                @foreach ($solres->SolResRM as $rm)
                                                    {{$rm}} <br>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>{{$servicio->ID_SolSer}}</td>
                                        <td>
                                        {{date('Y/m/d', strtotime($servicio->ProgVehSalida))}}
                                        </td>
                                        <td>{{$servicio->cliente->CliName}}</td>
                                        <td>
                                            @if($servicio->cliente->CliCategoria == 'Cliente')
                                                <span class="label label-primary">Regular</span>
                                            @elseif($servicio->cliente->CliCategoria == 'ClientePrepago')
                                                <span class="label label-warning">Express</span>
                                            @else
                                                <span class="label label-default">{{$servicio->cliente->CliCategoria ?? 'N/A'}}</span>
                                            @endif
                                        </td>
                                        <td>{{$servicio->cliente->CliNit}}</td>
                                        <td>{{$solres->generespel->gener_sedes->generadors->GenerName}} <br> ({{$solres->generespel->gener_sedes->GSedeName}})</td>
                                        <td>{{$solres->generespel->gener_sedes->generadors->GenerNit}}</td>
                                        <td>{{$solres->generespel->gener_sedes->GSedeCelular}}</td>
                                        <td>{{$solres->generespel->gener_sedes->GSedeAddress}}</td>
                                        <td>{{$solres->generespel->gener_sedes->municipio->MunName}}</td>
                                        <td>{{$solres->generespel->gener_sedes->GSedeMapLocalidad ?? 'N/A'}}</td>
                                        <td>{{$solres->generespel->respels->RespelName}}</td>                                        
                                        <td>{{$solres->generespel->respels->RespelEstado}}</td>
                                        <td>
                                            @if($solres->generespel->respels->YRespelClasf4741 <> null)
                                                {{$solres->generespel->respels->YRespelClasf4741}}
                                            @elseif($solres->generespel->respels->ARespelClasf4741 <> null)
                                                {{$solres->generespel->respels->ARespelClasf4741}}
                                            @else()
                                                {{'N/A'}}
                                            @endif
                                        </td>
                                        <td>{{$solres->generespel->respels->RespelIgrosidad}}</td>
                                        <td>{{$solres->requerimiento->tratamiento->TratName}}</td>
                                        <td>{{$solres->requerimiento->tratamiento->gestor->clientes->CliShortname}}</td>
                                        <td>{{$solres->SolResKgEnviado}}</td>
                                        <td>{{$solres->SolResKgRecibido}}</td>
                                        <td>{{$solres->SolResKgConciliado}}</td>
                                        @if ($solres->certdatoexpress)
                                        @if($solres->certdatoexpress->certificado->CertType == 0)
                                        <td>{{$solres->certdatoexpress->certificado->ID_Cert}}</td>
                                        @else
                                        <td>M{{$solres->certdatoexpress->certificado->CertManifNumero}}</td>
                                        @endif
                                    @else
                                        <td>Certificado no encontrado</td>
                                    @endif
                                        <td>{{$servicio->SolSerStatus}}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.24/af-2.3.5/b-1.7.0/b-colvis-1.7.0/b-html5-1.7.0/b-print-1.7.0/cr-1.5.3/date-1.0.3/kt-2.6.1/rr-1.2.7/sc-2.0.3/sb-1.0.1/sp-1.2.2/sl-1.3.3/datatables.min.js"></script>
<script src="{{asset('//cdn.datatables.net/plug-ins/1.10.24/api/sum().js')}}"></script>
<script>
    @switch(Auth::user()->UsRol)
        @case('Programador')
        @case('AdministradorBogota')
        @case('AdministradorPlanta')
        @case('AsistenteComercial')
        @case('JefeOperaciones')
        @case('Supervisor')
        @case('Tesorería')
        @case('AsistenteLogistica')
        @case('JefeLogistica')
            $(document).ready(function() {
                /*var rol defino el rol del usuario*/
                var rol = "<?php echo Auth::user()->fk_rol; ?>";
                /*var botoncito define los botones que se usaran si el usuario es programador*/
                var botoncito = (rol == 1) ? [{extend: 'colvis', text: 'Columnas'}, {extend: 'copy', text: 'Copiar'}, {extend: 'excel', text: 'Excel'}, {extend: 'pdf', text: 'Pdf'}, {extend: 'collection', text: 'Selector', buttons: ['selectRows', 'selectCells']}] : [{extend: 'colvis', text: 'Columnas'}, {extend: 'excel', text: 'Excel'}];
                /*inicializacion de datatable general*/
                $('#reporteTable').DataTable({
                    "dom":"<'row'<'col-md-12 collapse panels'P><'col-md-12 collapse filters'<'card'<'card-body'Q>>>>" +
                        "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
                        "<'row'<'col-md-12'<'pre-x-scrollable'rt>>>" +
                        "<'row justify-content-center justify-content-md-between'<'col-md-12'<'align-self-center'i><''p>>>",
                    "searchPanes": {
                        cascadePanes: true,
                        layout: 'columns-4',
                        columns: [1,2,4,5,6,7,8,9,11,12,14,21],
                        count: '{total}',
                        countFiltered: '{shown} / {total}',
                        viewTotal: true,
                        dtOpts: {
                            select: {
                                style: 'multi'
                            }
                        }
                    },
                    "scrollX": false,
                    "serverSide": false,
                    "autoWidth": true,
                    "select": true,
                    "colReorder": true,
                    "ordering": true,
                    "order": [0, 'desc'],
                    "searchHighlight": true,
                    "responsive": false,
                    "keys": true,
                    "lengthChange": true,
                    "searching": true,
                    "buttons": [
                        botoncito
                    ],
                    "language": {
                        "sProcessing":     "Procesando...",
                        "sLengthMenu":     "_MENU_ Filas",
                        "sZeroRecords":    "No se encontraron resultados",
                        "sEmptyTable":     "Ningún dato disponible en esta tabla",
                        "sInfo":           "_START_ al _END_ de _TOTAL_",
                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered":   "",
                        "sInfoPostFix":    "",
                        "sSearch":         "_INPUT_",
                        "sUrl":            "",
                        "sInfoThousands":  ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst":    "Primero",
                            "sLast":     "Último",
                            "sNext":     "->",
                            "sPrevious": "<-"
                        },
                        "oAria": {
                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        },
                        "colvis": 'Columnas Visibles'
                    },
                    "columnDefs": [
                        { "type": "num-fmt", "targets": [0,3,4,10,12,13]},
                        { "type": "date", "targets": [2]},
                        { "type": "html", "targets": '_all'},
                        { "orderable": false, "targets": [4] },
                        // { "className": "text-right", "targets": [2,3,5]},
                        // { "className": "text-left", "targets": [1]},
                        // { "visible": false, "targets": [4]}
                    ],
                    "drawCallback": function () {
                        var api = this.api();
                        $( api.table().footer() ).html(
                            `<th  scope="col" colspan="11" class="text-right pr-3">`+formattermoney.format(api.column( 10, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" colspan="2" class="text-right pr-3">`+formatternumber.format(api.column( 12, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" class="text-right pr-3">`+formatternumber.format(api.column( 13, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" colspan="3"></th>`
                        );
                        // $('.dataTables_scrollFoot').empty();
                    }
                });
            });
        @break
        @case('Comercial')
           $(document).ready(function() {
                /*var rol defino el rol del usuario*/
                var rol = "<?php echo Auth::user()->fk_rol; ?>";
                /*var botoncito define los botones que se usaran si el usuario es programador*/
                var botoncito = (rol == 1) ? [{extend: 'colvis', text: 'Columnas'}, {extend: 'copy', text: 'Copiar'}, {extend: 'excel', text: 'Excel'}, {extend: 'pdf', text: 'Pdf'}, {extend: 'collection', text: 'Selector', buttons: ['selectRows', 'selectCells']}] : [{extend: 'colvis', text: 'Columnas'}, {extend: 'excel', text: 'Excel'}];
                /*inicializacion de datatable general*/
                $('#reporteTable').DataTable({
                    "dom":"<'row'<'col-md-12 collapse panels'P><'col-md-12 collapse filters'<'card'<'card-body'Q>>>>" +
                        "<'row'<'col-md-3'l><'col-md-5'B><'col-md-4'f>>" +
                        "<'row'<'col-md-12'<'pre-x-scrollable'rt>>>" +
                        "<'row justify-content-center justify-content-md-between'<'col-md-12'<'align-self-center'i><''p>>>",
                    "searchPanes": {
                        cascadePanes: true,
                        layout: 'columns-4',
                        columns: [1,2,4,5,6,7,8,9,11,12,14,21],
                        count: '{total}',
                        countFiltered: '{shown} / {total}',
                        viewTotal: true,
                        dtOpts: {
                            select: {
                                style: 'multi'
                            }
                        }
                    },
                    "scrollX": false,
                    "serverSide": false,
                    "autoWidth": true,
                    "select": true,
                    "colReorder": true,
                    "ordering": true,
                    "order": [0, 'desc'],
                    "searchHighlight": true,
                    "responsive": false,
                    "keys": true,
                    "lengthChange": true,
                    "searching": true,
                    "buttons": [
                        botoncito
                    ],
                    "language": {
                        "sProcessing":     "Procesando...",
                        "sLengthMenu":     "_MENU_ Filas",
                        "sZeroRecords":    "No se encontraron resultados",
                        "sEmptyTable":     "Ningún dato disponible en esta tabla",
                        "sInfo":           "_START_ al _END_ de _TOTAL_",
                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered":   "",
                        "sInfoPostFix":    "",
                        "sSearch":         "_INPUT_",
                        "sUrl":            "",
                        "sInfoThousands":  ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst":    "Primero",
                            "sLast":     "Último",
                            "sNext":     "->",
                            "sPrevious": "<-"
                        },
                        "oAria": {
                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        },
                        "colvis": 'Columnas Visibles'
                    },
                    "columnDefs": [
                        { "type": "num-fmt", "targets": [0,3,4,10,12,13]},
                        { "type": "date", "targets": [2]},
                        { "type": "html", "targets": '_all'},
                        { "orderable": false, "targets": [4] },
                        // { "className": "text-right", "targets": [2,3,5]},
                        // { "className": "text-left", "targets": [1]},
                        // { "visible": false, "targets": [4]}
                    ],
                    "drawCallback": function () {
                        var api = this.api();
                        $( api.table().footer() ).html(
                            `<th  scope="col" colspan="11" class="text-right pr-3">`+formattermoney.format(api.column( 10, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" colspan="2" class="text-right pr-3">`+formatternumber.format(api.column( 12, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" class="text-right pr-3">`+formatternumber.format(api.column( 13, {filter:'applied'} ).data().sum())+`</th>
                            <th scope="col" colspan="3"></th>`
                        );
                        // $('.dataTables_scrollFoot').empty();
                    }
                });
            });
        @break

        @default
    
    @endswitch
	var formattermoney = new Intl.NumberFormat('es-CO', {
	style: 'currency',
	currency: 'COP',
	maximumFractionDigits: 2,
	//maximumFractionDigits: 0,
	});
    var formatternumber = new Intl.NumberFormat('es-CO', {
    maximumFractionDigits: 2,
    //maximumFractionDigits: 0,
    });
	/*funcion para actualizar elplugin responsive in chrome*/
	// function recalcularwitdth() {
	// var table = $('#reporteTable').DataTable();
	// table.columns.adjust();
	// table.responsive.recalc();
	// // console.log('tabla recalculada');
	// }
	$(document).ready(function () {
		var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
		// la funcion se ejecuta unicaente en chrome
		if(is_chrome)
		{
			setTimeout(recalcularwitdth, 100);
		}
	});
</script>
@endsection
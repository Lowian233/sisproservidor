@extends('layouts.app')
@section('htmlheader_title')
RM N° {{--{{$SolicitudServicio->ID_SolSer}}--}}
@endsection
@section('contentheader_title')
<div>
<span style="background-image: linear-gradient(40deg, #fbc2eb, #aa66cc); padding-right:30vw; position:relative; overflow:hidden;">
	Formato Entrega y Recibo de Material Recolección - Transporte
    <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw;  right:-20vw; top:-45%;"></div>
</span>
</div>
@endsection
@section('main-content')
<script  src =" https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js "> </script>

<div class="container-fluid spark-screen">
    <main style="border:solid windowtext 1.0pt; padding:30px;">
        <div>
            <p style="text-align: center; font-family: , serif; font-size: 24px; font-weight: bold; color: #333;">Información del cliente</p>
        </div>
        
        <div class="container-fluid spark-screen">
            <table class=MsoTable15Grid2Accent3 border=1 cellspacing=0 cellpadding=0
            style='width:100.0%;border-collapse:collapse;border:none'>
            <tr style='height:26.85pt'>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>EMPRESA:</b></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$Cliente->CliName}}</span></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                style='color:black'>NIT:</span></b></p>
                </td>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$Cliente->CliNit}}</span></p>
                </td>
            </tr>
            <tr style='height:26.85pt'>
                <td width=340 style='width:255.05pt;border:solid windowtext 1.0pt;
                border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>DIRECCIÓN:</b></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$Cliente->SedeAddress}}</span></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                style='color:black'>CIUDAD:</span></b></p>
                </td>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$Cliente->MunName}}</span></p>
                </td>
            </tr>

            <tr style='height:26.85pt'>
                <td width=340 style='width:255.05pt;border:solid windowtext 1.0pt;
                border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>FUNCIONARIO RESPONSABLE:</b></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->PersFirstName.' '.$SolicitudServicio->PersLastName}}</span></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                style='color:black'>CARGO:</span></b></p>
                </td>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->CargName}}</span></p>
                </td>
            </tr>
            <tr style='height:26.85pt'>
                <td width=340 style='width:255.05pt;border:solid windowtext 1.0pt;
                border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>CORREO ELECTRÓNICO</b></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->PersEmail}}</span></p>
                </td>
                <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                style='color:black'>TELÉFONO:</span></b></p>
                </td>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->PersCellphone}}</span></p>
                </td>
            </tr>
            <tr style='height:26.85pt'>
                <td width=340 style='width:255.05pt;border:solid windowtext 1.0pt;
                border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:none;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>CONDUCTOR ASIGNADO:</b></p>
                </td>
               <td width=340 style='width:255.05pt;border-bottom:solid windowtext 1.0pt;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->SolSerConductor}}</span></p>
                </td>
                <td width=340 style='width:255.05pt;border-bottom:solid windowtext 1.0pt;border-top:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                style='color:black'>VEHÍCULO:</span></b></p>
                </td>
                <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
                background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                style='color:black'>{{$SolicitudServicio->SolSerVehiculo}}</span></p>
                </td>
            </tr>
            </table>
        </div>
    </main>
    @foreach($GenerResiduos as $generadores)
            <main style="border:solid windowtext 1.0pt; padding:30px;"> 
                <div>
                    <p style="text-align: center; font-family: , serif; font-size: 24px; font-weight: bold; color: #333;">Información de recolección por generadores</p>
                    <a href="/serviciosexpress/{{$SolicitudServicio->SolSerSlug}}/add-respel" class="btn btn-primary pull-right"><i class="fas fa-plus"></i><b> Añadir Residuo</b></a>                    
                </div>
                <br>
                <br>
                <table class=MsoTable15Grid2Accent3 border=1 cellspacing=0 cellpadding=0
                    style='width:100.0%;border-collapse:collapse;border:none'>
                    <tr style='height:26.85pt'>
                        <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                        border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>GENERADOR:</b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->GenerName}}</span></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                        style='color:black'>NOMBRE SEDE:</span></b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                        border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->GSedeName}}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td width=340 style='width:255.05pt;border:solid windowtext 1.0pt;
                        border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>DIRECCIÓN:</b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt; border-right:solid windowtext 1.0pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->GSedeAddress}}</span></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                        style='color:black'>CIUDAD:</span></b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border-top:solid windowtext 1.0pt;
                        border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;
                        background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->MunName}}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;border-left:solid windowtext 1.0pt; border-bottom:solid windowtext 1.0pt;background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b>CORREO ELECTRÓNICO:</b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt; border-bottom:solid windowtext 1.0pt;background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->GSedeEmail}}</span></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;border-left:solid windowtext 1.0pt; border-bottom:solid windowtext 1.0pt;background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span
                        style='color:black'>TELEFONO:</span></b></p>
                        </td>
                        <td width=340 style='width:255.05pt;border:none;border-top:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; border-bottom:solid windowtext 1.0pt;background:white;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                        <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='color:black'>{{$generadores->GSedeCelular}}</span></p>
                        </td>
                    </tr>
                    </table>
                    <table id="SolserGenerTable" class="table table-bordered">
                            @php 
                                // $TotalEnv = 0;
                                // $TotalRec = 0;
                                // $TotalCons = 0;
                                // $TotalTrat = 0;
                            @endphp
                            <thead>
                                <tr>
                                    <th>{{__('adminlte::message.solserrespel')}}</th>
                                    <th>Tratamiento</th>
                                    <th>Corriente</th>
                                    <th>Embalaje</th> 
									<th>Cantidad <br> Embalaje</th>
                                    <th>Cantidad <br> Declarada</th>
                                    <th>Cantidad <br> Conciliada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Residuos as $Residuo)
                                    @if($Residuo->FK_SGener == $generadores->FK_SGener)
                                        @php
                                            // $TotalEnv = $Residuo->SolResKgEnviado+$TotalEnv;
                                            // $TotalRec = $Residuo->SolResKgRecibido+$TotalRec;
                                            // $TotalCons = $Residuo->SolResKgConciliado+$TotalCons;
                                            // $TotalTrat = $Residuo->SolResKgTratado+$TotalTrat;
											// $SolResCantEmbalaje = $Residuo->SolResCantEmbalaje;
                                            switch ($Residuo->SolResTypeUnidad) {
                                                case 'Unidad':
                                                    $TypeUnidad = 'Unidades';
                                                    break;
                                                case 'Litros':
                                                    $TypeUnidad = 'Litros';
                                                    break;
                                                default:
                                                    $TypeUnidad = 'Kilogramos';
                                                    break;
                                            }
                                        @endphp
                                    <tr>    
                                        <td><a title="Ver Residuo" href="/respels/{{$Residuo->RespelSlug}}" target="_blank" {{(in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA))&&($Residuo->RespelStatus != "Revisado") ? 'style=color:red;' : ""}} >
                                            <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            @if((in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA))&&($Residuo->SustanciaControladaTipo == 0)&&($Residuo->SustanciaControlada != Null))
                                                <a><i class="fas fa-flask" style="color: green"></i></a>
                                            @endif
                                            @if((in_array(Auth::user()->UsRol, Permisos::AREALOGISTICA))&&($Residuo->AceiteUsado == 1)&&($Residuo->AceiteUsado !=Null))
                                                <a><i class="fas fa-flask" style="color: #00c0ef"></i></a>
                                            @endif
                                                {{$Residuo->RespelName}}</td>
                                        <td>{{$Residuo->TratName}} {{in_array(Auth::user()->UsRol, Permisos::TODOPROSARC) ? '-' .$Residuo->CliShortName : ''}}</td>
                                        @foreach($PublicRespels as $corriente)
                                        @if ($corriente->ID_Respel == $Residuo->ID_Respel)
                                            @if($corriente->YRespelClasf4741 <> null)
                                                <td class="text-center">{{$corriente->YRespelClasf4741}}</td>
                                            @elseif($corriente->ARespelClasf4741 <> null)
                                                <td class="text-center">{{$corriente->ARespelClasf4741}}</td>
                                            @else
                                                <td class="text-center">N/D</td>
                                            @endif
                                        @endif	
                                        @endforeach
                                        <td>{{$Residuo->SolResEmbalaje}}</td>
                                        <td style="text-align: center;">@if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::SolSer1))
    									<a onclick="editEmbalaje('{{$Residuo->SolResSlug}}','{{$Residuo->SolResCantEmbalaje}}')">
      									<i class="fas fa-marker"></i>
    									</a>
  										@endif
  										{{$Residuo->SolResCantEmbalaje ?? 'N/A'}}
									    </td>
                                        <td style="text-align: center;">{{number_format($Residuo->SolResKgEnviado, $decimals = 2, $dec_point = ',', $thousands_sep = '.')}} Kilogramos</td>
                                        <td style="text-align: center;">
                                            @if(in_array(Auth::user()->UsRol, Permisos::SolSer1) || in_array(Auth::user()->UsRol2, Permisos::RECIBOMATERIAL))
                                                @if(($SolicitudServicio->SolSerStatus === 'Programado'||$SolicitudServicio->SolSerStatus === 'Notificado') && (count($Programaciones)>$ProgramacionesActivas))
                                                    @if($Residuo->SolResTypeUnidad == 'Litros' || $Residuo->SolResTypeUnidad == 'Unidad')
													<a onclick="addkg(`{{$Residuo->SolResSlug}}`, `{{$Residuo->SolResCantiUnidadRecibida}}`, `{{$Residuo->SolResCantiUnidadConciliada}}`, `{{$TypeUnidad}}`, `{{$Residuo->SolResKgRecibido == 0 ? '' : number_format($Residuo->SolResKgRecibido, $decimals = 2, $dec_point = ',', $thousands_sep = '.')}}`, null, `{!!json_encode($Residuo->SolResRM2, JSON_NUMERIC_CHECK)!!}`)">
                                                    @else
													<a onclick="addkg(`{{$Residuo->SolResSlug}}`, `{{$Residuo->SolResCantiUnidadRecibida}}`, `{{$Residuo->SolResCantiUnidadConciliada}}`, `{{$TypeUnidad}}`, `{{$Residuo->SolResKgRecibido == 0 ? '' : number_format($Residuo->SolResKgRecibido, $decimals = 2, $dec_point = ',', $thousands_sep = '.')}}`, null, `{!!json_encode($Residuo->SolResRM2, JSON_NUMERIC_CHECK)!!}`)"> 
                                                    @endif
                                                @else
                                                    <a style="color: black">
                                                @endif
                                                <i class="fas fa-marker"></i></a>
                                            @endif
                                            @if($Residuo->SolResTypeUnidad == 'Litros' || $Residuo->SolResTypeUnidad == 'Unidad')
                                                {{-- {{' '.$Residuo->SolResCantiUnidadRecibida}} --}}
                                                {{$Residuo->SolResCantiUnidadRecibida === null ? 'N/A' : $Residuo->SolResCantiUnidadRecibida }}

                                            @else
                                                {{' '.number_format($Residuo->SolResKgRecibido, $decimals = 2, $dec_point = ',', $thousands_sep = '.')}}
                                            @endif
                                            {{$TypeUnidad}}
                                        </td>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <a onclick="ModalStatusFirmaCliente('{{$SolicitudServicio->SolSerSlug}}', '{{$generadores->FK_SGener}}')" style="margin: 10px 10px;" class='btn btn-info float-left'>
                            <i class="fas fa-signature"></i><b> Firma Cliente</b>
                        </a>
                       {{--<a onclick="ModalStatusFirmaConductor('{{$SolicitudServicio->SolSerSlug}}', '{{$generadores->FK_SGener}}')" style="margin: 10px 10px;" class='btn btn-info float-left'>
                            <i class="fas fa-signature"></i><b> Firma Conductor</b>
                        </a>--}}
                        <br>
                        <a target="_blank" href="{{ route('serviceexpress.wordtemplate', ['id' => $generadores->FK_SGener, 'slug' => $SolicitudServicio->SolSerSlug]) }}" class="btn btn-primary pull-right" style="margin-right: 1em"> <i class="fas fa-file-word"></i> <b>Recibo Material</b></a>
					</td>
                        <br>
                        <br>   
                        <div id="addkgmodal"></div>
                        <div id="ModalStatusFirmaCliente"></div>
                        <div id="ModalStatusFirmaConductor"></div>
                        <div id="editEmbalajeModal"></div>

            </main>
        @endforeach
        <br>
		<!-- Botón Conciliado - Inicialmente deshabilitado hasta generar RM -->
        <a href='#' id="btn-conciliado" onclick="ModalStatus('{{$SolicitudServicio->SolSerSlug}}', 'Conciliada')" class="btn btn-secondary pull-right disabled" style="pointer-events: none; opacity: 0.6;">
            <i class="fas fa-clipboard-check"></i> {{__('adminlte::message.solserstatusconciliado')}} (Genere RM primero)
		</a>
		</div>
		<div id="ModalStatus"></div>
        <div id="ModalStatusPDA"></div>
        
</div>        
@endsection

<script>
	function editEmbalaje(slug, cant){
		document.getElementById('editEmbalajeModal').innerHTML = `
			<form role="form" action="/serviciosexpress-residuo/${slug}/Update" method="POST" id="FormEmbalaje" data-toggle="validator">
				@csrf
				@method('PUT')
				<input type="hidden" name="from_recibo" value="true">
				<div class="modal modal-default fade in" id="modalEditEmbalaje" tabindex="-1">
					<div class="modal-dialog"><div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<div style="font-size: 2em; color: #00a65a; text-align:center;">
								<i class="fas fa-box-open"></i>
								<span style="font-size:.5em;"><p>Cantidad de embalaje</p></span>
							</div>
						</div>
						<div class="modal-body">
							<div class="form-group col-md-12">
								<label for="SolResCantEmbalaje">Cantidad de embalaje</label>
								<small class="help-block with-errors">*</small>
								<input type="number" min="0" class="form-control" id="SolResCantEmbalaje"
									name="SolResCantEmbalaje" value="${cant ?? ''}" required>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary pull-right">Guardar</button>
						</div>
					</div></div>
				</div>
			</form>
		`;
		$('#modalEditEmbalaje').modal();
		$('#FormEmbalaje').validator('update');
		$('#FormEmbalaje').validator('validate');		
	}
</script>

<script>
    function addkg(slug, cantidad, cantidadmax, tipo, cantidadKG, KgConciliado, SolResRM){
        console.log('solresRM = '+SolResRM);
        var rmSelected = JSON.parse(SolResRM);
        var inputUnid =  '<label for="SolResCantiUnidadRecibida">Cantidad Conciliada'+tipo+'</label><small class="help-block with-errors">*</small><input type="text" class="form-control numberKg" id="SolResCantiUnidadRecibida" name="SolResCantiUnidadRecibida" maxlength="5" value="'+cantidad+'" required>';
        var inputKg =  '<label for="SolResCantiUnidadRecibida">Cantidad Conciliada'+tipo+'</label><small class="help-block with-errors">*</small><input type="text" class="form-control numberKg" id="SolResCantiUnidadRecibida" name="SolResKg" maxlength="5" value="'+cantidad+'" required>';
        // var arrayRMs = {!! json_encode($SolicitudServicio->SolSerRMs) !!};
        $('#addkgmodal').empty();
        $('#addkgmodal').append(`
            <form role="form" action="/serviciosexpress-residuo/`+slug+`/Update" method="POST" enctype="multipart/form-data" data-toggle="validator" id="FormKg">
                @method('PUT')
                @csrf
                <input type="hidden" name="from_recibo" value="true">
                <div class="modal modal-default fade in" id="editkgRecibido" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <div style="font-size: 5em; color: green; text-align: center; margin: auto;">
                                    <i class="fas fa-plus-circle"></i>
                                    <span style="font-size: 0.3em; color: black;"><p>
                                        Cantidad
                                        @switch($SolicitudServicio->SolSerStatus)
                                            @case('Programado')
                                            @case('Notificado')
                                                Conciliada
                                                @break
                                            @case('No Conciliado')
                                            @case('Completado')
                                                Conciliada
                                                @break
                                            @case('Conciliado')
                                                Tratada
                                                @break
                                        @endswitch
                                    </p></span>
                                </div>
                            </div>
                            <div class="modal-header">
                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <p>{{$error}}</p>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                    @switch($SolicitudServicio->SolSerStatus)
                                        @case('Programado')
                                        @case('Notificado')
                                        <div class="form-group col-md-12">
                                            <label for="SolResKgRecibido">Cantidad Conciliada (kg)</label>
                                            <small class="help-block with-errors">*</small>
                                            <input type="number" step=".01" class="form-control numberKg" id="SolResKgRecibido" name="SolResKg" maxlength="5" value="`+cantidadKG+`" required>
                                        </div>
                                        <div class="form-group col-md-12">	
                                             `+(tipo != 'Kilogramos' ? '<label for="SolResCantiUnidadRecibida">Cantidad Conciliada '+tipo+'</label><small class="help-block with-errors">*</small><input type="number" step=".1" min="0" class="form-control numberKg" id="SolResCantiUnidadRecibida" name="SolResCantiUnidadRecibida" maxlength="5" value="'+cantidad+'" required>' : '')+`
                                        </div>
                                            @break
                                        @case('No Conciliado')
                                        @case('Completado')
                                        <div class="form-group col-md-12">	
                                            <label for="SolResKgConciliado">Cantidad Conciliada (kg)</label><small class="help-block with-errors">*</small><input type="number" step=".01" min="0" class="form-control" id="SolResKgConciliado" name="SolResKg" maxlength="5" value="`+cantidadKG+`" required>
                                        </div>
                                        <div class="form-group col-md-12">	
                                                `+(tipo != 'Kilogramos' ? '<label for="SolResCantiUnidadConciliada">Cantidad Conciliada '+tipo+' </label><small class="help-block with-errors">*</small><input type="number" step=".1" min="0" class="form-control" id="SolResCantiUnidadConciliada" name="SolResCantiUnidadConciliada" maxlength="5" value="'+cantidad+'" required>' : '')+`
                                        </div>
                                            @break
                                        @case('Conciliado')
                                        @case('Certificacion')
                                        @case('Facturado')
                                        <div class="form-group col-md-12">	
                                            <label for="SolResKgTratado">Cantidad Tratada (kg)</label>
                                            <small class="help-block with-errors">*</small>
                                            <div class="input-group">
                                                <input type="number" step=".01" min="0" class="form-control cantidadmax" id="SolResKgTratado" name="SolResKg" maxlength="5" value="`+cantidadKG+`" max="`+KgConciliado+`" required>
                                                <div class="input-group-btn">
                                                    <a title="Lo conciliado ya esta tratado" id="btn-consiliado" class="btn btn-success" `+(tipo != 'Kilogramos' ? 'onclick="submit('+cantidadmax+','+KgConciliado+',\''+tipo+'\')"' : 'onclick="submit('+null+','+KgConciliado+',\''+tipo+'\')"')+`>Tratado</a>
                                                    <div id="conciliadokg"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">	
                                            `+(tipo != 'Kilogramos' ? '<label for="SolResCantiUnidadTratada">Cantidad Tratada '+tipo+' </label><small class="help-block with-errors">*</small><input type="number" step=".1" min="0" class="form-control" id="SolResCantiUnidadTratada" name="SolResCantiUnidadTratada" maxlength="5" max="'+cantidadmax+'" value="'+cantidad+'" required>' : '')+`
                                        </div>
                                            @break
                                    @endswitch
                                    <input type="text" hidden name="SolRes" value="`+slug+`">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary pull-right">{{__('adminlte::message.save')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        `);
        switch('{{$SolicitudServicio->SolSerStatus}}'){
            case('Programado'):
            case('Notificado'):
                numeroKg();
                break;
            case('Completado'):
            case('No Conciliado'):
                    $('.cantidadmax').inputmask({ alias: 'numeric', max:cantidadmax, rightAlign:false});
                break;
            case('Conciliado'):
                    $('.cantidadmax').inputmask({ alias: 'numeric', max:cantidadmax, rightAlign:false});
                break;
        };
        $('#editkgRecibido').modal();

        var arrayRMs = {!! json_encode($SolicitudServicio->SolSerRMs) !!};

        /*se verifica si todos los valores son nulos*/
        var nulos = 0;
        for (let indexnulos = 0; indexnulos < arrayRMs.length; indexnulos++) {
            if (arrayRMs[indexnulos] == null) {
                nulos++;
            }
        }
        SelectsMultiple();
        $('#FormKg').validator('update');
    };
	

    function ModalStatusFirmaCliente(slug, FK_SGener){
		$('#ModalStatusFirmaCliente').empty();
		$('#ModalStatusFirmaCliente').append(`
			<div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<div style="font-size: 5em; color: #f39c12; text-align: center; margin: auto;">
								<span style="font-size: 0.3em; color: black;"><p>¿Acepta marcar la solicitud de servicio como <b> Entregado</b>?</p></span>
							</div>
						</div>
						<form action="/serviciosexpress/{{$SolicitudServicio->SolSerSlug}}/firmacliente" method="POST" enctype="multipart/form-data" data-toggle="validator" id="SolSer">
							<div class="modal-header">
								@csrf
								<div class="signature-container col-md-12">
									<div id="signature-pad" class="signature-pad">
										<div class="signature-pad--body">
											<canvas id="firmaCanvas" width="540" height="180" style="border: 1px solid #000;"></canvas>
										</div>
										<div class="signature-pad--footer">
											<div class="description">Firma del Cliente</div>

											<div class="signature-pad--actions">
												<div>
													<button type="button" class="button clear" data-action="clear">Nuevo</button>
													<button type="button" class="button" data-action="undo">Borrar</button>
												</div>
												<div>
													<button type="button" class="button save" data-action="save-png">PNG</button>
													<button type="button" class="button save" data-action="save-svg">SVG</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<input type="hidden" id="FirmaCliente" name="FirmaCliente"/>
                                <input type="hidden" name="ID_Gener" value="${FK_SGener}"/>
								<input type="submit" id="Cambiar`+slug+`" style="display: none;">
								<input type="text" name="solserslug" value="`+slug+`" style="display: none;">
								<br>
								<div class="form-group col-md-12">
									<label  color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Observación</b>" data-content="Describa la observación del servicio"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Observación</label>					
									<textarea id="Observacion" rows ="1" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" name="Observacion"></textarea>
								</div>
								<div class="form-group col-md-12">
									<label  color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Funcionario</b>" data-content="Ingrese el nombre de la persona que entrega los residuos"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Nombre del Funcionario</label>					
									<textarea id="NombreFuncionario" rows ="1" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" name="NombreFuncionario"></textarea>
								</div>
								<div class="form-group col-md-12">
									<label  color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Documento</b>" data-content="Ingrese el numero de documento de la persona que entrega los residuos"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Numero de documento</label>					
									<textarea id="CedulaFuncionario" rows ="1" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" name="CedulaFuncionario"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Cancelar</button>
								<label for="Cambiar`+slug+`" class='btn btn-success'>Enviar</label>
							</div>
						</form>
					</div>
				</div>
			</div>
		`);
		var wrapper = document.getElementById("signature-pad");
		var clearButton = wrapper.querySelector("[data-action=clear]");
		var undoButton = wrapper.querySelector("[data-action=undo]");
		var savePNGButton = wrapper.querySelector("[data-action=save-png]");
		var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
		var canvas = wrapper.querySelector("canvas");
		var signaturePad = new SignaturePad(canvas, {
			minWidth: 2,
			maxWidth: 2,
			penColor: "rgb(0, 0, 0)",
		});
		function resizeCanvas() {
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear();
		}
		window.onresize = resizeCanvas;
		resizeCanvas();

		function download(dataURL, filename) {
			if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
				window.open(dataURL);
			} else {
				var blob = dataURLToBlob(dataURL);
				var url = window.URL.createObjectURL(blob);

				var a = document.createElement("a");
				a.style = "display: none";
				a.href = url;
				a.download = filename;

				document.body.appendChild(a);
				a.click();

				window.URL.revokeObjectURL(url);
			}
		}
		function dataURLToBlob(dataURL) {
			var parts = dataURL.split(';base64,');
			var contentType = parts[0].split(":")[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
			}
			return new Blob([uInt8Array], { type: contentType });
		}

		clearButton.addEventListener("click", function (event) {
			resizeCanvas();
		});

		undoButton.addEventListener("click", function (event) {
			var data = signaturePad.toData();

			if (data) {
				data.pop(); // remove the last dot or line
				signaturePad.fromData(data);
			}
		});

		savePNGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL();
				download(dataURL, "signature.png");
			}
		});

		saveSVGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL('image/svg+xml');
				download(dataURL, "signature.svg");
			}
		});

		$('#SolSer').validator('update');
		popover();
		var area = document.getElementById("textDescription");
		var message = document.getElementById("caracteresrestantes");
		var maxLength = 4000;
		$('#textDescription').keyup(function () {
			message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
			observacion = area.value;
		});

		function envsubmitconciliarExpress(){
			$('form').on('submit', function(){
				var data = signaturePad.toDataURL('image/png');
  				var input = document.getElementById('FirmaCliente');

				if(signaturePad.isEmpty()){
					alert("Debe de tomar la firma del cliente antes de continuar.");
					event.preventDefault();
					return false;
				}
  				input.value = data;
				var buttonsubmit = $(this).find('[type="submit"]');
				var idbutton = buttonsubmit[0].id;
				if(buttonsubmit.hasClass('disabled')){
					return false;
				}
				else{
					if(idbutton != ''){
						var label = $('label[for="'+idbutton+'"]');
						$(label).empty();
						$(label).append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
						$(label).attr('disabled', true);
					}
					buttonsubmit.prop('disabled', true);
					buttonsubmit.empty();
					buttonsubmit.append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
					$(this).submit(function(){
						return false;
					});
					return true;
				}
			});
		}
		envsubmitconciliarExpress();
		$('#myModal').modal();
	}

    
    function ModalStatusFirmaConductor(slug, FK_SGener){
		$('#ModalStatusFirmaConductor').empty();
		$('#ModalStatusFirmaConductor').append(`
			<div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<div style="font-size: 5em; color: #f39c12; text-align: center; margin: auto;">
								<span style="font-size: 0.3em; color: black;"><p>¿Acepta marcar la solicitud de servicio como <b> Entregado</b>?</p></span>
							</div>
						</div>
						<form action="/serviciosexpress/{{$SolicitudServicio->SolSerSlug}}/firmaconductor" method="POST" enctype="multipart/form-data" data-toggle="validator" id="SolSer">
							<div class="modal-header">
								@csrf
							    <div class="signature-container col-md-12">
									<div id="signature-pad" class="signature-pad">
										<div class="signature-pad--body">
											<canvas width="540" height="180"></canvas>
										</div>
										<div class="signature-pad--footer">
											<div class="description">Firma del Conductor</div>

											<div class="signature-pad--actions">
												<div>
													<button type="button" class="button clear" data-action="clear">Nuevo</button>
													<button type="button" class="button" data-action="undo">Borrar</button>
												</div>
												<div>
													<button type="button" class="button save" data-action="save-png">PNG</button>
													<button type="button" class="button save" data-action="save-svg">SVG</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<input type="hidden" id="FirmaConductor" name="FirmaConductor"/>
                                <input type="hidden" name="ID_Gener" value="${FK_SGener}"/>
								<input type="submit" id="Cambiar`+slug+`" style="display: none;">
								<input type="text" name="solserslug" value="`+slug+`" style="display: none;">
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Cancelar</button>
								<label for="Cambiar`+slug+`" class='btn btn-success'>Enviar</label>
							</div>
						</form>
					</div>
				</div>
			</div>
		`);
		var wrapper = document.getElementById("signature-pad");
		var clearButton = wrapper.querySelector("[data-action=clear]");
		var undoButton = wrapper.querySelector("[data-action=undo]");
		var savePNGButton = wrapper.querySelector("[data-action=save-png]");
		var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
		var canvas = wrapper.querySelector("canvas");
		var signaturePad = new SignaturePad(canvas, {
			minWidth: 2,
			maxWidth: 2,
			penColor: "rgb(0, 0, 0)",
		});
		function resizeCanvas() {
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear();
		}
		window.onresize = resizeCanvas;
		resizeCanvas();

		function download(dataURL, filename) {
			if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
				window.open(dataURL);
			} else {
				var blob = dataURLToBlob(dataURL);
				var url = window.URL.createObjectURL(blob);

				var a = document.createElement("a");
				a.style = "display: none";
				a.href = url;
				a.download = filename;

				document.body.appendChild(a);
				a.click();

				window.URL.revokeObjectURL(url);
			}
		}
		function dataURLToBlob(dataURL) {
			var parts = dataURL.split(';base64,');
			var contentType = parts[0].split(":")[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
			}
			return new Blob([uInt8Array], { type: contentType });
		}

		clearButton.addEventListener("click", function (event) {
			resizeCanvas();
		});

		undoButton.addEventListener("click", function (event) {
			var data = signaturePad.toData();

			if (data) {
				data.pop(); // remove the last dot or line
				signaturePad.fromData(data);
			}
		});

		savePNGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL();
				download(dataURL, "signature.png");
			}
		});

		saveSVGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL('image/svg+xml');
				download(dataURL, "signature.svg");
			}
		});

		$('#SolSer').validator('update');
		popover();
		var area = document.getElementById("textDescription");
		var message = document.getElementById("caracteresrestantes");
		var maxLength = 4000;
		$('#textDescription').keyup(function () {
			message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
			observacion = area.value;
		});

		function envsubmitconciliarExpress(){
			$('form').on('submit', function(){
				var data = signaturePad.toDataURL('image/png');
  				var input = document.getElementById('FirmaConductor');
  				input.value = data;

				var buttonsubmit = $(this).find('[type="submit"]');
				var idbutton = buttonsubmit[0].id;
				if(buttonsubmit.hasClass('disabled')){
					return false;
				}
				else{
					if(idbutton != ''){
						var label = $('label[for="'+idbutton+'"]');
						$(label).empty();
						$(label).append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
						$(label).attr('disabled', true);
					}
					buttonsubmit.prop('disabled', true);
					buttonsubmit.empty();
					buttonsubmit.append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
					$(this).submit(function(){
						return false;
					});
					return true;
				}
			});
		}
		envsubmitconciliarExpress();
		$('#myModal').modal();
	}

    function ModalStatusPDA(slug, FK_SGener){
		$('#ModalStatusPDA').empty();
		$('#ModalStatusPDA').append(`
			<div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<div style="font-size: 5em; color: #f39c12; text-align: center; margin: auto;">
								<span style="font-size: 0.3em; color: black;"><p>¿Acepta marcar la solicitud de servicio como <b> RECIBIDO</b>?</p></span>
							</div>
						</div>
						<form action="/serviciosexpress/{{$SolicitudServicio->SolSerSlug}}/firmapda" method="POST" enctype="multipart/form-data" data-toggle="validator" id="SolSer">
							<div class="modal-header">
								@csrf
								<div class="signature-container col-md-12">
									<div id="signature-pad" class="signature-pad">
										<div class="signature-pad--body">
											<canvas width="540" height="180"></canvas>
										</div>
										<div class="signature-pad--footer">
											<div class="description">Firma del área de PDA</div>

											<div class="signature-pad--actions">
												<div>
													<button type="button" class="button clear" data-action="clear">Nuevo</button>
													<button type="button" class="button" data-action="undo">Borrar</button>
												</div>
												<div>
													<button type="button" class="button save" data-action="save-png">PNG</button>
													<button type="button" class="button save" data-action="save-svg">SVG</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<input type="hidden" id="FirmaPDA" name="FirmaPDA"/>
                                <input type="hidden" name="ID_Gener" value="${FK_SGener}"/>
								<input type="submit" id="Cambiar`+slug+`" style="display: none;">
								<input type="text" name="solserslug" value="`+slug+`" style="display: none;">
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Cancelar</button>
								<label for="Cambiar`+slug+`" class='btn btn-success'>Enviar</label>
							</div>
						</form>
					</div>
				</div>
			</div>
		`);
		var wrapper = document.getElementById("signature-pad");
		var clearButton = wrapper.querySelector("[data-action=clear]");
		var undoButton = wrapper.querySelector("[data-action=undo]");
		var savePNGButton = wrapper.querySelector("[data-action=save-png]");
		var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
		var canvas = wrapper.querySelector("canvas");
		var signaturePad = new SignaturePad(canvas, {
			minWidth: 2,
			maxWidth: 2,
			penColor: "rgb(0, 0, 0)",
		});
		function resizeCanvas() {
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);
			signaturePad.clear();
		}
		window.onresize = resizeCanvas;
		resizeCanvas();

		function download(dataURL, filename) {
			if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
				window.open(dataURL);
			} else {
				var blob = dataURLToBlob(dataURL);
				var url = window.URL.createObjectURL(blob);

				var a = document.createElement("a");
				a.style = "display: none";
				a.href = url;
				a.download = filename;

				document.body.appendChild(a);
				a.click();

				window.URL.revokeObjectURL(url);
			}
		}
		function dataURLToBlob(dataURL) {
			var parts = dataURL.split(';base64,');
			var contentType = parts[0].split(":")[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
			}
			return new Blob([uInt8Array], { type: contentType });
		}

		clearButton.addEventListener("click", function (event) {
			resizeCanvas();
		});

		undoButton.addEventListener("click", function (event) {
			var data = signaturePad.toData();

			if (data) {
				data.pop(); // remove the last dot or line
				signaturePad.fromData(data);
			}
		});

		savePNGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL();
				download(dataURL, "signature.png");
			}
		});

		saveSVGButton.addEventListener("click", function (event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL('image/svg+xml');
				download(dataURL, "signature.svg");
			}
		});

		$('#SolSer').validator('update');
		popover();
		var area = document.getElementById("textDescription");
		var message = document.getElementById("caracteresrestantes");
		var maxLength = 4000;
		$('#textDescription').keyup(function () {
			message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
			observacion = area.value;
		});

		function envsubmitconciliarExpress(){
			$('form').on('submit', function(){
				var data = signaturePad.toDataURL('image/png');
  				var input = document.getElementById('FirmaPDA');
  				input.value = data;

				var buttonsubmit = $(this).find('[type="submit"]');
				var idbutton = buttonsubmit[0].id;
				if(buttonsubmit.hasClass('disabled')){
					return false;
				}
				else{
					if(idbutton != ''){
						var label = $('label[for="'+idbutton+'"]');
						$(label).empty();
						$(label).append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
						$(label).attr('disabled', true);
					}
					buttonsubmit.prop('disabled', true);
					buttonsubmit.empty();
					buttonsubmit.append(`<i class="fas fa-sync fa-spin"></i> Enviando...`);
					$(this).submit(function(){
						return false;
					});
					return true;
				}
			});
		}
		envsubmitconciliarExpress();
		$('#myModal').modal();
	}

	function ModalStatus(slug, status){
		$('#ModalStatus').empty();
		$('#ModalStatus').append(`
			<div class="modal modal-default fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<div style="font-size: 5em; color: #f39c12; text-align: center; margin: auto;">
								<i class="fas fa-exclamation-triangle"></i>
								<span style="font-size: 0.3em; color: black;"><p>¿Acepta marcar la solicitud de servicio como <b>`+status+`</b>?</p></span>
							</div>
						</div>
                        <form action="/serviciosexpress/changestatus" method="POST" data-toggle="validator" id="SolSer">
							<div class="modal-header">
								@csrf
								<div class="form-group col-md-12">
									<label  color: black; text-align: left;" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserstatusdescrip') }}</b>" data-content="{{ __('adminlte::message.solserstatusdescripdetaill') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{__('adminlte::message.solserstatusdescrip')}}</label>
									<small id="caracteresrestantes" class="help-block with-errors">`+(status == 'No Deacuerdo' ? '*' : '')+`</small>
									<textarea onchange="updatecaracteres()" id="textDescription" rows ="5" style="resize: vertical;" maxlength="4000" class="form-control col-xs-12" `+(status == 'No Deacuerdo' ? 'required' : '')+` name="solserdescript"></textarea>
								</div>
								<input type="submit" id="Cambiar`+slug+`" style="display: none;">
								<input type="text" name="solserslug" value="`+slug+`" style="display: none;">
								<input type="text" name="solserstatus" value="`+status+`" style="display: none;">
							</div> 
							<div class="modal-footer">
								<button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Cancelar</button>
								<label for="Cambiar`+slug+`" class='btn btn-success'>Enviar</label>
							</div>
						</form>
					</div>
				</div>
			</div>
		`);
		$('#SolSer').validator('update');
		popover();
		var area = document.getElementById("textDescription");
		var message = document.getElementById("caracteresrestantes");
		var maxLength = 4000;
		$('#textDescription').keyup(function () {
			message.innerHTML = (maxLength-area.value.length) + " caracteres restantes";
			observacion = area.value;
		});
		envsubmit();
		$('#myModal').modal();
	}

	// **NUEVO FLUJO**: Detectar clic en "Recibo Material" para habilitar botón "Conciliado"
	document.addEventListener('DOMContentLoaded', function() {
		console.log('Configurando listener para "Recibo Material"');
		
		// Buscar todos los enlaces de "Recibo Material" 
		const reciboMaterialLinks = document.querySelectorAll('a[href*="wordtemplate"]');
		
		reciboMaterialLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				console.log('¡Usuario hizo clic en "Recibo Material"!');
				
				// Mostrar notificación inmediata
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Generando Recibo de Material...',
						text: 'Por favor espere mientras se procesa el documento.',
						icon: 'info',
						timer: 2000,
						showConfirmButton: false
					});
					
					// Después de 2.5 segundos, habilitar el botón "Conciliado"
					setTimeout(function() {
						habilitarBotonConciliado();
					}, 2500);
				} else {
					// Si no hay SweetAlert, habilitar inmediatamente
					setTimeout(function() {
						habilitarBotonConciliado();
					}, 1000);
				}
			});
		});
	});
	
	function habilitarBotonConciliado() {
		const btnConciliado = document.getElementById('btn-conciliado');
		if (btnConciliado) {
			// Cambiar estilos para habilitar
			btnConciliado.classList.remove('btn-secondary', 'disabled');
			btnConciliado.classList.add('btn-success');
			btnConciliado.style.pointerEvents = 'auto';
			btnConciliado.style.opacity = '1';
			btnConciliado.innerHTML = '<i class="fas fa-clipboard-check"></i> {{__("adminlte::message.solserstatusconciliado")}}';
			
			console.log('✅ Botón "Conciliado" habilitado exitosamente');
			
			// Notificación visual
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: '¡Recibo de Material Generado!',
					text: 'Ahora puede marcar la solicitud como "Conciliada".',
					icon: 'success',
					timer: 3000,
					showConfirmButton: false,
					position: 'top-end',
					toast: true
				});
			}
		}
	}



</script>


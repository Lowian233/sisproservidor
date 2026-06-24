<!doctype html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
  {{-- <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'> --}}
  <title>Recibo de material</title>

  @php
      // Imágenes embebidas en base64 desde disco para que el PDF no dependa de peticiones HTTP (bloqueadas por Cloudflare)
      $watermarkPath = public_path('img/WATERMARKV5.png');
      $watermarkSrc = file_exists($watermarkPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath)) : '';

      $logoHeaderPath = public_path('img/logoheaderTinyVersion.png');
      $logoHeaderSrc = file_exists($logoHeaderPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoHeaderPath)) : '';
  @endphp

  <style>
    @page {
        size:612.0pt 792.0pt;
        margin: 3cm 0.66cm, 0cm, 0.66cm;
    }

    body {
        background-image: url('{{ $watermarkSrc }}');
        -webkit-background-size: contain;
        -moz-background-size: contain;
        -o-background-size: contain;
        background-size: 600px;
        background-repeat: no-repeat;
        background-position: center;
        padding-bottom: 5px; /* Ajusta según la altura del footer */
        padding-top: 125px;

    }
    header {
    height: 1000px;
    position: fixed;
    top: 0px;
    left: 0px;
    right: 0px;
    background-color: #ffffff00;
    color: rgb(0, 0, 0);
    text-align: right;
    z-index: 1000;
}

footer {
    margin-top: 0px;
    margin-bottom: 0px;
    width: 100%;
    position: relative; /* Permite que el footer se ubique en su lugar natural */
    bottom: 0;
    background-color: #ffffff00;
    color: rgb(0, 0, 0);
}

     /* Font Definitions */
    @font-face
        {font-family:"Cambria Math";
        panose-1:2 4 5 3 5 4 6 3 2 4;}
    @font-face
        {font-family:Calibri;
        panose-1:2 15 5 2 2 2 4 3 2 4;}
    @font-face
        {font-family:"Segoe UI";
        panose-1:2 11 5 2 4 2 4 2 2 3;}
     /* Style Definitions */
     p.MsoNormal, li.MsoNormal, div.MsoNormal
        {margin:0cm;
        font-size:10.0pt;
        font-family:"Times New Roman",serif;}
    p.MsoHeader, li.MsoHeader, div.MsoHeader
        {mso-style-link:"Encabezado Car";
        margin:0cm;
        font-size:10.0pt;
        font-family:"Times New Roman",serif;}
    span.EncabezadoCar
        {mso-style-name:"Encabezado Car";
        mso-style-link:Encabezado;
        font-family:"Times New Roman",serif;}
    .MsoChpDefault
        {font-size:10.0pt;
        font-family:"Calibri",sans-serif;}
     /* Page Definitions */
    #WordSection1
        {
          height: 4cm;
          /*margin: 0cm;*/
          margin: 3cm 0.66cm, 0cm, 0.66cm;
          }
     ol
        {margin-bottom:0cm;}
     ul
        {margin-bottom:0cm;}
      .invoice-box {
      /* padding: 10px; */
      /* border: 1px solid #eee; */
      font-size: 12px;
      line-height: 14px;
      font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
      color: #000;
    }
    .main{

    }
    .invoice-box table {
      width: 100%;
      line-height: inherit;
      text-align: left;
    }

    .invoice-box table td {
      padding: 3px;
      vertical-align: top;
    }

    .invoice-box table tr td:nth-child(3) {
      text-align: left;
    }

    .invoice-box table tr.top table td {
      padding-bottom: 10px;
    }

    .invoice-box table tr.top table td.title {
      font-size: 45px;
      line-height: 45px;
      color: #999;
    }

    .invoice-box table tr.information table td {
      padding-bottom: 0px;
    }

    .invoice-box table tr.heading td {
      background: rgb(0, 56, 140);
      border-bottom: 1px rgb(0, 56, 140);
      font-weight: bold;
        color: #ddd;
    }

    .invoice-box table tr.details td {
      padding-bottom: 0px;
    }

    .invoice-box table tr.item td {
      border-bottom: 1px solid rgb(198, 211, 255);
    }

    .invoice-box table tr.item.last td {
      border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(4) {
      border-top: 2px solid rgb(11, 24, 68);
      font-weight: bold;
      text-align: right;

    }

    @media only screen and (max-width: 600px) {
      .invoice-box table tr.top table td {
        width: 100%;
        display: block;
        text-align: center;
      }

      .invoice-box table tr.information table td {
        width: 100%;
        display: block;
        text-align: center;
      }
    }

    /** RTL **/
    .rtl {
      direction: rtl;
      font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    .rtl table {
      text-align: right;
    }

    .rtl table tr td:nth-child(2) {
      text-align: left;
    }
</style>

</head>

	<header>
        <table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=100% style='width:100%;margin-left:-.25pt;border-collapse:collapse;border:none'>
            <tr width=100% style='height:19.5pt'>
              <th rowspan=3 width=30%; style='width:30%;border:solid windowtext 1.0pt; padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'><p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><span style='position:relative;z-index:251659264; left:0px;margin-left:20px;margin-top:2px;width:178px;height:58px'><img width=178 height=58 src="{{ $logoHeaderSrc }}"></span></p></th>
                <td width=50% nowrap style='width:50%;border:solid windowtext 1.0pt; border-left:none;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal; word-wrap: break-word;'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>FORMATO ENTREGA Y RECIBO DE MATERIAL
                    <br>
                    TRANSPORTE - RECOLECCIÓN</span></b></p>
                </td>
                <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:none;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>CÓDIGO</span></b></p>
                </td>
                <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:none;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>FR-LG-014</span></b></p>
                </td>
            </tr>
            <tr width=100% style='height:19.5pt'>
                <td width=52% rowspan=2 style='width:52%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center;
                    line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>LOGISTICA</span></b></p>
                </td>
                <td width=8% nowrap style='width:8%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>FECHA</span></b></p>
                </td>
                <td width=8% nowrap style='width:8%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>01-12-2023</span></b></p>
                </td>
             </tr>
             <tr width=100% style='height:19.5pt'>
                <td width=8% nowrap style='width:8%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>VERSIÓN</span></b></p>
                </td>
                <td width=8% nowrap style='width:8%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center;line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>4</span></b></p>
                </td>
             </tr>
        </table>
	</header>
    <body>
        <div class=WordSection1>
            <div align=center>
                <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 align=center width=100% style='width:100.0%;border-collapse:collapse;margin-left:4.8pt;margin-right:4.8pt'>
                    <tr style='height:19.5pt'>
                        <td width=30% nowrap style='width:30.0%;padding:0cm 3.5pt 0cm 3.5pt; height:19.5pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>SERVICIO</b>: No. {{$SolicitudServicio->ID_SolSer}}&nbsp;</span></p>
                        </td>
                        @php
                            $esConductor = in_array(Auth::user()->UsRol, \App\Permisos::CONDUCTOR) || in_array(Auth::user()->UsRol2, \App\Permisos::CONDUCTOR);
                            if ($esConductor) {
                                $fechaMostrar = now()->format('d/m/Y');
                            } else {
                                $fechaProgSalida = (!empty($Programaciones) && !empty($Programaciones->ProgVehSalida) && $Programaciones->ProgVehSalida !== '0000-00-00 00:00:00')
                                    ? $Programaciones->ProgVehSalida
                                    : null;
                                $fechaRecep = $SolicitudServicio->recepcion ?? null;
                                $solserFecha = $SolicitudServicio->SolSerFecha ?? null;
                                if ($fechaProgSalida) {
                                    $fechaMostrar = \Carbon\Carbon::parse($fechaProgSalida)->format('d/m/Y');
                                } elseif ($fechaRecep) {
                                    $fechaMostrar = \Carbon\Carbon::parse($fechaRecep)->format('d/m/Y');
                                } elseif ($solserFecha) {
                                    $fechaMostrar = \Carbon\Carbon::parse($solserFecha)->format('d/m/Y');
                                } else {
                                    $fechaMostrar = now()->format('d/m/Y');
                                }
                            }
                        @endphp
                        <td width=30% style='width:30.0%;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center;
                            line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>FECHA RECOLECCIÓN</b>: {{ $fechaMostrar }}&nbsp;</span></p>
                        </td>
                        <td width=30% style='width:30.0%;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=right style='margin-bottom:0cm;text-align:right;
                            line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>AUDITABLE: </b>{{$SolicitudServicio->SolResAuditoriaTipo}}</span></p>
                        </td>
                    </tr>
                </table>
                <br>
                {{--@foreach($GenerResiduos as $GenerResiduo)--}}
                <table width="100%" class="MsoNormalTable" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:none;">
                    <tr style='height:26.85pt'>
                        <td style='border-top:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                             <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>EMPRESA:</span></b></p>
                        </td>
                            <td style='border-top:solid windowtext 1.0pt; border-left:none; border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{ $Cliente->CliName ?? 'N/A' }}</span></p>
                            </td>
                        <td style='border-top:solid windowtext 1.0pt; border-left:none; border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>NIT:</span></b></p>
                        </td>
                        <td style='border-top:solid windowtext 1.0pt; border-left:none ;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{ $Cliente->CliNit ?? 'N/A' }}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td style='border-top:none; border-left:solid windowtext 1.0pt ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>DIRECCIÓN:</span></b></p>
                        </td>
                            <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{$Cliente->SedeAddress}}</span></p>
                            </td>
                        <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>CIUDAD:</span></b></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{ $Cliente->MunName ?? 'N/A' }}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td style='border-top:none; border-left:solid windowtext 1.0pt ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>FUNCIONARIO <br> RESPONSABLE:</span></b></p>
                        </td>
                        <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt; height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{ ($SolicitudServicio->PersFirstName ?? '') . ' ' . ($SolicitudServicio->PersLastName ?? '') }}</span></p>
                        </td>
                        <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt; height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>CARGO:</span></b></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{ $SolicitudServicio->CargName ?? 'N/A' }}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td style='border-top:none; border-left:solid windowtext 1.0pt ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>CORREO <br> ELECTRÓNICO:</span></b></p>
                        </td>
                        <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; border-bottom:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{$SolicitudServicio->PersEmail}}</span></p>
                        </td>
                        <td style='border-top:none; border-left:none ;border-bottom:none;border-right:none; padding:0cm 5.4pt 0cm 5.4pt; height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>TELÉFONO:</b></span></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{$SolicitudServicio->PersCellphone}}</span></p>
                        </td>
                    </tr>
                    <tr style='height:26.85pt'>
                        <td style='border-top:none; border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>CONDUCTOR ASIGNADO:</span></b></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:solid windowtext 1.0pt;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{$SolSerConductor}}</span></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:solid windowtext 1.0pt;border-right:none; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'><b>VEHICULO</b></span></p>
                        </td>
                        <td style='border-top:none; border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:26.85pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>{{$SolicitudServicio->SolSerVehiculo}}</span></p>
                        </td>
                    </tr>
                </table>
                <table  width="100%" class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='border-collapse:collapse;border:none'>
                    <tr style='height:19.5pt'>
                        <td colspan="14" style='border:solid windowtext 1.0pt; border-top:none;background:#D6DCE4;padding:0cm 1.4pt 0cm 1.4pt;height:19.4pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif; color:black'>RESIDUOS A ENTREGAR Y TRANSPORTAR</span></b></p>
                        </td>
                    </tr>
                </table>
                <table width="100%" class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='border-collapse:collapse;border:none'>
                    <tr style='height:19.5pt'>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>DESCRIPCIÓN RESPEL</span></b></p>
                            </td>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>CLASIFICACIÓN (Decreto 4741)</span></b></p>
                            </td>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>GENERADOR</span></b></p>
                            </td>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>EMBALAJE</span></b></p>
                            </td>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>CANTIDAD (EMBALAJE)</span></b></p>
                            </td>
                            <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>CANTIDAD (KG)</span></b></p>
                            </td>
                            <td style='border-top:none; border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>TIPO DE PELIGROSIDAD</span></b></p>
                            </td>
                        </td>
                    </tr>
                     @foreach($Residuos as $Residuo)
                        <tr  width="100%" style='height:22.8pt' colspan=13 >
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{ $Residuo->RespelName ?? 'N/A' }}</span></b></p>
                                </td>
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>
                                      @if($Residuo->YRespelClasf4741 == NULL)
                                        {{$Residuo->ARespelClasf4741}}
                                      @else
                                        {{$Residuo->YRespelClasf4741}}
                                      @endif
                                    </span></b></p>
                                </td>
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{ $Residuo->GenerName ?? 'N/A' }}</span></b></p>
                                </td>
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{$Residuo->SolResEmbalaje}}</span></b></p>
                                </td>
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{$Residuo->SolResCantEmbalaje ?? 'N/A'}}</span></b></p>
                                </td>
                                <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{number_format($Residuo->SolResKgRecibido, $decimals = 2, $dec_point = ",", $thousands_sep = "." )}} Kilogramos</span></b></p>
                                </td>
                                <td style='border-top:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{$Residuo->RespelIgrosidad}} </span></b></p>
                                </td>
                            </td>
                        </tr>
                     @endforeach
                    <tr width="100%" style='height:19.5pt'>
                        <td style='border:none; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                        </td>

                        <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>PESO TOTAL ENTREGADO KG</span></b></p>
                        </td>
                        <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>

                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>{{number_format($totales, $decimals = 2, $dec_point = ',', $thousands_sep = '.')}} kg</span></b></p>
                        </td>

                        <td style='border:none; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                        </td>
                    </tr>
                    <tr width="100%" style='height:19.5pt'>
                        <td style='border:none; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                        </td>
                        <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'>VERIFICACIÓN EN PLANTA</span></b></p>
                        </td>
                        <td style='border:solid windowtext 1.0pt; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:7.0pt;font-family:"Arial",sans-serif'> </span></b></p>
                        </td>
                        <td style='border:none; border-top:none;padding:0cm 1.4pt 0cm 1.4pt;height:22.8pt'>
                        </td>
                    </tr>
                </table>
                <br>
                <table  width="100%" class=MsoNormalTable cellspacing=0 cellpadding=0 style='border:solid windowtext 1.0pt'><br>
                    <tr style='width:100.0%;height:19.4pt'>
                        <td style='border:solid windowtext 1.0pt;background:#D6DCE4;padding:0cm 1.4pt 0cm 1.4pt;height:19.4pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif;  color:black'>LAS PARTES HACEN CONSTAR QUE:</span></b></p>
                        </td>
                    </tr>
                    <tr style='height:21.9pt'>
                        <td colspan=1 style='border-top:none;border-left:solid windowtext 1.0pt;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:21.9pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;text-align:center; text-align: justify; line-height:normal'><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>a.La Empresa Usuaria certifica que los residuos y/o materiales entregados para su tratamiento y disposición final correspondiente fielmente a lo relacionado y especificado en la declaración de residuos y que NO ha enviado con el presente servicio materiales o residuos explosivos o radiactivos.</span></p>
                        </td>
                    </tr>
                    <tr style='height:20.1pt'>
                        <td colspan=14 style='border-top:none;border-left:solid windowtext 1.0pt;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:20.1pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;text-align: justify; line-height:normal'><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>b. Que los residuos y/o materiales entregados han sido identificados de acuerdo a lo estipulado en el Decreto 4741 y que se encuentran debidamente embalados y sin riesgo de que se produzca derrame durante su cargue o transporte.</span></p>
                        </td>
                    </tr>
                    <tr style='height:21.1pt'>
                        <td colspan=14 style='border-top:none;border-left: solid windowtext 1.0pt;border-bottom:none;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:21.1pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;text-align: justify; line-height:normal'><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>c. Así mismo se hace constar que las puertas del furgón ha sido cerradas con candado y ha sido colocado y cerrado el <b>PRECINTO DE SEGURIDAD No. {{ $precintosString }}</b> en presencia de <b>{{$firmas->NombreFuncionario}}</b> con cedula No.<b>{{$firmas->Cedula}}</b></span></p>
                        </td>
                         <br>
                         <br>
                    </tr>
                    <tr style='height:19.4pt'>
                        <td colspan=5 style='border:solid windowtext 1.0pt; background:#D6DCE4;padding:0cm 1.4pt 0cm 1.4pt;height:19.4pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif; color:black'>OBSERVACIONES</span></b></p>
                        </td>
                    </tr>
                    <tr style='height:21.1pt'>
                        <td colspan=14 style='border-top:none;border-left: solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 1.4pt 0cm 1.4pt;height:21.1pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>{{$firmas->Observaciones}} </span></p>
                        </td>
                    </tr>
                </table>
                <br>
                <br>
                <table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=100% style='width:100%;margin-left:-.25pt;border-collapse:collapse;border:none'>
                    <tr width=100% style='height:19.5pt'>
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt; background:#D6DCE4'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>ENTREGADO POR (Funcionario Empresa Usuaria)</span></b></p>
                        </td>
                        <td width=50% nowrap style='width:50%;border:solid windowtext 1.0pt; border-left:none;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt; background:#D6DCE4'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal; word-wrap: break-word;'><b><span style='font-size:10.0pt;font-family:"Arial",sans-serif'>RECIBIDO POR
                        </td>
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:none;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt; background:#D6DCE4'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>CONTROL HORARIO</span></b></p>
                        </td>
                    </tr>
                    <tr width=100% style='height:19.5pt'>
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class="MsoNormal" style="text-align:center;">
                                @php
                                    $firmaClienteSrc = '';
                                    $firmaClienteNombre = trim((string) ($firmas->FirmaCliente ?? ''));
                                    if (!empty($firmaClienteNombre) && $firmaClienteNombre !== '0') {
                                        // Normaliza valores legacy que pueden venir con ruta completa o con "public/".
                                        $firmaClienteNombre = str_replace('\\', '/', $firmaClienteNombre);
                                        $firmaClienteNombre = basename(explode('?', $firmaClienteNombre)[0]);
                                        $firmaClienteArchivo = preg_match('/\.png$/i', $firmaClienteNombre) ? $firmaClienteNombre : ($firmaClienteNombre . '.png');
                                        $firmaClienteCandidates = [
                                            storage_path('app/public/FirmasClientesRegulares/' . $firmaClienteArchivo),
                                            public_path('storage/FirmasClientesRegulares/' . $firmaClienteArchivo),
                                            // Fallback legacy por si hay despliegues antiguos.
                                            public_path('img/FirmasClientesRegulares/' . $firmaClienteArchivo),
                                        ];
                                        foreach ($firmaClienteCandidates as $candidate) {
                                            if (file_exists($candidate)) {
                                                $firmaClienteSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($candidate));
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <span lang="ES">
                                    @if(!empty($firmaClienteSrc))
                                        <img width="118" height="76" id="Imagen 6" src="{{ $firmaClienteSrc }}">
                                    @else
                                        <span style="font-size:8.0pt;font-family:'Arial',sans-serif; color:#666;">Sin firma registrada</span>
                                    @endif
                                </span>
                            </p>
                        </td>
                        <td width="10%" nowrap colspan="1" style="width: 10pt; border: solid windowtext 1.0pt; border-left: solid windowtext 1.0pt; padding: 0cm 3.5pt 0cm 3.5pt; height: 19.5pt; text-align: center; vertical-align: middle;">
                         @php
                            // Intentar primero con el ID del usuario (como funcionaba antes)
                            $userImagePath = public_path('img/' . $user->id . '.png');
                            // Si no existe, intentar con UsSlug
                            if (!file_exists($userImagePath)) {
                                $userImagePath = public_path('img/' . $user->UsSlug . '.png');
                            }
                            $transparentPixel = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/w8AAgMBAp+X2l0AAAAASUVORK5CYII=';
                            $defaultImagePath = public_path('img/5.png');
                            if (file_exists($userImagePath)) {
                                $imageData = base64_encode(file_get_contents($userImagePath));
                                $imageSrc = 'data:image/png;base64,' . $imageData;
                            } elseif (file_exists($defaultImagePath)) {
                                $imageData = base64_encode(file_get_contents($defaultImagePath));
                                $imageSrc = 'data:image/png;base64,' . $imageData;
                            } else {
                                $imageSrc = $transparentPixel;
                            }
                        @endphp
                        <img width="118" height="76" id="Imagen6" src="{{ $imageSrc }}" style="display: block; margin: auto;">
                        </td>
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>Fecha de Registro:
                                <br>
                                {{ now()->format('d/m/Y H:i') }}
                                &nbsp;</span></b>
                            </p>
                        </td>
                    </tr>
                    <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><span style='font-size:6.0pt;font-family:"Arial",sans-serif; color:#666666'>Nota: En algunas ocasiones la firma del cliente aparece con "L/P" cuando el RM es firmado por logística (logística prosarc).</span></p>
                    <tr width=100% style='height:19.5pt'>
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>Nombre: {{$firmas->NombreFuncionario}} </span></b></p>
                        </td>
                        @if($SolicitudServicio->SolSerTypeCollect === Null )
                            <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt">
                                <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>PROSARC SA ESP</span></b></p>
                            </td>
                        @else
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>Ayudante: {{$Programaciones ? $Programaciones->PersFirstName . ' ' . $Programaciones->PersLastName : 'No asignado'}} </span></b></p>
                        </td>
                        @endif
                        <td width=10% nowrap colspan=1 style='width:10pt;border:solid windowtext 1.0pt; border-left:solid windowtext 1.0pt;padding:0cm 3.5pt 0cm 3.5pt;height:19.5pt'>
                            <p class=MsoNormal align=center style='margin-bottom:0cm;text-align:center; line-height:normal'><b><span style='font-size:8.0pt;font-family:"Arial",sans-serif'>Ingreso a Planta: </span></b></p>
                        </td>
                </table>
            </body>
    <footer>
        <div class="invoice-box header-footer">
			<table cellpadding="0" cellspacing="0">
				<tr>
                    <td style="text-align: center; font-size: 10px; line-height: 11px;"> <b></b>
					    <b>Planta de procesos</b>: kilómetro 6, vía a la mesa<br>
						sub estación Balsillas <b>Mosquera Cundinamarca</b><br>
						<b>Telefax:</b> (571) 742 5395 – 7425417<br>
						<b>Celular:</b> 317 667 3032 – 317 667 3035<br>
						<br>
						<b>Sede administrativa y comercial:</b><br>
						Calle 120 A No 7 – 62/68 Of. 605 <b>Bogotá, D.C. - Colombia</b><br>
						<b>PBX-FAX</b> 629 9853 - 637 5112 <b>Servicio al cliente</b> 316 439 3895<br>
						<b>www.prosarc.com</b><br>
						</td>
				</tr>
				<tr>
					<td colspan="4" style="text-align: center; font-size: 10px;">
						<br>
						Recibo de material generado y firmado digitalmente desde la aplicación <b>SisPRO</b> &copy; <?php echo date("Y");?> <br>
						¡Protejamos el medio ambiente; así aseguramos la vida y bienestar de nuestros hijos, nietos y generaciones futuras!
					</td>
				</tr>
			</table>
		</div>

    </footer>
</html>

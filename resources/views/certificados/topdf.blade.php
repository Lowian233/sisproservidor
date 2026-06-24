<!DOCTYPE html>
<html>
<head>

  <meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
  {{--<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>--}}
  <title>Certificado C-{{sprintf("%10s", $certificado->ID_Cert)}}</title>

  <style>
    @page {
        size:612.0pt 792.0pt;
        margin: 3cm 0.66cm, 0cm, 0.66cm;
    }

    body {
        background-image: url('@pdfAsset("img/WATERMARKV5.png")');
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
    height: 100px;
    position: fixed;
    top: 0px;
    left: 0px;
    right: 0px;
    background-color: #ffffff00;
    color: rgb(0, 0, 0);
    text-align: right;
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

  <div class="encabezado">
    <div class="invoice-box header-footer">
      <table cellpadding="0" cellspacing="0">
        <tr class="top">
          <td colspan="1">
            <table width=100%>
              <tr>
                <td style="vertical-align: bottom; text-align: right;" width=60%>

                  <p class="MsoNormal" align="right" style="text-align:right; margin-bottom: 0;"><b><span lang="ES" style="font-size: 8.0pt; font-family: Arial, sans-serif;">PROSARC S.A. ESP</span></b></p>
                  <p class="MsoNormal" align="right" style="text-align:right; margin-bottom: 0;"><b><span lang="ES" style="font-size: 8.0pt; font-family: Arial, sans-serif;">NIT 900.079.188-0</span></b></p>
                  <br>
                  <p class="MsoNormal" align="right" style="text-align:right; margin-left:35.4pt; text-indent:-35.4pt; margin-bottom: 0;"><b><span lang="ES" style="font-size:12.0pt; font-family: Arial, sans-serif;">CERTIFICA:</span></b></p>

                </td>
                <td style="font-size: 12px; text-align: right;" width=40%>
                  <img src="@pdfAsset('img/logoheaderTinyVersion.png')" style="width:100%; max-width:200px; text-align:right;"><br><br>
                  <b style="margin-right:60.4pt;">Nit: 900.079.188-0</b><br><br>
                  <b>No.</b> <b style="margin-right:70.4pt; color:red;">{{sprintf("%05s", $certificado->ID_Cert)}}</b><br>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  </div>
</header>

  <body lang=ES-MX>


    <div class=WordSection1>



        <p class=MsoNormal><span lang=ES style='margin-left:60.4pt;text-align:center;font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Que el generador:</span></p>

        <p class=MsoNormal><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>&nbsp;</span></b></p>

        <div style='margin-left:65.4pt;text-align:center;'>

          <table width="85%" class="MsoNormalTable" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:none;">
            <tr>
                <td width="70" valign="top" style="width:82.0pt; border:solid gray 0.5pt; padding:0cm 5.4pt 0cm 5.4pt; ">
                    <p class="MsoNormal" style="text-align:justify"><span lang="ES" style="font-size: 7.5pt; font-family:'Arial',sans-serif; color:#0D0D0D">Empresa: <b>{{$certificado->sedegenerador->generadors->GenerName}}</b></span></p>
                </td>
                <td width="70" valign="top" style="width:82.0pt; border:solid gray 0.5pt; border-left:none; padding:0cm 5.4pt 0cm 5.4pt;">
                    <p class="MsoNormal" style="text-align:justify"><span lang="ES" style="font-size: 7.5pt; font-family:'Arial',sans-serif; color:#0D0D0D">NIT: <b>{{$certificado->sedegenerador->generadors->GenerNit}}</b></span></p>
                </td>
            </tr>
            <tr>
                <td width="200" valign="top" style="width:82.0pt; border:solid gray 0.5pt; border-top:none; padding:0cm 5.4pt 0cm 5.4pt;">
                    <p class="MsoNormal" style="text-align:justify"><span lang="ES" style="font-size: 7.5pt; font-family:'Arial',sans-serif; color:#0D0D0D">Dirección: <b>{{$certificado->sedegenerador->GSedeAddress}} (Municipio:{{$certificado->sedegenerador->municipio->MunName}}) ({{$certificado->sedegenerador->GSedeName}})</b></span></p>
                </td>
                <td width="130" style="width:92.15pt; border-top:none; border-left:none; border-bottom:solid gray 0.5pt; border-right:solid gray 0.5pt; padding:0cm 5.4pt 0cm 5.4pt;">
                    <p class="MsoNormal"><span lang="ES" style="font-size:7.5pt; font-family:'Arial',sans-serif; color:#0D0D0D">Ciudad: <b>{{$certificado->sedegenerador->municipio->MunName}}</b></span></p>
                </td>
            </tr>
          </table>
        </div>

        <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
        7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

        <p class=MsoNormal style='margin-left:60.4pt;'><span lang=ES style='font-size:
        7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Por intermedio de la empresa
        transportadora:</span></p>

        <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
        7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

      <div style='margin-left:65.4pt;text-align:center;'>

      @switch($certificado->SolicitudServicio->SolSerTipo)

      @case('Cliente')
      @case('Interno')

        <table width= "85%" class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 style='border-collapse:collapse;border:none'>
          <tr>
            <td width="198" valign=top style="width:148.45pt;border:solid gray 0.5pt;
            padding:0cm 5.4pt 0cm 5.4pt">
            <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
            color:#0D0D0D'>Empresa: <b>{{$certificado->transportador->ID_Cli == 1 ? $certificado->transportador->CliShortname : $certificado->transportador->CliName}}</b></span></p>
            </td>
            <td width="70" valign=top style="width:3.0cm;border:solid gray 0.5pt;
            border-left:none;padding:0cm 5.4pt 0cm 5.4pt">
            <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
            7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>NIT: <b>{{$certificado->transportador->CliNit}}</b></span></p>
            </td>
          </tr>
          <tr>
            <td width="298" valign=top style="width:248.45pt;border:solid gray 0.5pt;
            border-top:none;padding:0cm 5.4pt 0cm 5.4pt">
            <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
            7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Dirección:  <b>{{$certificado->transportador->sedes[0]->SedeAddress}} ({{$certificado->transportador->sedes[0]->Municipios->MunName}})</b></span></p>
            </td>
            <td width="113" valign=top style="width:3.0cm;border-top:none;border-left:none;
            border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt">
            <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
            color:#0D0D0D'>Ciudad: <b>{{$certificado->transportador->sedes[0]->Municipios->MunName}}</b></span></p>
            </td>
          </tr>
        </table>

          @break

          @case('Generador')
          @case('Externo')

        <table width= "40%" class=MsoNormalTable border=1 cellspacing=0 cellpadding=0
        style='border-collapse:collapse;border:none'>
        <tr>
        <td colspan="1" width=397 valign=top style='width:297.45pt;border:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt'>
          <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
          color:#0D0D0D'>Empresa:  <b>{{$certificado->SolicitudServicio->SolSerNameTrans}}</b></span></p>
          </td>
          <td colspan="1" width=70 valign=top style='width:3.0cm;border:solid gray 0.5pt;
          border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
          <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
          7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>NIT: <b>{{$certificado->SolicitudServicio->SolSerNitTrans}}</b></span></p>
          </td>
        </tr>
        <tr>
          <td colspan="1" width=397 valign=top style='width:297.45pt;border:solid gray 0.5pt;
          border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
          <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
          7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Dirección:  <b>{{$certificado->SolicitudServicio->SolSerAdressTrans}} ({{$certificado->SolicitudServicio->municipio->MunName}})</b></span></p>
          </td>
          <td colspan="1" width=113 valign=top style='width:3.0cm;border-top:none;border-left:none;
          border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt'>
          <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
          color:#0D0D0D'>Ciudad: <b>{{$certificado->SolicitudServicio->municipio->MunName}}</b></span></p>
          </td>
        </tr>
        </table>

        @break

        @endswitch
      </div>

      <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

      <p class=MsoNormal style='margin-left:60.4pt;margin-right:60.4pt;text-align:justify'><span lang=ES style='font-size:
        7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Entregó su(s) residuo(s)
      y/o desecho(s) a la planta <b>{{$certificado->gestor->ID_Cli == 1 ? $certificado->gestor->CliShortname : $certificado->gestor->CliName}}</b>, para <b>{{$certificado->tratamiento->TratName}}</b>de acuerdo con la siguiente información:</span></p>

      <p class=MsoNormal align=center style='text-align:center'><span lang=ES
      style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

    <div style='margin-left:65.4pt; margin-right: 65.4pt; text-align:center;'>

      <table width= "100%" class=MsoNormalTable border=1 cellspacing=0 cellpadding=0
      style='border-collapse:collapse;border:none'>
      <tr>
        <td width="170%" style='border:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Fecha de Recepción</span></p>
        </td>
        <td width="340%" colspan=4 style='border:solid gray 0.5pt;
        border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>@php
            $añorecepcion=date('Y', strtotime($fechaLlegadaPlanta));
            $mesrecepcion=date('m', strtotime($fechaLlegadaPlanta));
            $diarecepcion=date('d', strtotime($fechaLlegadaPlanta));
            $mesrecepciontexto = "";
            switch ($mesrecepcion) {
            case 1:
            $mesrecepciontexto = 'Enero';
            break;

            case 2:
            $mesrecepciontexto = 'Febrero';
            break;

            case 3:
            $mesrecepciontexto = 'Marzo';
            break;

            case 4:
            $mesrecepciontexto = 'Abril';
            break;

            case 5:
            $mesrecepciontexto = 'Mayo';
            break;

            case 6:
            $mesrecepciontexto = 'Junio';
            break;

            case 7:
            $mesrecepciontexto = 'Julio';
            break;

            case 8:
            $mesrecepciontexto = 'Agosto';
            break;

            case 9:
            $mesrecepciontexto = 'Septiembre';
            break;

            case 10:
            $mesrecepciontexto = 'Octubre';
            break;

            case 11:
            $mesrecepciontexto = 'Noviembre';
            break;

            case 12:
            $mesrecepciontexto = 'Diciembre';
            break;
            }
            if($fechaLlegadaPlanta != ""){
            }
        @endphp
        {{$diarecepcion}} de {{$mesrecepciontexto}} del {{$añorecepcion}}</span></p>
        </td>
      </tr>
      <tr>
        <td width= "170%" style='border:solid gray 0.5pt;border-top:none;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Número de Recibo de Materiales </span></p>
        </td>
        <td width= "76%" style='border-top:none;border-left:none;
        border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>{{$certificado->SolicitudServicio->ID_SolSer}}</span></b></p>
        </td>
        <td width= "198%" colspan=2 style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal align=center style='text-align:center'><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Número
        manifiesto de carga</span></p>
        </td>
        <td width= "66%"" style='border-top:none;border-left:none;border-bottom:
        solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>{{$certificado->SolicitudServicio->ID_SolSer}}</span></b></p>
        </td>
      </tr>
      <tr>
        <td width= "510%" colspan=5 style='border:solid gray 0.5pt;
        border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>INFORMACIÓN
        DEL RESIDUO Y/O DESECHO</span></b></p>
        </td>
      </tr>
      <tr style='height:8.05pt'>
        <td width="100%" colspan=2 style='border:solid gray 0.5pt;
        border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>RESIDUO</span></b></p>
        </td>
        <td width= "76%" style='border-top:none;border-left:none;
        border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt;
        height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>CORRIENTE</span></b></p>
        </td>
        <td width= "76%" style='border-top:none;border-left:none;
        border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt;
        height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>ESTADO</span></b></p>
        </td>
        <td width= "66%" style='border-top:none;border-left:none;border-bottom:
        solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt;
        height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>PESO
        (kg)</span></b></p>
        </td>
      @php
      $totalKg = 0;
      // Evitar duplicación: un residuo puede tener varios certdato; mostrar cada uno solo una vez
      $certdatoUnicos = $certificado->certdato->unique('FK_DatoCertSolRes');
      @endphp
      @foreach($certdatoUnicos as $certdato)
      @php $Residuo = $certificado->SolicitudServicio->SolicitudResiduo->firstWhere('ID_SolRes', $certdato->FK_DatoCertSolRes); @endphp
      @if($Residuo)
      </tr>
      <tr style='height:8.05pt'>
        <td width="368%" colspan=2 valign=top style='border:solid gray 0.5pt;
        border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:8.05pt'>
        <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#333333'>{{$Residuo->generespel->respels->RespelName}}</span></p>
        </td>
        <td width= "76%" valign=top style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt;height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#333333'>
        @if($Residuo->generespel->respels->RespelIgrosidad == 'No peligroso')
        N/A
        @else
        {{$Residuo->generespel->respels->YRespelClasf4741}}{{$Residuo->generespel->respels->ARespelClasf4741}}
        @endif
        </span></p>
        </td>
        <td width="76%" valign=top style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt;height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#333333'>
        {{$Residuo->generespel->respels->RespelEstado}}
        </span></p>
        </td>
        <td width= "66%" valign=top style='border-top:none;border-left:none;
        border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;padding:0cm 5.4pt 0cm 5.4pt;
        height:8.05pt'>
        <p class=MsoNormal align=center style='text-align:center'><span lang=ES
        style='font-size:7.5pt;font-family:"Arial",sans-serif;color:#333333'>{{$Residuo->SolResKgConciliado === null ? 'N/A' : $Residuo->SolResKgConciliado }}</span></p>
        </td>
      </tr>
      @if($Residuo->SolResKgConciliado !== null)
      @php
          $totalKg = $totalKg+$Residuo->SolResKgConciliado;
      @endphp
      @endif
      @endif
      @endforeach
      <tr>
        <td width= "170%" style='border:solid gray 0.5pt;border-top:none;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Cantidad Total.</span></b></p>
        </td>
        <td width= "340%" colspan=4 style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>{{$totalKg}}</span></b><b><span lang=ES style='font-size:7.5pt;
        font-family:"Arial",sans-serif;color:#0D0D0D'> kg</span></b></p>
        </td>
      </tr>
      <tr>
        <td  width= "170%" style='border:solid gray 0.5pt;border-top:none;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Mes del Tratamiento:</span></b></p>
        </td>
        <td  width= "340%" colspan=4 style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>
        {{$mesrecepciontexto}} del {{$añorecepcion}}
        </span></p>
        </td>
      </tr>
      <tr style='height:5.3pt'>
        <td  width= "170%" style='border:solid gray 0.5pt;border-top:none;
        padding:0cm 5.4pt 0cm 5.4pt;height:5.3pt'>
        <p class=MsoNormal><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
        color:#0D0D0D'>Observaciones</span></b></p>
        </td>
        <td width="340%" colspan=4 style='border-top:none;border-left:
        none;border-bottom:solid gray 0.5pt;border-right:solid gray 0.5pt;
        padding:0cm 5.4pt 0cm 5.4pt;height:5.3pt'>
        <p class=MsoNormal><strong><span lang=ES style='font-size:7.5pt;font-family:
        "Arial",sans-serif;color:#0D0D0D;background:white;font-weight:normal'>Ninguna.</span></strong></p>
        </td>
      </tr>

      </table>

    </div>

      <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

      <p class=MsoNormal style='margin-left:60.4pt;margin-right:60.4pt;text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Para este proceso se
      registraron temperaturas no menores a 850°C en la Cámara de combustión y
      1.200°C en la cámara de post-combustión. Se utilizaron los sistemas de
      enfriamiento y Depuración de Gases, con los cuales se presentaron emisiones
      atmosféricas dentro de los estándares permisibles, quedando un residuo
      consistente en cenizas calcinadas y dispuestas en un relleno industrial
      autorizado y legalizado.</span></p>

      <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>

      <p class=MsoNormal style='margin-left:60.4pt;margin-right:60.4pt;text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>Todos los procesos
      anteriores se realizaron, ajustados al cumplimiento de las Resoluciones 058 de
      enero 21 de 2002, 0886 de julio 27 de 2004 y la 909 del 06 de junio de 2008 del
      MAVDT y a nuestra Licencia Ambiental, según Resolución No. 3077 de noviembre 7
      de 2006, expedida por la CAR.</span></p>

      <p class=MsoNormal style='text-align:justify'><span lang=ES style='font-size:
      7.5pt;font-family:"Arial",sans-serif;color:#0D0D0D'>&nbsp;</span></p>
      @php
      $fechaActual = new DateTime();

      $fechaModificada = clone $fechaActual;
      $fechaModificada->modify('+8 days');

      $añofirma = $fechaModificada->format('Y');
      $mesfirma = $fechaModificada->format('m');
      $diafirma = $fechaModificada->format('d');
      $mesTexto = "";
      switch ($mesfirma) {
          case 1:
          $mesTexto = 'Enero';
          break;

          case 2:
          $mesTexto = 'Febrero';
          break;

          case 3:
          $mesTexto = 'Marzo';
          break;

          case 4:
          $mesTexto = 'Abril';
          break;

          case 5:
          $mesTexto = 'Mayo';
          break;

          case 6:
          $mesTexto = 'Junio';
          break;

          case 7:
          $mesTexto = 'Julio';
          break;

          case 8:
          $mesTexto = 'Agosto';
          break;

          case 9:
          $mesTexto = 'Septiembre';
          break;

          case 10:
          $mesTexto = 'Octubre';
          break;

          case 11:
          $mesTexto = 'Noviembre';
          break;

          case 12:
          $mesTexto = 'Diciembre';
          break;
          }
      @endphp
      <p class=MsoNormal style='margin-left:95.4pt;text-align:justify;text-indent:
      -35.4pt'><b><span lang=ES style='font-size:7.5pt;font-family:"Arial",sans-serif;
      color:#0D0D0D'>Para constancia se firma en Mosquera, el día {{$diafirma}} de {{$mesTexto}} del {{$añofirma
      }}.</span></b></p>

     </div>

     <div style='margin-left:140.4pt;text-align:center;'>
      <table  border=0 cellspacing=0 cellpadding=0
       style='border-collapse:collapse;border:none' width=20%>
       <tr style='height:8.1pt'width=20%>

          <td style='padding:0cm 0.4pt 0cm 0.4pt;height:8.1pt' width=5%>
             <p class=MsoNormal style='text-align:justify'><span lang=ES><img width=118
              height=110 id="Imagen 6"
              src="@pdfAsset('img/DuvanCampos.png')"></span></p>
          </td>

          <td style='padding:0cm 0.4pt 0cm 0.4pt;height:8.1pt' width=5%>
              <p class=MsoNormal style='text-align:justify'><span lang=ES><img width=110
              height=110 id="Imagen 4"
              src="@pdfAsset('img/FirmaJohn.png')"></span></p>
          </td>

          <td width=100 valign=top style='width:142.4pt;padding:0cm 0.4pt 0cm 0.4pt' width=5%>
            <p class=MsoNormal style='text-align:justify'><span lang=ES><img width=118
            height=110 id="Imagen 5"
            src="@pdfAsset('img/FirmaCesar.png')"></span></p>
         </td>
       </tr>

       <tr style='height:8.1pt'width=20%>
          <td style='padding:0cm 0.4pt 0cm 0.4pt;height:8.1pt' width=10%>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>DUVAN CAMPOS</span></b></p>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>Jefe de Logística</span></b></p>
          </td>

          <td style='padding:0cm 0.4pt 0cm 0.4pt;height:8.1pt' width=10%>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>JOHN JAWER LOPEZ</span></b></p>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>Vo. Bo.</span></b></p>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>Director de Planta</span></b></p>
          </td>

          <td style='padding:0cm 0.4pt 0cm 0.4pt;height:8.1pt' width=10%>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>CESAR BENITO</span></b></p>
            <p class=MsoNormal style='text-align:justify'><b><span lang=ES
            style='font-size:6pt;font-family:"Arial",sans-serif'>Jefe Operaciones</span></b></p>
          </td>
       </tr>
      </table>
      </div>
      <div style="margin-left:80.4pt">
        <table cellpadding="0" cellspacing="0" >
                <div class="invoice-box header-footer">
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td colspan="2">
                        <table>
                          <tr>
                            <td style="text-align: left; font-size: 8px;">
                            <img src="{!! $qrCode->writeDataUri() !!}" style="width: 75px;" alt="" id="inputQrImg"><br>
                            </td>
                          </tr>
                        </table>
                      </td>
                      <td colspan="2" style="vertical-align: bottom;">
                        <table style="margin-left:15.4pt;">
                          <tr>
                            <td style="font-size: 12px; line-height: 11px;"> <b></b>
                              <b>La lectura de este QR lo llevara a: https://sispro.prosarc.com</b><br><br>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </div>
        </table>
      </div>


    </body>
    <br>


    <footer>
      <div class="invoice-box header-footer">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2">
              <table>
                <tr>
                  <td style="text-align: left; font-size: 8px;">
                  </td>
                </tr>
              </table>
            </td>
            <td colspan="2" style="vertical-align: bottom;">
              <table>
                <tr>
                  <td style="text-align: right; font-size: 10px; line-height: 11px;"> <b></b>
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
              </table>
            </td>
          </tr>
        </table>
      </div>
    </footer>


</html>

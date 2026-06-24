<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
  {{-- <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'> --}}
  <title>Cotizacion Numero: {{ $cotizacion->id_cotizacion }} </title>

  <style>
  
    @page {
        size : 612.0pt 792.0pt;
		margin: 0cm 2cm 0cm 2cm;
	}
    /* General styles */
    * {
    box-sizing: border-box;
    }
    @page :first {
    margin: 0; /* No hay márgenes para la primera página */
    }

    .pagina-con-fondo {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{asset("img/pdf1pag.png")}}');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        page-break-after: always;
        margin: 0;
    }
    body{
        margin-top: 3cm;
    }
    /* Header section */
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
    /* Footer section */
    footer {
        margin-top: 0px;
        margin-bottom: 0px;
        width: 100%;
        position: relative; /* Permite que el footer se ubique en su lugar natural */
        bottom: 0;    
        background-color: #ffffff00;
        color: rgb(0, 0, 0);
        }
      /* Right corner icons */
    .logo-container {
        position: absolute;
        top: 11px;
        right: 10px;
        text-align: center;
    } 
    .logo_completo{
        width: 300px;
        height: 100px;
        margin: 0;
        padding: 0;
        margin-top:10px;
    }
    .logo-container img {
        display: inline-block;
        width: 80px;
        margin: -5x 5px 5px;
        margin-bottom: 5px; 
        vertical-align: top;
        margin-right: 25px;
        border-collapse: collapse;                    
    }

    /* Circular Image */
    .image-section {
        margin: 20px 0;
    }
      .servicios img {
        height: 100px;
        width: 100px;
        margin: 0;
        padding: 0;
    } 

    /* Content Section */
    .content {
        margin: 20px 0;
    }
    .content h2 {
        color: #333;
        font-size: 1.2em;
    }
    .content p {
        font-size: 1em;
        margin: 5px 0;
    }
    .content .date {
        font-size: 0.9em;
        color: #666;
    }

    .social-icons {
        margin-top: 10px;
        line-height: 1;
    }
    .social-icons img {
        width: 26px;
        height: 26px;
        margin-right: 8px;
        vertical-align: middle;
        display: inline-block;
    }

    
    /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        
        /* Listas */
    .list { margin: 10px 0 10px 20px; }
    .list li { margin: 5px 0; }
       

        .title2 {
            font-weight: bold;
            text-align: center;
        }
        .note {
            font-style: italic;
        }    
        
        

    .servicios{
        display: flex;
    justify-content: space-between;
    align-items: center;
    }
    h2{
        color: #00b050;
    } 
    li, p{
        color: #344d6c;
    } 
    .section_footer{
        margin-top: 10px;
        line-height: 1.6;
    }
      h4, strong{
        color: #00b050;
    }
    .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

    .tabla-datos th, .tabla-datos td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

    .tabla-datos th {
            background-color: #f2f2f2;
        }

    h2 {
            font-size: 16px;
            margin-top: 20px;
        }
    .main h2{
            font-size:1.9em;
        }
    .main p{
            font-size: 1.4em;
        }
     /* Page Definitions */
     #WordSection1{
          height: 4cm;
          /*margin: 0cm;*/
          margin: 5cm 0.66cm 0cm 0.66cm;
    } 
    

</style>
</head>
<header>
    <div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 20px; border: none; border-collapse: collapse; border-spacing: 0;">
            <tr>
                <td style="border: none; width: 50%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('img/logocompletosinbordes.png') }}" alt="logo completo" style="width: 200px; display: block; margin-left: auto; margin-right: auto;">
                </td>
                <td style="border: none; width: 50%; text-align: center; vertical-align: middle;">
                    <img src="{{ asset('img/bv.png') }}" alt="resolucion" style="width: 150px; height: auto; margin: 10px; display: block; margin-left: auto; margin-right: auto;">
                </td>
            </tr>
        </table>        
    </div>
</header>
<div class="pagina-con-fondo">
    <!-- Header -->
        <div style="position: absolute;top: 20px;left: 30px;">
            <h1 style="font-size: 3em;margin: 0;font-weight: bold;text-decoration: underline;color: #ffffff;">2026</h1>
            <div style="font-size: 3em;font-weight: bold;text-decoration: underline;color: #ffffff;">N-26- {{ $cotizacion->id_cotizacion }}</div>
            <div style="font-size: 2.5em;font-weight: bold; text-decoration: underline; color: #ffffff; text-shadow: 0 2px 6px rgba(0,0,0,0.85); background: rgba(0,0,0,0.25); padding: 6px 10px; border-radius: 6px; display: inline-block;">{{ $cotizacion->Razon_Social }}</div>
            
            <div class="main" style="position: absolute; top: 580px;left: 50px;">  
                <h2>PREPARADA:</h2>
            <p>Asesor Comercial</p>
            <p >{{ $usuario->name }}</p> <!-- usuario logeado -->
            <p>Bogotá D.C</p>
            <p class="date">{{ $cotizacion->FechaCotizacion }}</p>   
                <!-- Social Media Icons -->
                    <div class="social-icons">
                        
                        <img src='{{asset("img/instagram.jpg")}}' alt="Instagram">
                        <img src='{{asset("img/facebok.jpg")}}' alt="Facebook">
                        <img src='{{asset("img/whasapt.jpg")}}' alt="WhatsApp" >
                    </div>
                 <img src='{{asset("img/logocompletosinbordes.png")}}' alt="logo Prosarc" style="width: 220px; height: auto; margin-top: 14px; display: block;">
            </div>    
        </div>
</div>
    
    <body lang=ES-MX>
    <div class="WordSection1">
        <div>
            <p><strong>Fecha:</strong> {{ $cotizacion->FechaCotizacion }}</p>
            <p><strong>NIT:</strong> {{ $cotizacion->Nit }}</p>
            <p><strong>Razón Social:</strong> {{ $cotizacion->Razon_Social }}</p>
            <p><strong>Teléfono:</strong> {{ $cotizacion->Telefono }}</p>
            <p><strong>Correo:</strong> {{ $cotizacion->Correo }}</p>
            <p><strong>Dirección:</strong> {{ $cotizacion->Direccion }}</p>
            <p><strong>Sede:</strong> {{ $cotizacion->sede }}</p>

    <div>
        <h4>De acuerdo con su solicitud relacionada con la cotización para los servicios de Gestión Externa de Residuos nos permitimos presentar a ustedes nuestra oferta económica, no sin antes informarles sobre algunos temas que consideramos importantes para su Empresa:</h4>

        <h2>SERVICIOS PROSARC S.A. ESP</h2>
        <div class="servicios">
            <img src='{{ asset("img/camion.jpeg") }}' alt="camion" style="width: 250px;height: 180px; float: right; margin: 0 0 10px 10px;">    
            <ul>
                <li>Recolección y Transporte de Residuos Peligrosos y no peligrosos. (Industriales y/o Hospitalarios).</li>
                <li>Pre destrucción y Pretratamiento.</li>
                <li>Manejo integral de Residuos por Termo destrucción controlada de residuos Industriales y Hospitalarios.</li>
                <li>Despresurización y Destrucción de Aerosoles.</li>
                <li>Tratamiento de Aguas industriales No Peligrosas</li>
            </ul>
            <br>
        </div>
    </div>
    <div class="servicios">
        <img src='{{asset("img/posos.jpg")}}' alt="posos" style="width: 200px;height: 150px; float: left; margin: 10px 10px 10px 0;">  
            <ul><h2>ALIANZAS ESTRATÉGICAS PARA MANEJO DE</h2>
                <li>Neutralización y Estabilización de Solventes y Sustancias Químicas.</li>
                <li>Tratamiento y Disposición de Aguas Peligrosas.</li>
                <li>Manejo, despiece y disposición final de RAEES.</li>
                <li>Disposición Final en Celdas de Seguridad.</li>
                <li>Manejo de Llantas.</li>
                <li>Compostaje.</li>
                <li>Tratamiento de Aguas industriales Peligrosas y Domésticas.</li>
            </ul>
    </div>
    <div style="clear: both;"></div> 
    <!--<div class="page"> <hr></div>-->

    <div class="servicios">
         <img src="{{ asset('img/celular.jpg') }}" alt="camaras vigilancia" style="width: 200px;height: 200px; float: right; margin-left: 20px; clear: right;">
        <ul>
            <h2>COMPONENTES DEL SERVICIO</h2>
            <li>Flota de Vehículos dotada con ubicación
            satelital y Geo zonas.</li>
            <li>Almacenamiento temporal.</li>
            <li>Precintos de Seguridad.</li>
            <li>Esquema de Seguridad con Tele monitoreo
            24 horas.</li>
            <li>Cámaras en el Furgón monitoreando en
            servicio colectivo.</li>
            <li>Plataformas Hidráulicas de Cargue.</li>
            <li>Procesos y/o procedimientos de Gestión
            Integral</li>
            <li>Báscula Electrónica Calibrada y Certificada.</li>
            <li>VCR ACCESO REMOTO HD.</li>
        
        <img src="{{ asset('img/camaras.jpg') }}" alt="video satelital" style="width: 200px;height: 200px; float: right; margin-left: 20px; margin-bottom: 20px;">
        </ul>
    </div>     

   <h2>VALORES AGREGADOS</h2>
    <ul>
        <li>Asesoría Ambiental y Capacitaciones</li>
        <li>Auditorias presenciales y virtuales</li>
        <li>Almacenamiento de materiales predestrucción.</li>
    </ul>
   <img src='{{asset("img/presentacion.png")}}' alt="posos" style="width: 250px;height: 150px; float: left; margin: 10px 10px 10px 0;">
   <h2>BENEFICIOS</h2>
    <ul>
        <li>Protección de imagen e identidad
        corporativa.</li>
        <li>Minimiza el riesgo por hurtos de
        productos y materias primas.</li>
        <li>Evitar sanciones por parte de
        Autoridades ambientales y sanitarias.</li>
        <li>Mejores Condiciones de Almacenamiento y Asepsia de los Cuartos de Depósito.</li>
    </ul>
   
    
    <div>
        <h2>DOCUMENTACIÓN LEGAL</h2>
        <img src='{{asset("img/secretarias.PNG")}}' alt="logotipos_secretarias" style="width: 100px;height: 100px; float: right; margin-left: 20px; margin-bottom: 20px;">
        <ul>
            <li>Licencia Ambiental N°. 3077 del 7 de noviembre de 2006 expedida por la
            CAR de Cundinamarca.</li>
            <li>Certificado Aclaratorio CAR de Cundinamarca# 2066 del 17 de diciembre de
            2006 de la Licencia 3077 de noviembre de 2006</li>
            <li>Certificado de Bomberos Mosquera Cundinamarca</li>
            <li>Concepto Sanitario Secretaria de Salud Mosquera Cundinamarca.</li>
            <li>Póliza de responsabilidad civil extracontractual exigida por el Decreto 1609
            expedido por Ministerio de Transporte.</li>
            <li>Certificado de Carencia de Informes por Tráfico de Estupefacientes – CCITE
            # 109466 expedido por el Ministerio de Justicia y el Derecho.</li>
            <li>Resolución Secretaria de Ambiente de Bogotá para movilización de Aceites
            # 3733 de 2019.</li>
        </ul>
        
    </div>
    <table width="100%">
        <tr>
            <td valign="top" border="0" cellpadding="0" cellspacing="0"style="margin-bottom: 10px; border: none; border-collapse: collapse; border-spacing: 0;" >
                <h2>EXPERIENCIA PROSARC S.A. ESP</h2>
                <p>Enumeraremos algunos de los clientes más importantes a los cuales nuestra empresa
                presta o ha prestado sus servicios:</p>
            </td>
            <td align="right" style="margin-bottom: 20px; border: none; border-collapse: collapse; border-spacing: 0;">
                <img src="{{ asset('img/clientes.jpg') }}" alt="pc con logos de clientes" style="width: 120px; height: 120px; margin-left: 10px; margin-bottom: 10px;">
            </td>
        </tr>
    </table>
    <h2>OFERTA TÉCNICA - ECONÓMICA POR EL MANEJO, TRATAMIENTO, Y/O DISPOSICIÓN FINAL ADECUADA.</h2>
    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Clasificación 4741</th>
                <th>Peligrosidad</th>
                <th>Tratamiento</th>
                <th>Cantidad (kg)</th>
                <th>Precio por kg</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <!-- Código para mostrar datos del residuo -->
                @foreach($cotizacion->coti_respel as $residuo)
                <tr>
                    <td>{{ $residuo->respel->RespelName ?? 'N/A' }}</td>
                    <td>{{ $residuo->clasf4741 ?? 'N/A' }}</td>
                    <td>{{ $residuo->peligrosidad ?? 'N/A' }}</td>
                    <td>{{ $residuo->tratamiento->TratName ?? 'N/A' }}</td>
                    <td>{{ $residuo->cantidad_kilos ?? 'N/A' }}</td>
                    <td>{{ $residuo->precio_kg ? '$' . number_format($residuo->precio_kg, 2, ',', '.') : 'N/A' }}</td>
                    <td>{{ $residuo->subtotal ? '$' . number_format($residuo->subtotal, 2, ',', '.') : 'N/A' }}</td>
                </tr>
                @endforeach
                </tbody>
    </table>
    @if($cotizacion->Observaciones)
    <div style="margin-top: 20px;">
        <strong>Observaciones:</strong>
        <p>{{ $cotizacion->Observaciones }}</p>
    </div>
    @endif
    <p><strong>Transporte:</strong> {{ $cotizacion->Transporte ? '$' . number_format($cotizacion->Transporte, 2, ',', '.') : 'N/A' }}</p>
    <p><strong>Total:</strong> {{ $cotizacion->Total ? '$' . number_format($cotizacion->Total, 2, ',', '.') : 'N/A' }}</p>
    </div>
    <div style="margin:0">
        <h4>NOTA 1:</h4>
        <p>Queda entendido que el valor total se refiere a la cantidad informada por el Generador. Por lo tanto, cualquier diferencia mayor o menor
        será facturada sobre la base de la cantidad realmente pesada y procesada y con base en la cotización.</p>
        <h4>NOTA 2:</h4>
        <p>Todas nuestras actividades están exentas de IVA.</p>
        <h4>NOTA 3:</h4>
        <p>La asignación de tratamientos para cada residuo, será de acuerdo con la información correspondiente a las hojas de seguridad y/o
        caracterización de los Respel provistas por el cliente.</p>
        

        <h4>NOTA 4:</h4>
        <p>se verifican condiciones de empaque y embalaje de los residuos ..</p>
        <h4>NOTA 5</h4>
        <P>Para los clientes que requieren la devolución de envases, se aclara que esta condición depende su buen estado y sin remanentes
        adheridos en su interior, aclarándolo en cada solicitud de servicio.</P>
        <h4>NOTA 6</h4>
        <P>STAND BYE, el cliente deberá cancelar el valor de $980.000, en caso de que el vehículo se presente en las instalaciones con previa
        programación y por alguna circunstancia ajena a PROSARC no sea atendido, o cuando el vehículo llegue al lugar de recolección con previa
        programación y no sea atendido en rango de dos horas desde el aviso de llegada.</P>
        <h4>NOTA 7</h4>
        <P>el certificado de disposición de entrega en un plazo de 60 días hábiles contra el pago de la factura. El soporte de pago deberá ser
        enviado al correo cuentascorporativas@prosarc.com.co.</P>
        <h2>CERTIFICACIONES</h2>
        <p>Las certificaciones de destrucción y disposición final se expiden numéricamente, en forma consecutiva y son elaboradas en:</p>
        <ul>
            <li style="color:#344d6c"><strong style="color: #344d6c">PAPEL CON TINTA DE SEGURIDAD.</strong>Cuando el cliente <u>no se encuentre</u> efectuando las solicitudes de servicios por la plataforma SISPRO, desarrollada por PROSARC.</li>
            <li><mark style="background-color:#92d050;"> DIGITALES CON CODIGOS QR.</mark> Cuando el cliente adelante las solicitudes de servicios, por la plataforma SISPRO, desarrollada por PROSARC.</li>
        </ul>
        <P>Todo lo anterior para eliminar los riesgos de falsificaciones/adulteraciones, y son entregadas una vez efectuado el pago de facturas</P>
    </div>
    <div><P>Dichas certificaciones son expedidas por las cantidades realmente destruidas, después del reconteo y re-pesaje en nuestra báscula electrónica,firmadas por los Ingenieros, Gerente de Planta, directores de: Logística y Procesos o cualquier otro funcionario autorizado por la Empresa. Así mismo, serán firmadas juntamente con los auditores, en caso de que sean enviados por su empresa.</P></div>

    <H2>CONDICIONES DE PAGO</H2>
    <p>Se contempla crédito a 30 días, fecha factura. Para solicitar crédito debe enviar los documentos cámara de comercio, Rut, copia de la cedula del representante legal, estados financieros de la entidad. El pago se realiza a través de consignación o trasferencia electrónica en cualquiera de los siguientes Bancos a nombre de </p><a href="">PROSARC S.A ESP:</a>

    <title>Información de Pago</title>
    <h2 class="title2">GRUPO AVAL - Banco AV VILLAS 017212200 CUENTA CORRIENTE</h2>

    <p>Una vez se efectúe el depósito favor informarnos o enviarnos copia de la consignación.</p>

    <p class="note"><strong>NOTA 1:</strong> Para Cantidades inferiores a 500kg, no se contempla crédito y en consecuencia se debe realizar el depósito previo a la prestación del servicio.</p>

    <p>Para pago por anticipo debe hacerse directamente a las cuentas autorizadas por PROSARC SA ESP, no son válidas consignaciones de dinero a cuentas personales de ninguno de los funcionarios de la compañía.</p>

    <h2>VALIDEZ DE LA OFERTA:</h2>
    <p>Vigencia de 30 días.</p>

    <p>Cordialmente,</p>

    @php
        $firmaPath = public_path('img/' . $usuario->id . '.png');
        $firmaUrl = file_exists($firmaPath) ? asset('img/' . $usuario->id . '.png') : asset('img/5.png');
    @endphp
    <img src="{{ $firmaUrl }}" alt="Firma" style="width: 100%; display: block; margin-left: auto; margin-right: auto;">
    <br>
    <br>
    <footer>
            <!-- Columna 1 -->
            <div style="width: 30%; float: left; text-align: left; padding-right: 10px;">
                <h3 style="color: red; margin: 0;">SEDE ADMINISTRATIVA Y COMERCIAL</h3>
                <p style="margin: 5px 0;">Calle 120ª No 7 - 62 Of. 605 Bogotá - Colombia</p>
                <p style="margin: 5px 0;">PBX: 629 9853 - 6375112</p>
                <p style="margin: 5px 0;">Servicio al Cliente: 316 439 3895</p>
            </div>
            
            <!-- Columna 2 -->
            <div style="width: 30%; float: left; text-align: left; padding-right: 10px;">
                <h3 style="color: red; margin: 0;">PLANTA DE PROCESOS:</h3>
                <p style="margin: 5px 0;">Km 6 vía la mesa Sub estación</p>
                <p style="margin: 5px 0;">Salsillas (Mosquera - Cund.)</p>
                <p style="margin: 5px 0;">TELEFONO: 742 5395 - 742 5417</p>
                <p style="margin: 5px 0;">CEL: 317 667 3035 - 317 667 3032</p>
            </div>
            
            <!-- Columna 3 -->
            <div style="width: 30%; float: left; text-align: left;">
                <p style="font-size: 14px; color: #800080; margin-top: 30px;"><a href="https://www.prosarc.com.co" style="color: #800080; text-decoration: none;">www.prosarc.com.co</a></p>
            </div>
    </footer>

</div>
</body>
</html>
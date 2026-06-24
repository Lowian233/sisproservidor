<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Fecha de Servicio</th>
                <th>N째 de Servicio</th>
                <th>Estado</th>
                <th>Residuo</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Tratamiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $servicio)
                @foreach($servicio->SolicitudResiduo as $residuo)
                    <tr>
                        <tr>
                        <td>
                            @php
                                $fechaMostrar = null;
                                
                                // 1. PRIORIDAD: Fecha de recepción de la solicitud (ProgVehSalida de programaciones recibidas)
                                if (isset($servicio->programacionesrecibidas) && $servicio->programacionesrecibidas->count() > 0 && $servicio->programacionesrecibidas->first()->ProgVehSalida) {
                                    $fechaMostrar = $servicio->programacionesrecibidas->first()->ProgVehSalida;
                                }
                                // 2. Segunda opción: Fecha de programación realizada
                                elseif (isset($servicio->programacionesrealizadas) && $servicio->programacionesrealizadas->count() > 0 && $servicio->programacionesrealizadas->first()->ProgVehSalida) {
                                    $fechaMostrar = $servicio->programacionesrealizadas->first()->ProgVehSalida;
                                }
                                // 3. Tercera opción: Fecha de salida de la programación
                                elseif ($servicio->ProgVehSalida && $servicio->ProgVehSalida != '0000-00-00 00:00:00') {
                                    $fechaMostrar = $servicio->ProgVehSalida;
                                }
                                // 4. Cuarta opción: Fecha de creación de la solicitud
                                elseif ($servicio->created_at) {
                                    $fechaMostrar = $servicio->created_at;
                                }
                            @endphp
                            
                            @if($fechaMostrar)
                                {{ date('d/m/Y', strtotime($fechaMostrar)) }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $servicio->ID_SolSer }}</td>
                        <td>{{ $servicio->SolSerStatus }}</td>
                        <td>{{ $residuo->generespel->respels->RespelName }}</td>
                        <td>{{ $residuo->SolResKgConciliado ?? $residuo->SolResKgRecibido ?? $residuo->SolResKgEnviado ?? 'N/A' }}</td>
                        <td>{{ $residuo->SolResTypeUnidad }}</td>
                        <td>{{ $residuo->requerimiento->tratamiento->TratName ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html> 
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        .header-row {
            background-color: #D9E1F2;
            font-weight: bold;
        }
        .sub-header {
            background-color: #E7E6E6;
            font-weight: bold;
        }
        .number {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Informe Gerencial de Vehículos</h1>
    <p><strong>Período:</strong> {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}</p>
    <p><strong>Fecha de Generación:</strong> {{ date('d/m/Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr class="header-row">
                <th colspan="8" style="text-align: center; background-color: #4472C4; color: white;">RESUMEN POR VEHÍCULO</th>
            </tr>
            <tr class="sub-header">
                <th>Placa</th>
                <th>Tipo</th>
                <th>Capacidad (kg)</th>
                <th>KM Actual</th>
                <th>Sede</th>
                <th>Total Servicios</th>
                <th>Total Kilos Transportados</th>
                <th>Total KM Recorridos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Vehicles as $Vehicle)
                @if($Vehicle->VehicDelete == 0)
                <tr>
                    <td><strong>{{ $Vehicle->VehicPlaca }}</strong></td>
                    <td>{{ $Vehicle->VehicTipo }}</td>
                    <td class="number">{{ $Vehicle->VehicCapacidad }}</td>
                    <td class="number">{{ number_format($Vehicle->VehicKmActual, 0, ',', '.') }}</td>
                    <td>{{ $Vehicle->SedeName }}</td>
                    <td class="number">{{ $Vehicle->total_servicios ?? 0 }}</td>
                    <td class="number">{{ number_format($Vehicle->total_kilos ?? 0, 2, ',', '.') }}</td>
                    <td class="number">{{ number_format($Vehicle->total_km_recorridos ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <br><br>

    <!-- Servicios por Transportador -->
    <table>
        <thead>
            <tr class="header-row">
                <th colspan="4" style="text-align: center; background-color: #4472C4; color: white;">SERVICIOS POR TRANSPORTADOR</th>
            </tr>
            <tr class="sub-header">
                <th>Placa Vehículo</th>
                <th>Transportador</th>
                <th>Tipo</th>
                <th>Cantidad Servicios</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Vehicles as $Vehicle)
                @if($Vehicle->VehicDelete == 0 && isset($Vehicle->servicios_por_transportador) && count($Vehicle->servicios_por_transportador) > 0)
                    @foreach($Vehicle->servicios_por_transportador as $trans)
                    <tr>
                        <td><strong>{{ $Vehicle->VehicPlaca }}</strong></td>
                        <td>{{ $trans->nombre_conductor }} {{ $trans->apellido_conductor }}</td>
                        <td>{{ $trans->tipo_transportador }}</td>
                        <td class="number">{{ $trans->cantidad_servicios }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <br><br>

    <!-- Kilos por Día -->
    <table>
        <thead>
            <tr class="header-row">
                <th colspan="3" style="text-align: center; background-color: #4472C4; color: white;">KILOS TRANSPORTADOS POR DÍA</th>
            </tr>
            <tr class="sub-header">
                <th>Placa Vehículo</th>
                <th>Fecha</th>
                <th>Kilos Transportados (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Vehicles as $Vehicle)
                @if($Vehicle->VehicDelete == 0 && isset($Vehicle->kilos_por_dia) && count($Vehicle->kilos_por_dia) > 0)
                    @foreach($Vehicle->kilos_por_dia as $kilos)
                    <tr>
                        <td><strong>{{ $Vehicle->VehicPlaca }}</strong></td>
                        <td>{{ date('d/m/Y', strtotime($kilos->fecha)) }}</td>
                        <td class="number">{{ number_format($kilos->total_kilos, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <br><br>

    <!-- Kilometraje Diario -->
    <table>
        <thead>
            <tr class="header-row">
                <th colspan="5" style="text-align: center; background-color: #4472C4; color: white;">KILOMETRAJE DIARIO</th>
            </tr>
            <tr class="sub-header">
                <th>Placa Vehículo</th>
                <th>Fecha</th>
                <th>KM Inicial</th>
                <th>KM Final</th>
                <th>KM Recorridos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Vehicles as $Vehicle)
                @if($Vehicle->VehicDelete == 0 && isset($Vehicle->kilometraje_diario) && count($Vehicle->kilometraje_diario) > 0)
                    @foreach($Vehicle->kilometraje_diario as $km)
                    <tr>
                        <td><strong>{{ $Vehicle->VehicPlaca }}</strong></td>
                        <td>{{ date('d/m/Y', strtotime($km->fecha)) }}</td>
                        <td class="number">{{ number_format($km->km_inicial, 0, ',', '.') }}</td>
                        <td class="number">{{ number_format($km->km_final, 0, ',', '.') }}</td>
                        <td class="number"><strong>{{ number_format($km->km_recorridos, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>
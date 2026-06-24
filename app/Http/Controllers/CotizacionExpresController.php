<?php

namespace App\Http\Controllers;

use App\ClienteExpress;
use App\SedeExpress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CotizacionExpresController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('solicitudes_express as s')
            ->select([
                's.idSolicitud',
                DB::raw('MAX(s.idCliente) as idCliente'),
                DB::raw('MAX(s.localidad) as localidad'),
                DB::raw('MAX(s.estado) as estado'),
                DB::raw('MAX(s.precio) as precio'),
                DB::raw('MAX(s.peso) as peso'),
                DB::raw('GROUP_CONCAT(DISTINCT s.tipoResiduo ORDER BY s.tipoResiduo SEPARATOR ", ") as tiposResiduo'),
                DB::raw('MAX(s.RequiereContrato) as RequiereContrato'),
                DB::raw('MAX(c.nombreEmpresa) as nombreEmpresa'),
                DB::raw('MAX(c.numeroEmpresa) as telefono'),
                DB::raw('MAX(c.ciudadEmpresa) as ciudad'),
                DB::raw('MAX(c.direccion) as direccion'),
                DB::raw('MAX(se.nombreSede) as sede'),
            ])
            ->leftJoin('clientes_express as c', 's.idCliente', '=', 'c.id')
            ->leftJoin('sedes_express as se', 's.idSede', '=', 'se.id')
            ->groupBy('s.idSolicitud')
            ->orderByRaw('CAST(s.idSolicitud AS UNSIGNED) ASC');

        if ($request->filled('nombre')) {
            $query->where('c.nombreEmpresa', 'like', '%' . $request->input('nombre') . '%');
        }

        if ($request->filled('estado')) {
            $query->where('s.estado', $request->input('estado'));
        }

        $solicitudes = $query->paginate(20)->withQueryString();

        $documentosPorCliente = [];
        $idsCliente = $solicitudes->getCollection()
            ->pluck('idCliente')
            ->filter()
            ->unique()
            ->values();

        foreach ($idsCliente as $idCliente) {
            $documentosPorCliente[$idCliente] = $this->contarDocumentosCliente((int) $idCliente);
        }

        return view('cotizacionExpres.index', compact('solicitudes', 'documentosPorCliente'));
    }

    public function create()
    {
        return view('cotizacionExpres.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nit' => 'required|string|max:13',
            'nombreEmpresa' => 'required|string|max:255',
            'numeroEmpresa' => 'nullable|string|max:20',
            'numero_contacto' => 'nullable|string|max:20',
            'ciudadEmpresa' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'correoEmpresa' => 'nullable|email|max:255',
            'nombreRepLegal' => 'nullable|string|max:255',
            'encargado' => 'nullable|string|max:255',
            'identificacionRepLegal' => 'nullable|string|max:20',
            'lugarExpedicion' => 'nullable|string|max:255',
        ]);

        $cliente = ClienteExpress::create($request->only([
            'nit',
            'nombreEmpresa',
            'numeroEmpresa',
            'numero_contacto',
            'ciudadEmpresa',
            'direccion',
            'correoEmpresa',
            'nombreRepLegal',
            'encargado',
            'identificacionRepLegal',
            'lugarExpedicion'
        ]));

        $slug = Str::slug($cliente->nombreEmpresa ?? $cliente->nit) . '-' . $cliente->id;

        return redirect()->route('cotizacion-expres.show', $slug)
            ->with('success', 'Solicitud creada correctamente');
    }

    public function show($id)
    {
        [$cliente, $idSolicitud] = $this->resolverCotizacion($id);

        if (!$cliente && !$idSolicitud) {
            abort(404, 'Cotización no encontrada');
        }

        $slug = $cliente
            ? $this->slugCliente($cliente)
            : 'solicitud-' . $idSolicitud;

        // Redirigir IDs numéricos o slugs antiguos a la URL canónica
        if (is_numeric($id) || $id !== $slug) {
            $url = route('cotizacion-expres.show', $slug);
            if ($cliente && $idSolicitud) {
                $url .= '?solicitud=' . $idSolicitud;
            }
            return redirect()->to($url);
        }

        $solicitudData = $idSolicitud
            ? $this->datosSolicitud($idSolicitud)
            : null;

        // Sedes del cliente (para el panel de administración)
        $sedes = $cliente ? $cliente->sedes()->orderBy('id')->get() : collect();

        $cotizacionExpres = (object) [
            'id'                 => $idSolicitud ?? ($cliente->id ?? null),
            'idSolicitud'        => $idSolicitud,
            'idCliente'          => $cliente->id ?? null,
            'nit'                => $cliente->nit ?? '',
            'razon_social'       => $cliente->nombreEmpresa ?? ('Solicitud #' . $idSolicitud),
            'nombre_solicitante' => $cliente->nombreEmpresa ?? ('Solicitud #' . $idSolicitud),
            'telefono'           => $cliente->numeroEmpresa ?? '',
            'numero_contacto'    => $cliente->numero_contacto ?? '',
            'servicio'           => $solicitudData->tiposResiduo ?? 'Residuos Peligrosos',
            'direccion'          => $cliente->direccion ?? '',
            'ciudad'             => $cliente->ciudadEmpresa ?? '',
            'correo'             => $cliente->correoEmpresa ?? '',
            'encargado'          => $cliente->encargado ?? '',
            'representante_legal'=> $cliente->nombreRepLegal ?? '',
            'documento_identidad'=> $cliente->identificacionRepLegal ?? '',
            'lugar_expedicion'   => $cliente->lugarExpedicion ?? '',
            'franja_horaria'     => '',
            'localidad'          => $solicitudData->localidad ?? ($cliente->localidad ?? ''),
            'peso'               => $solicitudData->peso ?? '',
            'precio'             => $solicitudData->precio ?? '',
            'estado_pago'        => $solicitudData->estado ?? 'Pendiente',
            'fecha_solicitud'    => now(),
            'fecha_recogida'     => now(),
            'slug'               => $slug,
            'sin_cliente'        => $cliente === null,
        ];

        return view('cotizacionExpres.show', compact('cotizacionExpres', 'sedes'));
    }

    public function edit($id)
    {
        return view('cotizacionExpres.edit', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $slug = $id;
        $clienteId = last(explode('-', $slug));
        $cliente = ClienteExpress::findOrFail($clienteId);

        $request->validate([
            'nit' => 'nullable|string|max:13',
            'razon_social' => 'nullable|string|max:255',
            'nombre_solicitante' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'numero_contacto' => 'nullable|string|max:20',
            'correo' => 'nullable|email',
            'encargado' => 'nullable|string|max:255',
            'representante_legal' => 'nullable|string|max:255',
            'documento_identidad' => 'nullable|string|max:20',
            'franja_horaria' => 'nullable|string',
            'ciudad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'lugar_expedicion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
        ]);

        if ($request->has('nit')) {
            $cliente->nit = $request->input('nit');
        }

        if ($request->has('razon_social') || $request->has('nombre_solicitante')) {
            $cliente->nombreEmpresa = $request->input('razon_social') ?? $request->input('nombre_solicitante');
        }

        if ($request->has('telefono')) {
            $cliente->numeroEmpresa = $request->input('telefono');
        }

        if ($request->has('numero_contacto')) {
            $cliente->numero_contacto = $request->input('numero_contacto');
        }

        if ($request->has('correo')) {
            $cliente->correoEmpresa = $request->input('correo');
        }

        if ($request->has('encargado')) {
            $cliente->encargado = $request->input('encargado');
        }

        if ($request->has('representante_legal')) {
            $cliente->nombreRepLegal = $request->input('representante_legal');
        }

        if ($request->has('documento_identidad')) {
            $cliente->identificacionRepLegal = $request->input('documento_identidad');
        }

        if ($request->has('ciudad')) {
            $cliente->ciudadEmpresa = $request->input('ciudad');
        }

        if ($request->has('direccion')) {
            $cliente->direccion = $request->input('direccion');
        }

        if ($request->has('lugar_expedicion')) {
            $cliente->lugarExpedicion = $request->input('lugar_expedicion');
        }

        $cliente->save();

        // Actualizar localidad en solicitudes_express si viene en el request
        if ($request->filled('localidad') && $request->filled('idSolicitud')) {
            DB::table('solicitudes_express')
                ->where('idSolicitud', $request->input('idSolicitud'))
                ->update(['localidad' => $request->input('localidad')]);
        }

        return redirect()->route('cotizacion-expres.show', $slug)
            ->with('success', 'Solicitud actualizada correctamente');
    }

    public function destroy($id)
    {
        return redirect()->route('cotizacion-expres.index');
    }

    public function exportExcel()
    {
        $data = DB::table('solicitudes_express as s')
            ->select([
                's.idSolicitud',
                DB::raw('MAX(c.nit) as nit'),
                DB::raw('MAX(c.nombreEmpresa) as nombreEmpresa'),
                DB::raw('MAX(c.numeroEmpresa) as telefono'),
                DB::raw('GROUP_CONCAT(DISTINCT s.tipoResiduo ORDER BY s.tipoResiduo SEPARATOR ", ") as materiales'),
                DB::raw('MAX(s.peso) as kilos'),
                DB::raw('MAX(c.direccion) as direccion'),
                DB::raw('MAX(s.localidad) as localidad'),
                DB::raw('MAX(s.estado) as estado'),
                DB::raw('MAX(s.precio) as precio'),
            ])
            ->leftJoin('clientes_express as c', 's.idCliente', '=', 'c.id')
            ->groupBy('s.idSolicitud')
            ->orderByRaw('CAST(s.idSolicitud AS UNSIGNED) ASC')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cotizaciones Express');

        $headers = ['No.', 'NIT', 'Nombre', 'Teléfono', 'Material', 'Kilos', 'Localidad', 'Estado', 'Valor Cotización'];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '003366']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '999999']]],
        ];

        $dataStyle = [
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];

        $colWidths = [6, 18, 30, 15, 35, 14, 28, 14, 18];
        $lastCol   = 'I';

        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setWidth($colWidths[$i]);
        }
        $sheet->getRowDimension('1')->setRowHeight(22);
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);

        $rowNum = 2;
        foreach ($data as $index => $row) {
            $direccionLocalidad = trim(
                ($row->direccion ? $row->direccion : '') .
                ($row->direccion && $row->localidad ? ' - ' : '') .
                ($row->localidad ? $row->localidad : '')
            );

            $precio = ($row->precio && $row->precio !== 'Precio no definido')
                ? '$' . number_format((float) $row->precio, 0, ',', '.')
                : '';

            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $row->nit ?? '');
            $sheet->setCellValue('C' . $rowNum, $row->nombreEmpresa ?? '');
            $sheet->setCellValue('D' . $rowNum, $row->telefono ?? '');
            $sheet->setCellValue('E' . $rowNum, $row->materiales ?? '');
            $sheet->setCellValue('F' . $rowNum, $row->kilos ?? '');
            $sheet->setCellValue('G' . $rowNum, $direccionLocalidad);
            $sheet->setCellValue('H' . $rowNum, $row->estado ?? '');
            $sheet->setCellValue('I' . $rowNum, $precio);

            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray($dataStyle);
            $sheet->getRowDimension($rowNum)->setRowHeight(18);

            $rowNum++;
        }

        $sheet->getStyle('A1:' . $lastCol . ($rowNum - 1))->getAlignment()->setWrapText(false);

        if ($rowNum > 2) {
            $sheet->getStyle('E2:E' . ($rowNum - 1))->getAlignment()->setWrapText(true);
        }

        // Guardar copia en temp antes de enviar al navegador
        $timestamp = time();
        $tmpDir = storage_path('app/temp');
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        $tmpPath = $tmpDir . '/reporte_' . $timestamp . '.xlsx';

        $tmpWriter = new Xlsx($spreadsheet);
        $tmpWriter->save($tmpPath);

        $telefono = request('telefono');
        if ($telefono) {
            $numeros = array_map('trim', explode(',', $telefono));
            $this->enviarReportePorWhatsapp($tmpPath, $numeros);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Reporte_Cotizaciones_Express_' . date('Y-m-d') . '.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function enviarReportePorWhatsapp(string $filePath, array $numeros)
    {
        $timestamp = time();
        $fechaActual = date('d/m/Y');
        $mensaje = "Hola! 👋 Aquí está tu reporte de cotizaciones express de PROSCAR con fecha {$fechaActual}.\n"
                 . "Podés consultar tus reportes en: https://proscar.com/reportes\n"
                 . "¡Gracias por confiar en nosotros!";

        foreach ($numeros as $numero) {
            try {
                $response = Http::withToken(config('services.wati.token'))
                    ->post(config('services.wati.endpoint') . '/api/v2/sendTemplateMessage?whatsappNumber=' . $numero, [
                        'template_name' => 'envio_reporte_excel',
                        'broadcast_name' => 'reporte_' . $timestamp,
                    ]);

                if (!$response->successful()) {
                    Log::warning('Wati template fallido', [
                        'numero' => $numero,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                sleep(1);

                $response = Http::withToken(config('services.wati.token'))
                    ->attach('file', file_get_contents($filePath), 'reporte.xlsx')
                    ->post(config('services.wati.endpoint') . '/api/v1/sendSessionFile/' . $numero, [
                        'caption' => $mensaje,
                    ]);

                if (!$response->successful()) {
                    Log::warning('Wati archivo fallido', [
                        'numero' => $numero,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Wati error', [
                    'numero' => $numero,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function enviarReporte(Request $request)
    {
        $numeros = array_filter(array_map('trim', $request->input('numeros', [])));

        if (empty($numeros)) {
            return response()->json(['error' => 'Ingresa al menos un número'], 422);
        }

        // Generar el Excel en memoria
        $data = DB::table('solicitudes_express as s')
            ->select([
                's.idSolicitud',
                DB::raw('MAX(c.nit) as nit'),
                DB::raw('MAX(c.nombreEmpresa) as nombreEmpresa'),
                DB::raw('MAX(c.numeroEmpresa) as telefono'),
                DB::raw('GROUP_CONCAT(DISTINCT s.tipoResiduo ORDER BY s.tipoResiduo SEPARATOR ", ") as materiales'),
                DB::raw('MAX(s.peso) as kilos'),
                DB::raw('MAX(c.direccion) as direccion'),
                DB::raw('MAX(s.localidad) as localidad'),
                DB::raw('MAX(s.estado) as estado'),
                DB::raw('MAX(s.precio) as precio'),
            ])
            ->leftJoin('clientes_express as c', 's.idCliente', '=', 'c.id')
            ->groupBy('s.idSolicitud')
            ->orderByRaw('CAST(s.idSolicitud AS UNSIGNED) ASC')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cotizaciones Express');

        $headers = ['No.', 'NIT', 'Nombre', 'Teléfono', 'Material', 'Kilos', 'Localidad', 'Estado', 'Valor Cotización'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '1', $h);
        }

        foreach ($data as $index => $row) {
            $r = $index + 2;
            $loc = trim(($row->direccion ?? '') . ($row->direccion && $row->localidad ? ' - ' : '') . ($row->localidad ?? ''));
            $precio = ($row->precio && $row->precio !== 'Precio no definido') ? '$' . number_format((float)$row->precio, 0, ',', '.') : '';

            $sheet->setCellValue('A' . $r, $index + 1);
            $sheet->setCellValue('B' . $r, $row->nit ?? '');
            $sheet->setCellValue('C' . $r, $row->nombreEmpresa ?? '');
            $sheet->setCellValue('D' . $r, $row->telefono ?? '');
            $sheet->setCellValue('E' . $r, $row->materiales ?? '');
            $sheet->setCellValue('F' . $r, $row->kilos ?? '');
            $sheet->setCellValue('G' . $r, $loc);
            $sheet->setCellValue('H' . $r, $row->estado ?? '');
            $sheet->setCellValue('I' . $r, $precio);
        }

        $tmpDir  = storage_path('app/temp');
        if (!file_exists($tmpDir)) mkdir($tmpDir, 0755, true);
        $tmpPath = $tmpDir . '/reporte_envio_' . time() . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpPath);

        $resultados = [];
        $this->enviarReportePorWhatsapp($tmpPath, $numeros);

        foreach ($numeros as $numero) {
            $resultados[] = ['numero' => $numero, 'enviado' => true];
        }

        @unlink($tmpPath);

        Log::info('Reporte enviado por WhatsApp', ['numeros' => $numeros]);

        return response()->json([
            'ok'         => true,
            'message'    => 'Reporte enviado a ' . count($numeros) . ' número(s)',
            'resultados' => $resultados,
        ]);
    }

    public function documentos($slug)
    {
        if (is_numeric($slug)) {
            $cliente = ClienteExpress::find($slug);
            if ($cliente) {
                return redirect()->route('cotizacion-expres.documentos', $this->slugCliente($cliente));
            }
            abort(404, 'Cliente no encontrado');
        }

        $id = last(explode('-', $slug));
        $cliente = ClienteExpress::findOrFail($id);

        $razonSocial = $cliente->nombreEmpresa;
        $cotizacionExpres = (object) [
            'id' => $cliente->id,
            'nombre_solicitante' => $cliente->nombreEmpresa,
            'telefono' => $cliente->numeroEmpresa ?? '',
            'servicio' => 'Residuos Peligrosos',
            'direccion' => $cliente->ciudadEmpresa ?? '',
            'fecha_solicitud' => now(),
            'fecha_recogida' => now(),
            'estado_pago' => 'Pendiente',
            'slug' => Str::slug($cliente->nombreEmpresa) . '-' . $cliente->id,
        ];

        return view('cotizacionExpres.documentos', compact('cotizacionExpres', 'razonSocial', 'slug'));
    }

    public function historialDocumentos(Request $request, $slug)
    {
        if (is_numeric($slug)) {
            $cliente = ClienteExpress::find($slug);
            if ($cliente) {
                return redirect()->route('cotizacion-expres.historial-documentos', $this->slugCliente($cliente));
            }
            abort(404, 'Cliente no encontrado');
        }

        $id      = last(explode('-', $slug));
        $cliente = ClienteExpress::findOrFail($id);

        $razonSocial = $cliente->nombreEmpresa;
        $documentos  = $this->listarDocumentosCliente((int) $id, $request->input('fecha'));

        $documentosPorMes = $documentos->groupBy(function ($doc) {
            return $doc->fechaCarga->format('Y-m');
        });

        return view('cotizacionExpres.historialDocumentos', compact('razonSocial', 'slug', 'cliente', 'documentos', 'documentosPorMes'));
    }

    public function cargarDocumento(Request $request, $slug)
    {
        $id = last(explode('-', $slug));
        $cliente = ClienteExpress::findOrFail($id);

        $request->validate([
            'nombreDoc' => 'required|string|max:255',
            'archivoDoc' => 'required|file|mimes:pdf|max:10240',
            'tipoDoc' => 'nullable|string|max:100'
        ]);

        // Crear carpeta organizando por cliente/año/mes
        $ahora = now();
        $año = $ahora->year;
        $mes = str_pad($ahora->month, 2, '0', STR_PAD_LEFT);
        $carpeta = public_path('documentos/' . $id . '/' . $año . '/' . $mes);
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        // Guardar archivo
        $archivo = $request->file('archivoDoc');
        $nombreArchivo = time() . '_' . Str::slug($request->input('nombreDoc')) . '.pdf';
        $ruta = $archivo->move($carpeta, $nombreArchivo);
        $rutaRelativa = 'documentos/' . $id . '/' . $año . '/' . $mes . '/' . $nombreArchivo;

        // Guardar en BD
        $documento = \App\DocumentoExpres::create([
            'idClienteExpress' => $id,
            'nombreDocumento' => $request->input('nombreDoc'),
            'rutaDocumento' => $rutaRelativa,
            'tipoDocumento' => $request->input('tipoDoc') ?? 'Documento',
            'usuarioOrigen' => auth()->user()->name ?? 'Sistema',
            'fechaCarga' => now()
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Documento cargado correctamente',
            'documento' => $documento
        ], 201);
    }

    // ============================================================
    // Sedes del cliente express
    // ============================================================

    public function sedes($slug)
    {
        $cliente = $this->clienteDesdeSlug($slug);

        return response()->json([
            'ok'    => true,
            'sedes' => $cliente->sedes()->orderBy('id')->get(),
        ]);
    }

    public function storeSede(Request $request, $slug)
    {
        $cliente = $this->clienteDesdeSlug($slug);

        $data = $request->validate([
            'nombreSede' => 'required|string|max:255',
            'direccion'  => 'nullable|string|max:255',
            'localidad'  => 'nullable|string|max:255',
        ]);

        $data['idClienteExpress'] = $cliente->id;
        $sede = SedeExpress::create($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Sede creada correctamente',
            'sede'    => $sede,
        ], 201);
    }

    public function updateSede(Request $request, $slug, $idSede)
    {
        $cliente = $this->clienteDesdeSlug($slug);
        $sede    = SedeExpress::where('idClienteExpress', $cliente->id)->findOrFail($idSede);

        $data = $request->validate([
            'nombreSede' => 'required|string|max:255',
            'direccion'  => 'nullable|string|max:255',
            'localidad'  => 'nullable|string|max:255',
        ]);

        $sede->update($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Sede actualizada correctamente',
            'sede'    => $sede,
        ]);
    }

    public function destroySede($slug, $idSede)
    {
        $cliente = $this->clienteDesdeSlug($slug);
        $sede    = SedeExpress::where('idClienteExpress', $cliente->id)->findOrFail($idSede);
        $sede->delete();

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Sede eliminada correctamente',
        ]);
    }

    private function clienteDesdeSlug($slug)
    {
        $clienteId = last(explode('-', $slug));
        return ClienteExpress::findOrFail($clienteId);
    }

    private function slugCliente(ClienteExpress $cliente): string
    {
        return Str::slug($cliente->nombreEmpresa ?? $cliente->nit) . '-' . $cliente->id;
    }

    /**
     * Resuelve cliente e idSolicitud desde id numérico (idSolicitud o idCliente)
     * o desde slug. Si no se indica solicitud, usa la última disponible del cliente.
     */
    private function resolverCotizacion($id): array
    {
        $cliente     = null;
        $idSolicitud = null;

        if (is_numeric($id)) {
            $numId = (int) $id;

            $solicitudRow = DB::table('solicitudes_express')
                ->where('idSolicitud', $numId)
                ->first();

            if ($solicitudRow) {
                $idSolicitud = $numId;
                if ($solicitudRow->idCliente) {
                    $cliente = ClienteExpress::find($solicitudRow->idCliente);
                }
            } else {
                // Sin solicitud con ese número: interpretar como id de cliente
                $cliente = ClienteExpress::find($numId);
                if ($cliente) {
                    $idSolicitud = $this->ultimaSolicitudCliente($cliente->id);
                }
            }
        } elseif (preg_match('/^solicitud-(\d+)$/', $id, $matches)) {
            $idSolicitud = (int) $matches[1];
            $solicitudRow = DB::table('solicitudes_express')
                ->where('idSolicitud', $idSolicitud)
                ->first();

            if (!$solicitudRow) {
                return [null, null];
            }

            if ($solicitudRow->idCliente) {
                $cliente = ClienteExpress::find($solicitudRow->idCliente);
            }
        } else {
            $clienteId = last(explode('-', $id));
            $cliente   = ClienteExpress::find($clienteId);

            if ($cliente) {
                $solicitudParam = request()->query('solicitud');
                if ($solicitudParam && is_numeric($solicitudParam)) {
                    $idSolicitud = (int) $solicitudParam;
                } else {
                    $idSolicitud = $this->ultimaSolicitudCliente($cliente->id);
                }
            }
        }

        return [$cliente, $idSolicitud];
    }

    private function ultimaSolicitudCliente(int $idCliente): ?int
    {
        $id = DB::table('solicitudes_express')
            ->where('idCliente', $idCliente)
            ->orderByRaw('CAST(idSolicitud AS UNSIGNED) DESC')
            ->value('idSolicitud');

        return $id ? (int) $id : null;
    }

    private function datosSolicitud(int $idSolicitud): ?object
    {
        return DB::table('solicitudes_express')
            ->where('idSolicitud', $idSolicitud)
            ->select([
                DB::raw('MAX(localidad) as localidad'),
                DB::raw('MAX(estado) as estado'),
                DB::raw('MAX(precio) as precio'),
                DB::raw('MAX(peso) as peso'),
                DB::raw('GROUP_CONCAT(DISTINCT tipoResiduo ORDER BY tipoResiduo SEPARATOR ", ") as tiposResiduo'),
            ])
            ->first();
    }

    private function contarDocumentosCliente(int $idCliente): int
    {
        return $this->listarDocumentosCliente($idCliente)->count();
    }

    /**
     * Lista documentos del cliente desde disco y desde la tabla documentos_cotizacionesExpress.
     */
    private function listarDocumentosCliente(int $idCliente, ?string $fechaFiltro = null): \Illuminate\Support\Collection
    {
        $documentos = collect();
        $vistos     = [];

        $carpetaBase = public_path('documentos/' . $idCliente);
        if (file_exists($carpetaBase)) {
            $archivos = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($carpetaBase, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($archivos as $archivo) {
                if (!$archivo->isFile()) {
                    continue;
                }

                $doc = $this->documentoDesdeArchivo($archivo, $fechaFiltro);
                if ($doc) {
                    $vistos[$doc->rutaRelativa] = true;
                    $documentos->push($doc);
                }
            }
        }

        try {
            $registrosBd = \App\DocumentoExpres::where('idClienteExpress', $idCliente)->get();
            foreach ($registrosBd as $registro) {
                $rutaRelativa = str_replace('\\', '/', ltrim($registro->rutaDocumento, '/'));
                if (isset($vistos[$rutaRelativa])) {
                    continue;
                }

                $rutaAbsoluta = public_path($rutaRelativa);
                if (!file_exists($rutaAbsoluta)) {
                    continue;
                }

                $fechaCarga = $registro->fechaCarga
                    ? \Carbon\Carbon::parse($registro->fechaCarga)
                    : \Carbon\Carbon::createFromTimestamp(filemtime($rutaAbsoluta));

                if ($fechaFiltro && !$this->fechaCoincideFiltro($fechaCarga, $fechaFiltro)) {
                    continue;
                }

                $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
                $documentos->push((object) [
                    'nombre'        => $registro->nombreDocumento ?: basename($rutaAbsoluta),
                    'extension'     => $ext,
                    'tamaño'        => round(filesize($rutaAbsoluta) / 1024, 1) . ' KB',
                    'fechaCarga'    => $fechaCarga,
                    'url'           => asset($rutaRelativa),
                    'rutaRelativa'  => $rutaRelativa,
                ]);
                $vistos[$rutaRelativa] = true;
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo consultar documentos_cotizacionesExpress', [
                'idCliente' => $idCliente,
                'error'     => $e->getMessage(),
            ]);
        }

        return $documentos->sortByDesc('fechaCarga')->values();
    }

    private function documentoDesdeArchivo(\SplFileInfo $archivo, ?string $fechaFiltro = null): ?object
    {
        $ext = strtolower($archivo->getExtension());
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            return null;
        }

        $fechaCarga = \Carbon\Carbon::createFromTimestamp($archivo->getMTime());
        if ($fechaFiltro && !$this->fechaCoincideFiltro($fechaCarga, $fechaFiltro)) {
            return null;
        }

        $rutaRelativa = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $archivo->getRealPath());
        $rutaRelativa = str_replace('\\', '/', $rutaRelativa);

        return (object) [
            'nombre'       => $archivo->getFilename(),
            'extension'    => $ext,
            'tamaño'       => round($archivo->getSize() / 1024, 1) . ' KB',
            'fechaCarga'   => $fechaCarga,
            'url'          => asset($rutaRelativa),
            'rutaRelativa' => $rutaRelativa,
        ];
    }

    private function fechaCoincideFiltro(\Carbon\Carbon $fechaCarga, string $fechaFiltro): bool
    {
        try {
            $filtro = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFiltro)->startOfDay();
            return $fechaCarga->isSameDay($filtro);
        } catch (\Exception $e) {
            return true;
        }
    }
}

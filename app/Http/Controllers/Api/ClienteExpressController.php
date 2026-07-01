<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ClienteExpress;
use App\SedeExpress;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteExpressController extends Controller
{
    private function formatearCliente($cliente)
    {
        // Devuelve "No definido" cuando el campo viene vacío o nulo
        $valor = fn ($v) => ($v === null || $v === '') ? 'No definido' : $v;

        return [
            'id' => $cliente->id,
            'nit' => $cliente->nit,
            'nombreEmpresa' => $valor($cliente->nombreEmpresa),
            'ciudadEmpresa' => $valor($cliente->ciudadEmpresa),
            'direccion' => $valor($cliente->direccion),
            'numeroEmpresa' => $valor($cliente->numeroEmpresa),
            'numero_contacto' => $valor($cliente->numero_contacto),
            'correoEmpresa' => $valor($cliente->correoEmpresa),
            'nombreRepLegal' => $valor($cliente->nombreRepLegal),
            'encargado' => $valor($cliente->encargado),
            'identificacionRepLegal' => $valor($cliente->identificacionRepLegal),
            'lugarExpedicion' => $valor($cliente->lugarExpedicion),
            'localidad' => $valor($cliente->localidad),
        ];
    }

    public function verificarNit(Request $request, $nit = null)
    {
        $nit = $nit ?? $request->query('nit');

        if (!$nit) {
            return response()->json(['error' => 'El parámetro NIT es requerido'], 400);
        }

        $cliente = ClienteExpress::where('nit', $nit)->first();

        if ($cliente) {
            $sedes = $this->formatearSedes($cliente);

            return response()->json([
                'existe'      => true,
                'cliente'     => $this->formatearCliente($cliente),
                'datosRepre'              => $this->datosRepreVacios($cliente),
                'datosRepreIncompletos'   => $this->datosRepreIncompletos($cliente),
                'tieneSedes'  => $sedes->isNotEmpty(),
                'totalSedes'  => $sedes->count(),
                'sedes_texto' => $sedes->map(function ($s) {
                    $texto = $s['opcion'] . '. ' . ($s['nombreSede'] ?: 'Sede');
                    if ($s['direccion']) $texto .= ' - ' . $s['direccion'];
                    if ($s['localidad']) $texto .= ' (' . $s['localidad'] . ')';
                    return $texto;
                })->implode("\n"),
                'sedes'       => $sedes->values(),
            ]);
        }

        return response()->json([
            'existe' => false,
            'mensaje' => 'Cliente no encontrado',
        ]);
    }

    /**
     * Indica si los datos del representante legal estan vacios (No definido).
     * true = sin datos reales, false = tiene datos reales.
     */
    private function datosRepreVacios($cliente): bool
    {
        $campos = [$cliente->nombreRepLegal, $cliente->identificacionRepLegal, $cliente->lugarExpedicion];
        foreach ($campos as $campo) {
            if ($campo !== null && $campo !== '' && $campo !== 'No definido') {
                return false;
            }
        }
        return true;
    }

    /**
     * Indica si ALGUN dato del representante legal esta incompleto (vacio/No definido).
     * true = al menos un campo vacio, false = todos los campos tienen datos reales.
     */
    private function datosRepreIncompletos($cliente): bool
    {
        $campos = [
            $cliente->nombreRepLegal,
            $cliente->identificacionRepLegal,
            $cliente->lugarExpedicion,
        ];
        foreach ($campos as $campo) {
            if ($campo === null || $campo === '' || $campo === 'No definido') {
                return true;
            }
        }
        return false;
    }

    /**
     * Sedes del cliente enumeradas (opcion 1..N) para que Wati las muestre
     * sin tener que indexar arrays.
     */
    private function formatearSedes($cliente)
    {
        return $cliente->sedes()
            ->orderBy('id')
            ->get()
            ->values()
            ->map(function ($sede, $i) {
                return [
                    'opcion'     => $i + 1,
                    'id'         => $sede->id,
                    'nombreSede' => $sede->nombreSede,
                    'direccion'  => $sede->direccion,
                    'localidad'  => $sede->localidad,
                ];
            });
    }

    /**
     * Resuelve la elección del usuario (número de opción) a una sede concreta.
     * GET /api/v1/clientes/{idCliente}/sede?opcion=2
     */
    public function sede(Request $request, $idCliente)
    {
        $cliente = ClienteExpress::find($idCliente);

        if (!$cliente) {
            return response()->json(['ok' => false, 'mensaje' => 'Cliente no encontrado'], 404);
        }

        $opcion = (int) ($request->query('opcion') ?? $request->input('opcion'));
        $sedes  = $this->formatearSedes($cliente);
        $sede   = $sedes->firstWhere('opcion', $opcion);

        if (!$sede) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Opción de sede no válida',
            ], 422);
        }

        return response()->json([
            'ok'   => true,
            'sede' => $sede,
        ]);
    }

    /**
     * Crea una sede para un cliente (para Wati: caso "el usuario indica una
     * nueva ubicación por chat").
     * POST /api/v1/clientes/{idCliente}/sede
     * Body: { nombreSede (requerido), direccion, localidad }
     */
    public function crearSede(Request $request, $idCliente)
    {
        $cliente = ClienteExpress::find($idCliente);

        if (!$cliente) {
            return response()->json(['ok' => false, 'mensaje' => 'Cliente no encontrado'], 404);
        }

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
            'sede'    => [
                'id'         => $sede->id,
                'nombreSede' => $sede->nombreSede,
                'direccion'  => $sede->direccion,
                'localidad'  => $sede->localidad,
            ],
        ], 201);
    }

    private function camposActualizables(): array
    {
        return [
            'nombreEmpresa', 'ciudadEmpresa', 'direccion', 'numeroEmpresa',
            'numero_contacto', 'correoEmpresa', 'nombreRepLegal', 'encargado',
            'identificacionRepLegal', 'lugarExpedicion', 'localidad',
        ];
    }

    private function reglasCliente(?int $exceptId = null): array
    {
        $nitRule = 'nullable|string|unique:clientes_express,nit';
        if ($exceptId !== null) {
            $nitRule .= ',' . $exceptId;
        }

        return [
            'nit'                    => $nitRule,
            'nombreEmpresa'          => 'nullable|string|max:255',
            'ciudadEmpresa'          => 'nullable|string|max:255',
            'direccion'              => 'nullable|string|max:255',
            'numeroEmpresa'          => 'nullable|string|max:20',
            'numero_contacto'        => 'nullable|string|max:20',
            'correoEmpresa'          => 'nullable|email|max:255',
            'nombreRepLegal'         => 'nullable|string|max:255',
            'encargado'              => 'nullable|string|max:255',
            'identificacionRepLegal' => 'nullable|string|max:20',
            'lugarExpedicion'        => 'nullable|string|max:255',
            'localidad'              => 'nullable|string|max:255',
        ];
    }

    /** Solo campos presentes en el body de la petición (parcial, como el upsert por NIT). */
    private function datosEnviados(Request $request, array $campos): array
    {
        $datos = [];
        foreach ($campos as $campo) {
            if ($request->has($campo)) {
                $datos[$campo] = $request->input($campo);
            }
        }
        return $datos;
    }

    /**
     * Upsert por NIT:
     * - Si el NIT ya existe -> actualiza SOLO los campos que llegan (todo opcional).
     *   Útil para casos donde solo se manda, p.ej., el representante legal + cédula.
     * - Si el NIT no existe -> crea el cliente (requiere nit + nombreEmpresa).
     */
    public function store(Request $request)
    {
        $nit     = $request->input('nit');
        $cliente = $nit ? ClienteExpress::where('nit', $nit)->first() : null;

        // Campos que el cliente puede tener (sin contar el nit)
        $campos = $this->camposActualizables();

        // ===== UPDATE: el NIT ya existe =====
        if ($cliente) {
            $request->validate($this->reglasCliente($cliente->id));

            // Solo actualiza los campos que realmente vinieron en la petición
            $datos = $this->datosEnviados($request, $campos);

            if (!empty($datos)) {
                $cliente->update($datos);
            }

            return response()->json([
                'creado'      => false,
                'actualizado' => true,
                'cliente'     => $this->formatearCliente($cliente),
            ], 200);
        }

        // ===== El NIT no existe =====
        // Si NO viene nombreEmpresa, es una actualización (no un alta): no exigir
        // todos los campos de creación; avisar con claridad que el NIT no existe.
        if (!$request->filled('nombreEmpresa')) {
            return response()->json([
                'creado'      => false,
                'actualizado' => false,
                'mensaje'     => 'No existe un cliente con ese NIT. Para crear uno nuevo debes enviar también "nombreEmpresa".',
                'nit'         => $nit,
            ], 404);
        }

        // ===== CREATE: el NIT no existe y sí viene nombreEmpresa =====
        $validated = $request->validate([
            'nit'                    => 'required|string|unique:clientes_express',
            'nombreEmpresa'          => 'required|string|max:255',
            'ciudadEmpresa'          => 'nullable|string|max:255',
            'direccion'              => 'nullable|string|max:255',
            'numeroEmpresa'          => 'nullable|string|max:20',
            'numero_contacto'        => 'nullable|string|max:20',
            'correoEmpresa'          => 'nullable|email|max:255',
            'nombreRepLegal'         => 'nullable|string|max:255',
            'encargado'              => 'nullable|string|max:255',
            'identificacionRepLegal' => 'nullable|string|max:20',
            'lugarExpedicion'        => 'nullable|string|max:255',
            'localidad'              => 'nullable|string|max:255',
        ]);

        $cliente = ClienteExpress::create($validated);

        if (!$cliente) {
            return response()->json([
                'creado' => false,
                'mensaje' => 'No se pudo crear el cliente',
            ], 500);
        }

        return response()->json([
            'creado' => true,
            'cliente' => $this->formatearCliente($cliente),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $cliente = ClienteExpress::findOrFail($id);

        $request->validate($this->reglasCliente((int) $id));

        $campos = $this->camposActualizables();
        if ($request->has('nit')) {
            $campos = array_merge(['nit'], $campos);
        }

        $datos = $this->datosEnviados($request, $campos);

        if (!empty($datos)) {
            $cliente->update($datos);
        }

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'cliente' => $this->formatearCliente($cliente->fresh()),
        ]);
    }

    public function show($id)
    {
        $cliente = ClienteExpress::findOrFail($id);

        return response()->json([
            'cliente' => $this->formatearCliente($cliente),
        ]);
    }

    public function index()
    {
        $clientes = ClienteExpress::all();

        return response()->json([
            'total' => $clientes->count(),
            'clientes' => $clientes->map(function ($cliente) {
                return $this->formatearCliente($cliente);
            }),
        ]);
    }
}

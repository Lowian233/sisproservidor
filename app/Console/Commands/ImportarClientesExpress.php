<?php

namespace App\Console\Commands;

use App\ClienteExpress;
use App\SedeExpress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarClientesExpress extends Command
{
    protected $signature = 'clientes-express:import
                            {archivo : Ruta al archivo CSV o Excel (.xlsx/.xls)}
                            {--dry-run : Simula la importación sin guardar en BD}
                            {--update : Actualiza registros existentes por NIT (por defecto solo inserta nuevos)}
                            {--crear-sedes : Crea sede en sedes_express si la fila trae columna sede/nombreSede}
                            {--hoja=0 : Índice de la hoja Excel (0 = primera)}';

    protected $description = 'Importa clientes desde CSV/Excel a la tabla clientes_express';

    /** @var array<string, list<string>> */
    private array $aliasColumnas = [
        'nit' => ['nit', 'n.i.t', 'identificacion', 'identificación', 'documento', 'cedula', 'cédula', 'id', 'numero documento'],
        'nombreEmpresa' => ['nombreempresa', 'nombre empresa', 'razon social', 'razón social', 'empresa', 'nombre', 'cliente', 'nombre cliente'],
        'direccion' => ['direccion', 'dirección', 'address', 'dir', 'direccion empresa'],
        'ciudadEmpresa' => ['ciudadempresa', 'ciudad empresa', 'ciudad', 'municipio', 'city'],
        'numeroEmpresa' => ['numeroempresa', 'numero empresa', 'telefono', 'teléfono', 'tel', 'celular', 'phone', 'fijo'],
        'numero_contacto' => ['numerocontacto', 'numero contacto', 'celular contacto', 'telefono contacto', 'tel contacto'],
        'correoEmpresa' => ['correoempresa', 'correo empresa', 'correo', 'email', 'e-mail', 'mail'],
        'encargado' => ['encargado', 'contacto', 'nombre contacto', 'persona contacto', 'nombre encargado'],
        'nombreRepLegal' => ['nombrereplegal', 'nombre replegal', 'representante legal', 'rep legal', 'representante'],
        'identificacionRepLegal' => ['identificacionreplegal', 'cc replegal', 'cedula replegal', 'cédula replegal', 'documento replegal'],
        'lugarExpedicion' => ['lugarexpedicion', 'lugar expedicion', 'lugar expedición', 'expedicion', 'expedición'],
        'localidad' => ['localidad', 'barrio', 'zona'],
        'nombreSede' => ['nombresede', 'nombre sede', 'sede'],
    ];

    public function handle(): int
    {
        $ruta = $this->argument('archivo');

        if (!is_file($ruta)) {
            $rutaAlterna = base_path($ruta);
            if (is_file($rutaAlterna)) {
                $ruta = $rutaAlterna;
            } else {
                $rutaStorage = storage_path('app/importaciones/' . basename($ruta));
                if (is_file($rutaStorage)) {
                    $ruta = $rutaStorage;
                }
            }
        }

        if (!is_file($ruta)) {
            $this->error("No se encontró el archivo: {$this->argument('archivo')}");

            $carpetaImportaciones = storage_path('app/importaciones');
            if (is_dir($carpetaImportaciones)) {
                $archivos = array_values(array_filter(scandir($carpetaImportaciones) ?: [], function ($nombre) use ($carpetaImportaciones) {
                    return $nombre !== '.' && $nombre !== '..' && is_file($carpetaImportaciones . DIRECTORY_SEPARATOR . $nombre);
                }));

                if (!empty($archivos)) {
                    $this->line('Archivos disponibles en storage/app/importaciones/:');
                    foreach ($archivos as $archivo) {
                        $this->line("  - {$archivo}");
                    }
                }
            }

            $this->line('Use el nombre exacto del archivo, por ejemplo:');
            $this->line('  php artisan clientes-express:import storage/app/importaciones/Libro1.xlsx');
            return 1;
        }

        if (!class_exists(\ZipArchive::class)) {
            $this->error('La extensión PHP "zip" no está habilitada. Los archivos .xlsx la requieren.');
            $this->line('Opciones:');
            $this->line('  1. Ejecute el comando con el PHP de Laragon (que sí tiene zip habilitado).');
            $this->line('  2. Exporte el Excel a CSV y vuelva a intentar.');
            $this->line('  3. En php.ini active: extension=zip');
            return 1;
        }

        $this->info("Leyendo archivo: {$ruta}");

        try {
            $filas = $this->leerArchivo($ruta);
        } catch (\Throwable $e) {
            $this->error('Error al leer el archivo: ' . $e->getMessage());
            return 1;
        }

        if (empty($filas)) {
            $this->warn('El archivo no contiene filas de datos.');
            return 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $update = (bool) $this->option('update');
        $crearSedes = (bool) $this->option('crear-sedes');

        $creados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $errores = 0;
        $sedesCreadas = 0;

        $bar = $this->output->createProgressBar(count($filas));
        $bar->start();

        $procesar = function () use (
            $filas, $dryRun, $update, $crearSedes,
            &$creados, &$actualizados, &$omitidos, &$errores, &$sedesCreadas, $bar
        ) {
            foreach ($filas as $numFila => $fila) {
                $bar->advance();

                $datos = $this->mapearFila($fila);
                $nit = ClienteExpress::normalizarNit($datos['nit'] ?? '');
                $nombre = trim((string) ($datos['nombreEmpresa'] ?? ''));

                if ($nit === '' || $nombre === '') {
                    $omitidos++;
                    $this->newLine();
                    $this->warn("Fila " . ($numFila + 2) . ": omitida (falta NIT o nombre de empresa).");
                    continue;
                }

                unset($datos['nit'], $datos['nombreEmpresa']);
                $datos = $this->limpiarDatos($datos);

                $nombreSede = trim((string) ($datos['nombreSede'] ?? ''));
                unset($datos['nombreSede']);

                try {
                    $existente = ClienteExpress::where('nit', $nit)->first();

                    if ($existente) {
                        if (!$update) {
                            $omitidos++;
                            continue;
                        }

                        if (!$dryRun) {
                            $existente->update(array_merge(['nombreEmpresa' => $nombre], $datos));
                        }
                        $actualizados++;
                        $clienteId = $existente->id;
                    } else {
                        if (!$dryRun) {
                            $cliente = ClienteExpress::create(array_merge([
                                'nit' => $nit,
                                'nombreEmpresa' => $nombre,
                            ], $datos));
                            $clienteId = $cliente->id;
                        } else {
                            $clienteId = null;
                        }
                        $creados++;
                    }

                    if ($crearSedes && $nombreSede !== '' && !$dryRun && $clienteId) {
                        $sedeExiste = SedeExpress::where('idClienteExpress', $clienteId)
                            ->where('nombreSede', $nombreSede)
                            ->exists();

                        if (!$sedeExiste) {
                            SedeExpress::create([
                                'idClienteExpress' => $clienteId,
                                'nombreSede' => $nombreSede,
                                'direccion' => $datos['direccion'] ?? null,
                                'localidad' => $datos['localidad'] ?? null,
                            ]);
                            $sedesCreadas++;
                        }
                    }
                } catch (\Throwable $e) {
                    $errores++;
                    $this->newLine();
                    $this->error("Fila " . ($numFila + 2) . " (NIT {$nit}): " . $e->getMessage());
                }
            }
        };

        if ($dryRun) {
            $procesar();
        } else {
            DB::transaction($procesar);
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Creados', $creados],
                ['Actualizados', $actualizados],
                ['Omitidos', $omitidos],
                ['Errores', $errores],
                ['Sedes creadas', $sedesCreadas],
            ]
        );

        if ($dryRun) {
            $this->warn('Modo simulación (--dry-run): no se guardó nada en la base de datos.');
        }

        return $errores > 0 ? 1 : 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function leerArchivo(string $ruta): array
    {
        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->leerCsv($ruta);
        }

        $hoja = (int) $this->option('hoja');
        $spreadsheet = IOFactory::load($ruta);
        $worksheet = $spreadsheet->getSheet($hoja);
        $matriz = $worksheet->toArray(null, true, true, false);

        if (empty($matriz)) {
            return [];
        }

        $encabezados = array_map(fn ($h) => $this->normalizarEncabezado((string) $h), array_shift($matriz));
        $filas = [];

        foreach ($matriz as $fila) {
            if ($this->filaVacia($fila)) {
                continue;
            }

            $asociativa = [];
            foreach ($encabezados as $i => $encabezado) {
                if ($encabezado === '') {
                    continue;
                }
                $asociativa[$encabezado] = $fila[$i] ?? null;
            }
            $filas[] = $asociativa;
        }

        return $filas;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function leerCsv(string $ruta): array
    {
        $handle = fopen($ruta, 'r');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        $primeraLinea = fgets($handle);
        if ($primeraLinea === false) {
            fclose($handle);
            return [];
        }

        $delimitador = substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',') ? ';' : ',';
        rewind($handle);

        $encabezadosRaw = fgetcsv($handle, 0, $delimitador);
        if ($encabezadosRaw === false) {
            fclose($handle);
            return [];
        }

        $encabezados = array_map(fn ($h) => $this->normalizarEncabezado((string) $h), $encabezadosRaw);
        $filas = [];

        while (($fila = fgetcsv($handle, 0, $delimitador)) !== false) {
            if ($this->filaVacia($fila)) {
                continue;
            }

            $asociativa = [];
            foreach ($encabezados as $i => $encabezado) {
                if ($encabezado === '') {
                    continue;
                }
                $asociativa[$encabezado] = $fila[$i] ?? null;
            }
            $filas[] = $asociativa;
        }

        fclose($handle);

        return $filas;
    }

    /**
     * @param array<string, mixed> $fila
     * @return array<string, mixed>
     */
    private function mapearFila(array $fila): array
    {
        $normalizada = [];
        foreach ($fila as $columna => $valor) {
            $normalizada[$this->normalizarEncabezado((string) $columna)] = $valor;
        }

        $resultado = [];
        foreach ($this->aliasColumnas as $campo => $alias) {
            foreach ($alias as $nombre) {
                if (array_key_exists($nombre, $normalizada) && $this->tieneValor($normalizada[$nombre])) {
                    $resultado[$campo] = trim((string) $normalizada[$nombre]);
                    break;
                }
            }
        }

        // Si no hay encabezados reconocidos, asumir orden fijo de columnas
        if (!isset($resultado['nit']) && !isset($resultado['nombreEmpresa'])) {
            $valores = array_values($fila);
            $resultado['nombreEmpresa'] = trim((string) ($valores[0] ?? ''));
            $resultado['nit'] = trim((string) ($valores[1] ?? ''));
            $resultado['direccion'] = trim((string) ($valores[2] ?? ''));
            $resultado['ciudadEmpresa'] = trim((string) ($valores[3] ?? ''));
            $resultado['numeroEmpresa'] = trim((string) ($valores[4] ?? ''));
            $resultado['encargado'] = trim((string) ($valores[5] ?? ''));
        }

        return $resultado;
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    private function limpiarDatos(array $datos): array
    {
        $permitidos = [
            'direccion', 'ciudadEmpresa', 'numeroEmpresa', 'numero_contacto',
            'correoEmpresa', 'nombreRepLegal', 'encargado',
            'identificacionRepLegal', 'lugarExpedicion', 'localidad',
        ];

        $limpio = [];
        foreach ($permitidos as $campo) {
            if (!empty($datos[$campo])) {
                $limpio[$campo] = $datos[$campo];
            }
        }

        return $limpio;
    }

    private function normalizarEncabezado(string $texto): string
    {
        $texto = trim(mb_strtolower($texto));
        $texto = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $texto);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return $texto;
    }

    /**
     * @param array<int, mixed> $fila
     */
    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $celda) {
            if ($this->tieneValor($celda)) {
                return false;
            }
        }

        return true;
    }

    private function tieneValor($valor): bool
    {
        return $valor !== null && trim((string) $valor) !== '';
    }
}

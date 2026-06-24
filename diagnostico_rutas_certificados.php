<?php
// Script para diagnosticar problemas de rutas de certificados express
// Ejecutar desde la raíz del proyecto Laravel

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\CertificadoExpress;

echo "=== DIAGNÓSTICO DE RUTAS DE CERTIFICADOS EXPRESS ===\n\n";

// 1. Verificar configuración de rutas
echo "1. CONFIGURACIÓN DE RUTAS:\n";
echo "   APP_URL: " . env('APP_URL') . "\n";
echo "   Public path: " . public_path() . "\n";
echo "   Storage path: " . storage_path() . "\n";

echo "\n";

// 2. Verificar directorios
echo "2. VERIFICACIÓN DE DIRECTORIOS:\n";
$directorios = [
    'public/img/Certificados/',
    'public/img/Manifiestos/',
    'public/img/CertificadosEXT/',
    'public/CertificadosExpress/',
    'storage/app/public/certificadoExpress/',
    'storage/app/public/'
];

foreach ($directorios as $dir) {
    if (is_dir($dir)) {
        echo "   ✅ {$dir} - Existe\n";
        $archivos = glob($dir . '*.pdf');
        echo "      📄 Archivos PDF: " . count($archivos) . "\n";
        if (count($archivos) > 0) {
            $ejemplos = array_slice($archivos, 0, 3);
            foreach ($ejemplos as $archivo) {
                echo "         - " . basename($archivo) . "\n";
            }
        }
    } else {
        echo "   ❌ {$dir} - No existe\n";
    }
}

echo "\n";

// 3. Verificar certificados y sus rutas
echo "3. VERIFICACIÓN DE CERTIFICADOS Y RUTAS:\n";
$certificados = CertificadoExpress::where('CertSrc', 'LIKE', 'E-%')
    ->orderBy('ID_Cert', 'desc')
    ->limit(5)
    ->get();

foreach ($certificados as $cert) {
    echo "   ID: {$cert->ID_Cert}\n";
    echo "   CertSrc: {$cert->CertSrc}\n";
    
    // Verificar en diferentes ubicaciones
    $ubicaciones = [
        'public/img/Certificados/' . $cert->CertSrc,
        'public/img/Manifiestos/' . $cert->CertSrc,
        'public/img/CertificadosEXT/' . $cert->CertSrc,
        'public/CertificadosExpress/' . $cert->CertSrc,
        'storage/app/public/certificadoExpress/' . $cert->CertSrc,
    ];
    
    $encontrado = false;
    foreach ($ubicaciones as $ubicacion) {
        if (file_exists($ubicacion)) {
            echo "   ✅ Archivo encontrado en: {$ubicacion}\n";
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        echo "   ❌ Archivo NO encontrado en ninguna ubicación\n";
    }
    
    echo "\n";
}

// 4. Verificar configuración de storage
echo "4. CONFIGURACIÓN DE STORAGE:\n";
echo "   Storage link existe: " . (is_link('public/storage') ? '✅ Sí' : '❌ No') . "\n";
echo "   Storage link apunta a: " . (is_link('public/storage') ? readlink('public/storage') : 'N/A') . "\n";

// 5. Verificar permisos
echo "\n5. VERIFICACIÓN DE PERMISOS:\n";
foreach ($directorios as $dir) {
    if (is_dir($dir)) {
        $permisos = fileperms($dir);
        $escribible = is_writable($dir) ? '✅ Escribible' : '❌ No escribible';
        $leible = is_readable($dir) ? '✅ Leíble' : '❌ No leíble';
        echo "   {$dir}: {$escribible}, {$leible}\n";
    }
}

// 6. Generar URLs de ejemplo
echo "\n6. URLs DE EJEMPLO:\n";
if ($certificados->count() > 0) {
    $cert = $certificados->first();
    $baseUrl = env('APP_URL');
    
    echo "   Base URL: {$baseUrl}\n";
    echo "   URL ejemplo 1: {$baseUrl}/storage/certificadoExpress/{$cert->CertSrc}\n";
    echo "   URL ejemplo 2: {$baseUrl}/img/Certificados/{$cert->CertSrc}\n";
    echo "   URL ejemplo 3: {$baseUrl}/img/Manifiestos/{$cert->CertSrc}\n";
    echo "   URL ejemplo 4: {$baseUrl}/img/CertificadosEXT/{$cert->CertSrc}\n";
}

echo "\n=== FIN DE DIAGNÓSTICO ===\n";
?>

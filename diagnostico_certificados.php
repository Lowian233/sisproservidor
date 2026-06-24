<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Certificado;
use App\SolicitudServicio;

echo "=== DIAGNÓSTICO DE CERTIFICADOS ===\n\n";

echo "Total de certificados en DB: " . Certificado::count() . "\n";
echo "Certificados sin archivo (CertSrc vacío): " . Certificado::whereNull('CertSrc')->orWhere('CertSrc', '')->count() . "\n";
echo "Certificados con archivo: " . Certificado::whereNotNull('CertSrc')->where('CertSrc', '!=', '')->count() . "\n\n";

// Solicitudes en estado Conciliado o Certificacion sin certificados generados
$solicitudesSinCerts = SolicitudServicio::whereIn('SolSerStatus', ['Conciliado', 'Certificacion'])
    ->whereDoesntHave('certificados', function($query) {
        $query->whereNotNull('CertSrc')->where('CertSrc', '!=', '');
    })
    ->count();

echo "Solicitudes en estado Conciliado/Certificacion sin certificados PDF: " . $solicitudesSinCerts . "\n\n";

// Mostrar los últimos 10 certificados creados
echo "Últimos 10 certificados creados:\n";
$ultimos = Certificado::orderBy('created_at', 'desc')->take(10)->get();
foreach($ultimos as $cert) {
    echo "ID: {$cert->ID_Cert}, SolSer: {$cert->FK_CertSolser}, Src: {$cert->CertSrc}, Fecha: {$cert->created_at}\n";
}
?>

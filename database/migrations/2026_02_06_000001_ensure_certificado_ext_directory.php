<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * Asegura que exista el directorio certificadoExt en storage/public.
 * Al ejecutar php artisan migrate, se crea automáticamente en producción.
 */
class EnsureCertificadoExtDirectory extends Migration
{
    public function up()
    {
        Storage::disk('public')->makeDirectory('certificadoExt');
    }

    public function down()
    {
        // No eliminar el directorio en rollback (podría contener archivos)
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firmas_servicio', function (Blueprint $table) {
            $table->bigIncrements('ID_Firmas');            
            $table->unsignedBigInteger('FK_SolSer')->nullable();
            $table->unsignedBigInteger('FK_Gener')->nullable();
            $table->string('FirmaCliente')->unique();
            $table->string('FirmaConductor')->unique();
            $table->string('FirmaPDA')->unique();
            $table->string('SlugFirmas')->unique();
            $table->string('NombreFuncionario')->unique();
            $table->int('Cedula')->unique();
            $table->string('Observaciones')->unique();
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmas_servicio');
    }
};

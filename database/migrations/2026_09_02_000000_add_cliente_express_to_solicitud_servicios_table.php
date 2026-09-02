<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud_servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitud_servicios', 'FK_Cliente_Express')) {
                $table->unsignedBigInteger('FK_Cliente_Express')->nullable()->after('FK_SolSerCliente');
                $table->foreign('FK_Cliente_Express')->references('id')->on('clientes_express')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_servicios', function (Blueprint $table) {
            if (Schema::hasColumn('solicitud_servicios', 'FK_Cliente_Express')) {
                $table->dropForeign(['FK_Cliente_Express']);
                $table->dropColumn('FK_Cliente_Express');
            }
        });
    }
};

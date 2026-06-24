<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_express', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes_express', 'idSede')) {
                $table->unsignedBigInteger('idSede')->default(0)->after('idCliente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_express', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_express', 'idSede')) {
                $table->dropColumn('idSede');
            }
        });
    }
};

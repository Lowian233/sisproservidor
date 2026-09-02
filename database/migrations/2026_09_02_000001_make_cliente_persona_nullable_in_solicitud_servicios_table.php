<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El flujo express (CreateSolicitudExpressService) no tiene un ID_Cli/ID_Pers
 * real al cual apuntar, asi que estas FK deben admitir NULL.
 */
return new class extends Migration
{
    /**
     * Nombre de la constraint de FK, si existe actualmente en la base de datos.
     */
    private function foreignKeyExists(string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'solicitud_servicios')
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    public function up(): void
    {
        $teniaFkCliente = $this->foreignKeyExists('solicitud_servicios_fk_solsercliente_foreign');
        $teniaFkPersona = $this->foreignKeyExists('solicitud_servicios_fk_solserpersona_foreign');

        Schema::table('solicitud_servicios', function ($table) use ($teniaFkCliente, $teniaFkPersona) {
            if ($teniaFkCliente) {
                $table->dropForeign('solicitud_servicios_fk_solsercliente_foreign');
            }
            if ($teniaFkPersona) {
                $table->dropForeign('solicitud_servicios_fk_solserpersona_foreign');
            }
        });

        DB::statement('ALTER TABLE `solicitud_servicios` MODIFY `FK_SolSerCliente` int unsigned NULL');
        DB::statement('ALTER TABLE `solicitud_servicios` MODIFY `FK_SolSerPersona` int unsigned NULL');
        DB::statement("ALTER TABLE `solicitud_servicios` MODIFY `SolServMailCopia` json NULL");

        Schema::table('solicitud_servicios', function ($table) {
            $table->foreign('FK_SolSerCliente')->references('ID_Cli')->on('clientes')->onDelete('set null');
            $table->foreign('FK_SolSerPersona')->references('ID_Pers')->on('personals')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_servicios', function ($table) {
            if ($this->foreignKeyExists('solicitud_servicios_fk_solsercliente_foreign')) {
                $table->dropForeign(['FK_SolSerCliente']);
            }
            if ($this->foreignKeyExists('solicitud_servicios_fk_solserpersona_foreign')) {
                $table->dropForeign(['FK_SolSerPersona']);
            }
        });

        DB::statement('ALTER TABLE `solicitud_servicios` MODIFY `FK_SolSerCliente` int unsigned NOT NULL');
        DB::statement('ALTER TABLE `solicitud_servicios` MODIFY `FK_SolSerPersona` int unsigned NOT NULL');
        DB::statement("ALTER TABLE `solicitud_servicios` MODIFY `SolServMailCopia` json NOT NULL");

        Schema::table('solicitud_servicios', function ($table) {
            $table->foreign('FK_SolSerCliente')->references('ID_Cli')->on('clientes');
            $table->foreign('FK_SolSerPersona')->references('ID_Pers')->on('personals');
        });
    }
};

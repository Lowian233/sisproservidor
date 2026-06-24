<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class CotizacionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
public function up()
{
    Schema::create('coti_respel', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('cotizacion_id');
        $table->unsignedBigInteger('FK_ID_Respel');
        $table->string('FK_Tratamiento');
        $table->decimal('cantidad_kilos', 8, 2);
        $table->decimal('precio_kg', 8, 2);
        $table->decimal('subtotal', 10, 2);
        $table->string('peligrosidad');
        $table->string('clasf4741');
        $table->string('estado_fisico');
        $table->timestamps();

        // Clave foránea para cotizacion_id
        $table->foreign('cotizacion_id')->references('id_cotizacion')->on('cotizacion')->onDelete('cascade');

        // Clave foránea para FK_ID_Respel
        $table->foreign('FK_ID_Respel')->references('ID_Respel')->on('respels')->onDelete('restrict')->onUpdate('cascade');

        // Clave foránea para FK_Tratamiento (suponiendo que referencia a TratName en tratamientos y que TratName es único)
        $table->foreign('FK_Tratamiento')->references('TratName')->on('tratamientos')->onDelete('restrict')->onUpdate('cascade');
    });
}
}
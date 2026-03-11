<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // KITA BUAT TABELNYA LAGI DISINI
        Schema::create('plts_generations', function (Blueprint $table) {
            $table->id();
            $table->date('record_date');      // Tanggal Data
            $table->string('plant_name');     // Nama Area (Kantor Pusat, dll)
            $table->double('kwh_generated')->default(0); // Jumlah Kwh
            $table->string('phase_group')->nullable();   // Phase 1 / Phase 2
            $table->string('source_name')->nullable();   // Info Import/Manual
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plts_generations');
    }
};
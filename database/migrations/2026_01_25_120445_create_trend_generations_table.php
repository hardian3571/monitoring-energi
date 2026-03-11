<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trend_generations', function (Blueprint $table) {
            $table->id();
            $table->date('record_date'); // Tanggal Data (YYYY-MM-DD)
            
            // Kolom Data (Sesuai file Excel)
            $table->double('kwh_spu')->default(0); // Kolom Incoming SPU
            $table->double('kwh_ss5')->default(0); // Kolom Incoming SS-5
            
            $table->string('source_file')->nullable(); // Nama file asal (opsional)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trend_generations');
    }
};
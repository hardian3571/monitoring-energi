<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListrikSs4sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 public function up()
{
    Schema::create('listrik_ss4s', function (Blueprint $table) {
        $table->id();
        $table->date('record_date'); 
        
     
        $table->double('kwh_pkt')->default(0); 
        $table->double('kwh_kdm')->default(0); 
        
        $table->string('source_name')->nullable();
        $table->timestamps();
    });
}
}

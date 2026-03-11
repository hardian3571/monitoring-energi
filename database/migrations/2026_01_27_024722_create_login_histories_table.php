<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable(); // Browser/Device
            $table->timestamp('login_at');
        });
    }
    public function down() { Schema::dropIfExists('login_histories'); }
};
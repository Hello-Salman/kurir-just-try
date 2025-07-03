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
        Schema::create('kirims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kurir')->references('id')->on('kurirs')->onDelete('cascade');
            $table->foreignId('id_order')->references('id')->on('orders')->onDelete('cascade');
            $table->enum('status', ['Dikirim', 'Terkirim'])->default('Dikirim');
            $table->integer('rating')->default(0);
            $table->string('ulasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kirims');
    }
};

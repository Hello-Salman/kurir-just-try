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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ekspedisi'); // jne / tiki / pos
            $table->string('jenis_layanan')->nullable();
            $table->integer('biaya')->nullable();
            // Relasi lokasi
            $table->foreignId('asal_provinsi_id')->constrained('provinces');
            $table->foreignId('asal_kota_id')->constrained('cities');
            $table->string('alamat_asal');
            $table->foreignId('tujuan_provinsi_id')->constrained('provinces');
            $table->foreignId('tujuan_kota_id')->constrained('cities');
            $table->string('alamat_tujuan');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        });
    }
};

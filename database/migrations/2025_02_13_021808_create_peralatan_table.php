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
        Schema::create('peralatan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("nama_alat");
            $table->string("merk")->nullable();
            $table->string("tipe")->nullable();
            $table->string("kondisi")->nullable();
            $table->integer("tahun_pengadaan")->nullable();
            $table->boolean('monitoring_tidak')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->integer('sort')->nullable();
            $table->string("image")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peralatan');
    }
};

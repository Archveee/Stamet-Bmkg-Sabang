<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gts_monitoring', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->json('sinoptik_late_hours')->nullable();
            $table->json('sinoptik_missing_hours')->nullable();
            $table->json('sinoptik_invalid_hours')->nullable();
            $table->json('metar_late_hours')->nullable();
            $table->json('metar_missing_hours')->nullable();
            $table->json('metar_invalid_hours')->nullable();
            $table->json('taf_late_hours')->nullable();
            $table->json('taf_missing_hours')->nullable();
            $table->json('taf_invalid_hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gts_monitoring');
    }
};

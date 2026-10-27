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
        Schema::create('data_aws', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->timestamp('data_timestamp')->nullable();
            $table->integer('wind_dir_deg_dd')->nullable();
            $table->float('wind_speed_ff')->nullable();
            $table->float('wind_speed_ff_maks')->nullable();
            $table->float('pressure_qfe_mb_derived')->nullable();
            $table->float('pressure_qff_mb_derived')->nullable();
            $table->float('temp_drybulb_c_tttttt')->nullable();
            $table->float('temp_wetbulb_c')->nullable();
            $table->float('temp_dewpoint_c_tdtdtd')->nullable();
            $table->integer('relative_humidity_pc')->nullable();
            $table->float('rainfall_last_mm')->nullable();
            $table->float('temp_min_c_txtxtx')->nullable();
            $table->float('temp_max_c_tntntn')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_aws');
    }
};

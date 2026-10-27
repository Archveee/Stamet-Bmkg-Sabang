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
        Schema::create('data_awos', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->timestamp('data_timestamp')->nullable();
            $table->float('wind_spd_2m_kt')->nullable();
            $table->integer('wind_dir_2m')->nullable();
            $table->float('wind_gustav_10m_kt')->nullable();
            $table->float('wind_spdx_2m_kt')->nullable();
            $table->float('wind_spd_10m_kt')->nullable();
            $table->integer('wind_dir_10m')->nullable();
            $table->integer('wind_dirccw_10m')->nullable();
            $table->integer('wind_dircw_10m')->nullable();
            $table->float('wind_peak_10m_kt')->nullable();
            $table->float('at_5m_c')->nullable();
            $table->float('dp_5m_c')->nullable();
            $table->float('rh_5m')->nullable();
            $table->float('sr_6m_j_mncm2')->nullable();
            $table->float('qnh_1m_mb')->nullable();
            $table->float('qfe_1m_mb')->nullable();
            $table->float('bplow_1m_mb')->nullable();
            $table->float('pp_1h_mm')->nullable();
            $table->float('pp_6h_mm')->nullable();
            $table->float('sr_6m_w_m2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_awos');
    }
};

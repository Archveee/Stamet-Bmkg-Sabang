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
        Schema::create('data_sinoptik', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_timestamp')->nullable();
            $table->string('cloud_cover_oktas_m')->nullable();
            $table->string('cloud_low_type_cl')->nullable();
            $table->string('cloud_med_type_cm')->nullable();
            $table->string('cloud_high_type_ch')->nullable();
            $table->string('cloud_low_cover_oktas')->nullable();
            $table->string('cloud_med_cover_oktas')->nullable();
            $table->string('cloud_high_cover_oktas')->nullable();
            $table->string('cloud_layer_1_type_c')->nullable();
            $table->integer('cloud_layer_1_height_m_hshs')->nullable();
            $table->integer('cloud_layer_1_amt_oktas_ns')->nullable();
            $table->string('cloud_layer_2_type_c')->nullable();
            $table->integer('cloud_layer_2_height_m_hshs')->nullable();
            $table->integer('cloud_layer_2_amt_oktas_ns')->nullable();
            $table->string('cloud_layer_3_type_c')->nullable();
            $table->integer('cloud_layer_3_height_m_hshs')->nullable();
            $table->integer('cloud_layer_3_amt_oktas_ns')->nullable();
            $table->string('cloud_layer_4_type_c')->nullable();
            $table->integer('cloud_layer_4_height_m_hshs')->nullable();
            $table->integer('cloud_layer_4_amt_oktas_ns')->nullable();
            $table->float('evaporation_24hours_mm_eee')->nullable();
            $table->integer('evaporation_eq_indicator_ie')->nullable();
            $table->integer('land_cond')->nullable();
            $table->string('land_note')->nullable();
            $table->integer('past_weather_w1')->nullable();
            $table->integer('past_weather_w2')->nullable();
            $table->integer('present_weather_ww')->nullable();
            $table->float('pressure_reading_mb')->nullable();
            $table->float('pressure_temp_c')->nullable();
            $table->float('koreksi_pressure_qff_mb_derived')->nullable();
            $table->float('koreksi_pressure_qfe_mb_derived')->nullable();
            $table->float('rainfall_24h_rrrr')->nullable();
            $table->float('rainfall_6h_rrr')->nullable();
            $table->integer('rainfall_indicator_ir')->nullable();
            $table->float('rainfall_last_mm')->nullable();
            $table->float('solar_rad_24h_jcm2_f24')->nullable();
            $table->float('sunshine_h_sss')->nullable();
            $table->float('temp_dewpoint_c_tdtdtd')->nullable();
            $table->float('temp_drybulb_c_tttttt')->nullable();
            $table->float('temp_max_c_txtxtx')->nullable();
            $table->float('temp_min_c_tntntn')->nullable();
            $table->float('temp_wetbulb_c')->nullable();
            $table->integer('visibility_vv')->nullable();
            $table->integer('weather_indicator_ix')->nullable();
            $table->integer('wind_dir_deg_dd')->nullable();
            $table->integer('wind_indicator_iw')->nullable();
            $table->float('wind_speed_ff')->nullable();
            $table->integer('relative_humidity_pc')->nullable();
            $table->float('pressure_qff_mb_derived')->nullable();
            $table->float('pressure_qfe_mb_derived')->nullable();
            $table->float('pressure_tend_3h_mb_ppp')->nullable();
            $table->float('pressure_tend_24h_mb_ppp')->nullable();
            $table->string('cloud_low_type_1')->nullable();
            $table->string('cloud_low_type_2')->nullable();
            $table->string('cloud_low_type_3')->nullable();
            $table->string('cloud_med_type_1')->nullable();
            $table->string('cloud_med_type_2')->nullable();
            $table->string('cloud_high_type_1')->nullable();
            $table->string('cloud_high_type_2')->nullable();
            $table->integer('cloud_low_base_1')->nullable();
            $table->integer('cloud_low_base_2')->nullable();
            $table->integer('cloud_low_base_3')->nullable();
            $table->integer('cloud_med_base_1')->nullable();
            $table->integer('cloud_med_base_2')->nullable();
            $table->integer('cloud_high_base_1')->nullable();
            $table->integer('cloud_high_base_2')->nullable();
            $table->integer('cloud_low_peak_1')->nullable();
            $table->integer('cloud_low_peak_2')->nullable();
            $table->integer('cloud_low_cover_1')->nullable();
            $table->integer('cloud_low_cover_2')->nullable();
            $table->integer('cloud_low_cover_3')->nullable();
            $table->integer('cloud_med_cover_1')->nullable();
            $table->integer('cloud_med_cover_2')->nullable();
            $table->integer('cloud_high_cover_1')->nullable();
            $table->integer('cloud_high_cover_2')->nullable();
            $table->integer('cloud_low_dir_1')->nullable();
            $table->integer('cloud_low_dir_2')->nullable();
            $table->integer('cloud_low_dir_3')->nullable();
            $table->integer('cloud_med_dir_1')->nullable();
            $table->integer('cloud_med_dir_2')->nullable();
            $table->integer('cloud_high_dir_1')->nullable();
            $table->integer('cloud_high_dir_2')->nullable();
            $table->integer('cloud_elevation_angle_ec_1')->nullable();
            $table->integer('cloud_elevation_angle_ec_2')->nullable();
            $table->integer('cloud_low_dir_true_da_1')->nullable();
            $table->integer('cloud_low_dir_true_da_2')->nullable();
            $table->integer('cloud_vertical_vis')->nullable();
            $table->text('encoded_synop')->nullable();
            $table->integer('observer_id')->nullable();
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sinoptik');
    }
};

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
        Schema::create('data_aws_maritim', function (Blueprint $table) {
            $table->id();
            $table->string('idaws')->nullable();
            $table->timestamp('waktu')->nullable();
            $table->float('windspeed')->nullable();
            $table->float('winddir')->nullable();
            $table->float('temp')->nullable();
            $table->float('rh')->nullable();
            $table->float('pressure')->nullable();
            $table->float('rain')->nullable();
            $table->float('solrad')->nullable();
            $table->float('netrad')->nullable();
            $table->float('watertemp')->nullable();
            $table->float('waterlevel')->nullable();
            $table->float('ta_min')->nullable();
            $table->float('ta_max')->nullable();
            $table->float('pancilevel')->nullable();
            $table->float('pancitemp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_aws_maritim');
    }
};

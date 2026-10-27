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
        Schema::create('gts_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type_message')->nullable();
            $table->text('sandi_gts')->nullable();
            $table->timestamp('timestamp_data')->nullable();
            $table->timestamp('timestamp_sent_data')->nullable();
            $table->boolean('is_sent')->default(0)->nullable();
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
        Schema::dropIfExists('gts_messages');
    }
};

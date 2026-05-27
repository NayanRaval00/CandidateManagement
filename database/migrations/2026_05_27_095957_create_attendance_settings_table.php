<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8)->default(23.02250000);
            $table->decimal('longitude', 11, 8)->default(72.57140000);
            $table->integer('radius')->default(100); // in meters
            $table->integer('min_punch_out_delay')->default(30); // in minutes
            $table->time('punch_in_start')->nullable();
            $table->time('punch_in_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};

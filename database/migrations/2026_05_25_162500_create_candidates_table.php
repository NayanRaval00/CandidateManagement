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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('position')->nullable();
            $table->string('mobile')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('resume')->nullable();
            $table->string('current_company_name')->nullable();
            $table->string('current_position')->nullable();
            $table->string('education')->nullable();
            $table->decimal('current_ctc', 10, 2)->nullable();
            $table->decimal('expected_ctc', 10, 2)->nullable();
            $table->text('reason_for_job_change')->nullable();
            $table->string('notice_period')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

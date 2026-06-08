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
        Schema::create('timesheet_batches', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft, approved, dispatched
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('timesheet_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('timesheet_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_calendar_days');
            $table->integer('expected_working_days');
            $table->integer('days_worked');
            $table->integer('leaves_count');
            $table->integer('holidays_count');
            $table->integer('late_count');
            $table->decimal('total_hours', 8, 2);
            $table->string('formatted_hours');
            $table->json('daily_breakdown_json');
            $table->json('late_logs_json');
            $table->timestamps();
        });

        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('approved');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('timesheet_records');
        Schema::dropIfExists('timesheet_batches');
    }
};

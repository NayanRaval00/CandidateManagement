<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'batch_id',
        'user_id',
        'total_calendar_days',
        'expected_working_days',
        'days_worked',
        'leaves_count',
        'holidays_count',
        'late_count',
        'total_hours',
        'formatted_hours',
        'daily_breakdown_json',
        'late_logs_json',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_calendar_days' => 'integer',
            'expected_working_days' => 'integer',
            'days_worked' => 'integer',
            'leaves_count' => 'integer',
            'holidays_count' => 'integer',
            'late_count' => 'integer',
            'total_hours' => 'decimal:2',
            'daily_breakdown_json' => 'array',
            'late_logs_json' => 'array',
        ];
    }

    /**
     * Get the batch that owns the timesheet record.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(TimesheetBatch::class, 'batch_id');
    }

    /**
     * Get the user associated with this timesheet record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

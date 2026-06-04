<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'punch_in',
        'punch_out',
        'punch_in_latitude',
        'punch_in_longitude',
        'punch_in_location',
        'punch_out_latitude',
        'punch_out_longitude',
        'punch_out_location',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'punch_in' => 'datetime',
            'punch_out' => 'datetime',
        ];
    }

    /**
     * Get the user associated with this attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the number of minutes worked on the particular day of this attendance record.
     */
    public function getMinutesWorkedOnDay(): int
    {
        if (! $this->punch_in) {
            return 0;
        }

        $dateInstance = $this->date ?? $this->punch_in;
        $startLimit = $dateInstance->copy()->startOfDay();
        $endLimit = $dateInstance->copy()->addDay()->startOfDay();

        $start = $this->punch_in->greaterThan($startLimit) ? $this->punch_in : $startLimit;
        $actualEnd = $this->punch_out ?? now();
        $end = $actualEnd->lessThan($endLimit) ? $actualEnd : $endLimit;

        if ($start->greaterThanOrEqualTo($end)) {
            return 0;
        }

        return (int) $start->diffInMinutes($end);
    }

    /**
     * Get the number of hours worked.
     */
    public function getHoursWorkedAttribute(): float
    {
        $minutes = $this->getMinutesWorkedOnDay();

        return round($minutes / 60, 2);
    }

    /**
     * Get the formatted hours worked (e.g. 8h 30m).
     */
    public function getFormattedHoursWorkedAttribute(): string
    {
        $totalMinutes = $this->getMinutesWorkedOnDay();
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Calculate the distance in meters between two coordinates using the Haversine formula.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}

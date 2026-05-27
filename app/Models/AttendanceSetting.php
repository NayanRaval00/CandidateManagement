<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
        'min_punch_out_delay',
        'punch_in_start',
        'punch_in_end',
    ];

    /**
     * Get the active configuration singleton instance.
     */
    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'latitude' => 23.02250000,
            'longitude' => 72.57140000,
            'radius' => 100,
            'min_punch_out_delay' => 30,
        ]);
    }
}

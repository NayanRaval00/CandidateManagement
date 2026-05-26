<?php

namespace App\Models;

use App\Mail\HolidayNotificationMail;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'is_working_day',
        'description',
        'notified',
    ];

    protected $casts = [
        'date' => 'date',
        'is_working_day' => 'boolean',
        'notified' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($holiday) {
            // Check if the holiday is today
            $holidayDate = Carbon::parse($holiday->date);
            if ($holidayDate->isToday()) {
                // Mark holiday as notified
                $holiday->updateQuietly(['notified' => true]);

                $users = User::all();

                foreach ($users as $user) {
                    // Send database notification
                    try {
                        FilamentNotification::make()
                            ->title("Today is a Holiday: {$holiday->name}")
                            ->body($holiday->is_working_day ? 'Note: Today is a working holiday.' : 'Office is closed today.')
                            ->color($holiday->is_working_day ? 'info' : 'danger')
                            ->icon('heroicon-o-calendar')
                            ->sendToDatabase($user);
                    } catch (\Exception $e) {
                        Log::error("Failed to send database notification to {$user->email}: ".$e->getMessage());
                    }

                    // Send email
                    try {
                        Mail::to($user->email)->send(new HolidayNotificationMail($holiday, true));
                    } catch (\Exception $e) {
                        Log::error("Failed to send holiday email to {$user->email}: ".$e->getMessage());
                    }
                }
            }
        });
    }
}

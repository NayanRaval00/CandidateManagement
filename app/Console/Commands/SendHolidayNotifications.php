<?php

namespace App\Console\Commands;

use App\Mail\HolidayNotificationMail;
use App\Models\Holiday;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendHolidayNotifications extends Command
{
    protected $signature = 'app:send-holiday-notifications';

    protected $description = 'Send holiday notifications and emails to all users one day prior';

    public function handle()
    {
        $tomorrow = today()->addDay()->toDateString();
        $holidays = Holiday::whereDate('date', $tomorrow)
            ->where('notified', false)
            ->get();

        if ($holidays->isEmpty()) {
            $this->info('No holidays tomorrow.');

            return 0;
        }

        $users = User::all();

        foreach ($holidays as $holiday) {
            $this->info("Processing holiday notification for tomorrow: {$holiday->name}");

            foreach ($users as $user) {
                // Send database notification
                try {
                    FilamentNotification::make()
                        ->title("Tomorrow is a Holiday: {$holiday->name}")
                        ->body($holiday->is_working_day ? 'Note: Tomorrow is a working holiday.' : 'Office will be closed tomorrow.')
                        ->color($holiday->is_working_day ? 'info' : 'danger')
                        ->icon('heroicon-o-calendar')
                        ->sendToDatabase($user);
                } catch (\Exception $e) {
                    Log::error("Failed to send database notification for tomorrow holiday to {$user->email}: ".$e->getMessage());
                }

                // Send email
                try {
                    Mail::to($user->email)->send(new HolidayNotificationMail($holiday, false));
                } catch (\Exception $e) {
                    Log::error("Failed to send holiday email to {$user->email}: ".$e->getMessage());
                }
            }

            // Mark as notified
            $holiday->update(['notified' => true]);
        }

        $this->info('Holiday notifications processed successfully.');

        return 0;
    }
}

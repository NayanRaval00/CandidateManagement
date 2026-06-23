<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.attendance-widget';

    protected static ?int $sort = -10; // Ensure it renders at the top of the dashboard

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $locationError = null;

    public ?string $currentLocationName = null;

    public ?Attendance $todayRecord = null;

    public ?string $breakReason = 'Short Break';

    public function mount(): void
    {
        $this->refreshTodayRecord();
    }

    public function refreshTodayRecord(): void
    {
        $this->todayRecord = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->with('breaks')
            ->first();
    }

    public function resolveLocationName(?float $lat, ?float $lng): ?string
    {
        if (is_null($lat) || is_null($lng)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CandidateManagement/1.0 (internal-attendance)',
            ])->timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                return $response->json('display_name');
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed: '.$e->getMessage());
        }

        return null;
    }

    public function getPunchInStatus(): array
    {
        $setting = AttendanceSetting::getSingleton();
        $now = now();
        $nowTime = $now->format('H:i:s');

        if ($setting->punch_in_start && $nowTime < $setting->punch_in_start) {
            $formattedStart = Carbon::parse($setting->punch_in_start)->format('h:i A');

            return [
                'allowed' => false,
                'reason' => "Punch-in starts at {$formattedStart}",
            ];
        }

        if ($setting->punch_in_end && $nowTime > $setting->punch_in_end) {
            $formattedEnd = Carbon::parse($setting->punch_in_end)->format('h:i A');

            return [
                'allowed' => false,
                'reason' => "Punch-in window closed at {$formattedEnd}",
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
        ];
    }

    public function punchIn(?float $lat = null, ?float $lng = null): void
    {
        if ($lat !== null) {
            $this->latitude = $lat;
        }
        if ($lng !== null) {
            $this->longitude = $lng;
        }

        $this->refreshTodayRecord();

        if ($this->todayRecord && $this->todayRecord->punch_in) {
            Notification::make()
                ->title('Already Punched In')
                ->body('You have already punched in for today.')
                ->warning()
                ->send();

            return;
        }

        if (is_null($this->latitude) || is_null($this->longitude)) {
            Notification::make()
                ->title('Location Required')
                ->body('Please wait while your browser retrieves your GPS location, or ensure location permissions are enabled.')
                ->danger()
                ->send();

            return;
        }

        $punchInStatus = $this->getPunchInStatus();
        if (! $punchInStatus['allowed']) {
            Notification::make()
                ->title('Punch In Restricted')
                ->body($punchInStatus['reason'])
                ->danger()
                ->send();

            return;
        }

        $setting = AttendanceSetting::getSingleton();

        // Check Distance
        $distance = Attendance::calculateDistance(
            (float) $setting->latitude,
            (float) $setting->longitude,
            $this->latitude,
            $this->longitude
        );

        if ($distance > $setting->radius) {
            $formattedDistance = round($distance);
            Notification::make()
                ->title('Location Out of Bounds')
                ->body("You are currently {$formattedDistance} meters away from the office location. You must be within {$setting->radius} meters to punch in.")
                ->danger()
                ->send();

            return;
        }

        $now = now();
        $status = 'Present';

        $locationName = $this->resolveLocationName($this->latitude, $this->longitude);

        Attendance::create([
            'user_id' => auth()->id(),
            'date' => today(),
            'punch_in' => $now,
            'punch_in_latitude' => $this->latitude,
            'punch_in_longitude' => $this->longitude,
            'punch_in_location' => $locationName,
            'status' => $status,
        ]);

        Notification::make()
            ->title('Punched In Successfully')
            ->body('Have a great working day!')
            ->success()
            ->send();

        $this->refreshTodayRecord();
    }

    public function punchOut(?float $lat = null, ?float $lng = null): void
    {
        if ($lat !== null) {
            $this->latitude = $lat;
        }
        if ($lng !== null) {
            $this->longitude = $lng;
        }

        $this->refreshTodayRecord();

        if (! $this->todayRecord || ! $this->todayRecord->punch_in) {
            Notification::make()
                ->title('Not Punched In')
                ->body('You must punch in before you can punch out.')
                ->danger()
                ->send();

            return;
        }

        if ($this->todayRecord->punch_out) {
            Notification::make()
                ->title('Already Punched Out')
                ->body('You have already completed your punch out for today.')
                ->warning()
                ->send();

            return;
        }

        // Lockout delay validation
        $setting = AttendanceSetting::getSingleton();
        if ($this->todayRecord->punch_in->addMinutes($setting->min_punch_out_delay)->isFuture()) {
            $remaining = now()->diffInMinutes($this->todayRecord->punch_in->addMinutes($setting->min_punch_out_delay)) + 1;
            Notification::make()
                ->title('Punch Out Restricted')
                ->body("You cannot punch out so quickly. Please wait another {$remaining} minutes.")
                ->danger()
                ->send();

            return;
        }

        // Auto-end active break
        if ($this->todayRecord->is_on_break) {
            $currentBreak = $this->todayRecord->current_break;
            if ($currentBreak) {
                $currentBreak->update([
                    'end_time' => now(),
                ]);
            }
        }

        $locationName = $this->resolveLocationName($this->latitude, $this->longitude);

        $this->todayRecord->update([
            'punch_out' => now(),
            'punch_out_latitude' => $this->latitude,
            'punch_out_longitude' => $this->longitude,
            'punch_out_location' => $locationName,
        ]);

        Notification::make()
            ->title('Punched Out Successfully')
            ->body('Thank you! Work duration recorded.')
            ->success()
            ->send();

        $this->refreshTodayRecord();
    }

    /**
     * Start a break for the current attendance session.
     */
    public function startBreak(?string $reason = null): void
    {
        $this->refreshTodayRecord();

        if (! $this->todayRecord || ! $this->todayRecord->punch_in || $this->todayRecord->punch_out) {
            Notification::make()
                ->title('Error')
                ->body('You must be punched in and working to start a break.')
                ->danger()
                ->send();

            return;
        }

        if ($this->todayRecord->is_on_break) {
            Notification::make()
                ->title('Already on Break')
                ->body('You are already on break.')
                ->warning()
                ->send();

            return;
        }

        $reason = $reason ?: ($this->breakReason ?: 'Short Break');

        $this->todayRecord->breaks()->create([
            'start_time' => now(),
            'reason' => $reason,
        ]);

        Notification::make()
            ->title('Break Started')
            ->body('Working timer has been paused.')
            ->info()
            ->send();

        $this->breakReason = 'Short Break';
        $this->refreshTodayRecord();
    }

    /**
     * End the current active break.
     */
    public function endBreak(): void
    {
        $this->refreshTodayRecord();

        if (! $this->todayRecord || ! $this->todayRecord->is_on_break) {
            Notification::make()
                ->title('Error')
                ->body('You are not currently on a break.')
                ->danger()
                ->send();

            return;
        }

        $currentBreak = $this->todayRecord->current_break;
        if ($currentBreak) {
            $currentBreak->update([
                'end_time' => now(),
            ]);
        }

        Notification::make()
            ->title('Break Ended')
            ->body('Working timer has resumed.')
            ->success()
            ->send();

        $this->refreshTodayRecord();
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}

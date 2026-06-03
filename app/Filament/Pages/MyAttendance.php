<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'My Attendance';

    protected static ?string $title = 'Attendance Center';

    protected static string $view = 'filament.pages.my-attendance';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $locationError = null;

    public ?string $currentLocationName = null;

    public ?Attendance $todayRecord = null;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $this->refreshTodayRecord();
    }

    public function refreshTodayRecord(): void
    {
        $this->todayRecord = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->first();
    }

    public function updatedLatitude(): void
    {
        $this->updateLocationName();
    }

    public function updatedLongitude(): void
    {
        $this->updateLocationName();
    }

    public function updateLocationName(): void
    {
        if ($this->latitude && $this->longitude) {
            $this->currentLocationName = $this->resolveLocationName($this->latitude, $this->longitude);
        }
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
            Log::warning('Reverse geocoding failed: ' . $e->getMessage());
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

        // Validate Geolocation
        if (is_null($this->latitude) || is_null($this->longitude)) {
            Notification::make()
                ->title('Location Required')
                ->body('Please wait while your browser retrieves your GPS location, or ensure location permissions are enabled.')
                ->danger()
                ->send();

            return;
        }

        // Validate Timing
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

        // 1. Check Distance
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

        // Get location name
        $locationName = $this->resolveLocationName($this->latitude, $this->longitude);

        // Create attendance record
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

        // Validate Lockout Duration
        $setting = AttendanceSetting::getSingleton();
        $punchInTime = Carbon::parse($this->todayRecord->punch_in);
        $diffInMinutes = now()->diffInMinutes($punchInTime);

        // if ($diffInMinutes < $setting->min_punch_out_delay) {
        //     $remaining = $setting->min_punch_out_delay - $diffInMinutes;
        //     Notification::make()
        //         ->title('Punch Out Locked')
        //         ->body("You cannot punch out until {$setting->min_punch_out_delay} minutes after your punch in. Please wait {$remaining} more minutes.")
        //         ->danger()
        //         ->send();

        //     return;
        // }

        // Get location name
        $locationName = $this->resolveLocationName($this->latitude, $this->longitude);

        // Update attendance record
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

    public function table(Table $table): Table
    {
        return $table
            ->query(Attendance::query()->where('user_id', auth()->id()))
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('punch_in')
                    ->dateTime('h:i A')
                    ->label('Punch In'),
                TextColumn::make('punch_out')
                    ->dateTime('h:i A')
                    ->label('Punch Out'),
                TextColumn::make('punch_in_latitude')
                    ->label('Punch In Location')
                    ->state(fn($record) => $record->punch_in_latitude ? "{$record->punch_in_latitude}, {$record->punch_in_longitude}" : 'N/A')
                    ->description(fn($record) => $record->punch_in_location),
                TextColumn::make('punch_out_latitude')
                    ->label('Punch Out Location')
                    ->state(fn($record) => $record->punch_out_latitude ? "{$record->punch_out_latitude}, {$record->punch_out_longitude}" : 'N/A')
                    ->description(fn($record) => $record->punch_out_location),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Present' => 'success',
                        'Late' => 'warning',
                        'Half Day' => 'info',
                        'Absent' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('date', 'desc');
    }
}

<x-filament-widgets::widget>
    <div x-data="{
        time: '00:00:00',
        latitude: null,
        longitude: null,
        locationError: null,
        locationStatus: 'retrieving',
        init() {
            const update = () => {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                this.time = `${hours}:${minutes}:${seconds}`;
            };
            update();
            setInterval(update, 1000);
            this.refreshGPS();
        },
        refreshGPS() {
            if (!navigator.geolocation) {
                this.locationError = 'GPS Not Supported';
                this.locationStatus = 'error';
                return;
            }
            this.locationStatus = 'retrieving';
            this.locationError = null;

            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.locationStatus = 'success';
                    this.locationError = null;
                    $wire.set('latitude', this.latitude);
                    $wire.set('longitude', this.longitude);
                    $wire.set('locationError', null);
                },
                (error) => {
                    console.warn('Widget GPS high accuracy failed, trying low accuracy...', error);
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude;
                            this.longitude = position.coords.longitude;
                            this.locationStatus = 'success';
                            this.locationError = null;
                            $wire.set('latitude', this.latitude);
                            $wire.set('longitude', this.longitude);
                            $wire.set('locationError', null);
                        },
                        (fallbackError) => {
                            let msg = 'Failed to locate device.';
                            if (fallbackError.code === fallbackError.PERMISSION_DENIED) {
                                msg = 'Location permission denied. Location access is mandatory to punch. Please click the lock/settings icon next to the URL in your browser and enable location permissions.';
                            } else if (fallbackError.code === fallbackError.POSITION_UNAVAILABLE) {
                                msg = 'Location position unavailable. Check network or GPS signal.';
                            } else if (fallbackError.code === fallbackError.TIMEOUT) {
                                msg = 'Location lookup timed out.';
                            }
                            this.locationError = msg;
                            this.locationStatus = 'error';
                            $wire.set('locationError', msg);
                        },
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                    );
                },
                options
            );
        },
        gpsAndActionWidget(action) {
            const actionName = action === 'punchIn' ? 'Punch In' : 'Punch Out';

            // Check location state BEFORE showing confirmation
            if (this.locationStatus === 'error') {
                alert(`Cannot ${actionName}: Location permission is required. \n\n${this.locationError}`);
                return;
            }

            if (this.locationStatus === 'retrieving' || this.latitude === null || this.longitude === null) {
                alert(`Please wait while we resolve your GPS coordinates before trying to ${actionName}.`);
                return;
            }
            
            if (!confirm(`Are you sure you want to ${actionName}?`)) {
                return;
            }

            $wire.call(action, this.latitude, this.longitude);
        }
    }">
        <x-filament::section class="h-full flex flex-col justify-between">
            <x-slot name="heading">
                Attendance Center
            </x-slot>

            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold uppercase tracking-wider text-gray-500">Punch Card Status</span>
                @if(!$todayRecord)
                    <x-filament::badge color="gray" icon="heroicon-m-x-circle">Not Punched In</x-filament::badge>
                @elseif($todayRecord && $todayRecord->is_on_break)
                    <x-filament::badge color="warning" icon="heroicon-m-pause" class="animate-pulse">On Break</x-filament::badge>
                @elseif($todayRecord && !$todayRecord->punch_out)
                    <x-filament::badge color="success" icon="heroicon-m-play" class="animate-pulse">Working</x-filament::badge>
                @else
                    <x-filament::badge color="info" icon="heroicon-m-check-circle">Completed</x-filament::badge>
                @endif
            </div>

            <!-- GPS Location Status inside Widget -->
            <div class="mb-4">
                <template x-if="locationStatus === 'retrieving'">
                    <div class="p-3 rounded-xl flex items-start gap-2 bg-amber-50 text-amber-800 border border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30 text-xs">
                        <svg class="w-4 h-4 shrink-0 animate-spin mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18" />
                        </svg>
                        <div>
                            <span class="font-bold">Retrieving Location...</span>
                        </div>
                    </div>
                </template>
                <template x-if="locationStatus === 'success'">
                    <div class="p-3 rounded-xl flex items-start gap-2 bg-emerald-50 text-emerald-800 border border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30 text-xs">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>
                            <span class="font-bold">Location Locked</span>
                        </div>
                    </div>
                </template>
                <template x-if="locationStatus === 'error'">
                    <div class="p-3 rounded-xl flex flex-col gap-1 bg-rose-50 text-rose-800 border border-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30 text-xs">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="font-bold">Location Error</span>
                        </div>
                        <p class="text-[11px] leading-relaxed opacity-90 mt-1" x-text="locationError"></p>
                    </div>
                </template>
            </div>

            <!-- Clock Display & Hours Worked Today -->
            <div class="my-6 text-center bg-gray-50 dark:bg-white/5 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                <div x-text="time" class="text-5xl font-black tracking-wider text-indigo-600 dark:text-indigo-400 font-mono">00:00:00</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ today()->format('l, d F Y') }}</div>
                
                @if($todayRecord)
                    <div class="mt-4 pt-3 border-t border-gray-200/50 dark:border-gray-700/50 text-sm flex justify-around">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">Worked:</span>
                            <strong class="text-indigo-600 dark:text-indigo-400 font-extrabold ml-1">{{ $todayRecord->formatted_hours_worked }}</strong>
                        </div>
                        @if($todayRecord->breaks->isNotEmpty())
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 font-semibold">Break:</span>
                                <strong class="text-amber-600 dark:text-amber-400 font-extrabold ml-1">{{ $todayRecord->formatted_total_break_time }}</strong>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Active Details -->
            @if($todayRecord)
                <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-sm mb-6">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block text-xs font-semibold">PUNCH IN</span>
                        <strong class="text-gray-900 dark:text-white font-bold text-base">{{ $todayRecord->punch_in->format('h:i A') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block text-xs font-semibold">PUNCH OUT</span>
                        <strong class="text-gray-900 dark:text-white font-bold text-base">
                            {{ $todayRecord->punch_out ? $todayRecord->punch_out->format('h:i A') : '--:--' }}
                        </strong>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-4">
                @php
                    $punchInStatus = $this->getPunchInStatus();
                @endphp

                @if(!$todayRecord)
                    @if($punchInStatus['allowed'])
                        <x-filament::button 
                            size="xl" 
                            color="primary" 
                            icon="heroicon-m-arrow-right-on-rectangle"
                            @click="gpsAndActionWidget('punchIn')"
                            class="w-full text-base font-bold py-3"
                        >
                            Punch In
                        </x-filament::button>
                    @else
                        <x-filament::button 
                            size="xl" 
                            color="gray" 
                            disabled
                            class="w-full text-base font-bold py-3"
                        >
                            {{ $punchInStatus['reason'] }}
                        </x-filament::button>
                    @endif
                @elseif($todayRecord && !$todayRecord->punch_out)
                    <div class="space-y-4">
                        @if($todayRecord->is_on_break)
                            <x-filament::button 
                                size="xl" 
                                color="success" 
                                icon="heroicon-m-play"
                                wire:click="endBreak"
                                class="w-full text-base font-bold py-3 animate-pulse"
                            >
                                Resume Work
                            </x-filament::button>
                        @else
                            <div class="flex gap-4">
                                <div class="w-1/2">
                                    <select 
                                        wire:model="breakReason"
                                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-3"
                                    >
                                        <option value="Short Break">Short Break</option>
                                        <option value="Lunch">Lunch</option>
                                        <option value="Tea/Coffee Break">Tea/Coffee Break</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="w-1/2">
                                    <x-filament::button 
                                        size="xl" 
                                        color="warning" 
                                        icon="heroicon-m-pause"
                                        wire:click="startBreak"
                                        class="w-full text-base font-bold py-3"
                                    >
                                        Start Break
                                    </x-filament::button>
                                </div>
                            </div>
                        @endif

                        <x-filament::button 
                            size="xl" 
                            color="danger" 
                            icon="heroicon-m-arrow-left-on-rectangle"
                            @click="gpsAndActionWidget('punchOut')"
                            class="w-full text-base font-bold py-3"
                        >
                            Punch Out
                        </x-filament::button>
                    </div>
                @else
                    <x-filament::button 
                        size="xl" 
                        color="gray" 
                        disabled
                        class="w-full text-base font-bold py-3"
                    >
                        Punched Out for Today
                    </x-filament::button>
                @endif
            </div>

            <!-- Today's Breaks Log -->
            @if($todayRecord && $todayRecord->breaks->isNotEmpty())
                <div class="mt-6 border-t border-gray-200 dark:border-gray-800 pt-4">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-3 uppercase tracking-wider font-semibold">Today's Breaks ({{ $todayRecord->breaks->count() }})</span>
                    <div class="space-y-2 max-h-[150px] overflow-y-auto pr-1">
                        @foreach($todayRecord->breaks->sortBy('start_time') as $break)
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-white/5 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="font-bold text-sm text-gray-800 dark:text-gray-200 block">{{ $break->reason }}</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $break->start_time->format('h:i A') }} - 
                                        {{ $break->end_time ? $break->end_time->format('h:i A') : 'Active' }}
                                    </span>
                                </div>
                                <x-filament::badge color="{{ $break->end_time ? 'gray' : 'warning' }}" size="sm">
                                    {{ $break->duration_in_minutes }}m
                                </x-filament::badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>

<x-filament-panels::page>
    <div x-data="{
        time: '00:00:00',
        gpsStatus: 'retrieving',
        gpsErrorMsg: '',
        latitude: null,
        longitude: null,
        
        init() {
            // Live clock
            const updateClock = () => {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                this.time = `${hours}:${minutes}:${seconds}`;
            };
            updateClock();
            setInterval(updateClock, 1000);

            // Initialize GPS
            this.refreshGPS();
        },
        refreshGPS() {
            if (!navigator.geolocation) {
                this.gpsStatus = 'error';
                this.gpsErrorMsg = 'Your browser does not support geolocation.';
                return;
            }

            this.gpsStatus = 'retrieving';
            
            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                (pos) => this.successCallback(pos),
                (error) => {
                    console.warn('High accuracy failed, falling back to low accuracy...', error);
                    navigator.geolocation.getCurrentPosition(
                        (pos) => this.successCallback(pos),
                        (err) => this.errorCallback(err),
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                    );
                },
                options
            );
        },
        successCallback(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            this.latitude = lat;
            this.longitude = lng;
            this.gpsStatus = 'success';
            this.gpsErrorMsg = '';

            $wire.set('latitude', lat);
            $wire.set('longitude', lng);
            $wire.set('locationError', null);
        },
        errorCallback(error) {
            let msg = 'Failed to locate device.';
            
            if (error.code === error.PERMISSION_DENIED) {
                msg = 'GPS permission denied. Location access is mandatory to punch. Please click the lock/settings icon next to the URL in your browser and enable location permissions.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                msg = 'GPS position unavailable. Check network or GPS signal.';
            } else if (error.code === error.TIMEOUT) {
                msg = 'GPS lookup timed out.';
            }
            
            this.gpsStatus = 'error';
            this.gpsErrorMsg = msg;

            $wire.set('locationError', msg);
        },
        gpsAndAction(action) {
            const actionName = action === 'punchIn' ? 'Punch In' : 'Punch Out';

            if (this.gpsStatus === 'error') {
                alert(`Cannot ${actionName}: Location permission is required. \n\n${this.gpsErrorMsg}`);
                return;
            }

            if (this.gpsStatus === 'retrieving' || this.latitude === null || this.longitude === null) {
                alert(`Please wait while we resolve your GPS coordinates before trying to ${actionName}.`);
                return;
            }
            
            if (!confirm(`Are you sure you want to ${actionName}?`)) {
                return;
            }

            $wire.call(action, this.latitude, this.longitude);
        }
    }" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Punch Card Container -->
        <div class="md:col-span-2">
            <x-filament::section class="h-full flex flex-col justify-between min-h-[350px]">
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

                <!-- Clock Display -->
                <div class="my-6 text-center bg-gray-50 dark:bg-white/5 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div x-text="time" class="text-5xl font-black tracking-wider text-indigo-600 dark:text-indigo-400 font-mono">00:00:00</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ today()->format('l, d F Y') }}</div>
                </div>

                <!-- Active Details -->
                @if($todayRecord)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-100 dark:border-gray-800 text-sm mb-6">
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
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block text-xs font-semibold">WORKED TIME</span>
                            <strong class="text-indigo-600 dark:text-indigo-400 font-extrabold text-base">{{ $todayRecord->formatted_hours_worked }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 block text-xs font-semibold">BREAK TIME</span>
                            <strong class="text-amber-600 dark:text-amber-400 font-extrabold text-base">{{ $todayRecord->formatted_total_break_time }}</strong>
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
                                @click="gpsAndAction('punchIn')"
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
                                @click="gpsAndAction('punchOut')"
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
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block mb-3 uppercase tracking-wider">Today's Break Activity</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[160px] overflow-y-auto pr-1">
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

        <!-- GPS Location Status -->
        <div class="md:col-span-1">
            <x-filament::section class="h-full flex flex-col justify-between">
                <x-slot name="heading">
                    GPS Location Tracker
                </x-slot>

                <div class="space-y-4">
                    <!-- Status Indicator -->
                    <div :class="{
                        'bg-emerald-50 text-emerald-800 border-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30': gpsStatus === 'success',
                        'bg-rose-50 text-rose-800 border-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30': gpsStatus === 'error',
                        'bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30': gpsStatus === 'retrieving'
                    }" class="p-4 rounded-xl flex items-start gap-3 border transition duration-150">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <span class="font-bold text-sm" x-text="
                                gpsStatus === 'success' ? 'Location Validated' :
                                (gpsStatus === 'error' ? 'GPS Lock Failed' : 'Retrieving Location...')
                            ">Retrieving Location...</span>
                            <p class="text-xs mt-1 opacity-90" x-text="
                                gpsStatus === 'success' ? 'GPS coordinates successfully locked.' :
                                (gpsStatus === 'error' ? gpsErrorMsg : 'Please wait while we resolve your coordinates.')
                            ">Please wait while we resolve your coordinates.</p>
                        </div>
                    </div>

                    <!-- Coordinate Log -->
                    <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-100 dark:border-gray-800 space-y-2 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Latitude:</span>
                            <span class="text-gray-900 dark:text-white font-semibold" x-text="latitude !== null ? latitude.toFixed(6) : '--'">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Longitude:</span>
                            <span class="text-gray-900 dark:text-white font-semibold" x-text="longitude !== null ? longitude.toFixed(6) : '--'">--</span>
                        </div>
                        @if($currentLocationName)
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold block mb-1">Resolved Address:</span>
                                <span class="text-gray-700 dark:text-gray-300 normal-case font-medium block leading-relaxed">{{ $currentLocationName }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Manually Trigger Refresh -->
                <div class="mt-4">
                    <x-filament::button 
                        size="md" 
                        color="gray" 
                        icon="heroicon-m-arrow-path"
                        @click="refreshGPS()"
                        class="w-full"
                    >
                        Refresh GPS Location
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>
    </div>

    <!-- Attendance History Table Section -->
    <div class="mt-8 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-lg overflow-hidden p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">My Attendance History</h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
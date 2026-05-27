<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
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
                    @elseif($todayRecord && !$todayRecord->punch_out)
                        <x-filament::badge color="success" icon="heroicon-m-play" class="animate-pulse">Working</x-filament::badge>
                    @else
                        <x-filament::badge color="info" icon="heroicon-m-check-circle">Completed</x-filament::badge>
                    @endif
                </div>

                <!-- Clock Display -->
                <div class="my-6 text-center bg-gray-50 dark:bg-white/5 p-6 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div id="live-clock" class="text-5xl font-black tracking-wider text-indigo-600 dark:text-indigo-400 font-mono">00:00:00</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ today()->format('l, d F Y') }}</div>
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
                                onclick="gpsAndAction('punchIn')"
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
                        <x-filament::button 
                            size="xl" 
                            color="danger" 
                            icon="heroicon-m-arrow-left-on-rectangle"
                            onclick="gpsAndAction('punchOut')"
                            class="w-full text-base font-bold py-3"
                        >
                            Punch Out
                        </x-filament::button>
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
                    <div id="gps-status-card" class="p-4 rounded-xl flex items-start gap-3 bg-amber-50 text-amber-800 border border-amber-100 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <span id="gps-status-title" class="font-bold text-sm">Retrieving Location...</span>
                            <p id="gps-status-desc" class="text-xs mt-1 text-amber-700/90 dark:text-amber-500/90">Please wait while we resolve your coordinates.</p>
                        </div>
                    </div>

                    <!-- Coordinate Log -->
                    <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-100 dark:border-gray-800 space-y-2 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Latitude:</span>
                            <span id="coord-lat" class="text-gray-900 dark:text-white font-semibold">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Longitude:</span>
                            <span id="coord-lng" class="text-gray-900 dark:text-white font-semibold">--</span>
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
                        onclick="refreshGPS()"
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

    <!-- Geolocation Scripts -->
    <script>
        // Live Clock Script
        function updateClock() {
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.innerText = `${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // GPS Card UI State Updater
        function updateGPSCard(type, title, desc) {
            const card = document.getElementById('gps-status-card');
            const titleEl = document.getElementById('gps-status-title');
            const descEl = document.getElementById('gps-status-desc');

            if (!card || !titleEl || !descEl) return;

            // Reset class list
            card.className = 'p-4 rounded-xl flex items-start gap-3 border transition duration-150 ';

            if (type === 'success') {
                card.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-100', 'dark:bg-emerald-950/20', 'dark:text-emerald-400', 'dark:border-emerald-900/30');
            } else if (type === 'danger') {
                card.classList.add('bg-rose-50', 'text-rose-800', 'border-rose-100', 'dark:bg-rose-950/20', 'dark:text-rose-400', 'dark:border-rose-900/30');
            } else {
                card.classList.add('bg-amber-50', 'text-amber-800', 'border-amber-100', 'dark:bg-amber-950/20', 'dark:text-amber-400', 'dark:border-amber-900/30');
            }

            titleEl.innerText = title;
            descEl.innerText = desc;
        }

        // GPS Logic
        let lastLat = null;
        let lastLng = null;

        function refreshGPS() {
            if (!navigator.geolocation) {
                updateGPSCard('danger', 'GPS Not Supported', 'Your browser does not support geolocation.');
                return;
            }

            updateGPSCard('warning', 'Retrieving Location...', 'Requesting device coordinates...');

            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                successCallback,
                (error) => {
                    console.warn("High accuracy failed, falling back to low accuracy...", error);
                    // Fallback to low accuracy
                    navigator.geolocation.getCurrentPosition(
                        successCallback,
                        errorCallback,
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                    );
                },
                options
            );
        }

        function successCallback(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            lastLat = lat;
            lastLng = lng;

            // Sync with Livewire properties
            @this.set('latitude', lat);
            @this.set('longitude', lng);
            @this.set('locationError', null);

            // Update UI coordinates
            document.getElementById('coord-lat').innerText = lat.toFixed(6);
            document.getElementById('coord-lng').innerText = lng.toFixed(6);

            updateGPSCard('success', 'Location Validated', 'GPS coordinates successfully locked.');
        }

        function errorCallback(error) {
            let msg = 'Failed to locate device.';
            if (error.code === error.PERMISSION_DENIED) {
                msg = 'GPS permission denied. Please allow location access.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                msg = 'GPS position unavailable. Check network or GPS signal.';
            } else if (error.code === error.TIMEOUT) {
                msg = 'GPS lookup timed out.';
            }
            
            @this.set('locationError', msg);
            updateGPSCard('danger', 'GPS Lock Failed', msg);
        }

        // On Load Trigger
        document.addEventListener('DOMContentLoaded', () => {
            refreshGPS();
        });

        // Double-check GPS and call Livewire action directly passing the coordinates
        function gpsAndAction(action) {
            const actionName = action === 'punchIn' ? 'Punch In' : 'Punch Out';
            
            // Confirmation modal check
            if (!confirm(`Are you sure you want to ${actionName}?`)) {
                return;
            }

            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            updateGPSCard('warning', 'Syncing Location...', 'Getting fresh GPS lock prior to punching...');

            const options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    lastLat = lat;
                    lastLng = lng;

                    document.getElementById('coord-lat').innerText = lat.toFixed(6);
                    document.getElementById('coord-lng').innerText = lng.toFixed(6);

                    updateGPSCard('success', 'Location Validated', 'GPS coordinates successfully locked.');

                    // Execute Livewire function with direct parameters to avoid state race conditions
                    @this.call(action, lat, lng);
                },
                (error) => {
                    console.warn("High accuracy action failed, falling back to low accuracy...", error);
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            lastLat = lat;
                            lastLng = lng;

                            document.getElementById('coord-lat').innerText = lat.toFixed(6);
                            document.getElementById('coord-lng').innerText = lng.toFixed(6);

                            updateGPSCard('success', 'Location Validated', 'GPS coordinates locked.');

                            // Execute Livewire function
                            @this.call(action, lat, lng);
                        },
                        (fallbackError) => {
                            let msg = 'Failed to locate device.';
                            if (fallbackError.code === fallbackError.PERMISSION_DENIED) {
                                msg = 'GPS permission denied. Please allow location access to punch.';
                            }
                            alert(msg);
                            @this.set('locationError', msg);
                            updateGPSCard('danger', 'GPS Lock Failed', msg);
                        },
                        { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                    );
                },
                options
            );
        }
    </script>
</x-filament-panels::page>

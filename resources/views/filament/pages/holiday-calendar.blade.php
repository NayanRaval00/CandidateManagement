<x-filament-panels::page>
    <div class="space-y-8">
        <!-- FullCalendar Card Wrapper -->
        <div class="p-6 rounded-2xl border border-gray-200 bg-white/70 backdrop-blur-md shadow-lg dark:border-gray-800 dark:bg-gray-900/70">
            @livewire(\App\Filament\Widgets\HolidayCalendarWidget::class)
        </div>

        <!-- Agenda List Table -->
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>

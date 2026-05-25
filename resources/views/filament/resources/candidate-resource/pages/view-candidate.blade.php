<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
            {{ $record->name }}'s Application
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white dark:bg-gray-900 p-6 shadow-xl rounded-2xl border border-gray-100 dark:border-gray-800">

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Email</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1 truncate">{{ $record->email }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Mobile</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->mobile ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">City</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->city ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">State</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->state ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Position Applying For</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->position ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Current Company</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->current_company_name ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Current Position</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->current_position ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Education</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->education ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Current CTC</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">
                    {{ $record->current_ctc ? '₹' . number_format($record->current_ctc, 2) : 'N/A' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Expected CTC</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">
                    {{ $record->expected_ctc ? '₹' . number_format($record->expected_ctc, 2) : 'N/A' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Reason for Job Change</p>
                <p class="text-base text-gray-850 dark:text-gray-300 mt-1 leading-relaxed">{{ $record->reason_for_job_change ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Notice Period</p>
                <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $record->notice_period ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Resume</p>
                <div class="mt-1">
                    @if ($record->resume)
                        <a href="{{ asset('/' . $record->resume) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-xl shadow-lg transition-all duration-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Resume
                        </a>
                    @else
                        <p class="text-base font-bold text-gray-800 dark:text-gray-200 mt-1">Not uploaded</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-filament::page>

<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $record->name }}'s Profile
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 shadow rounded-lg">

            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->email }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Mobile</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->mobile ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">City</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->city ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">State</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->state ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Position</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->position ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Current Company Name</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->current_company_name ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Current Position</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->current_position ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Education</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->eduction ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Current CTC</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->current_ctc ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Expected CTC</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->expected_ctc ?? 'N/A' }}</p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Reason for Job Change</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->reason_for_job_change ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Notice Period</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->notice_period ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Email Verified At</p>
                <p class="text-lg text-gray-800 font-medium">{{ $record->email_verified_at ?? 'Not verified' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Resume</p>
                @if ($record->resume)
                    <a href="{{ asset('storage/' . $record->resume) }}"
                       target="_blank"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded hover:bg-primary-500 transition">
                        Download Resume
                    </a>
                @else
                    <p class="text-lg text-gray-800 font-medium">Not uploaded</p>
                @endif
            </div>

        </div>
    </div>
</x-filament::page>

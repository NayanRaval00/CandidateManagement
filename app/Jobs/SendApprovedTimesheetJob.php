<?php

namespace App\Jobs;

use App\Mail\TimesheetNotificationMail;
use App\Models\TimesheetBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendApprovedTimesheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $batchId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batch = TimesheetBatch::with('records.user')->findOrFail($this->batchId);

        // Only process approved batches
        if ($batch->status !== 'approved') {
            return;
        }

        foreach ($batch->records as $record) {
            $record->setRelation('batch', $batch);
            $user = $record->user;
            if (! $user || ! $user->email) {
                Log::warning("User or user email is missing for record: {$record->id}");

                continue;
            }

            try {
                Log::info("Generating PDF for user: {$user->name}");
                // Render PDF in memory
                $pdf = Pdf::loadView('pdf.timesheet', [
                    'record' => $record,
                    'batch' => $batch,
                    'user' => $user,
                ]);

                $pdfData = $pdf->output();
                Log::info("PDF generated successfully for user: {$user->name}. Length: ".strlen($pdfData).' bytes');

                $startDateStr = $batch->start_date->format('Ymd');
                $endDateStr = $batch->end_date->format('Ymd');
                $safeName = str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $user->name));
                $filename = "timesheet_{$safeName}_{$startDateStr}_{$endDateStr}.pdf";

                // Send email
                Log::info("Sending timesheet email to: {$user->email}");
                Mail::to($user->email)->send(new TimesheetNotificationMail($record, $pdfData, $filename));
                Log::info("Timesheet email sent successfully to: {$user->email}");
            } catch (\Exception $e) {
                Log::error('Exception in SendApprovedTimesheetJob: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }

        // Update batch status
        $batch->update(['status' => 'dispatched']);
    }
}

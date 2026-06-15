<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ExpensePdfService
{
    /**
     * Generate a PDF report for a collection of expenses.
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(Collection $expenses, ?string $userName = null)
    {
        // Load the logo
        $logoPath = public_path('images/logo-1.svg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/svg+xml;base64,'.base64_encode($logoData);
        }

        // Calculate summary
        $totalAmount = (float) $expenses->sum('amount');
        $totalCount = $expenses->count();

        // Find date range
        $minDate = $expenses->min('expense_date');
        $maxDate = $expenses->max('expense_date');

        $pdf = Pdf::loadView('pdf.expenses', [
            'expenses' => $expenses,
            'logoBase64' => $logoBase64,
            'totalAmount' => $totalAmount,
            'totalCount' => $totalCount,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'generatedBy' => $userName ?? 'Administrator',
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}

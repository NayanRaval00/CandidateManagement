<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select([
            'id',
            'name',
            'email',
            'position',
            'mobile',
            'city',
            'state',
            'resume',
            'current_company_name',
            'current_position',
            'eduction',
            'current_ctc',
            'expected_ctc',
            'reason_for_job_change',
            'notice_period',
            'email_verified_at',
            'created_at'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Position',
            'Mobile',
            'City',
            'State',
            'Resume Path',
            'Current Company',
            'Current Position',
            'Education',
            'Current CTC',
            'Expected CTC',
            'Reason for Job Change',
            'Notice Period',
            'Email Verified At',
            'Created At',
        ];
    }
}

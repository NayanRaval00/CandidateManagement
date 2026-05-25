<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'position',
        'mobile',
        'city',
        'state',
        'resume',
        'current_company_name',
        'current_position',
        'education',
        'current_ctc',
        'expected_ctc',
        'reason_for_job_change',
        'notice_period',
    ];
}

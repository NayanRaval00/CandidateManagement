<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function save(Request $request)
    {
        return view('save-details');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:candidates,email',
                'position' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
                'current_company_name' => 'nullable|string|max:255',
                'current_position' => 'nullable|string|max:255',
                'education' => 'nullable|string|max:255',
                'current_ctc' => 'nullable|numeric',
                'expected_ctc' => 'nullable|numeric',
                'reason_for_job_change' => 'nullable|string',
                'notice_period' => 'nullable|string|max:255',
            ],
            [
                'email.unique' => 'You have already applied.',
            ]
        );

        $data = $validated;
        unset($data['resume']);

        // Handle file upload
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Optional: create unique file name
            $filename = time().'_'.$file->getClientOriginalName();

            // Move file to public/resumes
            $file->move(public_path('resumes'), $filename);

            // Store relative path in database
            $data['resume'] = 'resumes/'.$filename;
        }

        Candidate::create($data);

        return redirect()->back()->with('success', 'Thank you for applying! Our HR team will be in touch with you shortly!');
    }
}

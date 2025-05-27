<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function save(Request $request)
    {
        return view('save-details');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
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
                'email.unique' => 'You have already applied.'
            ]
        );

        $data = $request->except('resume');

        // Handle file upload
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
            $data['resume'] = $resumePath;
        }

        User::create($data);

        return redirect()->back()->with('success', 'Thank you for applying! Our HR team will be in touch with you shortly!');
    }
}

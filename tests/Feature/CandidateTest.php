<?php

namespace Tests\Feature;

use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the save details page renders.
     */
    public function test_save_details_page_renders(): void
    {
        $response = $this->get('/save-details');

        $response->assertStatus(200);
        $response->assertSee('Job Application Form');
    }

    /**
     * Test a candidate can submit the form successfully.
     */
    public function test_candidate_can_submit_application(): void
    {
        Storage::fake('public');

        $resume = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');

        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'mobile' => '1234567890',
            'position' => 'Software Engineer',
            'city' => 'New York',
            'state' => 'New York',
            'resume' => $resume,
            'current_company_name' => 'Acme Corp',
            'current_position' => 'Junior Developer',
            'current_ctc' => 50000,
            'expected_ctc' => 70000,
            'notice_period' => '30 days',
            'reason_for_job_change' => 'Looking for growth opportunities.',
            'education' => 'Bachelor of Science in CS',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidates', [
            'email' => 'johndoe@example.com',
            'name' => 'John Doe',
        ]);

        // Clean up uploaded file if any
        $candidate = Candidate::where('email', 'johndoe@example.com')->first();
        $this->assertNotNull($candidate->resume);
        $this->assertFileExists(public_path($candidate->resume));

        // Clean up the created file in public/resumes
        if (file_exists(public_path($candidate->resume))) {
            unlink(public_path($candidate->resume));
        }
    }
}

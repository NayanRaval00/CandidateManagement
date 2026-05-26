<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_employee_fields_and_self_referencing_relationship()
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'position' => 'Manager',
            'work_location' => 'New York',
            'joining_date' => '2026-01-01',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'position' => 'Developer',
            'mobile' => '1234567890',
            'city' => 'San Francisco',
            'state' => 'CA',
            'residential_address' => '123 Main St, SF, CA',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_relation' => 'Spouse',
            'emergency_contact_number' => '9876543210',
            'emergency_contact_address' => '123 Main St, SF, CA',
            'reporting_to_id' => $manager->id,
            'work_location' => 'Remote',
            'joining_date' => '2026-05-01',
        ]);

        // Assert fields are saved properly
        $this->assertEquals('123 Main St, SF, CA', $employee->residential_address);
        $this->assertEquals('Jane Doe', $employee->emergency_contact_name);
        $this->assertEquals('Spouse', $employee->emergency_contact_relation);
        $this->assertEquals('9876543210', $employee->emergency_contact_number);
        $this->assertEquals('123 Main St, SF, CA', $employee->emergency_contact_address);
        $this->assertEquals($manager->id, $employee->reporting_to_id);
        $this->assertEquals('Remote', $employee->work_location);

        // Assert joining_date is cast to Carbon instance
        $this->assertInstanceOf(\Carbon\Carbon::class, $employee->joining_date);
        $this->assertEquals('2026-05-01', $employee->joining_date->format('Y-m-d'));

        // Assert reportingTo relationship works
        $this->assertNotNull($employee->reportingTo);
        $this->assertEquals('Manager User', $employee->reportingTo->name);
    }
}

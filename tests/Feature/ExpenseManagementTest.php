<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use App\Services\ExpensePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        if (! Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        if (! Role::where('name', 'employee')->exists()) {
            Role::create(['name' => 'employee', 'guard_name' => 'web']);
        }
    }

    public function test_admin_can_access_expenses_resource_while_employee_cannot()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $employee->assignRole('employee');

        // Admin can access
        $this->actingAs($admin)
            ->get('/admin/expenses')
            ->assertStatus(200);

        // Employee is forbidden/redirected
        $response = $this->actingAs($employee)->get('/admin/expenses');
        $this->assertTrue($response->status() >= 300);
    }

    public function test_it_can_create_and_manage_expenses()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $expenseData = [
            'title' => 'Software Subscription',
            'amount' => 99.99,
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'category' => 'Software',
            'description' => 'Monthly payment for IDE tools.',
        ];

        Expense::create($expenseData);

        $this->assertDatabaseHas('expenses', [
            'title' => 'Software Subscription',
            'amount' => 99.99,
            'category' => 'Software',
        ]);
    }

    public function test_it_generates_pdf_report_via_service()
    {
        $expenses = collect([
            Expense::create([
                'title' => 'Office Rent',
                'amount' => 1200.00,
                'expense_date' => now(),
                'category' => 'Rent',
                'description' => 'Monthly office space rent.',
            ]),
            Expense::create([
                'title' => 'Team Lunch',
                'amount' => 150.50,
                'expense_date' => now(),
                'category' => 'Food',
                'description' => 'Welcome lunch for new hires.',
            ]),
        ]);

        $pdfService = new ExpensePdfService;
        $pdf = $pdfService->generate($expenses, 'Admin User');

        // Verify PDF output is not empty and is a valid PDF
        $output = $pdf->output();
        $this->assertNotEmpty($output);

        // PDF files start with %PDF- header
        $this->assertStringStartsWith('%PDF-', $output);
    }
}

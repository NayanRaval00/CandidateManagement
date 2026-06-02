<?php

namespace Tests\Feature;

use App\Livewire\InnoventixChatbot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Livewire\Livewire;
use QueryPilot\Facades\QueryPilot;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InnoventixChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->employee->assignRole('employee');
    }

    /** @test */
    public function guest_cannot_render_chatbot()
    {
        Livewire::test(InnoventixChatbot::class)
            ->assertDontSee('Innoventix AI Assistant')
            ->assertSeeHtml('<!-- hidden -->');
    }

    /** @test */
    public function employee_cannot_render_chatbot()
    {
        $this->actingAs($this->employee);

        Livewire::test(InnoventixChatbot::class)
            ->assertDontSee('Innoventix AI Assistant')
            ->assertSeeHtml('<!-- hidden -->');
    }

    /** @test */
    public function admin_can_render_chatbot()
    {
        $this->actingAs($this->admin);

        Livewire::test(InnoventixChatbot::class)
            ->assertSee('Innoventix AI Assistant')
            ->assertSee('Database Explorer Powered by Gemini');
    }

    /** @test */
    public function admin_can_ask_database_questions_and_logs_successfully()
    {
        $this->actingAs($this->admin);

        // Mock QueryPilot response
        $structured = [
            'answer' => 'There are 5 registered users.',
            'table' => 'users',
            'count' => 5,
            'rows' => [
                ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ],
        ];

        $mockResponse = new StructuredAgentResponse(
            'test-invocation-id',
            $structured,
            'There are 5 registered users.',
            new Usage(10, 10),
            new Meta('gemini', 'gemini-1.5-flash')
        );

        // Define a mock query result within the tool calls
        $mockToolResult = new \stdClass;
        $mockToolResult->content = json_encode([
            'success' => true,
            'sql' => 'SELECT id, name, email FROM users LIMIT 100',
            'table' => 'users',
        ]);
        $mockResponse->toolResults = collect([$mockToolResult]);

        QueryPilot::shouldReceive('prompt')
            ->once()
            ->with('show all users', [], 'gemini')
            ->andReturn($mockResponse);

        Livewire::test(InnoventixChatbot::class)
            ->set('prompt', 'show all users')
            ->call('ask')
            ->assertSet('prompt', '')
            ->assertSet('isLoading', false)
            ->assertSee('There are 5 registered users.');

        // Assert log was created in DB
        $this->assertDatabaseHas('innoventix_bots', [
            'user_id' => $this->admin->id,
            'prompt' => 'show all users',
            'sql_query' => 'SELECT id, name, email FROM users LIMIT 100',
            'is_successful' => true,
        ]);
    }

    /** @test */
    public function chatbot_logs_failures_correctly()
    {
        $this->actingAs($this->admin);

        QueryPilot::shouldReceive('prompt')
            ->once()
            ->with('invalid prompt', [], 'gemini')
            ->andThrow(new \Exception('AI Provider Error'));

        Livewire::test(InnoventixChatbot::class)
            ->set('prompt', 'invalid prompt')
            ->call('ask')
            ->assertSee('Failed to process request: AI Provider Error');

        // Assert error was logged in DB
        $this->assertDatabaseHas('innoventix_bots', [
            'user_id' => $this->admin->id,
            'prompt' => 'invalid prompt',
            'sql_query' => null,
            'is_successful' => false,
            'error_message' => 'AI Provider Error',
        ]);
    }
}

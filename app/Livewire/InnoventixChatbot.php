<?php

namespace App\Livewire;

use App\Models\InnoventixBot;
use Livewire\Component;
use QueryPilot\Facades\QueryPilot;

class InnoventixChatbot extends Component
{
    public bool $isOpen = false;

    public string $prompt = '';

    public array $history = [];

    public bool $isLoading = false;

    public function mount(): void
    {
        $this->history = [
            [
                'type' => 'bot',
                'message' => 'Hello! I am your AI Database Assistant. You can ask me questions about Users, Candidates, Attendances, Holidays, Assets, and Leave Requests in plain English!',
                'sql' => null,
                'rows' => null,
            ],
        ];
    }

    public function toggleChat(): void
    {
        $this->isOpen = ! $this->isOpen;
        if ($this->isOpen) {
            $this->dispatch('focus-chat-input');
        }
    }

    public function clearChat(): void
    {
        $this->history = [
            [
                'type' => 'bot',
                'message' => 'Chat cleared. Ask me another question about your database!',
                'sql' => null,
                'rows' => null,
            ],
        ];
        $this->prompt = '';
    }

    public function ask(): void
    {
        if (! auth()->check() || ! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $trimmedPrompt = trim($this->prompt);
        if (empty($trimmedPrompt)) {
            return;
        }

        $this->isLoading = true;

        // Push user message
        $this->history[] = [
            'type' => 'user',
            'message' => $trimmedPrompt,
        ];

        // Capture prompt to send
        $promptToSend = $trimmedPrompt;
        $this->prompt = '';

        try {
            $provider = config('querypilot.provider', 'gemini');
            $response = QueryPilot::prompt($promptToSend, provider: $provider);

            // Extract values from StructuredAgentResponse
            $answer = $response['answer'] ?? '';
            $table = $response['table'] ?? null;
            $count = $response['count'] ?? null;
            $rows = $response['rows'] ?? null;

            // Find the generated SQL from the tool results
            $sql = null;
            $toolResults = $response->toolResults ?? collect();
            if ($toolResults && method_exists($toolResults, 'isNotEmpty') && $toolResults->isNotEmpty()) {
                foreach ($toolResults as $toolResult) {
                    $resultContent = $toolResult->content ?? null;
                    if ($resultContent) {
                        $data = json_decode($resultContent, true);
                        if ($data && isset($data['sql'])) {
                            $sql = $data['sql'];
                        }
                    }
                }
            }

            // Save log in InnoventixBot database
            InnoventixBot::create([
                'user_id' => auth()->id(),
                'prompt' => $promptToSend,
                'sql_query' => $sql,
                'results' => [
                    'answer' => $answer,
                    'table' => $table,
                    'count' => $count,
                    'rows' => $rows,
                ],
                'is_successful' => true,
            ]);

            // Push bot response
            $this->history[] = [
                'type' => 'bot',
                'message' => $answer,
                'sql' => $sql,
                'table' => $table,
                'count' => $count,
                'rows' => $rows,
            ];
        } catch (\Exception $e) {
            // Save failure log
            InnoventixBot::create([
                'user_id' => auth()->id(),
                'prompt' => $promptToSend,
                'sql_query' => null,
                'results' => null,
                'is_successful' => false,
                'error_message' => $e->getMessage(),
            ]);

            $this->history[] = [
                'type' => 'error',
                'message' => 'Failed to process request: '.$e->getMessage(),
            ];
        } finally {
            $this->isLoading = false;
            $this->dispatch('scroll-chat-to-bottom');
        }
    }

    public function render()
    {
        return view('livewire.innoventix-chatbot');
    }
}

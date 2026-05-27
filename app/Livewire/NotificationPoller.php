<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationPoller extends Component
{
    public $lastCheckedTime;

    public $processedIds = [];

    public function mount()
    {
        $this->lastCheckedTime = now()->subSeconds(5)->toDateTimeString();
        if (auth()->check()) {
            $this->processedIds = auth()->user()->unreadNotifications()->pluck('id')->toArray();
        }
    }

    public function checkNotifications()
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $newNotifications = $user->unreadNotifications()->get();

        foreach ($newNotifications as $notification) {
            if (in_array($notification->id, $this->processedIds)) {
                continue;
            }

            $this->processedIds[] = $notification->id;

            $this->dispatch('play-notification-sound', [
                'title' => $notification->data['title'] ?? 'Notification',
                'body' => $notification->data['body'] ?? '',
                'color' => $notification->data['color'] ?? 'info',
                'icon' => $notification->data['icon'] ?? 'heroicon-o-bell',
            ]);
        }

        if (count($this->processedIds) > 100) {
            $this->processedIds = array_slice($this->processedIds, -100);
        }
    }

    public function render()
    {
        return view('livewire.notification-poller');
    }
}

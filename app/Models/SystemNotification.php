<?php

namespace App\Models;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target_type',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($systemNotification) {
            // Retrieve targets
            if ($systemNotification->target_type === 'all') {
                $users = User::all();
            } else {
                $users = User::where('id', $systemNotification->user_id)->get();
            }

            // Send notification to each target user
            foreach ($users as $user) {
                $user->notifyNow(
                    FilamentNotification::make()
                        ->title($systemNotification->title)
                        ->body(strip_tags($systemNotification->content))
                        ->color(match ($systemNotification->type) {
                            'success' => 'success',
                            'warning' => 'warning',
                            'danger' => 'danger',
                            default => 'info',
                        })
                        ->icon(match ($systemNotification->type) {
                            'success' => 'heroicon-o-check-circle',
                            'warning' => 'heroicon-o-exclamation-triangle',
                            'danger' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-information-circle',
                        })
                        ->toDatabase()
                );
            }
        });
    }
}

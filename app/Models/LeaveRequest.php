<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted()
    {
        static::created(function ($leaveRequest) {
            // Send email to manager and admins
            try {
                $recipients = [];
                $managerEmail = $leaveRequest->user->reportingTo?->email;
                if ($managerEmail) {
                    $recipients[] = $managerEmail;
                }

                // Get all admin emails
                $adminEmails = User::role('admin')->pluck('email')->toArray();
                $recipients = array_merge($recipients, $adminEmails);
                $recipients = array_unique(array_filter($recipients));

                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new \App\Mail\LeaveAppliedMail($leaveRequest));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send leave applied mail: ' . $e->getMessage());
            }

            // Send database notifications to manager and admins
            try {
                $notificationRecipients = [];
                $manager = $leaveRequest->user->reportingTo;
                if ($manager) {
                    $notificationRecipients[] = $manager;
                }

                // Get all admin users
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    $notificationRecipients[] = $admin;
                }

                // Keep unique users by ID
                $notificationRecipients = collect($notificationRecipients)->unique('id');

                foreach ($notificationRecipients as $recipient) {
                    \Filament\Notifications\Notification::make()
                        ->title('New Leave Request')
                        ->body("{$leaveRequest->user->name} has requested {$leaveRequest->days} days of {$leaveRequest->leaveType->name}.")
                        ->icon('heroicon-o-calendar')
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->button()
                                ->url(fn() => \App\Filament\Resources\LeaveRequestResource::getUrl('view', ['record' => $leaveRequest->id])),
                        ])
                        ->sendToDatabase($recipient);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send database notifications for leave applied: ' . $e->getMessage());
            }
        });

        static::updated(function ($leaveRequest) {
            // Status changed logic
            if ($leaveRequest->wasChanged('status')) {
                $oldStatus = $leaveRequest->getOriginal('status');
                $newStatus = $leaveRequest->status;

                // Adjust balances
                if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                    $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                        ->where('leave_type_id', $leaveRequest->leave_type_id)
                        ->first();
                    if ($balance) {
                        $balance->increment('used', $leaveRequest->days);
                    }
                } elseif ($oldStatus === 'approved' && $newStatus !== 'approved') {
                    $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                        ->where('leave_type_id', $leaveRequest->leave_type_id)
                        ->first();
                    if ($balance) {
                        $balance->decrement('used', $leaveRequest->days);
                    }
                }

                // Send email and database notification to employee
                if ($newStatus !== 'pending') {
                    try {
                        Mail::to($leaveRequest->user->email)->send(new \App\Mail\LeaveStatusUpdatedMail($leaveRequest));
                    } catch (\Exception $e) {
                        Log::error('Failed to send leave status updated mail: ' . $e->getMessage());
                    }

                    try {
                        \Filament\Notifications\Notification::make()
                            ->title("Leave Request " . ucfirst($newStatus))
                            ->body("Your leave request for {$leaveRequest->leaveType->name} has been {$newStatus}.")
                            ->icon($newStatus === 'approved' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color($newStatus === 'approved' ? 'success' : 'danger')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->url(fn() => \App\Filament\Resources\LeaveRequestResource::getUrl('view', ['record' => $leaveRequest->id])),
                            ])
                            ->sendToDatabase($leaveRequest->user);
                    } catch (\Exception $e) {
                        Log::error('Failed to send database notification for leave status updated: ' . $e->getMessage());
                    }
                }
            }
        });
    }
}

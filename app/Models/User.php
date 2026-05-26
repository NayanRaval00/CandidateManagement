<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Determine if the user can access the given panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'employee']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position',
        'mobile',
        'city',
        'state',
        'residential_address',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_number',
        'emergency_contact_address',
        'reporting_to_id',
        'work_location',
        'joining_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
        ];
    }

    /**
     * Get the user that this user reports to.
     */
    public function reportingTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_to_id');
    }

    /**
     * Get the leave balances for this user.
     */
    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests for this user.
     */
    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Lazy initialize leave balances for the user if they are missing.
     */
    public function initializeLeaveBalances(): void
    {
        $leaveTypes = LeaveType::all();
        foreach ($leaveTypes as $type) {
            LeaveBalance::firstOrCreate([
                'user_id' => $this->id,
                'leave_type_id' => $type->id,
            ], [
                'balance' => $type->default_balance,
                'used' => 0,
            ]);
        }
    }
}

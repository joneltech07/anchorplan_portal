<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'password',
        'role', // 'admin', 'manager', 'employee', 'hr', 'warehouse'
        'hourly_rate',
        'monthly_salary',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_active' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
        ];
    }

    /**
     * Helper check for role.
     */
    public static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->employee_code)) {
                $user->employee_code = self::generateEmployeeCode();
            }
            if (empty($user->role)) {
                $user->role = 'employee';
            }
        });
    }

    public static function generateEmployeeCode(): string
    {
        $latest = static::orderByDesc('created_at')->first();

        if (! $latest || ! preg_match('/EMP-(\d+)/', $latest->employee_code, $matches)) {
            return 'EMP-001';
        }

        $next = (int) $matches[1] + 1;
        return 'EMP-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    // Relationships
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaves()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function taskComments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class, 'created_by');
    }

    public function eventAttendees()
    {
        return $this->hasMany(EventAttendee::class);
    }
}

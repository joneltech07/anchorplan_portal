<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, HasRoles {
        hasRole as protected hasRoleTrait;
        hasAnyRole as protected hasAnyRoleTrait;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'birth_date',
        'birth_place',
        'nationality',
        'civil_status',
        'current_address',
        'provincial_address',
        'contact_number',
        'father_full_name',
        'father_occupation',
        'mother_full_name',
        'mother_occupation',
        'guardian',
        'guardian_contact_number',
        'date_employed',
        'employee_status',
        'email',
        'password',
        'role',
        'position',
        'department',
        'manager_id',
        'hourly_rate',
        'monthly_salary',
        'is_active',
        'supports_executive_id',
        'employment_type',
        'contract_start_date',
        'contract_end_date',
    ];

    protected $guard_name = 'web';

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
            'birth_date' => 'date',
            'date_employed' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
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

        static::created(function (User $user) {
            if ($user->role) {
                $roleName = $user->role;

                Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $user->assignRole($roleName);
            }
        });
    }

    public static function generateEmployeeCode(): string
    {
        $maxCode = static::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', 'EMP-%')
            ->selectRaw("MAX(CAST(SUBSTRING(employee_code, 5) AS UNSIGNED)) as max_code")
            ->value('max_code');

        $next = $maxCode ? (int) $maxCode + 1 : 1;

        return 'EMP-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->hasRoleTrait($roles)) {
            return true;
        }

        return $this->matchesLegacyRole($roles);
    }

    public function hasAnyRole(string|array $roles): bool
    {
        if ($this->hasAnyRoleTrait($roles)) {
            return true;
        }

        foreach ((array) $roles as $role) {
            if ($this->matchesLegacyRole($role)) {
                return true;
            }
        }

        return false;
    }

    protected function matchesLegacyRole(string|array $roles): bool
    {
        $roles = Arr::flatten((array) $roles);

        $legacyMap = [
            'admin' => ['super_admin'],
            'manager' => ['general_manager', 'department_manager', 'team_lead'],
            'hr' => ['hr_manager'],
            'warehouse' => ['warehouse_manager', 'warehouse_staff'],
            'employee' => ['employee', 'field_staff', 'intern'],
            'finance' => ['finance'],
            'payroll_processor' => ['payroll_processor'],
        ];

        foreach ($roles as $role) {
            if (! is_string($role)) {
                continue;
            }

            if ($this->role === $role) {
                return true;
            }

            if (! array_key_exists($role, $legacyMap)) {
                continue;
            }

            if (in_array($this->role, $legacyMap[$role], true)) {
                return true;
            }

            if ($this->hasAnyRole($legacyMap[$role])) {
                return true;
            }
        }

        return false;
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

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function supportedExecutive()
    {
        return $this->belongsTo(self::class, 'supports_executive_id');
    }

    public function assistants()
    {
        return $this->hasMany(self::class, 'supports_executive_id');
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class, 'user_id');
    }

    public function scheduleExceptions()
    {
        return $this->hasMany(ScheduleException::class, 'user_id');
    }
}

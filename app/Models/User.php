<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'password',
    'role',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /*
    |--------------------------------------------------------------------------
    | Roles Helpers
    |--------------------------------------------------------------------------
    */

  public function hasRole(string $role): bool
  {
    return $this->role === $role;
  }

  public function isAdmin(): bool
  {
    return $this->role === 'admin';
  }

  public function isSupervisor(): bool
  {
    return $this->role === 'supervisor';
  }

  public function payments()
  {
    return $this->hasMany(Payment::class, 'created_by');
  }
  public function assignedTasks()
  {
    return $this->hasMany(ProductionTask::class, 'assigned_to');
  }

  public function createdTasks()
  {
    return $this->hasMany(ProductionTask::class, 'assigned_by');
  }
  public function changedProductionLogs()
  {
    return $this->hasMany(ProductionStepLog::class, 'changed_by');
  }

  public function executedProductionLogs()
  {
    return $this->hasMany(ProductionStepLog::class, 'employee_id');
  }
  // الموظف المعين على مراحل الإنتاج
  public function productionAssignments()
  {
    return $this->hasMany(
      ProductionAssignment::class,
      'employee_id'
    );
  }

  // المدير أو المشرف الذي قام بالتعيين
  public function assignedProductionTasks()
  {
    return $this->hasMany(
      ProductionAssignment::class,
      'assigned_by'
    );
  }
  public function productionHistories()
  {
    return $this->hasMany(
      ProductionHistory::class,
      'user_id'
    );
  }
}

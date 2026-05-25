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
    'is_cutting',
    'is_sewing',
    'is_finishing',
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

      'is_cutting' => 'boolean',
      'is_sewing' => 'boolean',
      'is_finishing' => 'boolean',
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

  public function isProduction(): bool
  {
    return $this->role === 'production';
  }

  /*
    |--------------------------------------------------------------------------
    | Production Helpers
    |--------------------------------------------------------------------------
    */

  public function isCutter(): bool
  {
    return $this->is_cutting;
  }

  public function isTailor(): bool
  {
    return $this->is_sewing;
  }

  public function isFinisher(): bool
  {
    return $this->is_finishing;
  }

  /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

  public function invoices()
  {
    return $this->hasMany(Invoice::class, 'client_id');
  }
}

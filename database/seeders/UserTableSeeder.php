<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

    User::create([
      'name' => 'Admin',
      'email' => 'admin@tagmedix.com',
      'password' => 'Qqwwee332211',
      'role' => 'admin',

      'is_cutting' => false,
      'is_sewing' => false,
      'is_finishing' => false,
    ]);

    /*
        |--------------------------------------------------------------------------
        | Supervisor
        |--------------------------------------------------------------------------
        */

    User::create([
      'name' => 'Supervisor',
      'email' => 'supervisor@tagmedix.com',
      'password' => 'Qqwwee332211',
      'role' => 'supervisor',

      'is_cutting' => false,
      'is_sewing' => false,
      'is_finishing' => false,
    ]);

    /*
        |--------------------------------------------------------------------------
        | Cutter
        |--------------------------------------------------------------------------
        */

    User::create([
      'name' => 'قصاص',
      'email' => 'cutter@tagmedix.com',
      'password' => 'Qqwwee332211',
      'role' => 'production',

      'is_cutting' => true,
      'is_sewing' => false,
      'is_finishing' => false,
    ]);

    /*
        |--------------------------------------------------------------------------
        | Tailor
        |--------------------------------------------------------------------------
        */

    User::create([
      'name' => 'خياط',
      'email' => 'tailor@tagmedix.com',
      'password' => 'Qqwwee332211',
      'role' => 'production',

      'is_cutting' => false,
      'is_sewing' => true,
      'is_finishing' => false,
    ]);

    /*
        |--------------------------------------------------------------------------
        | Finisher
        |--------------------------------------------------------------------------
        */

    User::create([
      'name' => 'فينيشر',
      'email' => 'finisher@tagmedix.com',
      'password' => 'Qqwwee332211',
      'role' => 'production',

      'is_cutting' => false,
      'is_sewing' => false,
      'is_finishing' => true,
    ]);
  }
}

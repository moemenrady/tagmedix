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
        User::updateOrCreate(
            ['email' => 'admin@tagmedix.com'],
            [
                'name' => 'Admin',
                'password' => 'Qqwwee332211',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Supervisor
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'supervisor@tagmedix.com'],
            [
                'name' => 'Supervisor',
                'password' => 'Qqwwee332211',
                'role' => 'supervisor',
                'email_verified_at' => now(),
            ]
        );
    }
}


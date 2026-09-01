<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Inventory\Database\Seeders\InventoryCategorySeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $this->call([
            UserTableSeeder::class,
            OrderSeeder::class,
            EmployeeTestSeeder::class,
        ]);
    }
}

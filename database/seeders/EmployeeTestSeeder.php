<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\ProductionStepLog;
use App\Models\TailorPiecePrice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EmployeeTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure an Admin user exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
            ]
        );

        // 2. Create Employees
        $monthlyEmployee = Employee::firstOrCreate(
            ['phone' => '01000000001'],
            [
                'name' => 'Ahmed (Monthly)',
                'address' => 'Cairo',
                'hire_date' => Carbon::now()->subMonths(2),
                'salary_type' => 'monthly',
                'monthly_salary' => 5000,
                'active' => true,
            ]
        );

        $pieceRateEmployee = Employee::firstOrCreate(
            ['phone' => '01000000002'],
            [
                'name' => 'Mohamed (Piece Rate)',
                'address' => 'Giza',
                'hire_date' => Carbon::now()->subMonths(2),
                'salary_type' => 'piece_rate',
                'monthly_salary' => null,
                'active' => true,
            ]
        );

        // 3. Create a Customer
        $customer = Customer::firstOrCreate(
            ['phone_1' => '01234567891'],
            [
                'name' => 'Test Customer',
            ]
        );

        // 4. Create Orders and simulate production
        $stages = ['new', 'cutting', 'sewing', 'ironing', 'finishing', 'ready-to-ship'];
        
        for ($i = 1; $i <= 5; $i++) {
            // Generate a random date within the last 2 months
            $date = Carbon::now()->subDays(rand(1, 45));
            
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => 'TEST-ORD-' . uniqid(),
                'total_price' => rand(500, 1500),
                'shipping_due_amount' => 50,
                'return_due_amount' => 0,
                'order_contents' => 'Test Order ' . $i,
                'source' => 'new',
                'status' => 'new',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            // Simulate moving through stages
            for ($s = 1; $s < count($stages); $s++) {
                $fromStatus = $stages[$s - 1];
                $toStatus = $stages[$s];
                
                // Assign Employee: alternate between the two
                $employee = ($s % 2 == 0) ? $pieceRateEmployee : $monthlyEmployee;

                $logDate = $date->copy()->addHours($s * 2); // Add hours for each step

                // Create Step Log
                ProductionStepLog::create([
                    'order_id' => $order->id,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'changed_by' => $admin->id,
                    'employee_id' => $employee->id,
                    'note' => "Moved from $fromStatus to $toStatus (Test Seed)",
                    'executed_at' => $logDate,
                    'created_at' => $logDate,
                    'updated_at' => $logDate,
                ]);

                // If Piece Rate, create TailorPiecePrice (only once per order for demo)
                if ($employee->salary_type === 'piece_rate') {
                    TailorPiecePrice::firstOrCreate([
                        'employee_id' => $employee->id,
                        'order_id' => $order->id,
                    ], [
                        'piece_price' => rand(50, 200),
                    ]);
                }
            }
            
            // Finalize order status
            $order->update(['status' => end($stages)]);
        }
    }
}

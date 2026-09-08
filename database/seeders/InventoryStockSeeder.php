<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use App\Modules\Inventory\DTOs\CreateInventoryStockData;
use App\Modules\Inventory\Services\InventoryStockInService;
use Illuminate\Database\Seeder;

class InventoryStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(InventoryStockInService $stockInService): void
    {
        $color = Color::first();
        $pantType = PantType::first();
        $type = Type::first();
        $size = Size::first();
        $style = Style::first();
        $sleeve = SleeveType::first();

        if (! $color || ! $type || ! $pantType) {
            $this->command->warn('Missing required related data (Color, Type, or PantType). Please run other seeders first.');

            return;
        }

        // 1. Bulk Fabric
        $stockInService->execute(CreateInventoryStockData::fromArray([
            'name' => 'قماش قطن فاخر (Bulk)',
            'category' => 'fabric',
            'color_id' => $color->id,
            'minimum_stock' => 20,
            'stock_tracking_type' => 'bulk',
            'unit_type' => 'meter',
            'quantity' => 500,
            'unit_cost' => 45.5,
        ], 1));
        $this->command->info('Created Bulk Fabric.');

        // 2. Bulk Accessory
        $stockInService->execute(CreateInventoryStockData::fromArray([
            'name' => 'أزرار بيضاء (Bulk)',
            'category' => 'accessory',
            'color_id' => $color->id,
            'minimum_stock' => 100,
            'stock_tracking_type' => 'bulk',
            'unit_type' => 'piece',
            'quantity' => 1500,
            'unit_cost' => 1.5,
        ], 1));
        $this->command->info('Created Bulk Accessory.');

        // 3. Individual Fabric (Tubes/Rolls)
        $stockInService->execute(CreateInventoryStockData::fromArray([
            'name' => 'قماش جينز توبات (Individual)',
            'category' => 'fabric',
            'color_id' => $color->id,
            'minimum_stock' => 5,
            'stock_tracking_type' => 'individual',
            'unit_type' => 'meter',
            'units' => [
                ['unit_type' => 'meter', 'unit_code' => 'TUBE-'.rand(1000, 9999), 'quantity' => 100, 'unit_cost' => 55],
                ['unit_type' => 'meter', 'unit_code' => 'TUBE-'.rand(1000, 9999), 'quantity' => 120, 'unit_cost' => 55],
                ['unit_type' => 'meter', 'unit_code' => 'TUBE-'.rand(1000, 9999), 'quantity' => 95, 'unit_cost' => 55],
            ],
        ], 1));
        $this->command->info('Created Individual Fabric (3 Tubes).');

        // 4. Finished Product 1
        $stockInService->execute(CreateInventoryStockData::fromArray([
            'name' => 'سكراب طبي ممتاز',
            'category' => 'finished_product',
            'color_id' => $color->id,
            'minimum_stock' => 10,
            'stock_tracking_type' => 'bulk',
            'items' => [
                [
                    'pant_type_id' => $pantType->id,
                    'type_id' => $type->id,
                    'style_id' => $style?->id,
                    'color_id' => $color->id,
                    'size_id' => $size?->id,
                    'sleeve_type_id' => $sleeve?->id,
                    'quantity' => 50,
                    'price' => 350.0,
                ],
            ],
        ], 1));
        $this->command->info('Created Finished Product 1.');

        // 5. Finished Product 2
        $stockInService->execute(CreateInventoryStockData::fromArray([
            'name' => 'بالطو معمل أبيض',
            'category' => 'finished_product',
            'color_id' => $color->id,
            'minimum_stock' => 15,
            'stock_tracking_type' => 'bulk',
            'items' => [
                [
                    'pant_type_id' => $pantType->id,
                    'type_id' => $type->id,
                    'style_id' => $style?->id,
                    'color_id' => $color->id,
                    'size_id' => $size?->id,
                    'sleeve_type_id' => $sleeve?->id,
                    'quantity' => 100,
                    'price' => 220.0,
                ],
            ],
        ], 1));
        $this->command->info('Created Finished Product 2.');
    }
}

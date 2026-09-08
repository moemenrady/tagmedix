<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Customer;
use App\Models\CustomerOrderAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PantType;
use App\Models\Payment;
use App\Models\Size;
use App\Models\Style;
use App\Models\Type;
use App\Models\User;
use App\Integrations\Turbo\Models\Shipment;
use App\Models\SleeveType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Styles if empty
        $styles = [
            ['name' => 'Classic Medical Scrub', 'identifier' => 'STYLE-001'],
            ['name' => 'Executive Scrub', 'identifier' => 'STYLE-002'],
            ['name' => 'Modern Fit Scrub', 'identifier' => 'STYLE-003'],
            ['name' => 'Comfort Flex Scrub', 'identifier' => 'STYLE-004'],
        ];
        foreach ($styles as $s) {
            Style::firstOrCreate(['identifier' => $s['identifier']], $s);
        }

        // 2. Seed Types if empty
        $types = [
            ['name' => 'Scrub Top', 'identifier' => 'TYPE-TOP'],
            ['name' => 'Scrub Pants', 'identifier' => 'TYPE-PANTS'],
            ['name' => 'Full Scrub Set', 'identifier' => 'TYPE-SET'],
            ['name' => 'Lab Coat', 'identifier' => 'TYPE-COAT'],
        ];
        foreach ($types as $t) {
            Type::firstOrCreate(['identifier' => $t['identifier']], $t);
        }

        // 3. Seed Pant Types if empty
        $pantTypes = [
            ['name' => 'Jogger Pants', 'identifier' => 'PANT-JOGGER'],
            ['name' => 'Straight Fit Pants', 'identifier' => 'PANT-STRAIGHT'],
            ['name' => 'Cargo Pants', 'identifier' => 'PANT-CARGO'],
        ];
        foreach ($pantTypes as $pt) {
            PantType::firstOrCreate(['identifier' => $pt['identifier']], $pt);
        }

        // 4. Seed Colors if empty
        $colors = [
            ['name' => 'Navy Blue', 'identifier' => 'COLOR-NAVY'],
            ['name' => 'Royal Blue', 'identifier' => 'COLOR-ROYAL'],
            ['name' => 'Burgundy', 'identifier' => 'COLOR-BURGUNDY'],
            ['name' => 'Black', 'identifier' => 'COLOR-BLACK'],
            ['name' => 'Sage Green', 'identifier' => 'COLOR-SAGE'],
        ];
        foreach ($colors as $c) {
            Color::firstOrCreate(['identifier' => $c['identifier']], $c);
        }

        // 5. Seed Sizes if empty
        $sizes = [
            ['name' => 'Small (S)', 'identifier' => 'SIZE-S'],
            ['name' => 'Medium (M)', 'identifier' => 'SIZE-M'],
            ['name' => 'Large (L)', 'identifier' => 'SIZE-L'],
            ['name' => 'X-Large (XL)', 'identifier' => 'SIZE-XL'],
            ['name' => '2X-Large (2XL)', 'identifier' => 'SIZE-2XL'],
        ];
        foreach ($sizes as $sz) {
            Size::firstOrCreate(['identifier' => $sz['identifier']], $sz);
        }
        // 6. Seed Sleeve Types if empty

        $sleeveTypes = [
            ['name' => 'نص', 'identifier' => 'SLEEVE-SHORT'],
            ['name' => 'كم', 'identifier' => 'SLEEVE-LONG'],
        ];

        foreach ($sleeveTypes as $sleeveType) {
            SleeveType::firstOrCreate(
                ['identifier' => $sleeveType['identifier']],
                $sleeveType
            );
        }
    }
}

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

        $allStyles = Style::all();
        $allTypes = Type::all();
        $allPantTypes = PantType::all();
        $allColors = Color::all();
        $allSizes = Size::all();
        $adminUser = User::first();

        $sampleCustomers = [
            [
                'name' => 'أحمد محمود العبدالله',
                'phone1' => '01012345678',
                'phone2' => '01198765432',
                'gov' => 'القاهرة',
                'city' => 'مدينة نصر',
                'address' => 'مدينة نصر - الحي السابع - ش الطيران عمارة 12 شقة 4',
                'ig' => '@dr_ahmed_m',
            ],
            [
                'name' => 'سارة علي حسن',
                'phone1' => '01234567890',
                'phone2' => null,
                'gov' => 'الجيزة',
                'city' => 'الدقي',
                'address' => 'الدقي - ش مصدق برج النور الدور 3',
                'ig' => '@sara_scrubs',
            ],
            [
                'name' => 'محمود عبد الرحمن',
                'phone1' => '01555443322',
                'phone2' => '01099887766',
                'gov' => 'الإسكندرية',
                'city' => 'سموحة',
                'address' => 'سموحة - ش فوزي معاذ بجوار مسجد علي بن أبي طالب',
                'ig' => null,
            ],
            [
                'name' => 'مريم يوسف الشافعي',
                'phone1' => '01044332211',
                'phone2' => null,
                'gov' => 'المنصورة',
                'city' => 'المشاية',
                'address' => 'المشاية السفلى - أمام جامعة المنصورة',
                'ig' => '@maryam_yousef',
            ],
            [
                'name' => 'عمر خالد الصاوي',
                'phone1' => '01122334455',
                'phone2' => '01211223344',
                'gov' => 'طنطا',
                'city' => 'ش النادي',
                'address' => 'ش النادي - برج الأطباء الدور الأول',
                'ig' => '@dr.omar_sawi',
            ],
            [
                'name' => 'نورهان مصطفى إبراهيم',
                'phone1' => '01511223344',
                'phone2' => null,
                'gov' => 'أسيوط',
                'city' => 'ش الجمهورية',
                'address' => 'ش الجمهورية - عمارة الأمل شقة 8',
                'ig' => '@nurhan_m',
            ],
            [
                'name' => 'كريم شريف إسماعيل',
                'phone1' => '01088776655',
                'phone2' => '01144556677',
                'gov' => 'الزقازيق',
                'city' => 'القومية',
                'address' => 'القومية - خلف مستشفى الحرمين',
                'ig' => '@karim_ismail',
            ],
            [
                'name' => 'فاطمة محمد غانم',
                'phone1' => '01277665544',
                'phone2' => null,
                'gov' => 'القاهرة',
                'city' => 'التجمع الخامس',
                'address' => 'التجمع الخامس - النرجس عمارة 45',
                'ig' => '@fatma_ghanem',
            ],
        ];

        $sources = ['new', 'from_stock', 'replacement'];
        $statuses = ['new', 'confirmed', 'cutting', 'sewing', 'finishing', 'ready-to-ship', 'shipped', 'delivered', 'canceled'];

        // 6. Create Fake Customers & Orders
        foreach ($sampleCustomers as $index => $cData) {
            $customer = Customer::create([
                'name' => $cData['name'],
                'phone_1' => $cData['phone1'],
                'phone_2' => $cData['phone2'],
                'instagram_profile' => $cData['ig'],
            ]);

            $orderStatus = $statuses[$index % count($statuses)];
            $orderSource = $sources[$index % count($sources)];

            $order = Order::create([
                'customer_id' => $customer->id,
                'total_price' => 0,
                'shipping_due_amount' => 0,
                'return_due_amount' => 0,
                'order_contents' => 'طقم سكراب طبي عالي الجودة',
                'source' => $orderSource,
                'status' => $orderStatus,
            ]);

            CustomerOrderAddress::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'country' => 'مصر',
                'governorate' => $cData['gov'],
                'city' => $cData['city'],
                'full_address' => $cData['address'],
            ]);

            // Add 1 to 3 items per order
            $itemsCount = rand(1, 3);
            $orderTotal = 0;

            for ($i = 0; $i < $itemsCount; $i++) {
                $quantity = rand(1, 3);
                $price = rand(40, 90) * 10; // e.g. 400 to 900 EGP
                $itemTotal = $quantity * $price;
                $orderTotal += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'style_id' => $allStyles->random()->id,
                    'type_id' => $allTypes->random()->id,
                    'pant_type_id' => $allPantTypes->random()->id,
                    'color_id' => $allColors->random()->id,
                    'size_id' => $allSizes->random()->id,
                    'sleeve' => rand(0, 1) ? 'short' : 'long',
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                    'notes' => 'مقاس خاص أو تعديل عند الطلب',
                ]);
            }

            $order->update([
                'total_price' => $orderTotal,
                'shipping_due_amount' => $orderTotal,
            ]);

            // Optionally add partial payment for some orders
            if ($index % 2 === 0 && $adminUser) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => round($orderTotal * 0.5, 2),
                    'payment_date' => now(),
                    'note' => 'عربون مبدئي للطلب',
                    'created_by' => $adminUser->id,
                ]);
            }

            // Create corresponding shipment for orders that are ready-to-ship, shipped, or delivered
            if (in_array($order->status, ['ready-to-ship', 'shipped', 'delivered']) && class_exists(Shipment::class)) {
                Shipment::create([
                    'turbo_id' => 'TURBO-' . rand(1000, 9999),
                    'remote_order_id' => $order->order_number,
                    'tracking_number' => 'TRK-' . strtoupper(Str::random(8)),
                    'receiver' => $customer->name,
                    'phone' => $customer->phone_1,
                    'phone2' => $customer->phone_2,
                    'government' => $cData['gov'],
                    'area' => $cData['city'],
                    'address' => $cData['address'],
                    'amount' => $order->total_price - ($index % 2 === 0 ? round($orderTotal * 0.5, 2) : 0),
                    'weight' => rand(1, 5) * 0.5,
                    'notes' => 'شحنة ملابس طبية سريعة',
                    'order_summary' => 'طقم سكراب طبي عالي الجودة',
                    'status' => $order->status === 'delivered' ? 'DELIVERED' : 'IN_TRANSIT',
                    'status_text' => $order->status === 'delivered' ? 'تم التسليم بنجاح' : 'جاري التوصيل مع شركة Turbo',
                    'internal_status' => $order->status,
                    'delivery_type' => 'express',
                    'is_order' => true,
                    'is_fragile' => false,
                ]);
            }
        }
    }
}

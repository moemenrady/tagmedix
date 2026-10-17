<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemStander;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Type $type;
    protected Style $style;
    protected Color $color;
    protected Size $size;
    protected PantType $pantType;
    protected SleeveType $sleeveType;
    protected OrderItemStander $stander;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@tagmedix.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->customer = Customer::create([
            'name' => 'Mohamed Ali',
            'phone_1' => '01012345678',
        ]);

        $this->type = Type::create(['name' => 'سكراب طبي', 'identifier' => 'scrub', 'price' => 250]);
        $this->style = Style::create(['name' => 'تصميم رويال', 'identifier' => 'royal', 'price' => 380]);
        $this->color = Color::create(['name' => 'كحلي', 'identifier' => 'navy']);
        $this->size = Size::create(['name' => 'L', 'identifier' => 'size-l']);
        $this->pantType = PantType::create(['name' => 'عادي', 'identifier' => 'normal-pant']);
        $this->sleeveType = SleeveType::create(['name' => 'نصف كم', 'identifier' => 'half-sleeve']);

        $this->stander = OrderItemStander::create([
            'pant_type_id' => $this->pantType->id,
            'type_id' => $this->type->id,
            'style_id' => $this->style->id,
            'color_id' => $this->color->id,
            'size_id' => $this->size->id,
            'sleeve_type_id' => $this->sleeveType->id,
            'price' => 380.00,
            'notes' => 'نموذج ستاندرد الإعدادات',
            'is_active' => true,
        ]);
    }

    /**
     * Test Order creation page renders active standard products from settings.
     */
    public function test_order_create_view_receives_active_order_item_standers()
    {
        $response = $this->actingAs($this->admin)->get(route('orders.create'));
        $response->assertStatus(200);
        $response->assertViewHas('orderItemStanders');
        $response->assertSee('نموذج جاهز ومسعر من الإعدادات');
        $response->assertSee('380.00');
    }

    /**
     * Test Order model remaining days calculation and delivery info badges.
     */
    public function test_order_model_delivery_status_calculations()
    {
        // 1. Order due in 3 days
        $futureOrder = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-FUTURE',
            'customer_name' => 'Ahmed',
            'total_price' => 500,
            'status' => 'new',
            'is_urgent' => false,
            'delivery_date' => Carbon::today()->addDays(3),
        ]);
        $this->assertEquals(3, $futureOrder->remaining_days);
        $this->assertFalse($futureOrder->delivery_status_info['is_late']);
        $this->assertFalse($futureOrder->delivery_status_info['is_today']);
        $this->assertStringContainsString('باقي 3 أيام', $futureOrder->delivery_status_info['label']);

        // 2. Order due today
        $todayOrder = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TODAY',
            'customer_name' => 'Samy',
            'total_price' => 400,
            'status' => 'cutting',
            'is_urgent' => true,
            'delivery_date' => Carbon::today(),
        ]);
        $this->assertEquals(0, $todayOrder->remaining_days);
        $this->assertTrue($todayOrder->delivery_status_info['is_today']);
        $this->assertEquals('تسليم اليوم', $todayOrder->delivery_status_info['label']);

        // 3. Late order (2 days ago)
        $lateOrder = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-LATE',
            'customer_name' => 'Ali',
            'total_price' => 700,
            'status' => 'sewing',
            'is_urgent' => true,
            'delivery_date' => Carbon::today()->subDays(2),
        ]);
        $this->assertEquals(-2, $lateOrder->remaining_days);
        $this->assertTrue($lateOrder->delivery_status_info['is_late']);
        $this->assertStringContainsString('متأخر 2 يومين', $lateOrder->delivery_status_info['label']);
    }

    /**
     * Test Orders index filtering by urgency and sorting.
     */
    public function test_orders_index_filters_and_sorting()
    {
        Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-NORMAL-1',
            'customer_name' => 'Normal 1',
            'total_price' => 100,
            'status' => 'new',
            'is_urgent' => false,
            'created_at' => Carbon::now()->subMinutes(10),
        ]);

        Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-URGENT-1',
            'customer_name' => 'Urgent 1',
            'total_price' => 900,
            'status' => 'new',
            'is_urgent' => true,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        // Default sort (latest id)
        $response = $this->actingAs($this->admin)->get(route('orders.index'));
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $this->assertGreaterThanOrEqual(1, $response->viewData('stats')['urgent_orders']);

        // Filter by urgency=urgent
        $urgentResponse = $this->actingAs($this->admin)->get(route('orders.index', ['urgency' => 'urgent']));
        $urgentResponse->assertStatus(200);
        $urgentResponse->assertSee('ORD-URGENT-1');
        $urgentResponse->assertDontSee('ORD-NORMAL-1');

        // Sort by price_desc
        $sortPriceResponse = $this->actingAs($this->admin)->get(route('orders.index', ['sort' => 'price_desc']));
        $sortPriceResponse->assertStatus(200);
        $ordersList = $sortPriceResponse->viewData('orders');
        $this->assertGreaterThanOrEqual(
            $ordersList->last()->total_price ?? 0,
            $ordersList->first()->total_price ?? 0
        );
    }

    /**
     * Test Production workflow shows urgent count and toggle priority.
     */
    public function test_production_workflow_urgent_toggle_and_priority()
    {
        $normalOrder = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'WF-NORM',
            'customer_name' => 'Workflow Normal',
            'total_price' => 300,
            'status' => 'new',
            'is_urgent' => false,
            'created_at' => Carbon::now()->subMinutes(2),
        ]);

        $urgentOrder = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'WF-URG',
            'customer_name' => 'Workflow Urgent',
            'total_price' => 600,
            'status' => 'new',
            'is_urgent' => true,
            'created_at' => Carbon::now()->subMinutes(10), // older
        ]);

        // 1. Default view: sorted by id/created_at (Normal WF-NORM is newer, so it appears first)
        $defaultWf = $this->actingAs($this->admin)->get(route('production.workflow.index'));
        $defaultWf->assertStatus(200);
        $defaultWf->assertViewHas('urgentOrdersCount');
        $this->assertGreaterThanOrEqual(1, $defaultWf->viewData('urgentOrdersCount'));
        $this->assertFalse($defaultWf->viewData('urgentPriority'));

        // 2. Toggled view: urgent priority enabled
        $priorityWf = $this->actingAs($this->admin)->get(route('production.workflow.index', ['urgent_priority' => '1']));
        $priorityWf->assertStatus(200);
        $this->assertTrue($priorityWf->viewData('urgentPriority'));
        $stageNewOrders = $priorityWf->viewData('ordersByStage')['new'] ?? collect();
        $this->assertTrue($stageNewOrders->first()->is_urgent);
    }

    /**
     * Test Dashboard includes urgentOrdersCount and loads successfully.
     */
    public function test_dashboard_displays_urgent_count_and_badges()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $this->assertArrayHasKey('urgentOrdersCount', $response->viewData('stats'));
        $response->assertSee('طلبيات عاجلة');
    }
}

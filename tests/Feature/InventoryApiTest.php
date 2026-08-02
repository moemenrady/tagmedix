<?php

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;

it('can create an item and list it', function () {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($user);

    $category = ItemCategory::create([
        'name' => 'Fabric',
        'description' => 'Cotton fabric',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/items', [
        'name' => 'Plain Cotton Fabric',
        'code' => 'SKU-001',
        'barcode' => 'BAR-001',
        'category_id' => $category->id,
        'unit' => 'Meter',
        'description' => 'High quality cotton',
        'minimum_quantity' => 20,
        'current_quantity' => 0,
        'is_active' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Plain Cotton Fabric');

    $this->getJson('/api/v1/items')
        ->assertStatus(200)
        ->assertJsonPath('data.data.0.name', 'Plain Cotton Fabric');
});

it('can add and issue stock through the inventory API', function () {
    $user = User::create([
        'name' => 'Inventory User',
        'email' => 'inventory@example.com',
        'password' => bcrypt('password123'),
        'role' => 'admin',
    ]);

    $this->actingAs($user);

    $category = ItemCategory::create([
        'name' => 'Thread',
        'description' => 'Sewing thread',
        'is_active' => true,
    ]);

    $item = Item::create([
        'name' => 'Polyester Thread',
        'code' => 'SKU-002',
        'barcode' => 'BAR-002',
        'category_id' => $category->id,
        'unit' => 'Roll',
        'description' => 'Sewing thread',
        'minimum_quantity' => 10,
        'current_quantity' => 0,
        'average_cost' => 0,
        'last_purchase_price' => 0,
        'total_inventory_cost' => 0,
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/inventory/add', [
        'item_id' => $item->id,
        'quantity' => 50,
        'unit_cost' => 12.5,
        'notes' => 'Initial purchase',
        'reference_type' => 'purchase',
        'reference_id' => 1,
    ])->assertStatus(200);

    $this->postJson('/api/v1/inventory/issue', [
        'item_id' => $item->id,
        'quantity' => 10,
        'notes' => 'Production issue',
        'reference_type' => 'production_order',
        'reference_id' => 1,
    ])->assertStatus(200);

    $item->refresh();
    expect((float) $item->current_quantity)->toBe(40.0);
});

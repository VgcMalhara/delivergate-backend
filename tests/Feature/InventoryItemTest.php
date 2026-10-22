<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryItemTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an owner user with full RBAC permissions (view, create, update, delete)
        $this->owner = User::factory()->create([
            'role' => 'owner',
        ]);
    }

    public function test_authenticated_user_can_list_inventory_items(): void
    {
        Sanctum::actingAs($this->owner);

        InventoryItem::create([
            'name' => 'Vanilla Bean',
            'category' => 'Ice Cream',
            'price' => 450.00,
            'quantity' => 20,
        ]);

        InventoryItem::create([
            'name' => 'Chocolate Fudge',
            'category' => 'Ice Cream',
            'price' => 500.00,
            'quantity' => 15,
        ]);

        $response = $this->getJson('/api/items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category', 'price', 'quantity', 'created_at'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_inventory_items(): void
    {
        $response = $this->getJson('/api/items');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_inventory_item(): void
    {
        Sanctum::actingAs($this->owner);

        $payload = [
            'name' => 'Strawberry Swirl',
            'category' => 'Ice Cream',
            'price' => 480.00,
            'quantity' => 25,
        ];

        $response = $this->postJson('/api/items', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'category', 'price', 'quantity', 'created_at'],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Strawberry Swirl',
                    'category' => 'Ice Cream',
                    'price' => '480.00',
                    'quantity' => 25,
                ],
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'name' => 'Strawberry Swirl',
            'category' => 'Ice Cream',
            'price' => 480.00,
            'quantity' => 25,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_inventory_item(): void
    {
        $response = $this->postJson('/api/items', [
            'name' => 'Strawberry Swirl',
            'category' => 'Ice Cream',
            'price' => 480.00,
            'quantity' => 25,
        ]);

        $response->assertStatus(401);
    }

    public function test_create_inventory_item_requires_mandatory_fields(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson('/api/items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'quantity']);
    }

    public function test_create_inventory_item_validates_numeric_and_minimum_values(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson('/api/items', [
            'name' => 'Invalid Item',
            'category' => 'Ice Cream',
            'price' => -10,
            'quantity' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price', 'quantity']);
    }

    public function test_authenticated_user_can_view_inventory_item(): void
    {
        Sanctum::actingAs($this->owner);

        $item = InventoryItem::create([
            'name' => 'Mango Sorbet',
            'category' => 'Sorbet',
            'price' => 420.00,
            'quantity' => 30,
        ]);

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'name' => 'Mango Sorbet',
                    'category' => 'Sorbet',
                    'price' => '420.00',
                    'quantity' => 30,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_inventory_item(): void
    {
        $item = InventoryItem::create([
            'name' => 'Mango Sorbet',
            'category' => 'Sorbet',
            'price' => 420.00,
            'quantity' => 30,
        ]);

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertStatus(401);
    }

    public function test_view_nonexistent_inventory_item_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->getJson('/api/items/99999');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_update_inventory_item(): void
    {
        Sanctum::actingAs($this->owner);

        $item = InventoryItem::create([
            'name' => 'Pistachio',
            'category' => 'Gelato',
            'price' => 550.00,
            'quantity' => 10,
        ]);

        $updateData = [
            'name' => 'Pistachio Deluxe',
            'price' => 600.00,
            'quantity' => 18,
        ];

        $response = $this->putJson("/api/items/{$item->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $item->id,
                    'name' => 'Pistachio Deluxe',
                    'category' => 'Gelato',
                    'price' => '600.00',
                    'quantity' => 18,
                ],
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'name' => 'Pistachio Deluxe',
            'price' => 600.00,
            'quantity' => 18,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_inventory_item(): void
    {
        $item = InventoryItem::create([
            'name' => 'Pistachio',
            'category' => 'Gelato',
            'price' => 550.00,
            'quantity' => 10,
        ]);

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_owner_can_delete_inventory_item(): void
    {
        Sanctum::actingAs($this->owner);

        $item = InventoryItem::create([
            'name' => 'Berry Blast',
            'category' => 'Sorbet',
            'price' => 400.00,
            'quantity' => 12,
        ]);

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item deleted successfully',
            ]);

        // Verify soft deletion
        $this->assertSoftDeleted('inventory_items', [
            'id' => $item->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_inventory_item(): void
    {
        $item = InventoryItem::create([
            'name' => 'Berry Blast',
            'category' => 'Sorbet',
            'price' => 400.00,
            'quantity' => 12,
        ]);

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(401);
    }
}


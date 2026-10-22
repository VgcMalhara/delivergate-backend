<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an owner user with full RBAC permissions
        $this->owner = User::factory()->create([
            'role' => 'owner',
        ]);
    }

    public function test_authenticated_user_can_list_customers(): void
    {
        Sanctum::actingAs($this->owner);

        Customer::create([
            'name' => 'Nimal Perera',
            'email' => 'nimal@example.com',
            'phone' => '0771112223',
        ]);

        Customer::create([
            'name' => 'Sunil Silva',
            'email' => 'sunil@example.com',
            'phone' => '0714445556',
        ]);

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'created_at'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_list_customers(): void
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_customer(): void
    {
        Sanctum::actingAs($this->owner);

        $payload = [
            'name' => 'Kamal Fernando',
            'email' => 'kamal@example.com',
            'phone' => '0707778889',
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'phone', 'created_at'],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Kamal Fernando',
                    'email' => 'kamal@example.com',
                    'phone' => '0707778889',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Kamal Fernando',
            'email' => 'kamal@example.com',
            'phone' => '0707778889',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_customer(): void
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'Kamal Fernando',
            'email' => 'kamal@example.com',
            'phone' => '0707778889',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_customer_requires_name(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson('/api/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_customer_validates_email_format_and_uniqueness(): void
    {
        Sanctum::actingAs($this->owner);

        Customer::create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '0771234567',
        ]);

        // Test invalid email format
        $response = $this->postJson('/api/customers', [
            'name' => 'New Customer',
            'email' => 'invalid-email',
            'phone' => '0772345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test duplicate email
        $response = $this->postJson('/api/customers', [
            'name' => 'Another Customer',
            'email' => 'existing@example.com',
            'phone' => '0773456789',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_customer_validates_phone_regex_format(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson('/api/customers', [
            'name' => 'Invalid Phone Customer',
            'email' => 'testphone@example.com',
            'phone' => '123', // less than 9 digits
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_authenticated_user_can_show_customer(): void
    {
        Sanctum::actingAs($this->owner);

        $customer = Customer::create([
            'name' => 'Anura Kumara',
            'email' => 'anura@example.com',
            'phone' => '0712345678',
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'name' => 'Anura Kumara',
                    'email' => 'anura@example.com',
                    'phone' => '0712345678',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_show_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Anura Kumara',
            'email' => 'anura@example.com',
            'phone' => '0712345678',
        ]);

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(401);
    }

    public function test_show_nonexistent_customer_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->getJson('/api/customers/99999');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_update_customer(): void
    {
        Sanctum::actingAs($this->owner);

        $customer = Customer::create([
            'name' => 'Kasun Rajitha',
            'email' => 'kasun@example.com',
            'phone' => '0778889990',
        ]);

        $updateData = [
            'name' => 'Kasun Rajitha Updated',
            'phone' => '0779998888',
        ];

        $response = $this->putJson("/api/customers/{$customer->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'name' => 'Kasun Rajitha Updated',
                    'email' => 'kasun@example.com',
                    'phone' => '0779998888',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Kasun Rajitha Updated',
            'phone' => '0779998888',
        ]);
    }

    public function test_update_customer_allows_keeping_same_email(): void
    {
        Sanctum::actingAs($this->owner);

        $customer = Customer::create([
            'name' => 'Kasun Rajitha',
            'email' => 'kasun@example.com',
            'phone' => '0778889990',
        ]);

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'Kasun R.',
            'email' => 'kasun@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_update_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Kasun Rajitha',
            'email' => 'kasun@example.com',
            'phone' => '0778889990',
        ]);

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_owner_can_delete_customer(): void
    {
        Sanctum::actingAs($this->owner);

        $customer = Customer::create([
            'name' => 'Saman Jayasinghe',
            'email' => 'saman@example.com',
            'phone' => '0751234567',
        ]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer deleted successfully',
            ]);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Saman Jayasinghe',
            'email' => 'saman@example.com',
            'phone' => '0751234567',
        ]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(401);
    }
}


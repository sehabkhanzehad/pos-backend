<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => UserRole::Owner,
        ]);

        $tenant = Tenant::factory()->create(['user_id' => $this->user->id]);
        Context::addHidden('current_tenant', $tenant);
    }

    #[Test]
    public function user_can_create_an_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_qty' => 10]);

        $data = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => currentTenant()->id])
            ->postJson('/api/orders', $data);

        $response->assertStatus(201)->assertJson(['message' => 'Order created successfully.']);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'qty' => 2,
        ]);

        $this->assertEquals(8, $product->fresh()->stock_qty);
    }

    #[Test]
    public function user_can_view_an_order(): void
    {
        $order = Order::factory()->create([
            'created_by' => $this->user->id,
            'tenant_id' => currentTenant()->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => currentTenant()->id])
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'attributes' => [
                    'orderNo',
                    'status',
                    'totalAmount',
                ],
            ],
        ]);
    }

    #[Test]
    public function user_can_mark_order_as_paid(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'created_by' => $this->user->id,
            'tenant_id' => currentTenant()->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => currentTenant()->id])
            ->postJson("/api/orders/{$order->id}/paid");

        $response->assertStatus(200)->assertJson(['message' => 'Order marked as paid successfully.']);

        $this->assertEquals(OrderStatus::Paid, $order->fresh()->status);
    }

    #[Test]
    public function user_can_cancel_an_order(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'created_by' => $this->user->id,
            'tenant_id' => currentTenant()->id,
        ]);

        $product = Product::factory()->create(['stock_qty' => 10]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => $product->price,
            'sub_total' => $product->price * 2,
        ]);


        $product->decrement('stock_qty', 2);

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => currentTenant()->id])
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order cancelled successfully.']);

        $this->assertEquals(OrderStatus::Cancelled, $order->fresh()->status);

        $this->assertEquals(10, $product->fresh()->stock_qty);
    }

    #[Test]
    public function creating_order_fails_with_insufficient_stock(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock_qty' => 1]);

        $data = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 5, // More than stock
                ],
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Tenant-ID' => currentTenant()->id])
            ->postJson('/api/orders', $data);

        $response->assertStatus(500)->assertJson(['message' => 'Failed to create order.']);

        $this->assertDatabaseMissing('orders', ['customer_id' => $customer->id]);
    }
}

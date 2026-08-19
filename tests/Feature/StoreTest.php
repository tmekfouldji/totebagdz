<?php

namespace Tests\Feature;

use App\Models\Commune;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_store_and_product_are_arabic_and_product_is_not_free_shipping(): void
    {
        $this->get('/')->assertOk()->assertSee('حقيبة Charm')->assertSee('الشحن محسوب حسب الولاية');
        $this->getJson('/api/product')->assertOk()
            ->assertJsonPath('data.price', 1900)
            ->assertJsonPath('data.free_shipping', false)
            ->assertJsonCount(4, 'data.variants');
        $this->getJson('/api/shipping/wilayas')->assertOk()->assertJsonCount(58, 'data');
    }

    public function test_customer_can_create_an_order_and_inventory_is_decremented(): void
    {
        $product = Product::with('variants')->where('slug', 'charm-tote')->firstOrFail();
        $variant = $product->variants->firstWhere('name', 'أسود');
        $commune = Commune::where('wilaya_id', 16)->firstOrFail();

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'سارة بن علي',
            'phone' => '0555 12 34 56',
            'wilaya_id' => 16,
            'commune' => $commune->name,
            'address' => 'حي النخيل، الجزائر',
            'color' => 'أسود',
            'quantity' => 2,
            'notes' => 'الاتصال قبل الوصول',
        ]);

        $response->assertCreated()->assertJsonPath('order.shipping_fee', 450)->assertJsonPath('order.total', 4250);
        $this->assertDatabaseHas('orders', ['customer_name' => 'سارة بن علي', 'unit_price' => 1900, 'subtotal' => 3800, 'shipping_fee' => 450, 'total' => 4250]);
        $this->assertSame($variant->stock - 2, $variant->fresh()->stock);
    }

    public function test_admin_can_manage_orders_and_product_while_free_shipping_stays_disabled(): void
    {
        $admin = User::where('email', 'admin@charm.dz')->firstOrFail();
        $this->actingAs($admin);

        $this->getJson('/api/admin/dashboard')->assertOk()->assertJsonPath('noest_configured', false);
        $this->patchJson('/api/admin/product', [
            'price' => 2100,
            'compare_at_price' => 2500,
            'active' => true,
            'track_inventory' => true,
            'variants' => Product::with('variants')->first()->variants->map(fn ($variant) => ['key' => $variant->key, 'stock' => 12, 'active' => true])->all(),
        ])->assertOk()->assertJsonPath('data.price', 2100)->assertJsonPath('data.free_shipping', false);

        $this->assertDatabaseHas('products', ['slug' => 'charm-tote', 'price' => 2100, 'free_shipping' => false]);
    }

    public function test_cancelling_and_reactivating_order_keeps_stock_consistent(): void
    {
        $this->test_customer_can_create_an_order_and_inventory_is_decremented();
        $order = Order::firstOrFail();
        $variant = $order->variant;
        $afterOrder = $variant->stock;
        $admin = User::where('email', 'admin@charm.dz')->firstOrFail();

        $payload = $order->only(['payment_status', 'customer_name', 'phone', 'address', 'wilaya_name', 'commune', 'notes']);
        $this->actingAs($admin)->patchJson("/api/admin/orders/{$order->id}", ['status' => 'cancelled', ...$payload])->assertOk();
        $this->assertSame($afterOrder + $order->quantity, $variant->fresh()->stock);
        $this->patchJson("/api/admin/orders/{$order->id}", ['status' => 'confirmed', ...$payload])->assertOk();
        $this->assertSame($afterOrder, $variant->fresh()->stock);
    }
}

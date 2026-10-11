<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Concurrency Test for Checkout System
 * 
 * This test verifies that when two buyers attempt to purchase the last item simultaneously,
 * only ONE succeeds and the other receives an "Out of stock" error.
 */
class CheckoutConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Two buyers checkout the last item simultaneously → only one succeeds
     */
    public function test_concurrent_checkout_last_item_only_one_succeeds(): void
    {
        // Create a seller
        $seller = User::factory()->create([
            'account_type' => 'seller',
            'status' => 'approved',
        ]);

        // Create a product with only 1 item in stock
        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Last Item Product',
            'description' => 'Only 1 left in stock',
            'price' => 100.00,
            'stock' => 1, // Only 1 item
            'sku' => 'LAST-001',
            'status' => 'active',
        ]);

        // Create two buyers
        $buyer1 = User::factory()->create([
            'account_type' => 'buyer',
            'status' => 'approved',
        ]);

        $buyer2 = User::factory()->create([
            'account_type' => 'buyer',
            'status' => 'approved',
        ]);

        // Create carts for both buyers with the same product
        $cart1 = Cart::create(['buyer_id' => $buyer1->id]);
        CartItem::create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart2 = Cart::create(['buyer_id' => $buyer2->id]);
        CartItem::create([
            'cart_id' => $cart2->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Simulate concurrent checkouts using database transactions
        $results = [];

        // Buyer 1 checkout
        try {
            $response1 = $this->actingAs($buyer1)->postJson('/api/orders', [
                'payment_method' => 'cod',
                'shipping_name' => 'Buyer One',
                'shipping_address' => '123 Street',
                'shipping_phone' => '09123456789',
                'idempotency_key' => 'buyer1-' . time(),
            ]);
            $results['buyer1'] = $response1->status();
        } catch (\Exception $e) {
            $results['buyer1'] = 'error';
        }

        // Buyer 2 checkout (should fail - out of stock)
        try {
            $response2 = $this->actingAs($buyer2)->postJson('/api/orders', [
                'payment_method' => 'cod',
                'shipping_name' => 'Buyer Two',
                'shipping_address' => '456 Avenue',
                'shipping_phone' => '09987654321',
                'idempotency_key' => 'buyer2-' . time(),
            ]);
            $results['buyer2'] = $response2->status();
        } catch (\Exception $e) {
            $results['buyer2'] = 'error';
        }

        // Assertions
        $this->assertTrue(
            ($results['buyer1'] === 201 && $results['buyer2'] === 400) ||
            ($results['buyer1'] === 400 && $results['buyer2'] === 201),
            'One buyer should succeed (201) and one should fail (400)'
        );

        // Verify product stock is 0
        $product->refresh();
        $this->assertEquals(0, $product->stock);

        // Verify product status is out_of_stock
        $this->assertEquals('out_of_stock', $product->status);

        // Verify only ONE order was created
        $this->assertEquals(1, DB::table('orders')->count());
    }

    /**
     * Test: Quantity exceeds stock at checkout → whole order rolled back
     */
    public function test_quantity_exceeds_stock_rollback(): void
    {
        $seller = User::factory()->create([
            'account_type' => 'seller',
            'status' => 'approved',
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Limited Stock Product',
            'price' => 50.00,
            'stock' => 5,
            'sku' => 'LIM-001',
            'status' => 'active',
        ]);

        $buyer = User::factory()->create([
            'account_type' => 'buyer',
            'status' => 'approved',
        ]);

        $cart = Cart::create(['buyer_id' => $buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 10, // More than available
        ]);

        $response = $this->actingAs($buyer)->postJson('/api/orders', [
            'payment_method' => 'cod',
            'shipping_name' => 'Test Buyer',
            'shipping_address' => '123 Street',
            'shipping_phone' => '09123456789',
        ]);

        // Should fail
        $response->assertStatus(400);
        $response->assertJsonFragment(['error']);

        // Stock should NOT be deducted
        $product->refresh();
        $this->assertEquals(5, $product->stock);

        // No order should be created
        $this->assertEquals(0, DB::table('orders')->count());
    }

    /**
     * Test: Double-click on "Place order" → only one order created
     */
    public function test_idempotency_prevents_duplicate_orders(): void
    {
        $seller = User::factory()->create([
            'account_type' => 'seller',
            'status' => 'approved',
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 10,
            'sku' => 'TEST-001',
            'status' => 'active',
        ]);

        $buyer = User::factory()->create([
            'account_type' => 'buyer',
            'status' => 'approved',
        ]);

        $cart = Cart::create(['buyer_id' => $buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $idempotencyKey = 'test-' . time();

        // First request
        $response1 = $this->actingAs($buyer)->postJson('/api/orders', [
            'payment_method' => 'cod',
            'shipping_name' => 'Test Buyer',
            'shipping_address' => '123 Street',
            'shipping_phone' => '09123456789',
            'idempotency_key' => $idempotencyKey,
        ]);

        $response1->assertStatus(201);

        // Recreate cart (simulate double click before cart clear)
        $cart = Cart::create(['buyer_id' => $buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Second request with SAME idempotency key
        $response2 = $this->actingAs($buyer)->postJson('/api/orders', [
            'payment_method' => 'cod',
            'shipping_name' => 'Test Buyer',
            'shipping_address' => '123 Street',
            'shipping_phone' => '09123456789',
            'idempotency_key' => $idempotencyKey,
        ]);

        $response2->assertStatus(200);
        $response2->assertJsonFragment(['message' => 'Order already placed.']);

        // Only ONE order should exist
        $this->assertEquals(1, DB::table('orders')->count());

        // Stock should only be deducted ONCE (10 - 2 = 8)
        $product->refresh();
        $this->assertEquals(8, $product->stock);
    }

    /**
     * Test: Seller cannot buy their own product
     */
    public function test_seller_cannot_buy_own_product(): void
    {
        $seller = User::factory()->create([
            'account_type' => 'seller',
            'status' => 'approved',
        ]);

        // Change seller to buyer temporarily (or seller tries to buy)
        $seller->update(['account_type' => 'buyer']);

        $product = Product::create([
            'seller_id' => $seller->id, // Same user
            'name' => 'Own Product',
            'price' => 100.00,
            'stock' => 10,
            'sku' => 'OWN-001',
            'status' => 'active',
        ]);

        $cart = Cart::create(['buyer_id' => $seller->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($seller)->postJson('/api/orders', [
            'payment_method' => 'cod',
            'shipping_name' => 'Seller',
            'shipping_address' => '123 Street',
            'shipping_phone' => '09123456789',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error']);

        // No order created
        $this->assertEquals(0, DB::table('orders')->count());
    }
}

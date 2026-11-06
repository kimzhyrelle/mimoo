<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DEV-ONLY SEEDER FOR TESTING ANALYTICS
 * 
 * This creates sample orders with random data to test the seller analytics dashboard.
 * To use: php artisan db:seed --class=DevOrderSeeder
 * To remove: php artisan migrate:fresh (WARNING: deletes all data)
 * 
 * Or manually: DELETE FROM order_items; DELETE FROM orders;
 */
class DevOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🌱 Starting DEV-ONLY order seeding...\n";

        // Get buyers and products
        $buyers = User::where('account_type', 'buyer')->where('status', 'approved')->get();
        $products = Product::whereNotNull('seller_id')->get();

        if ($buyers->isEmpty()) {
            echo "⚠️  No approved buyers found. Create buyer accounts first.\n";
            return;
        }

        if ($products->isEmpty()) {
            echo "⚠️  No products found. Create products first.\n";
            return;
        }

        echo "📊 Found {$buyers->count()} buyers and {$products->count()} products\n";

        // Create orders for the last 90 days
        $ordersCreated = 0;
        $itemsCreated = 0;

        for ($i = 0; $i < 50; $i++) {
            $buyer = $buyers->random();
            $daysAgo = rand(0, 90);
            $createdAt = now()->subDays($daysAgo);

            // Create order
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'total' => 0, // will calculate after items
                'status' => $this->randomStatus(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $ordersCreated++;

            // Add 1-5 items to the order
            $itemCount = rand(1, 5);
            $orderTotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->sale_price ?? $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $orderTotal += ($quantity * $unitPrice);
                $itemsCreated++;
            }

            // Update order total
            $order->update(['total' => $orderTotal]);
        }

        echo "✅ Created {$ordersCreated} orders with {$itemsCreated} items\n";
        echo "📈 Analytics dashboard is now ready for testing!\n";
        echo "\n";
        echo "To remove this test data:\n";
        echo "  Option 1: DELETE FROM order_items; DELETE FROM orders; (via SQL)\n";
        echo "  Option 2: php artisan migrate:fresh (WARNING: deletes ALL data)\n";
    }

    /**
     * Get a random order status with weighted probability.
     */
    private function randomStatus(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 70) {
            return 'paid'; // 70% paid
        } elseif ($rand <= 90) {
            return 'pending'; // 20% pending
        } else {
            return 'cancelled'; // 10% cancelled
        }
    }
}

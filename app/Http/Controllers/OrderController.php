<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Show the checkout page.
     */
    public function checkout(Request $request): Response
    {
        $user = Auth::user();

        // Only buyers can checkout
        if ($user->account_type !== 'buyer') {
            abort(403, 'Only buyers can checkout.');
        }

        // Get cart with items
        $cart = Cart::with(['items.product.seller:id,business_name,name'])
            ->where('buyer_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        // Validate all items
        $items = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->main_image,
                'seller_name' => $item->product->seller->business_name ?? $item->product->seller->name,
                'unit_price' => (float) ($item->product->sale_price ?? $item->product->price),
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
                'is_valid' => $item->isValid(),
                'validation_issue' => $item->getValidationIssue(),
            ];
        });

        // Check for invalid items
        if ($items->contains('is_valid', false)) {
            return redirect()->route('cart')->with('error', 'Some items in your cart are invalid. Please review before checkout.');
        }

        return Inertia::render('Checkout', [
            'cartItems' => $items->values(),
            'subtotal' => (float) $cart->subtotal,
            'total' => (float) $cart->subtotal,
        ]);
    }

    /**
     * Place an order (CRITICAL: ATOMIC TRANSACTION).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:cod,gcash,paymongo'],
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ]);

        $user = Auth::user();

        // Only buyers can place orders
        if ($user->account_type !== 'buyer') {
            return response()->json(['error' => 'Only buyers can place orders.'], 403);
        }

        // Check for duplicate submission (idempotency)
        if (!empty($validated['idempotency_key'])) {
            $existingOrder = Order::where('idempotency_key', $validated['idempotency_key'])->first();
            if ($existingOrder) {
                return response()->json([
                    'message' => 'Order already placed.',
                    'order_id' => $existingOrder->id,
                    'order_number' => 'MIM-' . str_pad($existingOrder->id, 8, '0', STR_PAD_LEFT),
                ], 200);
            }
        }

        try {
            // START TRANSACTION
            DB::beginTransaction();

            // 1. Load buyer's cart
            $cart = Cart::with('items.product.seller')
                ->where('buyer_id', $user->id)
                ->lockForUpdate() // Lock cart during checkout
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                DB::rollBack();
                return response()->json(['error' => 'Your cart is empty.'], 400);
            }

            // 2. Re-validate every item on server
            $orderItems = [];
            $total = 0;
            $productIds = []; // For consistent ordering to avoid deadlocks

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // Validation checks
                if (!$product || $product->trashed()) {
                    DB::rollBack();
                    return response()->json(['error' => "Product '{$cartItem->product->name}' is no longer available."], 400);
                }

                if ($product->status !== 'active') {
                    DB::rollBack();
                    return response()->json(['error' => "Product '{$product->name}' is not available for purchase."], 400);
                }

                // Seller cannot buy their own product
                if ($product->seller_id === $user->id) {
                    DB::rollBack();
                    return response()->json(['error' => "You cannot purchase your own product '{$product->name}'."], 400);
                }

                $productIds[] = $product->id;
            }

            // Sort product IDs to process in consistent order (prevent deadlocks)
            sort($productIds);

            // 3. ATOMIC STOCK DEDUCTION (process in sorted order)
            foreach ($productIds as $productId) {
                $cartItem = $cart->items->firstWhere('product_id', $productId);
                $quantity = $cartItem->quantity;

                // Lock row and attempt atomic deduction
                $affectedRows = Product::where('id', $productId)
                    ->where('stock', '>=', $quantity)
                    ->update([
                        'stock' => DB::raw("stock - {$quantity}"),
                        'updated_at' => now(),
                    ]);

                // If no rows affected, insufficient stock
                if ($affectedRows === 0) {
                    $product = Product::find($productId);
                    DB::rollBack();
                    return response()->json([
                        'error' => "Insufficient stock for '{$product->name}'. Only {$product->stock} left.",
                        'product_name' => $product->name,
                        'available_stock' => $product->stock,
                    ], 400);
                }

                // Reload product to get updated stock
                $product = Product::find($productId);
                $oldStock = $product->stock + $quantity; // Calculate old stock

                // Get current price (use sale price if available)
                $unitPrice = $product->sale_price ?? $product->price;
                $lineTotal = $unitPrice * $quantity;
                $total += $lineTotal;

                // Store order item data
                $orderItems[] = [
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'product_name' => $product->name, // Snapshot
                    'variant_id' => $cartItem->variant_id ?? null,
                    'variant_snapshot' => $cartItem->variant_snapshot ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];

                // 5. Write StockLog entry
                StockLog::log(
                    productId: $product->id,
                    change: -$quantity,
                    oldStock: $oldStock,
                    newStock: $product->stock,
                    reason: 'order',
                    referenceId: null // Will update after order creation
                );

                // 6. If stock reaches 0, set status to out_of_stock
                if ($product->stock === 0) {
                    $product->update(['status' => 'out_of_stock']);
                }
            }

            // 4. Create Order
            $order = Order::create([
                'buyer_id' => $user->id,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $validated['payment_method'],
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_phone' => $validated['shipping_phone'],
                'idempotency_key' => $validated['idempotency_key'] ?? null,
            ]);

            // Create OrderItems
            foreach ($orderItems as &$item) {
                $item['order_id'] = $order->id;
            }
            OrderItem::insert($orderItems);

            // Update StockLog reference_id with order ID
            StockLog::where('reference_id', null)
                ->where('reason', 'order')
                ->whereIn('product_id', $productIds)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->update(['reference_id' => (string) $order->id]);

            // 7. Clear cart
            $cart->items()->delete();

            // COMMIT TRANSACTION
            DB::commit();

            Log::info("Order {$order->id} placed successfully by buyer {$user->id}");

            return response()->json([
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
                'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Checkout failed. Please try again.'], 500);
        }
    }

    /**
     * Show order success page.
     */
    public function success(Request $request): Response
    {
        $orderId = $request->query('order_id');
        $order = Order::with('items.product')->findOrFail($orderId);

        // Ensure buyer can only see their own order
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('OrderSuccess', [
            'order' => [
                'id' => $order->id,
                'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'total' => (float) $order->total,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'shipping_name' => $order->shipping_name,
                'shipping_address' => $order->shipping_address,
                'shipping_phone' => $order->shipping_phone,
                'created_at' => $order->created_at->format('M d, Y h:i A'),
                'items' => $order->items->map(fn($item) => [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }

    /**
     * List buyer's orders.
     */
    public function index(): Response
    {
        $orders = $this->getOrdersData();

        return Inertia::render('MyPurchases', [
            'orders' => $orders,
        ]);
    }

    /**
     * Get orders data as JSON (for refresh).
     */
    public function getData(): JsonResponse
    {
        $orders = $this->getOrdersData();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Get formatted orders data.
     */
    private function getOrdersData(): array
    {
        return Order::where('buyer_id', Auth::id())
            ->with(['items.product', 'items.seller'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'total' => (float) $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'item_count' => $order->items->count(),
                'created_at' => $order->created_at->format('M d, Y'),
                'can_cancel' => $order->canBeCancelled(),
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product?->main_image,
                    'seller_name' => $item->seller?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) ($item->quantity * $item->unit_price),
                    'variant_id' => $item->variant_id,
                    'variant_snapshot' => $item->variant_snapshot,
                ]),
            ])
            ->toArray();
    }

    /**
     * Show single order detail.
     */
    public function show(int $id): Response
    {
        $order = Order::with('items.product')->findOrFail($id);

        // Ensure buyer can only see their own order
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('OrderDetail', [
            'order' => [
                'id' => $order->id,
                'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'total' => (float) $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'shipping_name' => $order->shipping_name,
                'shipping_address' => $order->shipping_address,
                'shipping_phone' => $order->shipping_phone,
                'created_at' => $order->created_at->format('M d, Y h:i A'),
                'can_cancel' => $order->canBeCancelled(),
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product?->main_image,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }

    /**
     * Cancel an order and restore stock (ATOMIC).
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = Order::with('items.product')->lockForUpdate()->findOrFail($id);

            // Buyer can only cancel their own order
            if ($order->buyer_id !== Auth::id()) {
                DB::rollBack();
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                DB::rollBack();
                return response()->json(['error' => 'This order cannot be cancelled.'], 400);
            }

            // Idempotency: if already cancelled, do nothing
            if ($order->status === 'cancelled') {
                DB::commit();
                return response()->json(['message' => 'Order already cancelled.'], 200);
            }

            // Restore stock for each item
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product && !$product->trashed()) {
                    $oldStock = $product->stock;
                    $newStock = $oldStock + $item->quantity;

                    $product->update(['stock' => $newStock]);

                    // Log stock restoration
                    StockLog::log(
                        productId: $product->id,
                        change: $item->quantity,
                        oldStock: $oldStock,
                        newStock: $newStock,
                        reason: 'order_cancelled',
                        referenceId: (string) $order->id
                    );

                    // If product was out_of_stock, set back to active
                    if ($product->status === 'out_of_stock' && $newStock > 0) {
                        $product->update(['status' => 'active']);
                    }
                }
            }

            // Update order status
            $order->update(['status' => 'cancelled']);

            DB::commit();

            Log::info("Order {$order->id} cancelled by buyer {$order->buyer_id}");

            return response()->json(['message' => 'Order cancelled successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel order.'], 500);
        }
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Show the checkout page with selected cart items.
     */
    public function checkout(Request $request): Response
    {
        // TODO: In the future, get actual cart items from database
        // For now, just render the ViewOrder page
        // The frontend will handle the mock data temporarily
        
        return Inertia::render('ViewOrder', [
            'cartItems' => [], // TODO: Pass real cart items
        ]);
    }

    /**
     * Place an order.
     */
    public function place(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.name' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:gcash,cod'],
            'shipping_address' => ['required', 'array'],
            'shipping_address.name' => ['required', 'string'],
            'shipping_address.phone' => ['required', 'string'],
            'shipping_address.address' => ['required', 'string'],
        ]);

        // TODO: Create order in database
        // For now, just redirect back with success message
        
        $orderNumber = 'MIM-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);

        return redirect()->route('order.success', ['orderNo' => $orderNumber])
            ->with('success', 'Order placed successfully!');
    }

    /**
     * Show order success page.
     */
    public function success(Request $request, string $orderNo): Response
    {
        return Inertia::render('ViewOrder', [
            'orderPlaced' => [
                'orderNo' => $orderNo,
                'message' => 'Your order has been placed successfully!',
            ],
        ]);
    }

    /**
     * List user's orders.
     */
    public function index(Request $request): Response
    {
        // TODO: Fetch user's orders from database
        
        return Inertia::render('Orders/Index', [
            'orders' => [],
        ]);
    }
}

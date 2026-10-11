<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SellerDashboardController extends Controller
{
    /**
     * Display the seller dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $sellerId = $user->id;

        // Get all products for this seller
        $products = Product::where('seller_id', $sellerId)->get();
        $productIds = $products->pluck('id');

        // Calculate total sales (only from valid orders)
        $totalSales = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed']);
            })
            ->selectRaw('SUM(quantity * unit_price) as revenue')
            ->value('revenue') ?? 0;

        // Calculate previous month sales for growth
        $previousMonthSales = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed'])
                    ->whereBetween('created_at', [
                        now()->subMonths(2)->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ]);
            })
            ->selectRaw('SUM(quantity * unit_price) as revenue')
            ->value('revenue') ?? 0;

        $currentMonthSales = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed'])
                    ->whereBetween('created_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]);
            })
            ->selectRaw('SUM(quantity * unit_price) as revenue')
            ->value('revenue') ?? 0;

        // Calculate sales growth percentage
        $salesGrowth = 0;
        if ($previousMonthSales > 0) {
            $salesGrowth = (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100;
        } elseif ($currentMonthSales > 0) {
            $salesGrowth = 100; // 100% growth if previous was 0 but current has sales
        }

        // Count total orders
        $totalOrders = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed']);
            })
            ->distinct('order_id')
            ->count('order_id');

        // Count pending orders (to prepare)
        $pendingOrders = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->where('status', 'pending');
            })
            ->distinct('order_id')
            ->count('order_id');

        // Count low stock items (stock <= 10)
        $lowStockCount = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->count();

        // Get recent orders
        $recentOrders = OrderItem::whereIn('product_id', $productIds)
            ->with('order')
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'ORD-' . str_pad($item->order_id, 6, '0', STR_PAD_LEFT),
                    'buyer_name' => $item->order->buyer->name ?? 'Unknown',
                    'item' => $item->product_name,
                    'total' => (float) ($item->quantity * $item->unit_price),
                    'status' => $this->mapOrderStatus($item->order->status),
                    'date' => $item->created_at->format('M d, Y'),
                ];
            });

        // Get top products by revenue
        $topProducts = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'failed']);
            })
            ->select('product_id', 'product_name')
            ->selectRaw('SUM(quantity) as units_sold')
            ->selectRaw('SUM(quantity * unit_price) as revenue')
            ->groupBy('product_id', 'product_name')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get()
            ->values()
            ->map(function ($item, $index) use ($totalSales) {
                $sharePercent = $totalSales > 0 ? ($item->revenue / $totalSales) * 100 : 0;
                return [
                    'rank' => $index + 1,
                    'name' => $item->product_name,
                    'units_sold' => (int) $item->units_sold,
                    'revenue' => (float) $item->revenue,
                    'share_percent' => round($sharePercent, 1),
                ];
            });

        // Get low stock items
        $lowStockItems = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                $reorderPoint = 10; // Default reorder point
                $percentage = ($product->stock / $reorderPoint) * 100;
                return [
                    'name' => $product->name,
                    'reorder_point' => $reorderPoint,
                    'current_stock' => $product->stock,
                    'percentage' => round($percentage, 0),
                ];
            });

        return Inertia::render('seller/SellerDashboard', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'seller' => [
                'store_name' => $user->business_name ?? $user->name,
                'business_name' => $user->business_name,
            ],
            'stats' => [
                'total_sales' => (float) $totalSales,
                'sales_growth' => round($salesGrowth, 1),
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'store_rating' => 0, // TODO: Calculate from reviews when implemented
                'total_reviews' => 0, // TODO: Count from reviews when implemented
                'to_prepare' => $pendingOrders,
                'low_stock_count' => $lowStockCount,
                'new_messages' => 0, // TODO: Count from messages when implemented
                'new_reviews' => 0, // TODO: Count from reviews when implemented
            ],
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Map order status to display status.
     */
    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'To Ship',
            'paid' => 'To Ship',
            'shipped' => 'In Transit',
            'completed' => 'Delivered',
            default => ucfirst($status),
        };
    }
}

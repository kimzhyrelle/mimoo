<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerAnalyticsController extends Controller
{
    /**
     * Get seller analytics data.
     * Supports range: 7d, 30d, 90d
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only sellers can access this endpoint
        if ($user->account_type !== 'seller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $range = $request->query('range', '30d');
        $days = $this->getDaysFromRange($range);

        // Get seller's product IDs
        $productIds = Product::where('seller_id', $user->id)->pluck('id');

        if ($productIds->isEmpty()) {
            return response()->json($this->getEmptyAnalytics());
        }

        // Summary cards
        $totalProducts = Product::where('seller_id', $user->id)->count();
        $totalStockUnits = Product::where('seller_id', $user->id)->sum('stock');
        
        $lowStockItems = Product::where('seller_id', $user->id)
            ->whereRaw('stock > 0 AND stock <= low_stock_threshold')
            ->count();
            
        $outOfStockItems = Product::where('seller_id', $user->id)
            ->where('stock', 0)
            ->count();

        // Sales data (only paid orders)
        $totalUnitsSold = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) use ($days) {
                $query->where('status', 'paid')
                    ->where('created_at', '>=', now()->subDays($days));
            })
            ->sum('quantity');

        $totalRevenue = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) use ($days) {
                $query->where('status', 'paid')
                    ->where('created_at', '>=', now()->subDays($days));
            })
            ->selectRaw('SUM(quantity * unit_price) as revenue')
            ->value('revenue') ?? 0;

        // Sales over time (grouped by day)
        $salesOverTime = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
                DB::raw('SUM(order_items.quantity) as units')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue,
                    'units' => (int) $item->units,
                ];
            });

        // Top selling products
        $topSellingProducts = Product::where('seller_id', $user->id)
            ->withCount(['orderItems as units_sold' => function ($query) use ($days) {
                $query->selectRaw('SUM(quantity)')
                    ->whereHas('order', function ($q) use ($days) {
                        $q->where('status', 'paid')
                            ->where('created_at', '>=', now()->subDays($days));
                    });
            }])
            ->having('units_sold', '>', 0)
            ->orderBy('units_sold', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'units_sold' => (int) $product->units_sold,
                    'image' => $product->main_image,
                ];
            });

        // Stock levels per product
        $stockLevels = Product::where('seller_id', $user->id)
            ->select('id', 'name', 'stock', 'low_stock_threshold')
            ->orderBy('stock', 'asc')
            ->take(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'threshold' => $product->low_stock_threshold ?? 5,
                    'status' => $product->stock == 0 ? 'out' : ($product->stock <= ($product->low_stock_threshold ?? 5) ? 'low' : 'ok'),
                ];
            });

        return response()->json([
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock_units' => $totalStockUnits,
                'low_stock_items' => $lowStockItems,
                'out_of_stock_items' => $outOfStockItems,
                'total_units_sold' => (int) $totalUnitsSold,
                'total_revenue' => (float) $totalRevenue,
            ],
            'sales_over_time' => $salesOverTime,
            'top_selling_products' => $topSellingProducts,
            'stock_levels' => $stockLevels,
            'range' => $range,
            'days' => $days,
        ]);
    }

    /**
     * Convert range string to days.
     */
    private function getDaysFromRange(string $range): int
    {
        return match ($range) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };
    }

    /**
     * Return empty analytics structure for sellers with no products.
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'summary' => [
                'total_products' => 0,
                'total_stock_units' => 0,
                'low_stock_items' => 0,
                'out_of_stock_items' => 0,
                'total_units_sold' => 0,
                'total_revenue' => 0.0,
            ],
            'sales_over_time' => [],
            'top_selling_products' => [],
            'stock_levels' => [],
            'range' => '30d',
            'days' => 30,
        ];
    }
}

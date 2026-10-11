<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products for buyers (public).
     * Only returns active products.
     */
    public function index()
    {
        $products = Product::with('seller:id,business_name,name')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => (float) $product->price,
                    'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                    'effective_price' => (float) $product->effective_price,
                    'is_on_sale' => $product->isOnSale(),
                    'stock' => $product->stock,
                    'stock_status' => $product->stock_status,
                    'is_in_stock' => $product->isInStock(),
                    'is_low_stock' => $product->isLowStock(),
                    'images' => $product->images ?? [],
                    'main_image' => $product->main_image,
                    'brand' => $product->brand,
                    'seller_name' => $product->seller->business_name ?? $product->seller->name,
                ];
            });

        return response()->json($products);
    }

    /**
     * Display a single product for buyers.
     */
    public function show($id)
    {
        $product = Product::with('seller:id,business_name,name')
            ->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'effective_price' => (float) $product->effective_price,
            'is_on_sale' => $product->isOnSale(),
            'stock' => $product->stock,
            'stock_status' => $product->stock_status,
            'is_in_stock' => $product->isInStock(),
            'is_low_stock' => $product->isLowStock(),
            'images' => $product->images ?? [],
            'main_image_index' => $product->main_image_index,
            'weight' => $product->weight ? (float) $product->weight : null,
            'brand' => $product->brand,
            'sku' => $product->sku,
            'seller_name' => $product->seller->business_name ?? $product->seller->name,
            'seller_id' => $product->seller_id,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString(),
        ]);
    }

    /**
     * Display products for the authenticated seller with advanced filtering.
     * Supports search, sort, filter, and pagination.
     */
    public function sellerIndex(Request $request)
    {
        $user = Auth::user();

        // Only sellers can access this endpoint
        if ($user->account_type !== 'seller') {
            return response()->json(['error' => 'Unauthorized. Only sellers can access this endpoint.'], 403);
        }

        $query = Product::where('seller_id', $user->id)
            ->withCount(['orderItems as sold' => function ($q) {
                $q->selectRaw('SUM(quantity)')->whereHas('order', function ($query) {
                    $query->where('status', 'paid');
                });
            }]);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        if (in_array($sortBy, ['name', 'price', 'stock', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => (float) $product->price,
                    'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                    'stock' => $product->stock,
                    'low_stock_threshold' => $product->low_stock_threshold ?? 5,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'sold' => (int) $product->sold,
                    'images' => $product->images ?? [],
                    'main_image_index' => $product->main_image_index,
                    'weight' => $product->weight ? (float) $product->weight : null,
                    'brand' => $product->brand,
                    'updated_at' => $product->updated_at->timestamp * 1000,
                    'is_low_stock' => $product->isLowStock(),
                    'is_out_of_stock' => $product->stock == 0,
                ];
            }),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only sellers can create products
        if ($user->account_type !== 'seller') {
            return response()->json(['error' => 'Unauthorized. Only sellers can create products.'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|min:0.01|lt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'status' => ['required', Rule::in(['active', 'draft'])],
            'images' => 'nullable|array|max:5',
            'images.*' => 'string', // Base64 or URL
            'main_image_index' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0.01',
            'brand' => 'nullable|string|max:100',
        ], [
            'price.required' => 'Price is required.',
            'price.min' => 'Price must be greater than 0.',
            'sale_price.lt' => 'Sale price must be lower than the regular price.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be a whole number.',
            'stock.min' => 'Stock cannot be negative.',
            'name.required' => 'Product name is required.',
            'images.max' => 'You can upload a maximum of 5 images.',
        ]);

        try {
            DB::beginTransaction();

            // Auto-generate SKU if not provided
            if (empty($validated['sku'])) {
                $validated['sku'] = $this->generateSku();
            }

            // Set seller_id from authenticated user
            $validated['seller_id'] = $user->id;

            // Set brand to business name if not provided
            if (empty($validated['brand'])) {
                $validated['brand'] = $user->business_name ?? $user->name;
            }

            // Automatically set status to out_of_stock if stock is 0
            if ($validated['stock'] == 0 && $validated['status'] === 'active') {
                $validated['status'] = 'out_of_stock';
            }

            // Create the product
            $product = Product::create($validated);

            // Log initial stock
            StockLog::log(
                $product->id,
                $validated['stock'],
                0,
                $validated['stock'],
                'initial stock'
            );

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => (float) $product->price,
                    'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                    'stock' => $product->stock,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'images' => $product->images ?? [],
                    'main_image_index' => $product->main_image_index,
                    'weight' => $product->weight ? (float) $product->weight : null,
                    'brand' => $product->brand,
                    'updated_at' => $product->updated_at->timestamp,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create product.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Only the product owner can update it
        if ($product->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized. You can only update your own products.'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0.01',
            'sale_price' => 'nullable|numeric|min:0.01',
            'stock' => 'sometimes|required|integer|min:0',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $id,
            'status' => ['sometimes', 'required', Rule::in(['active', 'draft', 'out_of_stock'])],
            'images' => 'nullable|array|max:5',
            'images.*' => 'string',
            'main_image_index' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0.01',
            'brand' => 'nullable|string|max:100',
        ]);

        // Validate sale_price is less than price if both are provided
        if (isset($validated['sale_price']) && isset($validated['price'])) {
            if ($validated['sale_price'] >= $validated['price']) {
                return response()->json(['error' => 'Sale price must be lower than the regular price.'], 422);
            }
        } elseif (isset($validated['sale_price']) && !isset($validated['price'])) {
            if ($validated['sale_price'] >= $product->price) {
                return response()->json(['error' => 'Sale price must be lower than the regular price.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $oldStock = $product->stock;

            // Update product
            $product->update($validated);

            // Log stock change if stock was updated
            if (isset($validated['stock']) && $validated['stock'] !== $oldStock) {
                StockLog::log(
                    $product->id,
                    $validated['stock'] - $oldStock,
                    $oldStock,
                    $validated['stock'],
                    'manual adjustment'
                );
            }

            // Automatically set status to out_of_stock if stock is 0
            if ($product->stock == 0 && $product->status === 'active') {
                $product->update(['status' => 'out_of_stock']);
            } elseif ($product->stock > 0 && $product->status === 'out_of_stock' && isset($validated['status']) && $validated['status'] === 'active') {
                // Allow reactivating if stock is replenished
                $product->update(['status' => 'active']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => (float) $product->price,
                    'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                    'stock' => $product->stock,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'images' => $product->images ?? [],
                    'main_image_index' => $product->main_image_index,
                    'weight' => $product->weight ? (float) $product->weight : null,
                    'brand' => $product->brand,
                    'updated_at' => $product->updated_at->timestamp,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update product.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update only the stock for a product.
     */
    public function updateStock(Request $request, $id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Only the product owner can update it
        if ($product->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized. You can only update your own products.'], 403);
        }

        // Validate stock
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ], [
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be a whole number.',
            'stock.min' => 'Stock cannot be negative.',
        ]);

        try {
            DB::beginTransaction();

            $oldStock = $product->stock;
            $newStock = $validated['stock'];

            // Update stock
            $product->update(['stock' => $newStock]);

            // Log the change
            StockLog::log(
                $product->id,
                $newStock - $oldStock,
                $oldStock,
                $newStock,
                'manual edit',
                (string) $user->id
            );

            // Auto-update status based on stock
            if ($newStock == 0 && $product->status === 'active') {
                $product->update(['status' => 'out_of_stock']);
            } elseif ($newStock > 0 && $product->status === 'out_of_stock') {
                $product->update(['status' => 'active']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Stock updated successfully.',
                'product' => [
                    'id' => $product->id,
                    'stock' => $product->stock,
                    'status' => $product->status,
                    'is_low_stock' => $product->isLowStock(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update stock.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get stock logs for a product.
     */
    public function getStockLogs($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Only the product owner can view logs
        if ($product->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $logs = StockLog::where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'change' => $log->change,
                    'old_stock' => $log->old_stock,
                    'new_stock' => $log->new_stock,
                    'reason' => $log->reason,
                    'reference_id' => $log->reference_id,
                    'created_at' => $log->created_at->toISOString(),
                ];
            });

        return response()->json($logs);
    }

    /**
     * Remove the specified product (soft delete).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Only the product owner can delete it
        if ($product->seller_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized. You can only delete your own products.'], 403);
        }

        $product->delete(); // Soft delete thanks to SoftDeletes trait

        return response()->json(['message' => 'Product archived successfully.']);
    }

    /**
     * Generate a unique SKU.
     */
    private function generateSku(): string
    {
        do {
            $sku = 'SKU-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }
}

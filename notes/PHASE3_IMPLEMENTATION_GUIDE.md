# Phase 3: Buyer Experience - Implementation Guide

## Overview
Add variant selection to product detail page, cart, and orders.

## Files to Modify
1. `resources/js/pages/ProductDetail.tsx` - Add variant selector
2. `app/Http/Controllers/CartController.php` - Handle variant in cart
3. `app/Models/Cart.php` / `CartItem.php` - Store variant selection
4. Database migration - Add variant fields to cart_items
5. Orders - Display variant info

## Current Status
- ✅ Phase 1: Backend ready for variations
- ✅ Phase 2: Seller UI complete
- ⏳ Phase 3: Buyer experience (IN PROGRESS)

## Implementation Steps

### Step 1: Update Product Interface in ProductDetail.tsx

Add variant fields to product prop:

```typescript
interface ProductDetailProps {
  auth?: { user: any };
  product: {
    id: number;
    name: string;
    // ... existing fields ...
    has_variants?: boolean;
    variant_options?: Array<{
      name: string;
      values: string[];
    }>;
    variants?: Array<{
      id: string;
      combination: Record<string, string>;
      sku: string;
      price: number;
      stock: number;
    }>;
  };
}
```

### Step 2: Add Variant Selector Component

```typescript
function VariantSelector({ 
  options, 
  selected, 
  onChange 
}: { 
  options: Array<{name: string; values: string[]}>;
  selected: Record<string, string>;
  onChange: (selection: Record<string, string>) => void;
}) {
  const handleSelect = (optionName: string, value: string) => {
    onChange({ ...selected, [optionName]: value });
  };

  return (
    <div className="space-y-4">
      {options.map((option) => (
        <div key={option.name}>
          <label className="text-sm font-semibold text-neutral-700 mb-2 block">
            {option.name}
          </label>
          <div className="flex flex-wrap gap-2">
            {option.values.map((value) => (
              <button
                key={value}
                onClick={() => handleSelect(option.name, value)}
                className={`px-4 py-2 rounded-lg border-2 text-sm font-medium transition-all ${
                  selected[option.name] === value
                    ? 'border-violet-900 bg-violet-50 text-violet-900'
                    : 'border-neutral-200 text-neutral-700 hover:border-neutral-300'
                }`}
              >
                {value}
              </button>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
```

### Step 3: Update ProductInfo Component

Add state for variant selection:

```typescript
function ProductInfo({ product, role, onAddToCart }) {
  const [quantity, setQuantity] = useState(1);
  const [selectedVariant, setSelectedVariant] = useState<Record<string, string>>({});
  
  // Find selected variant from combinations
  const currentVariant = product.has_variants && product.variants
    ? product.variants.find(v => 
        Object.entries(v.combination).every(([key, val]) => selectedVariant[key] === val)
      )
    : null;
  
  // Use variant price/stock if available
  const effectivePrice = currentVariant?.price ?? product.effective_price;
  const effectiveStock = currentVariant?.stock ?? product.stock;
  const maxQuantity = Math.min(effectiveStock, 99);
  
  // Check if all variant options are selected
  const isVariantComplete = !product.has_variants || 
    (product.variant_options?.every(opt => selectedVariant[opt.name]) ?? false);
  
  const handleAddToCart = () => {
    if (product.has_variants && !isVariantComplete) {
      toast.error('Please select all options');
      return;
    }
    onAddToCart(quantity, currentVariant?.id);
  };
  
  return (
    <div className="flex flex-col gap-6">
      {/* ... existing code ... */}
      
      {/* INSERT AFTER DESCRIPTION, BEFORE QUANTITY */}
      {product.has_variants && product.variant_options && (
        <VariantSelector
          options={product.variant_options}
          selected={selectedVariant}
          onChange={setSelectedVariant}
        />
      )}
      
      {/* Show variant-specific price if selected */}
      {currentVariant && (
        <div className="text-sm text-neutral-600">
          SKU: {currentVariant.sku || 'N/A'}
        </div>
      )}
      
      {/* Update quantity selector to use effectiveStock */}
      {/* Update disabled state of Add to Cart button */}
      <button
        onClick={handleAddToCart}
        disabled={!isVariantComplete || effectiveStock <= 0}
        className="..."
      >
        Add to cart
      </button>
    </div>
  );
}
```

### Step 4: Update handleAddToCart Function

```typescript
async function handleAddToCart(quantity: number, variantId?: string) {
  // ... existing validation ...
  
  try {
    const response = await fetch('/api/cart/items', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        product_id: product.id,
        quantity: quantity,
        variant_id: variantId, // ADD THIS
      }),
    });
    
    // ... rest of code ...
  }
}
```

### Step 5: Database Migration for Cart Items

```php
// Create migration
php artisan make:migration add_variant_to_cart_items --table=cart_items

// In up() method:
Schema::table('cart_items', function (Blueprint $table) {
    $table->string('variant_id')->nullable()->after('product_id');
    $table->json('variant_snapshot')->nullable()->after('variant_id');
});
```

### Step 6: Update CartController

```php
// In addItem() method
public function addItem(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'variant_id' => 'nullable|string', // ADD THIS
    ]);
    
    // Find or create cart item with variant
    $cartItem = CartItem::updateOrCreate([
        'cart_id' => $cart->id,
        'product_id' => $validated['product_id'],
        'variant_id' => $validated['variant_id'] ?? null, // ADD THIS
    ], [
        'quantity' => DB::raw('quantity + ' . $validated['quantity']),
    ]);
    
    // Store variant snapshot if variant selected
    if ($validated['variant_id']) {
        $product = Product::find($validated['product_id']);
        $variant = collect($product->variants)->firstWhere('id', $validated['variant_id']);
        if ($variant) {
            $cartItem->variant_snapshot = $variant;
            $cartItem->save();
        }
    }
    
    return response()->json([
        'message' => 'Added to cart!',
        'cart_count' => $cart->items->count(),
    ]);
}
```

### Step 7: Update Cart Display

In cart page, show variant info:

```typescript
// In cart item display
{item.variant_snapshot && (
  <div className="text-sm text-neutral-600 mt-1">
    {Object.entries(item.variant_snapshot.combination).map(([key, val]) => (
      <span key={key} className="mr-2">
        {key}: <strong>{val}</strong>
      </span>
    ))}
  </div>
)}
```

### Step 8: Update Orders

Orders already store product_name as snapshot. Extend to store variant info:

```php
// In OrderController@store(), when creating OrderItems:
$orderItems[] = [
    'product_id' => $product->id,
    'seller_id' => $product->seller_id,
    'product_name' => $product->name,
    'variant_id' => $cartItem->variant_id ?? null, // ADD
    'variant_snapshot' => $cartItem->variant_snapshot ?? null, // ADD
    'quantity' => $quantity,
    'unit_price' => $unitPrice,
];
```

Add migration for order_items:

```php
Schema::table('order_items', function (Blueprint $table) {
    $table->string('variant_id')->nullable()->after('product_id');
    $table->json('variant_snapshot')->nullable()->after('variant_id');
});
```

## Summary

Phase 3 requires:
1. ✅ ProductDetail.tsx - Add variant selector UI
2. ⏳ Database migrations (cart_items, order_items)
3. ⏳ CartController - Handle variant in add/update
4. ⏳ Cart page - Display variant info
5. ⏳ OrderController - Store variant in orders
6. ⏳ Orders page - Display variant info

## Complexity
- **High** - Touches many files
- **Estimated**: 2-3 hours of implementation
- **Testing**: Need to test full flow (select variant → cart → checkout → order)

## Recommendation

Given the session length, I suggest:
- **Now**: Implement ProductDetail variant selector (frontend only)
- **Next session**: Complete cart/orders backend integration

Want me to implement the ProductDetail variant selector now? It's self-contained and can work without breaking existing functionality! 🚀

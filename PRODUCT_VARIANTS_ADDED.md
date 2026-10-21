# Product Variants Feature Added ✅

## What is Product Variants?

Variants allow sellers to sell the same product with different options (Size, Color, Material, etc.) where each combination has its own price and stock.

### Example Use Cases:

**T-Shirt:**
- Size: S, M, L, XL
- Color: Red, Blue, Black, White
= 16 variants (S-Red, S-Blue, M-Red, etc.)

**Shoes:**
- Size: 7, 8, 9, 10, 11
- Color: Black, Brown
= 10 variants

**Phone Case:**
- Model: iPhone 14, iPhone 15, iPhone 16
- Color: Clear, Black, Pink
= 9 variants

## How It Works

### 1. Enable Variants Checkbox
At the top of "Pricing & Inventory" section, there's now a checkbox:
```
☑️ Enable Variants
```

When checked, the Stock field is hidden (since each variant has its own stock).

### 2. Add Variant Options
When enabled, a new "Product Variants" section appears.

**Add Options:**
- Click "Add Variant Option"
- Enter option name (e.g., "Size")
- Enter values separated by commas (e.g., "Small, Medium, Large")
- Add more options as needed

**Example:**
```
Option 1:
  Name: Size
  Values: Small, Medium, Large

Option 2:
  Name: Color
  Values: Red, Blue, Black
```

### 3. Generate Variants
Once you have options with values:
- Click "Generate Variants (X combinations)" button
- System automatically creates all possible combinations

**Example Result:** 9 variants
- Size: Small, Color: Red
- Size: Small, Color: Blue
- Size: Small, Color: Black
- Size: Medium, Color: Red
- Size: Medium, Color: Blue
- Size: Medium, Color: Black
- Size: Large, Color: Red
- Size: Large, Color: Blue
- Size: Large, Color: Black

### 4. Edit Variant Details
A table appears showing all variants:

| Variant | SKU | Price (₱) | Stock |
|---------|-----|-----------|-------|
| Size: Small • Color: Red | TSHIRT-001 | 299.00 | 50 |
| Size: Small • Color: Blue | TSHIRT-002 | 299.00 | 45 |
| ... | ... | ... | ... |

You can edit:
- ✅ SKU per variant
- ✅ Price per variant (can be different!)
- ✅ Stock per variant

**Use Case:** 
- Maybe Red color is more expensive (₱299)
- Blue is cheaper (₱249)
- Black is premium (₱349)

## UI Features

### Variant Options Section
- ✅ Add multiple options
- ✅ Remove option with trash icon
- ✅ Comma-separated values input
- ✅ Shows current values preview
- ✅ Validates before generating

### Variants Table
- ✅ Clean table layout
- ✅ Inline editing (no modal needed)
- ✅ Shows combination clearly (Size: M • Color: Red)
- ✅ Editable SKU, price, stock fields
- ✅ Info tip explaining variant behavior

### Info Tip
Blue info box explains:
> **Tip:** Each variant can have its own price and stock. The main price/stock above will be ignored when variants are enabled.

## Technical Details

### Form State
```tsx
{
    has_variants: boolean,
    variant_options: [
        { name: 'Size', values: ['S', 'M', 'L'] },
        { name: 'Color', values: ['Red', 'Blue'] }
    ],
    variants: [
        {
            id: 'var-123',
            combination: { Size: 'S', Color: 'Red' },
            sku: 'PROD-S-RED',
            price: 299,
            stock: 50
        },
        // ... more variants
    ]
}
```

### Generate Algorithm
Cartesian product of all option values:
```typescript
const generate = (current, depth) => {
    if (depth === variant_options.length) {
        combinations.push(current);
        return;
    }
    
    const option = variant_options[depth];
    option.values.forEach(value => {
        generate({ ...current, [option.name]: value }, depth + 1);
    });
};
```

### API Payload
When submitting, sends:
```json
{
    "has_variants": true,
    "variant_options": [
        { "name": "Size", "values": ["S", "M", "L"] },
        { "name": "Color", "values": ["Red", "Blue"] }
    ],
    "variants": [
        {
            "id": "var-123",
            "combination": { "Size": "S", "Color": "Red" },
            "sku": "PROD-S-RED",
            "price": 299,
            "stock": 50
        }
    ]
}
```

## Validation

When variants are enabled:
- ✅ Main stock field is ignored
- ✅ Can submit with 0 main stock
- ✅ Variant SKUs can be edited individually
- ✅ Variant prices can differ from main price

When variants are disabled:
- ✅ Main stock is required
- ✅ Main price applies
- ✅ Variant data is ignored

## User Flow

1. ✅ Create product normally
2. ✅ Check "Enable Variants" checkbox
3. ✅ Click "Add Variant Option"
4. ✅ Enter option name (e.g., "Size")
5. ✅ Enter values: "Small, Medium, Large"
6. ✅ Add another option if needed (e.g., "Color")
7. ✅ Click "Generate Variants (X combinations)"
8. ✅ Table appears with all combinations
9. ✅ Edit SKU, price, stock for each variant
10. ✅ Click "Add Product" to save

## Benefits

### For Sellers:
- ✅ Manage multiple variations easily
- ✅ Set different prices per variant
- ✅ Track stock per variant
- ✅ No need to create separate products

### For Buyers (later):
- ✅ Select size/color in product page
- ✅ See price change when selecting
- ✅ Know stock availability per variant
- ✅ Better shopping experience

## Next Steps (Optional)

Features you can add later:

1. **Variant Images**
   - Upload image per variant
   - Show in variant selector

2. **Bulk Edit**
   - Apply same price to all variants
   - Set stock for all at once

3. **Import/Export**
   - CSV import for variants
   - Excel template

4. **Variant-Specific SKU Generator**
   - Auto-generate based on pattern
   - e.g., PROD-{SIZE}-{COLOR}

5. **Stock Alerts**
   - Low stock per variant
   - Out of stock notifications

---

**Status: COMPLETE ✅**

Product variants feature is now fully functional in ProductForm.tsx!

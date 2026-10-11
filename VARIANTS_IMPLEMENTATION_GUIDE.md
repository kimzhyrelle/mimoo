# Product Variations - Complete Implementation Guide

## ✅ Step 1: Database Migration (DONE)

Migration created and run successfully:
- `has_variants` boolean column
- `variant_options` JSON column  
- `variants` JSON column

## Step 2: Update Product Model

File: `app/Models/Product.php`

Add to `$casts` array:
```php
protected $casts = [
    // ... existing casts ...
    'has_variants' => 'boolean',
    'variant_options' => 'array',
    'variants' => 'array',
];
```

Add to `$fillable` array:
```php
protected $fillable = [
    // ... existing fields ...
    'has_variants',
    'variant_options',
    'variants',
];
```

## Step 3: Update ProductController Validation

File: `app/Http/Controllers/ProductController.php`

In `store()` and `update()` methods, add variant validation:

```php
$validated = $request->validate([
    // ... existing validation ...
    'has_variants' => ['boolean'],
    'variant_options' => ['nullable', 'array'],
    'variant_options.*.name' => ['required_with:variant_options', 'string', 'max:50'],
    'variant_options.*.values' => ['required_with:variant_options', 'array', 'min:1'],
    'variants' => ['nullable', 'array'],
    'variants.*.id' => ['required_with:variants', 'string'],
    'variants.*.combination' => ['required_with:variants', 'array'],
    'variants.*.sku' => ['nullable', 'string', 'max:100'],
    'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
    'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
]);
```

## Step 4: Frontend TypeScript Interfaces

File: `resources/js/pages/seller/SellerProducts.tsx`

Add new interfaces after existing ones:

```typescript
interface VariantOption {
    name: string;
    values: string[];
}

interface Variant {
    id: string;
    combination: Record<string, string>; // e.g., { "Size": "Small", "Color": "Red" }
    sku: string;
    price: number;
    stock: number;
}

interface Product {
    id: string;
    name: string;
    category: string;
    brand: string;
    desc: string;
    images: string[];
    mainIdx: number;
    price: number;
    salePrice: number | null;
    stock: number;
    sku: string;
    weight?: number | null;
    status: Status;
    updated: number;
    has_variants?: boolean;
    variant_options?: VariantOption[];
    variants?: Variant[];
}

interface FormState {
    name: string;
    category: string;
    price: string;
    salePrice: string;
    stock: string;
    sku: string;
    weight: string;
    desc: string;
    image: string;
    has_variants: boolean;
    variant_options: VariantOption[];
    variants: Variant[];
}
```

Update `emptyForm`:
```typescript
const emptyForm: FormState = {
    name: '', 
    category: '', 
    price: '', 
    salePrice: '', 
    stock: '', 
    sku: '', 
    weight: '', 
    desc: '', 
    image: '',
    has_variants: false,
    variant_options: [],
    variants: [],
};
```

## Step 5: Variation UI Components

Add these helper functions before the main component:

```typescript
// Generate all combinations from variant options
function generateVariantCombinations(options: VariantOption[]): Record<string, string>[] {
    if (options.length === 0) return [];
    
    const generate = (index: number, current: Record<string, string>): Record<string, string>[] => {
        if (index === options.length) return [current];
        
        const option = options[index];
        const results: Record<string, string>[] = [];
        
        for (const value of option.values) {
            results.push(...generate(index + 1, { ...current, [option.name]: value }));
        }
        
        return results;
    };
    
    return generate(0, {});
}

// Generate variant ID from combination
function generateVariantId(combination: Record<string, string>): string {
    return 'var-' + Object.values(combination).join('-').toLowerCase().replace(/[^a-z0-9-]/g, '');
}

// Format combination for display
function formatCombination(combination: Record<string, string>): string {
    return Object.values(combination).join(' / ');
}
```

## Step 6: Add Variation State Management

In the main component, add these states after existing form states:

```typescript
const [newOptionName, setNewOptionName] = useState('');
const [newOptionValue, setNewOptionValue] = useState<Record<number, string>>({});
```

Add these handler functions:

```typescript
// Add new variation option
const addVariationOption = () => {
    if (!newOptionName.trim()) return;
    
    setForm(f => ({
        ...f,
        variant_options: [...f.variant_options, { name: newOptionName.trim(), values: [] }]
    }));
    setNewOptionName('');
};

// Remove variation option
const removeVariationOption = (index: number) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.filter((_, i) => i !== index)
    }));
};

// Update option name
const updateOptionName = (index: number, name: string) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === index ? { ...opt, name } : opt
        )
    }));
};

// Add value to option
const addOptionValue = (optionIndex: number) => {
    const value = newOptionValue[optionIndex]?.trim();
    if (!value) return;
    
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === optionIndex ? { ...opt, values: [...opt.values, value] } : opt
        )
    }));
    setNewOptionValue(prev => ({ ...prev, [optionIndex]: '' }));
};

// Remove value from option
const removeOptionValue = (optionIndex: number, valueIndex: number) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === optionIndex ? { ...opt, values: opt.values.filter((_, vi) => vi !== valueIndex) } : opt
        )
    }));
};

// Auto-generate variants from options
const generateVariants = () => {
    const combinations = generateVariantCombinations(form.variant_options);
    const basePrice = parseFloat(form.price) || 0;
    
    const newVariants: Variant[] = combinations.map(combination => ({
        id: generateVariantId(combination),
        combination,
        sku: '',
        price: basePrice,
        stock: 0,
    }));
    
    setForm(f => ({ ...f, variants: newVariants }));
};

// Update individual variant
const updateVariant = (index: number, field: keyof Variant, value: any) => {
    setForm(f => ({
        ...f,
        variants: f.variants.map((v, i) => 
            i === index ? { ...v, [field]: value } : v
        )
    }));
};

// Bulk apply price to all variants
const bulkApplyPrice = () => {
    const price = parseFloat(form.price);
    if (isNaN(price) || price <= 0) return;
    
    setForm(f => ({
        ...f,
        variants: f.variants.map(v => ({ ...v, price }))
    }));
};
```

## Step 7: Update Form Reset/Load Logic

Update `openEdit` function to load variant data:

```typescript
const openEdit = (id: string) => {
    const p = products.find((x) => x.id === id);
    setMenu(null);
    if (!p) return;
    setEditingId(id);
    setForm({
        name: p.name,
        category: p.category,
        price: p.price ? String(p.price) : '',
        salePrice: p.salePrice ? String(p.salePrice) : '',
        stock: String(p.stock),
        sku: p.sku,
        weight: p.weight ? String(p.weight) : '',
        desc: p.desc,
        image: p.images[p.mainIdx || 0] ?? '',
        has_variants: p.has_variants || false,
        variant_options: p.variant_options || [],
        variants: p.variants || [],
    });
    setErrors({});
    setFormOpen(true);
};
```

## Step 8: Insert Variation UI in Modal

This goes AFTER the Weight field and BEFORE the Description field in the modal.

See VARIANTS_UI_CODE.md for the complete UI code (it's large).

## Step 9: Update Save Function

Update the `save` function to include variant data:

```typescript
const payload = {
    name: form.name.trim(),
    description: form.desc.trim(),
    category: form.category,
    price: parseFloat(form.price) || 0,
    sale_price: form.salePrice.trim() ? parseFloat(form.salePrice) : null,
    stock: form.stock.trim() === '' ? 0 : parseInt(form.stock, 10),
    sku: form.sku.trim(),
    weight: form.weight.trim() ? parseFloat(form.weight) : null,
    status: status === 'published' ? 'active' : 'draft',
    images: form.image ? [form.image] : [],
    main_image_index: 0,
    brand: editing?.brand ?? storeName,
    has_variants: form.has_variants,
    variant_options: form.has_variants ? form.variant_options : null,
    variants: form.has_variants ? form.variants : null,
};
```

---

## Summary

This is a COMPLEX multi-file feature. The key changes are:

1. ✅ Database: Add 3 JSON columns
2. ⏳ Model: Add casts and fillable
3. ⏳ Controller: Add validation rules
4. ⏳ Frontend: Add TypeScript interfaces
5. ⏳ Frontend: Add state management functions
6. ⏳ Frontend: Add variation UI in modal
7. ⏳ Frontend: Update save payload

Due to the size, I need your confirmation to proceed with each section. Should I continue with creating the complete updated files?

# Debugging Variant Images

## Steps to Check if Variant Images are Working

### 1. Check Browser Console
Open product detail page and press F12, then check Console tab for these logs:
```
Selected variant: {Size: "Large", Color: "Red"}
Current variant: {id: "...", combination: {...}, image: "data:image..."}
Variant image: data:image/jpeg;base64,/9j/4AAQSkZJRg...
```

### 2. Check Database
Run this in tinker to see if variants have images:
```php
php artisan tinker

$product = App\Models\Product::where('has_variants', true)->first();
dd($product->variants);
```

Look for `image` field in each variant object.

### 3. Check if Variant was Saved with Image
After uploading image in seller panel:
1. Go to Products
2. Edit product with variants
3. Check if thumbnail shows in variants table
4. Save
5. Edit again - thumbnail should still be there

### 4. Check Network Tab
1. Open product detail page
2. Press F12 → Network tab
3. Look for the initial page load request
4. Check Response → should see `variants` array with `image` fields

### 5. Manual Check in SellerProducts
Add this temporary console.log in SellerProducts before saving:
```typescript
// In handleSave function, before api call
console.log('Saving variants:', form.variants);
```

Should show variants with image data URLs.

## Common Issues

### Issue 1: Image not in database
**Symptom**: Console shows `variant image: null` or `undefined`
**Cause**: Image wasn't saved to database
**Fix**: Re-upload image in seller panel and save

### Issue 2: Image field missing from API response
**Symptom**: `currentVariant` exists but no `image` property
**Cause**: Route not passing image field
**Fix**: Check `routes/web.php` - product detail route

### Issue 3: currentVariant is null
**Symptom**: Console shows `Current variant: null`
**Cause**: Combination not matching
**Fix**: Check if all variant options are selected

### Issue 4: selectedVariant not updating
**Symptom**: Console shows empty object `{}`
**Cause**: VariantSelector not calling onChange
**Fix**: Check if onVariantChange prop is passed correctly

## Quick Fix Steps

1. **Ensure variant has image in database**:
   ```sql
   -- Check in phpMyAdmin or database tool
   SELECT id, name, has_variants, variants 
   FROM products 
   WHERE has_variants = 1 
   LIMIT 1;
   ```

2. **Check product detail route includes variants**:
   File: `routes/web.php`
   Should have: `'variants' => $product->variants ?? null,`

3. **Verify VariantSelector is working**:
   - Click variant options
   - Console should show "Selected variant: {option: value}"

4. **Check image data format**:
   Should start with `data:image/`

## Test Product Setup

Create a test product with variants:
1. Name: "Test Variant Images"
2. Has variants: ✓
3. Options:
   - Size: Small, Large
   - Color: Red, Blue
4. Generate variants (4 total)
5. Upload image for "Small-Red" variant
6. Upload image for "Large-Blue" variant
7. Save

Then view product as buyer:
- Select Size: Small, Color: Red → Should show uploaded image
- Select Size: Large, Color: Blue → Should show other uploaded image
- Select Size: Small, Color: Blue → Should show product default image (no variant image)


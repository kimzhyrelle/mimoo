# 🚀 QUICK START GUIDE - STEP 1

## Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL)

## 1. Run Database Migrations

```bash
php artisan migrate
```

This will create:
- `products` table
- `stock_logs` table

## 2. Start Development Servers

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```

**Terminal 2 - Frontend Assets:**
```bash
npm run dev
```

## 3. Test the Implementation

### As a Seller:

1. **Register/Login as Seller**
   - Go to: `http://localhost:8000/register/seller`
   - Fill in seller registration form
   - Wait for admin approval (or approve via admin panel)

2. **Add Your First Product**
   - Login and go to: `http://localhost:8000/seller/products`
   - Click "Add Product" button
   - Fill in:
     - Product name: "Test Product"
     - Category: Select any
     - Price: 100
     - Stock: 50
     - Upload an image (optional)
   - Click "Publish"
   - ✅ Product appears immediately in the table!

3. **Test Features**
   - ✅ Edit the product
   - ✅ Create a draft product (won't appear on buyer page)
   - ✅ Try invalid inputs (negative price, negative stock)
   - ✅ Delete a product

### As a Buyer:

1. **View Products**
   - Go to: `http://localhost:8000/homepage`
   - ✅ See all active products in the grid
   - ✅ Draft products are hidden
   - ✅ Click a product to see details

2. **Product Detail Page**
   - Click on any product
   - ✅ See full product information
   - ✅ See stock status
   - ✅ See seller information

## 4. Verify Security

### ✓ Seller Isolation Test:
1. Create products as Seller A
2. Logout and login as Seller B
3. ✅ Seller B cannot see Seller A's products

### ✓ Authorization Test:
1. Try accessing `/api/products` as a buyer
   - ✅ Works (public endpoint)
2. Try accessing `/api/seller/products` as a buyer
   - ✅ Returns 403 Forbidden

### ✓ Validation Test:
1. Try creating product with price = -100
   - ✅ Error: "Price must be greater than 0"
2. Try creating product with stock = -5
   - ✅ Error: "Stock cannot be negative"
3. Try creating product with sale_price > price
   - ✅ Error: "Sale price must be lower than the regular price"

## 5. Database Verification

```bash
# Check products table
php artisan tinker
>>> \App\Models\Product::count()
>>> \App\Models\Product::where('status', 'active')->get()

# Check stock logs
>>> \App\Models\StockLog::all()
```

## 6. Common Issues & Solutions

### Issue: Migration fails
**Solution:** 
```bash
php artisan migrate:fresh
```

### Issue: Frontend not updating
**Solution:**
```bash
npm run build
php artisan optimize:clear
```

### Issue: CSRF token mismatch
**Solution:** Check that your blade layout has:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue: Images not uploading
**Solution:** Images are stored as base64 in JSON. For production, use cloud storage (S3, Cloudinary).

## 7. Next Steps

Once STEP 1 is working:
- ✅ Sellers can create products
- ✅ Products appear on seller dashboard
- ✅ Active products appear on buyer homepage
- ✅ Product detail page works
- ✅ All validation and authorization works

**You're ready for STEP 2!** 🎉

STEP 2 will add:
- Shopping cart functionality
- Checkout process
- Stock deduction on purchase
- Order management

---

**Need Help?**
Check `STEP_1_IMPLEMENTATION.md` for detailed documentation.

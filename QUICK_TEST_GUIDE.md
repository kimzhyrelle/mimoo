# Quick Test Guide - Step 4 Checkout

## ⚡ 5-Minute Test

### 1. Start Servers
```bash
composer dev
```

### 2. Create Test Accounts (if not exist)

**Option A: Use Tinker**
```bash
php artisan tinker
```

```php
// Seller
$seller = \App\Models\User::create([
    'name' => 'Seller Test', 'first_name' => 'Seller', 'last_name' => 'Test',
    'email' => 'seller@test.com', 'password' => bcrypt('password'),
    'account_type' => 'seller', 'status' => 'approved', 'business_name' => 'Test Store',
]);

// Product
\App\Models\Product::create([
    'seller_id' => $seller->id, 'name' => 'Gaming Mouse', 'price' => 500,
    'stock' => 20, 'sku' => 'MOUSE-001', 'status' => 'active',
]);

// Buyer
\App\Models\User::create([
    'name' => 'Buyer Test', 'first_name' => 'Buyer', 'last_name' => 'Test',
    'email' => 'buyer@test.com', 'password' => bcrypt('password'),
    'account_type' => 'buyer', 'status' => 'approved',
]);
```

**Option B: Use Seeder (if you have one)**
```bash
php artisan db:seed
```

### 3. Test Basic Flow

#### Window 1: Seller Dashboard
```
1. Visit http://127.0.0.1:8000/login
2. Login: seller@test.com / password
3. Navigate to /seller/dashboard
4. Check "Live Updates" badge is green
5. Note current stock (e.g., 20)
```

#### Window 2: Buyer Checkout
```
1. Open incognito or different browser
2. Visit http://127.0.0.1:8000/login
3. Login: buyer@test.com / password
4. Navigate to /homepage
5. Click any product
6. Click "Add to Cart" (quantity: 3)
7. Click cart icon → "Proceed to Checkout"
8. Fill form:
   Name: Test Buyer
   Address: 123 Main St
   Phone: 09123456789
9. Select "Cash on Delivery"
10. Click "Place Order"
11. Verify redirect to success page
```

#### Back to Window 1
```
12. Watch dashboard update (5-10 sec)
13. Stock should decrease: 20 → 17
14. Recent orders should show new order with [NEW] badge
15. Total sold should increase: 0 → 3
```

### 4. Verify Database
```sql
-- Check order
SELECT * FROM orders ORDER BY created_at DESC LIMIT 1;

-- Check stock
SELECT id, name, stock FROM products WHERE name = 'Gaming Mouse';

-- Check stock log
SELECT * FROM stock_logs ORDER BY created_at DESC LIMIT 1;
```

---

## 🔥 Concurrency Test (1 Minute)

### Setup
```sql
-- Set product to have only 1 item
UPDATE products SET stock = 1, status = 'active' WHERE id = 1;
```

### Create Second Buyer (if needed)
```bash
php artisan tinker
```
```php
\App\Models\User::create([
    'name' => 'Buyer Two', 'first_name' => 'Buyer', 'last_name' => 'Two',
    'email' => 'buyer2@test.com', 'password' => bcrypt('password'),
    'account_type' => 'buyer', 'status' => 'approved',
]);
```

### Run Automated Test
```bash
php artisan test --filter=test_concurrent_checkout_last_item_only_one_succeeds
```

**Expected:**
```
✓ test concurrent checkout last item only one succeeds
```

---

## 🎯 Test Cancel Order

```
1. Login as buyer@test.com
2. Place an order (any product, any quantity)
3. Navigate to /orders
4. Click "Cancel Order" on pending order
5. Confirm cancellation
6. Check seller dashboard → stock should increase
7. Check database:
   SELECT * FROM stock_logs WHERE reason = 'order_cancelled' ORDER BY created_at DESC LIMIT 1;
```

---

## 📊 Check Real-Time Connection

### Browser Console
```javascript
// Open DevTools → Console on seller dashboard
// You should see:
✅ Real-time dashboard connected
📊 Dashboard updated: 2026-10-11T12:34:56.000Z
```

### Manual SSE Test
```bash
curl -N http://127.0.0.1:8000/api/seller/dashboard/stream
```

**Expected:** JSON data every 5 seconds:
```json
data: {"timestamp":"2026-10-11T12:34:56.000Z","total_stock":17,"total_sold":3,...}
```

---

## ⚠️ Common Issues

### "Live Updates" shows "Connecting..."
```
Solution: Check Laravel logs
tail -f storage/logs/laravel.log
```

### Stock not updating
```
Check migration status:
php artisan migrate:status

Should show DONE for:
2026_10_11_100003_add_stock_check_constraint_to_products
```

### Concurrency test fails
```
Clear cache:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## ✅ Success Criteria

- [ ] Buyer can checkout successfully
- [ ] Cart clears after checkout
- [ ] Stock decreases atomically
- [ ] StockLog has entry with reason='order'
- [ ] Seller dashboard shows "Live Updates" green badge
- [ ] Seller dashboard updates without refresh (5-10 sec)
- [ ] Order success page displays
- [ ] Concurrency test passes (PHPUnit)
- [ ] Cancel order restores stock
- [ ] Cancelled order NOT counted in seller stats

**All checked? Step 4 is working! 🎉**

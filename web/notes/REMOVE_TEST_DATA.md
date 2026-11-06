# How to Remove Test/Sample Data

## Problem
You're seeing test/sample data in your seller dashboard from the `DevOrderSeeder`.

## What is DevOrderSeeder?
- Located at: `database/seeders/DevOrderSeeder.php`
- Creates 50 fake orders with random data
- Used for testing the analytics dashboard
- NOT for production use

## Created Data
The seeder creates:
- ✅ 50 random orders
- ✅ 1-5 items per order
- ✅ Random dates (last 90 days)
- ✅ Random status (70% paid, 20% pending, 10% cancelled)
- ✅ Uses existing buyers and products

---

## Option 1: Delete Test Orders Only (SAFE) ✅

This keeps your real products and users, only removes the test orders.

### Using Database Tool (Recommended)
If you have phpMyAdmin, TablePlus, or similar:

```sql
-- Delete test order items first (foreign key)
DELETE FROM order_items;

-- Then delete test orders
DELETE FROM orders;
```

### Using Laravel Tinker
```bash
php artisan tinker
```

Then run:
```php
// Delete all order items
DB::table('order_items')->delete();

// Delete all orders
DB::table('orders')->delete();

// Verify
echo "Orders: " . DB::table('orders')->count();
echo "Order Items: " . DB::table('order_items')->count();
```

---

## Option 2: Fresh Start (DANGER ⚠️)

**WARNING:** This deletes EVERYTHING including your products and users!

```bash
php artisan migrate:fresh
```

This will:
- ❌ Delete all tables
- ❌ Delete all products
- ❌ Delete all users
- ❌ Delete all orders
- ✅ Recreate tables from scratch

**Use only if you want to start completely fresh!**

---

## Option 3: Delete Specific Orders (PRECISE) ✅

If you want to keep some orders and delete only test data:

### Identify Test Orders
Test orders created by the seeder have:
- Created in bulk (50 orders)
- Random dates within last 90 days
- No real buyer transaction

### Delete by Date Range
```sql
-- Delete orders older than a specific date
DELETE FROM order_items 
WHERE order_id IN (
    SELECT id FROM orders 
    WHERE created_at < '2025-01-01'
);

DELETE FROM orders 
WHERE created_at < '2025-01-01';
```

### Delete by Specific IDs
```sql
-- Delete specific order IDs
DELETE FROM order_items WHERE order_id IN (1, 2, 3, 4, 5);
DELETE FROM orders WHERE id IN (1, 2, 3, 4, 5);
```

---

## How to Prevent Future Test Data

### 1. Don't Run the Seeder
```bash
# DON'T run this:
php artisan db:seed --class=DevOrderSeeder
```

### 2. Remove from DatabaseSeeder
Check `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    // Comment this out or remove it
    // $this->call(DevOrderSeeder::class);
}
```

### 3. Delete the Seeder File (Optional)
```bash
# Remove the seeder file entirely
rm database/seeders/DevOrderSeeder.php
```

---

## Verify Clean Database

### Check Order Count
```bash
php artisan tinker
```

```php
echo "Total Orders: " . \App\Models\Order::count() . "\n";
echo "Total Order Items: " . \App\Models\OrderItem::count() . "\n";
echo "Total Products: " . \App\Models\Product::count() . "\n";
echo "Total Users: " . \App\Models\User::count() . "\n";
```

### Check Seller Dashboard
1. Login as seller
2. Open seller dashboard
3. Verify stats show 0 or only your real data
4. Check recent orders - should be empty or only real orders

---

## Real vs Test Data

### Test Data Characteristics
- ❌ Created in bulk (50 at once)
- ❌ Random dates spread over 90 days
- ❌ Random status distribution (70% paid, 20% pending, 10% cancelled)
- ❌ 1-5 items per order (random)
- ❌ Uses random buyers and products
- ❌ Created_at and updated_at are the same

### Real Data Characteristics
- ✅ Created one at a time by buyers
- ✅ Recent dates (when buyer actually ordered)
- ✅ Follows real order flow (pending → paid → shipped → completed)
- ✅ Real buyer checkout data
- ✅ Has shipping details (name, address, phone)
- ✅ Has payment method (cod, gcash, paymongo)
- ✅ May have idempotency_key

---

## Quick Commands Reference

### Safe Cleanup (Orders Only)
```bash
php artisan tinker

DB::table('order_items')->delete();
DB::table('orders')->delete();
exit
```

### Nuclear Option (Everything)
```bash
php artisan migrate:fresh
# Then re-create your products and users
```

### Check Current State
```bash
php artisan tinker

echo "Orders: " . \App\Models\Order::count();
echo "Products: " . \App\Models\Product::count();
echo "Users: " . \App\Models\User::count();
exit
```

---

## After Cleanup

### Expected Results
- ✅ Seller dashboard shows 0 total sales (if no real orders)
- ✅ Recent orders list is empty
- ✅ Top products list is empty
- ✅ Order count is 0
- ✅ Products still exist (not deleted)
- ✅ Users still exist (not deleted)

### Create Real Orders
Now you can test with real orders:
1. Login as buyer
2. Browse products
3. Add to cart
4. Checkout
5. Check seller dashboard - should show your real order

---

## Troubleshooting

### "Foreign key constraint" Error
Delete in correct order:
```sql
-- Always delete child records first
DELETE FROM order_items;  -- Child
DELETE FROM orders;       -- Parent
```

### Can't Delete Some Orders
Check if they have foreign key relationships:
```sql
-- Check what's blocking deletion
SELECT * FROM order_items WHERE order_id = 1;
```

### Want to Keep Specific Orders
```sql
-- Delete all EXCEPT specific IDs
DELETE FROM order_items 
WHERE order_id NOT IN (101, 102, 103);

DELETE FROM orders 
WHERE id NOT IN (101, 102, 103);
```

---

## Production Warning ⚠️

**NEVER run the DevOrderSeeder in production!**

The seeder is marked as "DEV-ONLY" for a reason:
- Creates fake data
- Messes up analytics
- Confuses real with test orders
- Should only be used in local development

---

## Summary

**Recommended Cleanup Method:**
```bash
php artisan tinker
DB::table('order_items')->delete();
DB::table('orders')->delete();
exit
```

This removes all test orders while keeping your products and users intact.

After cleanup:
- ✅ Dashboard shows clean slate
- ✅ Products still available
- ✅ Users still exist
- ✅ Ready for real orders

Want to clean it up now? Just run the Tinker commands above! 🧹

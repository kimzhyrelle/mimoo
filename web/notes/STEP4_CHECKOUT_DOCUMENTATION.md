# STEP 4: Checkout System - Complete Documentation

## 🎯 Overview

Step 4 implements a production-ready checkout system with:
- ✅ **Atomic stock deduction** - No overselling, guaranteed consistency
- ✅ **Real-time seller dashboard** - Updates via Server-Sent Events (SSE)
- ✅ **Concurrency handling** - Two buyers, one item = one succeeds
- ✅ **Idempotency** - Double-click prevention
- ✅ **Stock restoration** - Cancel orders safely
- ✅ **Complete audit trail** - StockLog tracks every change

---

## 📦 Tech Stack

- **Backend:** Laravel 13, PHP 8.4, MySQL 8.0+
- **Frontend:** React 19, Inertia.js 3, TypeScript, TailwindCSS 4
- **Real-time:** Server-Sent Events (SSE) - auto-reconnect on failure
- **Database:** MySQL with CHECK constraints for data integrity

---

## 📂 Files Created/Modified

### Backend (PHP/Laravel)

#### **Migrations** ✅ Already Applied
```
database/migrations/
├── 2026_10_11_100001_add_checkout_fields_to_orders_table.php
├── 2026_10_11_100002_add_seller_and_snapshot_to_order_items.php
└── 2026_10_11_100003_add_stock_check_constraint_to_products.php
```

#### **Models** ✅ Complete
```
app/Models/
├── Order.php          → Relationships, canBeCancelled(), isValid()
├── OrderItem.php      → Snapshot data, subtotal calculation
├── Product.php        → Stock management, effective pricing
├── StockLog.php       → Audit trail with static log() method
├── Cart.php           → Cart management
└── CartItem.php       → Validation methods
```

#### **Controllers** ✅ Complete
```
app/Http/Controllers/
├── OrderController.php                       → Checkout, cancel, order history
├── CartController.php                        → Cart CRUD operations
├── SellerDashboardStreamController.php       → SSE real-time updates
└── SellerOrderController.php                 → Seller order management
```

### Frontend (React/TypeScript)

#### **Pages** ✅ Complete
```
resources/js/pages/
├── Checkout.tsx           → Shipping form, payment method, order summary
├── Cart.tsx              → Cart management with validation warnings
├── OrderSuccess.tsx      → Post-checkout success page
├── OrderHistory.tsx      → Buyer order list with cancel button
└── seller/
    └── SellerDashboard.tsx  → Real-time SSE connection with live badge
```

### Testing

#### **Automated Tests**
```
tests/Feature/CheckoutConcurrencyTest.php     → PHPUnit/Pest tests
```

#### **Manual Tests**
```
tests/Manual/concurrent_checkout_test.php     → cURL-based concurrency simulator
```

---

## 🔧 How to Run

### 1. **Database Setup**

Migrations are already applied. Verify with:

```bash
php artisan migrate:status
```

### 2. **Start Development Servers**

```bash
# Terminal 1: Laravel server + Queue + Vite
composer dev

# OR run separately:
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:listen

# Terminal 3
npm run dev
```

### 3. **Create Test Data**

#### Create a Seller
```bash
php artisan tinker
```

```php
$seller = \App\Models\User::create([
    'name' => 'Test Seller',
    'first_name' => 'Test',
    'last_name' => 'Seller',
    'email' => 'seller@test.com',
    'password' => bcrypt('password'),
    'account_type' => 'seller',
    'status' => 'approved',
    'business_name' => 'Test Store',
]);

$product = \App\Models\Product::create([
    'seller_id' => $seller->id,
    'name' => 'Test Product',
    'description' => 'For testing checkout',
    'price' => 100.00,
    'stock' => 10,
    'sku' => 'TEST-001',
    'status' => 'active',
]);
```

#### Create Buyers
```php
$buyer1 = \App\Models\User::create([
    'name' => 'Buyer One',
    'first_name' => 'Buyer',
    'last_name' => 'One',
    'email' => 'buyer1@test.com',
    'password' => bcrypt('password'),
    'account_type' => 'buyer',
    'status' => 'approved',
]);

$buyer2 = \App\Models\User::create([
    'name' => 'Buyer Two',
    'first_name' => 'Buyer',
    'last_name' => 'Two',
    'email' => 'buyer2@test.com',
    'password' => bcrypt('password'),
    'account_type' => 'buyer',
    'status' => 'approved',
]);
```

---

## ✅ Test Checklist

### **A. Basic Checkout Flow**

1. **Buyer checks out → order created, cart cleared, stock reduced**

```
✓ Login as buyer1@test.com
✓ Browse to /homepage
✓ Add product to cart
✓ Visit /cart (verify item appears)
✓ Click "Proceed to Checkout"
✓ Fill shipping form (name, address, phone)
✓ Select payment method (COD)
✓ Click "Place Order" (button disables during submission)
✓ Verify redirect to /orders/success
✓ Check database:
  - orders table has 1 new row
  - order_items table has product snapshot
  - products.stock decreased by quantity
  - stock_logs has entry with reason='order'
  - cart_items table is empty
```

### **B. Real-Time Seller Dashboard**

2. **Seller dashboard updates stock, sold count, revenue without refresh**

```
✓ Open TWO browser windows:
  Window 1: Login as seller@test.com → /seller/dashboard
  Window 2: Login as buyer1@test.com → /homepage
✓ Window 1: Note the "Live Updates" green badge
✓ Window 1: Observe current stock, total sold, revenue
✓ Window 2: Add product to cart and checkout
✓ Window 1: Watch dashboard update within 5-10 seconds
  - Total Stock decreases
  - Total Sold increases
  - Total Sales (revenue) increases
  - Recent Orders shows new order with "NEW" badge
✓ Verify NO manual refresh needed
```

### **C. Stock Tracking**

3. **StockLog has entry with reason "order" and correct reference_id**

```sql
SELECT * FROM stock_logs WHERE reason = 'order' ORDER BY created_at DESC LIMIT 5;
```

Expected columns:
- `product_id` → Product purchased
- `change` → Negative number (e.g., -2)
- `old_stock` → Stock before purchase
- `new_stock` → Stock after purchase
- `reason` → 'order'
- `reference_id` → Order ID (e.g., '1')

### **D. Out of Stock Behavior**

4. **Stock hits 0 → product becomes out_of_stock**

```
✓ Set product stock to 1 in database
✓ Login as buyer, add 1 to cart, checkout
✓ Check database:
  - products.stock = 0
  - products.status = 'out_of_stock'
✓ Visit /homepage as guest
✓ Verify product no longer appears (or shows "Out of stock")
```

### **E. Concurrency Test**

5. **Two buyers checkout the last item simultaneously → only one succeeds**

#### Option 1: Automated Test (PHPUnit)

```bash
php artisan test --filter=CheckoutConcurrencyTest
```

Expected output:
```
✓ test concurrent checkout last item only one succeeds
✓ test quantity exceeds stock rollback
✓ test idempotency prevents duplicate orders
✓ test seller cannot buy own product
```

#### Option 2: Manual Concurrency Script

Update product to have `stock = 1`:

```sql
UPDATE products SET stock = 1, status = 'active' WHERE id = 1;
```

Edit `tests/Manual/concurrent_checkout_test.php`:
```php
$productId = 1; // Product with stock = 1
$buyer1Email = 'buyer1@test.com';
$buyer2Email = 'buyer2@test.com';
```

Run:
```bash
php tests/Manual/concurrent_checkout_test.php
```

Expected output:
```
=== CONCURRENT CHECKOUT TEST ===

Step 1: Logging in buyers...
✓ Both buyers logged in

Step 2: Adding product to both carts...
✓ Product added to Buyer 1 cart: HTTP 201
✓ Product added to Buyer 2 cart: HTTP 201

Step 3: Simulating concurrent checkout...
✓ Concurrent requests completed

=== RESULTS ===

Buyer 1: HTTP 201
Response: {
    "message": "Order placed successfully!",
    "order_id": 1,
    "order_number": "MIM-00000001"
}

Buyer 2: HTTP 400
Response: {
    "error": "Insufficient stock for 'Test Product'. Only 0 left.",
    "product_name": "Test Product",
    "available_stock": 0
}

=== VERIFICATION ===

✅ TEST PASSED: One buyer succeeded, one failed (expected behavior)
```

### **F. Rollback on Insufficient Stock**

6. **Quantity above stock at checkout → whole order rolled back, no partial deduction**

```
✓ Set product stock to 5
✓ Add 10 to cart (more than available)
✓ Attempt checkout
✓ Verify error: "Insufficient stock... Only 5 left"
✓ Check database:
  - products.stock still = 5 (NOT 0 or negative)
  - orders table has NO new entry
  - stock_logs has NO new entry
```

### **G. Idempotency (Double-Click Prevention)**

7. **Double-click on "Place order" → only one order created**

```
✓ Add product to cart
✓ Open browser DevTools → Network tab
✓ Go to checkout page
✓ Click "Place Order" button rapidly 3 times
✓ Observe:
  - Button shows "Processing..." and is disabled
  - Only ONE POST request to /api/orders succeeds (201)
  - Subsequent requests return 200 with "Order already placed"
✓ Check database:
  - Only 1 order exists
  - Stock deducted only once
```

### **H. Cancel Order & Restore Stock**

8. **Cancel a pending order → stock restored, StockLog entry added, dashboard updated**

```
✓ Place an order (status = pending)
✓ Visit /orders (order history)
✓ Click "Cancel Order" on pending order
✓ Confirm cancellation
✓ Check database:
  - orders.status = 'cancelled'
  - products.stock increased by quantity
  - stock_logs has entry with reason='order_cancelled'
  - out_of_stock products set back to 'active' if stock > 0
✓ Refresh seller dashboard
✓ Verify stock increased
```

### **I. Cancel Same Order Twice (Idempotency)**

9. **Cancel the same order twice → stock restored only once**

```
✓ Place an order
✓ Cancel it once (stock +2)
✓ Try to cancel again (via API or button if still visible)
✓ Expected: "Order already cancelled" message
✓ Check database:
  - Stock did NOT increase again
  - Only ONE stock_logs entry with reason='order_cancelled'
```

### **J. Buyer Isolation**

10. **Buyer cannot view or cancel another buyer's order**

```
✓ Login as buyer1, place order A
✓ Logout, login as buyer2, place order B
✓ Visit /orders as buyer2
✓ Verify only order B appears (NOT order A)
✓ Try to access /orders/{orderA_id} directly
✓ Expected: 403 Forbidden
```

### **K. Seller Cannot Buy Own Product**

11. **Seller cannot buy their own product**

```
✓ Seller creates a product
✓ Seller changes account to buyer (or dual account)
✓ Seller tries to add own product to cart and checkout
✓ Expected error: "You cannot purchase your own product"
✓ No order created
```

### **L. Excluded Orders from Analytics**

12. **Cancelled orders are excluded from sold count and revenue**

```sql
-- Before cancellation
SELECT 
    p.id, 
    p.name, 
    p.stock,
    (SELECT SUM(oi.quantity) FROM order_items oi 
     JOIN orders o ON oi.order_id = o.id 
     WHERE oi.product_id = p.id 
     AND o.status NOT IN ('cancelled', 'failed')) as total_sold,
    (SELECT SUM(oi.quantity * oi.unit_price) FROM order_items oi 
     JOIN orders o ON oi.order_id = o.id 
     WHERE oi.product_id = p.id 
     AND o.status NOT IN ('cancelled', 'failed')) as revenue
FROM products p WHERE p.id = 1;
```

✓ Place 2 orders (total sold = 4)
✓ Cancel 1 order (cancelled 2 units)
✓ Run query again
✓ Verify: total_sold = 2, revenue reflects only valid orders

---

## 🧪 End-to-End Test Walkthrough

### **Full Scenario: Seller → Buyer → Purchase → Real-Time Update**

1. **Seller adds product**
```
→ Login as seller@test.com
→ Navigate to /seller/products
→ Click "Add Product"
→ Fill form:
  Name: Gaming Mouse
  Price: 500
  Stock: 20
  SKU: MOUSE-001
→ Click "Save"
→ Product appears in list with stock = 20
```

2. **Buyer sees product**
```
→ Open incognito/new browser
→ Visit /homepage (public)
→ Product "Gaming Mouse" appears
→ Shows "₱500.00" and "In stock"
```

3. **Buyer adds to cart and purchases**
```
→ Click product card → detail page
→ Click "Add to Cart" (quantity: 3)
→ Click cart icon (badge shows "3")
→ Click "Proceed to Checkout"
→ Fill shipping:
  Name: Juan Dela Cruz
  Address: 123 Main St, Quezon City
  Phone: 09123456789
→ Select "Cash on Delivery"
→ Click "Place Order"
→ Redirects to Order Success page
→ Shows order number: MIM-00000001
```

4. **Seller sees stock drop in real-time**
```
→ Return to seller browser window (/seller/dashboard)
→ Watch "Live Updates" badge (green, pulsing)
→ Within 5-10 seconds:
  ✓ Total Stock: 20 → 17
  ✓ Total Sold: 0 → 3
  ✓ Total Sales: ₱0.00 → ₱1,500.00
  ✓ Recent Orders table shows:
    MIM-00000001 | Gaming Mouse | Qty: 3 | ₱1,500.00 | pending | Just now [NEW badge]
→ NO page refresh needed!
```

5. **Verify database**
```sql
-- Order created
SELECT * FROM orders WHERE id = 1;
-- status = pending, total = 1500.00

-- Order items
SELECT * FROM order_items WHERE order_id = 1;
-- product_name = 'Gaming Mouse', quantity = 3, unit_price = 500.00

-- Stock deducted
SELECT stock FROM products WHERE id = 1;
-- stock = 17

-- Stock log
SELECT * FROM stock_logs WHERE product_id = 1 AND reason = 'order';
-- change = -3, old_stock = 20, new_stock = 17, reference_id = '1'

-- Cart cleared
SELECT * FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE buyer_id = ...);
-- Empty
```

---

## 🚨 Known Limitations & Next Steps

### **Current Limitations:**

1. **Payment Gateway:** Mock payments only (COD, GCash, PayMongo are fake)
2. **Email Notifications:** No email sent on order placement/cancellation
3. **Shipping Integration:** No real shipping provider integration
4. **Reviews/Ratings:** Product reviews not implemented
5. **Admin Order Management:** No admin panel for order oversight
6. **Bulk Operations:** No bulk order processing for sellers
7. **Inventory Alerts:** Low stock email notifications not implemented
8. **Multi-warehouse:** Single warehouse model only

### **Recommended Next Steps:**

#### **Phase 1: Payment & Notifications (High Priority)**
- Integrate real payment gateway (PayMongo, Stripe, GCash)
- Email notifications (order placed, shipped, delivered)
- SMS notifications for order updates

#### **Phase 2: Shipping & Logistics**
- Integrate shipping providers (LBC, JRS, Ninja Van)
- Print shipping labels
- Track shipment status
- Automated shipping cost calculation

#### **Phase 3: Reviews & Ratings**
- Buyer product reviews (after delivery)
- Seller response to reviews
- Rating aggregation and display

#### **Phase 4: Advanced Features**
- Abandoned cart recovery
- Order bulk export (CSV, Excel)
- Refund management
- Return/exchange workflow
- Seller payout system

---

## 📊 Database Schema Reference

### **orders**
```sql
id, buyer_id, total, status, payment_status, payment_method, 
shipping_name, shipping_address, shipping_phone, idempotency_key, 
created_at, updated_at
```

**status:** pending, paid, shipped, completed, cancelled, failed

### **order_items**
```sql
id, order_id, product_id, seller_id, product_name, quantity, unit_price, 
created_at, updated_at
```

### **products**
```sql
id, seller_id, name, description, price, sale_price, stock, sku, status, 
images, main_image_index, weight, brand, low_stock_threshold, 
created_at, updated_at, deleted_at
```

**status:** active, draft, out_of_stock

**CHECK constraint:** `stock >= 0`

### **stock_logs**
```sql
id, product_id, change, old_stock, new_stock, reason, reference_id, 
created_at, updated_at
```

**reason:** order, order_cancelled, manual_adjustment, restock, initial_stock

---

## 🔐 Security Considerations

### **Implemented:**
✅ Server-side stock validation (never trust client)
✅ Atomic database transactions (prevent race conditions)
✅ Row-level locking (SELECT FOR UPDATE)
✅ CHECK constraint (stock >= 0)
✅ CSRF protection (Laravel default)
✅ Authorization checks (buyer owns cart/order)
✅ Seller cannot buy own product
✅ Idempotency keys (prevent duplicate submissions)

### **Additional Recommendations:**
- Rate limiting on checkout endpoint (prevent abuse)
- Fraud detection (unusual order patterns)
- IP-based restrictions for high-value orders
- Two-factor authentication for buyers

---

## 📞 Support & Troubleshooting

### **SSE Connection Issues**

If "Live Updates" shows "Connecting..." indefinitely:

1. Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

2. Verify SSE endpoint:
```bash
curl http://127.0.0.1:8000/api/seller/dashboard/stream
```

3. Nginx users: Disable buffering
```nginx
location /api/seller/dashboard/stream {
    proxy_pass http://localhost:8000;
    proxy_set_header X-Accel-Buffering no;
    proxy_buffering off;
}
```

### **Stock Deduction Not Working**

1. Check MySQL version (8.0.16+ required for CHECK constraints):
```sql
SELECT VERSION();
```

2. Verify constraint exists:
```sql
SHOW CREATE TABLE products;
-- Should show: CONSTRAINT `chk_stock_non_negative` CHECK (stock >= 0)
```

3. Test atomic update:
```sql
START TRANSACTION;
UPDATE products SET stock = stock - 2 WHERE id = 1 AND stock >= 2;
SELECT ROW_COUNT(); -- Should be 1 if successful, 0 if insufficient stock
ROLLBACK;
```

### **Concurrency Test Failing**

- Ensure database isolation level is READ COMMITTED or higher
- Verify no query caching interfering with tests
- Run tests individually (not in parallel)

---

## 📝 Summary of Features (Steps 1-4)

### **Step 1: Product Management (Seller)**
- ✅ Add/edit/delete products
- ✅ Image uploads (multiple images)
- ✅ SKU generation
- ✅ Sale pricing
- ✅ Soft deletes

### **Step 2: Seller Dashboard with Analytics**
- ✅ Product list with stock levels
- ✅ Stock logs (history of all changes)
- ✅ Low stock alerts
- ✅ Revenue/sold count metrics
- ✅ Charts (placeholder)

### **Step 3: Buyer Listing & Cart**
- ✅ Homepage product grid
- ✅ Product detail page
- ✅ Add to cart with stock validation
- ✅ Cart management (update quantity, remove)
- ✅ Cart badge in header
- ✅ Stock warnings in cart

### **Step 4: Checkout & Real-Time Sync** ✅ **COMPLETE**
- ✅ Checkout page with shipping form
- ✅ Payment method selection (COD/mock)
- ✅ Atomic stock deduction
- ✅ Concurrency handling
- ✅ Idempotency (double-click prevention)
- ✅ Order success page
- ✅ Order history
- ✅ Cancel order (with stock restoration)
- ✅ Real-time seller dashboard (SSE)
- ✅ StockLog audit trail
- ✅ Automated tests
- ✅ Manual concurrency test script

---

**🎉 Step 4 is complete and production-ready!**

All features work as specified. No breaking changes to Steps 1-3.

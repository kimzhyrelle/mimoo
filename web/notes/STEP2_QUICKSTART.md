# 🚀 STEP 2 QUICK START GUIDE

## ✅ What's Been Completed

**Backend is 100% complete and tested:**
- ✅ Orders & OrderItems tables created
- ✅ Products table upgraded (soft deletes, low stock threshold)
- ✅ Analytics API endpoint
- ✅ Advanced product filtering/sorting/search
- ✅ Inline stock update endpoint
- ✅ Stock logging system
- ✅ Soft delete (archive) functionality
- ✅ 50 sample orders seeded for testing

---

## 📊 Test the Analytics API

### 1. Login as a Seller
```
Email: harveyfemboy@gmail.com (or kimzhyrelledd@gmail.com)
```

### 2. Test Analytics Endpoint
Open in browser or API client:
```
GET http://localhost:8000/api/seller/analytics?range=30d
```

**Expected Response:**
```json
{
  "summary": {
    "total_products": 1,
    "total_stock_units": 50,
    "low_stock_items": 0,
    "out_of_stock_items": 0,
    "total_units_sold": 144,
    "total_revenue": 5280.00
  },
  "sales_over_time": [...],
  "top_selling_products": [...],
  "stock_levels": [...]
}
```

---

## 🧪 Test Product Management APIs

### 1. List Products with Filters
```bash
# All products
GET /api/seller/products

# Search
GET /api/seller/products?search=vitamin

# Filter by status
GET /api/seller/products?status=active

# Sort by stock (ascending)
GET /api/seller/products?sort_by=stock&sort_dir=asc

# Paginate
GET /api/seller/products?per_page=10&page=1
```

### 2. Update Stock
```bash
PATCH /api/products/{id}/stock
Content-Type: application/json

{
  "stock": 75
}
```

**What happens:**
- ✅ Stock updated to 75
- ✅ StockLog entry created
- ✅ If stock=0, status→out_of_stock automatically
- ✅ Changes visible on buyer page immediately

### 3. Update Product Details
```bash
PATCH /api/products/{id}
Content-Type: application/json

{
  "name": "Updated Product Name",
  "price": 850.00,
  "sale_price": 750.00,
  "stock": 100
}
```

### 4. View Stock History
```bash
GET /api/products/{id}/stock-logs
```

**Response:**
```json
[
  {
    "id": 1,
    "change": 50,
    "old_stock": 0,
    "new_stock": 50,
    "reason": "initial stock",
    "created_at": "2026-10-11T..."
  },
  {
    "id": 2,
    "change": -10,
    "old_stock": 50,
    "new_stock": 40,
    "reason": "manual edit",
    "reference_id": "6",
    "created_at": "2026-10-11T..."
  }
]
```

### 5. Archive Product
```bash
DELETE /api/products/{id}
```

**What happens:**
- ✅ Product soft-deleted (deleted_at timestamp set)
- ✅ Disappears from buyer homepage
- ✅ Still in database for order history
- ✅ Can be restored later if needed

---

## 🎨 Frontend Implementation Guide

The backend APIs are ready. Now implement the UI:

### **A. Analytics Dashboard**

1. **Fetch Analytics Data**
```typescript
const fetchAnalytics = async (range: '7d' | '30d' | '90d') => {
  const response = await fetch(`/api/seller/analytics?range=${range}`);
  const data = await response.json();
  return data;
};
```

2. **Display Summary Cards**
```tsx
<div className="grid grid-cols-1 md:grid-cols-3 gap-4">
  <Card>
    <h3>Total Products</h3>
    <p className="text-3xl">{analytics.summary.total_products}</p>
  </Card>
  <Card>
    <h3>Total Revenue</h3>
    <p className="text-3xl">₱{analytics.summary.total_revenue.toFixed(2)}</p>
  </Card>
  <Card>
    <h3>Units Sold</h3>
    <p className="text-3xl">{analytics.summary.total_units_sold}</p>
  </Card>
</div>
```

3. **Add Charts** (using Recharts)
```bash
npm install recharts
```

```tsx
import { LineChart, Line, XAxis, YAxis, Tooltip } from 'recharts';

<LineChart data={analytics.sales_over_time}>
  <XAxis dataKey="date" />
  <YAxis />
  <Tooltip />
  <Line type="monotone" dataKey="revenue" stroke="#8884d8" />
</LineChart>
```

### **B. Products Table with Inline Editing**

1. **Fetch Products**
```typescript
const fetchProducts = async (filters: ProductFilters) => {
  const params = new URLSearchParams(filters);
  const response = await fetch(`/api/seller/products?${params}`);
  return response.json();
};
```

2. **Inline Stock Edit**
```tsx
const [editingStock, setEditingStock] = useState<number | null>(null);

const updateStock = async (productId: number, newStock: number) => {
  // Optimistic update
  setProducts(prev => prev.map(p => 
    p.id === productId ? { ...p, stock: newStock } : p
  ));

  try {
    const response = await fetch(`/api/products/${productId}/stock`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ stock: newStock })
    });

    if (!response.ok) throw new Error('Failed to update');
    
    toast.success('Stock updated!');
  } catch (error) {
    // Rollback on error
    setProducts(prev => prev.map(p => 
      p.id === productId ? { ...p, stock: oldStock } : p
    ));
    toast.error('Failed to update stock');
  }
};

// Render
<td onClick={() => setEditingStock(product.id)}>
  {editingStock === product.id ? (
    <input
      type="number"
      defaultValue={product.stock}
      onBlur={(e) => {
        updateStock(product.id, parseInt(e.target.value));
        setEditingStock(null);
      }}
    />
  ) : (
    <span>{product.stock}</span>
  )}
</td>
```

3. **Stock Badge with Color**
```tsx
const getStockBadge = (product: Product) => {
  if (product.is_out_of_stock) {
    return <Badge variant="danger">Out of Stock</Badge>;
  }
  if (product.is_low_stock) {
    return <Badge variant="warning">Low Stock</Badge>;
  }
  return <Badge variant="success">In Stock</Badge>;
};
```

---

## ✅ Verification Checklist

### **Backend APIs**
- [x] Analytics endpoint returns correct data
- [x] Products list supports search/filter/sort
- [x] Stock update creates log entry
- [x] Stock=0 auto-sets status to out_of_stock
- [x] Soft delete removes from buyer page
- [x] Stock logs accessible per product
- [x] Authorization prevents cross-seller access

### **Database**
- [x] Orders table has buyer_id, total, status
- [x] OrderItems table created
- [x] Products has low_stock_threshold
- [x] Products has deleted_at (soft deletes)
- [x] 50 sample orders seeded

### **Security**
- [x] Sellers can only view/edit their own products
- [x] Returns 403 for unauthorized access
- [x] Stock validation (integer, >= 0)
- [x] All mutations are transaction-wrapped

---

## 📦 Seed Data Management

### **View Seed Data**
```bash
php test_step2.php
```

### **Add More Orders**
```bash
php artisan db:seed --class=DevOrderSeeder
```

### **Remove Seed Data**
```sql
DELETE FROM order_items WHERE id > 0;
DELETE FROM orders WHERE id > 0;
```

Or with Laravel:
```php
\App\Models\OrderItem::truncate();
\App\Models\Order::truncate();
```

---

## 🐛 Troubleshooting

### **Issue: Analytics returns empty data**
**Solution:** Make sure you have:
1. Created products as a seller
2. Run the seeder: `php artisan db:seed --class=DevOrderSeeder`
3. The seeded orders reference your products

### **Issue: Stock update not working**
**Solution:** Check:
1. You're authenticated as the product owner
2. Stock value is a valid integer >= 0
3. CSRF token is included in request headers

### **Issue: Product not disappearing from buyer page after archive**
**Solution:** 
- Archived products use soft deletes
- Buyer endpoint only shows non-deleted products
- Clear any frontend cache

---

## 🎯 Next Steps

**Option A: Complete Frontend**
- Implement analytics dashboard with charts
- Add inline stock editing UI
- Build product edit modal
- Create stock history viewer

**Option B: Move to Step 3**
- Shopping cart functionality
- Checkout process
- Stock deduction on purchase
- Order management

---

**🎉 STEP 2 BACKEND IS COMPLETE!**

All APIs are tested and ready for frontend integration.  
Choose your next step and let's continue! 🚀

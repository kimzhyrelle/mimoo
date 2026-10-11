# 🧪 Quick Test Guide - Step 3 Frontend

## Prerequisites

✅ Backend API is running (`php artisan serve`)  
✅ Frontend is compiled (`npm run dev` or `npm run build`)  
✅ Database migrations are run  
✅ You have test accounts (buyers and sellers)

---

## Test Scenario 1: Add to Cart from Homepage

### Steps:
1. **Login as buyer**
   - Email: `kimzhyrelled@gmail.com`
   - Go to `/homepage`

2. **Browse products**
   - Should see products loaded from backend
   - Each product shows:
     - Image
     - Name
     - Price (sale price if applicable)
     - Stock status
     - "Add to cart" button

3. **Add item to cart**
   - Click "Add to cart" on any product
   - Expected: 
     - ✅ Green toast: "Added to cart successfully"
     - ✅ Cart badge count increases (top right)

4. **Add same product again**
   - Click "Add to cart" on same product
   - Expected:
     - ✅ Green toast: "Added to cart successfully"
     - ✅ Quantity should merge (not duplicate)
     - ✅ Cart count updates

5. **Try as guest**
   - Logout
   - Click "Add to cart"
   - Expected:
     - ✅ Signup modal appears

---

## Test Scenario 2: Add to Cart from Product Detail

### Steps:
1. **Login as buyer**
   - Go to `/homepage`
   - Click on any product card

2. **On product detail page**
   - Should see:
     - Product images
     - Name, description, price
     - Stock status
     - Quantity selector (min: 1, max: stock)

3. **Select quantity**
   - Change quantity to 3 using + button
   - Expected:
     - ✅ Can't go below 1
     - ✅ Can't exceed stock

4. **Add to cart with quantity**
   - Click "Add to cart"
   - Expected:
     - ✅ Green toast: "Added to cart!" with "View Cart" button
     - ✅ Cart badge shows correct total quantity

5. **Click "View Cart" in toast**
   - Expected:
     - ✅ Redirects to `/cart`

---

## Test Scenario 3: Cart Page - Happy Path

### Steps:
1. **Go to `/cart`**
   - Should see all items you added
   - Each item shows:
     - Product image
     - Name
     - Seller name
     - SKU
     - Unit price
     - Quantity controls
     - Line total
     - Stock status (green)
     - Remove button

2. **Update quantity**
   - Click + button to increase quantity
   - Expected:
     - ✅ Quantity increases
     - ✅ Line total updates
     - ✅ Cart count badge updates
     - ✅ Can't exceed max stock (button disabled)

3. **Decrease quantity**
   - Click - button
   - Expected:
     - ✅ Quantity decreases
     - ✅ Can't go below 1 (button disabled)

4. **Remove item**
   - Click X button
   - Expected:
     - ✅ Item disappears
     - ✅ Green toast: "Item removed"
     - ✅ Cart count updates

5. **Checkout**
   - Click "Proceed to Checkout"
   - Expected:
     - ✅ Redirects to `/checkout`

---

## Test Scenario 4: Cart Validation (Critical!)

This tests the real-time validation feature!

### Setup:
1. **As buyer**, add Product A (qty: 5) to cart
2. **Switch to seller account** (product owner)

### Test A: Lower Stock Below Cart Quantity
1. **As seller**, go to products dashboard
2. Edit Product A's stock to 3 (below cart qty of 5)
3. **Switch back to buyer account**
4. Refresh cart page (`/cart`)
5. **Expected:**
   - ⚠️ Orange warning banner: "Some items need attention"
   - ❌ Product A shows: "Only 3 left in stock" (red text)
   - ❌ Checkout button is DISABLED
   - ❌ Red background on invalid cart row

### Test B: Try to Increase Quantity Above Stock
1. Try to click + button on Product A
2. **Expected:**
   - ❌ Button is disabled (qty already exceeds stock)
3. Try to decrease to 3, then increase to 4
4. **Expected:**
   - ❌ Red toast: "Only 3 items available in stock."

### Test C: Set Stock to Zero
1. **As seller**, set Product A stock to 0
2. **As buyer**, refresh cart
3. **Expected:**
   - ❌ Shows: "Out of stock" (red text)
   - ❌ Checkout disabled
   - ✅ Can still remove item

### Test D: Archive Product in Cart
1. **As seller**, archive Product A (soft delete)
2. **As buyer**, refresh cart
3. **Expected:**
   - ❌ Shows: "Product no longer available" (red text)
   - ❌ Checkout disabled

### Recovery:
1. Remove invalid items OR
2. Fix quantities OR
3. Seller restores stock
4. **Expected:**
   - ✅ Warning banner disappears
   - ✅ Checkout button enabled

---

## Test Scenario 5: Live Price Updates

### Setup:
1. **As buyer**, add Product B (₱500) to cart (qty: 2)
   - Cart shows: ₱500 × 2 = ₱1,000

### Test:
1. **As seller**, change Product B price to ₱600
2. **As buyer**, refresh cart page
3. **Expected:**
   - ✅ Cart shows: ₱600 × 2 = ₱1,200
   - ✅ LIVE PRICE UPDATE (not cached)

---

## Test Scenario 6: Empty Cart

### Steps:
1. Remove all items from cart
2. **Expected:**
   - ✅ Shows package icon
   - ✅ "Your cart is empty" message
   - ✅ "Start Shopping" button
   - ✅ No placeholder/mock data

---

## Test Scenario 7: Sale Prices

### Steps:
1. **As seller**, set a product on sale:
   - Regular price: ₱750
   - Sale price: ₱550
2. **As buyer**, add to cart
3. Go to `/cart`
4. **Expected:**
   - ✅ Shows: ₱550 (bold)
   - ✅ Shows: ₱750 (crossed out, gray)
   - ✅ Line total uses sale price

---

## Test Scenario 8: Multiple Sellers

### Steps:
1. Add products from different sellers to cart
2. Go to `/cart`
3. **Expected:**
   - ✅ Shows correct seller name for each product
   - ✅ All items visible in one cart
   - ✅ Subtotal = sum of all line totals

---

## Common Issues & Fixes

### Issue: Toast doesn't appear
**Fix:** Check that `<Toaster />` is in `app.tsx` (it already is!)

### Issue: Cart count doesn't update
**Fix:** Check browser console for API errors, verify `/api/cart/count` endpoint

### Issue: "Failed to fetch cart"
**Fix:** 
- Check if backend is running (`php artisan serve`)
- Verify buyer is logged in
- Check `/api/cart` endpoint in browser dev tools

### Issue: Quantity update doesn't work
**Fix:**
- Check browser console
- Verify CSRF token is in `<meta>` tag
- Check `/api/cart/items/{id}` endpoint

### Issue: Products not loading on homepage
**Fix:**
- Check `/api/products` endpoint
- Verify products exist in database
- Check `ProductController.index()` method

---

## API Endpoints Reference

```
GET  /api/products          - List all products (public)
GET  /api/cart              - Get buyer's cart (auth)
GET  /api/cart/count        - Get cart count (auth)
POST /api/cart/items        - Add to cart (auth, buyer only)
PATCH /api/cart/items/{id}  - Update quantity (auth, buyer only)
DELETE /api/cart/items/{id} - Remove item (auth, buyer only)
```

---

## Browser Console Checks

Open Developer Tools (F12) and check:

### 1. Network Tab
- Look for API calls to `/api/cart/*`
- Check response status (200 = OK, 403 = forbidden, 422 = validation error)
- Inspect response data

### 2. Console Tab
- Should NOT see errors
- May see logs from `console.error()` if API fails

---

## Success Criteria ✅

- [ ] Can add items to cart from homepage
- [ ] Can add items with quantity from product detail
- [ ] Cart badge shows correct count
- [ ] Cart page loads real items
- [ ] Can update quantities
- [ ] Can remove items
- [ ] Invalid items are flagged
- [ ] Checkout is disabled when invalid items exist
- [ ] Live prices reflect seller changes
- [ ] Toast notifications work
- [ ] Guest users see signup modal
- [ ] Sellers can't add to cart

---

## 🎉 If All Tests Pass

**STEP 3 FRONTEND IS FULLY WORKING!** 🚀

You can now:
- Show it to the team
- Proceed to Step 4 (Checkout & Order Creation)
- Deploy to staging for user testing

---

## Troubleshooting Commands

```bash
# Check if backend is running
curl http://localhost:8000/api/products

# Check cart endpoint (requires auth)
curl http://localhost:8000/api/cart

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rebuild frontend
npm run build

# Check database
php artisan db:show
php artisan migrate:status

# Run migrations if needed
php artisan migrate
```

---

**Good luck testing! 🎯**

# Mimoo E-Commerce Platform - Database Documentation

## Database Overview
- **Database Name**: laravel
- **DBMS**: MySQL 8.0.43
- **Total Tables**: 77

## Main Tables

### 1. **users** (User Management)
Central table for all users (buyers, sellers, logistics/courier companies)

**Key Columns:**
- `id` - Primary key
- `name`, `first_name`, `last_name`, `middle_initial`
- `email`, `email_verified_at`
- `password`, `google_id` (for Google OAuth)
- `account_type` - ENUM: 'buyer', 'seller', 'logistics'
- `status` - ENUM: 'pending', 'approved', 'rejected'
- `verification_status` - ENUM: 'unverified', 'verified'
- `approved_at` - Timestamp when account was approved

**Personal Information:**
- `sex`, `birthday`, `contact_number`, `tin`

**Address Fields:**
- `region`, `province`, `municipality_city`, `barangay`
- `district` (for NCR only)
- `postal_code`, `street_address`

**Seller-Specific Fields:**
- `business_name`, `business_type` (sole_proprietor, partnership, corporation)
- `line_of_business`
- `business_permit`, `dti_registration`, `bir_2303`, `sec_certificate` (file paths)
- `profile_photo`, `cover_photo`, `store_photos` (JSON array)

**Logistics/Courier-Specific Fields:**
- `company_name`, `sec_registration_no`
- `contact_first_name`, `contact_last_name`, `contact_position`
- `service_areas` (JSON array of barangays/cities they service)
- `valid_id` (additional ID document)

**Document Uploads:**
- `id_document` - Government ID path

**Privacy & Terms:**
- `privacy_policy_accepted` - Boolean

**Timestamps:**
- `created_at`, `updated_at`, `remember_token`

---

### 2. **products** (Product Catalog)
Stores all products listed by sellers

**Key Columns:**
- `id` - Primary key
- `seller_id` - Foreign key to users table
- `name`, `description`, `category`, `brand`
- `price`, `sale_price`, `stock`
- `sku` - Stock Keeping Unit
- `weight` - Product weight for shipping
- `images` - JSON array of image paths
- `main_image_index` - Index of main image in array
- `status` - ENUM: 'active', 'inactive', 'out_of_stock'
- `approval_status` - ENUM: 'pending', 'approved', 'rejected'
- `has_variants` - Boolean
- `variant_options` - JSON (e.g., colors, sizes)
- `variants` - JSON array of variant combinations
- `low_stock_threshold` - Alert threshold
- `deleted_at` - Soft delete timestamp
- `created_at`, `updated_at`

---

### 3. **orders** (Order Management)
Main orders table tracking all purchases

**Key Columns:**
- `id` - Primary key
- `buyer_id` - Foreign key to users table
- `total` - Total amount including shipping
- `status` - ENUM: 'pending', 'paid', 'packed', 'shipped', 'completed', 'cancelled', 'failed'
- `payment_method` - 'cod', 'gcash', 'paymaya', 'card'
- `payment_status` - 'pending', 'paid', 'failed', 'refunded'

**Shipping Information:**
- `shipping_name`, `shipping_phone`, `shipping_email`
- `shipping_address` - Full address string
- `shipping_region`, `shipping_province`, `shipping_municipality_city`
- `shipping_barangay`, `shipping_postal_code`
- `shipping_notes`

**Waybill/Tracking:**
- `waybill_number` - Unique tracking number (format: MIMWB-YYYYMMDD-XXXXXXXX-XXXX)
- `waybill_generated_at` - When waybill was generated
- `waybill_printed_at` - When waybill was printed

**Voucher:**
- `voucher_id` - Foreign key to vouchers table
- `voucher_discount` - Discount amount applied

**Timestamps:**
- `created_at`, `updated_at`

---

### 4. **order_items** (Order Line Items)
Individual items in each order

**Key Columns:**
- `id` - Primary key
- `order_id` - Foreign key to orders table
- `product_id` - Foreign key to products table
- `seller_id` - Foreign key to users table (copied from product)
- `product_name` - Snapshot of product name at time of order
- `product_image` - Snapshot of product image
- `variant` - JSON object with selected variant (color, size, etc.)
- `quantity` - Number of items
- `price` - Unit price at time of order (snapshot)
- `subtotal` - quantity × price
- `created_at`, `updated_at`

---

### 5. **carts** (Shopping Carts)
User shopping carts

**Key Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users table (nullable for guest carts)
- `session_id` - Session identifier for guest users
- `created_at`, `updated_at`

---

### 6. **cart_items** (Cart Line Items)
Items in shopping carts

**Key Columns:**
- `id` - Primary key
- `cart_id` - Foreign key to carts table
- `product_id` - Foreign key to products table
- `variant` - JSON object with selected variant
- `quantity` - Number of items
- `created_at`, `updated_at`

---

### 7. **stock_logs** (Inventory Tracking)
Audit trail for stock changes

**Key Columns:**
- `id` - Primary key
- `product_id` - Foreign key to products table
- `type` - ENUM: 'restock', 'sale', 'adjustment', 'return'
- `quantity` - Amount changed (positive or negative)
- `previous_stock` - Stock before change
- `new_stock` - Stock after change
- `reason` - Description of change
- `user_id` - Who made the change
- `created_at`, `updated_at`

---

### 8. **vouchers** (Promotional Vouchers)
Discount codes and promotions

**Key Columns:**
- `id` - Primary key
- `code` - Unique voucher code
- `type` - 'percentage', 'fixed'
- `value` - Discount amount/percentage
- `min_purchase` - Minimum order amount required
- `max_discount` - Maximum discount cap (for percentage)
- `usage_limit` - Total usage limit
- `usage_count` - Current usage count
- `per_user_limit` - How many times one user can use
- `valid_from`, `valid_until` - Validity period
- `is_active` - Boolean
- `description`
- `created_at`, `updated_at`

---

### 9. **voucher_usages** (Voucher Usage Tracking)
Tracks who claimed/used which vouchers

**Key Columns:**
- `id` - Primary key
- `voucher_id` - Foreign key to vouchers table
- `user_id` - Foreign key to users table
- `order_id` - Foreign key to orders table (nullable if just claimed)
- `claimed_at` - When user claimed the voucher
- `used_at` - When voucher was applied to an order
- `created_at`, `updated_at`

---

### 10. **product_reports** (Product Reporting System)
User reports for inappropriate/problematic products

**Key Columns:**
- `id` - Primary key
- `product_id` - Foreign key to products table
- `reporter_id` - Foreign key to users table
- `reason` - Report reason
- `description` - Detailed description
- `status` - 'pending', 'reviewed', 'resolved', 'dismissed'
- `reviewed_by` - Admin user ID
- `reviewed_at`
- `resolution_notes`
- `created_at`, `updated_at`

---

### 11. **email_verifications** (Email Verification Tokens)
Temporary tokens for email verification

**Key Columns:**
- `id` - Primary key
- `email` - Email being verified
- `token` - Verification token
- `expires_at` - Token expiration
- `created_at`

---

### 12. System Tables (Laravel Framework)

**cache** & **cache_locks** - Application caching

**jobs** & **job_batches** & **failed_jobs** - Queue system for background jobs

**password_reset_tokens** - Password reset tokens

**sessions** - User session data

**migrations** - Migration tracking

---

## Relationships

### User Relationships
- One user (seller) → Many products
- One user (buyer) → Many orders
- One user → One cart
- One user → Many voucher usages
- One user → Many product reports
- One user (admin) → Many product report reviews

### Order Relationships
- One order → One buyer (user)
- One order → Many order items
- One order → One voucher (optional)

### Product Relationships
- One product → One seller (user)
- One product → Many order items
- One product → Many cart items
- One product → Many stock logs
- One product → Many product reports

### Cart Relationships
- One cart → One user
- One cart → Many cart items

### Voucher Relationships
- One voucher → Many voucher usages
- One voucher → Many orders

---

## Indexes & Performance
- Primary keys on all tables
- Foreign key indexes on relationship columns
- Index on `email` in users table
- Index on `status` and `account_type` in users table
- Index on `seller_id` in products table
- Index on `buyer_id` and `status` in orders table
- Index on `waybill_number` in orders table (unique)
- Index on `code` in vouchers table (unique)

---

## Data Security
- Passwords hashed using bcrypt
- Soft deletes on products table (deleted_at)
- File uploads validated and sanitized
- Email verification required for account activation
- Admin approval required for seller and logistics accounts

---

## Migrations Timeline
All migrations have been run successfully. See `migration_status.txt` for complete list.

Key migrations:
1. **2026_10_27_161843** - Added logistics/courier company fields
2. **2026_10_28_025603** - Added 'logistics' to account_type enum
3. **2026_11_03_000001** - Added waybill tracking fields
4. **2026_11_03_110008** - Created vouchers system

---

## Database Files
- **database_schema.sql** - Complete schema export (structure only)
- **migration_status.txt** - List of all applied migrations

---

*Generated: November 6, 2026*

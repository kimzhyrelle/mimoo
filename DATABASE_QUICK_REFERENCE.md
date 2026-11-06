# Mimoo Database - Quick Reference Guide

## 📊 Database Stats
- **Database**: laravel (MySQL 8.0.43)
- **Total Tables**: 77
- **Core Tables**: 12
- **System Tables**: 7
- **Framework Tables**: 58

---

## 🗂️ Table Structure Overview

```
mimoo_ecommerce/
├── 👥 USER MANAGEMENT
│   ├── users (45 columns)
│   │   ├── Account Types: buyer, seller, logistics
│   │   ├── Statuses: pending, approved, rejected
│   │   └── Fields: personal info, address, business docs, logistics info
│   │
│   └── email_verifications
│       └── Email verification tokens
│
├── 🛍️ PRODUCT CATALOG
│   ├── products
│   │   ├── Product info, pricing, inventory
│   │   ├── Variant support (colors, sizes, etc.)
│   │   ├── Image gallery
│   │   └── Approval workflow
│   │
│   ├── stock_logs
│   │   └── Inventory audit trail
│   │
│   └── product_reports
│       └── User reports for products
│
├── 🛒 SHOPPING & ORDERS
│   ├── carts
│   │   └── User shopping carts
│   │
│   ├── cart_items
│   │   └── Items in carts
│   │
│   ├── orders
│   │   ├── Order tracking
│   │   ├── Payment info
│   │   ├── Shipping details
│   │   └── Waybill tracking
│   │
│   └── order_items
│       └── Line items per order
│
├── 💰 PROMOTIONS
│   ├── vouchers
│   │   └── Discount codes & promos
│   │
│   └── voucher_usages
│       └── Tracks who claimed/used vouchers
│
└── ⚙️ SYSTEM TABLES
    ├── cache, cache_locks
    ├── jobs, job_batches, failed_jobs
    ├── sessions
    ├── password_reset_tokens
    └── migrations
```

---

## 🔑 Key Features by Table

### **users** - Multi-role System
```
Roles:
  ✓ Buyers (customers)
  ✓ Sellers (merchants)
  ✓ Logistics (courier companies)

Approval Workflow:
  Pending → Approved/Rejected

Features:
  • Google OAuth integration
  • Email verification
  • Complete address system (PH format)
  • Document uploads (IDs, permits, certifications)
  • Store customization (photos, branding)
```

### **products** - Full E-commerce Features
```
Features:
  • Product variants (size, color, etc.)
  • Image gallery with main image
  • Stock management + low stock alerts
  • Sale pricing
  • Admin approval workflow
  • Soft deletes
  • SKU tracking
```

### **orders** - Complete Order Flow
```
Status Flow:
  pending → paid → packed → shipped → completed

Payment Methods:
  • Cash on Delivery (COD)
  • GCash
  • PayMaya
  • Credit/Debit Card

Features:
  • Waybill generation (QR code)
  • Complete shipping details
  • Voucher integration
  • Payment status tracking
```

### **vouchers** - Flexible Promotions
```
Types:
  • Percentage discount
  • Fixed amount discount

Features:
  • Usage limits (total & per user)
  • Minimum purchase requirement
  • Maximum discount cap
  • Validity period
  • Claim & usage tracking
```

---

## 📈 Key Relationships

```
users (seller) ──1:N──> products
users (buyer)  ──1:N──> orders
users          ──1:1──> carts
orders         ──1:N──> order_items
products       ──1:N──> order_items
carts          ──1:N──> cart_items
vouchers       ──1:N──> orders
vouchers       ──1:N──> voucher_usages
products       ──1:N──> stock_logs
products       ──1:N──> product_reports
```

---

## 🔐 Security Features

✅ Password hashing (bcrypt)
✅ Email verification required
✅ Admin approval for sellers & logistics
✅ File upload validation
✅ Soft deletes on sensitive data
✅ Audit trails (stock logs, voucher usage)
✅ Session management
✅ CSRF protection
✅ SQL injection protection (prepared statements)

---

## 📋 Status/State Management

### Order Statuses
- `pending` - Order placed, awaiting payment
- `paid` - Payment received/confirmed
- `packed` - Seller prepared order for shipping
- `shipped` - Order in transit (confirmed by logistics)
- `completed` - Delivered to buyer
- `cancelled` - Order cancelled
- `failed` - Payment failed

### Account Statuses
- `pending` - Awaiting admin approval
- `approved` - Active account
- `rejected` - Application rejected

### Verification Status
- `unverified` - Email not verified
- `verified` - Email verified

### Product Approval
- `pending` - Awaiting admin review
- `approved` - Live on marketplace
- `rejected` - Not approved for sale

---

## 🚀 Special Features

### Waybill System
- Format: `MIMWB-YYYYMMDD-XXXXXXXX-XXXX`
- Includes QR code for scanning
- Tracks generation and print timestamps
- Used for logistics tracking

### Service Areas (Logistics)
- JSON array of barangays/cities
- Enables automatic courier assignment
- Supports route optimization

### Product Variants
- Flexible variant system (color, size, material, etc.)
- Individual pricing per variant
- Separate stock tracking

### Voucher Claims
- Users can claim vouchers before ordering
- Claimed vouchers stored for later use
- Usage tracked separately from claims

---

## 📁 Generated Files

1. **DATABASE_DOCUMENTATION.md** (this file)
   - Complete table descriptions
   - Column details
   - Relationships

2. **database_schema.sql**
   - Full SQL schema export
   - No data, structure only
   - Can be imported to recreate tables

3. **migration_status.txt**
   - List of all migrations
   - Run status

4. **DATABASE_QUICK_REFERENCE.md**
   - Visual overview
   - Quick lookup guide

---

## 📞 How to Show Your Database

### For Teacher/Presentation:

**Option 1: Visual Diagram** (BEST)
1. Open MySQL Workbench
2. Database → Reverse Engineer
3. Select `laravel` database
4. Export as PNG/PDF

**Option 2: Show Schema Files**
- `database_schema.sql` - Technical details
- `DATABASE_DOCUMENTATION.md` - Human-readable
- `DATABASE_QUICK_REFERENCE.md` - Overview

**Option 3: Live Demo**
- phpMyAdmin: http://localhost/phpmyadmin
- MySQL Workbench: Direct connection
- TablePlus/DBngin: Modern GUI tools

**Option 4: Code Walkthrough**
- Show `database/migrations/` folder
- Explain migration files
- Show relationships in Models

---

*Last Updated: November 6, 2026*

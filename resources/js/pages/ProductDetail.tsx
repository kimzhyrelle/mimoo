"use client";

import { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Star, ChevronRight, Minus, Plus, Package, ShoppingCart } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface ProductDetailProps {
  auth?: { user: any };
  product: {
    id: string;
    name: string;
    description: string;
    category: string;
    price: number;
    sale_price: number | null;
    effective_price: number;
    is_on_sale: boolean;
    stock: number;
    stock_status: string;
    is_in_stock: boolean;
    is_low_stock: boolean;
    images: string[];
    main_image_index: number;
    weight: number | null;
    brand: string;
    sku: string;
    seller_name: string;
    seller_id: number;
  };
}

interface ColorOption {
  id: string;
  label: string;
  hex: string;
}

interface SizeOption {
  id: string;
  label: string;
}

// ---------------------------------------------------------------------------
// Mock data
// ---------------------------------------------------------------------------

const COLORS: ColorOption[] = [
  { id: "tan", label: "Tan", hex: "#B8956A" },
  { id: "brown", label: "Brown", hex: "#3E2723" },
  { id: "cream", label: "Cream", hex: "#E8E4E0" },
];

const SIZES: SizeOption[] = [
  { id: "small", label: "Small" },
  { id: "medium", label: "Medium" },
  { id: "large", label: "Large" },
];

// ---------------------------------------------------------------------------
// Breadcrumb
// ---------------------------------------------------------------------------

function Breadcrumb({ category, sellerName }: { category: string; sellerName: string }) {
  return (
    <div className="flex items-center gap-2 text-sm text-neutral-500">
      <Link href="/homepage" className="hover:text-neutral-700">Home</Link>
      <ChevronRight className="h-3.5 w-3.5" />
      {category && (
        <>
          <span className="hover:text-neutral-700">{category}</span>
          <ChevronRight className="h-3.5 w-3.5" />
        </>
      )}
      <span className="text-neutral-700">{sellerName}</span>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Product Image Gallery
// ---------------------------------------------------------------------------

function ProductImageGallery({ images, productName }: { images: string[]; productName: string }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  
  if (!images || images.length === 0) {
    return (
      <div className="aspect-square w-full rounded-2xl bg-neutral-100 flex items-center justify-center">
        <Package className="w-32 h-32 text-neutral-300" />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-3">
      {/* Main image */}
      <div className="aspect-square w-full rounded-2xl bg-neutral-100 overflow-hidden">
        <img 
          src={images[selectedIndex]} 
          alt={productName} 
          className="w-full h-full object-cover"
        />
      </div>
      
      {/* Thumbnails */}
      {images.length > 1 && (
        <div className="flex gap-2 overflow-x-auto">
          {images.map((img, idx) => (
            <button
              key={idx}
              onClick={() => setSelectedIndex(idx)}
              className={`shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all ${
                selectedIndex === idx 
                  ? 'border-violet-900 ring-2 ring-violet-900 ring-offset-2' 
                  : 'border-neutral-200 hover:border-neutral-300'
              }`}
            >
              <img src={img} alt={`${productName} ${idx + 1}`} className="w-full h-full object-cover" />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

// ---------------------------------------------------------------------------
// Product Info
// ---------------------------------------------------------------------------

function ProductInfo({ 
  product, 
  role 
}: { 
  product: ProductDetailProps['product']; 
  role: UserRole;
}) {
  const [quantity, setQuantity] = useState(1);
  const maxQuantity = Math.min(product.stock, 99);

  const decreaseQuantity = () => {
    if (quantity > 1) setQuantity(quantity - 1);
  };

  const increaseQuantity = () => {
    if (quantity < maxQuantity) setQuantity(quantity + 1);
  };

  const formatPrice = (price: number) => {
    return '₱' + price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  return (
    <div className="flex flex-col gap-6">
      {/* Product title */}
      <div>
        <h1 className="text-2xl font-bold text-neutral-900 sm:text-3xl">
          {product.name}
        </h1>
        <div className="mt-2 flex items-center gap-3">
          <span className="text-sm text-neutral-600">SKU: {product.sku}</span>
          {product.brand && (
            <>
              <span className="text-neutral-300">•</span>
              <span className="text-sm text-neutral-600">Brand: {product.brand}</span>
            </>
          )}
        </div>
      </div>

      {/* Price */}
      <div>
        {product.is_on_sale ? (
          <div className="flex items-center gap-3">
            <span className="text-3xl font-bold text-violet-700 sm:text-4xl">
              {formatPrice(product.effective_price)}
            </span>
            <span className="text-xl text-neutral-400 line-through">
              {formatPrice(product.price)}
            </span>
            <span className="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
              {Math.round((1 - product.effective_price / product.price) * 100)}% OFF
            </span>
          </div>
        ) : (
          <div className="text-3xl font-bold text-neutral-900 sm:text-4xl">
            {formatPrice(product.price)}
          </div>
        )}
      </div>

      {/* Description */}
      {product.description && (
        <div>
          <h3 className="text-sm font-semibold text-neutral-700 mb-2">Description</h3>
          <p className="text-sm text-neutral-600 leading-relaxed whitespace-pre-line">
            {product.description}
          </p>
        </div>
      )}

      {/* Weight */}
      {product.weight && (
        <div className="flex items-center gap-2 text-sm text-neutral-600">
          <Package className="h-4 w-4" />
          <span>Weight: {product.weight} kg</span>
        </div>
      )}

      {/* Stock status */}
      <div>
        <span className={`text-sm font-medium ${
          product.is_in_stock 
            ? product.is_low_stock ? 'text-orange-600' : 'text-green-600'
            : 'text-red-600'
        }`}>
          {product.stock_status}
        </span>
      </div>

      {/* Quantity selector - only show if in stock */}
      {product.is_in_stock && role !== "seller" && (
        <div>
          <p className="mb-3 text-sm font-medium text-neutral-700">Quantity</p>
          <div className="flex items-center gap-3">
            <div className="flex items-center gap-2 rounded-lg border border-neutral-300 px-3 py-1.5">
              <button
                onClick={decreaseQuantity}
                disabled={quantity <= 1}
                className="text-neutral-700 hover:text-neutral-900 disabled:opacity-30"
                aria-label="Decrease quantity"
              >
                <Minus className="h-3.5 w-3.5" />
              </button>
              <span className="w-8 text-center text-sm font-medium text-neutral-900">
                {quantity}
              </span>
              <button
                onClick={increaseQuantity}
                disabled={quantity >= maxQuantity}
                className="text-neutral-700 hover:text-neutral-900 disabled:opacity-30"
                aria-label="Increase quantity"
              >
                <Plus className="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Action buttons */}
      {role === "guest" && (
        <div className="flex gap-2">
          <Link
            href="/register"
            className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-violet-900 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-violet-800"
          >
            Sign up to buy
          </Link>
        </div>
      )}

      {role === "buyer" && product.is_in_stock && (
        <div className="flex gap-2">
          <button className="flex flex-1 items-center justify-center gap-2 rounded-lg border border-violet-900 py-2.5 text-sm font-semibold text-violet-900 transition-colors hover:bg-violet-50">
            <ShoppingCart className="h-4 w-4" />
            Add to cart
          </button>
          <button className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-violet-900 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-violet-800">
            Buy now
          </button>
        </div>
      )}

      {role === "buyer" && !product.is_in_stock && (
        <div className="flex gap-2">
          <button disabled className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-neutral-300 py-2.5 text-sm font-semibold text-neutral-500 cursor-not-allowed">
            Out of stock
          </button>
        </div>
      )}

      {role === "seller" && (
        <div className="rounded-lg bg-neutral-100 p-4 text-sm text-neutral-600">
          You're viewing this as a seller. Switch to a buyer account to purchase products.
        </div>
      )}
    </div>
  );
}

// ---------------------------------------------------------------------------
// Seller Info Card
// ---------------------------------------------------------------------------

function SellerInfo({ sellerName }: { sellerName: string }) {
  const initials = sellerName
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);

  return (
    <div className="rounded-xl border border-neutral-200 bg-white p-6">
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-900 text-base font-semibold text-white">
            {initials}
          </div>
          <div>
            <p className="font-semibold text-neutral-900">
              {sellerName}
            </p>
            <span className="text-sm text-neutral-500">
              Seller
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Main Page
// ---------------------------------------------------------------------------

export default function ProductDetail({ auth, product }: ProductDetailProps) {
  const user = auth?.user;
  const role: UserRole = user?.account_type ? (user.account_type as UserRole) : "guest";

  function handleLogout() {
    router.post('/logout');
  }

  return (
    <div className="min-h-screen bg-neutral-50 font-[Syne]">
      <Head title={`${product.name} - mimoo`} />

      <BuyerHeader
        user={user}
        onLogout={handleLogout}
        cartCount={0}
      />

      <main className="mx-auto max-w-[1100px] bg-white px-6 py-6 sm:py-8">
        <Breadcrumb 
          category={product.category} 
          sellerName={product.seller_name} 
        />

        <div className="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-12">
          <ProductImageGallery 
            images={product.images} 
            productName={product.name} 
          />
          <ProductInfo 
            product={product} 
            role={role} 
          />
        </div>

        {/* Seller info card below */}
        <div className="mt-8">
          <SellerInfo sellerName={product.seller_name} />
        </div>
      </main>
    </div>
  );
}

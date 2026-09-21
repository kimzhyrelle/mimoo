"use client";

import { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Star, ChevronRight, Minus, Plus, Package, ShoppingCart } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

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

function Breadcrumb() {
  return (
    <div className="flex items-center gap-2 text-sm text-neutral-500">
      <button className="hover:text-neutral-700">Categories</button>
      <ChevronRight className="h-3.5 w-3.5" />
      <button className="hover:text-neutral-700">Arts & Crafts</button>
      <ChevronRight className="h-3.5 w-3.5" />
      <span className="text-neutral-700">Reyes Craft Corner</span>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Product Image Gallery
// ---------------------------------------------------------------------------

function ProductImageGallery() {
  return (
    <div className="aspect-square w-full rounded-2xl bg-neutral-100 flex items-center justify-center">
      <Package className="w-32 h-32 text-neutral-300" />
    </div>
  );
}

// ---------------------------------------------------------------------------
// Product Info
// ---------------------------------------------------------------------------

function ProductInfo() {
  const [selectedColor, setSelectedColor] = useState("cream");
  const [selectedSize, setSelectedSize] = useState("large");
  const [quantity, setQuantity] = useState(1);

  const decreaseQuantity = () => {
    if (quantity > 1) setQuantity(quantity - 1);
  };

  const increaseQuantity = () => {
    if (quantity < 3) setQuantity(quantity + 1);
  };

  return (
    <div className="flex flex-col gap-6">
      {/* Product title */}
      <div>
        <h1 className="text-2xl font-bold text-neutral-900 sm:text-3xl">
          Handwoven Rattan Basket (Medium)
        </h1>
        <div className="mt-2 flex items-center gap-3">
          <div className="flex items-center gap-1">
            {[1, 2, 3, 4].map((i) => (
              <Star
                key={i}
                className="h-4 w-4 fill-amber-400 text-amber-400"
              />
            ))}
            <Star className="h-4 w-4 text-neutral-300" />
          </div>
          <span className="text-sm text-neutral-600">(27 reviews)</span>
        </div>
      </div>

      {/* Price */}
      <div className="text-3xl font-bold text-neutral-900 sm:text-4xl">
        ₱450.00
      </div>

      {/* Color selector */}
      <div>
        <div className="mb-3 flex items-center gap-2">
          <span className="text-sm font-medium text-neutral-700">Color •</span>
          <span className="text-sm capitalize text-neutral-900">
            {COLORS.find((c) => c.id === selectedColor)?.label}
          </span>
        </div>
        <div className="flex gap-2">
          {COLORS.map((color) => (
            <button
              key={color.id}
              onClick={() => setSelectedColor(color.id)}
              className={`h-10 w-10 rounded-lg border-2 transition-all ${
                selectedColor === color.id
                  ? "border-violet-900 ring-2 ring-violet-900 ring-offset-2"
                  : "border-neutral-300 hover:border-neutral-400"
              }`}
              style={{ backgroundColor: color.hex }}
              aria-label={color.label}
            />
          ))}
        </div>
      </div>

      {/* Size selector */}
      <div>
        <p className="mb-3 text-sm font-medium text-neutral-700">Size</p>
        <div className="flex gap-2">
          {SIZES.map((size) => (
            <button
              key={size.id}
              onClick={() => setSelectedSize(size.id)}
              className={`rounded-lg border px-5 py-1.5 text-sm font-medium transition-all ${
                selectedSize === size.id
                  ? "border-violet-900 bg-violet-900 text-white"
                  : "border-neutral-300 bg-white text-neutral-700 hover:border-neutral-400"
              }`}
            >
              {size.label}
            </button>
          ))}
        </div>
      </div>

      {/* Quantity selector */}
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
            <span className="w-6 text-center text-sm font-medium text-neutral-900">
              {quantity}
            </span>
            <button
              onClick={increaseQuantity}
              disabled={quantity >= 3}
              className="text-neutral-700 hover:text-neutral-900 disabled:opacity-30"
              aria-label="Increase quantity"
            >
              <Plus className="h-3.5 w-3.5" />
            </button>
          </div>
          <span className="text-sm text-red-500">3 in stock</span>
        </div>
      </div>

      {/* Add to cart button */}
      <div className="flex gap-2">
        <button className="flex flex-1 items-center justify-center gap-2 rounded-lg border border-violet-900 py-2.5 text-sm font-semibold text-violet-900 transition-colors hover:bg-violet-50">
          <ShoppingCart className="h-4 w-4" />
          Add to cart
        </button>
        <button className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-violet-900 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-violet-800">
          Buy now
        </button>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Seller Info Card
// ---------------------------------------------------------------------------

function SellerInfo() {
  return (
    <div className="rounded-xl border border-neutral-200 bg-white p-6">
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-900 text-base font-semibold text-white">
            RC
          </div>
          <div>
            <p className="font-semibold text-neutral-900">
              Reyes Craft Corner
            </p>
            <button className="text-sm text-violet-600 hover:underline">
              Visit store
            </button>
          </div>
        </div>
        <button className="rounded-lg border border-neutral-300 px-4 py-1.5 text-xs font-medium text-neutral-700 transition-colors hover:bg-neutral-50">
          Follow
        </button>
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Main Page
// ---------------------------------------------------------------------------

export default function ProductDetail() {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth?.user;
  const role: UserRole = user?.account_type ? (user.account_type as UserRole) : "guest";

  function handleLogout() {
    router.post('/logout');
  }

  return (
    <div className="min-h-screen bg-neutral-50 font-[Syne]">
      <Head title="Product Detail" />

      <BuyerHeader
        user={user}
        onLogout={handleLogout}
        cartCount={4}
      />

      <main className="mx-auto max-w-[1100px] bg-white px-6 py-6 sm:py-8">
        <Breadcrumb />

        <div className="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-12">
          <ProductImageGallery />
          <ProductInfo />
        </div>

        {/* Seller info card below */}
        <div className="mt-8">
          <SellerInfo />
        </div>
      </main>
    </div>
  );
}

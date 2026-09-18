"use client";

import { useRef, useState, useEffect } from "react";
import { Link } from "@inertiajs/react";
import {
  ChevronRight,
  ChevronLeft,
} from "lucide-react";
import Header from "@/components/Header";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface Category {
  id: string;
  label: string;
}

interface Product {
  id: string;
  name: string;
  price: string;
}

// ---------------------------------------------------------------------------
// Placeholder data — swap out with real content/API data later
// ---------------------------------------------------------------------------

const CATEGORIES: Category[] = [
  { id: "c1", label: "Electronics" },
  { id: "c2", label: "Fashion" },
  { id: "c3", label: "Home & Living" },
  { id: "c4", label: "Beauty" },
  { id: "c5", label: "Groceries" },
  { id: "c6", label: "Gadgets" },
  { id: "c7", label: "Toys & Kids" },
  { id: "c8", label: "Sports" },
  { id: "c9", label: "Automotive" },
  { id: "c10", label: "Books" },
  { id: "c11", label: "Pet Supplies" },
  { id: "c12", label: "Health" },
];

const PRODUCTS: Product[] = [
  { id: "p1", name: "Wireless Earbuds", price: "₱799" },
  { id: "p2", name: "Ceramic Mug Set", price: "₱349" },
  { id: "p3", name: "Running Shoes", price: "₱1,499" },
  { id: "p4", name: "Skincare Bundle", price: "₱649" },
  { id: "p5", name: "Desk Lamp", price: "₱499" },
];

// ---------------------------------------------------------------------------
// Small building blocks
// ---------------------------------------------------------------------------

function HeroMainBanner() {
  return (
    <div className="flex min-h-36 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400 sm:min-h-44">
      Main promo banner
    </div>
  );
}

function SmallPlaceholderBanner({ label }: { label: string }) {
  return (
    <div className="flex flex-1 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400">
      {label}
    </div>
  );
}

function BuyOneGetOneBanner() {
  return (
    <div className="flex h-full min-h-24 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400">
      Promo banner
    </div>
  );
}

function SuperSeratusBanner() {
  return (
    <div className="flex h-full min-h-24 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400">
      Promo banner
    </div>
  );
}

function CategoryTile({ category }: { category: Category }) {
  return (
    <button
      className="flex shrink-0 snap-start flex-col items-center gap-1.5 rounded-lg p-1 text-center transition-colors hover:bg-violet-50 sm:gap-2"
      style={{ width: "calc((100% - 40px) / 4)" }}
    >
      <div className="aspect-square w-full rounded-lg bg-neutral-200" />
      <span className="text-[10px] font-medium text-neutral-700 line-clamp-1 sm:text-xs">
        {category.label}
      </span>
    </button>
  );
}

function CategoryCarousel({ categories }: { categories: Category[] }) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [progress, setProgress] = useState({ ratio: 0.5, position: 0 });
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(true);

  const updateProgress = () => {
    const el = scrollRef.current;
    if (!el) return;
    const { scrollLeft, scrollWidth, clientWidth } = el;
    const maxScroll = scrollWidth - clientWidth;
    const ratio = clientWidth / scrollWidth;
    const position = maxScroll > 0 ? scrollLeft / maxScroll : 0;
    setProgress({ ratio, position });
    setCanScrollLeft(scrollLeft > 4);
    setCanScrollRight(scrollLeft < maxScroll - 4);
  };

  useEffect(() => {
    updateProgress();
    window.addEventListener("resize", updateProgress);
    return () => window.removeEventListener("resize", updateProgress);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const scrollByPage = (direction: 1 | -1) => {
    const el = scrollRef.current;
    if (!el) return;
    el.scrollBy({ left: direction * el.clientWidth, behavior: "smooth" });
  };

  return (
    <div>
      <div className="relative">
        <div
          ref={scrollRef}
          onScroll={updateProgress}
          className="category-scroll flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-1"
          style={{ scrollbarWidth: "none", msOverflowStyle: "none" }}
        >
          <style>{`.category-scroll::-webkit-scrollbar { display: none; }`}</style>
          {categories.map((c) => (
            <CategoryTile key={c.id} category={c} />
          ))}
        </div>

        {/* Prev / next controls, centered on the image row */}
        <button
          aria-label="Previous categories"
          onClick={() => scrollByPage(-1)}
          disabled={!canScrollLeft}
          className="absolute left-0 top-10 -translate-x-1/2 -translate-y-1/2 rounded-full border border-neutral-200 bg-white p-1.5 shadow-sm transition-opacity disabled:pointer-events-none disabled:opacity-0"
        >
          <ChevronLeft className="h-4 w-4 text-neutral-600" />
        </button>
        <button
          aria-label="Next categories"
          onClick={() => scrollByPage(1)}
          disabled={!canScrollRight}
          className="absolute right-0 top-10 -translate-y-1/2 translate-x-1/2 rounded-full border border-neutral-200 bg-white p-1.5 shadow-sm transition-opacity disabled:pointer-events-none disabled:opacity-0"
        >
          <ChevronRight className="h-4 w-4 text-neutral-600" />
        </button>
      </div>

      {/* Scroll progress indicator */}
      <div className="relative mx-auto mt-3 h-1.5 w-20 rounded-full bg-neutral-200">
        <div
          className="absolute top-0 h-1.5 rounded-full bg-neutral-400 transition-all duration-150"
          style={{
            width: `${Math.max(progress.ratio * 100, 15)}%`,
            left: `${progress.position * (100 - Math.max(progress.ratio * 100, 15))}%`,
          }}
        />
      </div>
    </div>
  );
}

function ProductCard({ product, role }: { product: Product; role: UserRole }) {
  return (
    <Link
      href={`/product/${product.id}`}
      className="flex flex-col overflow-hidden rounded-lg border border-neutral-200 bg-white transition-shadow hover:shadow-md"
    >
      <div className="aspect-square w-full bg-neutral-200" />
      <div className="flex flex-1 flex-col gap-1 p-3">
        <span className="text-sm text-neutral-800 line-clamp-2">
          {product.name}
        </span>
        <span className="text-sm font-semibold text-violet-700">
          {product.price}
        </span>
        {role === "guest" && (
          <button
            onClick={(e) => {
              e.preventDefault();
              // Handle login redirect
            }}
            className="mt-2 rounded-md border border-violet-600 py-1.5 text-xs font-medium text-violet-600 transition-colors hover:bg-violet-50"
          >
            Log in to buy
          </button>
        )}
        {role === "buyer" && (
          <button
            onClick={(e) => {
              e.preventDefault();
              // Handle add to cart
            }}
            className="mt-2 rounded-md bg-violet-600 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700"
          >
            Add to cart
          </button>
        )}
        {role === "seller" && (
          <button
            onClick={(e) => {
              e.preventDefault();
            }}
            className="mt-2 rounded-md border border-neutral-300 py-1.5 text-xs font-medium text-neutral-500"
          >
            View as buyer to purchase
          </button>
        )}
      </div>
    </Link>
  );
}

// ---------------------------------------------------------------------------
// Header — search bar + cart/account icons always shown, matching the
// reference design. Role only changes downstream content (product CTAs).
// ---------------------------------------------------------------------------

// Moved to components/Header.tsx for reusability

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

export default function Homepage() {
  // Swap this out for your real auth/session state later — guest, buyer,
  // or seller — to drive the product card CTAs below.
  const role: UserRole = "guest";

  return (
    <div
      className="min-h-screen bg-neutral-50"
      style={{ fontFamily: "'Syne', sans-serif" }}
    >
      {/* Loads Syne from Google Fonts. If you already load it globally
          (e.g. via next/font or a <link> in your root layout), remove this
          block and the inline fontFamily above to avoid loading it twice. */}
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&display=swap');
      `}</style>

      <Header />

      <main className="mx-auto max-w-6xl px-4 py-4 sm:py-6">
        {/* Hero: big banner on top, 3-column row below it */}
        <section className="flex flex-col gap-2 sm:gap-3">
          <HeroMainBanner />

          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
            <div className="grid grid-rows-2 gap-2 sm:gap-3">
              <SmallPlaceholderBanner label="Perfume & makeup offers" />
              <SmallPlaceholderBanner label="iPhone offers" />
            </div>
            <BuyOneGetOneBanner />
            <SuperSeratusBanner />
          </div>
        </section>

        {/* Browse by categories */}
        <section className="mt-6 sm:mt-8">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="text-sm font-semibold text-neutral-900 sm:text-base">
              Browse by categories
            </h2>
            <button className="flex items-center text-xs text-violet-600 hover:underline sm:text-sm">
              See all <ChevronRight className="h-4 w-4" />
            </button>
          </div>
          <CategoryCarousel categories={CATEGORIES} />
        </section>

        {/* Top products */}
        <section className="mt-6 sm:mt-8">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="text-sm font-semibold text-neutral-900 sm:text-base">
              Top products
            </h2>
            <button className="flex items-center text-xs text-violet-600 hover:underline sm:text-sm">
              See all <ChevronRight className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 lg:grid-cols-5">
            {PRODUCTS.map((p) => (
              <ProductCard key={p.id} product={p} role={role} />
            ))}
          </div>
        </section>

        {/* Secondary banners */}
        <section className="mt-6 grid grid-cols-1 gap-2 sm:mt-8 sm:grid-cols-2 sm:gap-3">
          <div className="flex min-h-24 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400">
            Promo banner
          </div>
          <div className="flex min-h-24 items-center justify-center rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400">
            Promo banner
          </div>
        </section>

        {/* Discover something new */}
        <section className="mt-8 text-center sm:mt-10">
          <h2 className="text-xs font-semibold tracking-wide text-neutral-800 sm:text-sm">
            Discover something new!
          </h2>
          <div className="mx-auto mt-2 h-0.5 w-10 rounded-full bg-violet-600" />
        </section>

        {/* Recommended feed placeholder */}
        <section className="mt-4 grid grid-cols-2 gap-2 pb-8 sm:mt-6 sm:grid-cols-3 sm:gap-3 sm:pb-10 lg:grid-cols-5">
          {PRODUCTS.map((p) => (
            <ProductCard key={`rec-${p.id}`} product={p} role={role} />
          ))}
        </section>
      </main>
    </div>
  );
}
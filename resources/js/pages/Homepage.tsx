"use client";

import { useRef, useState, useEffect, type FormEvent } from "react";
import { Link } from "@inertiajs/react";
import {
  ChevronRight,
  ChevronLeft,
  Bell,
  Search,
  ShoppingCart,
} from "lucide-react";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface Category {
  id: string;
  label: string;
  icon?: string | null;
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
  { id: "c1", label: "Pet Supplies", icon: "/images/pet supplies.jpg" },
  { id: "c2", label: "Electronics and Gadgets", icon: "/images/electronics.jpg" },
  { id: "c3", label: "Women's Apparel", icon: "/images/women's apparel.jpg" },
  { id: "c4", label: "Men's Apparel", icon: "/images/mens apparel.jpg" },
  { id: "c5", label: "Kids and Baby", icon: "/images/kids and babies.jpg" },
  { id: "c6", label: "Home and Garden", icon: "/images/home and garden.jpg" },
  { id: "c7", label: "Sports and Outdoors", icon: "/images/sports.jpg" },
  { id: "c8", label: "Health and Beauty", icon: "/images/health and beauty.jpg" },
  { id: "c9", label: "Books and Media", icon: null },
  { id: "c10", label: "Food and Gourmet", icon: null },
  { id: "c11", label: "Furniture and Office Equipment", icon: null },
  { id: "c12", label: "Jewelry and Watches", icon: null },
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
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  
  const slides = [
    {
      title: 'Main promo banner 1',
      bgColor: 'bg-violet-100',
      image: '/images/main banner 1.jpg',
    },
    {
      title: 'Main promo banner 2',
      bgColor: 'bg-blue-100',
      image: '/images/main banner 2.jpg',
    },
    {
      title: 'Main promo banner 3',
      bgColor: 'bg-pink-100',
      image: null,
    },
  ];

  useEffect(() => {
    if (isPaused) return;
    
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 5000);

    return () => clearInterval(timer);
  }, [slides.length, isPaused]);

  return (
    <div className="relative overflow-hidden rounded-xl min-h-48 sm:min-h-56">
      {/* Carousel slides */}
      <div className="relative h-full min-h-48 sm:min-h-56">
        {slides.map((slide, index) => (
          <div
            key={index}
            className={`absolute inset-0 transition-opacity duration-1000 ${
              index === currentSlide ? 'opacity-100' : 'opacity-0 pointer-events-none'
            }`}
          >
            {slide.image ? (
              <div className="relative h-full w-full">
                <img src={slide.image} alt={slide.title} className="w-full h-full object-cover rounded-xl" />
              </div>
            ) : (
              <div className={`flex items-center justify-center rounded-xl ${slide.bgColor} text-xs font-medium text-neutral-400 h-full`}>
                {slide.title}
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Carousel indicators */}
      {slides.length > 1 && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
          {slides.map((_, index) => (
            <button
              key={index}
              onClick={() => setCurrentSlide(index)}
              className={`h-1.5 sm:h-2 rounded-full transition-all ${
                index === currentSlide 
                  ? 'w-6 sm:w-8 bg-white' 
                  : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/70'
              }`}
              aria-label={`Go to slide ${index + 1}`}
            />
          ))}
        </div>
      )}

      {/* Navigation controls - bottom right */}
      {slides.length > 1 && (
        <div className="absolute bottom-4 right-4 z-20 flex items-center gap-2">
          <button
            onClick={() => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length)}
            className="w-8 h-8 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center text-violet-600 text-xl font-bold transition shadow-md"
            aria-label="Previous slide"
          >
            ‹
          </button>
          <button
            onClick={() => setIsPaused(!isPaused)}
            className="w-8 h-8 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center text-violet-600 transition shadow-md"
            aria-label={isPaused ? 'Play' : 'Pause'}
          >
            {isPaused ? (
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8 5v14l11-7z"/>
              </svg>
            ) : (
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
              </svg>
            )}
          </button>
          <button
            onClick={() => setCurrentSlide((prev) => (prev + 1) % slides.length)}
            className="w-8 h-8 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center text-violet-600 text-xl font-bold transition shadow-md"
            aria-label="Next slide"
          >
            ›
          </button>
        </div>
      )}
    </div>
  );
}

// Each banner box uses the real aspect ratio of its image file, so
// object-fill never stretches. Images are absolutely positioned inside the
// box, so they never push the row height around.
function SmallPlaceholderBanner({ label }: { label: string }) {
  let imageSrc: string | null = null;
  let ratioClass = "min-h-24";
  if (label === "Perfume & makeup offers") {
    imageSrc = "/images/perfumes banner.jpg";
    ratioClass = "aspect-[1199/439]"; // image is 1199 x 439
  } else if (label === "iPhone offers") {
    imageSrc = "/images/phones banner.jpg";
    ratioClass = "aspect-[1200/600]"; // image is 1200 x 600
  }

  return (
    <div
      className={`relative flex items-center justify-center overflow-hidden rounded-xl bg-neutral-200 text-xs font-medium text-neutral-400 ${ratioClass}`}
    >
      {imageSrc ? (
        <img
          src={imageSrc}
          alt={label}
          className="absolute inset-0 h-full w-full object-fill"
        />
      ) : (
        label
      )}
    </div>
  );
}

// Image is 415 x 356. On mobile it keeps its own ratio; from sm up it
// stretches to the row height (which matches its ratio by design).
function BuyOneGetOneBanner() {
  return (
    <div className="relative aspect-[415/356] overflow-hidden rounded-xl bg-neutral-200 sm:aspect-auto">
      <img
        src="/images/payday sale.png"
        alt="Payday sale"
        className="absolute inset-0 h-full w-full object-fill"
      />
    </div>
  );
}

// Image is 1080 x 1080 (square).
function SuperSeratusBanner() {
  return (
    <div className="relative aspect-square overflow-hidden rounded-xl bg-neutral-200 sm:aspect-auto">
      <img
        src="/images/sale banner.jpg"
        alt="Sale Banner"
        className="absolute inset-0 h-full w-full object-fill"
      />
    </div>
  );
}

function CategoryTile({ category }: { category: Category }) {
  return (
    <button
      className="flex shrink-0 snap-start flex-col items-center gap-1.5 rounded-lg p-1 text-center transition-colors hover:bg-violet-50 sm:gap-2"
      style={{ width: "calc((100% - 40px) / 5)" }}
    >
      <div className="aspect-square w-full rounded-full bg-neutral-200 overflow-hidden">
        {category.icon ? (
          <img 
            src={category.icon} 
            alt={category.label}
            className="w-full h-full object-cover"
          />
        ) : (
          <div className="w-full h-full" />
        )}
      </div>
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
//
//   ┌──────────────────────────────────────────────────────────┐
//   │                                              ◯ username   │
//   │  mimoo   ( ───────── search ─────────────── ●)   🔔  🛒   │
//   └──────────────────────────────────────────────────────────┘
// ---------------------------------------------------------------------------

function CountBadge({ count }: { count: number }) {
  if (count <= 0) return null;
  return (
    <span className="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-semibold leading-none text-white">
      {count > 99 ? "99+" : count}
    </span>
  );
}

function Header({
  username = "username",
  avatarUrl = null,
  notificationCount = 5,
  cartCount = 100,
  onSearch,
}: {
  username?: string;
  avatarUrl?: string | null;
  notificationCount?: number;
  cartCount?: number;
  onSearch?: (query: string) => void;
}) {
  const [query, setQuery] = useState("");

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    onSearch?.(query.trim());
  };

  return (
    <header
      className="w-full text-white"
      style={{
        background:
          "linear-gradient(90deg, #1c1430 0%, #3a2d5c 45%, #6a5a8a 100%)",
      }}
    >
      <div className="mx-auto max-w-6xl px-4">
        {/* Utility row: account, top right (sits above bell + cart) */}
        <div className="flex h-7 items-center justify-end text-[10px] sm:text-[11px]">
          <Link
            href="/account"
            className="flex items-center gap-1.5 text-white/90 transition-colors hover:text-white"
          >
            <span className="h-3.5 w-3.5 shrink-0 overflow-hidden rounded-full bg-neutral-200">
              {avatarUrl && (
                <img
                  src={avatarUrl}
                  alt=""
                  className="h-full w-full object-cover"
                />
              )}
            </span>
            <span className="max-w-24 truncate">{username}</span>
          </Link>
        </div>

        {/* Main row: logo · pill search · bell · cart */}
        <div className="flex items-center gap-3 pb-3 pt-1 sm:gap-6 sm:pb-4">
          <Link
            href="/"
            className="shrink-0 text-xl font-bold lowercase tracking-tight sm:text-2xl"
          >
            mimoo
          </Link>

          <form
            onSubmit={handleSubmit}
            role="search"
            className="relative min-w-0 flex-1"
          >
            <input
              type="search"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search products"
              aria-label="Search products"
              className="h-8 w-full rounded-full bg-neutral-200 pl-4 pr-11 text-sm text-neutral-800 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-violet-300 sm:h-9"
            />
            <button
              type="submit"
              aria-label="Search"
              className="absolute right-1 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full bg-[#2a2340] text-white transition-colors hover:bg-[#3a2d5c] sm:h-7 sm:w-7"
            >
              <Search className="h-3.5 w-3.5" />
            </button>
          </form>

          {/* Bell + cart, side by side on the same row as the search bar */}
          <div className="flex shrink-0 items-center gap-4 sm:gap-5">
            <button
              aria-label="Notifications"
              className="relative flex items-center text-white transition-colors hover:text-white/80"
            >
              <Bell className="h-5 w-5" />
              <CountBadge count={notificationCount} />
            </button>

            <Link
              href="/cart"
              aria-label="Cart"
              className="relative flex items-center text-white transition-colors hover:text-white/80"
            >
              <ShoppingCart className="h-5 w-5" />
              <CountBadge count={cartCount} />
            </Link>
          </div>
        </div>
      </div>
    </header>
  );
}

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

export default function Homepage() {
  // Swap this out for your real auth/session state later — guest, buyer,
  // or seller — to drive the product card CTAs below.
  const role: UserRole = "guest";
  const [isChatOpen, setIsChatOpen] = useState(false);

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
        {/* Hero: big banner on top, 3-column row below it.
            Column widths (fr) are set from the image ratios so all three
            columns come out the same height with no stretching:
            perfume+phone stack : payday : sale  ≈  372 : 390 : 334 */}
        <section className="flex flex-col gap-2 sm:gap-3">
          <HeroMainBanner />

          <div className="grid grid-cols-[162fr_173fr] gap-2 sm:grid-cols-[372fr_390fr_334fr] sm:gap-3">
            <div className="flex flex-col gap-2 sm:gap-3">
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

      {/* Chat Button */}
      <button
        onClick={() => setIsChatOpen(!isChatOpen)}
        className="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full text-white shadow-lg transition-all hover:scale-110"
        style={{ backgroundColor: '#1c1430' }}
        aria-label="Open chat"
      >
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
        >
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
        </svg>
      </button>
    </div>
  );
}
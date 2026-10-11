"use client";

import { useRef, useState, useEffect, type FormEvent } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import {
  ChevronRight,
  ChevronLeft,
  Bell,
  Search,
  ShoppingCart,
  LogOut,
} from "lucide-react";
import { SignupModal } from "@/components/signup-modal";
import { toast } from "sonner";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface Category {
  id: string;
  label: string;
  icon?: string | null;
  subcategories?: string[]; // shown in the header hover menu
}

interface Product {
  id: number;
  name: string;
  price: number;
  sale_price?: number | null;
  effective_price?: number;
  is_on_sale?: boolean;
  stock?: number;
  stock_status?: string;
  is_in_stock?: boolean;
  is_low_stock?: boolean;
  main_image?: string | null;
  seller_name?: string;
  category?: string;
}

// ---------------------------------------------------------------------------
// Placeholder data — swap out with real content/API data later
// ---------------------------------------------------------------------------

const CATEGORIES: Category[] = [
  {
    id: "c1",
    label: "Pet Supplies",
    icon: "/images/pet supplies.jpg",
    subcategories: ["Dog Food", "Cat Food", "Dog Treats", "Cat Litter", "Pet Toys", "Grooming & Shampoo", "Beds & Crates", "Leashes & Collars", "Pet Clothing", "Feeding Bowls", "Pet Health & Vitamins", "Aquarium & Fish", "Bird Supplies", "Small Animal Supplies"],
  },
  {
    id: "c2",
    label: "Electronics and Gadgets",
    icon: "/images/electronics.jpg",
    subcategories: ["Mobile Phones", "Tablets", "Laptops", "Desktop PCs", "Audio & Earbuds", "Speakers", "Cameras", "Smartwatches", "Chargers & Cables", "Power Banks", "Phone Cases", "Gaming Consoles", "Monitors", "Keyboards & Mice", "Smart Home", "Drones"],
  },
  {
    id: "c3",
    label: "Women's Apparel",
    icon: "/images/women's apparel.jpg",
    subcategories: ["Dresses", "Tops & Blouses", "T-Shirts", "Pants & Jeans", "Skirts", "Shorts", "Jackets & Cardigans", "Activewear", "Sleepwear", "Lingerie", "Swimwear", "Handbags", "Sandals & Flats", "Heels", "Sneakers", "Scarves & Hats"],
  },
  {
    id: "c4",
    label: "Men's Apparel",
    icon: "/images/mens apparel.jpg",
    subcategories: ["T-Shirts", "Polo Shirts", "Button-Down Shirts", "Pants & Jeans", "Shorts", "Jackets & Hoodies", "Underwear", "Sleepwear", "Activewear", "Swim Trunks", "Sneakers", "Formal Shoes", "Sandals & Slippers", "Belts & Wallets", "Caps & Hats", "Backpacks"],
  },
  {
    id: "c5",
    label: "Kids and Baby",
    icon: "/images/kids and babies.jpg",
    subcategories: ["Baby Clothing", "Kids' Clothing", "Diapers & Wipes", "Baby Formula", "Feeding & Nursing", "Bath & Skincare", "Strollers", "Car Seats", "Cribs & Bedding", "Toys & Games", "Educational Toys", "Building Blocks", "School Bags", "School Supplies", "Kids' Shoes", "Maternity"],
  },
  {
    id: "c6",
    label: "Home and Garden",
    icon: "/images/home and garden.jpg",
    subcategories: ["Cookware", "Kitchen Appliances", "Dinnerware", "Food Storage", "Bedding & Pillows", "Curtains & Blinds", "Rugs & Carpets", "Home Decor", "Lighting", "Bath Essentials", "Laundry & Cleaning", "Organizers", "Plants & Pots", "Garden Tools", "Fans & Aircon", "Tools & Hardware"],
  },
  {
    id: "c7",
    label: "Sports and Outdoors",
    icon: "/images/sports.jpg",
    subcategories: ["Gym Equipment", "Yoga & Pilates", "Running", "Cycling", "Camping & Hiking", "Fishing", "Basketball", "Badminton & Tennis", "Volleyball", "Football", "Swimming", "Martial Arts", "Skateboards & Scooters", "Sportswear", "Sports Shoes", "Water Bottles & Hydration"],
  },
  {
    id: "c8",
    label: "Health and Beauty",
    icon: "/images/health and beauty.jpg",
    subcategories: ["Face Care", "Body Care", "Sunscreen", "Makeup - Face", "Makeup - Lips", "Makeup - Eyes", "Perfume & Cologne", "Hair Care", "Hair Tools", "Nail Care", "Vitamins & Supplements", "Feminine Care", "Men's Grooming", "Oral Care", "Medical Supplies", "Massage & Wellness"],
  },
  {
    id: "c9",
    label: "Books and Media",
    icon: null,
    subcategories: ["Fiction", "Non-Fiction", "Self-Help", "Children's Books", "Textbooks", "Comics & Manga", "Magazines", "Religious Books", "Cookbooks", "Vinyl & CDs", "Musical Instruments", "Movies & DVDs", "Video Games", "Board Games", "Puzzles", "Art & Craft Supplies"],
  },
  {
    id: "c10",
    label: "Food and Gourmet",
    icon: null,
    subcategories: ["Chips & Crackers", "Biscuits & Cookies", "Chocolates", "Candies", "Coffee", "Tea", "Juices & Soft Drinks", "Rice & Grains", "Canned Goods", "Noodles & Pasta", "Condiments & Sauces", "Cooking Oil & Spices", "Local Delicacies", "Dried Fish & Seafood", "Baking Supplies", "Gift Baskets"],
  },
  {
    id: "c11",
    label: "Furniture and Office Equipment",
    icon: null,
    subcategories: ["Sofas", "Dining Sets", "Beds & Mattresses", "Wardrobes & Cabinets", "Shelves & Bookcases", "TV Stands", "Coffee Tables", "Office Desks", "Office Chairs", "Filing Cabinets", "Printers & Ink", "Paper & Notebooks", "Pens & Markers", "Desk Lamps", "Whiteboards", "Outdoor Furniture"],
  },
  {
    id: "c12",
    label: "Jewelry and Watches",
    icon: null,
    subcategories: ["Necklaces", "Pendants", "Rings", "Wedding Bands", "Earrings", "Bracelets", "Anklets", "Brooches", "Gold Jewelry", "Silver Jewelry", "Pearls & Gemstones", "Men's Watches", "Women's Watches", "Smart & Digital Watches", "Watch Straps", "Jewelry Boxes"],
  },
];

interface HomepageProps {
  auth?: { user: any };
  initialProducts?: Product[];
}

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

function ProductCard({ 
  product, 
  role,
  onGuestAction,
  onAddToCart,
}: { 
  product: Product; 
  role: UserRole;
  onGuestAction?: () => void;
  onAddToCart?: (productId: number) => void;
}) {
  const formatPrice = (price: number) => {
    return '₱' + price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  return (
    <Link
      href={`/product/${product.id}`}
      className="flex flex-col overflow-hidden rounded-lg border border-neutral-200 bg-white transition-shadow hover:shadow-md"
    >
      <div className="aspect-square w-full bg-neutral-200 overflow-hidden">
        {product.main_image ? (
          <img src={product.main_image} alt={product.name} className="w-full h-full object-cover" />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-neutral-400 text-xs">
            No image
          </div>
        )}
      </div>
      <div className="flex flex-1 flex-col gap-1 p-3">
        <span className="text-sm text-neutral-800 line-clamp-2">
          {product.name}
        </span>
        <div className="flex items-center gap-2">
          {product.is_on_sale && product.sale_price ? (
            <>
              <span className="text-sm font-semibold text-violet-700">
                {formatPrice(product.effective_price || product.sale_price)}
              </span>
              <span className="text-xs text-neutral-500 line-through">
                {formatPrice(product.price)}
              </span>
            </>
          ) : (
            <span className="text-sm font-semibold text-violet-700">
              {formatPrice(product.price)}
            </span>
          )}
        </div>
        {product.stock_status && (
          <span className={`text-xs ${
            product.is_in_stock 
              ? product.is_low_stock ? 'text-orange-600' : 'text-green-600'
              : 'text-red-600'
          }`}>
            {product.stock_status}
          </span>
        )}
        {role === "guest" && (
          <button
            onClick={(e) => {
              e.preventDefault();
              onGuestAction?.();
            }}
            className="mt-2 rounded-md border border-violet-600 py-1.5 text-xs font-medium text-violet-600 transition-colors hover:bg-violet-50"
          >
            Log in to buy
          </button>
        )}
        {role === "buyer" && product.is_in_stock && (
          <button
            onClick={(e) => {
              e.preventDefault();
              onAddToCart?.(product.id);
            }}
            className="mt-2 rounded-md bg-violet-600 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700"
          >
            Add to cart
          </button>
        )}
        {role === "buyer" && !product.is_in_stock && (
          <button
            disabled
            className="mt-2 rounded-md bg-neutral-300 py-1.5 text-xs font-medium text-neutral-500 cursor-not-allowed"
          >
            Out of stock
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
// CategoryNav — plain-text category row under the header. Hovering a
// category (desktop) or tapping it (touch devices) opens a full-width panel
// with that category's subcategories.
// ---------------------------------------------------------------------------

function CategoryNav({ categories }: { categories: Category[] }) {
  const [activeId, setActiveId] = useState<string | null>(null);
  const navRef = useRef<HTMLElement>(null);
  const active = categories.find((c) => c.id === activeId);

  // Close when clicking/tapping outside the nav
  useEffect(() => {
    const handler = (e: MouseEvent | TouchEvent) => {
      if (navRef.current && !navRef.current.contains(e.target as Node)) {
        setActiveId(null);
      }
    };
    document.addEventListener("mousedown", handler);
    document.addEventListener("touchstart", handler);
    return () => {
      document.removeEventListener("mousedown", handler);
      document.removeEventListener("touchstart", handler);
    };
  }, []);

  const categoryHref = (c: Category, sub?: string) =>
    `/category/${encodeURIComponent(c.label)}${
      sub ? `?sub=${encodeURIComponent(sub)}` : ""
    }`;

  const isTouch = () =>
    typeof window !== "undefined" &&
    window.matchMedia("(hover: none)").matches;

  return (
    <nav
      ref={navRef}
      onMouseLeave={() => setActiveId(null)}
      className="relative border-t border-white/10 bg-black/10"
      aria-label="Categories"
    >
      <div className="mx-auto max-w-6xl px-4">
        <ul
          className="flex gap-1 overflow-x-auto whitespace-nowrap py-1 text-xs sm:text-[13px]"
          style={{ scrollbarWidth: "none", msOverflowStyle: "none" }}
        >
          {categories.map((c) => (
            <li key={c.id} onMouseEnter={() => setActiveId(c.id)}>
              <Link
                href={categoryHref(c)}
                onClick={(e) => {
                  // On touch devices the first tap opens the menu instead of navigating
                  if (isTouch() && c.subcategories?.length) {
                    e.preventDefault();
                    setActiveId(activeId === c.id ? null : c.id);
                  }
                }}
                className={`block rounded px-3 py-2 text-white/90 transition-colors hover:text-white ${
                  activeId === c.id ? "bg-white/15 text-white" : ""
                }`}
              >
                {c.label}
              </Link>
            </li>
          ))}
        </ul>
      </div>

      {/* Subcategory panel */}
      {active?.subcategories && active.subcategories.length > 0 && (
        <div className="absolute left-0 right-0 top-full z-40 border-b border-neutral-200 bg-white text-neutral-800 shadow-lg">
          <div className="mx-auto max-w-6xl px-4 py-4">
            <Link
              href={categoryHref(active)}
              onClick={() => setActiveId(null)}
              className="mb-3 inline-block text-sm font-bold text-violet-700 hover:underline"
            >
              {active.label}
            </Link>
            <div className="grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-3 lg:grid-cols-4">
              {active.subcategories.map((sub) => (
                <Link
                  key={sub}
                  href={categoryHref(active, sub)}
                  onClick={() => setActiveId(null)}
                  className="rounded px-2 py-1 text-neutral-700 transition-colors hover:bg-violet-50 hover:text-violet-700"
                >
                  {sub}
                </Link>
              ))}
            </div>
          </div>
        </div>
      )}
    </nav>
  );
}

// ---------------------------------------------------------------------------
// Header — search bar + cart/account icons always shown, matching the
// reference design. Role only changes downstream content (product CTAs).
// The category nav sits in its own row below the logo/search row.
//
//   ┌──────────────────────────────────────────────────────────┐
//   │                                              ◯ username   │
//   │  mimoo   ( ───────── search ─────────────── ●)   🔔  🛒   │
//   │  Pet Supplies  Electronics  Women's Apparel  ...         │
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
  cartCount = 0,
  categories = [],
  onSearch,
  onLogout,
}: {
  username?: string;
  avatarUrl?: string | null;
  notificationCount?: number;
  cartCount?: number;
  categories?: Category[];
  onSearch?: (query: string) => void;
  onLogout?: () => void;
}) {
  const [query, setQuery] = useState("");
  const [showMenu, setShowMenu] = useState(false);

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
          <div className="relative">
            <button
              onClick={() => setShowMenu(!showMenu)}
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
            </button>

            {/* Dropdown menu */}
            {showMenu && (
              <div className="absolute right-0 top-full mt-1 w-36 rounded-lg bg-white shadow-lg ring-1 ring-black/5 z-50">
                <Link
                  href="/orders"
                  className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg"
                  onClick={() => setShowMenu(false)}
                >
                  My Purchases
                </Link>
                <Link
                  href="/settings/profile"
                  className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  onClick={() => setShowMenu(false)}
                >
                  Settings
                </Link>
                <button
                  onClick={() => {
                    setShowMenu(false);
                    onLogout?.();
                  }}
                  className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-50 rounded-b-lg"
                >
                  <LogOut className="h-3.5 w-3.5" />
                  Logout
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Main row: logo · pill search · bell · cart */}
        <div className="flex items-center gap-3 pb-3 pt-1 sm:gap-6 sm:pb-4">
          <Link
            href="/homepage"
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

      {/* Category nav row (full width so the hover panel can span the header) */}
      {categories.length > 0 && <CategoryNav categories={categories} />}
    </header>
  );
}

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

export default function Homepage({ auth, initialProducts = [] }: HomepageProps) {
  const user = auth?.user;
  
  const role: UserRole = user ? (user.account_type as UserRole) : "guest";
  const [showSignupModal, setShowSignupModal] = useState(false);
  const [products, setProducts] = useState<Product[]>(initialProducts);
  const [cartCount, setCartCount] = useState(0);
  const [loading, setLoading] = useState(false);

  // Fetch products on mount
  useEffect(() => {
    fetchProducts();
    if (user && user.account_type === 'buyer') {
      fetchCartCount();
    }
  }, []);

  async function fetchProducts() {
    try {
      const response = await fetch('/api/products');
      const data = await response.json();
      if (data.data) {
        setProducts(data.data);
      }
    } catch (error) {
      console.error('Failed to fetch products:', error);
    }
  }

  async function fetchCartCount() {
    try {
      const response = await fetch('/api/cart/count');
      const data = await response.json();
      setCartCount(data.count || 0);
    } catch (error) {
      console.error('Failed to fetch cart count:', error);
    }
  }

  async function handleAddToCart(productId: number) {
    if (!user) {
      setShowSignupModal(true);
      return;
    }

    if (user.account_type !== 'buyer') {
      toast.error('Only buyers can add items to cart');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch('/api/cart/items', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          product_id: productId,
          quantity: 1,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        toast.success(data.message || 'Added to cart!');
        setCartCount(data.cart_count || 0);
      } else {
        toast.error(data.error || 'Failed to add to cart');
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      toast.error('Failed to add to cart');
    } finally {
      setLoading(false);
    }
  }

  function handleLogout() {
    router.post('/logout');
  }

  function handleGuestAction() {
    setShowSignupModal(true);
  }

  function handleSearch(query: string) {
    if (query) {
      router.visit(`/homepage?search=${encodeURIComponent(query)}`);
    }
  }

  return (
    <div
      className="min-h-screen bg-neutral-50"
      style={{ fontFamily: "'Syne', sans-serif" }}
    >
      <Head title="Home" />
      
      {/* Loads Syne from Google Fonts. If you already load it globally
          (e.g. via next/font or a <link> in your root layout), remove this
          block and the inline fontFamily above to avoid loading it twice. */}
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&display=swap');
      `}</style>

      <Header 
        username={user ? user.name : "Guest"} 
        onLogout={user ? handleLogout : undefined}
        cartCount={cartCount}
        categories={CATEGORIES}
        onSearch={handleSearch}
      />

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
              {products.length > 0 ? 'Available Products' : 'Top products'}
            </h2>
            <button className="flex items-center text-xs text-violet-600 hover:underline sm:text-sm">
              See all <ChevronRight className="h-4 w-4" />
            </button>
          </div>
          <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 lg:grid-cols-5">
            {products.slice(0, 10).map((p) => (
              <ProductCard 
                key={p.id} 
                product={p} 
                role={role} 
                onGuestAction={handleGuestAction}
                onAddToCart={handleAddToCart}
              />
            ))}
          </div>
          {products.length === 0 && (
            <div className="mt-4 text-center text-sm text-neutral-500">
              No products available yet. Sellers can start adding products!
            </div>
          )}
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
          {products.slice(0, 10).map((p) => (
            <ProductCard 
              key={`rec-${p.id}`} 
              product={p} 
              role={role} 
              onGuestAction={handleGuestAction}
              onAddToCart={handleAddToCart}
            />
          ))}
        </section>
      </main>

      {/* Signup Modal */}
      <SignupModal open={showSignupModal} onClose={() => setShowSignupModal(false)} />

      {/* Chat Button */}
      <button
        onClick={() => {}}
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
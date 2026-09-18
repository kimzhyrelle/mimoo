import {
  Search,
  ShoppingCart,
  User,
} from "lucide-react";
import { Link } from "@inertiajs/react";

export default function Header() {
  return (
    <header
      className="sticky top-0 z-10 py-3 shadow-sm"
      style={{
        background: "linear-gradient(90deg, #19132A 0%, #6E5F8F 100%)",
      }}
    >
      <div className="flex items-center justify-between gap-3 px-4">
        <Link
          href="/homepage"
          className="shrink-0 text-base font-bold tracking-tight text-white hover:opacity-90 transition-opacity sm:text-lg"
        >
          mimoo
        </Link>

        <div className="flex flex-1 max-w-2xl items-center gap-2 rounded-full bg-white px-3 py-1.5 sm:px-4 sm:py-2">
          <Search className="h-4 w-4 shrink-0 text-neutral-400" />
          <input
            type="text"
            placeholder="Search"
            className="w-full bg-transparent text-xs text-neutral-700 outline-none placeholder:text-neutral-400 sm:text-sm"
          />
        </div>

        <nav className="flex shrink-0 items-center gap-0.5 sm:gap-1">
          <button
            aria-label="Cart"
            className="relative rounded-full p-1.5 text-white hover:bg-white/10 sm:p-2"
          >
            <ShoppingCart className="h-4 w-4 sm:h-5 sm:w-5" />
          </button>
          <button
            aria-label="Account"
            className="rounded-full p-1.5 text-white hover:bg-white/10 sm:p-2"
          >
            <User className="h-4 w-4 sm:h-5 sm:w-5" />
          </button>
        </nav>
      </div>
    </header>
  );
}

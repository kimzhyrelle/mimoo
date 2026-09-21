import { useState, type FormEvent } from "react";
import { Link } from "@inertiajs/react";
import { Bell, Search, ShoppingCart, LogOut } from "lucide-react";

interface BuyerHeaderProps {
  user?: any;
  cartCount?: number;
  notificationCount?: number;
  onSearch?: (query: string) => void;
  onLogout?: () => void;
  onGuestAction?: () => void;
}

function CountBadge({ count }: { count: number }) {
  if (count <= 0) return null;
  return (
    <span className="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-semibold leading-none text-white">
      {count > 99 ? "99+" : count}
    </span>
  );
}

export function BuyerHeader({
  user,
  cartCount = 0,
  notificationCount = 5,
  onSearch,
  onLogout,
  onGuestAction,
}: BuyerHeaderProps) {
  const [query, setQuery] = useState("");
  const [showMenu, setShowMenu] = useState(false);

  // Single source of truth — everything branches off this
  const isLoggedIn = !!user;
  const username = user?.name ?? "Guest";

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    onSearch?.(query.trim());
  };

  return (
    <header
      className="w-full text-white"
      style={{
        background: "linear-gradient(90deg, #1c1430 0%, #3a2d5c 45%, #6a5a8a 100%)",
      }}
    >
      <div className="mx-auto max-w-[1100px] px-6">
        {/* Utility row: account top-right */}
        <div className="flex h-7 items-center justify-end text-[10px] sm:text-[11px]">
          <div className="relative">
            <button
              onClick={() => isLoggedIn ? setShowMenu(!showMenu) : onGuestAction?.()}
              className="flex items-center gap-1.5 text-white/90 transition-colors hover:text-white"
            >
              <span className="h-3.5 w-3.5 shrink-0 overflow-hidden rounded-full bg-neutral-200" />
              <span className="max-w-24 truncate">{username}</span>
            </button>

            {showMenu && isLoggedIn && (
              <div className="absolute right-0 top-full mt-1 w-32 rounded-lg bg-white shadow-lg ring-1 ring-black/5 z-50">
                <Link
                  href="/settings/profile"
                  className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg"
                  onClick={() => setShowMenu(false)}
                >
                  Settings
                </Link>
                <button
                  onClick={() => { setShowMenu(false); onLogout?.(); }}
                  className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-50 rounded-b-lg"
                >
                  <LogOut className="h-3.5 w-3.5" />
                  Logout
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Main row: logo · search · bell · cart */}
        <div className="flex items-center gap-3 pb-3 pt-1 sm:gap-6 sm:pb-4">
          <Link
            href={isLoggedIn ? "/homepage" : "/"}
            className="shrink-0 text-xl font-bold lowercase tracking-tight sm:text-2xl font-[Syncopate]"
          >
            mimoo
          </Link>

          <form onSubmit={handleSubmit} role="search" className="relative min-w-0 flex-1">
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

          <div className="flex shrink-0 items-center gap-4 sm:gap-5">
            {/* Bell */}
            <button
              aria-label="Notifications"
              onClick={!isLoggedIn ? onGuestAction : undefined}
              className="relative flex items-center text-white transition-colors hover:text-white/80"
            >
              <Bell className="h-5 w-5" />
              <CountBadge count={notificationCount} />
            </button>

            {/* Cart — Link for logged-in, button for guests */}
            {isLoggedIn ? (
              <Link
                href="/cart"
                aria-label="Cart"
                className="relative flex items-center text-white transition-colors hover:text-white/80"
              >
                <ShoppingCart className="h-5 w-5" />
                <CountBadge count={cartCount} />
              </Link>
            ) : (
              <button
                onClick={onGuestAction}
                aria-label="Cart"
                className="relative flex items-center text-white transition-colors hover:text-white/80"
              >
                <ShoppingCart className="h-5 w-5" />
                <CountBadge count={cartCount} />
              </button>
            )}
          </div>
        </div>
      </div>
    </header>
  );
}

import {
  Search,
  ShoppingCart,
  User,
  X,
} from "lucide-react";
import { Link } from "@inertiajs/react";
import { useState } from "react";

export default function Header() {
  const [showSignInModal, setShowSignInModal] = useState(false);
  const isAuthenticated = false; // TODO: Replace with actual auth check

  const handleCartClick = () => {
    if (!isAuthenticated) {
      setShowSignInModal(true);
    } else {
      // Handle cart navigation for authenticated users
    }
  };

  return (
    <>
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
              onClick={handleCartClick}
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

      {/* Sign In Modal */}
      {showSignInModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <button
              onClick={() => setShowSignInModal(false)}
              className="absolute right-4 top-4 rounded-full p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600"
              aria-label="Close"
            >
              <X className="h-5 w-5" />
            </button>

            <div className="text-center">
              <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-violet-100">
                <ShoppingCart className="h-8 w-8 text-violet-600" />
              </div>

              <h2 className="mb-2 text-xl font-bold text-neutral-900">
                Sign in required
              </h2>
              <p className="mb-6 text-sm text-neutral-600">
                You need to sign in to access your cart and make purchases.
              </p>

              <div className="flex flex-col gap-2">
                <Link
                  href="/register"
                  className="rounded-lg bg-violet-900 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-violet-800"
                >
                  Create account
                </Link>
                <button
                  onClick={() => setShowSignInModal(false)}
                  className="rounded-lg border border-neutral-300 px-6 py-3 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

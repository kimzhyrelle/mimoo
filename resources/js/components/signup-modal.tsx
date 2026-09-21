import { Link } from "@inertiajs/react";
import { X } from "lucide-react";

interface SignupModalProps {
  open: boolean;
  onClose: () => void;
}

export function SignupModal({ open, onClose }: SignupModalProps) {
  if (!open) return null;

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 z-50 bg-black/50 transition-opacity"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="fixed left-1/2 top-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-8 shadow-2xl">
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute right-4 top-4 rounded-full p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600"
          aria-label="Close"
        >
          <X className="h-5 w-5" />
        </button>

        {/* Content */}
        <div className="text-center">
          <h2
            className="text-2xl font-bold text-neutral-900"
            style={{ fontFamily: "'Syne', sans-serif" }}
          >
            Join mimoo
          </h2>
          <p className="mt-2 text-sm text-neutral-600">
            Create an account to start shopping and enjoy exclusive deals
          </p>

          <div className="mt-8 flex flex-col gap-3">
            <Link
              href="/register"
              className="rounded-lg py-3 text-sm font-semibold text-white transition-colors"
              style={{
                background:
                  "linear-gradient(90deg, #1c1430 0%, #3a2d5c 45%, #6a5a8a 100%)",
              }}
            >
              Sign Up
            </Link>

            <Link
              href="/login"
              className="rounded-lg border-2 py-3 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
              style={{ borderColor: "#3a2d5c" }}
            >
              Log In
            </Link>
          </div>

          <p className="mt-6 text-xs text-neutral-500">
            By continuing, you agree to our Terms of Service and Privacy Policy
          </p>
        </div>
      </div>
    </>
  );
}

"use client";

import { useState, useRef } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import { ArrowLeft, Package, CreditCard, MapPin, Phone, User, Loader2 } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";
import { toast } from "sonner";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface CartItem {
  id: number;
  product_id: number;
  product_name: string;
  product_image: string | null;
  seller_name: string;
  unit_price: number;
  quantity: number;
  line_total: number;
}

interface PageProps {
  cartItems: CartItem[];
  subtotal: number;
  total: number;
  auth: { user: any };
  [key: string]: any;
}

// ---------------------------------------------------------------------------
// Shared input style (explicit colors so text is always visible)
// ---------------------------------------------------------------------------

const inputClass =
  "w-full px-4 py-2.5 rounded-lg border-[1.5px] border-[#E6DFF2] bg-white text-[#2A1B4D] " +
  "placeholder:text-[#9B8FB0] caret-[#4B2E7E] [color-scheme:light] " +
  "focus:border-[#4B2E7E] focus:ring-2 focus:ring-[#4B2E7E]/20 outline-none text-[0.88rem]";

// ---------------------------------------------------------------------------
// Checkout Page Component
// ---------------------------------------------------------------------------

export default function Checkout() {
  const { cartItems, subtotal, total, auth } = usePage<PageProps>().props;
  const user = auth?.user;

  // State
  const [submitting, setSubmitting] = useState(false);
  const [shippingName, setShippingName] = useState(user?.name || "");
  const [shippingAddress, setShippingAddress] = useState("");
  const [shippingPhone, setShippingPhone] = useState("");
  const [paymentMethod, setPaymentMethod] = useState<"cod" | "gcash" | "paymongo">("cod");

  // Created ONCE per checkout page, so double-clicks/retries reuse the same key
  const idempotencyKeyRef = useRef(
    `${user?.id}-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`
  );

  const peso = (n: number) =>
    "₱" + n.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function handleLogout() {
    router.post("/logout");
  }

  async function handlePlaceOrder(e: React.FormEvent) {
    e.preventDefault();

    // Prevent double submit
    if (submitting) return;

    // Validate
    if (!shippingName.trim()) {
      toast.error("Please enter your name");
      return;
    }
    if (!shippingAddress.trim()) {
      toast.error("Please enter your address");
      return;
    }
    if (!shippingPhone.trim()) {
      toast.error("Please enter your phone number");
      return;
    }

    setSubmitting(true);

    try {
      const response = await fetch("/api/orders", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN":
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
        },
        body: JSON.stringify({
          payment_method: paymentMethod,
          shipping_name: shippingName,
          shipping_address: shippingAddress,
          shipping_phone: shippingPhone,
          idempotency_key: idempotencyKeyRef.current,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        // Success - redirect to order success page
        toast.success(data.message || "Order placed successfully!");
        router.visit(`/orders/success?order_id=${data.order_id}`);
      } else {
        // Error
        toast.error(data.error || "Failed to place order");

        // If insufficient stock, redirect back to cart
        if (data.available_stock !== undefined) {
          setTimeout(() => {
            router.visit("/cart");
          }, 2000);
        }
      }
    } catch (error) {
      console.error("Checkout error:", error);
      toast.error("Failed to place order. Please try again.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="min-h-screen bg-[#f5f4f8] font-[Syne]">
      <Head title="Checkout" />

      <BuyerHeader user={user} onLogout={handleLogout} cartCount={cartItems.length} />

      <main className="max-w-[1100px] mx-auto px-6 py-6 pb-16">
        {/* Checkout header */}
        <div className="flex items-center justify-between gap-4 mb-4">
          <div>
            <h1 className="text-[1.3rem] font-bold" style={{ fontFamily: "'Syne', sans-serif" }}>
              Checkout
            </h1>
            <p className="text-[0.83rem] text-[#6E6570] mt-1">Complete your order</p>
          </div>
          <button
            type="button"
            onClick={() => router.visit("/cart")}
            className="inline-flex items-center gap-2 text-[0.82rem] font-bold text-[#4B2E7E] hover:underline"
          >
            <ArrowLeft className="w-[14px] h-[14px]" />
            Back to Cart
          </button>
        </div>

        <form onSubmit={handlePlaceOrder}>
          <div className="grid lg:grid-cols-[1fr_400px] gap-5">
            {/* Left column - Forms */}
            <div className="space-y-5">
              {/* Shipping Information */}
              <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5">
                <div className="flex items-center gap-2 mb-4">
                  <MapPin className="w-5 h-5 text-[#4B2E7E]" />
                  <h2 className="text-[1.05rem] font-bold text-[#2A1B4D]">Shipping Information</h2>
                </div>

                <div className="space-y-4">
                  <div>
                    <label className="block text-[0.83rem] font-semibold text-[#2A1B4D] mb-1.5">
                      <User className="w-4 h-4 inline mr-1" />
                      Full Name
                    </label>
                    <input
                      type="text"
                      value={shippingName}
                      onChange={(e) => setShippingName(e.target.value)}
                      placeholder="Enter your full name"
                      className={inputClass}
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-[0.83rem] font-semibold text-[#2A1B4D] mb-1.5">
                      <MapPin className="w-4 h-4 inline mr-1" />
                      Address
                    </label>
                    <textarea
                      value={shippingAddress}
                      onChange={(e) => setShippingAddress(e.target.value)}
                      placeholder="House No., Street, Barangay, City, Province"
                      rows={3}
                      className={`${inputClass} resize-none`}
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-[0.83rem] font-semibold text-[#2A1B4D] mb-1.5">
                      <Phone className="w-4 h-4 inline mr-1" />
                      Phone Number
                    </label>
                    <input
                      type="tel"
                      value={shippingPhone}
                      onChange={(e) => setShippingPhone(e.target.value)}
                      placeholder="09XX XXX XXXX"
                      className={inputClass}
                      required
                    />
                  </div>
                </div>
              </div>

              {/* Payment Method */}
              <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5">
                <div className="flex items-center gap-2 mb-4">
                  <CreditCard className="w-5 h-5 text-[#4B2E7E]" />
                  <h2 className="text-[1.05rem] font-bold text-[#2A1B4D]">Payment Method</h2>
                </div>

                <div className="space-y-3">
                  <label
                    className={`flex items-center gap-3 p-4 rounded-lg border-[1.5px] cursor-pointer transition ${
                      paymentMethod === "cod"
                        ? "border-[#4B2E7E] bg-[#F9F7FC]"
                        : "border-[#E6DFF2] hover:border-[#B9A6DE]"
                    }`}
                  >
                    <input
                      type="radio"
                      name="payment"
                      value="cod"
                      checked={paymentMethod === "cod"}
                      onChange={() => setPaymentMethod("cod")}
                      className="w-4 h-4 accent-[#4B2E7E]"
                    />
                    <div className="flex-1">
                      <div className="text-[0.88rem] font-bold text-[#2A1B4D]">Cash on Delivery</div>
                      <div className="text-[0.76rem] text-[#6E6570]">Pay when you receive</div>
                    </div>
                  </label>

                  <label
                    className={`flex items-center gap-3 p-4 rounded-lg border-[1.5px] cursor-pointer transition ${
                      paymentMethod === "gcash"
                        ? "border-[#4B2E7E] bg-[#F9F7FC]"
                        : "border-[#E6DFF2] hover:border-[#B9A6DE]"
                    }`}
                  >
                    <input
                      type="radio"
                      name="payment"
                      value="gcash"
                      checked={paymentMethod === "gcash"}
                      onChange={() => setPaymentMethod("gcash")}
                      className="w-4 h-4 accent-[#4B2E7E]"
                    />
                    <div className="flex-1">
                      <div className="text-[0.88rem] font-bold text-[#2A1B4D]">GCash (Mock)</div>
                      <div className="text-[0.76rem] text-[#6E6570]">Digital payment</div>
                    </div>
                  </label>

                  <label
                    className={`flex items-center gap-3 p-4 rounded-lg border-[1.5px] cursor-pointer transition ${
                      paymentMethod === "paymongo"
                        ? "border-[#4B2E7E] bg-[#F9F7FC]"
                        : "border-[#E6DFF2] hover:border-[#B9A6DE]"
                    }`}
                  >
                    <input
                      type="radio"
                      name="payment"
                      value="paymongo"
                      checked={paymentMethod === "paymongo"}
                      onChange={() => setPaymentMethod("paymongo")}
                      className="w-4 h-4 accent-[#4B2E7E]"
                    />
                    <div className="flex-1">
                      <div className="text-[0.88rem] font-bold text-[#2A1B4D]">PayMongo (Mock)</div>
                      <div className="text-[0.76rem] text-[#6E6570]">Credit/Debit card</div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            {/* Right column - Order Summary */}
            <div>
              <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5 sticky top-6">
                <h2 className="text-[1.05rem] font-bold text-[#2A1B4D] mb-4">Order Summary</h2>

                {/* Items */}
                <div className="space-y-3 mb-4 max-h-[300px] overflow-y-auto">
                  {cartItems.map((item) => (
                    <div key={item.id} className="flex gap-3">
                      <div className="w-[60px] h-[60px] rounded-lg bg-[#F9F7FC] border border-[#E6DFF2] flex items-center justify-center overflow-hidden flex-none">
                        {item.product_image ? (
                          <img
                            src={item.product_image}
                            alt={item.product_name}
                            className="w-full h-full object-cover"
                          />
                        ) : (
                          <Package className="w-6 h-6 text-[#B9A6DE]" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="text-[0.85rem] font-bold text-[#2A1B4D] leading-snug line-clamp-2">
                          {item.product_name}
                        </div>
                        <div className="text-[0.75rem] text-[#6E6570] mt-0.5">{item.seller_name}</div>
                        <div className="flex items-center justify-between mt-1">
                          <span className="text-[0.8rem] text-[#6E6570]">Qty: {item.quantity}</span>
                          <span
                            className="text-[0.88rem] font-extrabold text-[#4B2E7E]"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                          >
                            {peso(item.line_total)}
                          </span>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="border-t border-[#E6DFF2] pt-4 space-y-2">
                  <div className="flex justify-between text-[0.85rem]">
                    <span className="text-[#6E6570]">Subtotal</span>
                    <span
                      className="font-bold text-[#2A1B4D]"
                      style={{ fontFamily: "'Poppins', sans-serif" }}
                    >
                      {peso(subtotal)}
                    </span>
                  </div>
                  <div className="flex justify-between text-[0.85rem]">
                    <span className="text-[#6E6570]">Shipping</span>
                    <span
                      className="font-bold text-[#2A1B4D]"
                      style={{ fontFamily: "'Poppins', sans-serif" }}
                    >
                      FREE
                    </span>
                  </div>
                  <div className="flex justify-between text-[1.1rem] pt-2 border-t border-[#E6DFF2]">
                    <span className="font-bold text-[#2A1B4D]">Total</span>
                    <span
                      className="font-extrabold text-[#4B2E7E]"
                      style={{ fontFamily: "'Poppins', sans-serif" }}
                    >
                      {peso(total)}
                    </span>
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={submitting}
                  className="w-full mt-5 bg-gradient-to-r from-[#2A1B4D] to-[#4B2E7E] text-white font-extrabold text-[0.92rem] px-6 py-3 rounded-full flex items-center justify-center gap-2 shadow-[0_4px_18px_rgba(75,46,126,0.45)] transition-all hover:translate-y-[-2px] hover:shadow-[0_8px_28px_rgba(75,46,126,0.55)] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                  {submitting ? (
                    <>
                      <Loader2 className="w-5 h-5 animate-spin" />
                      Processing...
                    </>
                  ) : (
                    <>Place Order</>
                  )}
                </button>

                <p className="text-[0.72rem] text-center text-[#6E6570] mt-3">
                  By placing this order, you agree to our terms and conditions
                </p>
              </div>
            </div>
          </div>
        </form>
      </main>
    </div>
  );
}
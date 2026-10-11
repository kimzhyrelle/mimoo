"use client";

import { useState, useEffect } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { ArrowLeft, Package, X, ChevronRight, AlertCircle } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";
import { toast } from "sonner";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface CartItem {
  id: number;
  product_id: number;
  product_name: string;
  product_image: string | null;
  product_sku: string;
  seller_name: string;
  unit_price: number;
  regular_price: number;
  is_on_sale: boolean;
  quantity: number;
  line_total: number;
  max_quantity: number;
  stock_status: string;
  is_valid: boolean;
  validation_issue: string | null;
  product: {
    id: number;
    name: string;
    status: string;
    stock: number;
  };
}

interface CartData {
  items: CartItem[];
  subtotal: number;
  total: number;
  total_items: number;
  has_invalid_items: boolean;
}

// ---------------------------------------------------------------------------
// Cart Page Component
// ---------------------------------------------------------------------------

export default function Cart() {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth?.user;
  const role: UserRole = user?.account_type ? (user.account_type as UserRole) : "guest";

  // State
  const [cart, setCart] = useState<CartData | null>(null);
  const [loading, setLoading] = useState(true);
  const [updating, setUpdating] = useState<number | null>(null);

  const peso = (n: number) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  // Fetch cart on mount
  useEffect(() => {
    fetchCart();
  }, []);

  async function fetchCart() {
    try {
      const response = await fetch('/api/cart');
      const data = await response.json();
      setCart(data);
    } catch (error) {
      console.error('Failed to fetch cart:', error);
      toast.error('Failed to load cart');
    } finally {
      setLoading(false);
    }
  }

  async function updateQuantity(itemId: number, newQty: number) {
    if (newQty < 1) return;
    
    setUpdating(itemId);
    try {
      const response = await fetch(`/api/cart/items/${itemId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ quantity: newQty }),
      });

      const data = await response.json();

      if (response.ok) {
        await fetchCart(); // Refresh cart
      } else {
        toast.error(data.error || 'Failed to update quantity');
      }
    } catch (error) {
      console.error('Update quantity error:', error);
      toast.error('Failed to update quantity');
    } finally {
      setUpdating(null);
    }
  }

  async function removeItem(itemId: number) {
    try {
      const response = await fetch(`/api/cart/items/${itemId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      const data = await response.json();

      if (response.ok) {
        toast.success(data.message || 'Item removed');
        await fetchCart(); // Refresh cart
      } else {
        toast.error('Failed to remove item');
      }
    } catch (error) {
      console.error('Remove item error:', error);
      toast.error('Failed to remove item');
    }
  }

  function handleLogout() {
    router.post('/logout');
  }

  function handleCheckout() {
    if (cart?.has_invalid_items) {
      toast.error('Please fix invalid items before checkout');
      return;
    }
    router.visit('/checkout');
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-[#f5f4f8] font-[Syne]">
        <Head title="My Cart" />
        <BuyerHeader user={user} onLogout={handleLogout} cartCount={0} />
        <div className="flex items-center justify-center min-h-[50vh]">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-violet-700 mx-auto"></div>
            <p className="mt-4 text-neutral-600">Loading cart...</p>
          </div>
        </div>
      </div>
    );
  }

  const items = cart?.items || [];
  const isEmpty = items.length === 0;

  return (
    <div className="min-h-screen bg-[#f5f4f8] font-[Syne]">
      <Head title="My Cart" />

      <BuyerHeader
        user={user}
        onLogout={handleLogout}
        cartCount={cart?.total_items || 0}
      />

      <main className="max-w-[1100px] mx-auto px-6 py-6 pb-16">
        {/* Cart header */}
        <div className="flex items-end justify-between gap-4 mb-4 flex-wrap">
          <div>
            <h1 className="text-[1.3rem] font-bold" style={{ fontFamily: "'Syne', sans-serif" }}>My Cart</h1>
            <p className="text-[0.83rem] text-[#6E6570] mt-1">
              <span className="font-bold text-[#2A1B4D]" style={{ fontFamily: "'Poppins', sans-serif" }}>{items.length}</span> items in your cart
            </p>
          </div>
          <Link href="/homepage" className="inline-flex items-center gap-2 text-[0.82rem] font-bold text-[#4B2E7E] hover:underline">
            <ArrowLeft className="w-[14px] h-[14px]" />
            Continue shopping
          </Link>
        </div>

        {/* Invalid items warning */}
        {cart?.has_invalid_items && (
          <div className="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-4 mb-4 flex items-start gap-3">
            <AlertCircle className="w-5 h-5 text-orange-600 flex-none mt-0.5" />
            <div>
              <h3 className="text-sm font-bold text-orange-900 mb-1">Some items need attention</h3>
              <p className="text-sm text-orange-800">Please fix invalid items before checkout</p>
            </div>
          </div>
        )}

        {/* Cart items card */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5 mb-4">
          {/* Column headers */}
          <div className="grid grid-cols-[72px_1fr_110px_132px_110px_60px] items-center gap-x-6 pb-3 mb-1 border-b-[1.5px] border-[#E6DFF2] text-[0.72rem] font-bold text-[#6E6570] uppercase tracking-wide">
            <span></span>
            <span>Product</span>
            <span className="text-right">Unit Price</span>
            <span className="text-center">Quantity</span>
            <span className="text-right">Total Price</span>
            <span className="text-center">Actions</span>
          </div>

          {/* Empty state */}
          {isEmpty ? (
            <div className="py-12 text-center">
              <Package className="w-16 h-16 text-neutral-300 mx-auto mb-4" />
              <p className="text-[0.88rem] text-[#6E6570] mb-4">Your cart is empty</p>
              <Link
                href="/homepage"
                className="inline-flex items-center gap-2 bg-[#4B2E7E] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#3a2360] transition"
              >
                Start Shopping
              </Link>
            </div>
          ) : (
            // Cart items
            items.map((item) => (
              <div
                key={item.id}
                className={`grid grid-cols-[72px_1fr_110px_132px_110px_60px] items-center gap-x-6 py-4 border-b border-[#E6DFF2] last:border-b-0 ${
                  !item.is_valid ? 'bg-red-50/30' : ''
                }`}
              >
                <div className="w-[72px] h-[72px] rounded-[10px] bg-[#F9F7FC] border border-[#E6DFF2] flex items-center justify-center text-[#B9A6DE] overflow-hidden">
                  {item.product_image ? (
                    <img src={item.product_image} alt={item.product_name} className="w-full h-full object-cover" />
                  ) : (
                    <Package className="w-[26px] h-[26px]" />
                  )}
                </div>
                <div className="min-w-0">
                  <div className="text-[0.88rem] font-bold text-[#2A1B4D] leading-snug">{item.product_name}</div>
                  <div className="text-[0.76rem] text-[#6E6570] mt-1">{item.seller_name}</div>
                  <div className="inline-block text-[0.72rem] text-[#4B2E7E] mt-1 bg-[#F9F7FC] border border-[#E6DFF2] rounded px-1.5 py-0.5">
                    SKU: {item.product_sku}
                  </div>
                  <div className={`text-[0.72rem] mt-1.5 font-medium ${
                    !item.is_valid ? 'text-red-600' : item.max_quantity < 5 ? 'text-orange-600' : 'text-green-600'
                  }`}>
                    {item.is_valid ? `${item.max_quantity} in stock` : item.validation_issue}
                  </div>
                </div>
                <div className="text-right whitespace-nowrap">
                  <span className="text-[0.88rem] font-extrabold text-[#2A1B4D]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                    {peso(item.unit_price)}
                  </span>
                  {item.is_on_sale && (
                    <span className="block text-[0.7rem] text-[#6E6570] line-through mt-0.5">
                      {peso(item.regular_price)}
                    </span>
                  )}
                </div>
                <div className="flex justify-center">
                  <div className="inline-flex items-center border-[1.5px] border-[#E6DFF2] rounded-lg overflow-hidden shadow-sm">
                    <button
                      onClick={() => updateQuantity(item.id, item.quantity - 1)}
                      disabled={item.quantity <= 1 || updating === item.id}
                      className="w-[30px] h-[30px] flex items-center justify-center text-[#2A1B4D] text-base font-bold bg-white hover:bg-[#F3EFFA] disabled:text-[#E6DFF2] disabled:cursor-not-allowed transition"
                    >
                      −
                    </button>
                    <span className="w-[32px] text-center text-[0.85rem] font-bold border-l border-r border-[#E6DFF2]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                      {item.quantity}
                    </span>
                    <button
                      onClick={() => updateQuantity(item.id, item.quantity + 1)}
                      disabled={item.quantity >= item.max_quantity || updating === item.id}
                      className="w-[30px] h-[30px] flex items-center justify-center text-[#2A1B4D] text-base font-bold bg-white hover:bg-[#F3EFFA] disabled:text-[#E6DFF2] disabled:cursor-not-allowed transition"
                    >
                      +
                    </button>
                  </div>
                </div>
                <div className="text-right whitespace-nowrap text-[0.92rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                  {peso(item.line_total)}
                </div>
                <div className="flex justify-center">
                  <button
                    onClick={() => removeItem(item.id)}
                    className="w-[34px] h-[34px] border-[1.5px] border-[#E6DFF2] bg-white rounded-lg flex items-center justify-center text-[#6E6570] hover:bg-[#fbeced] hover:border-[#b3384f] hover:text-[#b3384f] transition shadow-sm"
                  >
                    <X className="w-[15px] h-[15px]" />
                  </button>
                </div>
              </div>
            ))
          )}
        </div>

        {/* Checkout summary */}
        {!isEmpty && (
          <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-6">
            <div className="flex items-center justify-between gap-6 flex-wrap">
              <div>
                <div className="text-[0.83rem] text-[#6E6570] mb-2">
                  Total (<span style={{ fontFamily: "'Poppins', sans-serif" }}>{items.length}</span> items):
                </div>
                <div className="text-[1.8rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                  {peso(cart?.total || 0)}
                </div>
              </div>
              <button
                disabled={isEmpty || cart?.has_invalid_items}
                onClick={handleCheckout}
                className="appearance-none border-none cursor-pointer bg-gradient-to-r from-[#2A1B4D] to-[#4B2E7E] text-white font-extrabold text-[0.92rem] px-9 py-3 rounded-full flex items-center gap-2 shadow-[0_4px_18px_rgba(75,46,126,0.45)] transition-all hover:translate-y-[-2px] hover:shadow-[0_8px_28px_rgba(75,46,126,0.55)] disabled:bg-[#E6DFF2] disabled:text-[#6E6570] disabled:cursor-not-allowed disabled:shadow-none disabled:transform-none whitespace-nowrap"
              >
                Proceed to Checkout
              </button>
            </div>
          </div>
        )}

        {/* You may also like */}
        <div className="mt-7">
          <div className="flex items-center justify-between mb-3.5">
            <h2 className="text-[1.05rem] font-bold" style={{ fontFamily: "'Syne', sans-serif" }}>You may also like</h2>
            <Link href="/homepage" className="inline-flex items-center gap-1 text-[0.82rem] font-bold text-[#4B2E7E] hover:underline">
              See All <ChevronRight className="w-[14px] h-[14px]" />
            </Link>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <div key={i} className="bg-white rounded-[14px] border border-[rgba(185,166,222,0.18)] shadow-sm overflow-hidden cursor-pointer transition hover:shadow-md hover:translate-y-[-2px] flex flex-col">
                <div className="relative w-full aspect-square bg-[#F9F7FC] flex items-center justify-center text-[#B9A6DE]">
                  <Package className="w-[34px] h-[34px]" />
                </div>
                <div className="p-2.5 flex-1 flex flex-col">
                  <div className="text-[0.78rem] text-[#2A1B4D] leading-snug line-clamp-2 min-h-[2.1em] mb-1.5">
                    Product Name {i}
                  </div>
                  <div className="flex items-center gap-1 text-[0.7rem] text-[#6E6570] mb-1">
                    <span className="text-[#f5a623]">★</span>4.8
                  </div>
                  <div className="flex items-baseline justify-between mt-auto gap-2">
                    <span className="font-extrabold text-[#4B2E7E] text-[0.9rem] whitespace-nowrap" style={{ fontFamily: "'Poppins', sans-serif" }}>
                      ₱249
                    </span>
                    <span className="text-[0.68rem] text-[#6E6570] whitespace-nowrap">1K+ sold</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );
}

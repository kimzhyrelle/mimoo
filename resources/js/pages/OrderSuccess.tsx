"use client";

import { Head, Link, router, usePage } from "@inertiajs/react";
import { CheckCircle, Package, MapPin, CreditCard } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface OrderItem {
  product_name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
}

interface Order {
  id: number;
  order_number: string;
  total: number;
  status: string;
  payment_method: string;
  shipping_name: string;
  shipping_address: string;
  shipping_phone: string;
  created_at: string;
  items: OrderItem[];
}

interface PageProps {
  order: Order;
  auth: { user: any };
  [key: string]: any;
}

// ---------------------------------------------------------------------------
// Order Success Page Component
// ---------------------------------------------------------------------------

export default function OrderSuccess() {
  const { order, auth } = usePage<PageProps>().props;
  const user = auth?.user;

  const peso = (n: number) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function handleLogout() {
    router.post('/logout');
  }

  return (
    <div className="min-h-screen bg-[#f5f4f8] font-[Syne]">
      <Head title="Order Placed" />

      <BuyerHeader user={user} onLogout={handleLogout} cartCount={0} />

      <main className="max-w-[800px] mx-auto px-6 py-8">
        {/* Success message */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-8 mb-5 text-center">
          <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-4">
            <CheckCircle className="w-12 h-12 text-green-600" />
          </div>
          <h1 className="text-[1.6rem] font-bold mb-2" style={{ fontFamily: "'Syne', sans-serif" }}>Order Placed Successfully!</h1>
          <p className="text-[0.92rem] text-[#6E6570] mb-4">Thank you for your purchase</p>
          <div className="inline-block bg-[#F9F7FC] border border-[#E6DFF2] rounded-lg px-6 py-3">
            <div className="text-[0.76rem] text-[#6E6570] mb-1">Order Number</div>
            <div className="text-[1.3rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
              {order.order_number}
            </div>
          </div>
        </div>

        {/* Order details */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-6 mb-5">
          <h2 className="text-[1.1rem] font-bold mb-4">Order Details</h2>

          <div className="space-y-4">
            {/* Items */}
            <div>
              <div className="flex items-center gap-2 text-[0.85rem] font-semibold text-[#2A1B4D] mb-2">
                <Package className="w-4 h-4" />
                Items Ordered
              </div>
              <div className="space-y-2">
                {order.items.map((item, idx) => (
                  <div key={idx} className="flex justify-between items-center p-3 bg-[#F9F7FC] rounded-lg">
                    <div className="flex-1">
                      <div className="text-[0.88rem] font-bold text-[#2A1B4D]">{item.product_name}</div>
                      <div className="text-[0.76rem] text-[#6E6570]">Qty: {item.quantity} × {peso(item.unit_price)}</div>
                    </div>
                    <div className="text-[0.9rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                      {peso(item.subtotal)}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Shipping */}
            <div>
              <div className="flex items-center gap-2 text-[0.85rem] font-semibold text-[#2A1B4D] mb-2">
                <MapPin className="w-4 h-4" />
                Shipping Address
              </div>
              <div className="p-3 bg-[#F9F7FC] rounded-lg">
                <div className="text-[0.88rem] font-bold text-[#2A1B4D]">{order.shipping_name}</div>
                <div className="text-[0.82rem] text-[#6E6570] mt-1">{order.shipping_address}</div>
                <div className="text-[0.82rem] text-[#6E6570]">{order.shipping_phone}</div>
              </div>
            </div>

            {/* Payment */}
            <div>
              <div className="flex items-center gap-2 text-[0.85rem] font-semibold text-[#2A1B4D] mb-2">
                <CreditCard className="w-4 h-4" />
                Payment Method
              </div>
              <div className="p-3 bg-[#F9F7FC] rounded-lg">
                <div className="text-[0.88rem] font-bold text-[#2A1B4D] uppercase">{order.payment_method.replace('_', ' ')}</div>
                <div className="text-[0.76rem] text-[#6E6570] mt-1">
                  {order.payment_method === 'cod' && 'Pay when you receive your order'}
                  {order.payment_method === 'gcash' && 'Digital payment via GCash'}
                  {order.payment_method === 'paymongo' && 'Credit/Debit card payment'}
                </div>
              </div>
            </div>

            {/* Total */}
            <div className="border-t border-[#E6DFF2] pt-4">
              <div className="flex justify-between items-center">
                <span className="text-[1.1rem] font-bold">Total Amount</span>
                <span className="text-[1.6rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                  {peso(order.total)}
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Actions */}
        <div className="flex flex-wrap gap-3 justify-center">
          <Link
            href="/orders"
            className="inline-flex items-center gap-2 bg-gradient-to-r from-[#2A1B4D] to-[#4B2E7E] text-white font-extrabold text-[0.88rem] px-6 py-3 rounded-full shadow-[0_4px_18px_rgba(75,46,126,0.45)] transition-all hover:translate-y-[-2px] hover:shadow-[0_8px_28px_rgba(75,46,126,0.55)]"
          >
            View My Orders
          </Link>
          <Link
            href="/homepage"
            className="inline-flex items-center gap-2 bg-white border-[1.5px] border-[#E6DFF2] text-[#4B2E7E] font-bold text-[0.88rem] px-6 py-3 rounded-full transition-all hover:bg-[#F9F7FC]"
          >
            Continue Shopping
          </Link>
        </div>
      </main>
    </div>
  );
}

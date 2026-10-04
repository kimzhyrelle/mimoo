"use client";

import { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { ArrowLeft, Package, X, ChevronRight } from "lucide-react";
import { BuyerHeader } from "@/components/buyer-header";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type UserRole = "guest" | "buyer" | "seller";

interface CartItem {
  id: number;
  checked: boolean;
  name: string;
  seller: string;
  variation: string;
  unitPrice: number;
  qty: number;
  stock: number;
}

interface Address {
  name: string;
  phone: string;
  addr: string;
}

interface Voucher {
  code: string;
  amount: number;
  type?: string;
}

// ---------------------------------------------------------------------------
// Cart Page Component
// ---------------------------------------------------------------------------

export default function Cart() {
  const { auth } = usePage<{ auth: { user: any } }>().props;
  const user = auth?.user;
  const role: UserRole = user?.account_type ? (user.account_type as UserRole) : "guest";

  // State
  const [items, setItems] = useState<CartItem[]>([
    { id: 1, checked: true, name: 'Handwoven Rattan Basket (Medium)', seller: 'Reyes Craft Corner', variation: 'Color: Natural', unitPrice: 450, qty: 1, stock: 8 },
    { id: 2, checked: true, name: 'Organic Virgin Coconut Oil 250ml', seller: 'Tolentino Skin Studio', variation: 'Size: 250ml', unitPrice: 180, qty: 2, stock: 25 },
    { id: 3, checked: true, name: 'Dried Mango Strips 500g', seller: 'Cabrera Fresh Market', variation: 'Pack: 500g', unitPrice: 120, qty: 3, stock: 40 },
    { id: 4, checked: false, name: 'Macrame Wall Hanging', seller: 'Reyes Craft Corner', variation: 'Color: Cream', unitPrice: 650, qty: 1, stock: 3 },
  ]);

  const [address, setAddress] = useState<Address>({
    name: 'Miguel Angelo Cruz',
    phone: '0918 552 7734',
    addr: '3 Purok 5, Apokon, Tagum City, Davao del Norte',
  });

  const [voucher, setVoucher] = useState<Voucher | null>(null);
  const [voucherInput, setVoucherInput] = useState('');
  const [showVoucherModal, setShowVoucherModal] = useState(false);
  const [selectedVoucher, setSelectedVoucher] = useState<string | null>(null);

  const FREE_SHIP = 999;
  const SHIP_FEE = 58;

  const peso = (n: number) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  // Calculations
  const selectedItems = items.filter(i => i.checked);
  const subtotal = selectedItems.reduce((s, i) => s + i.unitPrice * i.qty, 0);
  const discount = voucher ? Math.min(voucher.amount, subtotal) : 0;
  const shipping = selectedItems.length === 0 ? 0 : (subtotal >= FREE_SHIP ? 0 : SHIP_FEE);
  const total = Math.max(subtotal - discount + shipping, 0);

  function handleLogout() {
    router.post('/logout');
  }

  function handleSelectAll(checked: boolean) {
    setItems(items.map(i => ({ ...i, checked })));
  }

  function handleItemCheck(id: number, checked: boolean) {
    setItems(items.map(i => i.id === id ? { ...i, checked } : i));
  }

  function handleQtyChange(id: number, delta: number) {
    setItems(items.map(i => {
      if (i.id !== id) return i;
      const newQty = Math.max(1, Math.min(i.stock, i.qty + delta));
      return { ...i, qty: newQty };
    }));
  }

  function handleRemoveItem(id: number) {
    setItems(items.filter(i => i.id !== id));
  }

  function handleRemoveSelected() {
    setItems(items.filter(i => !i.checked));
  }

  function applyVoucher() {
    const code = voucherInput.trim().toUpperCase();
    if (!code) return;
    if (code === 'MIMOO50') {
      setVoucher({ code, amount: 50 });
      setVoucherInput('');
    }
  }

  function removeVoucher() {
    setVoucher(null);
    setVoucherInput('');
  }

  const allChecked = items.length > 0 && items.every(i => i.checked);

  return (
    <div className="min-h-screen bg-[#f5f4f8] font-[Syne]">
      <Head title="My Cart" />

      <BuyerHeader
        user={user}
        onLogout={handleLogout}
        cartCount={items.length}
      />

      <main className="max-w-[1100px] mx-auto px-6 py-6 pb-16">
        {/* Delivery address bar */}
        <div className="bg-white rounded-[14px] border-l-4 border-[#4B2E7E] p-4 mb-4 shadow-sm flex items-center justify-between gap-4 flex-wrap">
          <div className="flex items-start gap-3">
            <svg className="w-[17px] h-[17px] text-[#4B2E7E] flex-none mt-1" viewBox="0 0 24 24" fill="none">
              <path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z" stroke="currentColor" strokeWidth="1.7" strokeLinejoin="round" />
              <circle cx="12" cy="9.5" r="2.4" stroke="currentColor" strokeWidth="1.5" />
            </svg>
            <div>
              <div className="text-[0.7rem] font-extrabold uppercase tracking-wider text-[#4B2E7E] mb-1">Deliver to</div>
              <div className="text-[0.84rem] text-[#2A1B4D]">
                <strong>{address.name}</strong> · {address.phone} — {address.addr}
              </div>
            </div>
          </div>
          <button className="text-[0.8rem] font-bold text-[#4B2E7E] hover:underline flex-none whitespace-nowrap">
            Change
          </button>
        </div>

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

        {/* Cart items card */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5 mb-4">
          {/* Column headers */}
          <div className="grid grid-cols-[22px_72px_1fr_110px_132px_110px_60px] items-center gap-x-6 pb-3 mb-1 border-b-[1.5px] border-[#E6DFF2] text-[0.72rem] font-bold text-[#6E6570] uppercase tracking-wide">
            <div className="flex items-center">
              <input
                type="checkbox"
                checked={allChecked}
                onChange={(e) => handleSelectAll(e.target.checked)}
                className="w-[17px] h-[17px] accent-[#4B2E7E] cursor-pointer"
              />
            </div>
            <span></span>
            <span>Product</span>
            <span className="text-right">Unit Price</span>
            <span className="text-center">Quantity</span>
            <span className="text-right">Total Price</span>
            <span className="text-center">Actions</span>
          </div>

          {/* Items */}
          {items.length === 0 ? (
            <div className="py-12 text-center text-[0.88rem] text-[#6E6570]">Your cart is empty.</div>
          ) : (
            items.map((item) => (
              <div
                key={item.id}
                className="grid grid-cols-[22px_72px_1fr_110px_132px_110px_60px] items-center gap-x-6 py-4 border-b border-[#E6DFF2] last:border-b-0"
              >
                <input
                  type="checkbox"
                  checked={item.checked}
                  onChange={(e) => handleItemCheck(item.id, e.target.checked)}
                  className="w-[17px] h-[17px] accent-[#4B2E7E] cursor-pointer"
                />
                <div className="w-[72px] h-[72px] rounded-[10px] bg-[#F9F7FC] border border-[#E6DFF2] flex items-center justify-center text-[#B9A6DE]">
                  <Package className="w-[26px] h-[26px]" />
                </div>
                <div className="min-w-0">
                  <div className="text-[0.88rem] font-bold text-[#2A1B4D] leading-snug">{item.name}</div>
                  <div className="text-[0.76rem] text-[#6E6570] mt-1">{item.seller}</div>
                  <div className="inline-block text-[0.72rem] text-[#4B2E7E] mt-1 bg-[#F9F7FC] border border-[#E6DFF2] rounded px-1.5 py-0.5">
                    {item.variation}
                  </div>
                  <div className={`text-[0.72rem] mt-1.5 ${item.stock < 5 ? 'text-[#b3384f]' : 'text-[#2c7a52]'}`}>
                    {item.stock} in stock
                  </div>
                </div>
                <div className="text-right whitespace-nowrap">
                  <span className="text-[0.88rem] font-extrabold text-[#2A1B4D]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                    {peso(item.unitPrice)}
                  </span>
                  <span className="block text-[0.7rem] text-[#6E6570] mt-0.5">each</span>
                </div>
                <div className="flex justify-center">
                  <div className="inline-flex items-center border-[1.5px] border-[#E6DFF2] rounded-lg overflow-hidden shadow-sm">
                    <button
                      onClick={() => handleQtyChange(item.id, -1)}
                      disabled={item.qty <= 1}
                      className="w-[30px] h-[30px] flex items-center justify-center text-[#2A1B4D] text-base font-bold bg-white hover:bg-[#F3EFFA] disabled:text-[#E6DFF2] disabled:cursor-not-allowed transition"
                    >
                      −
                    </button>
                    <span className="w-[32px] text-center text-[0.85rem] font-bold border-l border-r border-[#E6DFF2]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                      {item.qty}
                    </span>
                    <button
                      onClick={() => handleQtyChange(item.id, 1)}
                      disabled={item.qty >= item.stock}
                      className="w-[30px] h-[30px] flex items-center justify-center text-[#2A1B4D] text-base font-bold bg-white hover:bg-[#F3EFFA] disabled:text-[#E6DFF2] disabled:cursor-not-allowed transition"
                    >
                      +
                    </button>
                  </div>
                </div>
                <div className="text-right whitespace-nowrap text-[0.92rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                  {peso(item.unitPrice * item.qty)}
                </div>
                <div className="flex justify-center">
                  <button
                    onClick={() => handleRemoveItem(item.id)}
                    className="w-[34px] h-[34px] border-[1.5px] border-[#E6DFF2] bg-white rounded-lg flex items-center justify-center text-[#6E6570] hover:bg-[#fbeced] hover:border-[#b3384f] hover:text-[#b3384f] transition shadow-sm"
                  >
                    <X className="w-[15px] h-[15px]" />
                  </button>
                </div>
              </div>
            ))
          )}
        </div>

        {/* Platform voucher + checkout bar */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] overflow-hidden mb-4">
          {/* Platform voucher */}
          <div>
            <div className="flex items-center justify-end px-6 py-2 border-b border-[#f0eef5] gap-6">
              <div className="flex items-center gap-2 text-[0.83rem] text-[#2A1B4D] mr-auto">
                <span className="inline-flex items-center justify-center w-[26px] h-[18px] border-2 border-dashed border-[#4B2E7E] rounded text-[0.6rem] font-extrabold text-[#4B2E7E]">
                  🎫
                </span>
                <span>Platform Voucher</span>
              </div>
              <button
                onClick={() => setShowVoucherModal(true)}
                className="text-[0.82rem] font-bold text-[#4B2E7E] hover:underline whitespace-nowrap cursor-pointer"
              >
                {selectedVoucher || 'Select or enter code'}
              </button>
            </div>
            <div className="flex items-center justify-end px-6 py-2 gap-6">
              <div className="flex items-center gap-2 text-[0.83rem] text-[#aaa] mr-auto">
                <input type="checkbox" disabled className="w-[15px] h-[15px] accent-[#ccc] cursor-not-allowed opacity-50" />
                <span className="w-5 h-5 rounded-full bg-[#ddd] flex items-center justify-center text-[0.6rem] font-extrabold text-[#aaa]">
                  🪙
                </span>
                <span>Mimoo Coins</span>
                <span className="text-[0.8rem]">Insufficient Coin Balance ⓘ</span>
              </div>
              <span className="text-[0.8rem] text-[#aaa]">−₱0</span>
            </div>
          </div>

          {/* Bottom bar */}
          <div className="border-t-[1.5px] border-[#E6DFF2] px-6 py-3.5">
            <div className="flex items-center justify-between gap-4 flex-wrap">
              <div className="flex items-center gap-6 flex-wrap">
                <label className="flex items-center gap-2.5 text-[0.85rem] font-bold text-[#2A1B4D] cursor-pointer">
                  <input
                    type="checkbox"
                    checked={allChecked}
                    onChange={(e) => handleSelectAll(e.target.checked)}
                    className="w-[17px] h-[17px] accent-[#4B2E7E] cursor-pointer"
                  />
                  Select All
                </label>
                <button
                  onClick={handleRemoveSelected}
                  disabled={!items.some(i => i.checked)}
                  className="text-[0.8rem] font-bold text-[#b3384f] disabled:text-[#6E6570] disabled:cursor-not-allowed hover:underline p-1"
                >
                  Delete
                </button>
                <button className="text-[0.8rem] font-bold text-[#4B2E7E] hover:underline p-1">
                  Move to My Likes
                </button>
              </div>
              <div className="flex items-center gap-6 flex-wrap">
                <div>
                  <div className="text-[0.83rem] text-[#6E6570]">
                    Total (<span style={{ fontFamily: "'Poppins', sans-serif" }}>{selectedItems.length}</span> items):
                  </div>
                  {discount > 0 && (
                    <div className="text-[0.75rem] text-[#2c7a52] mt-0.5">Saved {peso(discount)}</div>
                  )}
                </div>
                <div className="text-[1.35rem] font-extrabold text-[#4B2E7E]" style={{ fontFamily: "'Poppins', sans-serif" }}>
                  {peso(total)}
                </div>
                <button
                  disabled={selectedItems.length === 0}
                  className="appearance-none border-none cursor-pointer bg-gradient-to-r from-[#2A1B4D] to-[#4B2E7E] text-white font-extrabold text-[0.92rem] px-9 py-3 rounded-full flex items-center gap-2 shadow-[0_4px_18px_rgba(75,46,126,0.45)] transition-all hover:translate-y-[-2px] hover:shadow-[0_8px_28px_rgba(75,46,126,0.55)] disabled:bg-[#E6DFF2] disabled:text-[#6E6570] disabled:cursor-not-allowed disabled:shadow-none disabled:transform-none whitespace-nowrap"
                >
                  Check Out
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Voucher card */}
        <div className="bg-white rounded-[14px] shadow-sm border border-[rgba(185,166,222,0.18)] p-5 mb-4">
          <h2 className="text-[0.95rem] font-bold mb-3" style={{ fontFamily: "'Syne', sans-serif" }}>Apply voucher</h2>
          <div className="flex gap-2.5">
            <input
              type="text"
              value={voucherInput}
              onChange={(e) => setVoucherInput(e.target.value)}
              placeholder="Enter voucher code"
              className="flex-1 border-[1.5px] border-[#E6DFF2] rounded-lg px-3.5 py-2.5 text-[0.82rem] text-[#2A1B4D] transition bg-[#F9F7FC] focus:border-[#4B2E7E] focus:outline-none focus:bg-white shadow-sm"
            />
            <button
              onClick={applyVoucher}
              className="appearance-none border-2 border-[#4B2E7E] bg-white text-[#4B2E7E] font-extrabold text-[0.82rem] px-5 py-2.5 rounded-lg cursor-pointer whitespace-nowrap transition hover:bg-[#4B2E7E] hover:text-white shadow-sm"
            >
              Apply
            </button>
          </div>
          {voucher && (
            <div className="flex items-center justify-between mt-3 px-3.5 py-2.5 bg-[#e7f5ec] rounded-lg text-[0.8rem] text-[#2c7a52] font-bold">
              <span>{voucher.code} applied — {peso(voucher.amount)} off</span>
              <button onClick={removeVoucher} className="underline text-[0.76rem]">Remove</button>
            </div>
          )}
        </div>

        {/* You may also like */}
        <div className="mt-7">
          <div className="flex items-center justify-between mb-3.5">
            <h2 className="text-[1.05rem] font-bold" style={{ fontFamily: "'Syne', sans-serif" }}>You may also like</h2>
            <button className="inline-flex items-center gap-1 text-[0.82rem] font-bold text-[#4B2E7E] hover:underline">
              See All <ChevronRight className="w-[14px] h-[14px]" />
            </button>
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

      {/* Voucher Modal */}
      {showVoucherModal && (
        <>
          <div
            className="fixed inset-0 bg-black/45 z-[200] flex items-center justify-center"
            onClick={() => setShowVoucherModal(false)}
          />
          <div className="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-[200] bg-white rounded-xl w-[92%] max-w-[480px] max-h-[88vh] flex flex-col shadow-[0_20px_60px_rgba(0,0,0,0.22)]">
            {/* Modal header */}
            <div className="flex items-center justify-between px-5 py-4 border-b border-[#f0eef5]">
              <h3 className="text-base font-bold" style={{ fontFamily: "'Syne', sans-serif" }}>Select Mimoo Voucher</h3>
              <button className="text-[0.78rem] text-[#aaa] hover:underline">Voucher Help ⓘ</button>
            </div>

            {/* Add voucher row */}
            <div className="flex items-center gap-0 px-5 py-3.5 border-b border-[#f0eef5]">
              <span className="text-[0.82rem] font-semibold text-[#2A1B4D] whitespace-nowrap pr-3 mr-3 border-r border-[#e0dcea]">
                Add Voucher
              </span>
              <input
                type="text"
                placeholder="Mimoo voucher code"
                className="flex-1 border-none outline-none text-[0.84rem] text-[#2A1B4D] bg-transparent placeholder:text-[#bbb]"
              />
              <button className="appearance-none border-none bg-none text-[0.82rem] font-bold text-[#ccc] cursor-pointer px-2 py-1 transition uppercase tracking-wider">
                APPLY
              </button>
            </div>

            {/* Modal body */}
            <div className="flex-1 overflow-y-auto px-5 py-3.5">
              <div className="text-[0.78rem] font-extrabold text-[#2A1B4D] mb-1">Free Shipping</div>
              <div className="text-[0.75rem] text-[#888] mb-3.5">1 voucher can be selected</div>

              {/* Voucher item (disabled) */}
              <div className="relative flex items-stretch border-[1.5px] border-[#e0dcea] rounded-lg mb-3 overflow-hidden opacity-65 cursor-not-allowed">
                <div className="w-[90px] min-h-[80px] flex-none bg-gradient-to-br from-[#4ecdc4] to-[#26a69a] flex flex-col items-center justify-center text-white text-[0.62rem] font-extrabold gap-1">
                  <span className="text-2xl">🚚</span>
                  <span className="bg-white text-[#26a69a] text-[0.65rem] font-extrabold px-2 py-0.5 rounded">FREE</span>
                </div>
                <div className="flex-1 px-3.5 py-3 border-l border-dashed border-[#e0dcea]">
                  <div className="text-[0.85rem] font-bold text-[#2A1B4D] mb-1">Shipping Discount up to ₱200 Off</div>
                  <div className="text-[0.76rem] text-[#777] mb-0.5">Min. Spend ₱0</div>
                  <div className="text-[0.73rem] text-[#aaa]">Valid Till: 27.02.2027</div>
                </div>
                <div className="flex items-center justify-center px-3.5">
                  <input type="radio" name="voucher" disabled className="w-[18px] h-[18px] accent-[#4B2E7E]" />
                </div>
              </div>

              <div className="flex items-center gap-2 bg-[#fff8e6] border border-[#ffe082] rounded px-3 py-2 text-[0.75rem] text-[#b8860b] mb-3">
                <span>⚠️</span>
                <span>Current device does not meet voucher T&C</span>
              </div>

              {/* Voucher item (active) */}
              <div
                className={`relative flex items-stretch border-[1.5px] rounded-lg mb-3 overflow-hidden cursor-pointer transition ${
                  selectedVoucher === 'MIMOO50' ? 'border-[#4B2E7E] bg-[#F9F7FC]' : 'border-[#e0dcea] hover:border-[#4B2E7E]'
                }`}
                onClick={() => setSelectedVoucher('MIMOO50')}
              >
                <div className="w-[90px] min-h-[80px] flex-none bg-gradient-to-br from-[#4B2E7E] to-[#2A1B4D] flex flex-col items-center justify-center text-white text-[0.62rem] font-extrabold gap-1">
                  <span className="text-2xl">🏷️</span>
                  <span className="bg-white text-[#4B2E7E] text-[0.65rem] font-extrabold px-2 py-0.5 rounded">50 OFF</span>
                </div>
                <div className="flex-1 px-3.5 py-3 border-l border-dashed border-[#e0dcea]">
                  <div className="text-[0.85rem] font-bold text-[#2A1B4D] mb-1">₱50 Off on Your Order</div>
                  <div className="text-[0.76rem] text-[#777] mb-0.5">Min. Spend ₱300</div>
                  <div className="text-[0.73rem] text-[#aaa]">Valid Till: 31.12.2025</div>
                </div>
                <div className="flex items-center justify-center px-3.5">
                  <input
                    type="radio"
                    name="voucher"
                    checked={selectedVoucher === 'MIMOO50'}
                    onChange={() => setSelectedVoucher('MIMOO50')}
                    className="w-[18px] h-[18px] accent-[#4B2E7E] cursor-pointer"
                  />
                </div>
              </div>
            </div>

            {/* Modal footer */}
            <div className="flex items-center gap-3 px-5 py-3.5 border-t border-[#f0eef5]">
              <button
                onClick={() => {
                  setShowVoucherModal(false);
                  setSelectedVoucher(null);
                }}
                className="flex-1 appearance-none border-[1.5px] border-[#ccc] bg-white text-[#2A1B4D] font-bold text-[0.88rem] px-4 py-3 rounded cursor-pointer uppercase tracking-wider transition hover:bg-[#f5f4f8]"
              >
                CANCEL
              </button>
              <button
                onClick={() => {
                  if (selectedVoucher === 'MIMOO50') {
                    setVoucher({ code: 'MIMOO50', amount: 50 });
                  }
                  setShowVoucherModal(false);
                }}
                className="flex-1 appearance-none border-none bg-gradient-to-br from-[#2A1B4D] to-[#4B2E7E] text-white font-bold text-[0.88rem] px-4 py-3 rounded cursor-pointer uppercase tracking-wider shadow-[0_4px_14px_rgba(75,46,126,0.4)] transition hover:translate-y-[-1px] hover:shadow-[0_7px_20px_rgba(75,46,126,0.5)]"
              >
                OK
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
}

import { useRef } from 'react';
import { X, Printer, Download } from 'lucide-react';

interface WaybillData {
  number: string;
  order_number: string;
  generated_at: string | null;
  printed_at: string | null;
  status: string;
  payment_method: string;
  shipping: {
    name: string;
    phone: string;
    address: string;
  };
  items: Array<{
    name: string;
    quantity: number;
    variant: any;
  }>;
  total_amount: string;
  item_count: number;
  total_quantity: number;
}

interface WaybillPreviewProps {
  waybill: WaybillData;
  onClose: () => void;
  onPrint: () => void;
}

export default function WaybillPreview({ waybill, onClose, onPrint }: WaybillPreviewProps) {
  const printRef = useRef<HTMLDivElement>(null);

  const handlePrint = () => {
    window.print();
    onPrint();
  };

  const handleDownload = () => {
    // For now, trigger print which allows "Save as PDF"
    // In the future, you can implement actual PDF generation
    handlePrint();
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-auto">
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b print:hidden">
          <h2 className="text-xl font-bold">Waybill Preview</h2>
          <div className="flex items-center gap-2">
            <button
              onClick={handleDownload}
              className="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
            >
              <Download size={18} />
              Download PDF
            </button>
            <button
              onClick={handlePrint}
              className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
            >
              <Printer size={18} />
              Print
            </button>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-lg transition"
            >
              <X size={20} />
            </button>
          </div>
        </div>

        {/* Waybill Content */}
        <div ref={printRef} className="p-8 print:p-12">
          {/* Header */}
          <div className="border-b-4 border-black pb-4 mb-6">
            <div className="flex justify-between items-start">
              <div>
                <h1 className="text-3xl font-bold">MIMOO</h1>
                <p className="text-sm text-gray-600 mt-1">Shipping Waybill</p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold">{waybill.number}</div>
                <div className="text-sm text-gray-600 mt-1">
                  Order: {waybill.order_number}
                </div>
              </div>
            </div>
          </div>

          {/* QR Code and Barcode Section */}
          <div className="flex justify-between items-center mb-6 pb-6 border-b-2">
            <div className="text-center">
              <img
                src={`/waybill/${waybill.number}/qr`}
                alt="QR Code"
                className="w-32 h-32 mx-auto"
              />
              <p className="text-xs text-gray-600 mt-2">Scan to Track</p>
            </div>
            <div className="flex-1 text-center px-8">
              <svg
                className="w-full h-20"
                style={{ maxWidth: '400px', margin: '0 auto' }}
              >
                {/* Simple barcode representation using SVG */}
                {waybill.number.split('').map((char, idx) => {
                  const width = char.match(/[0-9]/) ? 6 : 4;
                  return (
                    <rect
                      key={idx}
                      x={idx * 8}
                      y="0"
                      width={width}
                      height="60"
                      fill="black"
                    />
                  );
                })}
              </svg>
              <p className="text-xs font-mono mt-2">{waybill.number}</p>
            </div>
          </div>

          {/* Shipping Information */}
          <div className="grid grid-cols-2 gap-6 mb-6">
            {/* Recipient */}
            <div className="border-2 border-gray-300 p-4 rounded">
              <h3 className="font-bold text-lg mb-3 text-gray-700">DELIVER TO:</h3>
              <div className="space-y-2">
                <div>
                  <p className="text-xs text-gray-600">Name</p>
                  <p className="font-semibold">{waybill.shipping.name}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-600">Phone</p>
                  <p className="font-semibold">{waybill.shipping.phone}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-600">Address</p>
                  <p className="font-semibold">{waybill.shipping.address}</p>
                </div>
              </div>
            </div>

            {/* Package Info */}
            <div className="border-2 border-gray-300 p-4 rounded">
              <h3 className="font-bold text-lg mb-3 text-gray-700">PACKAGE INFO:</h3>
              <div className="space-y-2">
                <div>
                  <p className="text-xs text-gray-600">Payment Method</p>
                  <p className="font-semibold uppercase">{waybill.payment_method}</p>
                </div>
                <div>
                  <p className="text-xs text-gray-600">Total Items</p>
                  <p className="font-semibold">{waybill.item_count} ({waybill.total_quantity} pcs)</p>
                </div>
                <div>
                  <p className="text-xs text-gray-600">
                    {/cod|cash/i.test(waybill.payment_method) ? 'Amount to Collect (COD)' : 'Total Amount (Paid)'}
                  </p>
                  <p className="font-bold text-lg">₱{waybill.total_amount}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Items List */}
          <div className="mb-6">
            <h3 className="font-bold text-lg mb-3 border-b pb-2">ITEMS:</h3>
            <div className="space-y-2">
              {waybill.items.map((item, idx) => (
                <div key={idx} className="flex justify-between items-start py-2 border-b">
                  <div className="flex-1">
                    <p className="font-medium">{item.name}</p>
                    {item.variant && (
                      <p className="text-sm text-gray-600">
                        {Object.entries(item.variant)
                          .map(([key, value]) => `${key}: ${value}`)
                          .join(', ')}
                      </p>
                    )}
                  </div>
                  <div className="text-right">
                    <p className="font-semibold">x{item.quantity}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* COD / PAID Badge */}
          <div className={`mb-6 p-4 rounded-lg text-center ${
            /cod|cash/i.test(waybill.payment_method) 
              ? 'bg-yellow-100 border-2 border-yellow-500' 
              : 'bg-green-100 border-2 border-green-500'
          }`}>
            <p className={`text-sm font-semibold ${
              /cod|cash/i.test(waybill.payment_method) ? 'text-yellow-800' : 'text-green-800'
            }`}>
              {/cod|cash/i.test(waybill.payment_method) ? 'PAYMENT METHOD' : 'PAYMENT STATUS'}
            </p>
            <p className={`text-2xl font-bold mt-1 ${
              /cod|cash/i.test(waybill.payment_method) ? 'text-yellow-900' : 'text-green-900'
            }`}>
              {/cod|cash/i.test(waybill.payment_method) 
                ? `COLLECT ₱${waybill.total_amount}` 
                : `PAID - ${waybill.payment_method.toUpperCase()}`
              }
            </p>
            {/cod|cash/i.test(waybill.payment_method) && (
              <p className="text-xs text-yellow-700 mt-1">Cash on Delivery - Collect payment upon delivery</p>
            )}
          </div>

          {/* Signature Section */}
          <div className="grid grid-cols-2 gap-6 mt-8 pt-6 border-t-2">
            <div>
              <p className="text-sm text-gray-600 mb-8">Courier Signature</p>
              <div className="border-t border-black pt-1">
                <p className="text-xs text-gray-500">Date & Time</p>
              </div>
            </div>
            <div>
              <p className="text-sm text-gray-600 mb-8">Recipient Signature</p>
              <div className="border-t border-black pt-1">
                <p className="text-xs text-gray-500">Date & Time</p>
              </div>
            </div>
          </div>

          {/* Footer */}
          <div className="mt-8 pt-4 border-t text-center text-xs text-gray-500">
            <p>Generated: {waybill.generated_at || 'N/A'}</p>
            <p className="mt-1">MIMOO Logistics - Keep this waybill until delivery is complete</p>
          </div>
        </div>
      </div>

      {/* Print Styles */}
      <style>{`
        @media print {
          body * {
            visibility: hidden;
          }
          ${printRef.current ? `
            #${printRef.current.id}, 
            #${printRef.current.id} * {
              visibility: visible;
            }
          ` : ''}
          .print\\:hidden {
            display: none !important;
          }
          @page {
            size: A4;
            margin: 15mm;
          }
        }
      `}</style>
    </div>
  );
}

import { Head, Link } from '@inertiajs/react';
import { Package } from 'lucide-react';

export default function CheckoutPlaceholder() {
    return (
        <div className="min-h-screen bg-neutral-50 flex items-center justify-center p-4">
            <Head title="Checkout - Coming Soon" />
            
            <div className="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
                <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-violet-100 mb-6">
                    <Package className="w-10 h-10 text-violet-600" />
                </div>
                
                <h1 className="text-2xl font-bold text-neutral-900 mb-3">
                    Checkout Coming Soon
                </h1>
                
                <p className="text-neutral-600 mb-8">
                    The checkout and payment process will be available in Step 4. 
                    Your cart has been saved and is ready for checkout.
                </p>
                
                <div className="flex gap-3">
                    <Link
                        href="/cart"
                        className="flex-1 px-6 py-3 rounded-lg border-2 border-violet-600 text-violet-600 font-semibold hover:bg-violet-50 transition"
                    >
                        Back to Cart
                    </Link>
                    
                    <Link
                        href="/homepage"
                        className="flex-1 px-6 py-3 rounded-lg bg-violet-600 text-white font-semibold hover:bg-violet-700 transition"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </div>
        </div>
    );
}

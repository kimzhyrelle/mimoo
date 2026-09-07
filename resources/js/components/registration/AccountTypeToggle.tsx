import { ShoppingBag, Briefcase } from 'lucide-react';

const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

type Props = {
    value: 'buyer' | 'seller';
    onChange: (type: 'buyer' | 'seller') => void;
};

export default function AccountTypeToggle({ value, onChange }: Props) {
    return (
        <div className="relative flex bg-[#EFEAF7] rounded-full p-1 mb-6" style={fontSFCompact}>
            {/* Sliding white pill */}
            <div
                className={`absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] rounded-full bg-white border-2 border-purple-700 shadow-sm transition-transform duration-300 ease-out ${
                    value === 'seller' ? 'translate-x-[calc(100%+8px)]' : 'translate-x-0'
                }`}
            />

            <button
                type="button"
                onClick={() => onChange('buyer')}
                className={`relative z-10 flex-1 flex items-center justify-center gap-2 rounded-full py-2.5 font-semibold transition-colors duration-300 ${
                    value === 'buyer' ? 'text-gray-900' : 'text-gray-400'
                }`}
            >
                <ShoppingBag className="w-4 h-4" />
                Buyer
            </button>
            <button
                type="button"
                onClick={() => onChange('seller')}
                className={`relative z-10 flex-1 flex items-center justify-center gap-2 rounded-full py-2.5 font-semibold transition-colors duration-300 ${
                    value === 'seller' ? 'text-gray-900' : 'text-gray-400'
                }`}
            >
                <Briefcase className="w-4 h-4" />
                Seller
            </button>
        </div>
    );
}
import { useRef } from 'react';
import { ImageIcon, FileText } from 'lucide-react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

const lineOfBusinessOptions = [
    'Food & Beverage',
    'Fashion & Apparel',
    'Electronics',
    'Health & Beauty',
    'Home & Living',
    'Services',
    'Others',
];

type Props = {
    data: any;
    setData: (field: string, value: any) => void;
    errors: any;
    accountType: 'buyer' | 'seller';
    onBack: () => void;
    onNext: () => void;
};

export default function StepVerification({ data, setData, errors, accountType, onBack, onNext }: Props) {
    const idInputRef = useRef<HTMLInputElement>(null);
    const permitInputRef = useRef<HTMLInputElement>(null);

    const handleIdChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setData('id_document', e.target.files?.[0] ?? null);
    };

    const handlePermitChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setData('business_permit', e.target.files?.[0] ?? null);
    };

    return (
        <>
            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-2 tracking-wider" style={fontSyncopate}>
                Step 3 of 4 • {accountType === 'seller' ? 'Store & identity verification' : 'Identity verification'}
            </p>

            {accountType === 'seller' && (
                <>
                    <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-4 tracking-wider" style={fontSFCompact}>
                        Store details
                    </p>

                    <div style={fontSFCompact}>
                        <label className="text-sm font-semibold text-gray-900">
                            Business name<span className="text-red-500 ml-0.5">*</span>
                        </label>
                        <input
                            type="text"
                            placeholder="e.g Hoppers Pet Supply"
                            value={data.business_name}
                            onChange={(e) => setData('business_name', e.target.value)}
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                        />
                        {errors.business_name && <p className="text-red-500 text-xs mt-1">{errors.business_name}</p>}
                    </div>

                    <div className="mt-4" style={fontSFCompact}>
                        <label className="text-sm font-semibold text-gray-900">
                            Line of business<span className="text-red-500 ml-0.5">*</span>
                        </label>
                        <select
                            value={data.line_of_business}
                            onChange={(e) => setData('line_of_business', e.target.value)}
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 bg-white"
                        >
                            <option value="">Select a category</option>
                            {lineOfBusinessOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                        {errors.line_of_business && (
                            <p className="text-red-500 text-xs mt-1">{errors.line_of_business}</p>
                        )}
                    </div>

                    <div className="mt-4" style={fontSFCompact}>
                        <label className="text-sm font-semibold text-gray-900">
                            Business permit<span className="text-red-500 ml-0.5">*</span>
                        </label>
                        <div className="flex items-center gap-4 border border-gray-300 rounded-xl px-4 py-3 mt-1 bg-white">
                            <div className="w-9 h-9 rounded-lg bg-[#EFEAF7] flex items-center justify-center shrink-0">
                                <FileText className="w-4 h-4 text-purple-700" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-sm text-gray-700 truncate">
                                    {data.business_permit ? data.business_permit.name : 'No file selected'}
                                </p>
                                <p className="text-xs text-gray-400">PDF, JPG or PNG • up to 5MB</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => permitInputRef.current?.click()}
                                className="shrink-0 border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 transition"
                            >
                                Choose permit file
                            </button>
                            <input
                                ref={permitInputRef}
                                type="file"
                                accept="image/*,application/pdf"
                                onChange={handlePermitChange}
                                className="hidden"
                            />
                        </div>
                        {errors.business_permit && (
                            <p className="text-red-500 text-xs mt-1">{errors.business_permit}</p>
                        )}
                    </div>
                </>
            )}

            <p className={`text-xs font-bold text-[#6E5F8F] uppercase mb-4 tracking-wider ${accountType === 'seller' ? 'mt-6' : ''}`} style={fontSFCompact}>
                Identity verification
            </p>

            <div style={fontSFCompact}>
                <label className="text-sm font-semibold text-gray-900">
                    Upload a valid ID<span className="text-red-500 ml-0.5">*</span>
                </label>
                <div className="flex items-center gap-4 border border-gray-300 rounded-xl px-4 py-3 mt-1 bg-white">
                    <div className="w-9 h-9 rounded-lg bg-[#EFEAF7] flex items-center justify-center shrink-0">
                        <ImageIcon className="w-4 h-4 text-purple-700" />
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-sm text-gray-700 truncate">
                            {data.id_document ? data.id_document.name : 'No file selected'}
                        </p>
                        <p className="text-xs text-gray-400">Government-issue ID • up to 5MB</p>
                    </div>
                    <button
                        type="button"
                        onClick={() => idInputRef.current?.click()}
                        className="shrink-0 border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 transition"
                    >
                        Choose ID file
                    </button>
                    <input
                        ref={idInputRef}
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={handleIdChange}
                        className="hidden"
                    />
                </div>
                {errors.id_document && <p className="text-red-500 text-xs mt-1">{errors.id_document}</p>}
            </div>

            <div className="flex gap-3 mt-8" style={fontSFCompact}>
                <button
                    type="button"
                    onClick={onBack}
                    className="px-8 border border-gray-300 rounded-xl py-3 font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                    Back
                </button>
                <button
                    type="button"
                    onClick={onNext}
                    className="flex-1 bg-[#3B1F6B] text-white rounded-xl py-3 font-medium hover:bg-[#2E1854] transition"
                >
                    Next
                </button>
            </div>
        </>
    );
}
const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

type Props = {
    data: any;
    setData: (field: string, value: any) => void;
    errors: any;
    onBack: () => void;
    onNext: () => void;
};

export default function StepAddress({ data, setData, errors, onBack, onNext }: Props) {
    return (
        <>
            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-2 tracking-wider" style={fontSyncopate}>
                Step 2 of 4 • Address
            </p>

            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-3 tracking-wider" style={fontSFCompact}>
                Address
            </p>

            <div className="grid grid-cols-3 gap-4" style={fontSFCompact}>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Province<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        placeholder="Select province"
                        value={data.province}
                        onChange={(e) => setData('province', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                    {errors.province && <p className="text-red-500 text-xs mt-1">{errors.province}</p>}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Municipality / City<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        placeholder={data.province ? 'Enter municipality/city' : 'Select province first'}
                        disabled={!data.province}
                        value={data.municipality_city}
                        onChange={(e) => setData('municipality_city', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    />
                    {errors.municipality_city && (
                        <p className="text-red-500 text-xs mt-1">{errors.municipality_city}</p>
                    )}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Barangay<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        placeholder={data.municipality_city ? 'Enter barangay' : 'Select municipality first'}
                        disabled={!data.municipality_city}
                        value={data.barangay}
                        onChange={(e) => setData('barangay', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300 disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                    />
                    {errors.barangay && <p className="text-red-500 text-xs mt-1">{errors.barangay}</p>}
                </div>
            </div>

            <div className="mt-4" style={fontSFCompact}>
                <label className="text-sm font-semibold text-gray-900">
                    Street & house number<span className="text-red-500 ml-0.5">*</span>
                </label>
                <input
                    type="text"
                    placeholder="e.g 123 Mabini St."
                    value={data.street_address}
                    onChange={(e) => setData('street_address', e.target.value)}
                    className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                />
                {errors.street_address && <p className="text-red-500 text-xs mt-1">{errors.street_address}</p>}
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
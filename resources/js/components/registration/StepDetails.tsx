import AccountTypeToggle from './AccountTypeToggle';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

type Props = {
    data: any;
    setData: (field: string, value: any) => void;
    errors: any;
    accountType: 'buyer' | 'seller';
    setAccountType: (type: 'buyer' | 'seller') => void;
    age: number | null;
};

export default function StepDetails({ data, setData, errors, accountType, setAccountType, age }: Props) {
    return (
        <>
            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-2 tracking-wider" style={fontSyncopate}>
                Step 1 of 4 • Your details
            </p>
            <p className="text-sm text-gray-700 mb-3">I'm signing up as a</p>

            <AccountTypeToggle
                value={accountType}
                onChange={(type) => {
                    setAccountType(type);
                    setData('account_type', type);
                }}
            />

            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-3 tracking-wider" style={fontSFCompact}>
                Personal details
            </p>

            <div className="grid grid-cols-2 gap-4" style={fontSFCompact}>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        First name<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        value={data.first_name}
                        onChange={(e) => setData('first_name', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                    {errors.first_name && <p className="text-red-500 text-xs mt-1">{errors.first_name}</p>}
                </div>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Last name<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        value={data.last_name}
                        onChange={(e) => setData('last_name', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                    {errors.last_name && <p className="text-red-500 text-xs mt-1">{errors.last_name}</p>}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Middle initial<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        placeholder="e.g D"
                        maxLength={1}
                        value={data.middle_initial}
                        onChange={(e) => setData('middle_initial', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                </div>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Sex<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <div className="flex gap-4 mt-3">
                        <label className="flex items-center gap-2 text-sm font-medium text-gray-800">
                            <input
                                type="radio"
                                name="sex"
                                checked={data.sex === 'male'}
                                onChange={() => setData('sex', 'male')}
                                className="accent-purple-800"
                            />
                            Male
                        </label>
                        <label className="flex items-center gap-2 text-sm font-medium text-gray-800">
                            <input
                                type="radio"
                                name="sex"
                                checked={data.sex === 'female'}
                                onChange={() => setData('sex', 'female')}
                                className="accent-purple-800"
                            />
                            Female
                        </label>
                    </div>
                </div>
            </div>

            <p className="text-xs font-bold text-[#6E5F8F] uppercase mt-6 mb-3 tracking-wider" style={fontSFCompact}>
                Contact & birthday
            </p>

            <div style={fontSFCompact}>
                <label className="text-sm font-semibold text-gray-900">
                    Email adress<span className="text-red-500 ml-0.5">*</span>
                </label>
                <input
                    type="email"
                    placeholder="you@example.com"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                />
                {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
            </div>

            <div className="grid grid-cols-2 gap-4 mt-4" style={fontSFCompact}>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Contact number<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="text"
                        placeholder="09XXXXXXXXX"
                        value={data.contact_number}
                        onChange={(e) => setData('contact_number', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                    <p className="text-xs text-gray-400 mt-1">11-digit mobile number e.g 09985637297</p>
                </div>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Birthday<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <input
                        type="date"
                        value={data.birthday}
                        onChange={(e) => setData('birthday', e.target.value)}
                        className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                    />
                </div>
            </div>

            <div className="mt-4" style={fontSFCompact}>
                <label className="text-sm font-semibold text-gray-900">Age</label>
                <input
                    type="text"
                    disabled
                    placeholder="Auto-filled"
                    value={age ?? ''}
                    className="w-full border border-gray-300 rounded-xl px-3 py-2.5 mt-1 bg-gray-50 text-gray-400 cursor-not-allowed"
                />
                <p className="text-xs text-gray-400 mt-1">Calculated from your birthday</p>
            </div>
        </>
    );
}
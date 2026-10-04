import { useState } from 'react';
import { Eye, EyeOff, Mail } from 'lucide-react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

type Props = {
    data: any;
    setData: (field: string, value: any) => void;
    errors: any;
    processing: boolean;
    onBack: () => void;
    onSubmit: () => void;
};

export default function StepSecurity({ data, setData, errors, processing, onBack, onSubmit }: Props) {
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);

    return (
        <>
            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-2 tracking-wider" style={fontSyncopate}>
                Step 4 of 4 • Account security
            </p>

            <p className="text-xs font-bold text-[#6E5F8F] uppercase mb-4 tracking-wider" style={fontSFCompact}>
                Account security
            </p>

            <div className="grid grid-cols-2 gap-4" style={fontSFCompact}>
                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Password<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <div className="relative mt-1">
                        <input
                            type={showPassword ? 'text' : 'password'}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 pr-10 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword((v) => !v)}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                    </div>
                    <p className="text-xs text-gray-400 mt-1">At least 8 characters</p>
                    {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
                </div>

                <div>
                    <label className="text-sm font-semibold text-gray-900">
                        Confirm Password<span className="text-red-500 ml-0.5">*</span>
                    </label>
                    <div className="relative mt-1">
                        <input
                            type={showConfirm ? 'text' : 'password'}
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className="w-full border border-gray-300 rounded-xl px-3 py-2.5 pr-10 text-[#8B72A8] focus:outline-none focus:ring-2 focus:ring-purple-300"
                        />
                        <button
                            type="button"
                            onClick={() => setShowConfirm((v) => !v)}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            {showConfirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                    </div>
                    {errors.password_confirmation && (
                        <p className="text-red-500 text-xs mt-1">{errors.password_confirmation}</p>
                    )}
                </div>
            </div>

            <div className="flex items-start gap-3 bg-[#EFEAF7] rounded-xl px-4 py-3 mt-6" style={fontSFCompact}>
                <Mail className="w-4 h-4 text-purple-700 mt-0.5 shrink-0" />
                <p className="text-sm text-gray-700">
                    After submitting your registration, please wait for the administrator's approval, which
                    will be sent to your email.
                </p>
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
                    onClick={onSubmit}
                    disabled={processing}
                    className="flex-1 bg-[#3B1F6B] text-white rounded-xl py-3 font-medium hover:bg-[#2E1854] transition"
                >
                    Create account
                </button>
            </div>
        </>
    );
}
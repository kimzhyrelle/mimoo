import { Link, useForm } from '@inertiajs/react';
import { FormEvent, ReactNode, useState } from 'react';
import StepProgress from '../components/registration/StepProgress';
import StepDetails from '../components/registration/StepDetails';
import StepAddress from '../components/registration/StepAddress';
import StepVerification from '../components/registration/StepVerification';
import StepSecurity from '../components/registration/StepSecurity';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontPoppins = { fontFamily: 'Poppins, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

const TOTAL_STEPS = 4;

export default function Registration() {
    const [step, setStep] = useState(1);
    const [accountType, setAccountType] = useState<'buyer' | 'seller'>('buyer');

    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        middle_initial: '',
        sex: '',
        email: '',
        contact_number: '',
        birthday: '',
        account_type: 'buyer',
        province: '',
        municipality_city: '',
        barangay: '',
        street_address: '',
        id_document: null as File | null,
        business_name: '',
        line_of_business: '',
        business_permit: null as File | null,
        password: '',
        password_confirmation: '',
    });

    const age = data.birthday
        ? Math.floor(
              (Date.now() - new Date(data.birthday).getTime()) / (1000 * 60 * 60 * 24 * 365.25)
          )
        : null;

    const goNext = () => {
        if (step < TOTAL_STEPS) {
            setStep((s) => s + 1);
        } else {
            post('/register');
        }
    };

    const goBack = () => {
        if (step > 1) setStep((s) => s - 1);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        goNext();
    };

    return (
        <div className="flex h-screen overflow-hidden" style={fontPoppins}>
            <style>{`
                @keyframes badgePop {
                    0%   { opacity: 0; transform: scale(0.7) translateY(-4px); }
                    60%  { opacity: 1; transform: scale(1.08); }
                    100% { opacity: 1; transform: scale(1); }
                }
                .badge-pop {
                    animation: badgePop 0.35s ease-out;
                }
                @keyframes waterFillText {
                    0%   { clip-path: inset(0 0 100% 0); }
                    100% { clip-path: inset(0 0 0 0); }
                }
                .water-fill-text {
                    animation: waterFillText 0.5s ease-out forwards;
                }
                @keyframes waterFillDot {
                    0%   { clip-path: inset(0 0 100% 0); }
                    100% { clip-path: inset(0 0 0 0); }
                }
                .water-fill-dot {
                    animation: waterFillDot 0.5s ease-out forwards;
                }
            `}</style>

            {/* Sidebar - fixed, hindi scrollable */}
            <div className="w-72 h-screen sticky top-0 bg-gradient-to-b from-[#6E5F8F] to-[#19132A] text-white p-8 flex flex-col justify-between shrink-0">
                <div>
                    <div className="flex items-center gap-2">
                        <span className="w-6 h-6 rounded-full bg-purple-300" />
                        <h1 className="text-lg font-bold tracking-widest" style={fontSyncopate}>
                            mimoo
                        </h1>
                    </div>

                    <div className="mt-6">
                        <span
                            key={step}
                            className="badge-pop text-xs bg-white/10 px-3 py-1 rounded-full inline-block tracking-wide"
                            style={fontSFCompact}
                        >
                            STEP {step} OF {TOTAL_STEPS}
                        </span>
                    </div>

                    <h2 className="mt-8 text-2xl font-semibold leading-snug">
                        Join as a buyer or start selling — your call.
                    </h2>
                    <p className="mt-3 text-sm text-white/60">
                        Create a buyer account to start shopping right away.
                    </p>
                </div>

                <div>
                    <p className="text-xs uppercase text-white/50 mb-3 tracking-wide" style={fontSFCompact}>
                        Your registration, step by step
                    </p>
                    <ul className="space-y-3 text-sm">
                        {[
                            'Add your name, contact info and birthday',
                            'Set your province, city, and street address',
                            'Upload a valid ID for verification',
                            'Set a password to secure your account',
                        ].map((label, i) => {
                            const stepNumber = i + 1;
                            const isDone = stepNumber <= step;
                            return (
                                <li key={label} className="flex items-center gap-2">
                                    <span className="relative w-4 h-4 rounded-full flex items-center justify-center shrink-0 border border-white/40 overflow-hidden">
                                        {/* base fill (white circle) always rendered, revealed via water clip when done */}
                                        <span
                                            className={`absolute inset-0 rounded-full bg-white ${
                                                isDone ? '' : 'invisible'
                                            }`}
                                            style={isDone ? undefined : { clipPath: 'inset(100% 0 0 0)' }}
                                        />
                                        {isDone && (
                                            <span
                                                key={`circle-${label}`}
                                                className="water-fill-dot absolute inset-0 rounded-full bg-white"
                                            />
                                        )}
                                        {isDone && (
                                            <span className="relative w-2 h-2 rounded-full bg-[#4C2A85]" />
                                        )}
                                    </span>

                                    <span className="relative inline-block">
                                        {/* dim base text, always visible */}
                                        <span className="text-white/40">{label}</span>
                                        {/* bold bright overlay, water-fills in when done */}
                                        {isDone && (
                                            <span
                                                key={`label-${label}`}
                                                className="water-fill-text absolute inset-0 text-white"
                                            >
                                                {label}
                                            </span>
                                        )}
                                    </span>
                                </li>
                            );
                        })}
                    </ul>
                    <div className="border-t border-white/10 mt-5 pt-4">
                        <p className="text-xs text-white/30">
                            Your information is only used to verify and set up your account.
                        </p>
                    </div>
                </div>
            </div>

            {/* Main content - ito lang ang mag-s-scroll */}
            <div className="flex-1 h-screen overflow-y-auto bg-[#F8F6FC] p-12">
                <h2 className="text-2xl font-bold text-gray-900" style={fontSyncopate}>
                    create your account
                </h2>
                <p className="text-gray-500 mt-1 text-sm">
                    It only takes a few minutes. Choose the account type that fits you.
                </p>

                <StepProgress currentStep={step} />

                <form onSubmit={handleSubmit} className="mt-6 max-w-2xl">
                    {step === 1 && (
                        <StepDetails
                            data={data}
                            setData={setData}
                            errors={errors}
                            accountType={accountType}
                            setAccountType={setAccountType}
                            age={age}
                        />
                    )}

                    {step === 2 && (
                        <StepAddress
                            data={data}
                            setData={setData}
                            errors={errors}
                            onBack={goBack}
                            onNext={goNext}
                        />
                    )}

                    {step === 3 && (
                        <StepVerification
                            data={data}
                            setData={setData}
                            errors={errors}
                            accountType={accountType}
                            onBack={goBack}
                            onNext={goNext}
                        />
                    )}

                    {step === 4 && (
                        <StepSecurity
                            data={data}
                            setData={setData}
                            errors={errors}
                            processing={processing}
                            onBack={goBack}
                            onSubmit={goNext}
                        />
                    )}

                    {/* Step 1 has its own Continue button below since it has no Back */}
                    {step === 1 && (
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-[#3B1F6B] text-white rounded-xl py-3 mt-8 font-medium hover:bg-[#2E1854] transition"
                            style={fontSFCompact}
                        >
                            Continue
                        </button>
                    )}

                    <p className="text-center text-sm text-gray-500 mt-4">
                        Already have an account?{' '}
                        <Link href="/login" className="text-purple-800 font-medium">
                            Log in
                        </Link>
                    </p>
                </form>
            </div>
        </div>
    );
}

Registration.layout = (page: ReactNode) => page;
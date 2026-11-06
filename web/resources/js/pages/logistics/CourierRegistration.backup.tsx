import { Link, useForm } from '@inertiajs/react';
import { FormEvent, ReactNode, useEffect, useRef, useState } from 'react';
import StepProgress from '../../components/registration/StepProgress';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontCalibri = { fontFamily: 'Calibri, sans-serif' };
const fontPoppins = { fontFamily: 'Poppins, sans-serif' };
const fontSFCompact = { fontFamily: "'SF Compact', -apple-system, BlinkMacSystemFont, sans-serif" };

const TOTAL_STEPS = 4;
const MAX_FILE_MB = 5;
const PH_MOBILE_RE = /^09\d{9}$/;
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const NCR_NAME = "National Capital Region (NCR)";

const NCR_DISTRICTS = [
    '1st District (Manila)',
    '2nd District (Marikina, Pasig, Quezon City - East)',
    '3rd District (Caloocan, Malabon, Navotas, Valenzuela)',
    '4th District (Las Piñas, Muntinlupa, Parañaque, Pasay, Taguig)',
];

const SIDEBAR_STEPS = [
    'Company information and contact details',
    'Authorized representative information',
    'Business verification documents',
    'Service coverage and operations',
];

const VEHICLE_TYPES = [
    'Motorcycle',
    'Bicycle',
    'E-bike',
    'Tricycle',
    'Car',
    'Van',
];

// PSGC API Types
interface Region { code: string; name: string; }
interface Province { code: string; name: string; regionCode: string }
interface CityMunicipality { code: string; name: string; provinceCode: string }
interface Barangay { code: string; name: string; cityCode: string }

type FormShape = {
    // Step 1: Company Information
    company_name: string;
    trade_name: string;
    business_registration_number: string;
    business_address: string;
    business_email: string;
    company_contact_number: string;
    company_website: string;
    company_logo: File | null;
    
    // Step 2: Authorized Representative
    rep_full_name: string;
    rep_position: string;
    rep_email: string;
    rep_contact_number: string;
    username: string;
    password: string;
    password_confirmation: string;
    
    // Step 3: Business Documents
    dti_sec_certificate: File | null;
    mayors_permit: File | null;
    operating_permits: File | null;
    rep_valid_id: File | null;
    proof_of_address: File | null;
    
    // Step 4: Service Coverage
    regions_served: string;
    provinces_served: string;
    cities_served: string;
    delivery_services: string;
    pickup_available: boolean;
    estimated_delivery_time: string;
    
    privacy_policy_accepted: boolean;
    account_type: string;
};

type FieldName = keyof FormShape;
type Errs = Partial<Record<FieldName, string>>;

const FIELD_STEP: Partial<Record<FieldName, number>> = {
    first_name: 1, last_name: 1, middle_initial: 1, sex: 1, birthday: 1, email: 1, contact_number: 1,
    region: 2, province: 2, district: 2, municipality_city: 2, barangay: 2, street_address: 2,
    vehicle_type: 3, plate_number: 3, or_cr_document: 3,
    drivers_license: 4, password: 4, password_confirmation: 4,
};

const FIELD_LABEL: Partial<Record<FieldName, string>> = {
    first_name: 'First Name',
    last_name: 'Last Name',
    middle_initial: 'Middle Initial',
    sex: 'Sex',
    birthday: 'Birthday',
    email: 'Email',
    contact_number: 'Contact Number',
    region: 'Region',
    province: 'Province',
    district: 'District',
    municipality_city: 'City / Municipality',
    barangay: 'Barangay',
    street_address: 'Street Address (House number, street name, etc.)',
    vehicle_type: 'Vehicle Type',
    plate_number: 'Plate Number',
    or_cr_document: 'OR/CR Document',
    drivers_license: 'ID / Driver\'s License',
    password: 'Password',
    password_confirmation: 'Confirm Password',
    privacy_policy_accepted: 'Privacy Policy',
};

function calculateAge(birthday: string): number {
    if (!birthday) return 0;
    const birthDate = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function requiredFileError(f: File | null): string | undefined {
    if (!f) return 'This file is required.';
    if (!/\.(pdf|jpe?g|png)$/i.test(f.name)) return 'Use a PDF, JPG or PNG file.';
    if (f.size > MAX_FILE_MB * 1024 * 1024) return `File is too large. Max ${MAX_FILE_MB}MB.`;
    return undefined;
}

function validate(step: number, d: FormShape): Errs {
    const e: Errs = {};
    if (step === 1) {
        if (!d.first_name.trim()) e.first_name = 'Enter first name.';
        if (!d.last_name.trim()) e.last_name = 'Enter last name.';
        if (!d.sex) e.sex = 'Select sex.';
        if (!d.birthday) e.birthday = 'Enter birthday.';
        else {
            const age = calculateAge(d.birthday);
            if (age < 18) e.birthday = 'You must be at least 18 years old.';
        }
        if (!EMAIL_RE.test(d.email.trim())) e.email = 'Enter a valid email address.';
        if (!PH_MOBILE_RE.test(d.contact_number.trim())) e.contact_number = 'Enter a valid PH mobile number (09XXXXXXXXX).';
    }
    if (step === 2) {
        const isNCR = d.region === NCR_NAME;
        if (!d.region) e.region = 'Select a region.';
        if (!isNCR && !d.province) e.province = 'Select a province.';
        if (isNCR && !d.district) e.district = 'Select a district.';
        if (!d.municipality_city) e.municipality_city = 'Select a city or municipality.';
        if (!d.barangay) e.barangay = 'Select a barangay.';
        if (!d.street_address.trim()) e.street_address = 'Enter street address.';
    }
    if (step === 3) {
        if (!d.vehicle_type) e.vehicle_type = 'Select a vehicle type.';
        if (!d.plate_number.trim()) e.plate_number = 'Enter plate number.';
        const orErr = requiredFileError(d.or_cr_document);
        if (orErr) e.or_cr_document = orErr;
    }
    if (step === 4) {
        const idErr = requiredFileError(d.drivers_license);
        if (idErr) e.drivers_license = idErr;
        if (d.password.length < 8) e.password = 'Use at least 8 characters.';
        const hasUpper = /[A-Z]/.test(d.password);
        const hasLower = /[a-z]/.test(d.password);
        const hasNumber = /\d/.test(d.password);
        const hasSpecial = /[^A-Za-z0-9]/.test(d.password);
        if (!hasUpper || !hasLower || !hasNumber || !hasSpecial) {
            e.password = 'Password must include uppercase, lowercase, number, and special character.';
        }
        if (!d.password_confirmation || d.password_confirmation !== d.password)
            e.password_confirmation = "Passwords don't match.";
        if (!d.privacy_policy_accepted) e.privacy_policy_accepted = 'You must accept the Privacy Policy.';
    }
    return e;
}

const inputCls = (err?: string) =>
    `w-full rounded-xl border px-3.5 py-2.5 text-sm text-gray-900 bg-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/30 focus:border-[#3B1F6B] transition ${
        err ? 'border-red-400 bg-red-50' : 'border-[#DDD5EE]'
    }`;

function Field({
    label, htmlFor, required, error, hint, className = '', children,
}: {
    label: string; htmlFor: string; required?: boolean; error?: string; hint?: string;
    className?: string; children: ReactNode;
}) {
    return (
        <div className={`flex flex-col gap-1.5 min-w-0 ${className}`}>
            <label htmlFor={htmlFor} className="text-sm font-medium text-gray-800">
                {label} {required && <span className="text-red-600" aria-hidden="true">*</span>}
            </label>
            {children}
            {hint && !error && <span id={`${htmlFor}-hint`} className="text-xs text-gray-500">{hint}</span>}
            {error && (
                <span id={`${htmlFor}-err`} className="text-xs font-medium text-red-600" role="alert">
                    {error}
                </span>
            )}
        </div>
    );
}

function PasswordInput({
    id, value, onChange, error, autoComplete,
}: { id: string; value: string; onChange: (v: string) => void; error?: string; autoComplete: string }) {
    const [show, setShow] = useState(false);
    const hasUpper = /[A-Z]/.test(value);
    const hasLower = /[a-z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSpecial = /[^A-Za-z0-9]/.test(value);
    const hasLength = value.length >= 8;

    return (
        <div>
            <div className="relative">
                <input
                    id={id}
                    type={show ? 'text' : 'password'}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    autoComplete={autoComplete}
                    aria-invalid={!!error}
                    aria-describedby={error ? `${id}-err` : undefined}
                    className={`${inputCls(error)} pr-12`}
                />
                <button
                    type="button"
                    onClick={() => setShow((s) => !s)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500 hover:text-[#3B1F6B]"
                >
                    {show ? 'Hide' : 'Show'}
                </button>
            </div>
            {value && (
                <div className="mt-2 text-xs space-y-1">
                    <div className={hasLength ? 'text-green-600' : 'text-gray-500'}>✓ 8+ characters</div>
                    <div className={hasUpper ? 'text-green-600' : 'text-gray-500'}>✓ Uppercase</div>
                    <div className={hasLower ? 'text-green-600' : 'text-gray-500'}>✓ Lowercase</div>
                    <div className={hasNumber ? 'text-green-600' : 'text-gray-500'}>✓ Number</div>
                    <div className={hasSpecial ? 'text-green-600' : 'text-gray-500'}>✓ Special character</div>
                </div>
            )}
        </div>
    );
}

function FileField({
    id, label, hint, file, error, onPick, required,
}: {
    id: string; label: string; hint: string;
    file: File | null; error?: string; onPick: (f: File | null) => void; required?: boolean;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    return (
        <Field label={label} htmlFor={id} required={required} error={error}>
            <div className={`flex items-center gap-3 rounded-xl p-4 ${
                file ? 'border border-[#B4A7D6] bg-white' : 'border-2 border-dashed border-[#DDD5EE] bg-[#F8F6FC]'
            }`}>
                <span className="shrink-0 w-10 h-10 rounded-lg bg-[#F1EDFB] text-[#3B1F6B] flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" className="w-5 h-5" stroke="currentColor" strokeWidth="1.6">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" strokeLinejoin="round" />
                        <path d="M14 3v5h5" strokeLinejoin="round" />
                    </svg>
                </span>
                <span className="flex-1 min-w-0">
                    <span className="block text-sm font-medium text-gray-800 break-words">
                        {file ? file.name : 'No file selected'}
                    </span>
                    <span className="block text-xs text-gray-500">
                        {file ? `${(file.size / 1024 / 1024).toFixed(2)} MB` : hint}
                    </span>
                </span>
                <button
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    className="shrink-0 rounded-lg border border-[#DDD5EE] bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-800 hover:border-[#9B5DE5] hover:text-[#3B1F6B] transition"
                >
                    {file ? 'Change' : 'Choose file'}
                </button>
                <input
                    ref={inputRef}
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                    tabIndex={-1}
                    className="sr-only"
                    onChange={(e) => onPick(e.target.files?.[0] ?? null)}
                />
            </div>
        </Field>
    );
}

export default function CourierRegistration() {
    const [step, setStep] = useState(1);
    const [clientErrors, setClientErrors] = useState<Errs>({});
    const headingRef = useRef<HTMLHeadingElement>(null);

    // PSGC API states
    const [regions, setRegions] = useState<Region[]>([]);
    const [provinces, setProvinces] = useState<Province[]>([]);
    const [cities, setCities] = useState<CityMunicipality[]>([]);
    const [barangays, setBarangays] = useState<Barangay[]>([]);
    const [loading, setLoading] = useState({ regions: false, provinces: false, cities: false, barangays: false });

    const { data, setData, post, processing, errors } = useForm<FormShape>({
        first_name: '',
        last_name: '',
        middle_initial: '',
        sex: '',
        birthday: '',
        email: '',
        contact_number: '',
        region: '',
        province: '',
        district: '',
        municipality_city: '',
        barangay: '',
        street_address: '',
        vehicle_type: '',
        plate_number: '',
        or_cr_document: null,
        drivers_license: null,
        password: '',
        password_confirmation: '',
        privacy_policy_accepted: false,
        account_type: 'courier',
    });

    const allErrors: Errs = { ...(errors as Errs), ...clientErrors };
    const err = (f: FieldName) => allErrors[f];
    const summary = (Object.keys(clientErrors) as FieldName[]).filter((f) => clientErrors[f]);

    // Calculate age from birthday
    const age = calculateAge(data.birthday);

    // Check if selected region is NCR
    const isNCR = data.region.toUpperCase().includes('NCR') || data.region === NCR_NAME;

    // Load regions on mount
    useEffect(() => {
        setLoading((prev) => ({ ...prev, regions: true }));
        fetch('https://psgc.gitlab.io/api/regions/')
            .then((res) => res.json())
            .then((list: Region[]) => {
                setRegions(list.sort((a, b) => a.name.localeCompare(b.name)));
                setLoading((prev) => ({ ...prev, regions: false }));
            })
            .catch(() => setLoading((prev) => ({ ...prev, regions: false })));
    }, []);

    // Load provinces when region changes (skip for NCR)
    useEffect(() => {
        if (!data.region) {
            setProvinces([]);
            setCities([]);
            setBarangays([]);
            return;
        }

        const selected = regions.find((r) => r.name === data.region);
        if (!selected) return;

        if (isNCR) {
            // For NCR, load cities directly from region
            setProvinces([]);
            setData('province', 'N/A');
            setLoading((prev) => ({ ...prev, cities: true }));
            fetch(`https://psgc.gitlab.io/api/regions/${selected.code}/cities-municipalities/`)
                .then((res) => res.json())
                .then((list: CityMunicipality[]) => {
                    setCities(list.sort((a, b) => a.name.localeCompare(b.name)));
                    setLoading((prev) => ({ ...prev, cities: false }));
                })
                .catch(() => setLoading((prev) => ({ ...prev, cities: false })));
        } else {
            // For non-NCR, load provinces first
            setCities([]);
            setLoading((prev) => ({ ...prev, provinces: true }));
            fetch(`https://psgc.gitlab.io/api/regions/${selected.code}/provinces/`)
                .then((res) => res.json())
                .then((list: Province[]) => {
                    setProvinces(list.sort((a, b) => a.name.localeCompare(b.name)));
                    setLoading((prev) => ({ ...prev, provinces: false }));
                })
                .catch(() => setLoading((prev) => ({ ...prev, provinces: false })));
        }
    }, [data.region, regions]);

    // Load cities/municipalities when province changes (only for non-NCR)
    useEffect(() => {
        if (isNCR) return;

        if (data.province && data.province !== 'N/A') {
            const selected = provinces.find((p) => p.name === data.province);
            if (selected) {
                setLoading((prev) => ({ ...prev, cities: true }));
                fetch(`https://psgc.gitlab.io/api/provinces/${selected.code}/cities-municipalities/`)
                    .then((res) => res.json())
                    .then((list: CityMunicipality[]) => {
                        setCities(list.sort((a, b) => a.name.localeCompare(b.name)));
                        setLoading((prev) => ({ ...prev, cities: false }));
                    })
                    .catch(() => setLoading((prev) => ({ ...prev, cities: false })));
            }
        } else {
            setCities([]);
        }
    }, [data.province, provinces, isNCR]);

    // Load barangays when city/municipality changes
    useEffect(() => {
        if (data.municipality_city) {
            const selected = cities.find((c) => c.name === data.municipality_city);
            if (selected) {
                setLoading((prev) => ({ ...prev, barangays: true }));
                fetch(`https://psgc.gitlab.io/api/cities-municipalities/${selected.code}/barangays/`)
                    .then((res) => res.json())
                    .then((list: Barangay[]) => {
                        setBarangays(list.sort((a, b) => a.name.localeCompare(b.name)));
                        setLoading((prev) => ({ ...prev, barangays: false }));
                    })
                    .catch(() => setLoading((prev) => ({ ...prev, barangays: false })));
            }
        } else {
            setBarangays([]);
        }
    }, [data.municipality_city, cities]);

    // Return to step with first error
    useEffect(() => {
        const steps = (Object.keys(errors) as FieldName[]).map((f) => FIELD_STEP[f]).filter(Boolean) as number[];
        if (steps.length) setStep(Math.min(...steps));
    }, [errors]);

    const set = <K extends FieldName>(key: K, value: FormShape[K]) => {
        setData(key, value as any);
        if (clientErrors[key]) setClientErrors((c) => ({ ...c, [key]: undefined }));
    };

    const goTo = (n: number) => {
        setStep(n);
        setTimeout(() => headingRef.current?.focus(), 0);
    };

    const goNext = () => {
        const e = validate(step, data);
        if (Object.keys(e).length) {
            setClientErrors(e);
            return;
        }
        setClientErrors({});
        if (step < TOTAL_STEPS) goTo(step + 1);
        else {
            const email = data.email.trim();
            
            setTimeout(() => {
                post('/register', {
                    forceFormData: true,
                    onSuccess: () => {
                        window.location.href = `/verify-email?email=${encodeURIComponent(email)}`;
                    },
                    onError: (errors) => {
                        console.error('Registration failed:', errors);
                    },
                });
            }, 100);
        }
    };

    const goBack = () => {
        if (step > 1) {
            setClientErrors({});
            goTo(step - 1);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        goNext();
    };

    const focusField = (f: FieldName) => document.getElementById(f)?.focus();

    const stepTitles = ['Personal Information', 'Residential Address', 'Vehicle Information', 'Documents & Account'];

    return (
        <div className="flex flex-col lg:flex-row min-h-screen overflow-hidden" style={fontPoppins}>
            {/* Mobile header */}
            <div className="lg:hidden w-full bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <Link href="/" className="flex items-center gap-2">
                    <span className="w-5 h-5 rounded-full bg-[#3B1F6B]" />
                    <h1 className="text-base font-bold tracking-widest text-gray-900" style={fontSyncopate}>mimoo</h1>
                </Link>
                <span className="text-xs bg-[#F1EDFB] text-[#3B1F6B] px-3 py-1 rounded-full font-medium" style={fontSFCompact}>
                    STEP {step} OF {TOTAL_STEPS}
                </span>
            </div>

            {/* Desktop sidebar */}
            <aside
                className="hidden lg:flex lg:w-80 lg:h-screen lg:sticky lg:top-0 shrink-0 flex-col gap-9 px-8 pt-10 pb-10 text-[#F1EEFB]"
                style={{ background: 'radial-gradient(120% 140% at 15% 0%, #2B2249 0%, #201A33 60%)' }}
            >
                <div className="relative z-10">
                    <Link href="/" className="flex items-center gap-2">
                        <span className="w-6 h-6 rounded-full bg-[#9B5DE5]" />
                        <h1 className="text-lg font-bold tracking-widest" style={fontSyncopate}>mimoo</h1>
                    </Link>
                    <div className="mt-6">
                        <span
                            key={step}
                            className="text-xs bg-white/10 px-3 py-1 rounded-full inline-block"
                            style={fontSFCompact}
                        >
                            STEP {step} OF {TOTAL_STEPS}
                        </span>
                    </div>
                </div>

                <div className="relative z-10">
                    <h2 className="text-[1.7rem] leading-tight font-black mb-2.5" style={fontCalibri}>
                        Register as a Courier
                    </h2>
                    <p className="text-[0.95rem] text-[#CEC6E6] max-w-[34ch]">
                        Join our courier network. Your application will be reviewed by the logistics/sorting center.
                    </p>
                </div>

                <div className="relative z-10 mt-auto flex flex-col gap-4">
                    <p className="uppercase tracking-[0.08em] text-[0.7rem] font-bold text-[#B4A7D6]" style={fontSFCompact}>
                        Registration Steps
                    </p>
                    <ol className="flex flex-col gap-3.5">
                        {SIDEBAR_STEPS.map((label, i) => {
                            const isDone = i + 1 < step;
                            const isCurrent = i + 1 === step;
                            return (
                                <li key={label} className="flex items-start gap-3 text-[0.85rem]">
                                    <span className={`relative mt-0.5 w-5 h-5 shrink-0 rounded-full border-[1.5px] flex items-center justify-center ${
                                        isDone ? 'bg-[#B4A7D6] border-[#B4A7D6]' : 
                                        isCurrent ? 'border-white' : 'border-[#B4A7D6]'
                                    }`}>
                                        {isDone && <span className="w-1.5 h-1.5 rounded-full bg-[#201A33]" />}
                                        {isCurrent && <span className="w-1.5 h-1.5 rounded-full bg-white" />}
                                    </span>
                                    <span className={isDone || isCurrent ? 'text-white' : 'text-white/40'}>{label}</span>
                                </li>
                            );
                        })}
                    </ol>
                </div>

                <p className="relative z-10 border-t border-white/10 pt-4 text-[0.78rem] text-[#B4A7D6]">
                    After submitting, wait for the Logistic/Sorting Center's approval via email.
                </p>
            </aside>

            {/* Main content */}
            <div className="flex-1 min-h-screen lg:h-screen overflow-y-auto bg-[#F8F6FC] p-4 sm:p-6 md:p-8 lg:p-12 xl:p-16">
                <div className="max-w-3xl mx-auto lg:mx-0">
                    <h2 className="text-xl md:text-2xl font-bold text-gray-900" style={fontSyncopate}>
                        courier registration
                    </h2>
                    <p className="text-gray-500 mt-1 text-sm">
                        For delivery riders and drivers.
                    </p>
                    <p className="text-gray-500 mt-1 text-xs">
                        Fields marked <span className="text-red-600">*</span> are required.
                    </p>

                    <StepProgress currentStep={step} />

                    {/* Error summary */}
                    {summary.length > 0 && (
                        <div role="alert" className="mt-6 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3">
                            <h3 className="text-sm font-semibold mb-1">Please fix the following:</h3>
                            <ul className="list-disc pl-5 text-sm">
                                {summary.map((f) => (
                                    <li key={f}>
                                        <button type="button" onClick={() => focusField(f)} className="underline">
                                            {FIELD_LABEL[f]}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="mt-6" noValidate>
                        <h3
                            ref={headingRef}
                            tabIndex={-1}
                            className="text-xs font-semibold uppercase tracking-wide text-[#7A63AC] mb-4 focus:outline-none"
                            style={fontSFCompact}
                        >
                            Step {step} · {stepTitles[step - 1]}
                        </h3>

                        {/* STEP 1: Personal Information */}
                        {step === 1 && (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="First name" htmlFor="first_name" required error={err('first_name')}>
                                    <input
                                        id="first_name"
                                        type="text"
                                        value={data.first_name}
                                        onChange={(e) => set('first_name', e.target.value)}
                                        autoComplete="given-name"
                                        className={inputCls(err('first_name'))}
                                    />
                                </Field>

                                <Field label="Last name" htmlFor="last_name" required error={err('last_name')}>
                                    <input
                                        id="last_name"
                                        type="text"
                                        value={data.last_name}
                                        onChange={(e) => set('last_name', e.target.value)}
                                        autoComplete="family-name"
                                        className={inputCls(err('last_name'))}
                                    />
                                </Field>

                                <Field label="Middle initial" htmlFor="middle_initial">
                                    <input
                                        id="middle_initial"
                                        type="text"
                                        maxLength={2}
                                        placeholder="e.g. D"
                                        value={data.middle_initial}
                                        onChange={(e) => set('middle_initial', e.target.value.toUpperCase())}
                                        className={inputCls()}
                                    />
                                </Field>

                                <div className="flex flex-col gap-1.5">
                                    <span id="sex-label" className="text-sm font-medium text-gray-800">
                                        Sex <span className="text-red-600" aria-hidden="true">*</span>
                                    </span>
                                    <div role="radiogroup" aria-labelledby="sex-label" className="flex gap-5 py-2.5">
                                        {['male', 'female'].map((v, i) => (
                                            <label key={v} className="flex items-center gap-2 text-sm capitalize text-gray-800 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    id={i === 0 ? 'sex' : undefined}
                                                    name="sex"
                                                    value={v}
                                                    checked={data.sex === v}
                                                    onChange={() => set('sex', v)}
                                                    className="w-4 h-4 accent-[#3B1F6B] cursor-pointer"
                                                />
                                                {v}
                                            </label>
                                        ))}
                                    </div>
                                    {err('sex') && <span className="text-xs font-medium text-red-600" role="alert">{err('sex')}</span>}
                                </div>

                                <Field label="Email address" htmlFor="email" required error={err('email')} className="sm:col-span-2">
                                    <input
                                        id="email"
                                        type="email"
                                        placeholder="you@example.com"
                                        value={data.email}
                                        onChange={(e) => set('email', e.target.value)}
                                        autoComplete="email"
                                        className={inputCls(err('email'))}
                                    />
                                </Field>

                                <Field label="Contact number" htmlFor="contact_number" required error={err('contact_number')} hint="09XXXXXXXXX or +639XXXXXXXXX">
                                    <input
                                        id="contact_number"
                                        type="tel"
                                        inputMode="numeric"
                                        placeholder="09XXXXXXXXX"
                                        maxLength={13}
                                        value={data.contact_number}
                                        onChange={(e) => set('contact_number', e.target.value.replace(/[^\d+]/g, ''))}
                                        autoComplete="tel"
                                        className={inputCls(err('contact_number'))}
                                    />
                                </Field>

                                <Field label="Birthday" htmlFor="birthday" required error={err('birthday')}>
                                    <input
                                        id="birthday"
                                        type="date"
                                        max={new Date().toISOString().split('T')[0]}
                                        value={data.birthday}
                                        onChange={(e) => set('birthday', e.target.value)}
                                        autoComplete="bday"
                                        className={inputCls(err('birthday'))}
                                    />
                                </Field>

                                <Field label="Age" htmlFor="age" hint="Calculated from your birthday">
                                    <input
                                        id="age"
                                        readOnly
                                        placeholder="Auto-filled"
                                        value={age ? `${age} years old` : ''}
                                        className={`${inputCls()} bg-[#F1EDFB] text-gray-500`}
                                    />
                                </Field>
                            </div>
                        )}

                        {/* STEP 2: Address */}
                        {step === 2 && (
                            <div className="space-y-5">
                                <Field label="Region" htmlFor="region" required error={err('region')}>
                                    <select
                                        id="region"
                                        value={data.region}
                                        onChange={(e) => {
                                            set('region', e.target.value);
                                            set('province', '');
                                            set('district', '');
                                            set('municipality_city', '');
                                            set('barangay', '');
                                        }}
                                        disabled={loading.regions}
                                        className={inputCls(err('region'))}
                                    >
                                        <option value="">Select region</option>
                                        {regions.map((r) => (
                                            <option key={r.code} value={r.name}>{r.name}</option>
                                        ))}
                                    </select>
                                </Field>

                                {isNCR ? (
                                    <Field label="District" htmlFor="district" required error={err('district')}>
                                        <select
                                            id="district"
                                            value={data.district}
                                            onChange={(e) => {
                                                set('district', e.target.value);
                                                set('municipality_city', '');
                                                set('barangay', '');
                                            }}
                                            className={inputCls(err('district'))}
                                        >
                                            <option value="">Select district</option>
                                            {NCR_DISTRICTS.map((d) => (
                                                <option key={d} value={d}>{d}</option>
                                            ))}
                                        </select>
                                    </Field>
                                ) : (
                                    <Field label="Province" htmlFor="province" required error={err('province')}>
                                        <select
                                            id="province"
                                            value={data.province}
                                            onChange={(e) => {
                                                set('province', e.target.value);
                                                set('municipality_city', '');
                                                set('barangay', '');
                                            }}
                                            disabled={loading.provinces || !data.region}
                                            className={inputCls(err('province'))}
                                        >
                                            <option value="">Select province</option>
                                            {provinces.map((p) => (
                                                <option key={p.code} value={p.name}>{p.name}</option>
                                            ))}
                                        </select>
                                    </Field>
                                )}

                                <Field label="City / Municipality" htmlFor="municipality_city" required error={err('municipality_city')}>
                                    <select
                                        id="municipality_city"
                                        value={data.municipality_city}
                                        onChange={(e) => {
                                            set('municipality_city', e.target.value);
                                            set('barangay', '');
                                        }}
                                        disabled={loading.cities || (!isNCR && !data.province) || (isNCR && !data.district)}
                                        className={inputCls(err('municipality_city'))}
                                    >
                                        <option value="">Select city/municipality</option>
                                        {cities.map((c) => (
                                            <option key={c.code} value={c.name}>{c.name}</option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Barangay" htmlFor="barangay" required error={err('barangay')}>
                                    <select
                                        id="barangay"
                                        value={data.barangay}
                                        onChange={(e) => set('barangay', e.target.value)}
                                        disabled={loading.barangays || !data.municipality_city}
                                        className={inputCls(err('barangay'))}
                                    >
                                        <option value="">Select barangay</option>
                                        {barangays.map((b) => (
                                            <option key={b.code} value={b.name}>{b.name}</option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Street Address" htmlFor="street_address" required error={err('street_address')} hint="House number, street name, subdivision, etc.">
                                    <input
                                        id="street_address"
                                        type="text"
                                        value={data.street_address}
                                        onChange={(e) => set('street_address', e.target.value)}
                                        className={inputCls(err('street_address'))}
                                        placeholder="e.g., 123 Main Street, Greenfield Subdivision"
                                    />
                                </Field>
                            </div>
                        )}

                        {/* STEP 3: Vehicle Information */}
                        {step === 3 && (
                            <div className="space-y-5">
                                <Field label="Choose Vehicle" htmlFor="vehicle_type" required error={err('vehicle_type')}>
                                    <select
                                        id="vehicle_type"
                                        value={data.vehicle_type}
                                        onChange={(e) => set('vehicle_type', e.target.value)}
                                        className={inputCls(err('vehicle_type'))}
                                    >
                                        <option value="">Select vehicle type</option>
                                        {VEHICLE_TYPES.map((v) => (
                                            <option key={v} value={v}>{v}</option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Enter Plate Number" htmlFor="plate_number" required error={err('plate_number')}>
                                    <input
                                        id="plate_number"
                                        type="text"
                                        value={data.plate_number}
                                        onChange={(e) => set('plate_number', e.target.value.toUpperCase())}
                                        className={inputCls(err('plate_number'))}
                                        placeholder="e.g., ABC 1234"
                                    />
                                </Field>

                                <FileField
                                    id="or_cr_document"
                                    label="Upload OR/CR"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.or_cr_document}
                                    error={err('or_cr_document')}
                                    onPick={(f) => set('or_cr_document', f)}
                                    required
                                />
                            </div>
                        )}

                        {/* STEP 4: Documents & Password */}
                        {step === 4 && (
                            <div className="space-y-5">
                                <FileField
                                    id="drivers_license"
                                    label="Upload ID / Driver's License"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.drivers_license}
                                    error={err('drivers_license')}
                                    onPick={(f) => set('drivers_license', f)}
                                    required
                                />

                                <Field label="Password" htmlFor="password" required error={err('password')}>
                                    <PasswordInput
                                        id="password"
                                        value={data.password}
                                        onChange={(v) => set('password', v)}
                                        error={err('password')}
                                        autoComplete="new-password"
                                    />
                                </Field>

                                <Field label="Confirm Password" htmlFor="password_confirmation" required error={err('password_confirmation')}>
                                    <PasswordInput
                                        id="password_confirmation"
                                        value={data.password_confirmation}
                                        onChange={(v) => set('password_confirmation', v)}
                                        error={err('password_confirmation')}
                                        autoComplete="new-password"
                                    />
                                </Field>

                                <div className="rounded-xl bg-[#F1EDFB] border border-[#DDD5EE] p-4">
                                    <label className="flex items-start gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={data.privacy_policy_accepted}
                                            onChange={(e) => set('privacy_policy_accepted', e.target.checked)}
                                            className="mt-0.5 rounded border-[#B4A7D6] text-[#3B1F6B] focus:ring-[#9B5DE5]"
                                        />
                                        <span className="text-sm text-gray-700">
                                            I accept the{' '}
                                            <Link href="/privacy-policy" target="_blank" className="text-[#3B1F6B] font-semibold hover:underline">
                                                Privacy Policy
                                            </Link>
                                            {' '}and understand that my application will be reviewed by the logistics/sorting center.
                                        </span>
                                    </label>
                                    {err('privacy_policy_accepted') && (
                                        <p className="text-xs font-medium text-red-600 mt-2">{err('privacy_policy_accepted')}</p>
                                    )}
                                </div>

                                <div className="rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                                    <p className="font-semibold mb-1">📧 After submission:</p>
                                    <p>Please wait for the Logistic/Sorting Center's approval, which will be sent to your email.</p>
                                </div>
                            </div>
                        )}

                        {/* Navigation */}
                        <div className="flex items-center gap-4 mt-8">
                            {step > 1 && (
                                <button
                                    type="button"
                                    onClick={goBack}
                                    disabled={processing}
                                    className="flex items-center gap-2 px-5 py-3 rounded-full text-sm font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50 transition"
                                >
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M19 12H5M12 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                            )}
                            <button
                                type="submit"
                                disabled={processing}
                                className="flex items-center gap-2 px-5 py-3 rounded-full bg-[#3B1F6B] text-white text-sm font-semibold hover:bg-[#2E1854] disabled:opacity-50 transition ml-auto"
                            >
                                {processing ? 'Submitting...' : step === TOTAL_STEPS ? 'Complete Registration' : 'Continue'}
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}

CourierRegistration.layout = (page: ReactNode) => page;

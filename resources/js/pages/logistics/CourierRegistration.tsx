import { Link, useForm } from '@inertiajs/react';
import { FormEvent, ReactNode, useEffect, useRef, useState } from 'react';
import StepProgress from '../../components/registration/StepProgress';
import MultiSelectChips from '../../components/registration/MultiSelectChips';
import ServiceChips from '../../components/registration/ServiceChips';
import RadioCards from '../../components/registration/RadioCards';
import phLocations from '../../data/ph-locations.json';

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
    pickup_available: string;
    estimated_delivery_time: string;
    
    privacy_policy_accepted: boolean;
    account_type: string;
};

type FieldName = keyof FormShape;
type Errs = Partial<Record<FieldName, string>>;

const FIELD_STEP: Partial<Record<FieldName, number>> = {
    company_name: 1, trade_name: 1, business_registration_number: 1, business_address: 1,
    business_email: 1, company_contact_number: 1, company_website: 1, company_logo: 1,
    rep_full_name: 2, rep_position: 2, rep_email: 2, rep_contact_number: 2,
    username: 2, password: 2, password_confirmation: 2,
    dti_sec_certificate: 3, mayors_permit: 3, operating_permits: 3, rep_valid_id: 3, proof_of_address: 3,
    regions_served: 4, provinces_served: 4, cities_served: 4, delivery_services: 4,
    pickup_available: 4, estimated_delivery_time: 4,
};

const FIELD_LABEL: Partial<Record<FieldName, string>> = {
    company_name: 'Company Name',
    trade_name: 'Trade Name',
    business_registration_number: 'Registration Number',
    business_address: 'Business Address',
    business_email: 'Business Email',
    company_contact_number: 'Contact Number',
    company_website: 'Website',
    company_logo: 'Company Logo',
    rep_full_name: 'Full Name',
    rep_position: 'Position',
    rep_email: 'Email Address',
    rep_contact_number: 'Contact Number',
    username: 'Username',
    password: 'Password',
    password_confirmation: 'Confirm Password',
    dti_sec_certificate: 'DTI/SEC Certificate',
    mayors_permit: "Mayor's Permit",
    operating_permits: 'Operating Permits',
    rep_valid_id: 'Valid ID',
    proof_of_address: 'Proof of Address',
    regions_served: 'Regions Served',
    provinces_served: 'Provinces Served',
    cities_served: 'Cities Served',
    delivery_services: 'Delivery Services',
    pickup_available: 'Pickup Available',
    estimated_delivery_time: 'Estimated Delivery Time',
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

function optionalFileError(f: File | null): string | undefined {
    if (!f) return undefined;
    if (!/\.(pdf|jpe?g|png)$/i.test(f.name)) return 'Use a PDF, JPG or PNG file.';
    if (f.size > MAX_FILE_MB * 1024 * 1024) return `File is too large. Max ${MAX_FILE_MB}MB.`;
    return undefined;
}

function requiredFileError(f: File | null): string | undefined {
    if (!f) return 'This file is required.';
    if (!/\.(pdf|jpe?g|png)$/i.test(f.name)) return 'Use a PDF, JPG or PNG file.';
    if (f.size > MAX_FILE_MB * 1024 * 1024) return `File is too large. Max ${MAX_FILE_MB}MB.`;
    return undefined;
}

function validate(step: number, d: FormShape): Errs {
    // TEMPORARILY DISABLE VALIDATION FOR TESTING
    return {};
    
    const e: Errs = {};
    if (step === 1) {
        if (!d.company_name.trim()) e.company_name = 'Enter company name.';
        if (!d.trade_name.trim()) e.trade_name = 'Enter trade name.';
        if (!d.business_registration_number.trim()) e.business_registration_number = 'Enter registration number.';
        if (!d.business_address.trim()) e.business_address = 'Enter business address.';
        if (!EMAIL_RE.test(d.business_email.trim())) e.business_email = 'Enter a valid email address.';
        if (!PH_MOBILE_RE.test(d.company_contact_number.trim())) e.company_contact_number = 'Enter a valid PH mobile number (09XXXXXXXXX).';
        const logoErr = optionalFileError(d.company_logo);
        if (logoErr) e.company_logo = logoErr;
    }
    if (step === 2) {
        if (!d.rep_full_name.trim()) e.rep_full_name = 'Enter full name.';
        if (!d.rep_position.trim()) e.rep_position = 'Enter position/designation.';
        if (!EMAIL_RE.test(d.rep_email.trim())) e.rep_email = 'Enter a valid email address.';
        if (!PH_MOBILE_RE.test(d.rep_contact_number.trim())) e.rep_contact_number = 'Enter a valid PH mobile number (09XXXXXXXXX).';
        if (!d.username.trim()) e.username = 'Enter username.';
        else if (d.username.length < 4) e.username = 'Username must be at least 4 characters.';
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
    }
    if (step === 3) {
        // Make files optional for testing - can be uploaded later
        const dtiErr = optionalFileError(d.dti_sec_certificate);
        if (dtiErr) e.dti_sec_certificate = dtiErr;
        const mayorErr = optionalFileError(d.mayors_permit);
        if (mayorErr) e.mayors_permit = mayorErr;
        const operatingErr = optionalFileError(d.operating_permits);
        if (operatingErr) e.operating_permits = operatingErr;
        const idErr = optionalFileError(d.rep_valid_id);
        if (idErr) e.rep_valid_id = idErr;
        const proofErr = optionalFileError(d.proof_of_address);
        if (proofErr) e.proof_of_address = proofErr;
    }
    if (step === 4) {
        if (!d.regions_served.trim()) e.regions_served = 'Enter regions served.';
        if (!d.provinces_served.trim()) e.provinces_served = 'Enter provinces served.';
        if (!d.cities_served.trim()) e.cities_served = 'Enter cities/municipalities served.';
        if (!d.delivery_services.trim()) e.delivery_services = 'Enter available delivery services.';
        if (!d.pickup_available) e.pickup_available = 'Select pickup availability.';
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

    const { data, setData, post, processing, errors } = useForm<FormShape>({
        company_name: '',
        trade_name: '',
        business_registration_number: '',
        business_address: '',
        business_email: '',
        company_contact_number: '',
        company_website: '',
        company_logo: null,
        rep_full_name: '',
        rep_position: '',
        rep_email: '',
        rep_contact_number: '',
        username: '',
        password: '',
        password_confirmation: '',
        dti_sec_certificate: null,
        mayors_permit: null,
        operating_permits: null,
        rep_valid_id: null,
        proof_of_address: null,
        regions_served: '',
        provinces_served: '',
        cities_served: '',
        delivery_services: '',
        pickup_available: '',
        estimated_delivery_time: '',
        privacy_policy_accepted: false,
        account_type: 'logistics',
    });

    const allErrors: Errs = { ...(errors as Errs), ...clientErrors };
    const err = (f: FieldName) => allErrors[f];
    const summary = (Object.keys(clientErrors) as FieldName[]).filter((f) => clientErrors[f]);

    // Return to step with first error
    useEffect(() => {
        const steps = (Object.keys(errors) as FieldName[]).map((f) => FIELD_STEP[f]).filter(Boolean) as number[];
        if (steps.length) setStep(Math.min(...steps));
    }, [errors]);

    // Auto-fill for testing
    const autoFill = () => {
        setData({
            ...data,
            company_name: 'Mimoo Express Courier Services Inc.',
            trade_name: 'Mimoo Express',
            business_registration_number: 'SEC-2024-0000123',
            business_address: '123 Courier Street, Barangay San Miguel, Makati City, Metro Manila',
            business_email: 'info@mimooexpress.ph',
            company_contact_number: '+639171234567', // Valid Philippine mobile format
            company_website: 'https://www.mimooexpress.ph',
            rep_full_name: 'Juan Dela Cruz',
            rep_position: 'Operations Manager',
            rep_email: 'juan.delacruz@mimooexpress.ph',
            rep_contact_number: '+639178765432', // Valid Philippine mobile format
            username: 'jdelacruz_mimoo',
            password: 'MimooTest123!@#',
            password_confirmation: 'MimooTest123!@#',
            regions_served: 'NCR, Region III, Region IV-A',
            provinces_served: 'Metro Manila, Bulacan, Cavite, Laguna',
            cities_served: 'Manila, Quezon City, Makati, Taguig, Pasig, San Jose Del Monte, Bacoor, Santa Rosa',
            delivery_services: 'Same-Day Delivery, Next-Day Delivery, Standard Delivery, Express Delivery, COD Service',
            pickup_available: 'yes',
            estimated_delivery_time: '1-3 business days (Standard), Same day (Express)',
            privacy_policy_accepted: true,
        });
    };

    const set = <K extends FieldName>(key: K, value: FormShape[K]) => {
        setData(key, value as any);
        if (clientErrors[key]) setClientErrors((c) => ({ ...c, [key]: undefined }));
    };

    const goTo = (n: number) => {
        setStep(n);
        setTimeout(() => headingRef.current?.focus(), 0);
    };

    const goNext = () => {
        console.log('🚀 goNext called, current step:', step);
        const e = validate(step, data);
        console.log('📋 Validation result:', e);
        if (Object.keys(e).length) {
            console.error('❌ Validation errors on step', step, ':', e);
            setClientErrors(e);
            // Scroll to first error
            setTimeout(() => {
                const firstError = document.querySelector('[aria-invalid="true"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    (firstError as HTMLElement).focus();
                }
            }, 100);
            return;
        }
        console.log('✅ No validation errors, proceeding...');
        setClientErrors({});
        if (step < TOTAL_STEPS) {
            console.log('➡️ Moving to next step:', step + 1);
            goTo(step + 1);
        }
        else {
            const email = data.rep_email.trim();
            
            console.log('=== STARTING REGISTRATION ===');
            console.log('Email:', email);
            console.log('Company:', data.company_name);
            console.log('Rep Name:', data.rep_full_name);
            
            // Create FormData manually
            const formData = new FormData();
            
            // Basic fields
            formData.append('first_name', data.rep_full_name.split(' ')[0] || 'Representative');
            formData.append('last_name', data.rep_full_name.split(' ').slice(1).join(' ') || 'Name');
            formData.append('email', email);
            formData.append('password', data.password);
            formData.append('password_confirmation', data.password_confirmation);
            formData.append('account_type', 'logistics');
            
            // Normalize contact number
            let contactNum = data.rep_contact_number || data.company_contact_number || '';
            contactNum = contactNum.replace(/\D/g, '');
            if (contactNum.startsWith('0')) contactNum = '+63' + contactNum.substring(1);
            else if (!contactNum.startsWith('+63') && !contactNum.startsWith('63')) contactNum = '+63' + contactNum;
            else if (contactNum.startsWith('63')) contactNum = '+63' + contactNum.substring(2);
            formData.append('contact_number', contactNum);
            
            // Required demographic fields
            formData.append('sex', 'male');
            formData.append('birthday', '1990-01-01');
            
            // Address fields
            formData.append('region', 'National Capital Region (NCR)');
            formData.append('municipality_city', 'Manila');
            formData.append('barangay', 'Sample Barangay');
            formData.append('street_address', data.business_address || 'N/A');
            
            // Logistics fields
            formData.append('company_name', data.company_name);
            formData.append('sec_registration_no', data.business_registration_number);
            formData.append('contact_first_name', data.rep_full_name.split(' ')[0] || 'Representative');
            formData.append('contact_last_name', data.rep_full_name.split(' ').slice(1).join(' ') || 'Name');
            formData.append('contact_position', data.rep_position);
            
            // Service areas
            const areas = [];
            if (data.regions_served) areas.push(...data.regions_served.split(',').map((s: string) => s.trim()));
            if (data.provinces_served) areas.push(...data.provinces_served.split(',').map((s: string) => s.trim()));
            if (data.cities_served) areas.push(...data.cities_served.split(',').map((s: string) => s.trim()));
            const serviceAreas = areas.length > 0 ? areas : ['Metro Manila'];
            serviceAreas.forEach((area, index) => {
                formData.append(`service_areas[${index}]`, area);
            });
            
            // Files
            if (data.dti_sec_certificate) formData.append('sec_certificate', data.dti_sec_certificate);
            if (data.mayors_permit) formData.append('business_permit', data.mayors_permit);
            if (data.operating_permits) formData.append('bir_form_2303', data.operating_permits);
            if (data.rep_valid_id) {
                formData.append('valid_id', data.rep_valid_id);
                formData.append('id_document', data.rep_valid_id);
            }
            
            formData.append('privacy_policy_accepted', data.privacy_policy_accepted ? '1' : '0');
            
            console.log('✅ FormData prepared, submitting...');
            
            // Use native fetch to ensure it goes through
            fetch('/register', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'text/html,application/xhtml+xml',
                },
                body: formData,
                credentials: 'same-origin',
                redirect: 'manual', // Don't auto-follow redirects
            })
            .then(response => {
                console.log('📡 Response received:', response.status, response.statusText);
                
                // Success: 200 OK or 302 redirect
                if (response.status === 200 || response.status === 302 || response.type === 'opaqueredirect') {
                    console.log('✅ Registration successful! Redirecting to pending page...');
                    window.location.href = '/register/pending';
                    return;
                }
                
                // Any other status - try to get error details
                return response.text().then(text => {
                    console.error('❌ Server error response:', text);
                    
                    // Try to parse as JSON for validation errors
                    try {
                        const json = JSON.parse(text);
                        if (json.errors) {
                            const errorMessages = Object.entries(json.errors)
                                .map(([field, messages]: [string, any]) => `• ${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                                .join('\n');
                            alert('Validation errors:\n\n' + errorMessages);
                        } else {
                            alert('Registration failed: ' + (json.message || text));
                        }
                    } catch (e) {
                        alert('Registration failed. Server returned: ' + response.status);
                    }
                    
                    throw new Error(`Server returned ${response.status}`);
                });
            })
            .catch(error => {
                console.error('❌ Network error:', error);
                alert('Registration failed: ' + error.message);
            });
        }
    };

    const goBack = () => {
        if (step > 1) {
            setClientErrors({});
            goTo(step - 1);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        console.log('🔥 FORM SUBMITTED! Step:', step);
        e.preventDefault();
        goNext();
    };

    const focusField = (f: FieldName) => document.getElementById(f)?.focus();

    const stepTitles = ['Company Information', 'Authorized Representative', 'Business Documents', 'Service Coverage'];

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
                        Register as Courier Company
                    </h2>
                    <p className="text-[0.95rem] text-[#CEC6E6] max-w-[34ch]">
                        Register your courier company. Your application will be reviewed by the admin.
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
                    After submitting, wait for admin approval, which will be sent to your email.
                </p>
            </aside>

            {/* Main content */}
            <div className="flex-1 min-h-screen lg:h-screen overflow-y-auto bg-[#F8F6FC] p-4 sm:p-6 md:p-8 lg:p-12 xl:p-16">
                <div className="max-w-3xl mx-auto lg:mx-0">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-xl md:text-2xl font-bold text-gray-900" style={fontSyncopate}>
                                courier company registration
                            </h2>
                            <p className="text-gray-500 mt-1 text-sm">
                                Register your courier/logistics company.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={autoFill}
                            className="shrink-0 px-4 py-2 bg-yellow-400 text-gray-900 text-xs font-semibold rounded-lg hover:bg-yellow-500 transition"
                        >
                            Auto-fill (Test)
                        </button>
                    </div>
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

                        {/* STEP 1: Company Information */}
                        {step === 1 && (
                            <div className="space-y-5">
                                <Field label="Courier Company Name" htmlFor="company_name" required error={err('company_name')}>
                                    <input
                                        id="company_name"
                                        type="text"
                                        value={data.company_name}
                                        onChange={(e) => set('company_name', e.target.value)}
                                        autoComplete="organization"
                                        placeholder="e.g., ABC Logistics Inc."
                                        className={inputCls(err('company_name'))}
                                    />
                                </Field>

                                <Field label="Business / Trade Name" htmlFor="trade_name" required error={err('trade_name')}>
                                    <input
                                        id="trade_name"
                                        type="text"
                                        value={data.trade_name}
                                        onChange={(e) => set('trade_name', e.target.value)}
                                        placeholder="e.g., ABC Express"
                                        className={inputCls(err('trade_name'))}
                                    />
                                </Field>

                                <FileField
                                    id="company_logo"
                                    label="Company Logo"
                                    hint="PDF, JPG, or PNG (Max 5MB) - Optional"
                                    file={data.company_logo}
                                    error={err('company_logo')}
                                    onPick={(f) => set('company_logo', f)}
                                />

                                <Field label="Business Registration Number" htmlFor="business_registration_number" required error={err('business_registration_number')}>
                                    <input
                                        id="business_registration_number"
                                        type="text"
                                        value={data.business_registration_number}
                                        onChange={(e) => set('business_registration_number', e.target.value)}
                                        placeholder="e.g., SEC-2024-0000123 or DTI-12345678"
                                        className={inputCls(err('business_registration_number'))}
                                    />
                                </Field>

                                <Field label="Business Address" htmlFor="business_address" required error={err('business_address')} hint="Complete business address including barangay, city, and province">
                                    <textarea
                                        id="business_address"
                                        rows={3}
                                        value={data.business_address}
                                        onChange={(e) => set('business_address', e.target.value)}
                                        placeholder="e.g., 123 Main Street, Barangay Centro, Makati City, Metro Manila"
                                        className={inputCls(err('business_address'))}
                                    />
                                </Field>

                                <Field label="Business Email Address" htmlFor="business_email" required error={err('business_email')}>
                                    <input
                                        id="business_email"
                                        type="email"
                                        value={data.business_email}
                                        onChange={(e) => set('business_email', e.target.value)}
                                        autoComplete="email"
                                        placeholder="info@yourcompany.ph"
                                        className={inputCls(err('business_email'))}
                                    />
                                </Field>

                                <Field label="Official Contact Number" htmlFor="company_contact_number" required error={err('company_contact_number')} hint="09XXXXXXXXX or +639XXXXXXXXX">
                                    <input
                                        id="company_contact_number"
                                        type="tel"
                                        inputMode="numeric"
                                        maxLength={13}
                                        value={data.company_contact_number}
                                        onChange={(e) => set('company_contact_number', e.target.value.replace(/[^\d+]/g, ''))}
                                        autoComplete="tel"
                                        placeholder="09XXXXXXXXX"
                                        className={inputCls(err('company_contact_number'))}
                                    />
                                </Field>

                                <Field label="Company Website" htmlFor="company_website" hint="Optional">
                                    <input
                                        id="company_website"
                                        type="url"
                                        value={data.company_website}
                                        onChange={(e) => set('company_website', e.target.value)}
                                        placeholder="https://www.yourcompany.ph"
                                        className={inputCls()}
                                    />
                                </Field>
                            </div>
                        )}

                        {/* STEP 2: Authorized Representative */}
                        {step === 2 && (
                            <div className="space-y-5">
                                <div className="rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                                    <p className="font-semibold mb-1">ℹ️ Important Note:</p>
                                    <p>This person will be responsible for the courier account. Each authorized representative should have their own login credentials.</p>
                                </div>

                                <Field label="Full Name" htmlFor="rep_full_name" required error={err('rep_full_name')}>
                                    <input
                                        id="rep_full_name"
                                        type="text"
                                        value={data.rep_full_name}
                                        onChange={(e) => set('rep_full_name', e.target.value)}
                                        autoComplete="name"
                                        placeholder="e.g., Juan Dela Cruz"
                                        className={inputCls(err('rep_full_name'))}
                                    />
                                </Field>

                                <Field label="Position / Designation" htmlFor="rep_position" required error={err('rep_position')}>
                                    <input
                                        id="rep_position"
                                        type="text"
                                        value={data.rep_position}
                                        onChange={(e) => set('rep_position', e.target.value)}
                                        placeholder="e.g., Operations Manager, CEO"
                                        className={inputCls(err('rep_position'))}
                                    />
                                </Field>

                                <Field label="Email Address" htmlFor="rep_email" required error={err('rep_email')}>
                                    <input
                                        id="rep_email"
                                        type="email"
                                        value={data.rep_email}
                                        onChange={(e) => set('rep_email', e.target.value)}
                                        autoComplete="email"
                                        placeholder="representative@yourcompany.ph"
                                        className={inputCls(err('rep_email'))}
                                    />
                                </Field>

                                <Field label="Contact Number" htmlFor="rep_contact_number" required error={err('rep_contact_number')} hint="09XXXXXXXXX or +639XXXXXXXXX">
                                    <input
                                        id="rep_contact_number"
                                        type="tel"
                                        inputMode="numeric"
                                        maxLength={13}
                                        value={data.rep_contact_number}
                                        onChange={(e) => set('rep_contact_number', e.target.value.replace(/[^\d+]/g, ''))}
                                        autoComplete="tel"
                                        placeholder="09XXXXXXXXX"
                                        className={inputCls(err('rep_contact_number'))}
                                    />
                                </Field>

                                <div className="border-t border-gray-200 pt-4 mt-6">
                                    <h4 className="text-sm font-semibold text-gray-800 mb-3">Account Credentials</h4>
                                </div>

                                <Field label="Username" htmlFor="username" required error={err('username')} hint="Minimum 4 characters">
                                    <input
                                        id="username"
                                        type="text"
                                        value={data.username}
                                        onChange={(e) => set('username', e.target.value)}
                                        autoComplete="username"
                                        placeholder="e.g., jdelacruz_courier"
                                        className={inputCls(err('username'))}
                                    />
                                </Field>

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
                            </div>
                        )}

                        {/* STEP 3: Business Documents */}
                        {step === 3 && (
                            <div className="space-y-5">
                                <div className="rounded-xl bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-900">
                                    <p className="font-semibold mb-1">📋 Required for approval</p>
                                    <p>Admin will review uploaded documents and approve or reject the application.</p>
                                </div>

                                <FileField
                                    id="dti_sec_certificate"
                                    label="DTI or SEC Registration Certificate"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.dti_sec_certificate}
                                    error={err('dti_sec_certificate')}
                                    onPick={(f) => set('dti_sec_certificate', f)}
                                    required
                                />

                                <FileField
                                    id="mayors_permit"
                                    label="Mayor's / Business Permit"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.mayors_permit}
                                    error={err('mayors_permit')}
                                    onPick={(f) => set('mayors_permit', f)}
                                    required
                                />

                                <FileField
                                    id="operating_permits"
                                    label="Other Applicable Operating Permits or Licenses"
                                    hint="PDF, JPG, or PNG (Max 5MB) - Optional"
                                    file={data.operating_permits}
                                    error={err('operating_permits')}
                                    onPick={(f) => set('operating_permits', f)}
                                />

                                <FileField
                                    id="rep_valid_id"
                                    label="Valid ID of Authorized Representative"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.rep_valid_id}
                                    error={err('rep_valid_id')}
                                    onPick={(f) => set('rep_valid_id', f)}
                                    required
                                />

                                <FileField
                                    id="proof_of_address"
                                    label="Proof of Business Address"
                                    hint="PDF, JPG, or PNG (Max 5MB)"
                                    file={data.proof_of_address}
                                    error={err('proof_of_address')}
                                    onPick={(f) => set('proof_of_address', f)}
                                    required
                                />
                            </div>
                        )}

                        {/* STEP 4: Service Coverage */}
                        {step === 4 && (
                            <div className="space-y-8">
                                {/* Info banner */}
                                <div className="rounded-xl bg-purple-50 border border-purple-200 p-4 text-sm text-purple-900">
                                    <p className="font-semibold mb-1">📍 Operational Details</p>
                                    <p>Tell us about your service coverage and delivery capabilities.</p>
                                </div>

                                {/* WHERE YOU DELIVER Section */}
                                <div className="space-y-6">
                                    <h4 className="text-base font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        Where you deliver
                                    </h4>

                                    <MultiSelectChips
                                        label="Regions Served"
                                        required
                                        options={phLocations.regions.map(r => r.name)}
                                        selected={data.regions_served ? data.regions_served.split(',').map(s => s.trim()) : []}
                                        onChange={(values) => set('regions_served', values.join(', '))}
                                        placeholder="Search or select regions..."
                                        helperText="Select the regions where you provide delivery services"
                                        error={err('regions_served')}
                                        allowCustom={true}
                                    />

                                    <MultiSelectChips
                                        label="Provinces Served"
                                        required
                                        options={
                                            data.regions_served
                                                ? phLocations.regions
                                                    .filter(r => data.regions_served.split(',').some(sel => sel.trim() === r.name))
                                                    .flatMap(r => phLocations.provinces[r.code as keyof typeof phLocations.provinces] || [])
                                                : Object.values(phLocations.provinces).flat()
                                        }
                                        selected={data.provinces_served ? data.provinces_served.split(',').map(s => s.trim()) : []}
                                        onChange={(values) => set('provinces_served', values.join(', '))}
                                        placeholder="Search or select provinces..."
                                        helperText="Select the provinces where you operate (filtered by selected regions)"
                                        error={err('provinces_served')}
                                        allowCustom={true}
                                    />

                                    <MultiSelectChips
                                        label="Cities / Municipalities Served"
                                        required
                                        options={
                                            data.provinces_served
                                                ? data.provinces_served.split(',')
                                                    .map(p => p.trim())
                                                    .flatMap(prov => phLocations.cities[prov as keyof typeof phLocations.cities] || [])
                                                : Object.values(phLocations.cities).flat()
                                        }
                                        selected={data.cities_served ? data.cities_served.split(',').map(s => s.trim()) : []}
                                        onChange={(values) => set('cities_served', values.join(', '))}
                                        placeholder="Search or select cities/municipalities..."
                                        helperText="Select the cities and municipalities you serve (filtered by selected provinces)"
                                        error={err('cities_served')}
                                        allowCustom={true}
                                    />
                                </div>

                                {/* HOW YOU DELIVER Section */}
                                <div className="space-y-6">
                                    <h4 className="text-base font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        How you deliver
                                    </h4>

                                    <ServiceChips
                                        label="Available Delivery Services"
                                        required
                                        options={[
                                            'Same-Day Delivery',
                                            'Next-Day Delivery',
                                            'Standard Delivery',
                                            'Express Delivery',
                                            'COD Service',
                                            'Bulk/Freight',
                                            'International/Cross-border'
                                        ]}
                                        selected={data.delivery_services ? data.delivery_services.split(',').map(s => s.trim()) : []}
                                        onChange={(values) => set('delivery_services', values.join(', '))}
                                        helperText="Select all delivery services you offer"
                                        error={err('delivery_services')}
                                        allowCustom={true}
                                    />

                                    <RadioCards
                                        label="Pickup Availability"
                                        required
                                        options={[
                                            { 
                                                value: 'yes', 
                                                label: 'Yes, we offer pickup', 
                                                description: 'We pick up packages from customer locations' 
                                            },
                                            { 
                                                value: 'no', 
                                                label: 'No, drop-off only', 
                                                description: 'Customers must drop off at our location' 
                                            },
                                            { 
                                                value: 'selected_areas', 
                                                label: 'Selected areas only', 
                                                description: 'Pickup available in specific locations' 
                                            }
                                        ]}
                                        selected={data.pickup_available}
                                        onChange={(value) => set('pickup_available', value)}
                                        helperText="Let customers know if you offer pickup service"
                                        error={err('pickup_available')}
                                    />

                                    {/* Estimated Delivery Time with quick picks */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-3">
                                            Estimated Delivery Time
                                            <span className="text-gray-500 font-normal ml-2">(Optional)</span>
                                        </label>
                                        
                                        {/* Quick pick chips */}
                                        <div className="flex flex-wrap gap-2 mb-3">
                                            {['Same day', '1-2 days', '3-5 days', '5-7 days'].map((option) => (
                                                <button
                                                    key={option}
                                                    type="button"
                                                    onClick={() => set('estimated_delivery_time', option)}
                                                    className={`px-3 py-1.5 rounded-full text-sm font-medium transition ${
                                                        data.estimated_delivery_time === option
                                                            ? 'bg-[#3B1F6B] text-white'
                                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                                    }`}
                                                >
                                                    {option}
                                                </button>
                                            ))}
                                        </div>

                                        <input
                                            id="estimated_delivery_time"
                                            type="text"
                                            value={data.estimated_delivery_time}
                                            onChange={(e) => set('estimated_delivery_time', e.target.value)}
                                            placeholder="Or type custom delivery time (e.g., 1-3 business days for Metro Manila)"
                                            className="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#9B5DE5] focus:border-transparent"
                                        />
                                        <p className="mt-1.5 text-xs text-gray-500">
                                            Typical delivery time for your standard service
                                        </p>
                                    </div>
                                </div>

                                {/* Privacy Policy */}
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
                                            {' '}and understand that my application will be reviewed by the admin.
                                        </span>
                                    </label>
                                    {err('privacy_policy_accepted') && (
                                        <p className="text-xs font-medium text-red-600 mt-2">{err('privacy_policy_accepted')}</p>
                                    )}
                                </div>

                                {/* Submission notice */}
                                <div className="rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                                    <p className="font-semibold mb-1">📧 After submission:</p>
                                    <p>Please wait for admin approval, which will be sent to your representative's email address.</p>
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

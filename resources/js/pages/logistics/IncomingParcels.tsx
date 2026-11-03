import { Head, Link, router } from '@inertiajs/react';
import { ReactNode, useState, useRef, useEffect } from 'react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSyne = { fontFamily: 'Syne, sans-serif' };

interface IncomingParcel {
    id: number;
    order_number: string;
    waybill_number: string;
    buyer_name: string;
    buyer_phone: string;
    buyer_address: string;
    seller_name: string;
    total_amount: number;
    payment_method: string;
    items_count: number;
    weight: number;
    shipped_at: string;
    status: string;
}

interface Props {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            account_type: string;
        };
    };
    company: {
        name: string;
        contact_person: string;
    };
    incomingParcels: IncomingParcel[];
}

export default function IncomingParcels({ auth, company, incomingParcels = [] }: Props) {
    const [activeTab, setActiveTab] = useState('incoming-parcels');
    const [viewMode, setViewMode] = useState<'scanner' | 'pickup'>('scanner');
    const [selectedParcel, setSelectedParcel] = useState('#1013');
    const [scannedParcel, setScannedParcel] = useState('#1078');
    const [showUserMenu, setShowUserMenu] = useState(false);
    const [cameraActive, setCameraActive] = useState(false);
    const [cameraError, setCameraError] = useState<string | null>(null);
    const videoRef = useRef<HTMLVideoElement>(null);
    const streamRef = useRef<MediaStream | null>(null);

    const handleLogout = () => {
        router.post('/logout');
    };

    const startCamera = async () => {
        try {
            setCameraError(null);
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Use back camera on mobile
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });
            
            if (videoRef.current) {
                videoRef.current.srcObject = stream;
                streamRef.current = stream;
                setCameraActive(true);
            }
        } catch (error) {
            console.error('Camera error:', error);
            setCameraError('Cannot access camera. Please allow camera permissions.');
            setCameraActive(false);
        }
    };

    const stopCamera = () => {
        if (streamRef.current) {
            streamRef.current.getTracks().forEach(track => track.stop());
            streamRef.current = null;
        }
        if (videoRef.current) {
            videoRef.current.srcObject = null;
        }
        setCameraActive(false);
    };

    // Start camera when component mounts and scanner view is active
    useEffect(() => {
        if (viewMode === 'scanner') {
            startCamera();
        }
        
        // Cleanup camera when component unmounts
        return () => {
            stopCamera();
        };
    }, [viewMode]);

    const navItems = [
        { id: 'dashboard', label: 'Dashboard', icon: 'grid', href: '/logistics/dashboard' },
        { id: 'incoming-parcels', label: 'Incoming Parcels', icon: 'inbox', href: '/logistics/incoming-parcels' },
        { id: 'sort-assign', label: 'Sort & Assign', icon: 'sort', href: '/logistics/sort-assign' },
        { id: 'delivery-monitoring', label: 'Delivery Monitoring', icon: 'monitor', href: '/logistics/delivery-monitoring' },
        { id: 'riders', label: 'Riders', icon: 'bike', href: '/logistics/riders' },
        { id: 'branches-checklists', label: 'Branches & Checklists', icon: 'checklist', href: '/logistics/branches-checklists' },
        { id: 'zones-rates', label: 'Zones & Rates', icon: 'zones', href: '/logistics/zones-rates' },
        { id: 'cod-remittance', label: 'COD & Remittance', icon: 'chart-bar', href: '/logistics/cod-remittance' },
        { id: 'reports', label: 'Reports', icon: 'chart', href: '/logistics/reports' },
        { id: 'support', label: 'Support', icon: 'headset', href: '/logistics/support' },
        { id: 'messages', label: 'Messages', icon: 'message', href: '/logistics/messages' },
    ];

    const getIcon = (type: string) => {
        switch (type) {
            case 'grid':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>;
            case 'inbox':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>;
            case 'sort':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 6h18M7 12h10M10 18h4"/></svg>;
            case 'monitor':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>;
            case 'bike':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>;
            case 'checklist':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/></svg>;
            case 'zones':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/></svg>;
            case 'chart-bar':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>;
            case 'chart':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>;
            case 'headset':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>;
            case 'message':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>;
            default:
                return null;
        }
    };

    return (
        <>
            <Head title="Incoming Parcels" />
            
            <style>{`
                .scrollbar-hide::-webkit-scrollbar {
                    display: none;
                }
                .scrollbar-hide {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
                @keyframes scan {
                    0% { top: 0; }
                    50% { top: 100%; }
                    100% { top: 0; }
                }
                .animate-scan {
                    animation: scan 2s ease-in-out infinite;
                }
            `}</style>
            
            <div className="flex h-screen bg-gray-50" style={fontSyne}>
                {/* Sidebar */}
                <aside className="w-64 bg-[#2B2249] text-white flex-shrink-0 hidden lg:block overflow-y-auto scrollbar-hide" style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
                    <div className="p-6">
                        <Link href="/" className="flex items-center gap-2 mb-8">
                            <span className="w-8 h-8 rounded-full bg-[#9B5DE5]" />
                            <span className="font-bold text-xl tracking-widest" style={fontSyncopate}>
                                mimoo
                            </span>
                        </Link>

                        <nav className="space-y-1">
                            {navItems.map((item) => (
                                <Link
                                    key={item.id}
                                    href={item.href}
                                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition ${
                                        activeTab === item.id
                                            ? 'bg-[#2E2240] text-white'
                                            : 'text-gray-300 hover:bg-[#2E2240]/50 hover:text-white'
                                    }`}
                                >
                                    {getIcon(item.icon)}
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                    </div>

                    <div className="absolute bottom-0 left-0 w-64 border-t border-white/10 bg-[#2B2249]">
                        <Link
                            href="/logistics/account"
                            className={`w-full flex items-center gap-3 px-6 py-4 text-sm font-medium transition ${
                                activeTab === 'account'
                                    ? 'bg-[#4B5EF9] text-white'
                                    : 'text-gray-300 hover:bg-[#2E2240]/50 hover:text-white'
                            }`}
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Account
                        </Link>
                    </div>
                </aside>

                {/* Main Content */}
                <div className="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
                    {/* Top Bar */}
                    <header className="bg-white border-b border-gray-200 px-6 py-4 flex-shrink-0">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-6">
                                <h1 className="text-base font-bold text-gray-700">
                                    Laguna Hub (Santa Cruz)
                                </h1>
                                <span className="text-sm text-gray-400">/</span>
                                <h2 className="text-base font-semibold text-gray-900">Incoming Parcels</h2>
                            </div>
                            <div className="flex items-center gap-4">
                                <div className="relative">
                                    <input
                                        type="text"
                                        className="w-64 px-4 py-2 pl-10 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#3B1F6B] focus:border-transparent"
                                    />
                                    <svg
                                        className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                    >
                                        <circle cx="11" cy="11" r="7" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                    <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Ctrl + K</span>
                                </div>
                                <button className="p-2 hover:bg-gray-100 rounded-lg transition">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <circle cx="12" cy="12" r="5"/>
                                        <path d="M12 1v6m0 6v6"/>
                                    </svg>
                                </button>
                                <button className="p-2 hover:bg-gray-100 rounded-lg transition relative">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                    <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                </button>
                                <div className="relative">
                                    <button 
                                        onClick={() => setShowUserMenu(!showUserMenu)}
                                        className="w-9 h-9 rounded-full bg-[#2B2249] text-white flex items-center justify-center text-sm font-bold hover:bg-[#3d2e5f] transition"
                                    >
                                        {auth.user.name.charAt(0)}
                                    </button>
                                    {showUserMenu && (
                                        <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                            <Link
                                                href="/logistics/account"
                                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <div className="flex items-center gap-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="12" cy="7" r="4"/>
                                                    </svg>
                                                    Account
                                                </div>
                                            </Link>
                                            <button
                                                onClick={handleLogout}
                                                className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2"
                                            >
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                                    <polyline points="16 17 21 12 16 7"/>
                                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                                </svg>
                                                Log out
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <main className="flex-1 overflow-hidden p-6 bg-gray-50">
                        <div className="h-full flex gap-6 overflow-hidden">
                            {/* Left Panel */}
                            <div className="flex-1 flex flex-col overflow-hidden">
                                {/* Mode Toggle Buttons */}
                                <div className="flex items-center justify-between mb-6 flex-shrink-0">
                                    <div className="flex gap-2">
                                        <button
                                            onClick={() => setViewMode('scanner')}
                                            className={`px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 ${
                                                viewMode === 'scanner'
                                                    ? 'bg-[#2B2249] text-white'
                                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                            }`}
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                                                <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                                                <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                                                <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                            </svg>
                                            FIRST SCAN (INBOUND/TRACKING ID)
                                        </button>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <div className="text-sm text-gray-600">
                                            <span className="font-semibold">FIRST RATE</span>
                                            <br/>
                                            <span className="text-xs">0 parcels</span>
                                        </div>
                                        <button className="px-4 py-2 bg-[#E8E3F0] text-[#5B4B6E] rounded-lg text-sm font-semibold hover:bg-[#D4CEE0] transition flex items-center gap-2">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="3" y="3" width="7" height="7"/>
                                                <rect x="14" y="3" width="7" height="7"/>
                                                <rect x="14" y="14" width="7" height="7"/>
                                                <rect x="3" y="14" width="7" height="7"/>
                                            </svg>
                                            Scanner Ready
                                        </button>
                                    </div>
                                </div>

                                {/* Scanner View */}
                                {viewMode === 'scanner' && (
                                    <div className="bg-white rounded-xl border border-gray-200 p-6 flex-1 overflow-y-auto" style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
                                        {/* QR Scanner Card */}
                                        <div className="mb-6">
                                            <div className="flex items-center justify-between mb-4">
                                                <h3 className="text-lg font-bold text-gray-900" style={fontSyncopate}>Live Optical Viewfinder</h3>
                                                <button 
                                                    onClick={cameraActive ? stopCamera : startCamera}
                                                    className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                                                        cameraActive 
                                                            ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                                                            : 'bg-[#E8E3F0] text-[#5B4B6E] hover:bg-[#D4CEE0]'
                                                    }`}
                                                >
                                                    {cameraActive ? '? Camera Active' : 'Start Camera'}
                                                </button>
                                            </div>
                                            <div className="bg-[#1a1f3a] rounded-xl overflow-hidden relative w-full" style={{ minHeight: '500px', aspectRatio: '16/9' }}>
                                                {/* Camera Video Feed - Must be first to be in background */}
                                                <video
                                                    ref={videoRef}
                                                    autoPlay
                                                    playsInline
                                                    muted
                                                    className="absolute inset-0 w-full h-full object-cover z-0"
                                                    style={{ display: cameraActive ? 'block' : 'none' }}
                                                />
                                                
                                                {/* Camera Error */}
                                                {cameraError && (
                                                    <div className="absolute inset-0 flex items-center justify-center bg-[#1a1f3a] p-8 z-10">
                                                        <div className="text-center">
                                                            <svg className="w-16 h-16 mx-auto mb-4 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                                            </svg>
                                                            <p className="text-red-400 text-sm mb-4">{cameraError}</p>
                                                            <button 
                                                                onClick={startCamera}
                                                                className="px-4 py-2 bg-[#2B2249] text-white rounded-lg text-sm font-semibold hover:bg-[#3d2e5f] transition"
                                                            >
                                                                Try Again
                                                            </button>
                                                        </div>
                                                    </div>
                                                )}
                                                
                                                {/* Placeholder when camera is off */}
                                                {!cameraActive && !cameraError && (
                                                    <div className="absolute inset-0 flex items-center justify-center bg-[#1a1f3a] p-8 z-10">
                                                        <div className="text-center">
                                                            <svg className="w-16 h-16 mx-auto mb-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                                <circle cx="12" cy="13" r="4"/>
                                                            </svg>
                                                            <p className="text-gray-400 text-sm mb-4">Camera is off</p>
                                                            <button 
                                                                onClick={startCamera}
                                                                className="px-4 py-2 bg-[#2B2249] text-white rounded-lg text-sm font-semibold hover:bg-[#3d2e5f] transition"
                                                            >
                                                                Start Camera
                                                            </button>
                                                        </div>
                                                    </div>
                                                )}
                                                
                                                {/* Scanning Overlay with corners */}
                                                {cameraActive && (
                                                    <div className="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                                                        <div className="relative w-64 h-64">
                                                            {/* Semi-transparent overlay around scanner */}
                                                            <div className="absolute inset-0 border-4 border-purple-400/30 rounded-2xl"></div>
                                                            
                                                            {/* Corner brackets */}
                                                            <div className="absolute top-0 left-0 w-12 h-12 border-t-4 border-l-4 border-purple-400 rounded-tl-2xl"></div>
                                                            <div className="absolute top-0 right-0 w-12 h-12 border-t-4 border-r-4 border-purple-400 rounded-tr-2xl"></div>
                                                            <div className="absolute bottom-0 left-0 w-12 h-12 border-b-4 border-l-4 border-purple-400 rounded-bl-2xl"></div>
                                                            <div className="absolute bottom-0 right-0 w-12 h-12 border-b-4 border-r-4 border-purple-400 rounded-br-2xl"></div>
                                                            
                                                            {/* Scanning line animation */}
                                                            <div className="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-purple-400 to-transparent animate-scan"></div>
                                                        </div>
                                                    </div>
                                                )}
                                                
                                                {/* Instructions */}
                                                {cameraActive && (
                                                    <div className="absolute bottom-4 left-0 right-0 text-center z-30">
                                                        <p className="text-white text-sm font-medium bg-black/50 inline-block px-4 py-2 rounded-lg">
                                                            Center QR code inside the frame
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                            <button className="w-full mt-4 px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-100 transition flex items-center justify-center gap-2">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                    <polyline points="7 10 12 15 17 10"/>
                                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                                </svg>
                                                Enter Parcel ID manually
                                            </button>
                                        </div>

                                        {/* Scanned Result */}
                                        <div className="border-t border-gray-200 pt-6">
                                            <div className="flex items-center justify-between mb-4">
                                                <h3 className="text-lg font-bold text-gray-900" style={fontSyncopate}>Scanned Result</h3>
                                                <div className="px-3 py-1 bg-[#E8E3F0] text-[#5B4B6E] rounded-lg text-xs font-bold">
                                                    TRACKING ID: {scannedParcel}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-3 gap-4">
                                                <div className="bg-gray-50 rounded-lg p-4 flex items-center justify-center">
                                                    <svg width="80" height="80" viewBox="0 0 100 100">
                                                        <rect x="0" y="0" width="10" height="10" fill="black"/>
                                                        <rect x="20" y="0" width="10" height="10" fill="black"/>
                                                        <rect x="40" y="0" width="10" height="10" fill="black"/>
                                                        <rect x="70" y="0" width="10" height="10" fill="black"/>
                                                        <rect x="90" y="0" width="10" height="10" fill="black"/>
                                                    </svg>
                                                </div>
                                                <div className="col-span-2 space-y-3">
                                                    <div className="flex justify-between">
                                                        <span className="text-xs font-semibold text-gray-500">Arrival Time</span>
                                                        <span className="text-sm font-medium">Oct 26, 2026 "� 07:21 AM</span>
                                                    </div>
                                                    <div className="flex justify-between">
                                                        <span className="text-xs font-semibold text-gray-500">Contact Number</span>
                                                        <span className="text-sm font-medium">0917 105 1040</span>
                                                    </div>
                                                    <div className="flex justify-between">
                                                        <span className="text-xs font-semibold text-gray-500">Route Name</span>
                                                        <span className="text-sm font-medium">Monte Clera Tomtes</span>
                                                    </div>
                                                    <div className="flex justify-between">
                                                        <span className="text-xs font-semibold text-gray-500">Delivery Address</span>
                                                        <span className="text-sm font-medium">17 Bros! St., Purok 2, Palasan I</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Target Bin */}
                                            <div className="bg-[#E8E3F0] border border-[#D4CEE0] rounded-lg p-4 mt-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-10 h-10 bg-[#D4CEE0] rounded-lg flex items-center justify-center">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div className="text-xs font-semibold text-gray-500 mb-1">ASSIGNMENT</div>
                                                            <div className="text-base font-bold text-gray-900">Target Bin: BIN-RO1</div>
                                                            <div className="text-xs text-gray-600">Palasan "� Station Zone Sector 801</div>
                                                        </div>
                                                    </div>
                                                    <button className="px-3 py-1.5 bg-[#D4CEE0] text-[#5B4B6E] rounded-lg text-xs font-semibold">
                                                        Category: China #5
                                                    </button>
                                                </div>
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="grid grid-cols-2 gap-3 mt-6">
                                                <button className="px-6 py-3 bg-[#2B2249] text-white rounded-lg font-semibold hover:bg-[#2E2240] transition flex items-center justify-center gap-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <polyline points="20 6 9 17 4 12"/>
                                                    </svg>
                                                    Confirm Received
                                                </button>
                                                <button className="px-6 py-3 bg-white border border-red-200 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition flex items-center justify-center gap-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <circle cx="12" cy="12" r="10"/>
                                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                                    </svg>
                                                    Report Problem
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Right Sidebar - Last 5 Scanned Parcels */}
                            <div className="w-96 flex-shrink-0 overflow-y-auto scrollbar-hide" style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
                                <div className="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h3 className="text-lg font-bold text-gray-900" style={fontSyncopate}>Last 5 Scanned Parcels</h3>
                                        <button className="p-1.5 hover:bg-gray-100 rounded-lg">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-400">
                                                <polyline points="1 4 1 10 7 10"/>
                                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p className="text-sm text-gray-500 mb-6">Orders in transit from sellers ({incomingParcels.length} parcels)</p>
                                    <div className="space-y-3">
                                        {incomingParcels.length === 0 ? (
                                            <div className="text-center py-8">
                                                <p className="text-sm text-gray-500">No incoming parcels yet</p>
                                            </div>
                                        ) : (
                                            incomingParcels.slice(0, 5).map((parcel) => (
                                                <div key={parcel.id} className="bg-gray-50 rounded-lg p-4 hover:bg-[#E8E3F0] cursor-pointer transition">
                                                    <div className="flex items-start justify-between mb-2">
                                                        <div className="font-bold text-gray-900">{parcel.order_number}</div>
                                                        <div className="text-xs text-gray-500">{parcel.shipped_at.split(' ')[2]}</div>
                                                    </div>
                                                    <div className="text-sm text-gray-900 mb-1">{parcel.buyer_name}</div>
                                                    <div className="text-xs text-gray-500">{parcel.waybill_number}</div>
                                                    <div className="text-xs text-gray-600 mt-2">{parcel.buyer_address.substring(0, 30)}...</div>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}

IncomingParcels.layout = (page: ReactNode) => page;

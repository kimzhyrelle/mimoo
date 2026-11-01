import { Head, Link } from '@inertiajs/react';
import { ReactNode, useState } from 'react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSyne = { fontFamily: 'Syne, sans-serif' };

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
}

export default function SortAssign({ auth, company }: Props) {
    const [activeTab, setActiveTab] = useState('sort-assign');
    const [viewMode, setViewMode] = useState<'staging' | 'parcel'>('staging');
    const [selectedParcels, setSelectedParcels] = useState<string[]>([]);

    const parcels = [
        { id: 'PKG44', address: '33 Road St., Brgy. Lumban', barangay: 'Lumban', carrier: 'J&T Express', rider: '' },
        { id: 'PKG45', address: 'Purok 2, Brgy. Gatid', barangay: 'Gatid', carrier: 'J&T Express', rider: '' },
        { id: 'PKG94', address: 'Purok 2, Brgy. Palasan', barangay: 'Palasan', carrier: 'J&T Express', rider: '' },
        { id: 'PKG30', address: '8 Quintos St., Lumban', barangay: 'Lumban', carrier: 'J&T Express', rider: '' },
        { id: 'PKG45', address: 'Purok 4, Brgy. Magsaysay', barangay: 'Magsaysay', carrier: 'Lalamore', rider: '' },
        { id: 'PKG96', address: '850 Ross, Brgy. Noalisan', barangay: 'Noalisan', carrier: 'J&T Express', rider: '' }
    ];

    const destinations = [
        { 
            name: 'Magsaysay', 
            color: 'green',
            parcels: 3, 
            weight: '6.4 kg', 
            rider: { initial: 'EN', name: 'E. Navarro', parcels: 3 }
        },
        { 
            name: 'Bagong Pook', 
            color: 'blue',
            parcels: 2, 
            weight: '5.0 kg', 
            rider: { initial: 'CD', name: 'C. Delos Santos', parcels: 3 }
        },
        { 
            name: 'Duluti', 
            color: 'red',
            parcels: 2, 
            weight: '4.2 kg', 
            rider: { initial: 'KV', name: 'K. Villar', parcels: 3 }
        },
        { 
            name: 'Gatid', 
            color: 'purple',
            parcels: 3, 
            weight: '7.9 kg', 
            rider: { initial: 'JC', name: 'J. Dela Cruz', parcels: 3 }
        },
        { 
            name: 'Lumban', 
            color: 'purple',
            parcels: 3, 
            weight: '4.5 kg', 
            rider: null
        }
    ];

    const toggleParcelSelection = (id: string) => {
        if (selectedParcels.includes(id)) {
            setSelectedParcels(selectedParcels.filter(p => p !== id));
        } else {
            setSelectedParcels([...selectedParcels, id]);
        }
    };

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
            <style>{`.scrollbar-hide::-webkit-scrollbar { display: none; } .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }`}</style><Head title="Sort & Assign" />
            
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
                                <h2 className="text-base font-semibold text-gray-900">Sort & Assign</h2>
                            </div>
                            <div className="flex items-center gap-4">
                                <div className="relative">
                                    <input
                                        type="text"
                                        placeholder="Search / Scan"
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
                                <button className="w-9 h-9 rounded-full bg-[#2B2249] text-white flex items-center justify-center text-sm font-bold">
                                    {auth.user.name.charAt(0)}
                                </button>
                            </div>
                        </div>
                    </header>

                    {/* Content Area */}
                    <main className="flex-1 overflow-auto p-6 bg-gray-50">
                        <div className="max-w-7xl mx-auto">
                            {/* View Toggle Tabs */}
                            <div className="flex gap-2 mb-6">
                                <button
                                    onClick={() => setViewMode('staging')}
                                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition ${
                                        viewMode === 'staging'
                                            ? 'bg-[#2B2249] text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                                >
                                    STAGE 01 â€” HUB #1 ALLOCATION
                                </button>
                                <button
                                    onClick={() => setViewMode('parcel')}
                                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition ${
                                        viewMode === 'parcel'
                                            ? 'bg-[#2B2249] text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                                >
                                    Lumbun
                                </button>
                            </div>

                            {/* Staging Bay #5 Status View */}
                            {viewMode === 'staging' && (
                                <>
                                    {/* Status Banner */}
                                    <div className="bg-[#2B2249] rounded-xl p-6 mb-6 text-white">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-4">
                                                <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M3 6h18M7 12h10M10 18h4"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 className="text-xl font-bold mb-1" style={fontSyncopate}>Staging Bay #5: Status</h3>
                                                    <p className="text-sm text-gray-300">1 of 10 clusters assigned â€¢ 38 parcels staged</p>
                                                    <p className="text-xs text-gray-400 mt-1">22 weigh-in parcels have unrecorded weights</p>
                                                </div>
                                            </div>
                                            <div className="flex gap-3">
                                                <button className="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm font-semibold transition">
                                                    Review Weigh-in Logs
                                                </button>
                                                <button className="px-4 py-2 bg-[#3D2E54] hover:bg-[#2E2240] text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                                    </svg>
                                                    Push to Loading Goods
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Stats Cards */}
                                    <div className="grid grid-cols-3 gap-6 mb-6">
                                        <div className="bg-white rounded-xl border border-gray-200 p-6">
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    </svg>
                                                </div>
                                                <div className="text-xs font-semibold text-gray-500" style={fontSyncopate}>WORK VOLUME</div>
                                            </div>
                                            <div className="text-3xl font-bold text-gray-900 mb-2">32</div>
                                            <div className="text-sm text-gray-600">10 clusters pending</div>
                                        </div>
                                        <div className="bg-white rounded-xl border border-gray-200 p-6">
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                        <circle cx="5.5" cy="17.5" r="3.5"/>
                                                        <circle cx="18.5" cy="17.5" r="3.5"/>
                                                        <path d="M12 17.5V14l-3-3 4-3 2 3h2"/>
                                                    </svg>
                                                </div>
                                                <div className="text-xs font-semibold text-gray-500" style={fontSyncopate}>FLEET REQUIREMENT</div>
                                            </div>
                                            <div className="text-3xl font-bold text-gray-900 mb-2">0</div>
                                            <div className="text-sm text-gray-600">0 idle â€¢ 0 on Route</div>
                                        </div>
                                        <div className="bg-white rounded-xl border border-gray-200 p-6">
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                                                        <path d="M16 3h2a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </div>
                                                <div className="text-xs font-semibold text-gray-500" style={fontSyncopate}>TODAY'S PROGRESS</div>
                                            </div>
                                            <div className="text-3xl font-bold text-gray-900 mb-2">0</div>
                                            <div className="text-sm text-gray-600">Fully assigned today</div>
                                        </div>
                                    </div>

                                    {/* Destination Clusters */}
                                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                                        <div className="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 className="text-lg font-bold text-gray-900" style={fontSyncopate}>Destination Clusters</h3>
                                                <p className="text-sm text-gray-500">10 clusters</p>
                                            </div>
                                            <div className="flex gap-2">
                                                <button className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm hover:bg-gray-100">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-600">
                                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                                    </svg>
                                                </button>
                                                <button className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                    Unassigned only
                                                </button>
                                                <button className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                    Heaviest First
                                                </button>
                                                <button className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                                                    Oldest First
                                                </button>
                                                <button className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm hover:bg-gray-100 flex items-center gap-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-600">
                                                        <line x1="4" y1="21" x2="4" y2="14"/>
                                                        <line x1="4" y1="10" x2="4" y2="3"/>
                                                        <line x1="12" y1="21" x2="12" y2="12"/>
                                                        <line x1="12" y1="8" x2="12" y2="3"/>
                                                        <line x1="20" y1="21" x2="20" y2="16"/>
                                                        <line x1="20" y1="12" x2="20" y2="3"/>
                                                        <line x1="1" y1="14" x2="7" y2="14"/>
                                                        <line x1="9" y1="8" x2="15" y2="8"/>
                                                        <line x1="17" y1="16" x2="23" y2="16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div className="space-y-4">
                                            <div className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Show all rows</div>
                                            
                                            {/* Table Header */}
                                            <div className="grid grid-cols-12 gap-4 px-6 py-3 bg-gray-50 rounded-t-lg border-b border-gray-200">
                                                <div className="col-span-3 text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>BARANGAY</div>
                                                <div className="col-span-2 text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>PARCELS</div>
                                                <div className="col-span-2 text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>WEIGHT</div>
                                                <div className="col-span-5 text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>RIDER</div>
                                            </div>

                                            {/* Table Rows */}
                                            {destinations.map((dest, idx) => (
                                                <div key={idx} className="grid grid-cols-12 gap-4 px-6 py-4 hover:bg-gray-50 rounded-lg border-b border-gray-100 last:border-0">
                                                    <div className="col-span-3 flex items-center gap-2">
                                                        <div className={`w-3 h-3 rounded-full ${
                                                            dest.color === 'green' ? 'bg-green-500' :
                                                            dest.color === 'blue' ? 'bg-[#E8E3F0]' :
                                                            dest.color === 'red' ? 'bg-red-500' :
                                                            'bg-[#E8E3F0]'
                                                        }`}></div>
                                                        <span className="text-sm font-medium text-gray-900">{dest.name}</span>
                                                    </div>
                                                    <div className="col-span-2 flex items-center">
                                                        <span className="text-sm text-gray-900">{dest.parcels}</span>
                                                        <span className="text-xs text-gray-500 ml-1">sorted</span>
                                                    </div>
                                                    <div className="col-span-2 flex items-center">
                                                        <span className="text-sm font-medium text-gray-900">{dest.weight}</span>
                                                    </div>
                                                    <div className="col-span-5 flex items-center">
                                                        {dest.rider ? (
                                                            <div className="flex items-center gap-3">
                                                                <div className="w-8 h-8 rounded-full bg-[#D4CEE0] text-[#5B4B6E] flex items-center justify-center text-xs font-bold">
                                                                    {dest.rider.initial}
                                                                </div>
                                                                <div>
                                                                    <div className="text-sm font-medium text-gray-900">{dest.rider.name}</div>
                                                                    <div className="text-xs text-gray-500">{dest.rider.parcels} parcels</div>
                                                                </div>
                                                            </div>
                                                        ) : (
                                                            <span className="text-sm text-gray-400">â€”</span>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </>
                            )}

                            {/* Parcel Queue View */}
                            {viewMode === 'parcel' && (
                                <div className="space-y-6">
                                    {/* Parcel Queue Section */}
                                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                                        <div className="mb-6">
                                            <h3 className="text-lg font-bold text-gray-900 mb-2" style={fontSyncopate}>Parcel queue</h3>
                                            <p className="text-sm text-gray-500">Newly arrived parcels â€” determine barangay, then assign rider</p>
                                        </div>

                                        {/* Search and Filters */}
                                        <div className="grid grid-cols-7 gap-3 mb-6">
                                            <input
                                                type="text"
                                                placeholder="Search parcel ID or s"
                                                className="col-span-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2249]"
                                            />
                                            <select className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2249]">
                                                <option>Sort: Default</option>
                                            </select>
                                            <select className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2249]">
                                                <option>All statuses</option>
                                            </select>
                                            <select className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2249]">
                                                <option>All barangays</option>
                                            </select>
                                            <select className="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#2B2249]">
                                                <option>All riders</option>
                                            </select>
                                            <div className="flex gap-2">
                                                <button className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm hover:bg-gray-100">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-600">
                                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                    </svg>
                                                </button>
                                                <button className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm hover:bg-gray-100">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-gray-600">
                                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        {/* Parcels Table */}
                                        <div className="overflow-x-auto">
                                            <table className="w-full">
                                                <thead className="bg-gray-50 border-b border-gray-200">
                                                    <tr>
                                                        <th className="px-4 py-3 text-left">
                                                            <input type="checkbox" className="rounded border-gray-300" />
                                                        </th>
                                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>PARCEL</th>
                                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>DELIVERY ADDRESS</th>
                                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>BARANGAY</th>
                                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>CARRIER</th>
                                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>ASSIGNED RIDER</th>
                                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>ACTION</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-gray-100">
                                                    {parcels.map((parcel, idx) => (
                                                        <tr key={idx} className="hover:bg-gray-50">
                                                            <td className="px-4 py-4">
                                                                <input 
                                                                    type="checkbox" 
                                                                    className="rounded border-gray-300"
                                                                    checked={selectedParcels.includes(parcel.id)}
                                                                    onChange={() => toggleParcelSelection(parcel.id)}
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <div className="text-sm font-bold text-gray-900">#{parcel.id}</div>
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <div className="text-sm text-gray-900">{parcel.address}</div>
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <div className="text-sm font-medium text-gray-900">{parcel.barangay}</div>
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <div className="text-sm text-gray-600">{parcel.carrier}</div>
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <span className="text-sm text-gray-400">â€”</span>
                                                            </td>
                                                            <td className="px-6 py-4 text-right">
                                                                <button className="text-sm font-semibold text-[#5B4B6E] hover:underline">
                                                                    View
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>

                                        <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
                                            <span>38 sorting</span>
                                            <span>91 results</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}

SortAssign.layout = (page: ReactNode) => page;

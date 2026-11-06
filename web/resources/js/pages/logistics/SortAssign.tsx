import { Head, Link, router } from '@inertiajs/react';
import { ReactNode, useState } from 'react';

const fontSyncopate = { fontFamily: 'Syncopate, sans-serif' };
const fontSyne = { fontFamily: 'Syne, sans-serif' };

interface ParcelData {
    id: number;
    order_number: string;
    waybill_number: string;
    buyer_name: string;
    buyer_phone: string;
    buyer_address: string;
    barangay: string;
    seller_name: string;
    total_amount: number;
    payment_method: string;
    items_count: number;
    weight: number;
    confirmed_at: string;
    status: string;
}

interface CourierData {
    id: number;
    name: string;
    contact_person: string;
    email: string;
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
    parcels: ParcelData[];
    couriers: CourierData[];
}

export default function SortAssign({ auth, company, parcels = [], couriers = [] }: Props) {
    const [activeTab, setActiveTab] = useState('sort-assign');
    const [showUserMenu, setShowUserMenu] = useState(false);
    const [selectedParcels, setSelectedParcels] = useState<number[]>([]);
    const [selectedCourier, setSelectedCourier] = useState<string>('');

    const handleLogout = () => {
        router.post('/logout');
    };

    // Group parcels by barangay
    const groupedByBarangay = parcels.reduce((acc, parcel) => {
        const barangay = parcel.barangay;
        if (!acc[barangay]) {
            acc[barangay] = {
                name: barangay,
                parcels: [],
                totalWeight: 0,
                totalCount: 0,
            };
        }
        acc[barangay].parcels.push(parcel);
        acc[barangay].totalWeight += parcel.weight || 0;
        acc[barangay].totalCount += 1;
        return acc;
    }, {} as Record<string, { name: string; parcels: ParcelData[]; totalWeight: number; totalCount: number }>);

    const clusters = Object.values(groupedByBarangay);
    const totalParcels = parcels.length;
    const totalWeight = parcels.reduce((sum, p) => sum + (p.weight || 0), 0);

    const toggleParcelSelection = (id: number) => {
        if (selectedParcels.includes(id)) {
            setSelectedParcels(selectedParcels.filter(p => p !== id));
        } else {
            setSelectedParcels([...selectedParcels, id]);
        }
    };

    const toggleSelectAll = () => {
        if (selectedParcels.length === parcels.length) {
            setSelectedParcels([]);
        } else {
            setSelectedParcels(parcels.map(p => p.id));
        }
    };

    const handleAssign = () => {
        if (selectedParcels.length === 0) {
            alert('Please select at least one parcel');
            return;
        }
        if (!selectedCourier) {
            alert('Please select a courier');
            return;
        }
        alert(`Assigning ${selectedParcels.length} parcel(s) to ${selectedCourier}...`);
        // TODO: Call backend API to assign parcels
    };

    const navItems = [
        { id: 'dashboard', label: 'Dashboard', icon: 'grid', href: '/logistics/dashboard' },
        { id: 'incoming-parcels', label: 'Incoming Parcels', icon: 'inbox', href: '/logistics/incoming-parcels' },
        { id: 'sort-assign', label: 'Sort & Assign', icon: 'sort', href: '/logistics/sort-assign' },
        { id: 'courier-applications', label: 'Courier Applications', icon: 'file-text', href: '/logistics/courier-applications' },
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
            case 'file-text':
                return <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>;
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
            <Head title="Sort & Assign" />
            
            <style>{`
                .scrollbar-hide::-webkit-scrollbar { display: none; }
                .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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
                                <h2 className="text-base font-semibold text-gray-900">Sort & Assign</h2>
                            </div>
                            <div className="flex items-center gap-4">
                                <div className="relative">
                                    <input
                                        type="text"
                                        placeholder="Search parcels..."
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
                    <main className="flex-1 overflow-auto p-6 bg-gray-50">
                        <div className="max-w-7xl mx-auto space-y-6">
                            {/* Summary Cards */}
                            <div className="grid grid-cols-3 gap-6">
                                <div className="bg-white rounded-xl border border-gray-200 p-6">
                                    <div className="flex items-center gap-3 mb-3">
                                        <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                            </svg>
                                        </div>
                                        <div className="text-xs font-semibold text-gray-500 uppercase tracking-wide" style={fontSyncopate}>Total Parcels</div>
                                    </div>
                                    <div className="text-3xl font-bold text-gray-900">{totalParcels}</div>
                                    <div className="text-sm text-gray-600 mt-1">Ready for assignment</div>
                                </div>

                                <div className="bg-white rounded-xl border border-gray-200 p-6">
                                    <div className="flex items-center gap-3 mb-3">
                                        <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            </svg>
                                        </div>
                                        <div className="text-xs font-semibold text-gray-500 uppercase tracking-wide" style={fontSyncopate}>Total Weight</div>
                                    </div>
                                    <div className="text-3xl font-bold text-gray-900">{totalWeight.toFixed(1)} kg</div>
                                    <div className="text-sm text-gray-600 mt-1">Combined weight</div>
                                </div>

                                <div className="bg-white rounded-xl border border-gray-200 p-6">
                                    <div className="flex items-center gap-3 mb-3">
                                        <div className="w-10 h-10 bg-[#E8E3F0] rounded-lg flex items-center justify-center">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-[#5B4B6E]">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                        </div>
                                        <div className="text-xs font-semibold text-gray-500 uppercase tracking-wide" style={fontSyncopate}>Destinations</div>
                                    </div>
                                    <div className="text-3xl font-bold text-gray-900">{clusters.length}</div>
                                    <div className="text-sm text-gray-600 mt-1">Unique barangays</div>
                                </div>
                            </div>

                            {/* Main Content Grid */}
                            <div className="grid grid-cols-3 gap-6">
                                {/* Unassigned Parcels - Takes 2 columns */}
                                <div className="col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                                    <div className="flex items-center justify-between mb-6">
                                        <div>
                                            <h3 className="text-lg font-bold text-gray-900" style={fontSyncopate}>Unassigned Parcels</h3>
                                            <p className="text-sm text-gray-500">{totalParcels} parcels awaiting courier assignment</p>
                                        </div>
                                        <div className="flex gap-2">
                                            <button 
                                                onClick={toggleSelectAll}
                                                className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition"
                                            >
                                                {selectedParcels.length === parcels.length ? 'Deselect All' : 'Select All'}
                                            </button>
                                            <select className="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                                                <option className="text-gray-900">Filter by Barangay</option>
                                                {clusters.map((cluster, idx) => (
                                                    <option key={idx} value={cluster.name} className="text-gray-900">{cluster.name}</option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>

                                    {/* Parcels Table */}
                                    <div className="overflow-x-auto max-h-[600px] overflow-y-auto">
                                        {parcels.length > 0 ? (
                                            <table className="w-full">
                                                <thead className="bg-gray-50 border-b border-gray-200 sticky top-0">
                                                    <tr>
                                                        <th className="px-4 py-3 text-left">
                                                            <input 
                                                                type="checkbox" 
                                                                className="rounded border-gray-300"
                                                                checked={selectedParcels.length === parcels.length && parcels.length > 0}
                                                                onChange={toggleSelectAll}
                                                            />
                                                        </th>
                                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>Waybill</th>
                                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>Buyer</th>
                                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>Barangay</th>
                                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>Weight</th>
                                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600" style={fontSyncopate}>Payment</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-gray-100">
                                                    {parcels.map((parcel) => (
                                                        <tr key={parcel.id} className="hover:bg-gray-50 cursor-pointer">
                                                            <td className="px-4 py-3">
                                                                <input 
                                                                    type="checkbox" 
                                                                    className="rounded border-gray-300"
                                                                    checked={selectedParcels.includes(parcel.id)}
                                                                    onChange={() => toggleParcelSelection(parcel.id)}
                                                                />
                                                            </td>
                                                            <td className="px-4 py-3">
                                                                <div className="text-sm font-bold text-gray-900">{parcel.waybill_number}</div>
                                                                <div className="text-xs text-gray-500">{parcel.order_number}</div>
                                                            </td>
                                                            <td className="px-4 py-3">
                                                                <div className="text-sm text-gray-900">{parcel.buyer_name}</div>
                                                                <div className="text-xs text-gray-500">{parcel.buyer_phone}</div>
                                                            </td>
                                                            <td className="px-4 py-3">
                                                                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                    {parcel.barangay}
                                                                </span>
                                                            </td>
                                                            <td className="px-4 py-3">
                                                                <div className="text-sm text-gray-900">
                                                                    {parcel.weight > 0 ? `${parcel.weight} kg` : <span className="text-gray-400">No weight</span>}
                                                                </div>
                                                            </td>
                                                            <td className="px-4 py-3">
                                                                <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                                                    parcel.payment_method === 'Cash on Delivery' 
                                                                        ? 'bg-orange-100 text-orange-800' 
                                                                        : 'bg-green-100 text-green-800'
                                                                }`}>
                                                                    {parcel.payment_method === 'Cash on Delivery' ? 'COD' : 'PAID'}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        ) : (
                                            <div className="py-12 text-center text-gray-400">
                                                <svg className="w-12 h-12 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                </svg>
                                                <p className="text-sm">No unassigned parcels</p>
                                                <p className="text-xs text-gray-500 mt-1">Parcels will appear here after confirmation at incoming parcels</p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Right Column */}
                                <div className="space-y-6">
                                    {/* Courier Selection */}
                                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                                        <h3 className="text-lg font-bold text-gray-900 mb-4" style={fontSyncopate}>Courier Selection</h3>
                                        
                                        {selectedParcels.length > 0 && (
                                            <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <div className="text-sm font-medium text-blue-900">
                                                    {selectedParcels.length} parcel(s) selected
                                                </div>
                                            </div>
                                        )}

                                        <div className="space-y-3">
                                            <label className="text-sm font-medium text-gray-700">Select Courier</label>
                                            <select 
                                                className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2B2249] focus:border-transparent"
                                                value={selectedCourier}
                                                onChange={(e) => setSelectedCourier(e.target.value)}
                                            >
                                                <option value="" className="text-gray-900">-- Select Courier --</option>
                                                {couriers.length > 0 ? (
                                                    couriers.map((courier) => (
                                                        <option key={courier.id} value={courier.id} className="text-gray-900">
                                                            {courier.name}
                                                        </option>
                                                    ))
                                                ) : (
                                                    <option disabled className="text-gray-500">No approved couriers available</option>
                                                )}
                                            </select>

                                            <button
                                                onClick={handleAssign}
                                                disabled={selectedParcels.length === 0 || !selectedCourier}
                                                className={`w-full px-4 py-3 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2 ${
                                                    selectedParcels.length === 0 || !selectedCourier
                                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        : 'bg-[#2B2249] text-white hover:bg-[#3d2e5f]'
                                                }`}
                                            >
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <circle cx="5.5" cy="17.5" r="3.5"/>
                                                    <circle cx="18.5" cy="17.5" r="3.5"/>
                                                    <path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                                    <path d="M12 17.5V14l-3-3 4-3 2 3h2"/>
                                                </svg>
                                                Assign to Courier
                                            </button>
                                        </div>
                                    </div>

                                    {/* Assignment History */}
                                    <div className="bg-white rounded-xl border border-gray-200 p-6">
                                        <h3 className="text-lg font-bold text-gray-900 mb-4" style={fontSyncopate}>Assignment History</h3>
                                        
                                        <div className="space-y-3">
                                            <div className="text-sm text-gray-500 text-center py-8">
                                                <svg className="w-10 h-10 mx-auto mb-2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                <p>No assignments yet</p>
                                                <p className="text-xs text-gray-400 mt-1">Assignment history will appear here</p>
                                            </div>
                                        </div>
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

SortAssign.layout = (page: ReactNode) => page;

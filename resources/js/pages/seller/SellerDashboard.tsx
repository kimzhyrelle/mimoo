import { Head } from '@inertiajs/react';
import { AlertTriangle, Box, MessageSquare, Package, Star, TrendingUp } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import SellerShell from '@/layouts/seller/seller-shell';

const fontPoppins = { fontFamily: 'Poppins, sans-serif' };
const fontSyne = { fontFamily: "'Syne', sans-serif" };

interface RecentOrder {
    id: string;
    buyer: string;
    item: string;
    total: string;
    status: 'To Ship' | 'In Transit' | 'Delivered';
    date: string;
}

interface TopProduct {
    rank: number;
    name: string;
    unitsSold: number;
    revenue: string;
    sharePercent: number;
}

interface LowStockItem {
    name: string;
    reorderPoint: number;
    currentStock: number;
    percentage: number;
}

const recentOrders: RecentOrder[] = [
    { id: '#12489', buyer: 'Maria Clara Santos', item: 'Organic Virgin Coconut Oil 250ml ×2', total: '₱360.00', status: 'To Ship', date: 'Sep 16' },
    { id: '#12470', buyer: 'Angela Bautista', item: 'Vitamin C Serum 30ml ×1', total: '₱420.00', status: 'In Transit', date: 'Sep 14' },
    { id: '#12352', buyer: 'Miguel Cruz', item: 'Charcoal Soap Bar ×3', total: '₱285.00', status: 'Delivered', date: 'Sep 10' },
    { id: '#12290', buyer: 'Maria Clara Santos', item: 'Aloe Vera Gel 200ml ×1', total: '₱210.00', status: 'Delivered', date: 'Sep 5' },
];

const topProducts: TopProduct[] = [
    { rank: 1, name: 'Organic Virgin Coconut Oil 250ml', unitsSold: 820, revenue: '₱73,800', sharePercent: 40.5 },
    { rank: 2, name: 'Vitamin C Serum 30ml', unitsSold: 410, revenue: '₱61,500', sharePercent: 33.7 },
    { rank: 3, name: 'Charcoal Soap Bar', unitsSold: 520, revenue: '₱31,200', sharePercent: 17.1 },
    { rank: 4, name: 'Aloe Vera Gel 200ml', unitsSold: 140, revenue: '₱15,900', sharePercent: 8.7 },
];

const lowStockItems: LowStockItem[] = [
    { name: 'Charcoal Soap Bar', reorderPoint: 15, currentStock: 6, percentage: 40 },
    { name: 'Vitamin C Serum 30ml', reorderPoint: 10, currentStock: 3, percentage: 30 },
];

const TH = 'text-left text-[0.7rem] font-semibold uppercase tracking-wide text-[#6E6570] pb-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]';

export default function SellerDashboard() {
    const [chartPeriod, setChartPeriod] = useState<'daily' | 'monthly' | 'yearly'>('monthly');

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'To Ship': return 'text-[#a9752b]';
            case 'In Transit': return 'text-[#4B2E7E]';
            case 'Delivered': return 'text-[#2c7a52]';
            default: return 'text-gray-600';
        }
    };

    return (
        <SellerShell active="dashboard">
            <Head title="Seller Dashboard" />
            <div style={fontPoppins}>

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap');

                :root {
                    --deep: #2A1B4D;
                    --deep-2: color-mix(in srgb, var(--deep) 85%, black);
                    --primary: #4B2E7E;
                    --primary-hover: color-mix(in srgb, var(--primary) 82%, black);
                    --accent: color-mix(in srgb, var(--primary) 55%, #B9A6DE 45%);
                    --accent-soft: #B9A6DE;
                    --muted: #6E6570;
                    --lav-50: color-mix(in srgb, var(--accent-soft) 12%, white);
                    --lav-100: color-mix(in srgb, var(--accent-soft) 24%, white);
                    --lav-200: color-mix(in srgb, var(--accent-soft) 44%, white);
                    --gold: #a9752b;
                    --gold-bg: #f7ecd6;
                    --ok: #2c7a52;
                    --ok-bg: #e7f5ec;
                    --danger: #b3384f;
                    --danger-bg: #fbeced;
                    --logi: #2f6f8f;
                    --logi-bg: #e4eef3;
                }
            `}</style>

            {/* Store Header */}
            <div className="mb-6">
                <p className="text-[0.76rem] font-semibold text-[#4B2E7E] uppercase tracking-wider mb-1" style={fontPoppins}>Welcome back, Seller</p>
                <h1 className="text-[1.8rem] font-semibold leading-tight" style={fontSyne}>Your Store Dashboard</h1>
                <p className="text-sm text-[#6E6570] mt-1">Here is how your store is performing this month.</p>
            </div>

            {/* Stats Row */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-4" style={fontSyne}>Store overview</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Total Sales - Accent card */}
                    <div className="bg-gradient-to-br from-[#4B2E7E] to-[#B9A6DE] text-white rounded-xl p-5 border-0">
                        <div className="flex items-center gap-2 text-sm font-semibold opacity-80 mb-3" style={fontSyne}>
                            <TrendingUp className="w-4 h-4" />
                            <span>Total Sales</span>
                        </div>
                        <div className="flex items-end gap-2">
                            <span className="text-[1.7rem] font-extrabold leading-none" style={fontPoppins}>₱182,400</span>
                            <span className="text-xs font-extrabold bg-white/20 px-2 py-1 rounded-full">▲ 6.1%</span>
                        </div>
                        <p className="text-[0.76rem] opacity-75 mt-2">This month</p>
                    </div>

                    {/* Orders */}
                    <div className="bg-white rounded-xl p-5 border-2 border-[#E4EEF3] border-l-[3px] border-l-[#2f6f8f]">
                        <div className="flex items-center gap-2 text-sm font-semibold text-[#6E6570] mb-3" style={fontSyne}>
                            <Box className="w-4 h-4 text-[#2f6f8f]" />
                            <span>Orders</span>
                        </div>
                        <div className="text-[1.7rem] font-extrabold leading-none" style={fontPoppins}>1,240</div>
                        <p className="text-[0.76rem] text-[#6E6570] mt-2">Completed & in progress</p>
                    </div>

                    {/* Pending Orders */}
                    <div className="bg-white rounded-xl p-5 border-2 border-[#F7ECD6] border-l-[3px] border-l-[#a9752b]">
                        <div className="flex items-center gap-2 text-sm font-semibold text-[#6E6570] mb-3" style={fontSyne}>
                            <Package className="w-4 h-4 text-[#a9752b]" />
                            <span>Pending Orders</span>
                        </div>
                        <div className="text-[1.7rem] font-extrabold leading-none" style={fontPoppins}>8</div>
                        <p className="text-[0.76rem] text-[#6E6570] mt-2">Need packing today</p>
                    </div>

                    {/* Store Rating */}
                    <div className="bg-white rounded-xl p-5 border-2 border-[#E7F5EC] border-l-[3px] border-l-[#2c7a52]">
                        <div className="flex items-center gap-2 text-sm font-semibold text-[#6E6570] mb-3" style={fontSyne}>
                            <Star className="w-4 h-4 text-[#2c7a52]" />
                            <span>Store Rating</span>
                        </div>
                        <div className="text-[1.7rem] font-extrabold leading-none" style={fontPoppins}>4.9</div>
                        <p className="text-[0.76rem] text-[#6E6570] mt-2">From 312 reviews</p>
                    </div>
                </div>
            </div>

            {/* Sales Performance */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-4" style={fontSyne}>Sales performance</h2>
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    {/* Chart Card */}
                    <div className="lg:col-span-2 bg-white rounded-xl p-6 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                        <div className="flex items-start justify-between mb-4 flex-wrap gap-4">
                            <div>
                                <p className="text-xs text-[#6E6570] mb-1">Sales overview — last 6 months</p>
                            </div>
                            <div className="flex gap-1 bg-[color-mix(in_srgb,#B9A6DE_12%,white)] border border-[color-mix(in_srgb,#B9A6DE_44%,white)] rounded-lg p-1">
                                <button onClick={() => setChartPeriod('daily')} className={`text-xs font-bold px-3 py-2 rounded ${chartPeriod === 'daily' ? 'bg-[#4B2E7E] text-white' : 'text-[#6E6570]'}`}>Daily</button>
                                <button onClick={() => setChartPeriod('monthly')} className={`text-xs font-bold px-3 py-2 rounded ${chartPeriod === 'monthly' ? 'bg-[#4B2E7E] text-white' : 'text-[#6E6570]'}`}>Monthly</button>
                                <button onClick={() => setChartPeriod('yearly')} className={`text-xs font-bold px-3 py-2 rounded ${chartPeriod === 'yearly' ? 'bg-[#4B2E7E] text-white' : 'text-[#6E6570]'}`}>Yearly</button>
                            </div>
                        </div>

                        <div className="flex items-end gap-9 mb-4 flex-wrap">
                            <div>
                                <div className="text-2xl font-extrabold" style={fontPoppins}>₱182,400</div>
                                <div className="text-xs text-[#6E6570] mt-1">Sales this month</div>
                            </div>
                            <div>
                                <div className="text-xl font-extrabold" style={fontPoppins}>1,240</div>
                                <div className="text-xs text-[#6E6570] mt-1">Orders this month</div>
                            </div>
                        </div>

                        {/* Simplified Chart Placeholder */}
                        <div className="relative h-32 mb-2">
                            <svg className="w-full h-full" viewBox="0 0 600 150" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sellerRevFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stopColor="#4B2E7E" stopOpacity="0.18" />
                                        <stop offset="100%" stopColor="#4B2E7E" stopOpacity="0" />
                                    </linearGradient>
                                </defs>
                                <path d="M0,118 C60,118 60,108 120,108 C180,108 180,120 240,120 C300,120 300,92 360,92 C420,92 420,100 480,100 C540,100 540,55 600,55 L600,150 L0,150 Z" fill="url(#sellerRevFill)" />
                                <path d="M0,118 C60,118 60,108 120,108 C180,108 180,120 240,120 C300,120 300,92 360,92 C420,92 420,100 480,100 C540,100 540,55 600,55" fill="none" stroke="#4B2E7E" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                                <circle cx="600" cy="55" r="4.5" fill="#4B2E7E" />
                            </svg>
                            <div className="flex justify-between text-[0.7rem] text-[#6E6570] font-semibold mt-2 px-1">
                                <span>Apr</span><span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span>
                            </div>
                        </div>

                        {/* Sub Stats */}
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-5 mt-5 border-t border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                            <div className="pl-3 border-l-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                <div className="text-[0.72rem] text-[#6E6570] truncate">Avg. order value</div>
                                <div className="text-[0.95rem] font-extrabold mt-1" style={fontPoppins}>₱147.10</div>
                            </div>
                            <div className="pl-3 border-l-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                <div className="text-[0.72rem] text-[#6E6570] truncate">Items sold</div>
                                <div className="text-[0.95rem] font-extrabold mt-1" style={fontPoppins}>1,890</div>
                            </div>
                            <div className="pl-3 border-l-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                <div className="text-[0.72rem] text-[#6E6570] truncate">Active vouchers</div>
                                <div className="text-[0.95rem] font-extrabold mt-1" style={fontPoppins}>3</div>
                            </div>
                            <div className="pl-3 border-l-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                <div className="text-[0.72rem] text-[#6E6570] truncate">Awaiting courier</div>
                                <div className="text-[0.95rem] font-extrabold mt-1" style={fontPoppins}>5</div>
                            </div>
                        </div>
                    </div>

                    {/* Donut Card */}
                    <div className="bg-white rounded-xl p-6 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                        <div className="text-base font-semibold mb-1" style={fontSyne}>Order status</div>
                        <p className="text-xs text-[#6E6570] mb-5">Share of this month's orders</p>

                        {/* Donut SVG */}
                        <div className="flex items-center justify-center py-6">
                            <svg className="w-36 h-36" viewBox="0 0 42 42">
                                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="color-mix(in srgb, #B9A6DE 24%, white)" strokeWidth="6"></circle>
                                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#2c7a52" strokeWidth="6" strokeDasharray="60 40" strokeDashoffset="25" transform="rotate(-90 21 21)"></circle>
                                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#4B2E7E" strokeWidth="6" strokeDasharray="25 75" strokeDashoffset="-35" transform="rotate(-90 21 21)"></circle>
                                <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#a9752b" strokeWidth="6" strokeDasharray="15 85" strokeDashoffset="-60" transform="rotate(-90 21 21)"></circle>
                            </svg>
                        </div>

                        {/* Legend */}
                        <div className="flex justify-between gap-3 pt-4 border-t border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                            <div>
                                <div className="flex items-center gap-2 text-[0.74rem] text-[#6E6570] mb-1">
                                    <i className="w-2 h-2 rounded-full bg-[#2c7a52]"></i>
                                    <span>Delivered</span>
                                </div>
                                <div className="text-base font-extrabold" style={fontPoppins}>60%</div>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 text-[0.74rem] text-[#6E6570] mb-1">
                                    <i className="w-2 h-2 rounded-full bg-[#4B2E7E]"></i>
                                    <span>In transit</span>
                                </div>
                                <div className="text-base font-extrabold" style={fontPoppins}>25%</div>
                            </div>
                            <div>
                                <div className="flex items-center gap-2 text-[0.74rem] text-[#6E6570] mb-1">
                                    <i className="w-2 h-2 rounded-full bg-[#a9752b]"></i>
                                    <span>To ship</span>
                                </div>
                                <div className="text-base font-extrabold" style={fontPoppins}>15%</div>
                            </div>
                        </div>

                        <p className="text-[0.74rem] text-[#6E6570] mt-4">Based on 1,240 orders placed this month.</p>
                    </div>
                </div>
            </div>

            {/* Needs Your Attention */}
            <div className="mb-6">
                <h2 className="text-lg font-semibold mb-4" style={fontSyne}>Needs your attention</h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* To Prepare */}
                    <a href="#" className="flex items-center gap-4 bg-white rounded-xl p-4 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] hover:border-[#B9A6DE] hover:-translate-y-0.5 hover:shadow-lg transition-all">
                        <div className="relative flex-none w-12 h-12 bg-[#f7ecd6] text-[#a9752b] rounded-xl flex items-center justify-center">
                            <Box className="w-5 h-5" />
                            <span className="absolute -top-2 -right-2 min-w-[21px] h-5 px-1 bg-[#a9752b] text-white text-[0.7rem] font-extrabold rounded-full flex items-center justify-center border-2 border-white" style={fontPoppins}>8</span>
                        </div>
                        <div>
                            <div className="text-sm font-semibold" style={fontSyne}>To Prepare</div>
                            <div className="text-xs text-[#6E6570]">Orders awaiting packing</div>
                        </div>
                    </a>

                    {/* Low Stock */}
                    <a href="#" className="flex items-center gap-4 bg-white rounded-xl p-4 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] hover:border-[#B9A6DE] hover:-translate-y-0.5 hover:shadow-lg transition-all">
                        <div className="relative flex-none w-12 h-12 bg-[#f7ecd6] text-[#a9752b] rounded-xl flex items-center justify-center">
                            <AlertTriangle className="w-5 h-5" />
                            <span className="absolute -top-2 -right-2 min-w-[21px] h-5 px-1 bg-[#a9752b] text-white text-[0.7rem] font-extrabold rounded-full flex items-center justify-center border-2 border-white" style={fontPoppins}>2</span>
                        </div>
                        <div>
                            <div className="text-sm font-semibold" style={fontSyne}>Low Stock</div>
                            <div className="text-xs text-[#6E6570]">Products below reorder point</div>
                        </div>
                    </a>

                    {/* New Messages */}
                    <a href="#" className="flex items-center gap-4 bg-white rounded-xl p-4 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] hover:border-[#B9A6DE] hover:-translate-y-0.5 hover:shadow-lg transition-all">
                        <div className="relative flex-none w-12 h-12 bg-[#e4eef3] text-[#2f6f8f] rounded-xl flex items-center justify-center">
                            <MessageSquare className="w-5 h-5" />
                            <span className="absolute -top-2 -right-2 min-w-[21px] h-5 px-1 bg-[#2f6f8f] text-white text-[0.7rem] font-extrabold rounded-full flex items-center justify-center border-2 border-white" style={fontPoppins}>3</span>
                        </div>
                        <div>
                            <div className="text-sm font-semibold" style={fontSyne}>New Messages</div>
                            <div className="text-xs text-[#6E6570]">From buyers & support</div>
                        </div>
                    </a>

                    {/* New Reviews */}
                    <a href="#" className="flex items-center gap-4 bg-white rounded-xl p-4 border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] hover:border-[#B9A6DE] hover:-translate-y-0.5 hover:shadow-lg transition-all">
                        <div className="relative flex-none w-12 h-12 bg-[#e7f5ec] text-[#2c7a52] rounded-xl flex items-center justify-center">
                            <Star className="w-5 h-5" />
                            <span className="absolute -top-2 -right-2 min-w-[21px] h-5 px-1 bg-[#2c7a52] text-white text-[0.7rem] font-extrabold rounded-full flex items-center justify-center border-2 border-white" style={fontPoppins}>1</span>
                        </div>
                        <div>
                            <div className="text-sm font-semibold" style={fontSyne}>New Reviews</div>
                            <div className="text-xs text-[#6E6570]">Awaiting your reply</div>
                        </div>
                    </a>
                </div>
            </div>

            {/* Recent Orders */}
            <div className="mb-6">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-lg font-semibold" style={fontSyne}>Recent orders</h2>
                    <a href="#" className="text-sm font-bold text-[#4B2E7E] hover:text-[color-mix(in_srgb,#4B2E7E_82%,black)] hover:underline">View all orders</a>
                </div>
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 overflow-x-auto">
                    <table className="w-full min-w-[720px] border-collapse">
                        <thead>
                            <tr>
                                <th className={TH} style={fontPoppins}>Order</th>
                                <th className={TH} style={fontPoppins}>Buyer</th>
                                <th className={TH} style={fontPoppins}>Item</th>
                                <th className={TH} style={fontPoppins}>Total</th>
                                <th className={TH} style={fontPoppins}>Status</th>
                                <th className={TH} style={fontPoppins}>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentOrders.map((order) => (
                                <tr key={order.id}>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] font-bold" style={fontPoppins}>{order.id}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] text-[#6E6570] text-sm">{order.buyer}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] text-[#6E6570] text-sm">{order.item}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]" style={fontPoppins}>{order.total}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                        <span className={`text-xs font-bold uppercase tracking-wide ${getStatusColor(order.status)}`} style={fontPoppins}>{order.status}</span>
                                    </td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] text-[#6E6570] text-sm" style={fontPoppins}>{order.date}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Products & Inventory */}
            <div>
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-lg font-semibold" style={fontSyne}>Products & inventory</h2>
                    <a href="#" className="text-sm font-bold text-[#4B2E7E] hover:text-[color-mix(in_srgb,#4B2E7E_82%,black)] hover:underline">Manage inventory</a>
                </div>

                {/* Top Products */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6 mb-5 overflow-x-auto">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-semibold" style={fontSyne}>Top products</h3>
                        <span className="text-xs text-[#6E6570]">Ranked by revenue this month</span>
                    </div>
                    <table className="w-full border-collapse">
                        <thead>
                            <tr>
                                <th className={`${TH} w-11`} style={fontSyne}>#</th>
                                <th className={TH} style={fontPoppins}>Product</th>
                                <th className={TH} style={fontPoppins}>Units sold</th>
                                <th className={TH} style={fontPoppins}>Revenue</th>
                                <th className={`${TH} hidden md:table-cell`} style={fontSyne}>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            {topProducts.map((product) => (
                                <tr key={product.rank}>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]">
                                        <span className="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] text-xs font-extrabold" style={fontPoppins}>{product.rank}</span>
                                    </td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] font-bold text-sm" style={fontPoppins}>{product.name}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]" style={fontPoppins}>{product.unitsSold}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)]" style={fontPoppins}>{product.revenue}</td>
                                    <td className="py-3 border-b border-[color-mix(in_srgb,#B9A6DE_44%,white)] hidden md:table-cell">
                                        <div className="flex items-center gap-3 min-w-[150px]">
                                            <div className="flex-1 h-2 bg-[color-mix(in_srgb,#B9A6DE_24%,white)] rounded-full overflow-hidden">
                                                <div className="h-full bg-gradient-to-r from-[#B9A6DE] to-[#4B2E7E] rounded-full" style={{ width: `${product.sharePercent}%` }}></div>
                                            </div>
                                            <span className="text-xs text-[#6E6570] w-11 text-right" style={fontPoppins}>{product.sharePercent}%</span>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Low Stock Alerts */}
                <div className="bg-white rounded-xl border-2 border-[color-mix(in_srgb,#B9A6DE_44%,white)] p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-semibold" style={fontSyne}>Low stock alerts</h3>
                        <span className="text-xs text-[#6E6570]">2 products are below their reorder point</span>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4">
                        {lowStockItems.map((item, idx) => (
                            <div key={idx} className="flex items-center justify-between gap-4">
                                <div className="flex-1">
                                    <div className="font-bold text-sm mb-1">{item.name}</div>
                                    <div className="text-xs text-[#6E6570] mb-2">Reorder point: {item.reorderPoint} units</div>
                                    <div className="h-1.5 bg-[color-mix(in_srgb,#B9A6DE_24%,white)] rounded-full overflow-hidden">
                                        <div className={`h-full rounded-full ${item.currentStock <= 5 ? 'bg-[#b3384f]' : 'bg-[#a9752b]'}`} style={{ width: `${item.percentage}%` }}></div>
                                    </div>
                                </div>
                                <div className={`text-sm font-extrabold whitespace-nowrap ${item.currentStock <= 5 ? 'text-[#b3384f]' : 'text-[#a9752b]'}`} style={fontPoppins}>
                                    {item.currentStock} left
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
            </div>
        </SellerShell>
    );
}

SellerDashboard.layout = (page: ReactNode) => page;
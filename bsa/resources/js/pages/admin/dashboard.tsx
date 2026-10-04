import { Head } from '@inertiajs/react';
import {
    BarChart3,
    ChevronUp,
    CircleDollarSign,
    ClipboardList,
    Minus,
    ShoppingBag,
    Truck,
    Users,
    Zap,
} from 'lucide-react';
import type { ReactNode } from 'react';
import AdminShell from '@/layouts/admin/admin-shell';

// ─── Helpers ──────────────────────────────────────────────────────────────────

function SectionLabel({ children }: { children: ReactNode }) {
    return <h2 className="mb-4 text-sm font-semibold text-gray-700">{children}</h2>;
}

function EmptyValue({ label }: { label: string }) {
    return (
        <div className="flex flex-col">
            <span className="text-2xl font-bold text-gray-800">—</span>
            <span className="mt-1 flex items-center gap-1 text-xs text-gray-400">
                <Minus className="h-3 w-3" />
                {label}
            </span>
        </div>
    );
}

function StatCard({ label, icon: Icon, color }: { label: string; icon: React.ElementType; color: string }) {
    return (
        <div className="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div className="flex items-center gap-2">
                <Icon className={`h-4 w-4 ${color}`} strokeWidth={1.5} />
                <span className="text-[11px] font-semibold uppercase tracking-wider text-gray-500">{label}</span>
            </div>
            <EmptyValue label="No data yet" />
        </div>
    );
}

function AttentionCard({ label, action, badge }: { label: string; action: string; badge?: string }) {
    return (
        <div className="flex flex-col justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p className="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{label}</p>
                <p className="mt-1 text-2xl font-bold text-gray-800">—</p>
                {badge && (
                    <span className="mt-1 inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                        <ChevronUp className="h-3 w-3 text-emerald-400" />
                        {badge}
                    </span>
                )}
            </div>
            <button className="mt-3 text-left text-xs font-medium text-[#3B1F6B] hover:underline">{action}</button>
        </div>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function AdminDashboard() {
    return (
        <>
            <Head title="Admin Dashboard" />
            <AdminShell active="dashboard">
                {/* Welcome */}
                <div className="mb-8">
                    <h1 className="text-xl font-bold text-gray-900">Welcome back, Admin 👋</h1>
                    <p className="mt-0.5 text-sm text-gray-400">Here's what's happening across Mimoo today.</p>
                </div>

                {/* Stat cards */}
                <div className="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-5">
                    <StatCard label="Total Buyers"   icon={Users}              color="text-blue-500"    />
                    <StatCard label="Total Sellers"  icon={ShoppingBag}        color="text-emerald-500" />
                    <StatCard label="Total Couriers" icon={Truck}              color="text-violet-500"  />
                    <StatCard label="Total Orders"   icon={ClipboardList}      color="text-orange-500"  />
                    <StatCard label="Revenue (MTD)"  icon={CircleDollarSign}   color="text-pink-500"    />
                </div>

                {/* Needs attention */}
                <div className="mb-8">
                    <SectionLabel>Needs your attention</SectionLabel>
                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <AttentionCard label="Pending Registrations" action="Review applications"   badge="0 today"   />
                        <AttentionCard label="Compliance Flags Open"  action="Review flags"          badge="0 new"     />
                        <AttentionCard label="Open Disputes"          action="Review disputes"       badge="0 overdue" />
                        <AttentionCard label="Commission This Month"  action="Open commission report"                  />
                    </div>
                </div>

                {/* Financial overview */}
                <div className="mb-8">
                    <SectionLabel>Financial overview</SectionLabel>
                    <div className="grid grid-cols-3 gap-4">
                        <div className="col-span-2 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <div className="mb-4 flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-gray-400">Commission trend — No data yet</p>
                                    <p className="mt-1 text-xl font-bold text-gray-800">₱0</p>
                                    <p className="text-xs text-gray-400">Commission earned this month</p>
                                </div>
                                <div className="flex gap-1">
                                    {['Daily', 'Monthly', 'Yearly'].map((t) => (
                                        <button
                                            key={t}
                                            className={[
                                                'rounded-md px-2.5 py-1 text-xs font-medium',
                                                t === 'Monthly' ? 'bg-[#3B1F6B] text-white' : 'text-gray-500 hover:bg-gray-100',
                                            ].join(' ')}
                                        >
                                            {t}
                                        </button>
                                    ))}
                                </div>
                            </div>
                            <div className="flex h-36 items-center justify-center rounded-lg border border-dashed border-gray-200 bg-gray-50">
                                <div className="flex flex-col items-center gap-2 text-gray-300">
                                    <BarChart3 className="h-8 w-8" strokeWidth={1} />
                                    <span className="text-xs">No chart data yet</span>
                                </div>
                            </div>
                            <div className="mt-4 grid grid-cols-4 gap-3">
                                {[
                                    { label: 'Platform balance',     value: '₱0' },
                                    { label: 'Pending payouts',      value: '₱0' },
                                    { label: 'Projected commission', value: '₱0' },
                                    { label: 'Net earnings',         value: '₱0' },
                                ].map(({ label, value }) => (
                                    <div key={label}>
                                        <p className="text-[10px] text-gray-400">{label}</p>
                                        <p className="text-sm font-semibold text-gray-700">{value}</p>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p className="text-xs font-semibold text-gray-700">Revenue by module</p>
                            <p className="mb-4 text-[10px] text-gray-400">Share of commission, this month</p>
                            <div className="flex items-center justify-center py-6">
                                <div className="relative flex h-24 w-24 items-center justify-center">
                                    <svg viewBox="0 0 36 36" className="h-24 w-24 -rotate-90">
                                        <circle cx="18" cy="18" r="14" fill="none" stroke="#E5E7EB" strokeWidth="4" />
                                    </svg>
                                    <span className="absolute text-[10px] font-medium text-gray-400">No data</span>
                                </div>
                            </div>
                            <div className="space-y-1.5">
                                {[
                                    { label: 'Buyer orders', color: 'bg-[#3B1F6B]' },
                                    { label: 'Seller fees',  color: 'bg-[#9B5DE5]' },
                                    { label: 'Logistics',    color: 'bg-amber-400'  },
                                ].map(({ label, color }) => (
                                    <div key={label} className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <span className={`h-2 w-2 rounded-full ${color}`} />
                                            <span className="text-xs text-gray-500">{label}</span>
                                        </div>
                                        <span className="text-xs font-medium text-gray-400">—</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Live operations */}
                <div>
                    <SectionLabel>Live operations</SectionLabel>
                    <div className="grid grid-cols-3 gap-4">
                        {[
                            { label: 'Active deliveries',    icon: Truck        },
                            { label: 'Orders in progress',   icon: ShoppingBag  },
                            { label: 'Support tickets open', icon: Zap          },
                        ].map(({ label, icon: Icon }) => (
                            <div key={label} className="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                                <div className="flex items-center gap-2">
                                    <Icon className="h-4 w-4 text-[#9B5DE5]" strokeWidth={1.5} />
                                    <span className="text-xs font-semibold text-gray-500">{label}</span>
                                </div>
                                <p className="text-2xl font-bold text-gray-800">—</p>
                                <div className="h-1.5 w-full rounded-full bg-gray-100">
                                    <div className="h-1.5 w-0 rounded-full bg-[#9B5DE5]" />
                                </div>
                                <p className="text-xs text-gray-400">No live data yet</p>
                            </div>
                        ))}
                    </div>
                </div>
            </AdminShell>
        </>
    );
}

AdminDashboard.layout = (page: React.ReactNode) => page;

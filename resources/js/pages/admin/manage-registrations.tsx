import { Head, router } from '@inertiajs/react';
import {
    CheckCircle2,
    ChevronDown,
    ClipboardList,
    Eye,
    Search,
    ThumbsDown,
    ThumbsUp,
    Timer,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';
import AdminShell from '@/layouts/admin/admin-shell';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Registration {
    id: number;
    name: string;
    email: string;
    account_type: string;
    business_name: string | null;
    status: 'pending' | 'approved' | 'disapproved';
    created_at: string;
    approved_at: string | null;
}

interface Stats {
    total: number;
    pending: number;
    approved: number;
    disapproved: number;
}

interface Props {
    registrations: Registration[];
    stats: Stats;
}

// ─── Stat cards ───────────────────────────────────────────────────────────────

function StatCards({ stats }: { stats: Stats }) {
    const statCards = [
        {
            label: 'Total Applications',
            icon: ClipboardList,
            value: stats.total.toString(),
            sub: 'All time',
            accent: 'bg-[#3B1F6B]',
            iconColor: 'text-white',
            dark: true,
        },
        {
            label: 'Pending Review',
            icon: Timer,
            value: stats.pending.toString(),
            sub: 'Awaiting action',
            accent: 'bg-white',
            iconColor: 'text-amber-500',
            dark: false,
        },
        {
            label: 'Approved',
            icon: CheckCircle2,
            value: stats.approved.toString(),
            sub: 'Active accounts',
            accent: 'bg-white',
            iconColor: 'text-emerald-500',
            dark: false,
        },
        {
            label: 'Disapproved',
            icon: XCircle,
            value: stats.disapproved.toString(),
            sub: 'Rejected',
            accent: 'bg-white',
            iconColor: 'text-red-400',
            dark: false,
        },
    ];

    return (
        <div className="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            {statCards.map(({ label, icon: Icon, value, sub, accent, iconColor, dark }) => (
                <div
                    key={label}
                    className={`flex flex-col gap-1 rounded-lg border p-4 shadow-sm ${accent} ${dark ? 'border-[#3B1F6B]' : 'border-gray-200'}`}
                >
                    <div className="flex items-center gap-2">
                        <Icon className={`h-4 w-4 ${iconColor}`} strokeWidth={1.5} />
                        <span className={`text-[11px] font-semibold uppercase tracking-wider ${dark ? 'text-white/70' : 'text-gray-500'}`}>
                            {label}
                        </span>
                    </div>
                    <p className={`text-3xl font-bold ${dark ? 'text-white' : 'text-gray-800'}`}>{value}</p>
                    <p className={`text-xs ${dark ? 'text-white/50' : 'text-gray-400'}`}>{sub}</p>
                </div>
            ))}
        </div>
    );
}

// ─── Table columns ────────────────────────────────────────────────────────────

const columns = ['Applicant', 'Role', 'Business / Permit', 'Documents', 'Date Applied', 'Status', 'Action'];

// ─── Status badge ─────────────────────────────────────────────────────────────

function StatusBadge({ status }: { status: 'pending' | 'approved' | 'disapproved' }) {
    const map = {
        pending:     'text-amber-600  bg-amber-50  border-amber-200',
        approved:    'text-emerald-600 bg-emerald-50 border-emerald-200',
        disapproved: 'text-red-500    bg-red-50    border-red-200',
    };
    return (
        <span className={`inline-block rounded border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${map[status]}`}>
            {status}
        </span>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function ManageRegistrations({ registrations, stats }: Props) {
    const [roleFilter, setRoleFilter] = useState('All roles');
    const [statusFilter, setStatusFilter] = useState('All');
    const [search, setSearch] = useState('');

    // Filter registrations
    const filteredRegistrations = registrations.filter((reg) => {
        const matchesRole =
            roleFilter === 'All roles' ||
            reg.account_type.toLowerCase() === roleFilter.toLowerCase();
        const matchesStatus =
            statusFilter === 'All' ||
            reg.status.toLowerCase() === statusFilter.toLowerCase();
        const matchesSearch =
            search === '' ||
            reg.name.toLowerCase().includes(search.toLowerCase()) ||
            reg.email.toLowerCase().includes(search.toLowerCase()) ||
            reg.business_name?.toLowerCase().includes(search.toLowerCase());

        return matchesRole && matchesStatus && matchesSearch;
    });

    function handleApprove(userId: number) {
        if (confirm('Are you sure you want to approve this registration?')) {
            router.post(`/admin/registrations/${userId}/approve`);
        }
    }

    function handleDisapprove(userId: number) {
        if (confirm('Are you sure you want to disapprove this registration?')) {
            router.post(`/admin/registrations/${userId}/disapprove`);
        }
    }

    return (
        <>
            <Head title="Manage Registrations" />
            <AdminShell active="manage-registrations">
                {/* Header row */}
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-xl font-bold text-gray-900">Manage Account Registrations</h1>
                    <div className="flex items-center gap-2">
                        {/* Role filter */}
                        <div className="relative">
                            <select
                                value={roleFilter}
                                onChange={(e) => setRoleFilter(e.target.value)}
                                className="h-8 appearance-none rounded-md border border-gray-200 bg-white pl-3 pr-8 text-xs text-gray-600 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/30"
                            >
                                {['All roles', 'Buyer', 'Seller'].map((r) => (
                                    <option key={r}>{r}</option>
                                ))}
                            </select>
                            <ChevronDown className="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                        </div>

                        {/* Status filter */}
                        <div className="relative">
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="h-8 appearance-none rounded-md border border-gray-200 bg-white pl-3 pr-8 text-xs text-gray-600 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/30"
                            >
                                {['All', 'Pending', 'Approved', 'Disapproved'].map((s) => (
                                    <option key={s}>{s}</option>
                                ))}
                            </select>
                            <ChevronDown className="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                        </div>
                    </div>
                </div>

                {/* Stat cards */}
                <StatCards stats={stats} />

                {/* Table card */}
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    {/* Table header */}
                    <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <div>
                            <p className="text-sm font-semibold text-gray-800">Registration applications</p>
                            <p className="mt-0.5 text-xs text-gray-400">
                                Sorted by newest first — tap the review icon to see full submitted details.
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            {/* Search */}
                            <div className="flex h-8 w-56 items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3">
                                <Search className="h-3.5 w-3.5 text-gray-400" strokeWidth={1.5} />
                                <input
                                    type="text"
                                    placeholder="Search by name or business…"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full bg-transparent text-xs text-gray-600 placeholder-gray-400 outline-none"
                                />
                            </div>
                            {/* Sort */}
                            <div className="relative">
                                <select className="h-8 appearance-none rounded-md border border-gray-200 bg-white pl-3 pr-7 text-xs text-gray-600 shadow-sm focus:outline-none">
                                    <option>Newest first</option>
                                    <option>Oldest first</option>
                                </select>
                                <ChevronDown className="pointer-events-none absolute right-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100 bg-gray-50">
                                    {columns.map((col) => (
                                        <th
                                            key={col}
                                            className="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400"
                                        >
                                            {col}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {filteredRegistrations.length === 0 ? (
                                    /* Empty state */
                                    <tr>
                                        <td colSpan={columns.length} className="px-5 py-16 text-center">
                                            <div className="flex flex-col items-center gap-3 text-gray-300">
                                                <ThumbsDown className="h-8 w-8" strokeWidth={1} />
                                                <p className="text-sm font-medium text-gray-400">
                                                    {search || roleFilter !== 'All roles' || statusFilter !== 'All'
                                                        ? 'No matching registrations'
                                                        : 'No registrations yet'}
                                                </p>
                                                <p className="text-xs text-gray-300">
                                                    {search || roleFilter !== 'All roles' || statusFilter !== 'All'
                                                        ? 'Try adjusting your filters or search query.'
                                                        : 'Applications will appear here once users start signing up.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    filteredRegistrations.map((reg) => (
                                        <tr key={reg.id} className="border-b border-gray-100 hover:bg-gray-50">
                                            {/* Applicant */}
                                            <td className="px-5 py-4">
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-medium text-gray-800">{reg.name}</span>
                                                    <span className="text-xs text-gray-400">{reg.email}</span>
                                                </div>
                                            </td>

                                            {/* Role */}
                                            <td className="px-5 py-4">
                                                <span className="inline-block rounded bg-gray-100 px-2 py-1 text-xs font-medium capitalize text-gray-600">
                                                    {reg.account_type}
                                                </span>
                                            </td>

                                            {/* Business / Permit */}
                                            <td className="px-5 py-4">
                                                <span className="text-xs text-gray-600">
                                                    {reg.business_name || '—'}
                                                </span>
                                            </td>

                                            {/* Documents */}
                                            <td className="px-5 py-4">
                                                <button className="text-xs text-[#3B1F6B] hover:underline">
                                                    View docs
                                                </button>
                                            </td>

                                            {/* Date Applied */}
                                            <td className="px-5 py-4">
                                                <span className="text-xs text-gray-500">{reg.created_at}</span>
                                            </td>

                                            {/* Status */}
                                            <td className="px-5 py-4">
                                                <StatusBadge status={reg.status} />
                                            </td>

                                            {/* Action */}
                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-2">
                                                    {reg.status === 'pending' && (
                                                        <>
                                                            <button
                                                                onClick={() => handleApprove(reg.id)}
                                                                className="flex h-7 w-7 items-center justify-center rounded border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                                                title="Approve"
                                                            >
                                                                <ThumbsUp className="h-3.5 w-3.5" strokeWidth={1.5} />
                                                            </button>
                                                            <button
                                                                onClick={() => handleDisapprove(reg.id)}
                                                                className="flex h-7 w-7 items-center justify-center rounded border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100"
                                                                title="Disapprove"
                                                            >
                                                                <XCircle className="h-3.5 w-3.5" strokeWidth={1.5} />
                                                            </button>
                                                        </>
                                                    )}
                                                    {reg.status === 'approved' && (
                                                        <button
                                                            onClick={() => handleDisapprove(reg.id)}
                                                            className="flex h-7 w-7 items-center justify-center rounded border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100"
                                                            title="Revoke approval"
                                                        >
                                                            <XCircle className="h-3.5 w-3.5" strokeWidth={1.5} />
                                                        </button>
                                                    )}
                                                    {reg.status === 'disapproved' && (
                                                        <button
                                                            onClick={() => handleApprove(reg.id)}
                                                            className="flex h-7 w-7 items-center justify-center rounded border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                                            title="Approve"
                                                        >
                                                            <ThumbsUp className="h-3.5 w-3.5" strokeWidth={1.5} />
                                                        </button>
                                                    )}
                                                    <button
                                                        className="flex h-7 w-7 items-center justify-center rounded border border-gray-200 bg-white text-gray-400 transition hover:bg-gray-50"
                                                        title="View details"
                                                    >
                                                        <Eye className="h-3.5 w-3.5" strokeWidth={1.5} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Table footer / pagination stub */}
                    <div className="flex items-center justify-between border-t border-gray-100 px-5 py-3">
                        <p className="text-xs text-gray-400">
                            Showing {filteredRegistrations.length} of {registrations.length} result
                            {registrations.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                </div>

                {/* Status badge legend */}
                <div className="mt-4 flex items-center gap-4 px-1">
                    {(['pending', 'approved', 'disapproved'] as const).map((s) => (
                        <div key={s} className="flex items-center gap-1.5">
                            <StatusBadge status={s} />
                        </div>
                    ))}
                    <span className="ml-1 text-xs text-gray-300">— status indicators</span>
                    <Eye className="ml-auto h-3.5 w-3.5 text-gray-300" strokeWidth={1.5} />
                    <span className="text-xs text-gray-300">review icon</span>
                </div>
            </AdminShell>
        </>
    );
}

ManageRegistrations.layout = (page: React.ReactNode) => page;

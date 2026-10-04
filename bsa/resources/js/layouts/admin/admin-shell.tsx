import { Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    Bell,
    CircleDollarSign,
    ClipboardList,
    Cog,
    FileText,
    LayoutDashboard,
    LogOut,
    MessageSquare,
    ShieldCheck,
    Truck,
    UserCheck,
    Users,
} from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

// ─── Nav definition ───────────────────────────────────────────────────────────

export type AdminNavKey =
    | 'dashboard'
    | 'manage-registrations'
    | 'user-accounts'
    | 'seller-compliance'
    | 'complaints-disputes'
    | 'rider-logistics'
    | 'commissions'
    | 'reports'
    | 'platform-settings'
    | 'chat-messaging'
    | 'account-management';

const navItems: { key: AdminNavKey; label: string; icon: React.ElementType; href: string }[] = [
    { key: 'dashboard',              label: 'Dashboard',              icon: LayoutDashboard, href: '/admin/dashboard'              },
    { key: 'manage-registrations',   label: 'Registrations',          icon: ClipboardList,   href: '/admin/manage-registrations'   },
    { key: 'user-accounts',          label: 'User Accounts',          icon: Users,           href: '/admin/user-accounts'          },
    { key: 'seller-compliance',      label: 'Seller Compliance',      icon: UserCheck,       href: '/admin/seller-compliance'      },
    { key: 'complaints-disputes',    label: 'Complaints & Disputes',  icon: AlertTriangle,   href: '/admin/complaints-disputes',   },
    { key: 'rider-logistics',        label: 'Rider & Logistics',      icon: Truck,           href: '/admin/rider-logistics'        },
    { key: 'commissions',            label: 'Commissions',            icon: CircleDollarSign,href: '/admin/commissions'            },
    { key: 'reports',                label: 'Reports',                icon: FileText,        href: '/admin/reports'                },
    { key: 'platform-settings',      label: 'Platform Settings',      icon: Cog,             href: '/admin/platform-settings'      },
    { key: 'chat-messaging',         label: 'Chat / Messaging',       icon: MessageSquare,   href: '/admin/chat-messaging'         },
    { key: 'account-management',     label: 'Account Management',     icon: ShieldCheck,     href: '/admin/account-management'     },
];

// ─── Shell ────────────────────────────────────────────────────────────────────

interface AdminShellProps {
    active: AdminNavKey;
    children: ReactNode;
}

export default function AdminShell({ active, children }: AdminShellProps) {
    function handleLogout(e: FormEvent) {
        e.preventDefault();
        router.post('/admin/logout');
    }

    return (
        <div
            className="flex h-screen overflow-hidden bg-gray-50 text-gray-900"
            style={{ fontFamily: "'Syne', ui-sans-serif, system-ui, sans-serif" }}
        >
            {/* ── Sidebar ──────────────────────────────────────────── */}
            <aside className="flex w-[224px] shrink-0 flex-col bg-[#1E1B2E] text-white" style={{ fontFamily: "'Syne', ui-sans-serif, system-ui, sans-serif" }}>
                {/* Logo */}
                <div className="flex h-14 items-center gap-2 border-b border-white/10 px-5">
                    <span className="h-5 w-5 rounded-full bg-[#9B5DE5]" />
                    <span
                        className="text-base font-extrabold uppercase tracking-widest"
                        style={{ fontFamily: "'Syncopate', ui-sans-serif, system-ui, sans-serif" }}
                    >
                        mimoo.
                    </span>
                </div>

                {/* Nav */}
                <nav className="flex-1 overflow-y-auto px-2 py-4">
                    {navItems.map(({ key, label, icon: Icon, href }) => (
                        <Link
                            key={key}
                            href={href}
                            className={[
                                'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition-colors',
                                active === key
                                    ? 'bg-[#3B1F6B] font-semibold text-white'
                                    : 'text-white/60 hover:bg-white/5 hover:text-white/90',
                            ].join(' ')}
                        >
                            <Icon className="h-4 w-4 shrink-0" strokeWidth={1.5} />
                            <span className="truncate">{label}</span>
                        </Link>
                    ))}
                </nav>

                {/* Logout */}
                <div className="border-t border-white/10 px-2 py-3">
                    <form onSubmit={handleLogout}>
                        <button
                            type="submit"
                            className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-white/50 transition-colors hover:bg-white/5 hover:text-white/80"
                        >
                            <LogOut className="h-4 w-4 shrink-0" strokeWidth={1.5} />
                            Logout
                        </button>
                    </form>
                    <div className="mt-2 flex items-center gap-2 px-3 py-2">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#9B5DE5] text-[10px] font-bold">
                            A
                        </span>
                        <span className="truncate text-xs text-white/40">MIMOO ADMIN</span>
                    </div>
                </div>
            </aside>

            {/* ── Main area ─────────────────────────────────────────── */}
            <div className="flex flex-1 flex-col overflow-hidden">
                {/* Top bar */}
                <header className="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6">
                    <div className="flex h-8 w-72 items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 text-sm text-gray-400">
                        <svg className="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z" />
                        </svg>
                        Search orders, sellers, applications…
                    </div>

                    <div className="flex items-center gap-3">
                        <button className="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50">
                            <MessageSquare className="h-4 w-4" strokeWidth={1.5} />
                        </button>
                        <button className="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50">
                            <Bell className="h-4 w-4" strokeWidth={1.5} />
                        </button>
                        <button className="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50">
                            <Cog className="h-4 w-4" strokeWidth={1.5} />
                        </button>
                        <div className="flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1">
                            <span className="flex h-6 w-6 items-center justify-center rounded-full bg-[#3B1F6B] text-[10px] font-bold text-white">
                                KF
                            </span>
                            <span className="text-xs font-semibold text-gray-700">Super Admin</span>
                        </div>
                    </div>
                </header>

                {/* Page content (scrollable) */}
                <main className="flex-1 overflow-y-auto px-8 pt-10 pb-16">
                    {children}
                </main>
            </div>
        </div>
    );
}

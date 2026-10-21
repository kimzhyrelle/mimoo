import { Head } from '@inertiajs/react';
import SellerShell from '@/layouts/seller/seller-shell';
import { Users, Search, Filter, Download } from 'lucide-react';

export default function SellerCustomers() {
    return (
        <>
            <Head title="Customers - Seller Dashboard" />
            <SellerShell active="customers">
                <div className="mx-auto max-w-[1400px]">
                    {/* Header */}
                    <div className="mb-8 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900" style={{ fontFamily: "'Syne', sans-serif" }}>
                                Customers
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                View and manage your customer relationships
                            </p>
                        </div>
                        <button className="flex items-center gap-2 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2D1652] transition">
                            <Download className="h-4 w-4" strokeWidth={1.5} />
                            Export Customers
                        </button>
                    </div>

                    {/* Stats Cards */}
                    <div className="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Total Customers</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">0</p>
                                </div>
                                <div className="rounded-full bg-blue-100 p-3">
                                    <Users className="h-6 w-6 text-blue-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">New This Month</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">0</p>
                                </div>
                                <div className="rounded-full bg-green-100 p-3">
                                    <Users className="h-6 w-6 text-green-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Repeat Customers</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">0</p>
                                </div>
                                <div className="rounded-full bg-purple-100 p-3">
                                    <Users className="h-6 w-6 text-purple-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Avg Order Value</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">₱0</p>
                                </div>
                                <div className="rounded-full bg-orange-100 p-3">
                                    <Users className="h-6 w-6 text-orange-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Search and Filters */}
                    <div className="mb-6 flex items-center gap-4">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" strokeWidth={1.5} />
                            <input
                                type="text"
                                placeholder="Search customers by name or email..."
                                className="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm focus:border-[#3B1F6B] focus:outline-none focus:ring-2 focus:ring-[#3B1F6B]/20"
                            />
                        </div>
                        <button className="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <Filter className="h-4 w-4" strokeWidth={1.5} />
                            Filters
                        </button>
                    </div>

                    {/* Customers Table */}
                    <div className="rounded-lg border border-gray-200 bg-white">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Customer
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Email
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Orders
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Total Spent
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Last Order
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center">
                                            <Users className="mx-auto h-12 w-12 text-gray-300" strokeWidth={1.5} />
                                            <p className="mt-2 text-sm font-medium text-gray-500">No customers yet</p>
                                            <p className="text-xs text-gray-400">Customers who purchase from you will appear here</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </SellerShell>
        </>
    );
}

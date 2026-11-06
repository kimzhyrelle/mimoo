import { Head } from '@inertiajs/react';
import SellerShell from '@/layouts/seller/seller-shell';
import { TrendingUp, DollarSign, ShoppingBag, Users, ArrowUp, ArrowDown } from 'lucide-react';

export default function SellerAnalytics() {
    return (
        <>
            <Head title="Analytics - Seller Dashboard" />
            <SellerShell active="analytics">
                <div className="mx-auto max-w-[1400px]">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900" style={{ fontFamily: "'Syne', sans-serif" }}>
                            Analytics
                        </h1>
                        <p className="mt-1 text-sm text-gray-500">
                            Track your store's performance and insights
                        </p>
                    </div>

                    {/* Time Period Filter */}
                    <div className="mb-6 flex items-center gap-2">
                        <button className="rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white">
                            Last 7 Days
                        </button>
                        <button className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Last 30 Days
                        </button>
                        <button className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Last 90 Days
                        </button>
                        <button className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            This Year
                        </button>
                    </div>

                    {/* Key Metrics */}
                    <div className="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Total Revenue</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">₱0</p>
                                    <div className="mt-2 flex items-center gap-1 text-xs">
                                        <ArrowUp className="h-3 w-3 text-green-600" strokeWidth={2} />
                                        <span className="font-semibold text-green-600">0%</span>
                                        <span className="text-gray-500">vs last period</span>
                                    </div>
                                </div>
                                <div className="rounded-full bg-green-100 p-3">
                                    <DollarSign className="h-6 w-6 text-green-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Total Orders</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">0</p>
                                    <div className="mt-2 flex items-center gap-1 text-xs">
                                        <ArrowUp className="h-3 w-3 text-green-600" strokeWidth={2} />
                                        <span className="font-semibold text-green-600">0%</span>
                                        <span className="text-gray-500">vs last period</span>
                                    </div>
                                </div>
                                <div className="rounded-full bg-blue-100 p-3">
                                    <ShoppingBag className="h-6 w-6 text-blue-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Avg Order Value</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">₱0</p>
                                    <div className="mt-2 flex items-center gap-1 text-xs">
                                        <ArrowDown className="h-3 w-3 text-red-600" strokeWidth={2} />
                                        <span className="font-semibold text-red-600">0%</span>
                                        <span className="text-gray-500">vs last period</span>
                                    </div>
                                </div>
                                <div className="rounded-full bg-purple-100 p-3">
                                    <TrendingUp className="h-6 w-6 text-purple-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">New Customers</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">0</p>
                                    <div className="mt-2 flex items-center gap-1 text-xs">
                                        <ArrowUp className="h-3 w-3 text-green-600" strokeWidth={2} />
                                        <span className="font-semibold text-green-600">0%</span>
                                        <span className="text-gray-500">vs last period</span>
                                    </div>
                                </div>
                                <div className="rounded-full bg-orange-100 p-3">
                                    <Users className="h-6 w-6 text-orange-600" strokeWidth={1.5} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Charts Row */}
                    <div className="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {/* Revenue Chart */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Revenue Overview</h3>
                            <div className="flex h-64 items-center justify-center border-2 border-dashed border-gray-200 rounded-lg">
                                <p className="text-sm text-gray-400">Chart will appear here</p>
                            </div>
                        </div>

                        {/* Orders Chart */}
                        <div className="rounded-lg border border-gray-200 bg-white p-6">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Orders Over Time</h3>
                            <div className="flex h-64 items-center justify-center border-2 border-dashed border-gray-200 rounded-lg">
                                <p className="text-sm text-gray-400">Chart will appear here</p>
                            </div>
                        </div>
                    </div>

                    {/* Top Products */}
                    <div className="mb-6 rounded-lg border border-gray-200 bg-white p-6">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Top Selling Products</h3>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b border-gray-200">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Product
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Sales
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Revenue
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Trend
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colSpan={4} className="px-4 py-12 text-center">
                                            <TrendingUp className="mx-auto h-12 w-12 text-gray-300" strokeWidth={1.5} />
                                            <p className="mt-2 text-sm font-medium text-gray-500">No sales data yet</p>
                                            <p className="text-xs text-gray-400">Your top products will appear here once you start making sales</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Traffic Sources */}
                    <div className="rounded-lg border border-gray-200 bg-white p-6">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Traffic Sources</h3>
                        <div className="flex h-48 items-center justify-center border-2 border-dashed border-gray-200 rounded-lg">
                            <p className="text-sm text-gray-400">Traffic data will appear here</p>
                        </div>
                    </div>
                </div>
            </SellerShell>
        </>
    );
}

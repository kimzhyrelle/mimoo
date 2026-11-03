import { Head } from '@inertiajs/react';
import { Plus, Image, Eye, EyeOff, Edit2, Trash2, Upload, X, ChevronUp, ChevronDown, ExternalLink } from 'lucide-react';
import { useState, useRef } from 'react';
import AdminShell from '@/layouts/admin/admin-shell';
import { toast } from 'sonner';

interface Banner {
    id: number;
    title: string;
    image_url: string;
    link_url?: string;
    position: 'hero' | 'secondary' | 'sidebar';
    status: 'active' | 'inactive';
    order: number;
    created_at: string;
}

export default function BannerManagement() {
    const [banners, setBanners] = useState<Banner[]>([
        {
            id: 1,
            title: 'Main promo banner 1',
            image_url: '/images/main banner 1.jpg',
            link_url: '/category/Pet%20Supplies',
            position: 'hero',
            status: 'active',
            order: 1,
            created_at: 'Nov 1, 2026',
        },
        {
            id: 2,
            title: 'Main promo banner 2',
            image_url: '/images/main banner 2.jpg',
            link_url: '/category/Electronics%20and%20Gadgets',
            position: 'hero',
            status: 'active',
            order: 2,
            created_at: 'Nov 2, 2026',
        },
        {
            id: 3,
            title: 'Perfume & Makeup Offers',
            image_url: '/images/perfumes banner.jpg',
            link_url: '/category/Health%20and%20Beauty',
            position: 'secondary',
            status: 'active',
            order: 1,
            created_at: 'Nov 3, 2026',
        },
        {
            id: 4,
            title: 'iPhone & Electronics Sale',
            image_url: '/images/phones banner.jpg',
            link_url: '/category/Electronics%20and%20Gadgets',
            position: 'secondary',
            status: 'active',
            order: 2,
            created_at: 'Oct 28, 2026',
        },
        {
            id: 5,
            title: 'Payday Sale Promo',
            image_url: '/images/payday sale.png',
            link_url: '/homepage',
            position: 'sidebar',
            status: 'active',
            order: 1,
            created_at: 'Oct 15, 2026',
        },
        {
            id: 6,
            title: 'Special Sale Banner',
            image_url: '/images/sale banner.jpg',
            link_url: '/homepage',
            position: 'sidebar',
            status: 'active',
            order: 2,
            created_at: 'Nov 1, 2026',
        },
    ]);
    const [selectedPosition, setSelectedPosition] = useState<string>('all');

    const filteredBanners = selectedPosition === 'all' 
        ? banners 
        : banners.filter(b => b.position === selectedPosition);

    const activeBanners = banners.filter(b => b.status === 'active').length;

    return (
        <AdminShell active="monitoring">
            <Head title="Banner Management" />

            {/* Page Header */}
            <div className="mb-8">
                <h1 className="text-2xl font-bold text-gray-900">Banner Management</h1>
                <p className="mt-1 text-sm text-gray-500">
                    Manage promotional banners displayed across the platform
                </p>
            </div>

            {/* Stats Cards */}
            <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                            <Image className="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-500">Total Banners</p>
                            <p className="text-2xl font-bold text-gray-900">{banners.length}</p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                            <Eye className="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-500">Active Banners</p>
                            <p className="text-2xl font-bold text-gray-900">{activeBanners}</p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <Image className="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-500">Hero Banners</p>
                            <p className="text-2xl font-bold text-gray-900">
                                {banners.filter(b => b.position === 'hero').length}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border border-gray-200 bg-white p-5">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                            <Image className="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-500">Secondary Banners</p>
                            <p className="text-2xl font-bold text-gray-900">
                                {banners.filter(b => b.position === 'secondary').length}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Actions Bar */}
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                {/* Position Filter */}
                <div className="flex items-center gap-3">
                    <label className="text-sm font-semibold text-gray-700">Filter by Position:</label>
                    <select
                        value={selectedPosition}
                        onChange={(e) => setSelectedPosition(e.target.value)}
                        className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                    >
                        <option value="all">All Positions</option>
                        <option value="hero">Hero Banner</option>
                        <option value="secondary">Secondary Banner</option>
                        <option value="sidebar">Sidebar Banner</option>
                    </select>
                </div>

                {/* Create Button */}
                <button className="flex items-center justify-center gap-2 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#2D1752]">
                    <Plus className="h-4 w-4" />
                    Upload Banner
                </button>
            </div>

            {/* Current Live Preview */}
            <div className="mb-6 rounded-lg border-2 border-purple-200 bg-gradient-to-br from-purple-50 to-pink-50 p-6">
                <div className="mb-4 flex items-center justify-between">
                    <div>
                        <h3 className="flex items-center gap-2 font-semibold text-gray-900">
                            <Eye className="h-5 w-5 text-purple-600" />
                            Current Live Banners Preview
                        </h3>
                        <p className="mt-1 text-sm text-gray-600">
                            These are the active banners currently displayed on the homepage
                        </p>
                    </div>
                    <a
                        href="/homepage"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-sm font-semibold text-purple-600 hover:text-purple-700 hover:underline"
                    >
                        View Live Site →
                    </a>
                </div>

                {/* Hero Banners Preview */}
                <div className="mb-4">
                    <h4 className="mb-2 text-sm font-semibold text-gray-700">
                        Hero Carousel ({banners.filter(b => b.position === 'hero' && b.status === 'active').length} active)
                    </h4>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {banners
                            .filter(b => b.position === 'hero' && b.status === 'active')
                            .sort((a, b) => a.order - b.order)
                            .map((banner) => (
                                <div
                                    key={banner.id}
                                    className="group relative overflow-hidden rounded-lg border-2 border-white shadow-md"
                                >
                                    <div className="relative aspect-[16/6] overflow-hidden bg-gray-100">
                                        <img
                                            src={banner.image_url}
                                            alt={banner.title}
                                            className="h-full w-full object-cover"
                                            onError={(e) => {
                                                // Fallback to gradient if image fails
                                                e.currentTarget.style.display = 'none';
                                            }}
                                        />
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                        <div className="absolute bottom-2 left-2 right-2">
                                            <p className="text-xs font-bold text-white drop-shadow-lg">{banner.title}</p>
                                            <p className="mt-0.5 text-xs text-white/90 drop-shadow">Slide {banner.order}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        {banners.filter(b => b.position === 'hero' && b.status === 'active').length === 0 && (
                            <div className="col-span-full rounded-lg border-2 border-dashed border-gray-300 bg-white p-4 text-center text-sm text-gray-500">
                                No active hero banners
                            </div>
                        )}
                    </div>
                </div>

                {/* Secondary Banners Preview */}
                <div>
                    <h4 className="mb-2 text-sm font-semibold text-gray-700">
                        Secondary Banners ({banners.filter(b => b.position === 'secondary' && b.status === 'active').length} active)
                    </h4>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        {banners
                            .filter(b => b.position === 'secondary' && b.status === 'active')
                            .sort((a, b) => a.order - b.order)
                            .map((banner) => (
                                <div
                                    key={banner.id}
                                    className="relative overflow-hidden rounded-lg border-2 border-white shadow-md"
                                >
                                    <div className="relative aspect-[8/3] overflow-hidden bg-gray-100">
                                        <img
                                            src={banner.image_url}
                                            alt={banner.title}
                                            className="h-full w-full object-cover"
                                            onError={(e) => {
                                                // Fallback to gradient if image fails
                                                e.currentTarget.style.display = 'none';
                                            }}
                                        />
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                                        <div className="absolute bottom-2 left-2 right-2">
                                            <p className="text-xs font-bold text-white drop-shadow-lg">{banner.title}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        {banners.filter(b => b.position === 'secondary' && b.status === 'active').length === 0 && (
                            <div className="col-span-full rounded-lg border-2 border-dashed border-gray-300 bg-white p-4 text-center text-sm text-gray-500">
                                No active secondary banners
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* All Banners Section */}
            <div className="mb-4">
                <h3 className="font-semibold text-gray-900">All Banners Management</h3>
                <p className="text-sm text-gray-500">
                    View, edit, and manage all banner uploads
                </p>
            </div>

            {/* Banner Grid/List */}
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
                {filteredBanners.length === 0 ? (
                    <div className="col-span-full rounded-lg border border-gray-200 bg-white p-16">
                        <div className="flex flex-col items-center justify-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                                <Image className="h-8 w-8 text-gray-400" />
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-gray-900">
                                No banners yet
                            </h3>
                            <p className="mb-6 text-sm text-gray-500">
                                Upload your first banner to start promoting products and offers
                            </p>
                            <button className="flex items-center gap-2 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#2D1752]">
                                <Upload className="h-4 w-4" />
                                Upload First Banner
                            </button>
                        </div>
                    </div>
                ) : (
                    filteredBanners.map((banner) => (
                        <div
                            key={banner.id}
                            className="group relative overflow-hidden rounded-lg border border-gray-200 bg-white transition-shadow hover:shadow-md"
                        >
                            {/* Banner Image */}
                            <div className="relative aspect-[16/9] overflow-hidden bg-gray-100">
                                {/* Actual Banner Image */}
                                <img
                                    src={banner.image_url}
                                    alt={banner.title}
                                    className="h-full w-full object-cover"
                                    onError={(e) => {
                                        // Fallback to placeholder design if image fails
                                        e.currentTarget.style.display = 'none';
                                        const fallback = e.currentTarget.nextElementSibling as HTMLElement;
                                        if (fallback) fallback.style.display = 'flex';
                                    }}
                                />
                                {/* Fallback Placeholder (hidden by default) */}
                                <div className="hidden absolute inset-0 h-full w-full items-center justify-center p-6 bg-gradient-to-br from-purple-100 via-purple-50 to-pink-50">
                                    <div className="text-center">
                                        <Image className="mx-auto mb-3 h-12 w-12 text-purple-300" />
                                        <p className="text-lg font-bold text-purple-900">{banner.title}</p>
                                        <p className="mt-1 text-sm text-purple-600">
                                            {banner.position.toUpperCase()} BANNER
                                        </p>
                                    </div>
                                </div>
                                
                                {/* Status Badge */}
                                <div className="absolute left-3 top-3 z-10">
                                    <span className={`inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold backdrop-blur-sm ${
                                        banner.status === 'active'
                                            ? 'bg-green-500/90 text-white'
                                            : 'bg-gray-500/90 text-white'
                                    }`}>
                                        {banner.status === 'active' ? (
                                            <Eye className="h-3 w-3" />
                                        ) : (
                                            <EyeOff className="h-3 w-3" />
                                        )}
                                        {banner.status === 'active' ? 'Active' : 'Inactive'}
                                    </span>
                                </div>

                                {/* Position Badge */}
                                <div className="absolute right-3 top-3 z-10">
                                    <span className="inline-flex items-center rounded-full bg-purple-600/90 px-2 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        {banner.position}
                                    </span>
                                </div>

                                {/* Hover Actions */}
                                <div className="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100 z-20">
                                    <button className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-700 transition-colors hover:bg-gray-100">
                                        <Edit2 className="h-4 w-4" />
                                    </button>
                                    <button className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-red-600 transition-colors hover:bg-red-50">
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            {/* Banner Info */}
                            <div className="p-4">
                                <h3 className="mb-1 font-semibold text-gray-900">{banner.title}</h3>
                                {banner.link_url && (
                                    <p className="mb-2 truncate text-xs text-gray-500">
                                        Link: {banner.link_url}
                                    </p>
                                )}
                                <div className="flex items-center justify-between text-xs text-gray-500">
                                    <span>Order: {banner.order}</span>
                                    <span>{banner.created_at}</span>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Banner Positions Guide */}
            <div className="mt-8 rounded-lg border border-gray-200 bg-white p-6">
                <h3 className="mb-4 font-semibold text-gray-900">Banner Position Guide</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h4 className="mb-2 font-semibold text-gray-900">Hero Banner</h4>
                        <p className="mb-2 text-sm text-gray-600">
                            Large carousel banner at the top of homepage
                        </p>
                        <div className="text-xs text-gray-500">
                            Recommended: 1920x600px
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h4 className="mb-2 font-semibold text-gray-900">Secondary Banner</h4>
                        <p className="mb-2 text-sm text-gray-600">
                            Promotional banners below featured products
                        </p>
                        <div className="text-xs text-gray-500">
                            Recommended: 800x300px
                        </div>
                    </div>
                    <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h4 className="mb-2 font-semibold text-gray-900">Sidebar Banner</h4>
                        <p className="mb-2 text-sm text-gray-600">
                            Vertical banner on category/product pages
                        </p>
                        <div className="text-xs text-gray-500">
                            Recommended: 300x600px
                        </div>
                    </div>
                </div>
            </div>
        </AdminShell>
    );
}

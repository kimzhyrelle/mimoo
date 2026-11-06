import { Head } from '@inertiajs/react';
import { Plus, Image, Eye, EyeOff, Edit2, Trash2, Upload, X, ChevronUp, ChevronDown, ExternalLink, GripVertical } from 'lucide-react';
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
    const [showUploadModal, setShowUploadModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showPreviewModal, setShowPreviewModal] = useState(false);
    const [currentBanner, setCurrentBanner] = useState<Banner | null>(null);
    const [previewBanner, setPreviewBanner] = useState<Banner | null>(null);
    const [draggedBanner, setDraggedBanner] = useState<Banner | null>(null);
    const [dragOverBanner, setDragOverBanner] = useState<number | null>(null);
    const [imagePosition, setImagePosition] = useState({ x: 50, y: 50 }); // percentage position
    const [isDraggingImage, setIsDraggingImage] = useState(false);
    const [dragStartPos, setDragStartPos] = useState({ x: 0, y: 0 });
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Form state for upload/edit
    const [formData, setFormData] = useState({
        title: '',
        image_url: '',
        link_url: '',
        position: 'hero' as 'hero' | 'secondary' | 'sidebar',
        status: 'active' as 'active' | 'inactive',
    });
    const [previewImage, setPreviewImage] = useState<string>('');

    const filteredBanners = selectedPosition === 'all' 
        ? banners 
        : banners.filter(b => b.position === selectedPosition);

    const activeBanners = banners.filter(b => b.status === 'active').length;

    // Upload new banner
    const handleUpload = () => {
        if (!formData.title || !previewImage) {
            toast.error('Please provide title and image');
            return;
        }

        const newBanner: Banner = {
            id: Math.max(...banners.map(b => b.id), 0) + 1,
            title: formData.title,
            image_url: previewImage,
            link_url: formData.link_url || undefined,
            position: formData.position,
            status: formData.status,
            order: banners.filter(b => b.position === formData.position).length + 1,
            created_at: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        };

        setBanners([...banners, newBanner]);
        resetForm();
        setShowUploadModal(false);
        toast.success('Banner uploaded successfully!');
    };

    // Edit banner
    const handleEdit = () => {
        if (!currentBanner || !formData.title) {
            toast.error('Please provide title');
            return;
        }

        setBanners(banners.map(b => 
            b.id === currentBanner.id 
                ? { ...b, ...formData, image_url: previewImage || b.image_url }
                : b
        ));
        resetForm();
        setShowEditModal(false);
        setCurrentBanner(null);
        toast.success('Banner updated successfully!');
    };

    // Delete banner
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this banner?')) {
            setBanners(banners.filter(b => b.id !== id));
            toast.success('Banner deleted successfully!');
        }
    };

    // Toggle status
    const toggleStatus = (id: number) => {
        setBanners(banners.map(b => 
            b.id === id 
                ? { ...b, status: b.status === 'active' ? 'inactive' : 'active' }
                : b
        ));
        toast.success('Status updated!');
    };

    // Reorder banners
    const moveUp = (banner: Banner) => {
        const samePosoBanners = banners
            .filter(b => b.position === banner.position)
            .sort((a, b) => a.order - b.order);
        
        const currentIndex = samePosoBanners.findIndex(b => b.id === banner.id);
        if (currentIndex <= 0) return;

        const prevBanner = samePosoBanners[currentIndex - 1];
        
        setBanners(banners.map(b => {
            if (b.id === banner.id) return { ...b, order: prevBanner.order };
            if (b.id === prevBanner.id) return { ...b, order: banner.order };
            return b;
        }));
        toast.success('Banner moved up!');
    };

    const moveDown = (banner: Banner) => {
        const samePosoBanners = banners
            .filter(b => b.position === banner.position)
            .sort((a, b) => a.order - b.order);
        
        const currentIndex = samePosoBanners.findIndex(b => b.id === banner.id);
        if (currentIndex >= samePosoBanners.length - 1) return;

        const nextBanner = samePosoBanners[currentIndex + 1];
        
        setBanners(banners.map(b => {
            if (b.id === banner.id) return { ...b, order: nextBanner.order };
            if (b.id === nextBanner.id) return { ...b, order: banner.order };
            return b;
        }));
        toast.success('Banner moved down!');
    };

    // Drag and drop handlers
    const handleDragStart = (e: React.DragEvent, banner: Banner) => {
        setDraggedBanner(banner);
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.currentTarget.innerHTML);
        // Add ghost image effect
        if (e.currentTarget instanceof HTMLElement) {
            e.currentTarget.style.opacity = '0.4';
        }
    };

    const handleDragEnd = (e: React.DragEvent) => {
        if (e.currentTarget instanceof HTMLElement) {
            e.currentTarget.style.opacity = '1';
        }
        setDraggedBanner(null);
        setDragOverBanner(null);
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    const handleDragEnter = (e: React.DragEvent, banner: Banner) => {
        e.preventDefault();
        if (draggedBanner && draggedBanner.id !== banner.id && draggedBanner.position === banner.position) {
            setDragOverBanner(banner.id);
        }
    };

    const handleDragLeave = (e: React.DragEvent) => {
        // Only clear if we're leaving the card entirely, not just entering a child
        if (e.currentTarget === e.target) {
            setDragOverBanner(null);
        }
    };

    const handleDrop = (e: React.DragEvent, targetBanner: Banner) => {
        e.preventDefault();
        setDragOverBanner(null);

        if (!draggedBanner || draggedBanner.id === targetBanner.id) {
            return;
        }

        // Only allow reordering within same position
        if (draggedBanner.position !== targetBanner.position) {
            toast.error('Can only reorder within same position type');
            return;
        }

        // Get all banners in same position
        const samePosoBanners = banners
            .filter(b => b.position === draggedBanner.position)
            .sort((a, b) => a.order - b.order);

        const draggedIndex = samePosoBanners.findIndex(b => b.id === draggedBanner.id);
        const targetIndex = samePosoBanners.findIndex(b => b.id === targetBanner.id);

        // Reorder the array
        const reordered = [...samePosoBanners];
        const [removed] = reordered.splice(draggedIndex, 1);
        reordered.splice(targetIndex, 0, removed);

        // Update order numbers
        const updatedBanners = banners.map(b => {
            const newIndex = reordered.findIndex(rb => rb.id === b.id);
            if (newIndex !== -1) {
                return { ...b, order: newIndex + 1 };
            }
            return b;
        });

        setBanners(updatedBanners);
        toast.success('Banner reordered!');
    };

    // Open edit modal
    const openEditModal = (banner: Banner) => {
        setCurrentBanner(banner);
        setFormData({
            title: banner.title,
            image_url: banner.image_url,
            link_url: banner.link_url || '',
            position: banner.position,
            status: banner.status,
        });
        setPreviewImage(banner.image_url);
        setImagePosition({ x: 50, y: 50 }); // reset to center
        setShowEditModal(true);
    };

    // Image position drag handlers
    const handleImageMouseDown = (e: React.MouseEvent) => {
        setIsDraggingImage(true);
        setDragStartPos({ x: e.clientX, y: e.clientY });
        e.preventDefault();
    };

    const handleImageMouseMove = (e: React.MouseEvent) => {
        if (!isDraggingImage) return;

        const deltaX = e.clientX - dragStartPos.x;
        const deltaY = e.clientY - dragStartPos.y;

        // Convert pixel movement to percentage (adjust sensitivity)
        const sensitivity = 0.1;
        const newX = Math.max(0, Math.min(100, imagePosition.x + deltaX * sensitivity));
        const newY = Math.max(0, Math.min(100, imagePosition.y + deltaY * sensitivity));

        setImagePosition({ x: newX, y: newY });
        setDragStartPos({ x: e.clientX, y: e.clientY });
    };

    const handleImageMouseUp = () => {
        setIsDraggingImage(false);
    };

    // Preview banner
    const openPreview = (banner: Banner) => {
        setPreviewBanner(banner);
        setShowPreviewModal(true);
    };

    // Handle file upload
    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            // In real app, upload to server and get URL
            // For now, create local preview
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewImage(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const resetForm = () => {
        setFormData({
            title: '',
            image_url: '',
            link_url: '',
            position: 'hero',
            status: 'active',
        });
        setPreviewImage('');
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

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
                <button 
                    onClick={() => {
                        resetForm();
                        setShowUploadModal(true);
                    }}
                    className="flex items-center justify-center gap-2 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#2D1752]"
                >
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
                    filteredBanners
                        .sort((a, b) => {
                            if (a.position !== b.position) {
                                const posOrder = { hero: 1, secondary: 2, sidebar: 3 };
                                return posOrder[a.position] - posOrder[b.position];
                            }
                            return a.order - b.order;
                        })
                        .map((banner) => {
                            const samePosoBanners = banners
                                .filter(b => b.position === banner.position)
                                .sort((a, b) => a.order - b.order);
                            const currentIndex = samePosoBanners.findIndex(b => b.id === banner.id);
                            const canMoveUp = currentIndex > 0;
                            const canMoveDown = currentIndex < samePosoBanners.length - 1;

                            return (
                        <div
                            key={banner.id}
                            draggable
                            onDragStart={(e) => handleDragStart(e, banner)}
                            onDragEnd={handleDragEnd}
                            onDragOver={handleDragOver}
                            onDragEnter={(e) => handleDragEnter(e, banner)}
                            onDragLeave={handleDragLeave}
                            onDrop={(e) => handleDrop(e, banner)}
                            className={`group relative overflow-hidden rounded-lg border-2 bg-white transition-all cursor-move ${
                                dragOverBanner === banner.id 
                                    ? 'border-purple-500 shadow-lg scale-105' 
                                    : 'border-gray-200 hover:shadow-md'
                            }`}
                        >
                            {/* Drag Handle */}
                            <div className="absolute left-2 top-2 z-30 flex items-center gap-1 rounded bg-white/90 px-2 py-1 text-xs font-semibold text-gray-600 backdrop-blur-sm shadow-sm">
                                <GripVertical className="h-4 w-4" />
                                <span>Drag</span>
                            </div>

                            {/* Banner Image */}
                            <div className="relative aspect-[16/9] overflow-hidden bg-gray-100">
                                {/* Actual Banner Image */}
                                <img
                                    src={banner.image_url}
                                    alt={banner.title}
                                    className="h-full w-full object-cover cursor-pointer"
                                    onClick={() => openPreview(banner)}
                                    onError={(e) => {
                                        e.currentTarget.style.display = 'none';
                                        const fallback = e.currentTarget.nextElementSibling as HTMLElement;
                                        if (fallback) fallback.style.display = 'flex';
                                    }}
                                />
                                {/* Fallback Placeholder */}
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
                                <div className="absolute left-2 top-12 z-10">
                                    <button
                                        onClick={() => toggleStatus(banner.id)}
                                        className={`inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold backdrop-blur-sm transition-colors ${
                                            banner.status === 'active'
                                                ? 'bg-green-500/90 text-white hover:bg-green-600/90'
                                                : 'bg-gray-500/90 text-white hover:bg-gray-600/90'
                                        }`}
                                    >
                                        {banner.status === 'active' ? (
                                            <Eye className="h-3 w-3" />
                                        ) : (
                                            <EyeOff className="h-3 w-3" />
                                        )}
                                        {banner.status === 'active' ? 'Active' : 'Inactive'}
                                    </button>
                                </div>

                                {/* Position Badge */}
                                <div className="absolute right-3 top-3 z-10">
                                    <span className="inline-flex items-center rounded-full bg-purple-600/90 px-2 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                        {banner.position}
                                    </span>
                                </div>

                                {/* Reorder Buttons */}
                                <div className="absolute right-3 bottom-3 z-10 flex flex-col gap-1">
                                    <button
                                        onClick={() => moveUp(banner)}
                                        disabled={!canMoveUp}
                                        className={`flex h-7 w-7 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-colors ${
                                            canMoveUp 
                                                ? 'text-gray-700 hover:bg-white' 
                                                : 'text-gray-300 cursor-not-allowed'
                                        }`}
                                        title="Move up"
                                    >
                                        <ChevronUp className="h-4 w-4" />
                                    </button>
                                    <button
                                        onClick={() => moveDown(banner)}
                                        disabled={!canMoveDown}
                                        className={`flex h-7 w-7 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-colors ${
                                            canMoveDown 
                                                ? 'text-gray-700 hover:bg-white' 
                                                : 'text-gray-300 cursor-not-allowed'
                                        }`}
                                        title="Move down"
                                    >
                                        <ChevronDown className="h-4 w-4" />
                                    </button>
                                </div>

                                {/* Hover Actions */}
                                <div className="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100 z-20">
                                    <button 
                                        onClick={() => openPreview(banner)}
                                        className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-700 transition-colors hover:bg-gray-100"
                                        title="Preview"
                                    >
                                        <Eye className="h-4 w-4" />
                                    </button>
                                    <button 
                                        onClick={() => openEditModal(banner)}
                                        className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-700 transition-colors hover:bg-gray-100"
                                        title="Edit"
                                    >
                                        <Edit2 className="h-4 w-4" />
                                    </button>
                                    <button 
                                        onClick={() => handleDelete(banner.id)}
                                        className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-red-600 transition-colors hover:bg-red-50"
                                        title="Delete"
                                    >
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
                    );
                })
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

            {/* Upload Modal */}
            {showUploadModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl">
                        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
                            <h2 className="text-xl font-bold text-gray-900">Upload New Banner</h2>
                            <button
                                onClick={() => {
                                    setShowUploadModal(false);
                                    resetForm();
                                }}
                                className="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="p-6">
                            {/* Image Upload */}
                            <div className="mb-6">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Banner Image *
                                </label>
                                <div className="relative">
                                    {previewImage ? (
                                        <div className="relative aspect-[16/9] overflow-hidden rounded-lg border-2 border-gray-200">
                                            <img src={previewImage} alt="Preview" className="h-full w-full object-cover" />
                                            <button
                                                onClick={() => setPreviewImage('')}
                                                className="absolute right-2 top-2 rounded-full bg-red-500 p-2 text-white transition-colors hover:bg-red-600"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                        </div>
                                    ) : (
                                        <label className="flex aspect-[16/9] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100">
                                            <Upload className="mb-3 h-12 w-12 text-gray-400" />
                                            <p className="mb-1 text-sm font-semibold text-gray-700">
                                                Click to upload
                                            </p>
                                            <p className="text-xs text-gray-500">PNG, JPG up to 10MB</p>
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                accept="image/*"
                                                onChange={handleFileChange}
                                                className="hidden"
                                            />
                                        </label>
                                    )}
                                </div>
                            </div>

                            {/* Title */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Banner Title *
                                </label>
                                <input
                                    type="text"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    placeholder="e.g., Summer Sale 2026"
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                />
                            </div>

                            {/* Link URL */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Link URL (optional)
                                </label>
                                <input
                                    type="text"
                                    value={formData.link_url}
                                    onChange={(e) => setFormData({ ...formData, link_url: e.target.value })}
                                    placeholder="/category/Electronics or https://external.com"
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                />
                            </div>

                            {/* Position */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Position *
                                </label>
                                <select
                                    value={formData.position}
                                    onChange={(e) => setFormData({ ...formData, position: e.target.value as any })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                >
                                    <option value="hero">Hero Banner (Main Carousel)</option>
                                    <option value="secondary">Secondary Banner</option>
                                    <option value="sidebar">Sidebar Banner</option>
                                </select>
                            </div>

                            {/* Status */}
                            <div className="mb-6">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Status *
                                </label>
                                <select
                                    value={formData.status}
                                    onChange={(e) => setFormData({ ...formData, status: e.target.value as any })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                >
                                    <option value="active">Active (Visible on site)</option>
                                    <option value="inactive">Inactive (Hidden)</option>
                                </select>
                            </div>

                            {/* Actions */}
                            <div className="flex gap-3">
                                <button
                                    onClick={() => {
                                        setShowUploadModal(false);
                                        resetForm();
                                    }}
                                    className="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    onClick={handleUpload}
                                    className="flex-1 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#2D1752]"
                                >
                                    Upload Banner
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Edit Modal */}
            {showEditModal && currentBanner && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl">
                        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
                            <h2 className="text-xl font-bold text-gray-900">Edit Banner</h2>
                            <button
                                onClick={() => {
                                    setShowEditModal(false);
                                    setCurrentBanner(null);
                                    resetForm();
                                }}
                                className="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="p-6">
                            {/* Image Upload */}
                            <div className="mb-6">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Banner Image
                                </label>
                                <div className="relative">
                                    <div 
                                        className="relative aspect-[16/9] overflow-hidden rounded-lg border-2 border-gray-200 group cursor-move"
                                        onMouseDown={handleImageMouseDown}
                                        onMouseMove={handleImageMouseMove}
                                        onMouseUp={handleImageMouseUp}
                                        onMouseLeave={handleImageMouseUp}
                                    >
                                        <img 
                                            src={previewImage} 
                                            alt="Preview" 
                                            className="absolute w-full h-full object-cover pointer-events-none select-none"
                                            style={{
                                                objectPosition: `${imagePosition.x}% ${imagePosition.y}%`,
                                                transition: isDraggingImage ? 'none' : 'object-position 0.1s ease'
                                            }}
                                            draggable={false}
                                        />
                                        {/* Overlay hint */}
                                        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                            <div className="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 px-4 py-2 rounded-lg shadow-lg">
                                                <p className="text-sm font-semibold text-gray-800">
                                                    Drag to adjust image position
                                                </p>
                                            </div>
                                        </div>
                                        {/* Change image button */}
                                        <label className="absolute bottom-3 right-3 cursor-pointer bg-white hover:bg-gray-50 px-3 py-2 rounded-lg shadow-md transition-colors flex items-center gap-2 text-sm font-semibold text-gray-700">
                                            <Upload className="h-4 w-4" />
                                            Change Image
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                accept="image/*"
                                                onChange={handleFileChange}
                                                className="hidden"
                                            />
                                        </label>
                                    </div>
                                    <p className="mt-2 text-xs text-gray-500">
                                        Position: {Math.round(imagePosition.x)}% horizontal, {Math.round(imagePosition.y)}% vertical
                                    </p>
                                </div>
                            </div>

                            {/* Title */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Banner Title *
                                </label>
                                <input
                                    type="text"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                />
                            </div>

                            {/* Link URL */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Link URL (optional)
                                </label>
                                <input
                                    type="text"
                                    value={formData.link_url}
                                    onChange={(e) => setFormData({ ...formData, link_url: e.target.value })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                />
                            </div>

                            {/* Position */}
                            <div className="mb-4">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Position *
                                </label>
                                <select
                                    value={formData.position}
                                    onChange={(e) => setFormData({ ...formData, position: e.target.value as any })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                >
                                    <option value="hero">Hero Banner (Main Carousel)</option>
                                    <option value="secondary">Secondary Banner</option>
                                    <option value="sidebar">Sidebar Banner</option>
                                </select>
                            </div>

                            {/* Status */}
                            <div className="mb-6">
                                <label className="mb-2 block text-sm font-semibold text-gray-700">
                                    Status *
                                </label>
                                <select
                                    value={formData.status}
                                    onChange={(e) => setFormData({ ...formData, status: e.target.value as any })}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                >
                                    <option value="active">Active (Visible on site)</option>
                                    <option value="inactive">Inactive (Hidden)</option>
                                </select>
                            </div>

                            {/* Actions */}
                            <div className="flex gap-3">
                                <button
                                    onClick={() => {
                                        setShowEditModal(false);
                                        setCurrentBanner(null);
                                        resetForm();
                                    }}
                                    className="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    onClick={handleEdit}
                                    className="flex-1 rounded-lg bg-[#3B1F6B] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#2D1752]"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Preview Modal */}
            {showPreviewModal && previewBanner && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4">
                    <div className="relative w-full max-w-5xl">
                        <div className="mb-4 flex items-center justify-between">
                            <div className="text-white">
                                <h2 className="text-xl font-bold">{previewBanner.title}</h2>
                                <p className="text-sm text-white/70">
                                    {previewBanner.position.toUpperCase()} • {previewBanner.status.toUpperCase()}
                                </p>
                            </div>
                            <button
                                onClick={() => {
                                    setShowPreviewModal(false);
                                    setPreviewBanner(null);
                                }}
                                className="rounded-lg bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                            >
                                <X className="h-6 w-6" />
                            </button>
                        </div>
                        <div className="overflow-hidden rounded-lg bg-white shadow-2xl">
                            <img
                                src={previewBanner.image_url}
                                alt={previewBanner.title}
                                className="w-full"
                            />
                        </div>
                        {previewBanner.link_url && (
                            <div className="mt-4 flex items-center justify-center gap-2 text-white">
                                <ExternalLink className="h-4 w-4" />
                                <span className="text-sm">Links to: {previewBanner.link_url}</span>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </AdminShell>
    );
}

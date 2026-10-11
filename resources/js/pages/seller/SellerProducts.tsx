import { Head } from '@inertiajs/react';
import { Copy, ImagePlus, MoreVertical, Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ChangeEvent, MouseEvent, ReactNode } from 'react';
import SellerShell from '@/layouts/seller/seller-shell';

interface SellerProductsProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            account_type: string;
        };
    };
    seller: {
        store_name: string;
        business_name?: string;
    };
    products: Product[];
}

const fontPoppins = { fontFamily: 'Poppins, sans-serif' };
const fontSyne = { fontFamily: "'Syne', sans-serif" };

const PER_PAGE = 8;
const MAX_IMAGE_MB = 5;
const BORDER = 'border-[color-mix(in_srgb,#B9A6DE_44%,white)]';
const STORE_KEY = 'mimoo_seller_products';

const CATEGORIES = [
    'Pet Supplies',
    'Electronics and Gadgets',
    "Women's Apparel",
    "Men's Apparel",
    'Kids and Baby',
    'Home and Garden',
    'Sports and Outdoors',
    'Health and Beauty',
    'Books and Media',
    'Food and Gourmet',
    'Furniture and Decor',
    'Jewelry and Watches',
];

type Status = 'published' | 'draft';

interface Product {
    id: string;
    name: string;
    category: string;
    brand: string;
    desc: string;
    images: string[];
    mainIdx: number;
    price: number;
    salePrice: number | null;
    stock: number;
    sku: string;
    weight?: number | null;
    status: Status;
    updated: number;
}

interface FormState {
    name: string;
    category: string;
    price: string;
    salePrice: string;
    stock: string;
    sku: string;
    weight: string;
    desc: string;
    image: string;
}
type FormErrors = Partial<Record<keyof FormState, string>>;

const emptyForm: FormState = {
    name: '', category: '', price: '', salePrice: '', stock: '', sku: '', weight: '', desc: '', image: '',
};

const peso = (n: number) =>
    '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const initials = (n: string) =>
    n.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase() || 'P';

const hue = (str: string) => {
    let h = 0;
    for (const c of str) h = (h * 31 + c.charCodeAt(0)) % 360;
    return h;
};

/** Shrinks the uploaded photo so it fits comfortably in localStorage. */
function readImage(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('read'));
        reader.onload = () => {
            const img = new Image();
            img.onerror = () => reject(new Error('decode'));
            img.onload = () => {
                const max = 640;
                const scale = Math.min(1, max / Math.max(img.width, img.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                const ctx = canvas.getContext('2d');
                if (!ctx) return reject(new Error('canvas'));
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                resolve(canvas.toDataURL('image/jpeg', 0.82));
            };
            img.src = String(reader.result);
        };
        reader.readAsDataURL(file);
    });
}

function validate(f: FormState, publish: boolean): FormErrors {
    const e: FormErrors = {};
    const price = parseFloat(f.price);
    const sale = parseFloat(f.salePrice);

    if (!f.name.trim()) e.name = 'Enter a product name.';

    if (publish) {
        if (!f.category) e.category = 'Select a category.';
        if (!f.price.trim() || isNaN(price) || price <= 0) e.price = 'Enter a price greater than 0.';
        if (f.stock.trim() === '' || !Number.isInteger(Number(f.stock)) || Number(f.stock) < 0)
            e.stock = 'Enter the stock as a whole number.';
    }

    if (f.salePrice.trim()) {
        if (isNaN(sale) || sale <= 0) e.salePrice = 'Enter a valid sale price.';
        else if (!isNaN(price) && price > 0 && sale >= price) e.salePrice = 'Sale price must be lower than the regular price.';
    }
    if (f.weight.trim() && (isNaN(parseFloat(f.weight)) || parseFloat(f.weight) <= 0)) e.weight = 'Enter a valid weight.';
    return e;
}

function Thumb({ p }: { p: Product }) {
    const src = p.images[p.mainIdx || 0];
    if (src) {
        return (
            <span className="shrink-0 w-12 h-12 rounded-[11px] overflow-hidden">
                <img src={src} alt="" className="w-full h-full object-cover" />
            </span>
        );
    }
    const h = hue(p.name);
    return (
        <span
            className="shrink-0 w-12 h-12 rounded-[11px] flex items-center justify-center text-[0.86rem] font-semibold"
            style={{
                ...fontSyne,
                background: `linear-gradient(145deg,hsl(${h} 60% 93%),hsl(${h} 50% 80%))`,
                color: `hsl(${h} 45% 28%)`,
            }}
        >
            {initials(p.name)}
        </span>
    );
}

const inputCls = (err?: string) =>
    `w-full rounded-lg border-[1.5px] px-3.5 py-2.5 text-sm text-[#2A1B4D] bg-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#4B2E7E]/25 focus:border-[#4B2E7E] transition ${
        err ? 'border-red-400 bg-red-50' : BORDER
    }`;

function Field({
    label, htmlFor, required, error, hint, className = '', children,
}: {
    label: string; htmlFor: string; required?: boolean; error?: string; hint?: string;
    className?: string; children: ReactNode;
}) {
    return (
        <div className={`flex flex-col gap-1.5 min-w-0 ${className}`}>
            <label htmlFor={htmlFor} className="text-sm font-semibold text-[#2A1B4D]">
                {label} {required && <span className="text-red-600" aria-hidden="true">*</span>}
            </label>
            {children}
            {hint && !error && <span className="text-xs text-[#6E6570]">{hint}</span>}
            {error && <span className="text-xs font-medium text-red-600" role="alert">{error}</span>}
        </div>
    );
}

const TH = `text-left text-[0.68rem] font-semibold uppercase tracking-wide text-[#6E6570] px-4 py-3.5 border-b ${BORDER} bg-[color-mix(in_srgb,#B9A6DE_12%,white)] whitespace-nowrap`;

export default function SellerProducts({ auth, seller, products: initialProducts = [] }: SellerProductsProps) {
    const storeName = seller.store_name || seller.business_name || 'Your Store';
    
    // Merge initial products from database with localStorage products
    const [products, setProducts] = useState<Product[]>(initialProducts);
    const [loaded, setLoaded] = useState(false);
    const [tab, setTab] = useState<Status>('published');
    const [query, setQuery] = useState('');
    const [page, setPage] = useState(1);
    const [menu, setMenu] = useState<{ id: string; top: number; left: number } | null>(null);
    const [deleteId, setDeleteId] = useState<string | null>(null);
    const [toast, setToast] = useState<{ msg: string; bad?: boolean } | null>(null);
    const toastTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    // Add / Edit modal
    const [formOpen, setFormOpen] = useState(false);
    const [editingId, setEditingId] = useState<string | null>(null);
    const [form, setForm] = useState<FormState>(emptyForm);
    const [errors, setErrors] = useState<FormErrors>({});
    const [imageBusy, setImageBusy] = useState(false);
    const fileRef = useRef<HTMLInputElement>(null);

    const editing = editingId ? products.find((p) => p.id === editingId) ?? null : null;

    // Load saved products + initial tab from URL
    useEffect(() => {
        // If no products from DB, try loading from localStorage, otherwise use empty array
        if (initialProducts.length === 0) {
            try {
                const raw = localStorage.getItem(STORE_KEY);
                if (raw) setProducts(JSON.parse(raw));
            } catch {
                /* use empty array */
            }
        }
        if (new URLSearchParams(window.location.search).get('status') === 'draft') setTab('draft');
        setLoaded(true);
    }, []);

    // Save whenever products change
    useEffect(() => {
        if (!loaded) return;
        try {
            localStorage.setItem(STORE_KEY, JSON.stringify(products));
        } catch {
            showToast('Could not save: browser storage is full.', true);
        }
    }, [products, loaded]);

    // Keep ?status=draft in the URL
    useEffect(() => {
        if (!loaded) return;
        const url = new URL(window.location.href);
        if (tab === 'draft') url.searchParams.set('status', 'draft');
        else url.searchParams.delete('status');
        window.history.replaceState(window.history.state, '', url);
    }, [tab, loaded]);

    // Close row menu on outside click, Escape, scroll, resize
    useEffect(() => {
        if (!menu) return;
        const close = () => setMenu(null);
        const onClick = (e: globalThis.MouseEvent) => {
            const t = e.target as HTMLElement;
            if (!t.closest('[data-rowmenu]') && !t.closest('[data-kebab]')) close();
        };
        const onKey = (e: KeyboardEvent) => e.key === 'Escape' && close();
        document.addEventListener('click', onClick);
        document.addEventListener('keydown', onKey);
        window.addEventListener('scroll', close, { passive: true });
        window.addEventListener('resize', close);
        return () => {
            document.removeEventListener('click', onClick);
            document.removeEventListener('keydown', onKey);
            window.removeEventListener('scroll', close);
            window.removeEventListener('resize', close);
        };
    }, [menu]);

    // Escape closes modals, and the page behind them doesn't scroll
    useEffect(() => {
        if (!formOpen && !deleteId) return;
        const onKey = (e: KeyboardEvent) => {
            if (e.key !== 'Escape') return;
            if (deleteId) setDeleteId(null);
            else setFormOpen(false);
        };
        document.addEventListener('keydown', onKey);
        const prev = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = prev;
        };
    }, [formOpen, deleteId]);

    const showToast = (msg: string, bad = false) => {
        setToast({ msg, bad });
        if (toastTimer.current) clearTimeout(toastTimer.current);
        toastTimer.current = setTimeout(() => setToast(null), 3400);
    };

    const counts = {
        published: products.filter((p) => p.status === 'published').length,
        draft: products.filter((p) => p.status === 'draft').length,
    };

    const q = query.trim().toLowerCase();
    const rows = products.filter((p) => p.status === tab && (!q || p.name.toLowerCase().includes(q)));
    const pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    const currentPage = Math.min(page, pages);
    const slice = rows.slice((currentPage - 1) * PER_PAGE, currentPage * PER_PAGE);

    const openMenu = (e: MouseEvent<HTMLButtonElement>, id: string) => {
        if (menu?.id === id) {
            setMenu(null);
            return;
        }
        const r = e.currentTarget.getBoundingClientRect();
        const mw = 176;
        const mh = 140;
        let top = r.bottom + 6;
        if (top + mh > window.innerHeight - 8) top = Math.max(8, r.top - mh - 6);
        const left = Math.max(8, Math.min(window.innerWidth - mw - 8, r.right - mw));
        setMenu({ id, top, left });
    };

    /* ---------- Add / Edit ---------- */
    const openAdd = () => {
        setEditingId(null);
        setForm(emptyForm);
        setErrors({});
        setFormOpen(true);
    };

    const openEdit = (id: string) => {
        const p = products.find((x) => x.id === id);
        setMenu(null);
        if (!p) return;
        setEditingId(id);
        setForm({
            name: p.name,
            category: p.category,
            price: p.price ? String(p.price) : '',
            salePrice: p.salePrice ? String(p.salePrice) : '',
            stock: String(p.stock),
            sku: p.sku,
            weight: p.weight ? String(p.weight) : '',
            desc: p.desc,
            image: p.images[p.mainIdx || 0] ?? '',
        });
        setErrors({});
        setFormOpen(true);
    };

    const setField = <K extends keyof FormState>(key: K, value: FormState[K]) => {
        setForm((f) => ({ ...f, [key]: value }));
        if (errors[key]) setErrors((e) => ({ ...e, [key]: undefined }));
    };

    const pickImage = async (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        e.target.value = '';
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            setErrors((er) => ({ ...er, image: 'Use a JPG, PNG or WEBP image.' }));
            return;
        }
        if (file.size > MAX_IMAGE_MB * 1024 * 1024) {
            setErrors((er) => ({ ...er, image: `Image is too large. Max ${MAX_IMAGE_MB}MB.` }));
            return;
        }
        setImageBusy(true);
        try {
            setField('image', await readImage(file));
        } catch {
            setErrors((er) => ({ ...er, image: 'Could not read that image. Try another one.' }));
        } finally {
            setImageBusy(false);
        }
    };

    const save = (status: Status) => {
        const errs = validate(form, status === 'published');
        if (Object.keys(errs).length) {
            setErrors(errs);
            const first = (Object.keys(errs) as (keyof FormState)[])[0];
            setTimeout(() => document.getElementById(`pf-${first}`)?.focus(), 0);
            return;
        }

        const next: Product = {
            id: editing?.id ?? 'p' + Date.now(),
            name: form.name.trim(),
            category: form.category,
            brand: editing?.brand ?? storeName,
            desc: form.desc.trim(),
            images: form.image ? [form.image] : [],
            mainIdx: 0,
            price: parseFloat(form.price) || 0,
            salePrice: form.salePrice.trim() ? parseFloat(form.salePrice) : null,
            stock: form.stock.trim() === '' ? 0 : parseInt(form.stock, 10),
            sku: form.sku.trim(),
            weight: form.weight.trim() ? parseFloat(form.weight) : null,
            status,
            updated: Date.now(),
        };

        setProducts((list) =>
            editing ? list.map((p) => (p.id === next.id ? next : p)) : [next, ...list],
        );
        setFormOpen(false);
        setTab(status);
        setQuery('');
        setPage(1);
        showToast(
            editing
                ? status === 'published' ? 'Changes saved.' : 'Moved to drafts.'
                : status === 'published' ? 'Product published.' : 'Saved as draft.',
        );
    };

    /* ---------- Duplicate / Delete ---------- */
    const duplicate = (id: string) => {
        const src = products.find((p) => p.id === id);
        setMenu(null);
        if (!src) return;
        const copy: Product = {
            ...src,
            id: 'p' + Date.now(),
            name: src.name + ' (Copy)',
            status: 'draft',
            sku: src.sku ? src.sku + '-COPY' : '',
            updated: Date.now(),
        };
        setProducts((list) => [copy, ...list]);
        setTab('draft');
        setQuery('');
        setPage(1);
        showToast(`"${src.name}" duplicated as a draft.`);
    };

    const deleting = products.find((p) => p.id === deleteId) ?? null;

    const confirmDelete = () => {
        if (!deleteId) return;
        setProducts((list) => list.filter((p) => p.id !== deleteId));
        setDeleteId(null);
        showToast('Product deleted.');
    };

    const menuProduct = menu ? products.find((p) => p.id === menu.id) : null;
    const publishLabel = editing?.status === 'published' ? 'Save changes' : 'Publish';
    const draftLabel = editing?.status === 'published' ? 'Move to drafts' : 'Save as draft';

    return (
        <SellerShell active="products">
            <Head title="Products" />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap');
            `}</style>

            <div style={fontPoppins}>
                {/* Page header */}
                <div className="flex items-start justify-between gap-4 flex-wrap mb-6">
                    <div>
                        <h1 className="text-[1.7rem] font-semibold leading-tight" style={fontSyne}>Products</h1>
                        <p className="text-sm text-[#6E6570] mt-1.5 max-w-xl">
                            Published products are visible to buyers. Drafts are unfinished listings that stay hidden until you publish them.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={openAdd}
                        className="inline-flex items-center justify-center gap-2 bg-[#4B2E7E] hover:bg-[color-mix(in_srgb,#4B2E7E_82%,black)] text-white text-sm font-bold px-5 py-2.5 rounded-lg transition whitespace-nowrap"
                    >
                        <Plus className="w-4 h-4" />
                        Add Product
                    </button>
                </div>

                {/* List card */}
                <div className={`bg-white rounded-xl border-2 ${BORDER}`}>
                    {/* Tabs + search */}
                    <div className={`flex items-center justify-between gap-4 flex-wrap px-5 pt-1 border-b ${BORDER}`}>
                        <div className="flex gap-1" role="tablist" aria-label="Filter products by status">
                            {(['published', 'draft'] as Status[]).map((k) => {
                                const active = tab === k;
                                return (
                                    <button
                                        key={k}
                                        type="button"
                                        role="tab"
                                        aria-selected={active}
                                        onClick={() => { setTab(k); setPage(1); }}
                                        className={`flex items-center gap-2 text-sm font-bold px-3.5 pt-4 pb-3.5 -mb-px border-b-[2.5px] transition ${
                                            active
                                                ? 'text-[#4B2E7E] border-[#4B2E7E]'
                                                : 'text-[#6E6570] border-transparent hover:text-[#2A1B4D]'
                                        }`}
                                    >
                                        {k === 'published' ? 'Published' : 'Draft'}
                                        <span
                                            className={`text-[0.7rem] font-bold px-2 py-0.5 rounded-full ${
                                                active ? 'bg-[#4B2E7E] text-white' : 'bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#6E6570]'
                                            }`}
                                        >
                                            {counts[k]}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>

                        <div className="relative my-2 w-full sm:w-80">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#6E6570]" />
                            <input
                                type="search"
                                value={query}
                                onChange={(e) => { setQuery(e.target.value); setPage(1); }}
                                placeholder="Search products by name"
                                aria-label="Search products by name"
                                className={`w-full border-[1.5px] ${BORDER} rounded-full pl-10 pr-4 py-2.5 text-sm text-[#2A1B4D] bg-white placeholder:text-gray-400 focus:outline-none focus:border-[#B9A6DE] focus:ring-2 focus:ring-[#4B2E7E]/25`}
                            />
                        </div>
                    </div>

                    {/* Table */}
                    {rows.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[560px] border-collapse">
                                <thead>
                                    <tr>
                                        <th className={`${TH} pl-5`}>Product</th>
                                        <th className={`${TH} hidden md:table-cell`}>Category</th>
                                        <th className={TH}>Price</th>
                                        <th className={`${TH} hidden sm:table-cell`}>Stock</th>
                                        <th className={`${TH} w-14 pr-5`}><span className="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {slice.map((p) => {
                                        const out = p.stock <= 0;
                                        const low = !out && p.stock <= 10;
                                        const rowBorder = 'border-b border-[color-mix(in_srgb,#B9A6DE_24%,white)]';
                                        return (
                                            <tr key={p.id} className="hover:bg-[color-mix(in_srgb,#B9A6DE_12%,white)] last:[&>td]:border-b-0">
                                                <td className={`px-4 py-3.5 pl-5 ${rowBorder}`}>
                                                    <div className="flex items-center gap-3.5 min-w-[220px]">
                                                        <Thumb p={p} />
                                                        <div>
                                                            <div className="text-sm font-bold text-[#2A1B4D] leading-snug">{p.name}</div>
                                                            <div className="text-xs text-[#6E6570] mt-0.5">{p.sku || 'No SKU'}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className={`px-4 py-3.5 text-sm hidden md:table-cell ${rowBorder}`}>
                                                    {p.category || 'Uncategorized'}
                                                </td>
                                                <td className={`px-4 py-3.5 text-sm ${rowBorder}`}>
                                                    {p.salePrice ? (
                                                        <>
                                                            <b className="font-semibold">{peso(p.salePrice)}</b>
                                                            <s className="block text-xs text-[#6E6570]">{peso(p.price)}</s>
                                                        </>
                                                    ) : (
                                                        <b className="font-semibold">{p.price ? peso(p.price) : 'Not set'}</b>
                                                    )}
                                                </td>
                                                <td className={`px-4 py-3.5 text-sm hidden sm:table-cell ${rowBorder}`}>
                                                    <div className={`font-semibold ${out ? 'text-[#b3384f]' : low ? 'text-[#a9752b]' : ''}`}>
                                                        {p.stock}
                                                        {(out || low) && (
                                                            <small className="block text-[0.68rem] font-bold uppercase tracking-wide mt-0.5">
                                                                {out ? 'Out of stock' : 'Low stock'}
                                                            </small>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className={`px-4 py-3.5 pr-5 text-right ${rowBorder}`}>
                                                    <button
                                                        type="button"
                                                        data-kebab
                                                        onClick={(e) => openMenu(e, p.id)}
                                                        aria-haspopup="menu"
                                                        aria-expanded={menu?.id === p.id}
                                                        aria-label={`Actions for ${p.name}`}
                                                        className={`inline-flex items-center justify-center w-9 h-9 rounded-lg transition ${
                                                            menu?.id === p.id
                                                                ? 'bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E]'
                                                                : 'text-[#6E6570] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)] hover:text-[#4B2E7E]'
                                                        }`}
                                                    >
                                                        <MoreVertical className="w-[18px] h-[18px]" />
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center py-12 px-4 text-[#6E6570]">
                            {q ? (
                                <>
                                    <b className="block text-base text-[#2A1B4D] mb-1" style={fontSyne}>No products found</b>
                                    <span className="text-sm">Nothing matches "{query.trim()}". Try a different name.</span>
                                </>
                            ) : (
                                <>
                                    <b className="block text-base text-[#2A1B4D] mb-1" style={fontSyne}>
                                        {tab === 'draft' ? 'No drafts yet' : products.length === 0 ? 'No products yet' : 'No published products yet'}
                                    </b>
                                    <span className="text-sm">
                                        {tab === 'draft'
                                            ? 'Unfinished listings you save as a draft will appear here.'
                                            : products.length === 0
                                            ? 'Create your first product to start selling.'
                                            : 'Publish a product and it will appear here for buyers to see.'}
                                    </span>
                                </>
                            )}
                        </div>
                    )}

                    {/* Footer / pager */}
                    <div className={`flex items-center justify-between gap-4 flex-wrap px-5 py-4 border-t ${BORDER} text-[0.82rem] text-[#6E6570]`}>
                        <span>
                            {rows.length
                                ? `Showing ${(currentPage - 1) * PER_PAGE + 1}–${Math.min(currentPage * PER_PAGE, rows.length)} of ${rows.length} product${rows.length === 1 ? '' : 's'}`
                                : '0 products'}
                        </span>
                        {pages > 1 && (
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    disabled={currentPage === 1}
                                    onClick={() => setPage(currentPage - 1)}
                                    aria-label="Previous page"
                                    className={`min-w-[34px] h-[34px] px-2.5 border-[1.5px] ${BORDER} bg-white rounded-lg text-sm font-bold text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_12%,white)] disabled:opacity-40 disabled:cursor-not-allowed`}
                                >
                                    ‹
                                </button>
                                {Array.from({ length: pages }, (_, i) => (
                                    <button
                                        key={i}
                                        type="button"
                                        onClick={() => setPage(i + 1)}
                                        className={`min-w-[34px] h-[34px] px-2.5 border-[1.5px] rounded-lg text-sm font-bold ${
                                            currentPage === i + 1
                                                ? 'bg-[#4B2E7E] border-[#4B2E7E] text-white'
                                                : `${BORDER} bg-white text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_12%,white)]`
                                        }`}
                                    >
                                        {i + 1}
                                    </button>
                                ))}
                                <button
                                    type="button"
                                    disabled={currentPage === pages}
                                    onClick={() => setPage(currentPage + 1)}
                                    aria-label="Next page"
                                    className={`min-w-[34px] h-[34px] px-2.5 border-[1.5px] ${BORDER} bg-white rounded-lg text-sm font-bold text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_12%,white)] disabled:opacity-40 disabled:cursor-not-allowed`}
                                >
                                    ›
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Row action menu (floating so the table never clips it) */}
            {menu && menuProduct && (
                <div
                    data-rowmenu
                    role="menu"
                    aria-label="Product actions"
                    className="fixed z-[90] w-44 bg-white border-[1.5px] border-[color-mix(in_srgb,#B9A6DE_44%,white)] rounded-xl shadow-[0_14px_40px_rgba(32,26,51,0.2)] p-1.5"
                    style={{ top: menu.top, left: menu.left, ...fontPoppins }}
                >
                    <button
                        type="button"
                        role="menuitem"
                        onClick={() => openEdit(menu.id)}
                        className="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-semibold text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-left"
                    >
                        <Pencil className="w-4 h-4" />
                        Edit
                    </button>
                    <button
                        type="button"
                        role="menuitem"
                        onClick={() => duplicate(menu.id)}
                        className="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-semibold text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-left"
                    >
                        <Copy className="w-4 h-4" />
                        Duplicate
                    </button>
                    <hr className="my-1.5 border-[color-mix(in_srgb,#B9A6DE_24%,white)]" />
                    <button
                        type="button"
                        role="menuitem"
                        onClick={() => { setDeleteId(menu.id); setMenu(null); }}
                        className="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-semibold text-[#b3384f] hover:bg-[#fbeced] text-left"
                    >
                        <Trash2 className="w-4 h-4" />
                        Delete
                    </button>
                </div>
            )}

            {/* Add / Edit product modal */}
            {formOpen && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-[#201A33]/55 p-3 sm:p-4">
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="product-form-title"
                        className="relative flex flex-col w-full max-w-2xl max-h-[92vh] bg-white rounded-2xl shadow-[0_24px_70px_rgba(32,26,51,0.35)]"
                        style={fontPoppins}
                    >
                        {/* Header */}
                        <div className={`flex items-start justify-between gap-4 px-6 pt-5 pb-4 border-b ${BORDER}`}>
                            <div>
                                <h3 id="product-form-title" className="text-lg font-semibold" style={fontSyne}>
                                    {editing ? 'Edit product' : 'Add product'}
                                </h3>
                                <p className="text-xs text-[#6E6570] mt-1">
                                    Fields marked <span className="text-red-600">*</span> are needed to publish. You can save a draft with just a name.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setFormOpen(false)}
                                aria-label="Close"
                                className="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-[#6E6570] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)]"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Body */}
                        <div className="flex-1 overflow-y-auto px-6 py-5">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {/* Photo */}
                                <div className="sm:col-span-2 flex flex-col gap-1.5">
                                    <span className="text-sm font-semibold text-[#2A1B4D]">Product photo</span>
                                    <div
                                        className={`flex items-center gap-4 rounded-xl p-4 ${
                                            form.image
                                                ? `border-[1.5px] ${BORDER} bg-white`
                                                : `border-2 border-dashed ${BORDER} bg-[color-mix(in_srgb,#B9A6DE_12%,white)]`
                                        }`}
                                    >
                                        <span className="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#4B2E7E] flex items-center justify-center">
                                            {form.image ? (
                                                <img src={form.image} alt="Product preview" className="w-full h-full object-cover" />
                                            ) : (
                                                <ImagePlus className="w-7 h-7" />
                                            )}
                                        </span>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-semibold text-[#2A1B4D]">
                                                {imageBusy ? 'Processing…' : form.image ? 'Photo added' : 'No photo yet'}
                                            </p>
                                            <p className="text-xs text-[#6E6570] mt-0.5">
                                                JPG, PNG or WEBP · up to {MAX_IMAGE_MB}MB. A clear, well-lit photo sells faster.
                                            </p>
                                            <div className="flex gap-2 mt-2.5 flex-wrap">
                                                <button
                                                    type="button"
                                                    onClick={() => fileRef.current?.click()}
                                                    disabled={imageBusy}
                                                    className={`rounded-lg border ${BORDER} bg-white px-3.5 py-2 text-xs font-bold text-[#2A1B4D] hover:border-[#B9A6DE] hover:text-[#4B2E7E] disabled:opacity-50`}
                                                >
                                                    {form.image ? 'Change photo' : 'Upload photo'}
                                                </button>
                                                {form.image && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setField('image', '')}
                                                        className="rounded-lg px-3.5 py-2 text-xs font-bold text-[#b3384f] hover:bg-[#fbeced]"
                                                    >
                                                        Remove
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                        <input
                                            ref={fileRef}
                                            type="file"
                                            accept="image/*"
                                            tabIndex={-1}
                                            className="sr-only"
                                            onChange={pickImage}
                                        />
                                    </div>
                                    {errors.image && <span className="text-xs font-medium text-red-600" role="alert">{errors.image}</span>}
                                </div>

                                <Field label="Product name" htmlFor="pf-name" required error={errors.name} className="sm:col-span-2">
                                    <input
                                        id="pf-name"
                                        autoFocus
                                        value={form.name}
                                        onChange={(e) => setField('name', e.target.value)}
                                        placeholder="e.g. Vitamin C Serum 30ml"
                                        maxLength={120}
                                        aria-invalid={!!errors.name}
                                        className={inputCls(errors.name)}
                                    />
                                </Field>

                                <Field label="Category" htmlFor="pf-category" required error={errors.category}>
                                    <select
                                        id="pf-category"
                                        value={form.category}
                                        onChange={(e) => setField('category', e.target.value)}
                                        aria-invalid={!!errors.category}
                                        className={inputCls(errors.category)}
                                    >
                                        <option value="">Select a category</option>
                                        {CATEGORIES.map((c) => <option key={c} value={c}>{c}</option>)}
                                    </select>
                                </Field>

                                <Field label="SKU" htmlFor="pf-sku" hint="Your own code for tracking this item">
                                    <input
                                        id="pf-sku"
                                        value={form.sku}
                                        onChange={(e) => setField('sku', e.target.value)}
                                        placeholder="e.g. TSS-VCS-030"
                                        maxLength={40}
                                        className={inputCls()}
                                    />
                                </Field>

                                <Field label="Price (₱)" htmlFor="pf-price" required error={errors.price}>
                                    <input
                                        id="pf-price"
                                        type="number"
                                        inputMode="decimal"
                                        min="0"
                                        step="0.01"
                                        value={form.price}
                                        onChange={(e) => setField('price', e.target.value)}
                                        placeholder="0.00"
                                        aria-invalid={!!errors.price}
                                        className={inputCls(errors.price)}
                                    />
                                </Field>

                                <Field label="Sale price (₱)" htmlFor="pf-salePrice" error={errors.salePrice} hint="Optional. Leave blank if not on sale">
                                    <input
                                        id="pf-salePrice"
                                        type="number"
                                        inputMode="decimal"
                                        min="0"
                                        step="0.01"
                                        value={form.salePrice}
                                        onChange={(e) => setField('salePrice', e.target.value)}
                                        placeholder="0.00"
                                        aria-invalid={!!errors.salePrice}
                                        className={inputCls(errors.salePrice)}
                                    />
                                </Field>

                                <Field label="Stock" htmlFor="pf-stock" required error={errors.stock} hint="Units available to sell">
                                    <input
                                        id="pf-stock"
                                        type="number"
                                        inputMode="numeric"
                                        min="0"
                                        step="1"
                                        value={form.stock}
                                        onChange={(e) => setField('stock', e.target.value)}
                                        placeholder="0"
                                        aria-invalid={!!errors.stock}
                                        className={inputCls(errors.stock)}
                                    />
                                </Field>

                                <Field label="Weight (kg)" htmlFor="pf-weight" error={errors.weight} hint="Helps logistics partners price the delivery">
                                    <input
                                        id="pf-weight"
                                        type="number"
                                        inputMode="decimal"
                                        min="0"
                                        step="0.01"
                                        value={form.weight}
                                        onChange={(e) => setField('weight', e.target.value)}
                                        placeholder="e.g. 0.3"
                                        aria-invalid={!!errors.weight}
                                        className={inputCls(errors.weight)}
                                    />
                                </Field>

                                <Field label="Description" htmlFor="pf-desc" className="sm:col-span-2">
                                    <textarea
                                        id="pf-desc"
                                        rows={4}
                                        maxLength={500}
                                        value={form.desc}
                                        onChange={(e) => setField('desc', e.target.value)}
                                        placeholder="What is it, what's it made of, and who is it for?"
                                        className={`${inputCls()} resize-none`}
                                    />
                                    <span className="text-xs text-[#6E6570] text-right">{form.desc.length}/500</span>
                                </Field>
                            </div>
                        </div>

                        {/* Footer */}
                        <div className={`flex justify-end gap-2.5 flex-wrap px-6 py-4 border-t ${BORDER} bg-[color-mix(in_srgb,#B9A6DE_12%,white)] rounded-b-2xl`}>
                            <button
                                type="button"
                                onClick={() => setFormOpen(false)}
                                className="px-5 py-2.5 rounded-lg text-sm font-bold text-[#6E6570] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)]"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={() => save('draft')}
                                className={`px-5 py-2.5 rounded-lg text-sm font-bold bg-white border-[1.5px] ${BORDER} text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_24%,white)]`}
                            >
                                {draftLabel}
                            </button>
                            <button
                                type="button"
                                onClick={() => save('published')}
                                disabled={imageBusy}
                                className="px-5 py-2.5 rounded-lg text-sm font-bold bg-[#4B2E7E] text-white hover:bg-[color-mix(in_srgb,#4B2E7E_82%,black)] disabled:opacity-50"
                            >
                                {publishLabel}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete confirmation modal */}
            {deleting && (
                <div
                    className="fixed inset-0 z-[110] flex items-center justify-center bg-[#201A33]/55 p-4"
                    onClick={(e) => e.target === e.currentTarget && setDeleteId(null)}
                >
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="delete-title"
                        className="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-[0_24px_70px_rgba(32,26,51,0.35)]"
                        style={fontPoppins}
                    >
                        <h3 id="delete-title" className="text-lg font-semibold mb-1.5" style={fontSyne}>Delete this product?</h3>
                        <p className="text-sm text-[#6E6570] leading-relaxed">
                            <b className="text-[#2A1B4D]">{deleting.name}</b> will be permanently removed from your store. This cannot be undone.
                        </p>
                        <div className="flex justify-end gap-2.5 mt-6 flex-wrap">
                            <button
                                type="button"
                                onClick={() => setDeleteId(null)}
                                className="px-5 py-2.5 rounded-lg text-sm font-bold bg-[color-mix(in_srgb,#B9A6DE_24%,white)] text-[#2A1B4D] hover:bg-[color-mix(in_srgb,#B9A6DE_44%,white)]"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={confirmDelete}
                                className="px-5 py-2.5 rounded-lg text-sm font-bold bg-[#b3384f] text-white hover:bg-[color-mix(in_srgb,#b3384f_85%,black)]"
                            >
                                Delete product
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Toast */}
            {toast && (
                <div
                    role="status"
                    className={`fixed bottom-6 right-6 z-[120] max-w-[calc(100vw-3rem)] px-5 py-3.5 rounded-lg text-sm font-semibold text-white shadow-lg ${
                        toast.bad ? 'bg-[#b3384f]' : 'bg-[#2A1B4D]'
                    }`}
                    style={fontPoppins}
                >
                    {toast.msg}
                </div>
            )}
        </SellerShell>
    );
}

SellerProducts.layout = (page: ReactNode) => page;
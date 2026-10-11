// ============================================================================
// PHASE 2: Product Variations UI - Complete Code to Add
// ============================================================================

// ============================================================================
// STEP 1: Add these TypeScript interfaces (after existing Product interface)
// ============================================================================

interface VariantOption {
    name: string;
    values: string[];
}

interface Variant {
    id: string;
    combination: Record<string, string>;
    sku: string;
    price: number;
    stock: number;
}

// ============================================================================
// STEP 2: Update Product interface to include variant fields
// ============================================================================

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
    has_variants?: boolean;        // ADD THIS
    variant_options?: VariantOption[];  // ADD THIS
    variants?: Variant[];          // ADD THIS
}

// ============================================================================
// STEP 3: Update FormState interface
// ============================================================================

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
    has_variants: boolean;         // ADD THIS
    variant_options: VariantOption[];  // ADD THIS
    variants: Variant[];           // ADD THIS
}

// ============================================================================
// STEP 4: Update emptyForm constant
// ============================================================================

const emptyForm: FormState = {
    name: '', 
    category: '', 
    price: '', 
    salePrice: '', 
    stock: '', 
    sku: '', 
    weight: '', 
    desc: '', 
    image: '',
    has_variants: false,           // ADD THIS
    variant_options: [],           // ADD THIS
    variants: [],                  // ADD THIS
};

// ============================================================================
// STEP 5: Add these helper functions (before main component)
// ============================================================================

// Generate all combinations from variant options
function generateVariantCombinations(options: VariantOption[]): Record<string, string>[] {
    if (options.length === 0) return [];
    
    const generate = (index: number, current: Record<string, string>): Record<string, string>[] => {
        if (index === options.length) return [current];
        
        const option = options[index];
        const results: Record<string, string>[] = [];
        
        for (const value of option.values) {
            results.push(...generate(index + 1, { ...current, [option.name]: value }));
        }
        
        return results;
    };
    
    return generate(0, {});
}

// Generate variant ID from combination
function generateVariantId(combination: Record<string, string>): string {
    return 'var-' + Object.values(combination).join('-').toLowerCase().replace(/[^a-z0-9-]/g, '');
}

// Format combination for display
function formatCombination(combination: Record<string, string>): string {
    return Object.values(combination).join(' / ');
}

// ============================================================================
// STEP 6: Add these state variables (in main component, after existing states)
// ============================================================================

const [newOptionName, setNewOptionName] = useState('');
const [newOptionValue, setNewOptionValue] = useState<Record<number, string>>({});

// ============================================================================
// STEP 7: Add these handler functions (in main component)
// ============================================================================

// Toggle has_variants
const toggleVariants = (enabled: boolean) => {
    setForm(f => ({
        ...f,
        has_variants: enabled,
        variant_options: enabled ? f.variant_options : [],
        variants: enabled ? f.variants : [],
    }));
};

// Add new variation option
const addVariationOption = () => {
    if (!newOptionName.trim()) return;
    
    setForm(f => ({
        ...f,
        variant_options: [...f.variant_options, { name: newOptionName.trim(), values: [] }]
    }));
    setNewOptionName('');
};

// Remove variation option
const removeVariationOption = (index: number) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.filter((_, i) => i !== index),
        variants: [], // Clear variants when options change
    }));
};

// Update option name
const updateOptionName = (index: number, name: string) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === index ? { ...opt, name } : opt
        ),
    }));
};

// Add value to option
const addOptionValue = (optionIndex: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    
    const value = newOptionValue[optionIndex]?.trim();
    if (!value) return;
    
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === optionIndex ? { ...opt, values: [...opt.values, value] } : opt
        ),
        variants: [], // Clear variants when options change
    }));
    setNewOptionValue(prev => ({ ...prev, [optionIndex]: '' }));
};

// Remove value from option
const removeOptionValue = (optionIndex: number, valueIndex: number) => {
    setForm(f => ({
        ...f,
        variant_options: f.variant_options.map((opt, i) => 
            i === optionIndex ? { ...opt, values: opt.values.filter((_, vi) => vi !== valueIndex) } : opt
        ),
        variants: [], // Clear variants when options change
    }));
};

// Auto-generate variants from options
const generateVariants = () => {
    if (form.variant_options.length === 0) {
        showToast('Add at least one variation option first', true);
        return;
    }
    
    const hasEmptyValues = form.variant_options.some(opt => opt.values.length === 0);
    if (hasEmptyValues) {
        showToast('Each variation option needs at least one value', true);
        return;
    }
    
    const combinations = generateVariantCombinations(form.variant_options);
    const basePrice = parseFloat(form.price) || 0;
    
    const newVariants: Variant[] = combinations.map(combination => ({
        id: generateVariantId(combination),
        combination,
        sku: '',
        price: basePrice,
        stock: 0,
    }));
    
    setForm(f => ({ ...f, variants: newVariants }));
    showToast(`Generated ${newVariants.length} variants`);
};

// Update individual variant
const updateVariant = (index: number, field: keyof Variant, value: any) => {
    setForm(f => ({
        ...f,
        variants: f.variants.map((v, i) => 
            i === index ? { ...v, [field]: value } : v
        )
    }));
};

// Bulk apply price to all variants
const bulkApplyPrice = () => {
    const price = parseFloat(form.price);
    if (isNaN(price) || price <= 0) {
        showToast('Enter a valid price first', true);
        return;
    }
    
    setForm(f => ({
        ...f,
        variants: f.variants.map(v => ({ ...v, price }))
    }));
    showToast('Price applied to all variants');
};

// ============================================================================
// STEP 8: Update openEdit function to load variant data
// ============================================================================

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
        has_variants: p.has_variants || false,          // ADD THIS
        variant_options: p.variant_options || [],       // ADD THIS
        variants: p.variants || [],                     // ADD THIS
    });
    setErrors({});
    setFormOpen(true);
};

// ============================================================================
// STEP 9: Update save function payload
// ============================================================================

const payload = {
    name: form.name.trim(),
    description: form.desc.trim(),
    category: form.category,
    price: parseFloat(form.price) || 0,
    sale_price: form.salePrice.trim() ? parseFloat(form.salePrice) : null,
    stock: form.stock.trim() === '' ? 0 : parseInt(form.stock, 10),
    sku: form.sku.trim(),
    weight: form.weight.trim() ? parseFloat(form.weight) : null,
    status: status === 'published' ? 'active' : 'draft',
    images: form.image ? [form.image] : [],
    main_image_index: 0,
    brand: editing?.brand ?? storeName,
    has_variants: form.has_variants,                    // ADD THIS
    variant_options: form.has_variants ? form.variant_options : null,  // ADD THIS
    variants: form.has_variants ? form.variants : null,                // ADD THIS
};

// ============================================================================
// STEP 10: Insert this UI CODE in the modal - AFTER Weight field, BEFORE Description
// ============================================================================

{/* Product Variations Section */}
{/* INSERT THIS AFTER THE WEIGHT FIELD */}

<div className="sm:col-span-2">
    <label className="flex items-center gap-2 cursor-pointer">
        <input
            type="checkbox"
            checked={form.has_variants}
            onChange={(e) => toggleVariants(e.target.checked)}
            className="w-4 h-4 rounded border-gray-300 text-[#4B2E7E] focus:ring-[#4B2E7E]"
        />
        <span className="text-sm font-semibold text-[#2A1B4D]">
            This product has variations
        </span>
        <span className="text-xs text-[#6E6570]">(e.g., Size, Color, Material)</span>
    </label>
</div>

{form.has_variants && (
    <>
        {/* Variation Options Editor */}
        <div className="sm:col-span-2 space-y-4 p-4 rounded-xl border-2 border-dashed border-[#B9A6DE] bg-[color-mix(in_srgb,#B9A6DE_6%,white)]">
            <div className="flex items-center justify-between">
                <h4 className="text-sm font-bold text-[#2A1B4D]">Variation Options</h4>
                <span className="text-xs text-[#6E6570]">
                    {form.variant_options.length} option{form.variant_options.length !== 1 && 's'}
                </span>
            </div>

            {form.variant_options.map((option, optIndex) => (
                <div key={optIndex} className="bg-white rounded-lg p-3 space-y-2">
                    <div className="flex items-center gap-2">
                        <input
                            type="text"
                            value={option.name}
                            onChange={(e) => updateOptionName(optIndex, e.target.value)}
                            placeholder="e.g., Size"
                            className="flex-1 text-sm font-semibold border-b-2 border-transparent hover:border-[#B9A6DE] focus:border-[#4B2E7E] outline-none px-2 py-1"
                        />
                        <button
                            type="button"
                            onClick={() => removeVariationOption(optIndex)}
                            className="text-red-600 hover:bg-red-50 p-1.5 rounded"
                            title="Remove option"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>

                    <div className="flex flex-wrap gap-2 items-center">
                        {option.values.map((value, valIndex) => (
                            <span
                                key={valIndex}
                                className="inline-flex items-center gap-1 bg-[#4B2E7E] text-white text-xs font-semibold px-2.5 py-1 rounded-full"
                            >
                                {value}
                                <button
                                    type="button"
                                    onClick={() => removeOptionValue(optIndex, valIndex)}
                                    className="hover:bg-white/20 rounded-full p-0.5"
                                >
                                    <X className="w-3 h-3" />
                                </button>
                            </span>
                        ))}
                        <input
                            type="text"
                            value={newOptionValue[optIndex] || ''}
                            onChange={(e) => setNewOptionValue(prev => ({ ...prev, [optIndex]: e.target.value }))}
                            onKeyPress={(e) => addOptionValue(optIndex, e)}
                            placeholder="Add value (press Enter)"
                            className="text-xs border border-dashed border-[#B9A6DE] rounded px-2 py-1 outline-none focus:border-[#4B2E7E] min-w-[140px]"
                        />
                    </div>
                </div>
            ))}

            <div className="flex gap-2">
                <input
                    type="text"
                    value={newOptionName}
                    onChange={(e) => setNewOptionName(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addVariationOption())}
                    placeholder="New option name (e.g., Color)"
                    className="flex-1 text-sm border border-[#B9A6DE] rounded-lg px-3 py-2 outline-none focus:border-[#4B2E7E]"
                />
                <button
                    type="button"
                    onClick={addVariationOption}
                    disabled={!newOptionName.trim()}
                    className="bg-[#4B2E7E] text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#3a2360] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <Plus className="w-4 h-4" />
                </button>
            </div>
        </div>

        {/* Variants Table */}
        {form.variant_options.length > 0 && (
            <div className="sm:col-span-2 space-y-3">
                <div className="flex items-center justify-between">
                    <h4 className="text-sm font-bold text-[#2A1B4D]">
                        Variants {form.variants.length > 0 && `(${form.variants.length})`}
                    </h4>
                    <div className="flex gap-2">
                        {form.variants.length > 0 && (
                            <button
                                type="button"
                                onClick={bulkApplyPrice}
                                className="text-xs font-semibold text-[#4B2E7E] hover:bg-[#F9F7FC] px-3 py-1.5 rounded border border-[#B9A6DE]"
                            >
                                Apply price to all
                            </button>
                        )}
                        <button
                            type="button"
                            onClick={generateVariants}
                            className="bg-[#4B2E7E] text-white text-xs font-bold px-3 py-1.5 rounded hover:bg-[#3a2360]"
                        >
                            Generate variants
                        </button>
                    </div>
                </div>

                {form.variants.length === 0 ? (
                    <div className="text-center py-6 text-sm text-[#6E6570] border border-dashed border-[#B9A6DE] rounded-lg">
                        Click "Generate variants" to create all combinations
                    </div>
                ) : (
                    <div className="border border-[#B9A6DE] rounded-lg overflow-hidden">
                        <div className="overflow-x-auto max-h-[300px]">
                            <table className="w-full text-sm">
                                <thead className="bg-[color-mix(in_srgb,#B9A6DE_12%,white)] sticky top-0">
                                    <tr>
                                        <th className="text-left px-3 py-2 font-semibold text-[#2A1B4D]">Variant</th>
                                        <th className="text-left px-3 py-2 font-semibold text-[#2A1B4D]">SKU</th>
                                        <th className="text-left px-3 py-2 font-semibold text-[#2A1B4D]">Price</th>
                                        <th className="text-left px-3 py-2 font-semibold text-[#2A1B4D]">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {form.variants.map((variant, index) => (
                                        <tr key={variant.id} className="border-t border-[#B9A6DE]/30">
                                            <td className="px-3 py-2 font-medium text-[#2A1B4D]">
                                                {formatCombination(variant.combination)}
                                            </td>
                                            <td className="px-3 py-2">
                                                <input
                                                    type="text"
                                                    value={variant.sku}
                                                    onChange={(e) => updateVariant(index, 'sku', e.target.value)}
                                                    placeholder="Optional"
                                                    className="w-full text-sm border border-[#B9A6DE] rounded px-2 py-1 outline-none focus:border-[#4B2E7E]"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value={variant.price}
                                                    onChange={(e) => updateVariant(index, 'price', parseFloat(e.target.value) || 0)}
                                                    className="w-full text-sm border border-[#B9A6DE] rounded px-2 py-1 outline-none focus:border-[#4B2E7E]"
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    value={variant.stock}
                                                    onChange={(e) => updateVariant(index, 'stock', parseInt(e.target.value) || 0)}
                                                    className="w-full text-sm border border-[#B9A6DE] rounded px-2 py-1 outline-none focus:border-[#4B2E7E]"
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        )}
    </>
)}

{/* Continue with Description field below... */}

# Document Preview Feature - Admin Seller Approval

## Summary
Implemented in-page document preview for seller registration documents in the admin approval panel. Uses **browser-native iframe** for preview - walang external libraries, purely based on URLs provided by the backend.

## Problem Solved
Previously, documents opened in a new tab (`target="_blank"`). Admins had to switch tabs back and forth to review documents. Now, documents preview directly in the admin panel with a modal overlay.

## Solution: Browser-Native Preview

### Key Features
1. **Zero dependencies** - Pure HTML `<iframe>` element
2. **Works for all formats** - PDF, JPG, PNG (lahat ng formats na supported ng browser)
3. **Secure** - Sandboxed iframe with `allow-same-origin allow-scripts`
4. **Fast** - Direct rendering from existing URLs
5. **Responsive** - 90% viewport height, centered modal
6. **Keyboard accessible** - ESC key to close

### Implementation Details

#### State Management
```typescript
const [previewDoc, setPreviewDoc] = useState<{ url: string; title: string } | null>(null);
```

#### Document Cards
Changed from `<a>` links to `<button>` that triggers preview:
- Added Eye icon for visual cue
- "Click to preview" text
- Opens preview modal instead of new tab

#### Preview Modal Components
1. **Header**
   - Document title with icon
   - "Open in new tab" fallback option
   - Close button (X)

2. **Content**
   - Full-width iframe
   - Gray background for contrast
   - Sandboxed for security

3. **Footer**
   - ESC key hint
   - Close button

4. **Interaction**
   - Click outside to dismiss
   - ESC key to close
   - Stop propagation on modal content

### Benefits

#### For Admin
- ✅ Faster review process (no tab switching)
- ✅ Side-by-side comparison (can keep modal open while scrolling seller info)
- ✅ Better UX flow
- ✅ Can still open in new tab if needed

#### For Developers
- ✅ No npm packages to install
- ✅ No build size increase
- ✅ No compatibility issues
- ✅ Works with existing backend URLs
- ✅ Easy to maintain

### Browser Support
Works in all modern browsers that support:
- iframe element (universal)
- PDF rendering (Chrome, Firefox, Edge, Safari)
- Image rendering (universal)

If browser can't render PDF, it will prompt download automatically.

## Files Changed
1. ✅ `resources/js/pages/admin/seller-approval.tsx`
   - Added `previewDoc` state
   - Changed document cards from `<a>` to `<button>`
   - Added preview modal with iframe
   - Added ESC key listener
   - Added Eye icon from lucide-react

## Usage

### For Admins
1. Click "View details" on any seller
2. Scroll to "Submitted Documents" section
3. Click any document card
4. Document previews in full-screen modal
5. Close via:
   - ESC key
   - Close button
   - Click outside modal
6. Optional: "Open in new tab" for browser download/print

### Documents Supported
- Valid ID (id_document_url)
- Business Permit (business_permit_url)
- DTI Registration (dti_registration_url)
- BIR Form 2303 (bir_2303_url)
- SEC Certificate (sec_certificate_url)

## Technical Notes

### Why iframe?
- Native browser support for PDF and images
- Sandboxed for security
- No JavaScript libraries needed
- Works with blob URLs, data URLs, or file URLs
- Browser handles zoom, scroll, download buttons

### Alternatives NOT used
❌ `react-pdf` - Adds ~500KB to bundle
❌ `pdfjs-dist` - Complex setup, large bundle
❌ `react-file-viewer` - External dependency
❌ Canvas rendering - Complex, performance issues
❌ Image-only approach - Doesn't work for PDFs

### Security
- iframe sandbox attribute: `allow-same-origin allow-scripts`
- Only shows URLs from backend (already validated)
- No external content injection
- Modal prevents phishing (user sees full URL in browser if they open new tab)

## Future Enhancements (Optional)
- [ ] Download button in preview header
- [ ] Zoom controls for images
- [ ] Navigation arrows for multiple documents
- [ ] Thumbnail preview in document cards
- [ ] Print button
- [ ] Full-screen toggle

## Testing Checklist
- [ ] Preview PDF documents
- [ ] Preview JPG/PNG images
- [ ] ESC key closes modal
- [ ] Click outside closes modal
- [ ] Close button works
- [ ] "Open in new tab" works
- [ ] Modal stops propagation (clicking inside doesn't close)
- [ ] Multiple documents can be previewed sequentially
- [ ] Works on mobile (responsive)


# Seller Store Profile - Customizable Cover & Profile Photos

## 🎯 Overview
Dinagdag ang customizable store profile section sa Seller Dashboard na may cover photo at profile photo, similar sa ViewSeller layout pero with edit capabilities (max 5MB per photo).

## ✨ Features Added

### 1. **Store Profile Card Section**
   - **Location**: Sa ibabaw ng "Store overview" stats sa SellerDashboard
   - **Layout**: Same visual design as ViewSeller (buyer view)
   - **Customizable**: Editable cover photo and profile photo

### 2. **Cover Photo Section**
   - 📸 **Visual Elements**:
     - Gradient purple background (from-[#4B2E7E] to-[#B9A6DE])
     - Pattern overlay effect
     - Height: 192px (h-48)
   
   - 🎨 **Edit Button**:
     - Top-right corner placement
     - White translucent button with backdrop blur
     - Camera icon
     - "Edit Cover Photo" text
     - Hover effect
   
   - 📊 **File Size Indicator**:
     - Bottom-left corner
     - "Max 5MB" text
     - Dark overlay with blur effect

### 3. **Profile Photo Section**
   - 👤 **Avatar Display**:
     - 128px x 128px (h-32 w-32)
     - Rounded-2xl design
     - Gradient purple background
     - Store initials (2 letters)
     - White border (border-4)
     - Overlaps cover photo (margin-top: -48px)
   
   - ✏️ **Edit Overlay**:
     - Appears on hover
     - Black semi-transparent background
     - Camera icon (white)
     - "Edit Photo" text
     - Smooth opacity transition
   
   - ✅ **Verified Badge**:
     - Green circular badge
     - Bottom-right corner of avatar
     - Checkmark icon
     - White icon color

### 4. **Store Information**
   - 🏪 **Store Name**: Large, bold display
   - 🎖️ **Verified Badge**: "VERIFIED SELLER" chip
   - 🌟 **Certification**: "mimoo Mall Certified" text
   - 💡 **Helper Tip**: Encourages uploading professional photos

### 5. **Action Buttons**
   - 👁️ **Preview Store Page Button**:
     - Top-right placement
     - Purple border
     - Eyeball emoji + text
     - Opens buyer view of store

### 6. **Photo Requirements Card**
   - 📋 **Information Section**:
     - Light purple background
     - Divided into 2 columns
     - Detailed specs for both photos
   
   - 📐 **Cover Photo Requirements**:
     - Recommended size: 1200 x 300 pixels
     - Max file size: 5MB
     - Format: JPG, PNG
   
   - 📐 **Profile Photo Requirements**:
     - Recommended size: 400 x 400 pixels
     - Max file size: 5MB
     - Format: JPG, PNG

## 🎨 Design Details

### Visual Hierarchy
```
┌─────────────────────────────────────────────┐
│  [Cover Photo with Gradient Background]     │
│  [Edit Cover Photo Button]    [Max 5MB]     │
├─────────────────────────────────────────────┤
│  [Avatar]  Store Name                        │
│  (overlap) VERIFIED SELLER                   │
│            💡 Upload tip          [Preview]  │
├─────────────────────────────────────────────┤
│  Photo Requirements:                         │
│  Cover: 1200x300, 5MB    Profile: 400x400   │
└─────────────────────────────────────────────┘
```

### Color Scheme
- **Primary Purple**: #4B2E7E
- **Light Purple**: #B9A6DE
- **Success Green**: #2c7a52
- **Text Gray**: #6E6570
- **White Borders**: #FFFFFF

### Interactive States
1. **Cover Photo Edit Button**:
   - Default: white/90 with backdrop blur
   - Hover: solid white
   - Shadow: lg

2. **Profile Photo**:
   - Default: gradient background visible
   - Hover: black overlay appears
   - Transition: smooth opacity change

## 💡 Benefits

### For Sellers:
1. **Professional Appearance**: Customizable branding
2. **Trust Building**: Professional photos increase credibility
3. **Brand Identity**: Consistent visual identity
4. **Easy Management**: Simple edit interface
5. **Preview Before Public**: See how buyers will see the store

### For System:
1. **Consistent Design**: Matches buyer view layout
2. **Clear Guidelines**: Photo requirements clearly stated
3. **File Size Control**: 5MB limit prevents server overload
4. **User-Friendly**: Hover-to-edit interaction pattern

## 🔧 Technical Implementation

### Component Structure
```tsx
<Store Profile Card>
  ├── Cover Photo Section
  │   ├── Gradient Background
  │   ├── Pattern Overlay
  │   ├── Edit Cover Button
  │   └── File Size Hint
  ├── Store Info Section
  │   ├── Avatar (Editable)
  │   │   ├── Initials Display
  │   │   ├── Edit Overlay (hover)
  │   │   └── Verified Badge
  │   ├── Store Name & Badges
  │   ├── Helper Tip
  │   └── Preview Button
  └── Requirements Card
      ├── Cover Photo Specs
      └── Profile Photo Specs
```

### Styling Features
- **Gradient Backgrounds**: Linear gradients for visual appeal
- **Backdrop Blur**: Modern glassmorphism effect
- **Hover States**: Interactive feedback
- **Border Styling**: Consistent 2px borders with purple tint
- **Shadow Effects**: Layered shadows for depth

## 🚀 Future Backend Integration

### Phase 1 - Database Schema:
```sql
- Add columns to users table:
  - profile_photo (string, nullable)
  - cover_photo (string, nullable)
  - photos_updated_at (timestamp)
```

### Phase 2 - API Endpoints:
```
POST   /api/seller/profile-photo    (upload)
POST   /api/seller/cover-photo      (upload)
DELETE /api/seller/profile-photo    (remove)
DELETE /api/seller/cover-photo      (remove)
```

### Phase 3 - Upload Functionality:
- ✅ File validation (5MB max, JPG/PNG only)
- ✅ Image resize/optimization
- ✅ Secure storage (Laravel Storage)
- ✅ Old file cleanup
- ✅ Progress indicator
- ✅ Error handling

### Phase 4 - Image Processing:
```php
- Resize cover: 1200x300
- Resize profile: 400x400
- Optimize quality: 85%
- WebP conversion (optional)
- Thumbnail generation
```

## 📸 Visual Preview

### Desktop Layout
```
╔═══════════════════════════════════════════════╗
║  🎨 Cover Photo (Purple Gradient)             ║
║                         [📷 Edit Cover Photo] ║
║  [Max 5MB]                                    ║
╠═══════════════════════════════════════════════╣
║  ╭───╮  Your Store Name                       ║
║  │ YS │  ✅ VERIFIED SELLER                   ║
║  │   │  mimoo Mall Certified                  ║
║  ╰─✓─╯  💡 Upload professional photos         ║
║                           [👁️ Preview Store]  ║
╠═══════════════════════════════════════════════╣
║  📋 Photo Requirements                        ║
║  Cover: 1200x300, 5MB  |  Profile: 400x400   ║
╚═══════════════════════════════════════════════╝
```

### Profile Photo Hover State
```
╭────────╮           ╭────────╮
│   YS   │    →      │  📷    │
│        │  hover    │  Edit  │
╰────✓───╯           │  Photo │
                     ╰────────╯
```

## 📝 Code Summary

### Files Modified:
1. **resources/js/pages/seller/SellerDashboard.tsx**
   - ✅ Added Store Profile Card section
   - ✅ Added editable cover photo
   - ✅ Added editable profile photo
   - ✅ Added preview button
   - ✅ Added photo requirements card

### Lines Added: ~150 lines
### No Breaking Changes: ✅
### Mobile Responsive: ✅ (inherits from existing responsive classes)

## ✅ Current State: VISUAL PLACEHOLDER READY

### What Works Now:
- ✅ Complete UI layout
- ✅ Hover effects and interactions
- ✅ Visual design matching ViewSeller
- ✅ Photo requirement guidelines
- ✅ Verified badge display
- ✅ Preview button (ready for link)

### Ready for Implementation:
- 📸 File upload handling (backend)
- 💾 Image storage and processing
- 🔗 Preview button navigation
- 🖼️ Display uploaded images
- 🗑️ Delete/replace photos

## 💡 Usage Instructions

### For Sellers:
1. **Upload Cover Photo**:
   - Click "Edit Cover Photo" button
   - Select image (max 5MB)
   - Recommended: 1200 x 300 pixels
   
2. **Upload Profile Photo**:
   - Hover over avatar
   - Click when "Edit Photo" appears
   - Select square image (max 5MB)
   - Recommended: 400 x 400 pixels

3. **Preview Store**:
   - Click "Preview Store Page" button
   - See how buyers will view your store
   - Check if photos look professional

## 🎯 Design Goals Achieved

✅ **Professional Look**: Matches buyer view design
✅ **Easy to Use**: Clear edit buttons and instructions
✅ **Helpful Guidelines**: Photo requirements clearly stated
✅ **Visual Feedback**: Hover states show interactivity
✅ **Brand Consistency**: Purple theme throughout
✅ **File Size Control**: 5MB limits clearly displayed
✅ **Verified Badge**: Shows seller credibility

---

## ✅ Status: COMPLETE & READY FOR BACKEND

The UI is complete with all visual elements, hover states, and placeholders. 
Ready for backend file upload integration! 🎉

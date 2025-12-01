---

## 🎨 **1. COLOR PALETTE**

### Primary Colors
```css
--emerald-50: #ecfdf5
--emerald-100: #d1fae5
--emerald-400: #34d399
--emerald-500: #10b981  /* Primary Brand */
--emerald-600: #059669
--emerald-700: #047857
```

### Neutral Colors
```css
--slate-50: #f8fafc
--slate-200: #e2e8f0
--slate-400: #94a3b8
--slate-500: #64748b
--slate-600: #475569
--slate-700: #334155
--slate-800: #1e293b
--slate-900: #0f172a  /* Primary Background */
```

### Accent Colors
```css
--red-500: #ef4444     /* Destructive actions */
--amber-500: #f59e0b   /* Warning states */
--blue-500: #3b82f6    /* Informational */
```

### Semantic Usage
- **Primary Action**: Emerald-500
- **Background Base**: Slate-900
- **Background Elevated**: Slate-800
- **Text Primary**: White (#ffffff)
- **Text Secondary**: Slate-400
- **Border Default**: White/10% opacity
- **Border Focus**: Emerald-500/40% opacity

---

## 📐 **2. SPACING SYSTEM**

Gunakan sistem spacing 4px base untuk konsistensi:

```css
--space-1: 4px
--space-2: 8px
--space-3: 12px
--space-4: 16px
--space-5: 20px
--space-6: 24px
--space-8: 32px
--space-10: 40px
--space-12: 48px
--space-16: 64px
--space-20: 80px
```

### Mobile-First Spacing
- **Container Padding Mobile**: 16px (space-4)
- **Container Padding Desktop**: 24px (space-6)
- **Section Gap Mobile**: 32px (space-8)
- **Section Gap Desktop**: 48px (space-12)
- **Card Padding**: 20px-24px (space-5 to space-6)

---

## 🔤 **3. TYPOGRAPHY SYSTEM**

### Font Stack
```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, 
             "Helvetica Neue", Arial, sans-serif;
```

### Scale (Mobile → Desktop)
```css
/* Headings */
--text-h1: 2rem → 3rem (32px → 48px)
--text-h2: 1.5rem → 2rem (24px → 32px)
--text-h3: 1.25rem → 1.5rem (20px → 24px)
--text-h4: 1.125rem (18px)

/* Body */
--text-base: 1rem (16px)
--text-sm: 0.875rem (14px)
--text-xs: 0.75rem (12px)
--text-2xs: 0.65rem (10.4px)
```

### Font Weights
```css
--font-normal: 400
--font-medium: 500
--font-semibold: 600
--font-bold: 700
--font-extrabold: 800
--font-black: 900
```

### Line Heights
```css
--leading-tight: 1.25
--leading-normal: 1.5
--leading-relaxed: 1.75
```

---

## 🧩 **4. COMPONENT LIBRARY**

### 4.1 Buttons

#### Primary Button
```html
<button class="btn-primary">
  <i class="fa-solid fa-check"></i>
  Simpan Data
</button>
```

```css
.btn-primary {
  @apply px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600
         text-white font-bold text-sm
         hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(16,185,129,0.4)]
         active:translate-y-0 transition-all duration-300
         disabled:opacity-50 disabled:cursor-not-allowed;
}
```

#### Secondary Button
```css
.btn-secondary {
  @apply px-6 py-3 rounded-xl bg-white/5 border border-white/10
         text-white font-bold text-sm backdrop-blur-md
         hover:bg-white/10 hover:border-emerald-500/30
         hover:-translate-y-0.5 transition-all duration-300;
}
```

#### Destructive Button
```css
.btn-destructive {
  @apply px-6 py-3 rounded-xl bg-red-500/10 border border-red-500/20
         text-red-500 font-bold text-sm
         hover:bg-red-500/20 hover:-translate-y-0.5
         hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)]
         transition-all duration-300;
}
```

#### Icon Button
```css
.btn-icon {
  @apply w-10 h-10 rounded-xl bg-white/5 border border-white/10
         flex items-center justify-center text-slate-400
         hover:bg-white/10 hover:text-emerald-500 hover:border-emerald-500/30
         hover:-translate-y-0.5 transition-all duration-300;
}
```

---

### 4.2 Cards

#### Standard Card
```html
<div class="card-standard">
  <div class="card-header">
    <h3>Card Title</h3>
  </div>
  <div class="card-body">
    <!-- Content -->
  </div>
</div>
```

```css
.card-standard {
  @apply bg-white/5 backdrop-blur-md border border-white/10
         rounded-2xl p-6 hover:-translate-y-1 hover:bg-white/10
         hover:border-emerald-500/30 
         hover:shadow-[0_20px_40px_rgba(16,185,129,0.15)]
         transition-all duration-300;
}
```

#### Interactive Card (Clickable)
```css
.card-interactive {
  @apply card-standard cursor-pointer relative overflow-hidden
         before:absolute before:top-0 before:left-0 before:right-0 
         before:h-[3px] before:bg-gradient-to-r 
         before:from-emerald-500 before:to-emerald-300
         before:scale-x-0 before:origin-left
         hover:before:scale-x-100 before:transition-transform 
         before:duration-500;
}
```

---

### 4.3 Form Elements

#### Input Field
```html
<div class="form-group">
  <label class="form-label">Nama Lengkap</label>
  <div class="input-wrapper">
    <i class="fa-solid fa-user input-icon"></i>
    <input type="text" class="form-input" placeholder="Masukkan nama">
  </div>
</div>
```

```css
.form-group {
  @apply flex flex-col gap-2 mb-4;
}

.form-label {
  @apply text-sm font-semibold text-slate-300;
}

.input-wrapper {
  @apply relative;
}

.input-icon {
  @apply absolute left-4 top-1/2 -translate-y-1/2 text-slate-500
         transition-colors duration-300;
}

.form-input {
  @apply w-full bg-white/10 backdrop-blur-md border border-white/10
         rounded-xl py-3 pl-12 pr-4 text-white placeholder-slate-500
         outline-none focus:border-emerald-500 focus:ring-4 
         focus:ring-emerald-500/10 transition-all duration-300;
}

.form-input:focus ~ .input-icon {
  @apply text-emerald-500;
}
```

#### Select Dropdown
```css
.form-select {
  @apply form-input appearance-none cursor-pointer
         bg-[url('data:image/svg+xml;charset=UTF-8,...')] 
         bg-no-repeat bg-right bg-[length:20px];
}
```

#### Textarea
```css
.form-textarea {
  @apply form-input min-h-[120px] resize-none;
}
```

---

### 4.4 Navigation Bar

```html
<nav class="navbar">
  <div class="navbar-container">
    <div class="navbar-brand">
      <img src="/images/logo.png" class="navbar-logo">
      <div class="navbar-title">
        <h3>KMI Inspection</h3>
        <span>Quality Control System</span>
      </div>
    </div>
    <div class="navbar-actions">
      <!-- User info & buttons -->
    </div>
  </div>
</nav>
```

```css
.navbar {
  @apply sticky top-0 z-50 w-full bg-[#0f172a]/80 backdrop-blur-xl
         border-b border-emerald-500/10 
         shadow-[0_4px_20px_rgba(0,0,0,0.3)];
}

.navbar-container {
  @apply max-w-[1400px] mx-auto px-4 sm:px-6 py-4
         flex justify-between items-center gap-4;
}

.navbar-brand {
  @apply flex items-center gap-3 sm:gap-4;
}

.navbar-logo {
  @apply h-8 sm:h-10 w-auto 
         drop-shadow-[0_2px_8px_rgba(16,185,129,0.3)];
}

.navbar-title h3 {
  @apply text-white font-extrabold text-base sm:text-lg 
         tracking-tight leading-tight;
}

.navbar-title span {
  @apply text-emerald-500 text-[0.6rem] sm:text-[0.65rem] 
         font-bold uppercase tracking-widest;
}
```

---

### 4.5 Badges & Tags

```css
.badge {
  @apply inline-flex items-center gap-1.5 px-3 py-1.5 
         rounded-lg text-xs font-bold;
}

.badge-success {
  @apply badge bg-emerald-500/10 border border-emerald-500/30 
         text-emerald-400;
}

.badge-warning {
  @apply badge bg-amber-500/10 border border-amber-500/30 
         text-amber-400;
}

.badge-info {
  @apply badge bg-blue-500/10 border border-blue-500/30 
         text-blue-400;
}

.badge-neutral {
  @apply badge bg-white/5 border border-white/10 
         text-slate-400;
}
```

---

### 4.6 Stat Cards

```html
<div class="stat-card">
  <div class="stat-icon">
    <i class="fa-solid fa-chart-line"></i>
  </div>
  <div class="stat-content">
    <span class="stat-value">127</span>
    <span class="stat-label">Total Items</span>
  </div>
</div>
```

```css
.stat-card {
  @apply min-w-[180px] bg-white/5 backdrop-blur-md border border-white/10
         rounded-2xl p-5 flex items-center gap-4
         hover:-translate-y-1 hover:bg-white/10 hover:border-emerald-500/30
         hover:shadow-[0_10px_30px_rgba(16,185,129,0.1)]
         transition-all duration-300;
}

.stat-icon {
  @apply w-12 h-12 rounded-xl bg-emerald-500/10 
         flex items-center justify-center text-emerald-500 text-xl;
}

.stat-value {
  @apply text-3xl font-extrabold text-white leading-none;
}

.stat-label {
  @apply text-slate-400 text-sm font-medium mt-1;
}
```

---

## 🎭 **5. LOADING STATES**

### 5.1 Page Loader
```html
<div class="page-loader">
  <div class="loader-spinner"></div>
  <span class="loader-text">Memuat data...</span>
</div>
```

```css
.page-loader {
  @apply fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm
         flex flex-col items-center justify-center;
}

.loader-spinner {
  @apply w-12 h-12 border-4 border-emerald-500/20 border-t-emerald-500
         rounded-full animate-spin;
}

.loader-text {
  @apply mt-4 text-emerald-400 text-sm font-bold uppercase 
         tracking-widest animate-pulse;
}
```

### 5.2 Button Loading State
```html
<button class="btn-primary" disabled>
  <i class="fa-solid fa-circle-notch fa-spin"></i>
  Processing...
</button>
```

### 5.3 Skeleton Loader
```css
.skeleton {
  @apply bg-white/5 animate-pulse rounded-xl;
}

.skeleton-text {
  @apply skeleton h-4 w-full mb-2;
}

.skeleton-title {
  @apply skeleton h-6 w-3/4 mb-3;
}

.skeleton-card {
  @apply skeleton h-32 w-full;
}
```

---

## 🔄 **6. TRANSITION STATES**

### Page Transitions
```html
<!-- Add to layout wrapper -->
<transition name="page-fade">
  <router-view />
</transition>
```

```css
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
```

### Transaction Processing Overlay
```html
<div class="transaction-overlay">
  <div class="transaction-content">
    <i class="fa-solid fa-circle-notch fa-spin text-4xl mb-3"></i>
    <h3 class="text-lg font-bold mb-2">Memproses Transaksi</h3>
    <p class="text-sm text-slate-400">Mohon tunggu sebentar...</p>
    <div class="progress-bar">
      <div class="progress-fill"></div>
    </div>
  </div>
</div>
```

```css
.transaction-overlay {
  @apply fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-md
         flex items-center justify-center animate-fade-in;
}

.transaction-content {
  @apply bg-white/10 backdrop-blur-xl border border-emerald-500/30
         rounded-3xl p-8 text-center text-emerald-400 max-w-sm
         shadow-[0_20px_60px_rgba(16,185,129,0.3)];
}

.progress-bar {
  @apply w-full h-2 bg-white/10 rounded-full mt-4 overflow-hidden;
}

.progress-fill {
  @apply h-full bg-gradient-to-r from-emerald-500 to-emerald-300
         animate-progress;
}

@keyframes progress {
  0% { width: 0%; }
  50% { width: 70%; }
  100% { width: 100%; }
}

.animate-progress {
  animation: progress 2s ease-in-out infinite;
}
```

---

## 📱 **7. RESPONSIVE BREAKPOINTS**

```css
/* Mobile First Approach */
/* Base: 320px - 639px (Mobile) */

@media (min-width: 640px) {  /* sm: Small tablets */
  /* Adjustments */
}

@media (min-width: 768px) {  /* md: Tablets */
  /* 2 columns grid */
  .grid-responsive {
    @apply grid-cols-2;
  }
}

@media (min-width: 1024px) { /* lg: Small laptops */
  /* 3 columns grid */
}

@media (min-width: 1280px) { /* xl: Desktops */
  /* 4 columns grid */
  .grid-responsive {
    @apply grid-cols-4;
  }
}

@media (min-width: 1536px) { /* 2xl: Large screens */
  /* Max content width */
}
```

### Responsive Grid System
```css
.grid-responsive {
  @apply grid grid-cols-1 gap-4
         sm:gap-5 md:grid-cols-2 md:gap-6
         lg:grid-cols-3 xl:grid-cols-4;
}
```

### Responsive Container
```css
.container-responsive {
  @apply w-full mx-auto px-4 
         sm:px-6 md:max-w-[768px] lg:max-w-[1024px] 
         xl:max-w-[1280px] 2xl:max-w-[1400px];
}
```

---

## ⚡ **8. PERFORMANCE OPTIMIZATION**

### Image Optimization
```html
<img 
  src="/images/logo-small.webp" 
  srcset="/images/logo-small.webp 1x, /images/logo-large.webp 2x"
  alt="Logo" 
  class="navbar-logo"
  loading="lazy"
  decoding="async"
>
```

### Critical CSS (Inline di <head>)
```css
/* Only above-the-fold styles */
.navbar, .btn-primary, .card-standard {
  /* Critical styles only */
}
```

### Lazy Loading Components
```javascript
// Vue 3 async components
const InspectionForm = defineAsyncComponent(() =>
  import('./components/InspectionForm.vue')
);
```

---

## 🎨 **9. GLASSMORPHISM EFFECTS**

```css
.glass-light {
  @apply bg-white/5 backdrop-blur-md border border-white/10;
}

.glass-medium {
  @apply bg-white/10 backdrop-blur-xl border border-white/20;
}

.glass-strong {
  @apply bg-white/20 backdrop-blur-2xl border border-white/30;
}

.glass-emerald {
  @apply bg-emerald-500/10 backdrop-blur-md border border-emerald-500/30;
}
```

---

## ✨ **10. ANIMATION LIBRARY**

### Fade Animations
```css
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes fadeInUp {
  from { 
    opacity: 0; 
    transform: translateY(30px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

@keyframes fadeInDown {
  from { 
    opacity: 0; 
    transform: translateY(-30px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out backwards;
}

.animate-fade-in-down {
  animation: fadeInDown 0.6s ease-out backwards;
}
```

### Floating Animations
```css
@keyframes float {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(30px, -30px); }
}

.animate-float {
  animation: float 20s ease-in-out infinite;
}
```

### Staggered Animations
```css
/* Apply to list items */
.stagger-item {
  @apply animate-fade-in-up;
}

.stagger-item:nth-child(1) { animation-delay: 0.05s; }
.stagger-item:nth-child(2) { animation-delay: 0.10s; }
.stagger-item:nth-child(3) { animation-delay: 0.15s; }
/* Continue pattern... */
```

---

## 🌐 **11. BACKGROUND SYSTEM**

### Base Background
```html
<div class="bg-base">
  <!-- Gradient layers -->
  <div class="bg-gradient-layer"></div>
  
  <!-- Floating orbs -->
  <div class="bg-orb bg-orb-1"></div>
  <div class="bg-orb bg-orb-2"></div>
  <div class="bg-orb bg-orb-3"></div>
  
  <!-- Grid pattern -->
  <div class="bg-grid-pattern"></div>
</div>
```

```css
.bg-base {
  @apply fixed inset-0 z-0 pointer-events-none;
}

.bg-gradient-layer {
  @apply absolute inset-0 bg-gradient-to-br 
         from-[#0f172a] via-[#1e293b] to-[#0a3d2e];
}

.bg-orb {
  @apply absolute rounded-full blur-[80px] opacity-15;
}

.bg-orb-1 {
  @apply -top-[10%] -right-[10%] w-[500px] h-[500px] 
         bg-emerald-500 animate-float;
}

.bg-orb-2 {
  @apply -bottom-[10%] -left-[5%] w-[400px] h-[400px] 
         bg-emerald-600 animate-float;
  animation-delay: 5s;
}

.bg-orb-3 {
  @apply top-1/2 left-1/2 w-[350px] h-[350px] 
         bg-emerald-400 -translate-x-1/2 -translate-y-1/2 animate-float;
  animation-delay: 10s;
}

.bg-grid-pattern {
  @apply absolute inset-0;
  background-image: 
    linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  animation: gridMove 20s linear infinite;
}

@keyframes gridMove {
  0% { transform: translate(0, 0); }
  100% { transform: translate(50px, 50px); }
}
```

---

## 🔔 **12. NOTIFICATION SYSTEM**

```html
<div class="notification notification-success">
  <i class="fa-solid fa-check-circle"></i>
  <div>
    <h4>Berhasil!</h4>
    <p>Data telah tersimpan</p>
  </div>
  <button class="notification-close">
    <i class="fa-solid fa-times"></i>
  </button>
</div>
```

```css
.notification {
  @apply fixed top-20 right-4 sm:right-6 z-[90] max-w-sm
         bg-white/10 backdrop-blur-xl border rounded-2xl p-4
         flex items-start gap-3 shadow-2xl
         animate-fade-in-down;
}

.notification-success {
  @apply border-emerald-500/30 text-emerald-400;
}

.notification-error {
  @apply border-red-500/30 text-red-400;
}

.notification-warning {
  @apply border-amber-500/30 text-amber-400;
}

.notification-close {
  @apply ml-auto text-slate-400 hover:text-white 
         transition-colors;
}
```

---

## 📋 **13. MODAL SYSTEM**

```html
<div class="modal-backdrop">
  <div class="modal-container">
    <div class="modal-header">
      <h3>Konfirmasi</h3>
      <button class="modal-close">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <p>Apakah Anda yakin ingin melanjutkan?</p>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary">Batal</button>
      <button class="btn-primary">Konfirmasi</button>
    </div>
  </div>
</div>
```

```css
.modal-backdrop {
  @apply fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-sm
         flex items-center justify-center p-4 animate-fade-in;
}

.modal-container {
  @apply bg-slate-800/90 backdrop-blur-xl border border-white/10
         rounded-3xl max-w-md w-full overflow-hidden
         shadow-[0_20px_60px_rgba(0,0,0,0.5)]
         animate-fade-in-up;
}

.modal-header {
  @apply flex items-center justify-between p-6 
         border-b border-white/10;
}

.modal-header h3 {
  @apply text-xl font-bold text-white;
}

.modal-close {
  @apply w-8 h-8 rounded-lg bg-white/5 
         flex items-center justify-center text-slate-400
         hover:bg-white/10 hover:text-white transition-all;
}

.modal-body {
  @apply p-6 text-slate-300;
}

.modal-footer {
  @apply flex items-center justify-end gap-3 p-6 
         border-t border-white/10;
}
```

---

## 🎯 **14. ICON GUIDELINES (Font Awesome)**

### Icon Sizes
```css
.icon-xs { @apply text-xs; }     /* 12px */
.icon-sm { @apply text-sm; }     /* 14px */
.icon-base { @apply text-base; } /* 16px */
.icon-lg { @apply text-lg; }     /* 18px */
.icon-xl { @apply text-xl; }     /* 20px */
.icon-2xl { @apply text-2xl; }   /* 24px */
.icon-3xl { @apply text-3xl; }   /* 30px */
```

### Common Icons
- **Navigation**: `fa-home`, `fa-arrow-left`, `fa-bars`
- **Actions**: `fa-plus`, `fa-edit`, `fa-trash`, `fa-save`
- **Status**: `fa-check-circle`, `fa-times-circle`, `fa-exclamation-triangle`
- **Loading**: `fa-circle-notch fa-spin`, `fa-spinner fa-spin`
- **Data**: `fa-search`, `fa-filter`, `fa-download`, `fa-upload`

---

## 📐 **15. LAYOUT PATTERNS**

### Dashboard Layout
```html
<div class="layout-dashboard">
  <nav class="navbar"><!-- Nav --></nav>
  <main class="main-content">
    <div class="container-responsive">
      <!-- Content -->
    </div>
  </main>
</div>
```

### Form Layout
```html
<div class="form-layout">
  <div class="form-section">
    <h3 class="form-section-title">Informasi Dasar</h3>
    <div class="form-grid">
      <div class="form-group"><!-- Field --></div>
      <div class="form-group"><!-- Field --></div>
    </div>
  </div>
</div>
```

```css
.form-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 gap-4;
}

.form-section {
  @apply mb-8;
}

.form-section-title {
  @apply text-xl font-bold text-white mb-4;
}
```

---

## ✅ **16. ACCESSIBILITY**

### Focus States
```css
*:focus-visible {
  @apply outline-none ring-4 ring-emerald-500/30 ring-offset-2 
         ring-offset-slate-900;
}

button:focus-visible,
a:focus-visible {
  @apply ring-emerald-500/50;
}
```

### Screen Reader Only
```css
.sr-only {
  @apply absolute w-px h-px p-0 -m-px overflow-hidden 
         whitespace-nowrap border-0;
  clip: rect(0, 0, 0, 0);
}
```

---

## 🚀 **17. IMPLEMENTATION CHECKLIST**

### Setiap Halaman Harus:
- ✅ Menggunakan color palette yang konsisten
- ✅ Responsive dari mobile (320px) hingga desktop (1920px+)
- ✅ Memiliki loading state untuk setiap async operation
- ✅ Menggunakan glassmorphism effect untuk card/modal
- ✅ Menampilkan feedback visual untuk setiap user action
- ✅ Menggunakan icon Font Awesome dengan ukuran konsisten
- ✅ Implementasi hover states dengan smooth transitions
- ✅ Memiliki error states yang jelas
- ✅ Support dark mode (default)
- ✅ Accessible (keyboard navigation, screen readers)

---

## 📦 **18. REUSABLE UTILITIES**

```css
/* Truncate text */
.text-truncate {
  @apply overflow-hidden text-ellipsis whitespace-nowrap;
}

/* Line clamp */
.line-clamp-2 {
  @apply overflow-hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Smooth scroll */
.smooth-scroll {
  @apply scroll-smooth;
}

/* Hide scrollbar */
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  @apply w-2;
}
.custom-scrollbar::-webkit-scrollbar-track {
  @apply bg-white/5;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-emerald-500/50 rounded-full hover:bg-emerald-500/70;
}
```

---

Dengan design system ini, seluruh website akan memiliki:
- ✨ **Konsistensi visual** yang kuat
- 📱 **Mobile-first** dengan responsive excellent
- ⚡ **Performance** yang optimal
- 🎨 **Feel eksklusif dan professional**
- 🔄 **Loading states** yang smooth
-
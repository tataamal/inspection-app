# 🎨 Design System Implementation Guide
## Inspection App - KMI Quality Control System

Dokumen ini mengintegrasikan **Design System** dengan implementasi aktual di project inspection-app untuk memastikan konsistensi visual dan UX yang optimal.

---

## 📊 **STATUS IMPLEMENTASI**

### ✅ **Sudah Diimplementasikan**

#### 1. **Color Palette** ✅
- ✅ Emerald-500 sebagai primary brand color
- ✅ Slate-900 sebagai background base
- ✅ Glassmorphism dengan `bg-white/5 backdrop-blur-md`
- ✅ Border dengan opacity (`border-white/10`, `border-emerald-500/30`)

**Contoh Implementasi:**
```vue
<!-- Dashboard.vue -->
<div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl">
```

#### 2. **Background System** ✅
- ✅ Gradient background layers
- ✅ Floating orbs dengan blur effect
- ✅ Grid pattern animation
- ✅ Fixed positioning dengan z-index layering

**Contoh:**
```vue
<div class="fixed inset-0 z-0 pointer-events-none">
  <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
  <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
  <div class="absolute inset-0 grid-pattern"></div>
</div>
```

#### 3. **Navigation Bar** ✅
- ✅ Sticky positioning
- ✅ Backdrop blur effect
- ✅ Responsive dengan hidden/show pada mobile
- ✅ Logo dan branding konsisten

**Pattern:**
```vue
<nav class="sticky top-0 z-50 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-emerald-500/10">
```

#### 4. **Cards & Interactive Elements** ✅
- ✅ Glassmorphism cards
- ✅ Hover effects dengan translate dan shadow
- ✅ Loading states dengan overlay
- ✅ Staggered animations

**Pattern:**
```vue
<div class="group relative bg-white/5 backdrop-blur-md border border-white/10 rounded-[20px] p-6 cursor-pointer overflow-hidden hover:-translate-y-2 hover:bg-white/10 hover:border-emerald-500/40 hover:shadow-[0_20px_40px_rgba(16,185,129,0.2)] transition-all duration-300">
```

#### 5. **Form Elements** ✅
- ✅ Input dengan icon wrapper
- ✅ Focus states dengan emerald ring
- ✅ Error states dengan red accent
- ✅ Loading button states

**Pattern (Login.vue):**
```vue
<div class="group relative flex items-center bg-[#0f172a]/60 border border-white/10 rounded-[14px] transition-all duration-300 focus-within:border-emerald-500 focus-within:shadow-[0_0_0_4px_rgba(16,185,129,0.15)]">
```

#### 6. **Animations** ✅
- ✅ Fade in up animations
- ✅ Float animations untuk orbs
- ✅ Grid pattern movement
- ✅ Staggered delays untuk list items

---

## 🔄 **PENYESUAIAN YANG DIPERLUKAN**

### 1. **Typography System** ⚠️

**Status:** Sebagian konsisten, perlu standardisasi

**Masalah:**
- Font size menggunakan hardcoded values (`text-4xl`, `text-5xl`)
- Tidak ada sistem scale yang jelas untuk mobile → desktop

**Rekomendasi:**
```vue
<!-- Gunakan responsive typography -->
<h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white">
  <!-- Sesuai design system: 2rem → 3rem (32px → 48px) -->
</h1>

<h2 class="text-2xl md:text-3xl font-bold text-white">
  <!-- Sesuai design system: 1.5rem → 2rem (24px → 32px) -->
</h2>
```

### 2. **Spacing System** ⚠️

**Status:** Menggunakan Tailwind default, perlu konsistensi

**Rekomendasi:**
- Gunakan spacing scale yang konsisten:
  - Mobile: `px-4` (16px)
  - Desktop: `px-6` (24px)
  - Section gap: `gap-6 md:gap-8` (24px → 32px)

**Contoh Perbaikan:**
```vue
<!-- Sebelum -->
<div class="px-6 py-12">

<!-- Sesuai Design System -->
<div class="px-4 py-8 md:px-6 md:py-12">
```

### 3. **Button Components** ⚠️

**Status:** Inline styles, perlu komponen reusable

**Rekomendasi:** Buat component `Button.vue` dengan variants:

```vue
<!-- components/Button.vue -->
<template>
  <button 
    :class="buttonClasses"
    :disabled="disabled || processing"
    @click="$emit('click')"
  >
    <i v-if="icon && !processing" :class="icon"></i>
    <i v-if="processing" class="fa-solid fa-circle-notch fa-spin"></i>
    <span v-if="!processing">{{ label }}</span>
    <span v-else>{{ loadingText }}</span>
  </button>
</template>

<script setup>
const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary, secondary, destructive
  icon: String,
  label: String,
  loadingText: { type: String, default: 'Processing...' },
  processing: Boolean,
  disabled: Boolean
});

const buttonClasses = computed(() => {
  const base = 'px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300';
  
  if (props.variant === 'primary') {
    return `${base} bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(16,185,129,0.4)] disabled:opacity-50`;
  }
  
  if (props.variant === 'secondary') {
    return `${base} bg-white/5 border border-white/10 text-white backdrop-blur-md hover:bg-white/10 hover:border-emerald-500/30`;
  }
  
  if (props.variant === 'destructive') {
    return `${base} bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)]`;
  }
  
  return base;
});
</script>
```

### 4. **Card Components** ⚠️

**Status:** Inline classes, perlu komponen reusable

**Rekomendasi:** Buat `Card.vue` component:

```vue
<!-- components/Card.vue -->
<template>
  <div 
    :class="cardClasses"
    @click="clickable ? $emit('click') : null"
  >
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  variant: { type: String, default: 'standard' }, // standard, interactive, stat
  clickable: Boolean,
  hover: { type: Boolean, default: true }
});

const cardClasses = computed(() => {
  const base = 'bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6';
  const interactive = props.clickable ? 'cursor-pointer overflow-hidden' : '';
  const hoverEffect = props.hover && props.clickable 
    ? 'hover:-translate-y-1 hover:bg-white/10 hover:border-emerald-500/30 hover:shadow-[0_20px_40px_rgba(16,185,129,0.15)]' 
    : '';
  
  return `${base} ${interactive} ${hoverEffect} transition-all duration-300`;
});
</script>
```

### 5. **Loading States** ⚠️

**Status:** Implementasi berbeda-beda, perlu standardisasi

**Rekomendasi:** Buat `LoadingOverlay.vue`:

```vue
<!-- components/LoadingOverlay.vue -->
<template>
  <div 
    v-if="show"
    class="absolute inset-0 z-20 bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center text-emerald-400 transition-all"
  >
    <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-2"></i>
    <span class="text-xs font-bold uppercase tracking-widest animate-pulse">
      {{ message }}
    </span>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  message: { type: String, default: 'Loading...' }
});
</script>
```

### 6. **Modal System** ⚠️

**Status:** Menggunakan SweetAlert2, perlu modal component sesuai design system

**Rekomendasi:** Buat `Modal.vue` component:

```vue
<!-- components/Modal.vue -->
<template>
  <Teleport to="body">
    <Transition name="modal">
      <div 
        v-if="show"
        class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="close"
      >
        <div class="bg-slate-800/90 backdrop-blur-xl border border-white/10 rounded-3xl max-w-md w-full overflow-hidden animate-fade-in-up">
          <div class="flex items-center justify-between p-6 border-b border-white/10">
            <h3 class="text-xl font-bold text-white">{{ title }}</h3>
            <button 
              @click="close"
              class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 hover:bg-white/10 hover:text-white transition-all"
            >
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
          <div class="p-6 text-slate-300">
            <slot />
          </div>
          <div v-if="$slots.footer" class="flex items-center justify-end gap-3 p-6 border-t border-white/10">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  title: String
});

const emit = defineEmits(['update:show', 'close']);

const close = () => {
  emit('update:show', false);
  emit('close');
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
```

---

## 📐 **RESPONSIVE BREAKPOINTS - IMPLEMENTASI**

### Current Implementation ✅
Project sudah menggunakan Tailwind breakpoints dengan baik:

```vue
<!-- Dashboard.vue - Grid Responsive -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

<!-- Navigation - Responsive User Info -->
<div class="hidden md:flex items-center gap-3">

<!-- Typography - Responsive -->
<h1 class="text-4xl md:text-5xl font-extrabold">
```

### Rekomendasi Perbaikan:

1. **Container Max Width:**
```vue
<!-- Sesuai Design System -->
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
```

2. **Grid System Standard:**
```vue
<!-- Mobile First -->
<div class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2 md:gap-6 lg:grid-cols-3 xl:grid-cols-4">
```

---

## 🎯 **BEST PRACTICES - CHECKLIST**

### Untuk Setiap Komponen Baru:

- [ ] **Color:** Gunakan emerald-500 untuk primary, slate-900 untuk background
- [ ] **Glassmorphism:** `bg-white/5 backdrop-blur-md border border-white/10`
- [ ] **Hover States:** `hover:-translate-y-1 hover:shadow-[...] transition-all duration-300`
- [ ] **Loading:** Gunakan `fa-circle-notch fa-spin` dengan overlay
- [ ] **Responsive:** Mobile-first dengan breakpoints `sm:`, `md:`, `lg:`, `xl:`
- [ ] **Spacing:** Gunakan scale 4px (4, 8, 12, 16, 20, 24, 32, 48, 64)
- [ ] **Typography:** Responsive font sizes (mobile → desktop)
- [ ] **Animations:** Fade in up dengan staggered delays untuk lists
- [ ] **Focus States:** `focus:ring-4 focus:ring-emerald-500/10`
- [ ] **Accessibility:** Keyboard navigation, screen reader support

---

## 🔧 **UTILITY CLASSES - REKOMENDASI**

Tambahkan ke `app.css` atau buat file terpisah:

```css
/* Custom Utilities untuk Design System */

/* Glassmorphism Variants */
.glass-light {
  @apply bg-white/5 backdrop-blur-md border border-white/10;
}

.glass-medium {
  @apply bg-white/10 backdrop-blur-xl border border-white/20;
}

.glass-emerald {
  @apply bg-emerald-500/10 backdrop-blur-md border border-emerald-500/30;
}

/* Text Truncate */
.text-truncate {
  @apply overflow-hidden text-ellipsis whitespace-nowrap;
}

/* Line Clamp */
.line-clamp-2 {
  @apply overflow-hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Custom Scrollbar */
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

## 📦 **KOMPONEN YANG PERLU DIBUAT**

### Priority 1 (High):
1. ✅ `Button.vue` - Reusable button dengan variants
2. ✅ `Card.vue` - Standard card component
3. ✅ `LoadingOverlay.vue` - Loading state component
4. ✅ `Modal.vue` - Modal dialog component

### Priority 2 (Medium):
5. `Input.vue` - Form input dengan icon support
6. `Badge.vue` - Badge/tag component
7. `StatCard.vue` - Stat card component
8. `Notification.vue` - Toast notification

### Priority 3 (Low):
9. `Skeleton.vue` - Skeleton loader
10. `Tooltip.vue` - Tooltip component
11. `Dropdown.vue` - Dropdown menu

---

## 🎨 **CONSISTENCY AUDIT**

### Halaman yang Sudah Sesuai Design System:
- ✅ `Dashboard.vue` - 95% sesuai
- ✅ `Auth/Login.vue` - 90% sesuai
- ⚠️ `Inspection/List.vue` - 75% sesuai (perlu perbaikan spacing & typography)
- ❓ `Inspection/Form.vue` - Perlu audit

### Action Items:
1. **Standardisasi Typography** di semua halaman
2. **Extract Components** untuk Button, Card, Modal
3. **Konsistensi Spacing** menggunakan scale 4px
4. **Unified Loading States** di seluruh aplikasi
5. **Responsive Audit** untuk semua breakpoints

---

## 🚀 **IMPLEMENTASI PRIORITAS**

### Phase 1: Core Components (Week 1)
- [ ] Buat `Button.vue` component
- [ ] Buat `Card.vue` component
- [ ] Buat `LoadingOverlay.vue` component
- [ ] Update semua halaman untuk menggunakan komponen baru

### Phase 2: Form Components (Week 2)
- [ ] Buat `Input.vue` component
- [ ] Buat `Modal.vue` component
- [ ] Standardisasi form styling di semua halaman

### Phase 3: Polish & Optimization (Week 3)
- [ ] Audit responsive di semua breakpoints
- [ ] Optimasi animations & transitions
- [ ] Accessibility improvements
- [ ] Performance optimization

---

## 📝 **NOTES**

1. **Design System sebagai Single Source of Truth**
   - Semua styling harus mengacu ke `design-system.md`
   - Jika ada inkonsistensi, update design system terlebih dahulu

2. **Tailwind CSS v4**
   - Gunakan `@theme` directive untuk custom properties
   - Hindari hardcoded colors, gunakan CSS variables

3. **Component Reusability**
   - Jangan duplicate styling, extract ke komponen
   - Gunakan props untuk variants, bukan inline classes

4. **Performance**
   - Lazy load komponen yang tidak critical
   - Optimasi images dengan `loading="lazy"`
   - Minimize re-renders dengan computed properties

---

**Last Updated:** 2025-01-XX  
**Maintained By:** Development Team  
**Reference:** `design-system.md`


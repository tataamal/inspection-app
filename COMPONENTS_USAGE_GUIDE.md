# 📦 Components Usage Guide
## Reusable Components untuk Inspection App

Dokumen ini menjelaskan cara menggunakan komponen-komponen reusable yang telah dibuat sesuai dengan Design System.

---

## 🎯 **Button Component**

### Import
```vue
<script setup>
import Button from '@/components/Button.vue';
</script>
```

### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'primary' \| 'secondary' \| 'destructive' \| 'icon'` | `'primary'` | Variant button |
| `icon` | `String` | `undefined` | Font Awesome icon class |
| `label` | `String` | `undefined` | Text label button |
| `loadingText` | `String` | `'Processing...'` | Text saat loading |
| `processing` | `Boolean` | `false` | Loading state |
| `disabled` | `Boolean` | `false` | Disabled state |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Ukuran button |
| `fullWidth` | `Boolean` | `false` | Full width button |

### Events
- `@click` - Emitted saat button diklik

### Examples

#### Primary Button
```vue
<Button
    variant="primary"
    label="Simpan Data"
    icon="fa-solid fa-save"
    @click="handleSave"
/>
```

#### Secondary Button
```vue
<Button
    variant="secondary"
    label="Batal"
    @click="handleCancel"
/>
```

#### Destructive Button
```vue
<Button
    variant="destructive"
    label="Hapus"
    icon="fa-solid fa-trash"
    @click="handleDelete"
/>
```

#### Icon Button
```vue
<Button
    variant="icon"
    icon="fa-solid fa-edit"
    @click="handleEdit"
/>
```

#### Loading State
```vue
<Button
    variant="primary"
    label="Submit"
    :processing="isSubmitting"
    loading-text="Menyimpan..."
    @click="handleSubmit"
/>
```

#### Full Width
```vue
<Button
    variant="primary"
    label="Login"
    full-width
    @click="handleLogin"
/>
```

---

## 🃏 **Card Component**

### Import
```vue
<script setup>
import Card from '@/components/Card.vue';
</script>
```

### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'standard' \| 'interactive' \| 'stat'` | `'standard'` | Variant card |
| `clickable` | `Boolean` | `false` | Apakah card bisa diklik |
| `hover` | `Boolean` | `true` | Enable hover effects |
| `padding` | `'sm' \| 'md' \| 'lg'` | `'md'` | Padding size |

### Events
- `@click` - Emitted saat card diklik (jika `clickable` true)

### Examples

#### Standard Card
```vue
<Card>
    <h3 class="text-white text-xl font-bold mb-2">Card Title</h3>
    <p class="text-slate-400">Card content goes here...</p>
</Card>
```

#### Interactive Card (Clickable)
```vue
<Card
    variant="interactive"
    clickable
    @click="handleCardClick"
>
    <div class="flex justify-between items-center mb-5">
        <h3 class="text-white text-2xl font-extrabold">{{ mrp.code }}</h3>
        <span class="badge-success">Active</span>
    </div>
    <p class="text-slate-400 text-sm">{{ mrp.description }}</p>
</Card>
```

#### Stat Card
```vue
<Card variant="stat">
    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-xl">
        <i class="fa-solid fa-chart-line"></i>
    </div>
    <div class="flex flex-col">
        <span class="text-3xl font-extrabold text-white leading-none">127</span>
        <span class="text-slate-400 text-sm font-medium mt-1">Total Items</span>
    </div>
</Card>
```

#### Custom Padding
```vue
<Card padding="lg">
    <h3 class="text-white text-xl font-bold mb-4">Large Padding Card</h3>
    <p class="text-slate-400">More space for content...</p>
</Card>
```

---

## ⏳ **LoadingOverlay Component**

### Import
```vue
<script setup>
import LoadingOverlay from '@/components/LoadingOverlay.vue';
</script>
```

### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `show` | `Boolean` | `false` | Tampilkan overlay |
| `message` | `String` | `'Loading...'` | Loading message |
| `fullScreen` | `Boolean` | `false` | Full screen overlay |

### Examples

#### Overlay dalam Card
```vue
<Card class="relative">
    <LoadingOverlay
        :show="isLoading"
        message="Memuat data..."
    />
    <div v-if="!isLoading">
        <!-- Content -->
    </div>
</Card>
```

#### Full Screen Overlay
```vue
<LoadingOverlay
    :show="isProcessing"
    message="Memproses transaksi..."
    full-screen
/>
```

#### Dalam List Item
```vue
<div
    v-for="item in items"
    :key="item.id"
    class="relative"
>
    <LoadingOverlay
        :show="processingItem === item.id"
        message="Syncing..."
    />
    <!-- Item content -->
</div>
```

---

## 🪟 **Modal Component**

### Import
```vue
<script setup>
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';
</script>
```

### Props
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `show` | `Boolean` | `false` | Tampilkan modal |
| `title` | `String` | `''` | Modal title |
| `closeOnBackdrop` | `Boolean` | `true` | Close saat klik backdrop |
| `size` | `'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Ukuran modal |

### Events
- `@update:show` - Emitted saat show state berubah
- `@close` - Emitted saat modal ditutup

### Slots
- `default` - Modal body content
- `header` - Custom header (optional)
- `footer` - Footer dengan buttons (optional)

### Examples

#### Basic Modal
```vue
<script setup>
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';
import Button from '@/components/Button.vue';

const showModal = ref(false);
</script>

<template>
    <Button
        variant="primary"
        label="Open Modal"
        @click="showModal = true"
    />
    
    <Modal
        v-model:show="showModal"
        title="Konfirmasi"
        @close="showModal = false"
    >
        <p>Apakah Anda yakin ingin melanjutkan?</p>
        
        <template #footer>
            <Button
                variant="secondary"
                label="Batal"
                @click="showModal = false"
            />
            <Button
                variant="primary"
                label="Konfirmasi"
                @click="handleConfirm"
            />
        </template>
    </Modal>
</template>
```

#### Modal dengan Custom Header
```vue
<Modal
    v-model:show="showModal"
    @close="showModal = false"
>
    <template #header>
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-exclamation-triangle text-amber-500"></i>
            <h3 class="text-xl font-bold text-white">Warning</h3>
        </div>
    </template>
    
    <p>This action cannot be undone.</p>
</Modal>
```

#### Large Modal
```vue
<Modal
    v-model:show="showModal"
    title="Detail Inspection"
    size="lg"
    @close="showModal = false"
>
    <!-- Large content -->
</Modal>
```

#### Modal tanpa Close on Backdrop
```vue
<Modal
    v-model:show="showModal"
    title="Important"
    :close-on-backdrop="false"
    @close="showModal = false"
>
    <p>You must complete this action.</p>
</Modal>
```

---

## 🔄 **Migration Guide**

### Migrating dari Inline Styles ke Components

#### Before (Dashboard.vue)
```vue
<button 
    @click="logout" 
    class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] transition-all duration-300 flex items-center justify-center"
>
    <i class="fa-solid fa-right-from-bracket"></i>
</button>
```

#### After
```vue
<Button
    variant="icon"
    icon="fa-solid fa-right-from-bracket"
    @click="logout"
/>
```

#### Before (Card)
```vue
<div 
    class="group relative bg-white/5 backdrop-blur-md border border-white/10 rounded-[20px] p-6 cursor-pointer overflow-hidden hover:-translate-y-2 hover:bg-white/10 hover:border-emerald-500/40 hover:shadow-[0_20px_40px_rgba(16,185,129,0.2)] transition-all duration-300"
    @click="handleClick"
>
    <!-- Content -->
</div>
```

#### After
```vue
<Card
    variant="interactive"
    clickable
    @click="handleClick"
>
    <!-- Content -->
</Card>
```

---

## ✅ **Best Practices**

1. **Konsistensi Variants**
   - Gunakan `variant="primary"` untuk primary actions
   - Gunakan `variant="secondary"` untuk secondary actions
   - Gunakan `variant="destructive"` untuk delete/danger actions

2. **Loading States**
   - Selalu gunakan `processing` prop untuk button loading
   - Gunakan `LoadingOverlay` untuk async operations

3. **Modal Usage**
   - Gunakan `v-model:show` untuk two-way binding
   - Selalu sediakan footer dengan action buttons
   - Gunakan `size` prop untuk konten yang berbeda

4. **Card Variants**
   - `standard` - Untuk static content
   - `interactive` - Untuk clickable cards
   - `stat` - Untuk statistik/metrics cards

5. **Accessibility**
   - Selalu sediakan `label` untuk buttons
   - Gunakan `aria-label` untuk icon-only buttons
   - Modal otomatis handle keyboard (ESC) dan focus trap

---

## 🐛 **Troubleshooting**

### Modal tidak muncul
- Pastikan menggunakan `Teleport` (sudah di-handle di component)
- Check z-index conflicts
- Pastikan `show` prop adalah reactive

### Button tidak trigger click
- Pastikan tidak `disabled` atau `processing`
- Check event handler di parent component

### LoadingOverlay tidak muncul
- Pastikan parent element memiliki `position: relative`
- Check `show` prop value

---

**Last Updated:** 2025-01-XX  
**Maintained By:** Development Team


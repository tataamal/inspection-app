<script setup>
import { ref, computed, reactive, nextTick, watch, onBeforeUnmount, onMounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3'; 
import flatpickr from "flatpickr";
import "flatpickr/dist/themes/dark.css"; 
import debounce from 'lodash/debounce'; 

const props = defineProps({
    authUser: Object, 
    mrpList: Array,
    historyList: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} })
    },
    // POINT 1 & 3: Menambahkan prop untuk menerima Total Quantity dari Backend
    totalQty: {
        type: Number,
        default: 0
    },
    filters: Object 
});

const searchQuery = ref('');
const processingMrp = ref(null);
const viewMode = ref(props.authUser?.role === 'admin' ? 'history' : 'mrp'); 
const datePickerRef = ref(null); 
let fpInstance = null; 

// --- REACTIVE FORM (SERVER-SIDE FILTER) ---
const form = reactive({
    startDate: props.filters?.startDate || '',
    endDate: props.filters?.endDate || '',
    status: props.filters?.status || '', 
    section: props.filters?.section || '',
    search: props.filters?.search || ''
});

const isAdmin = computed(() => {
    return props.authUser?.role === 'admin';
});

// CHECK DATE > 25
const isAfter25th = computed(() => {
    const d = new Date();
    return d.getDate() > 25;
});

const goToInspectionOperation = () => {
    router.visit('/inspection-operation');
};

// --- LOGIC MRP (USER BIASA) ---
const filteredMrp = computed(() => {
    if (!searchQuery.value) return props.mrpList;
    const lowerSearch = searchQuery.value.toLowerCase();
    return props.mrpList.filter(mrp => 
        mrp.code.toLowerCase().includes(lowerSearch) || 
        (mrp.name && mrp.name.toLowerCase().includes(lowerSearch))
    );
});

const toggleViewMode = (mode) => {
    viewMode.value = mode;
    if (mode === 'history' && !isAdmin.value) {
        nextTick(() => initFlatpickr());
    }
};

const goToInspectionList = (mrpItem) => {
    processingMrp.value = mrpItem.code;
    router.get(`/inspection/${mrpItem.code}`, { plant: mrpItem.plant }, {
        onFinish: () => { processingMrp.value = null; }
    });
};

// --- WATCHER & FILTERING ---
const applyFilter = debounce(() => {
    if (isAdmin.value || viewMode.value === 'history') {
        router.get('/dashboard', form, {
            preserveState: true, 
            preserveScroll: true, 
            replace: true 
        });
    }
}, 500); 

watch(form, () => {
    applyFilter();
}, { deep: true });

// --- DATE HELPER ---
const formatDateISO = (d) => {
    if (!d) return '';
    const offset = d.getTimezoneOffset();
    const date = new Date(d.getTime() - (offset * 60 * 1000));
    return date.toISOString().split('T')[0];
};

const setDateRange = (type) => {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    if (type === 'today') {
        // Start = End = Today
    } else if (type === '7days') {
        start.setDate(today.getDate() - 7);
    } else if (type === '30days') {
        start.setDate(today.getDate() - 30);
    } else if (type === 'week') {
        const day = today.getDay() || 7; 
        if (day !== 1) start.setHours(-24 * (day - 1));
        else start = today;
    } else if (type === 'yesterday') {
        start.setDate(today.getDate() - 1);
        end.setDate(today.getDate() - 1);
    }
    
    form.startDate = formatDateISO(start);
    form.endDate = formatDateISO(end);

    if (fpInstance) {
        fpInstance.setDate([start, end], true);
    }
};

const clearFilters = () => {
    form.startDate = '';
    form.endDate = '';
    form.status = '';
    form.section = '';
    form.search = '';
    if (fpInstance) fpInstance.clear();
};

// --- FLATPICKR INIT ---
const initFlatpickr = () => {
    if (fpInstance) fpInstance.destroy(); 
    if (datePickerRef.value) {
        fpInstance = flatpickr(datePickerRef.value, {
            mode: "range",
            dateFormat: "Y-m-d",
            theme: "dark",
            defaultDate: [form.startDate, form.endDate],
            onChange: (selectedDates) => {
                if (selectedDates.length > 0) form.startDate = formatDateISO(selectedDates[0]);
                if (selectedDates.length > 1) form.endDate = formatDateISO(selectedDates[1]);
                if (selectedDates.length === 0) {
                    form.startDate = '';
                    form.endDate = '';
                }
            }
        });
    }
};

onMounted(() => {
    if (isAdmin.value) {
        nextTick(() => initFlatpickr());
    }
});

onBeforeUnmount(() => {
    if (fpInstance) fpInstance.destroy();
});

// --- UI HELPERS ---
const printHistory = () => {
    const params = new URLSearchParams();
    for (const key in form) {
        if (form[key]) params.append(key, form[key]);
    }
    window.open(`/inspection/history/export?${params.toString()}`, '_blank');
};

const logout = () => {
    router.post('/logout');
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const removeLeadingZeros = (str) => {
    if (!str) return '';
    if (/^\d+$/.test(str)) return str.replace(/^0+/, '') || '0';
    return str;
};

const truncateText = (text, length) => {
    if (!text) return '-';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

// Helper format angka ribuan (e.g. 1.200,50)
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};

const getStatusLabel = (status) => {
    if (status === 'ERROR') return 'GAGAL UD';
    return status;
}

const getStatusColor = (status) => {
    if (status === 'SUCCESS') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    if (status === 'ERROR') return 'bg-red-500/10 text-red-400 border-red-500/20';
    return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
};
</script>

<template>
    <Head title="Dashboard Area Kerja" />

    <div class="relative min-h-screen bg-slate-900 font-sans text-slate-200 overflow-x-hidden selection:bg-emerald-500 selection:text-white pb-20">
        
        <!-- Background Effects -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 w-full bg-[#0f172a]/90 backdrop-blur-xl border-b border-emerald-500/10 shadow-lg">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
                 <div class="flex items-center gap-4">
                    <img src="/images/KMI.png" alt="KMI Logo" class="h-8 md:h-10 w-auto" />
                    <div class="flex flex-col">
                        <h3 class="text-white font-extrabold text-lg leading-tight">KMI Inspection</h3>
                        <span class="text-emerald-500 text-[0.65rem] font-bold uppercase tracking-widest">System</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg text-xs">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-white font-bold text-sm">{{ authUser.username }}</span>
                            <span class="text-slate-400 text-[0.65rem] font-medium">NIK: {{ authUser.nik }}</span>
                        </div>
                    </div>
                    <button @click="logout" class="w-9 h-9 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center hover:bg-red-500/20 active:bg-red-500/30 active:scale-95 transition-all duration-150" title="Keluar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </nav>

        <main class="relative z-10 max-w-[1600px] mx-auto px-4 sm:px-6 py-8">
            
            <!-- ========================================== -->
            <!-- TAMPILAN ADMIN (UTAMA YANG DIMODIFIKASI)   -->
            <!-- ========================================== -->
            <div v-if="isAdmin" class="animate-fade-in-up flex flex-col gap-6">
                
                <!-- HEADER ADMIN -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-white/5 pb-4">
                    <div class="w-full md:w-auto">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-xs font-bold mb-2">
                            <i class="fa-solid fa-shield-halved"></i> ADMIN PANEL
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                            Monitoring Inspeksi
                        </h2>
                    </div>

                    <!-- TOTAL DATA & TOTAL QTY QM (Responsive) -->
                    <div class="flex flex-col md:flex-row items-end md:items-center gap-4 w-full md:w-auto mt-2 md:mt-0">
                        <div class="flex items-center gap-4 bg-[#1e293b]/50 p-2 px-4 rounded-xl border border-white/10 w-full md:w-auto justify-between md:justify-end">
                            <div class="text-right">
                                <span class="text-slate-400 text-[0.65rem] uppercase font-bold block">Total Data</span>
                                <span class="text-xl md:text-2xl font-bold text-white">{{ formatNumber(props.historyList.total || 0) }}</span>
                            </div>
                            
                            <div class="w-px h-8 bg-white/10"></div>

                            <div class="text-right">
                                <span class="text-emerald-400 text-[0.65rem] uppercase font-bold block">Total Qty (SUCCESS)</span>
                                <span class="text-xl md:text-2xl font-bold text-white font-mono">{{ formatNumber(props.totalQty || 0) }}</span>
                            </div>
                        </div>

                        <button @click="printHistory" class="w-full md:w-auto px-4 py-3 md:py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-sm font-bold shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-file-export"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- FILTERS ADMIN -->
                <div class="bg-[#1e293b]/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 md:p-6 shadow-xl">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        
                        <!-- 1. Date Filter -->
                        <div class="md:col-span-5 flex flex-col gap-2">
                            <div class="flex justify-between items-center">
                                <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Periode Tanggal</label>
                                <div class="flex gap-1">
                                    <button @click="setDateRange('today')" class="px-2 py-0.5 rounded text-[0.6rem] border border-white/10 bg-white/5 hover:bg-emerald-500/20 hover:text-emerald-400 transition-colors">Hari Ini</button>
                                    <button @click="setDateRange('yesterday')" class="px-2 py-0.5 rounded text-[0.6rem] border border-white/10 bg-white/5 hover:bg-emerald-500/20 hover:text-emerald-400 transition-colors">Kemarin</button>
                                    <button @click="setDateRange('7days')" class="px-2 py-0.5 rounded text-[0.6rem] border border-white/10 bg-white/5 hover:bg-emerald-500/20 hover:text-emerald-400 transition-colors">7 Hari</button>
                                </div>
                            </div>
                            <div class="relative group/date">
                                <input ref="datePickerRef" type="text" placeholder="Pilih Rentang Tanggal..." 
                                    class="w-full bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2.5 pl-10 pr-3 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all cursor-pointer"
                                >
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within/date:text-emerald-400"></i>
                            </div>
                        </div>

                        <!-- 2. Status & Section Filter -->
                        <div class="md:col-span-3 flex flex-col gap-2">
                            <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Kategori</label>
                            <div class="flex gap-2 h-full">
                                <select v-model="form.status" class="w-1/2 bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 px-3 focus:border-emerald-500/50 cursor-pointer">
                                    <option value="">Semua Status</option>
                                    <option value="SUCCESS">Success</option>
                                    <option value="ERROR">Gagal UD</option>
                                </select>
                                <select v-model="form.section" class="w-1/2 bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 px-3 focus:border-emerald-500/50 cursor-pointer">
                                    <option value="">Semua Bagian</option>
                                    <option value="Packing">Packing</option>
                                    <option value="Painting">Painting</option>
                                </select>
                            </div>
                        </div>

                        <!-- 3. Search & Reset -->
                        <div class="md:col-span-4 flex flex-col gap-2">
                            <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Pencarian</label>
                            <div class="flex gap-2">
                                <div class="relative w-full group/search">
                                    <input type="text" v-model="form.search" placeholder="Cari Lot, Material, SO..." 
                                        class="w-full bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2.5 pl-10 pr-3 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all"
                                    >
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within/search:text-indigo-400"></i>
                                </div>
                                <button @click="clearFilters" class="px-4 rounded-lg border border-white/10 bg-white/5 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/30 transition-all" title="Reset Filter">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- TABLE ADMIN -->
                <div class="bg-[#1e293b]/40 backdrop-blur-sm border border-white/5 rounded-2xl overflow-hidden shadow-2xl flex flex-col min-h-[500px]">
                    <div class="flex-grow overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-[#0f172a] shadow-sm sticky top-0 z-10">
                                <tr class="text-xs uppercase tracking-wider text-slate-400 font-bold border-b border-white/10">
                                    <th class="p-4 w-[140px]">Waktu</th>
                                    <!-- MODIFIKASI: Prueflos dihapus, Kolom Order/Batch -->
                                    <th class="p-4 hidden md:table-cell">Order / Batch</th>
                                    <th class="p-4">SO / Item</th>
                                    <th class="p-4 min-w-[200px]">Material</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4 text-right">Qty</th>
                                    <th class="p-4 text-center w-[120px]">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-for="item in props.historyList.data" :key="item.id" class="hover:bg-white/5 transition-colors group">
                                    <!-- Waktu -->
                                    <td class="p-4 align-top">
                                        <div class="text-white font-mono text-xs">{{ formatDate(item.created_at).split(' ')[0] }}</div>
                                        <div class="text-slate-500 text-[0.65rem] font-mono mt-0.5">{{ formatDate(item.created_at).split(' ')[1] }}</div>
                                    </td>

                                    <!-- MODIFIKASI: Order Number Utama, Batch Kecil -->
                                    <td class="p-4 align-top hidden md:table-cell">
                                        <div class="text-white font-bold text-sm font-mono tracking-tight">{{ item.order_number || '-' }}</div>
                                        <div v-if="item.batch" class="text-emerald-400 text-xs mt-1 font-mono bg-emerald-500/10 px-1.5 py-0.5 rounded inline-block">
                                            Batch: {{ item.batch }}
                                        </div>
                                    </td>

                                    <!-- SO & Item -->
                                    <td class="p-4 align-top">
                                        <div class="text-indigo-300 font-mono text-sm font-bold">{{ item.sales_order || '-' }}</div>
                                        <div class="text-slate-500 text-xs mt-0.5">Pos: {{ item.sales_item || '-' }}</div>
                                    </td>

                                    <!-- Material -->
                                    <td class="p-4 align-top">
                                        <div class="text-slate-200 font-medium text-sm leading-relaxed" :title="item.material_desc">
                                            {{ truncateText(item.material_desc, 45) }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono mt-1 flex items-center gap-2">
                                            <i class="fa-solid fa-cube text-[0.6rem]"></i>
                                            {{ removeLeadingZeros(item.material_code) }}
                                        </div>
                                    </td>

                                    <!-- Customer/Buyer -->
                                    <td class="p-4 align-top max-w-[180px]">
                                        <div class="truncate text-slate-300 text-sm" :title="item.buyer_name">{{ item.buyer_name || '-' }}</div>
                                        <div class="text-xs text-slate-500 font-mono truncate mt-0.5">{{ item.customer_po || '-' }}</div>
                                    </td>

                                    <!-- Qty -->
                                    <td class="p-4 align-top text-right font-mono text-slate-300 text-sm">
                                        {{ parseFloat(item.quantity) }} <span class="text-xs text-slate-500">{{ item.uom === 'ST' ? 'PC' : item.uom }}</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="p-4 align-top text-center">
                                        <span class="inline-block px-2 py-0.5 rounded text-[0.65rem] font-bold border" :class="getStatusColor(item.status)">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                        <div v-if="item.status === 'ERROR' && item.sap_message" class="relative group/tooltip mt-2 flex justify-center">
                                            <i class="fa-solid fa-triangle-exclamation text-red-500 hover:text-red-400 cursor-pointer animate-pulse"></i>
                                            <div class="hidden group-hover/tooltip:block absolute right-full top-0 w-64 bg-slate-900 border border-red-500/50 p-2 rounded shadow-2xl z-50 text-[0.65rem] text-red-200 text-left">
                                                <div class="font-bold border-b border-red-500/30 mb-1 pb-1">Error SAP:</div>
                                                {{ item.sap_message }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.historyList.data.length === 0">
                                    <td colspan="7" class="p-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fa-solid fa-folder-open text-4xl opacity-30 mb-2"></i>
                                            <p class="text-sm">Tidak ada data ditemukan untuk filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- FOOTER PAGINATION ADMIN -->
                    <div class="bg-[#0f172a] p-3 border-t border-white/5 flex flex-col md:flex-row justify-between items-center px-4 gap-3">
                        <span class="text-[0.65rem] text-slate-500 w-full md:w-auto text-center md:text-left">
                            Menampilkan data {{ props.historyList.from || 0 }} - {{ props.historyList.to || 0 }} dari total {{ props.historyList.total }}
                        </span>
                        
                        <!-- Paginasi Scrollable di HP -->
                        <div class="w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                            <div class="flex items-center gap-1 min-w-max justify-center md:justify-end">
                                <template v-for="(link, k) in props.historyList.links" :key="k">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-1.5 text-xs rounded border transition-all duration-150 min-w-[32px] text-center"
                                        :class="{
                                            'bg-emerald-600 border-emerald-600 text-white font-bold': link.active,
                                            'bg-white/5 border-white/10 text-slate-400 hover:bg-white/10 hover:text-white': !link.active
                                        }"
                                        preserve-scroll
                                        preserve-state
                                    />
                                    <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-slate-600 opacity-50"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAMPILAN USER BIASA (TIDAK BERUBAH)        -->
            <!-- ========================================== -->
            <div v-else>
                <!-- SUMMARY CARDS -->
                <div class="text-center mb-10 animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold backdrop-blur-md mb-4">
                        <i class="fa-solid" :class="viewMode === 'mrp' ? 'fa-briefcase' : 'fa-clock-rotate-left'"></i>
                        {{ viewMode === 'mrp' ? 'Area Kerja' : 'Riwayat Inspeksi Saya' }}
                    </div>
                    
                    <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-3 tracking-tight">
                        {{ viewMode === 'mrp' ? 'Manufacturing Resource Planning' : 'Log Aktivitas Inspeksi' }}
                    </h1>
                    
                    <div class="flex flex-wrap justify-center gap-5 mt-8">
                        <div @click="toggleViewMode('mrp')" 
                             class="group min-w-[220px] bg-white/5 backdrop-blur-md border rounded-2xl p-5 flex items-center gap-4 cursor-pointer transition-all duration-300 active:scale-95"
                             :class="viewMode === 'mrp' ? 'border-emerald-500/50 bg-white/10 shadow-[0_0_20px_rgba(16,185,129,0.2)]' : 'border-white/10 hover:bg-white/10'">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-colors"
                                 :class="viewMode === 'mrp' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-400/10 text-slate-400 group-hover:text-emerald-400'">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-3xl font-extrabold text-white leading-none">{{ props.mrpList.length }}</span>
                                <span class="text-sm font-medium mt-1" :class="viewMode === 'mrp' ? 'text-emerald-400' : 'text-slate-400'">Area MRP</span>
                            </div>
                        </div>
                        
                        <div @click="toggleViewMode('history')"
                             class="group min-w-[220px] bg-white/5 backdrop-blur-md border rounded-2xl p-5 flex items-center gap-4 cursor-pointer transition-all duration-300 active:scale-95"
                             :class="viewMode === 'history' ? 'border-indigo-500/50 bg-white/10 shadow-[0_0_20px_rgba(99,102,241,0.2)]' : 'border-white/10 hover:bg-white/10'">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-colors"
                                 :class="viewMode === 'history' ? 'bg-indigo-500/20 text-indigo-400' : 'bg-slate-400/10 text-slate-400 group-hover:text-indigo-400'">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-3xl font-extrabold text-white leading-none">{{ props.historyList.total || 0 }}</span>
                                <span class="text-sm font-medium mt-1" :class="viewMode === 'history' ? 'text-indigo-400' : 'text-slate-400'">Riwayat Saya</span>
                            </div>
                        </div>

                        <!-- NEW CARD: INSPECTION OPERATION (ONLY AFTER 25th) -->
                        <div v-if="isAfter25th" @click="goToInspectionOperation"
                             class="group min-w-[220px] bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 flex items-center gap-4 cursor-pointer transition-all duration-300 active:scale-95 hover:bg-white/10">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-colors bg-sky-500/20 text-sky-400 group-hover:text-sky-300">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-xl font-extrabold text-white leading-none">Inspeksi</span>
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-400 mt-1">Operation</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VIEW 1: MRP LIST -->
                <div v-if="viewMode === 'mrp'" class="animate-fade-in-up">
                    <div class="relative max-w-xl mx-auto mb-10 group">
                        <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-emerald-500 transition-colors z-10"></i>
                        <input type="text" v-model="searchQuery" placeholder="Cari kode atau nama MRP..."
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-white placeholder-slate-500 outline-none focus:border-emerald-500/50 focus:bg-black/20 focus:ring-1 focus:ring-emerald-500/50 transition-all">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        <div v-for="(mrp, index) in filteredMrp" :key="mrp.id" @click="goToInspectionList(mrp)"
                             class="group relative bg-white/5 backdrop-blur-md border border-white/10 rounded-[20px] p-6 cursor-pointer overflow-hidden hover:-translate-y-2 hover:border-emerald-500/40 hover:shadow-2xl transition-all duration-300"
                             :class="{ 'pointer-events-none opacity-80': processingMrp === mrp.code }">
                            
                            <div v-if="processingMrp === mrp.code" class="absolute inset-0 z-20 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center text-emerald-400">
                                <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-2"></i>
                                <span class="text-xs font-bold uppercase animate-pulse">Syncing SAP...</span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-5">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-center justify-center text-white text-2xl font-black shadow-lg group-hover:scale-110 transition-transform">
                                    {{ mrp.code.substring(0, 1) }}
                                </div>
                                <span class="px-3 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">Plant {{ mrp.plant }}</span>
                            </div>
                            <h3 class="text-white text-2xl font-extrabold mb-1">{{ mrp.code }}</h3>
                            <p class="text-slate-400 text-sm line-clamp-2">{{ mrp.name || 'Nama area belum didefinisikan' }}</p>
                        </div>
                    </div>
                </div>

                <!-- VIEW 2: HISTORY LIST USER -->
                <div v-else class="animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <button @click="toggleViewMode('mrp')" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Area Kerja
                        </button>
                    </div>

                    <div class="mb-6 bg-[#1e293b]/50 border border-white/10 rounded-2xl p-6 animate-fade-in-up relative overflow-hidden group">
                         <div class="grid grid-cols-1 md:grid-cols-12 gap-4 relative z-10">
                            <!-- Date Range -->
                            <div class="md:col-span-6 flex flex-col gap-1.5">
                                <div class="relative group/date">
                                    <input ref="datePickerRef" type="text" placeholder="Pilih Rentang Tanggal..." class="w-full bg-black/40 rounded-xl border border-white/10 text-white text-sm py-2.5 pl-10 pr-4 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all cursor-pointer">
                                    <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/date:text-indigo-400 transition-colors pointer-events-none"></i>
                                </div>
                            </div>
                             <!-- Status & Search User -->
                            <div class="md:col-span-2 flex flex-col gap-1.5">
                                <select v-model="form.status" class="w-full bg-black/40 rounded-xl border border-white/10 text-white text-sm py-2.5 px-3 focus:border-indigo-500/50">
                                    <option value="">Semua Status</option>
                                    <option value="SUCCESS">Success</option>
                                    <option value="ERROR">Error</option>
                                </select>
                            </div>
                            <div class="md:col-span-4 flex flex-col gap-1.5">
                                <input type="text" v-model="form.search" placeholder="Cari Lot / Material..." class="w-full bg-black/40 rounded-xl border border-white/10 text-white text-sm py-2.5 px-4 focus:border-indigo-500/50">
                            </div>
                        </div>
                    </div>

                    <!-- TABLE USER -->
                    <div class="bg-[#1e293b]/50 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-xl">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white/5 border-b border-white/10 text-xs uppercase tracking-wider text-slate-400 font-bold">
                                        <th class="p-4">Tanggal</th>
                                        <!-- MODIFIKASI USER TABLE -->
                                        <th class="p-4">Order / Batch</th>
                                        <th class="p-4">Material</th>
                                        <th class="p-4">Qty</th>
                                        <th class="p-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-sm">
                                    <tr v-for="item in props.historyList.data" :key="item.id" class="hover:bg-white/5 transition-colors">
                                        <td class="p-4 whitespace-nowrap text-slate-300 font-mono text-xs">{{ formatDate(item.created_at) }}</td>
                                        <!-- MODIFIKASI CELL USER -->
                                        <td class="p-4">
                                            <div class="text-white font-bold">{{ item.order_number || '-' }}</div>
                                            <div v-if="item.batch" class="text-emerald-500 text-xs mt-0.5 font-mono">Batch: {{ item.batch }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ item.sales_order || '-' }}</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="text-slate-200 font-medium line-clamp-1" :title="item.material_desc">{{ truncateText(item.material_desc, 30) }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ removeLeadingZeros(item.material_code) }}</div>
                                        </td>
                                        <td class="p-4 font-mono">{{ parseFloat(item.quantity) }} <span class="text-[0.65rem]">{{ item.uom }}</span></td>
                                        <td class="p-4 text-center">
                                            <span class="px-2 py-1 rounded text-[0.65rem] font-bold border" :class="getStatusColor(item.status)">{{ getStatusLabel(item.status) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="props.historyList.data.length === 0">
                                        <td colspan="5" class="p-10 text-center text-slate-500">Tidak ada riwayat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination User -->
                        <div class="p-3 border-t border-white/10 bg-white/5 flex justify-between items-center">
                            <span class="text-[0.65rem] text-slate-500">Total: {{ props.historyList.total }}</span>
                             <div class="flex gap-1">
                                <template v-for="(link, k) in props.historyList.links" :key="k">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-2 py-1 text-xs rounded border" 
                                          :class="link.active ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-white/10 text-slate-400 hover:bg-white/10'" 
                                          preserve-scroll preserve-state />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
.grid-pattern {
    background-image: linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px), 
                      linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: gridMove 20s linear infinite;
}

@keyframes float { 
    0%, 100% { transform: translate(0, 0); } 
    50% { transform: translate(30px, -30px); } 
}
@keyframes gridMove { 
    0% { transform: translate(0, 0); } 
    100% { transform: translate(50px, 50px); } 
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-float-1 { animation: float 20s ease-in-out infinite; }
.animate-float-2 { animation: float 20s ease-in-out infinite 5s; }
.animate-fade-in-up { animation: fadeInUp 0.6s ease-out backwards; }
</style>
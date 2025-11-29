<script setup>
import { ref, computed, reactive, nextTick, watch, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import flatpickr from "flatpickr";
import "flatpickr/dist/themes/dark.css"; 

const props = defineProps({
    authUser: Object, 
    mrpList: Array,
    historyList: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const processingMrp = ref(null);
const viewMode = ref('mrp'); 

// --- STATE FILTER & TOGGLE ---
const showFilters = ref(false); 
const showColumnDropdown = ref(false);
const datePickerRef = ref(null); 
let fpInstance = null; 

const filters = reactive({
    startDate: '',
    endDate: '',
    status: '', 
    search: '' 
});

const columns = reactive({
    date: { label: 'Tanggal', show: true },
    lot: { label: 'Inspection Lot', show: true },
    material: { label: 'Material', show: true },
    so: { label: 'Sales Order / Item', show: true },
    buyer: { label: 'Buyer / PO', show: true },
    order: { label: 'Order / Batch', show: true },
    qty: { label: 'Qty', show: true },
    ud: { label: 'UD Code', show: true },
    status: { label: 'Status & Log', show: true },
});

const isSpecialUser = computed(() => {
    return props.authUser?.username === 'KMI-U124' && props.authUser?.nik === '10001069';
});

const filteredMrp = computed(() => {
    if (!searchQuery.value) return props.mrpList;
    const lowerSearch = searchQuery.value.toLowerCase();
    return props.mrpList.filter(mrp => 
        mrp.code.toLowerCase().includes(lowerSearch) || 
        (mrp.name && mrp.name.toLowerCase().includes(lowerSearch))
    );
});

// --- HELPER TIMEZONE ---
const formatDateISO = (d) => {
    if (!d) return '';
    const offset = d.getTimezoneOffset();
    const date = new Date(d.getTime() - (offset * 60 * 1000));
    return date.toISOString().split('T')[0];
};

// --- LOGIC FILTER UTAMA ---
const filteredHistory = computed(() => {
    let data = props.historyList;

    // 1. Filter Tanggal (Logic Diperbaiki: Local Time Comparison)
    if (filters.startDate) {
        // Buat tanggal start jam 00:00:00 Local
        const start = new Date(filters.startDate + 'T00:00:00');
        data = data.filter(item => new Date(item.created_at) >= start);
    }
    
    if (filters.endDate) {
        // Buat tanggal end jam 23:59:59 Local
        const end = new Date(filters.endDate + 'T23:59:59.999');
        data = data.filter(item => new Date(item.created_at) <= end);
    }

    // 2. Filter Status
    if (filters.status) {
        data = data.filter(item => item.status === filters.status);
    }

    // 3. Filter Search (Multi-Input & All Columns)
    if (filters.search) {
        // Split berdasarkan spasi, koma, atau baris baru (enter) -> Support Copy Paste Excel
        const terms = filters.search.toLowerCase().split(/[\s,\n]+/).filter(t => t.trim().length > 0);
        
        if (terms.length > 0) {
            data = data.filter(item => {
                // Cek apakah item ini mengandung SALAH SATU kata kunci (Logic OR antar keywords)
                return terms.some(term => {
                    return (
                        (item.prueflos && item.prueflos.toLowerCase().includes(term)) ||
                        (item.material_desc && item.material_desc.toLowerCase().includes(term)) ||
                        (item.material_code && item.material_code.toLowerCase().includes(term)) ||
                        (item.sales_order && item.sales_order.toLowerCase().includes(term)) ||
                        (item.buyer_name && item.buyer_name.toLowerCase().includes(term)) ||
                        (item.customer_po && item.customer_po.toLowerCase().includes(term)) ||
                        (item.order_number && item.order_number.toLowerCase().includes(term)) ||
                        (item.batch && item.batch.toLowerCase().includes(term)) ||
                        (item.ud_code && item.ud_code.toLowerCase().includes(term))
                    );
                });
            });
        }
    }

    return data;
});

// --- FLATPICKR CONFIG ---
const initFlatpickr = () => {
    if (datePickerRef.value && !fpInstance) {
        fpInstance = flatpickr(datePickerRef.value, {
            mode: "range",
            dateFormat: "Y-m-d",
            theme: "dark",
            defaultDate: [filters.startDate, filters.endDate],
            onChange: (selectedDates) => {
                if (selectedDates.length > 0) filters.startDate = formatDateISO(selectedDates[0]);
                
                if (selectedDates.length > 1) {
                    filters.endDate = formatDateISO(selectedDates[1]);
                } else {
                    // Jangan kosongkan endDate dulu saat selection, tunggu user selesai atau onClose
                    // Agar UX tidak 'flicker' hasil
                }
                
                if (selectedDates.length === 0) {
                    filters.startDate = '';
                    filters.endDate = '';
                }
            },
            onClose: (selectedDates) => {
                // BUG FIX: Jika user hanya pilih 1 tanggal lalu close, anggap Start = End
                if (selectedDates.length === 1) {
                    const singleDate = formatDateISO(selectedDates[0]);
                    filters.startDate = singleDate;
                    filters.endDate = singleDate;
                    // Update tampilan kalender jadi range hari yang sama
                    fpInstance.setDate([selectedDates[0], selectedDates[0]], true); 
                }
            }
        });
    }
};

watch(showFilters, async (newVal) => {
    if (newVal) {
        await nextTick();
        initFlatpickr();
    } else {
        if (fpInstance) {
            fpInstance.destroy();
            fpInstance = null;
        }
    }
});

onBeforeUnmount(() => {
    if (fpInstance) fpInstance.destroy();
});

// Helper: Quick Date Actions (Updated)
const setDateRange = (type) => {
    const today = new Date();
    const start = new Date();
    
    if (type === 'today') {
        // Start = End = Today
    } else if (type === '7days') {
        start.setDate(today.getDate() - 7);
    } else if (type === 'month') {
        start.setDate(1); 
    }
    
    filters.startDate = formatDateISO(start);
    filters.endDate = formatDateISO(today);

    if (fpInstance) {
        fpInstance.setDate([filters.startDate, filters.endDate], true); // Trigger onChange
    }
};

const removeLeadingZeros = (str) => {
    if (!str) return '';
    return parseInt(str, 10).toString();
};

const toggleViewMode = (mode) => {
    viewMode.value = mode;
    if(mode === 'mrp') showFilters.value = false;
};

const goToInspectionList = (mrpItem) => {
    processingMrp.value = mrpItem.code;
    router.get(`/inspection/${mrpItem.code}`, { plant: mrpItem.plant }, {
        onFinish: () => { processingMrp.value = null; }
    });
};

const logout = () => {
    router.post('/logout');
};

const printHistory = () => {
    const params = new URLSearchParams();
    if (filters.startDate) params.append('start_date', filters.startDate);
    if (filters.endDate) params.append('end_date', filters.endDate); 
    
    Object.keys(columns).forEach(key => {
        if (columns[key].show === false) params.append(`hide_${key}`, '1');
    });
    
    window.open(`/inspection/history/export?${params.toString()}`, '_blank');
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const getHistoryStatusColor = (status) => {
    if (status === 'SUCCESS') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    if (status === 'ERROR') return 'bg-red-500/10 text-red-400 border-red-500/20';
    return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
};
</script>

<template>
    <Head title="Dashboard Area Kerja" />

    <div class="relative min-h-screen bg-slate-900 font-sans text-slate-200 overflow-x-hidden selection:bg-emerald-500 selection:text-white pb-20">
        
        <!-- Background Ambient -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-emerald-500/10 shadow-lg">
            <div class="max-w-[1400px] mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10 backdrop-blur-sm shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                        <img src="/images/KMI.png" alt="KMI Logo" class="h-9 md:h-10 w-auto drop-shadow-md brightness-110" />
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-white font-extrabold text-lg tracking-tight leading-tight">KMI Inspection</h3>
                        <span class="text-emerald-500 text-[0.65rem] font-bold uppercase tracking-widest">Quality Control System</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-white font-bold text-sm">{{ authUser.username }}</span>
                            <span class="text-slate-400 text-xs font-medium">NIK: {{ authUser.nik }}</span>
                        </div>
                    </div>
                    <button @click="logout" class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 transition-all flex items-center justify-center" title="Keluar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </nav>

        <main class="relative z-10 max-w-[1400px] mx-auto px-6 py-12">
            
            <!-- ========================================== -->
            <!-- VIEW: USER BIASA (Regular Inspector)       -->
            <!-- ========================================== -->
            <div v-if="!isSpecialUser">
                
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
                             class="group min-w-[220px] bg-white/5 backdrop-blur-md border rounded-2xl p-5 flex items-center gap-4 cursor-pointer transition-all duration-300"
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
                             class="group min-w-[220px] bg-white/5 backdrop-blur-md border rounded-2xl p-5 flex items-center gap-4 cursor-pointer transition-all duration-300"
                             :class="viewMode === 'history' ? 'border-indigo-500/50 bg-white/10 shadow-[0_0_20px_rgba(99,102,241,0.2)]' : 'border-white/10 hover:bg-white/10'">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-colors"
                                 :class="viewMode === 'history' ? 'bg-indigo-500/20 text-indigo-400' : 'bg-slate-400/10 text-slate-400 group-hover:text-indigo-400'">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-3xl font-extrabold text-white leading-none">{{ props.historyList.length }}</span>
                                <span class="text-sm font-medium mt-1" :class="viewMode === 'history' ? 'text-indigo-400' : 'text-slate-400'">Riwayat Saya</span>
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

                <!-- VIEW 2: HISTORY LIST (User Biasa) -->
                <div v-else class="animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <button @click="toggleViewMode('mrp')" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Area Kerja
                        </button>
                        
                        <button @click="showFilters = !showFilters" 
                                class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-sm font-bold text-slate-300 flex items-center gap-2 transition-colors"
                                :class="{'bg-indigo-500/20 border-indigo-500/50 text-indigo-300': showFilters}">
                            <i class="fa-solid fa-filter"></i> {{ showFilters ? 'Tutup Filter' : 'Filter & Cari' }}
                        </button>
                    </div>

                    <!-- PANEL FILTER (User Biasa) -->
                    <div v-if="showFilters" class="mb-6 bg-[#1e293b]/50 border border-white/10 rounded-2xl p-6 animate-fade-in-up relative overflow-hidden group">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-[50px] pointer-events-none"></div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 relative z-10">
                            
                            <!-- Date Range (Flatpickr) -->
                            <div class="md:col-span-6 flex flex-col gap-1.5">
                                <div class="flex justify-between items-center">
                                    <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Periode Inspeksi</label>
                                    <div class="flex gap-1">
                                        <button @click="setDateRange('today')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">Hari Ini</button>
                                        <button @click="setDateRange('7days')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">7 Hari</button>
                                    </div>
                                </div>
                                <div class="relative group/date">
                                    <input 
                                        ref="datePickerRef" 
                                        type="text" 
                                        placeholder="Pilih Rentang Tanggal..." 
                                        class="w-full bg-black/40 rounded-xl border border-white/10 text-white text-sm py-2.5 pl-10 pr-4 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all cursor-pointer"
                                    >
                                    <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/date:text-indigo-400 transition-colors pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="md:col-span-2 flex flex-col gap-1.5">
                                <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Status</label>
                                <div class="relative h-[42px]">
                                    <select v-model="filters.status" class="w-full h-full appearance-none bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-4 pr-10 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all cursor-pointer">
                                        <option value="">Semua</option>
                                        <option value="SUCCESS">Success</option>
                                        <option value="ERROR">Error</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="md:col-span-4 flex flex-col gap-1.5">
                                <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Multi-Search (Spasi/Enter)</label>
                                <div class="relative group/search h-[42px]">
                                    <input type="text" v-model="filters.search" placeholder="Lot A, Lot B, Material..." class="w-full h-full bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-10 pr-4 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                                    <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within/search:text-indigo-400 transition-colors"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="bg-[#1e293b]/50 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-xl">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white/5 border-b border-white/10 text-xs uppercase tracking-wider text-slate-400 font-bold">
                                        <th class="p-4" v-if="columns.date.show">Tanggal</th>
                                        <th class="p-4" v-if="columns.lot.show">Lot</th>
                                        <th class="p-4" v-if="columns.so.show">SO / Item</th> 
                                        <th class="p-4" v-if="columns.buyer.show">Buyer / PO</th>
                                        <th class="p-4" v-if="columns.material.show">Material</th>
                                        <th class="p-4" v-if="columns.qty.show">Qty</th>
                                        <th class="p-4 text-center" v-if="columns.status.show">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-sm">
                                    <tr v-for="item in filteredHistory" :key="item.id" class="hover:bg-white/5 transition-colors">
                                        <td class="p-4 whitespace-nowrap text-slate-300 font-mono text-xs">{{ formatDate(item.created_at) }}</td>
                                        <td class="p-4 font-bold text-white">{{ item.prueflos }}</td>
                                        <td class="p-4 whitespace-nowrap">
                                            <div class="text-indigo-300 font-mono font-bold">{{ item.sales_order || '-' }}</div>
                                            <div v-if="item.sales_item" class="text-xs text-slate-500">Item: {{ removeLeadingZeros(item.sales_item) }}</div>
                                        </td>
                                        <td class="p-4 max-w-[150px]">
                                            <div class="truncate text-white font-bold text-xs" :title="item.buyer_name">{{ item.buyer_name || '-' }}</div>
                                            <div class="text-[0.65rem] text-emerald-400 font-mono truncate" :title="item.customer_po">{{ item.customer_po || '-' }}</div>
                                        </td>
                                        <td class="p-4 max-w-[200px]">
                                            <div class="truncate font-medium text-slate-200" :title="item.material_desc">{{ item.material_desc || '-' }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ item.material_code }}</div>
                                        </td>
                                        <td class="p-4 font-mono">{{ parseFloat(item.quantity) }} <span class="text-[0.65rem]">{{ item.uom }}</span></td>
                                        
                                        <td class="p-4 text-center">
                                            <span class="px-2 py-1 rounded text-[0.65rem] font-bold border" :class="getHistoryStatusColor(item.status)">{{ item.status }}</span>
                                            <div v-if="item.status === 'ERROR' && item.sap_message" 
                                                 class="mt-2 text-[0.6rem] text-red-300 bg-red-500/10 p-1.5 rounded border border-red-500/20 text-left leading-tight max-w-[180px] mx-auto break-words relative group cursor-help">
                                                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ item.sap_message.substring(0, 30) }}{{ item.sap_message.length > 30 ? '...' : '' }}
                                                <div class="hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 bg-slate-800 border border-white/20 p-2 rounded shadow-xl z-50 text-white">
                                                    {{ item.sap_message }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredHistory.length === 0">
                                        <td colspan="7" class="p-10 text-center text-slate-500">Tidak ada riwayat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- VIEW: USER KHUSUS (KMI-U124)               -->
            <!-- ========================================== -->
            <div v-else class="animate-fade-in-up">
                
                <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6 mb-8">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold mb-3">
                            <i class="fa-solid fa-user-gear"></i> Administrator View
                        </div>
                        <h2 class="text-3xl font-extrabold text-white tracking-tight">Riwayat Inspeksi Lengkap</h2>
                    </div>

                    <div class="flex flex-col gap-4 w-full xl:w-auto">
                        <div class="flex justify-end">
                             <button @click="showFilters = !showFilters" class="text-sm font-bold text-slate-400 hover:text-white transition-colors flex items-center gap-2">
                                <i class="fa-solid" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i> 
                                {{ showFilters ? 'Sembunyikan Panel Kontrol' : 'Tampilkan Panel Kontrol' }}
                            </button>
                        </div>

                        <!-- PANEL FILTER (User Khusus) -->
                        <div v-if="showFilters" class="w-full bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 p-5 rounded-2xl animate-fade-in-up mb-2 shadow-2xl relative overflow-hidden">
                            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-emerald-500/10 rounded-full blur-[60px] pointer-events-none"></div>

                            <div class="flex flex-col lg:flex-row gap-4 items-end relative z-10">
                                
                                <!-- Date Range (Flatpickr) -->
                                <div class="flex-1 flex flex-col gap-1.5 w-full lg:w-auto">
                                    <div class="flex justify-between items-center">
                                        <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Periode Inspeksi</label>
                                        <div class="flex gap-1">
                                            <button @click="setDateRange('today')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">Hari Ini</button>
                                            <button @click="setDateRange('7days')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">7 Hari</button>
                                            <button @click="setDateRange('month')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">Bulan Ini</button>
                                        </div>
                                    </div>
                                    <div class="relative group/date">
                                        <input 
                                            ref="datePickerRef" 
                                            type="text" 
                                            placeholder="Pilih Rentang Tanggal..." 
                                            class="w-full bg-black/40 rounded-xl border border-white/10 text-white text-sm py-2.5 pl-10 pr-4 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all cursor-pointer"
                                        >
                                        <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/date:text-emerald-400 transition-colors pointer-events-none"></i>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="w-full lg:w-48 flex flex-col gap-1.5">
                                    <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Status QC</label>
                                    <div class="relative h-[42px]">
                                        <select v-model="filters.status" class="w-full h-full appearance-none bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-4 pr-10 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all cursor-pointer">
                                            <option value="">Semua Status</option>
                                            <option value="SUCCESS">Success</option>
                                            <option value="ERROR">Error</option>
                                        </select>
                                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs pointer-events-none"></i>
                                    </div>
                                </div>

                                <!-- Search -->
                                <div class="flex-1 flex flex-col gap-1.5 w-full">
                                    <label class="text-[0.65rem] uppercase font-bold text-slate-300 tracking-wider ml-1">Multi-Search (Spasi/Enter)</label>
                                    <div class="relative group/search h-[42px]">
                                        <input type="text" v-model="filters.search" placeholder="Lot A, Lot B, Material..." class="w-full h-full bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-10 pr-4 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all">
                                        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within/search:text-emerald-400 transition-colors"></i>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pb-0.5">
                                    <div class="relative">
                                        <button @click="showColumnDropdown = !showColumnDropdown" 
                                                class="h-[42px] px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-sm font-bold text-slate-300 flex items-center gap-2 transition-all active:scale-95"
                                                :class="{'bg-white/10 text-white': showColumnDropdown}"
                                                title="Atur Kolom">
                                            <i class="fa-solid fa-table-columns"></i>
                                        </button>
                                        
                                        <div v-if="showColumnDropdown" class="absolute right-0 top-full mt-2 w-56 bg-[#1e293b] border border-white/10 rounded-xl shadow-2xl z-50 p-2 transform origin-top-right transition-all">
                                            <div class="text-[0.65rem] font-bold text-slate-500 uppercase px-2 py-1 mb-1">Tampilkan Kolom</div>
                                            <div v-for="(col, key) in columns" :key="key" class="flex items-center gap-3 p-2 hover:bg-white/5 rounded-lg cursor-pointer transition-colors" @click="col.show = !col.show">
                                                <div class="w-4 h-4 rounded border flex items-center justify-center transition-all" 
                                                     :class="col.show ? 'bg-emerald-500 border-emerald-500' : 'border-slate-600 bg-transparent'">
                                                    <i v-if="col.show" class="fa-solid fa-check text-[0.6rem] text-black"></i>
                                                </div>
                                                <span class="text-sm" :class="col.show ? 'text-white' : 'text-slate-400'">{{ col.label }}</span>
                                            </div>
                                        </div>
                                        <div v-if="showColumnDropdown" @click="showColumnDropdown = false" class="fixed inset-0 z-40 cursor-default"></div>
                                    </div>

                                    <button @click="printHistory" 
                                            class="h-[42px] px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-sm shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all hover:-translate-y-0.5 active:translate-y-0">
                                        <i class="fa-solid fa-print"></i> Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE HISTORY (Special User) -->
                <div class="bg-[#1e293b]/50 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto min-h-[400px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white/5 border-b border-white/10 text-xs uppercase tracking-wider text-slate-400 font-bold">
                                    <th class="p-4" v-if="columns.date.show">Tanggal</th>
                                    <th class="p-4" v-if="columns.lot.show">Lot</th>
                                    <th class="p-4" v-if="columns.so.show">SO / Item</th> 
                                    <th class="p-4" v-if="columns.buyer.show">Buyer / PO</th>
                                    <th class="p-4" v-if="columns.material.show">Material</th>
                                    <th class="p-4" v-if="columns.order.show">Order/Batch</th>
                                    <th class="p-4" v-if="columns.qty.show">Qty</th>
                                    <th class="p-4" v-if="columns.ud.show">UD</th>
                                    <th class="p-4 text-center" v-if="columns.status.show">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-for="item in filteredHistory" :key="item.id" class="hover:bg-white/5 transition-colors">
                                    <td class="p-4 whitespace-nowrap text-slate-300 font-mono text-xs" v-if="columns.date.show">{{ formatDate(item.created_at) }}</td>
                                    <td class="p-4 font-bold text-white" v-if="columns.lot.show">{{ item.prueflos }}</td>
                                    
                                    <td class="p-4 whitespace-nowrap" v-if="columns.so.show">
                                        <div class="text-indigo-300 font-mono font-bold">{{ item.sales_order || '-' }}</div>
                                        <div v-if="item.sales_item" class="text-xs text-slate-500">Item: {{ removeLeadingZeros(item.sales_item) }}</div>
                                    </td>
                                    <td class="p-4 max-w-[150px]" v-if="columns.buyer.show">
                                        <div class="truncate text-white font-bold text-xs" :title="item.buyer_name">{{ item.buyer_name || '-' }}</div>
                                        <div class="text-[0.65rem] text-emerald-400 font-mono truncate" :title="item.customer_po">{{ item.customer_po || '-' }}</div>
                                    </td>

                                    <td class="p-4 max-w-[200px]" v-if="columns.material.show">
                                        <div class="truncate font-medium text-slate-200" :title="item.material_desc">{{ item.material_desc || '-' }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ item.material_code }}</div>
                                    </td>
                                    <td class="p-4" v-if="columns.order.show">
                                        <div class="text-xs text-slate-400">Ord: <span class="text-slate-300">{{ item.order_number || '-' }}</span></div>
                                        <div class="text-xs text-slate-400">Bch: <span class="text-slate-300">{{ item.batch || '-' }}</span></div>
                                    </td>
                                    <td class="p-4 font-mono" v-if="columns.qty.show">{{ parseFloat(item.quantity) }} <span class="text-[0.65rem]">{{ item.uom }}</span></td>
                                    <td class="p-4" v-if="columns.ud.show">
                                        <span class="font-bold text-white bg-slate-800 px-2 py-1 rounded border border-white/10 text-xs">{{ item.ud_code }}</span>
                                    </td>
                                    
                                    <td class="p-4 text-center" v-if="columns.status.show">
                                        <span class="px-2 py-1 rounded text-[0.65rem] font-bold border" :class="getHistoryStatusColor(item.status)">{{ item.status }}</span>
                                         <div v-if="item.status === 'ERROR' && item.sap_message" 
                                             class="mt-2 text-[0.6rem] text-red-300 bg-red-500/10 p-1.5 rounded border border-red-500/20 text-left leading-tight max-w-[180px] mx-auto break-words relative group cursor-help">
                                            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ item.sap_message.substring(0, 30) }}{{ item.sap_message.length > 30 ? '...' : '' }}
                                            <div class="hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 bg-slate-800 border border-white/20 p-2 rounded shadow-xl z-50 text-white">
                                                {{ item.sap_message }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredHistory.length === 0">
                                    <td colspan="9" class="p-10 text-center text-slate-500">
                                        Tidak ada data yang sesuai dengan filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-t border-white/10 bg-white/5 text-[0.65rem] text-slate-500 text-right">
                        Menampilkan {{ filteredHistory.length }} data
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
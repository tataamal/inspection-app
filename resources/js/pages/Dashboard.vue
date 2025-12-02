<script setup>
import { ref, computed, reactive, nextTick, watch, onBeforeUnmount } from 'vue';
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
    filters: Object 
});

const searchQuery = ref('');
const processingMrp = ref(null);
const viewMode = ref(props.authUser?.role === 'admin' ? 'history' : 'mrp'); 

// --- STATE FILTER ---
const showFilters = ref(false); // Default false untuk user biasa agar tidak semak
const datePickerRef = ref(null); 
let fpInstance = null; 

// Reactive Form untuk Server-Side Filtering
const form = reactive({
    startDate: props.filters?.startDate || '',
    endDate: props.filters?.endDate || '',
    status: props.filters?.status || 'SUCCESS', // Default Success
    section: props.filters?.section || '',
    search: props.filters?.search || ''
});

const isAdmin = computed(() => {
    return props.authUser?.role === 'admin';
});

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
    if (mode === 'history') {
        nextTick(() => initFlatpickr());
    }
};

const goToInspectionList = (mrpItem) => {
    processingMrp.value = mrpItem.code;
    router.get(`/inspection/${mrpItem.code}`, { plant: mrpItem.plant }, {
        onFinish: () => { processingMrp.value = null; }
    });
};

// --- WATCHER UNTUK SERVER-SIDE FILTERING ---
const applyFilter = debounce(() => {
    if (viewMode.value === 'history' || isAdmin.value) {
        router.get('/dashboard', form, {
            preserveState: true, 
            preserveScroll: true, 
            replace: true 
        });
    }
}, 500); 

// FIXED: Tambahkan { immediate: true } agar filter 'SUCCESS' jalan saat pertama load
watch(form, () => {
    applyFilter();
}, { deep: true, immediate: true });

// --- HELPER TIMEZONE ---
const formatDateISO = (d) => {
    if (!d) return '';
    const offset = d.getTimezoneOffset();
    const date = new Date(d.getTime() - (offset * 60 * 1000));
    return date.toISOString().split('T')[0];
};

// --- DATE ACTIONS ---
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
        // FIXED: Logika Kemarin (-1 Hari) yang bersih
        start.setDate(today.getDate() - 1);
        end.setDate(today.getDate() - 1);
    }
    
    form.startDate = formatDateISO(start);
    form.endDate = formatDateISO(end);

    if (fpInstance) {
        fpInstance.setDate([form.startDate, form.endDate], true);
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

// --- FLATPICKR ---
const initFlatpickr = () => {
    if (datePickerRef.value && !fpInstance) {
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

watch(() => isAdmin.value, (val) => {
    if(val) nextTick(() => initFlatpickr());
}, { immediate: true });

onBeforeUnmount(() => {
    if (fpInstance) fpInstance.destroy();
});

// --- ACTIONS ---
const printHistory = () => {
    const params = new URLSearchParams();
    if (form.startDate) params.append('startDate', form.startDate); 
    if (form.endDate) params.append('endDate', form.endDate); 
    if (form.status) params.append('status', form.status);
    if (form.section) params.append('section', form.section);
    if (form.search) params.append('search', form.search);
    
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
    // Cek apakah string HANYA berisi angka (0-9)
    if (/^\d+$/.test(str)) {
        // Hapus nol di depan. Jika hasilnya kosong (misal "000"), return "0"
        return str.replace(/^0+/, '') || '0';
    }
    // Jika ada huruf/simbol, kembalikan apa adanya
    return str;
};

const truncateText = (text, length) => {
    if (!text) return '-';
    return text.length > length ? text.substring(0, length) + '...' : text;
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
            <!-- TAMPILAN ADMIN                             -->
            <!-- ========================================== -->
            <div v-if="isAdmin" class="animate-fade-in-up flex flex-col gap-6">
                
                <!-- ROW 1: HEADER ADMIN -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/5 pb-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-xs font-bold mb-2">
                            <i class="fa-solid fa-shield-halved"></i> ADMINISTRATOR ACCESS
                        </div>
                        <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                            Riwayat Inspeksi & Audit
                        </h2>
                    </div>
                    <div class="text-right hidden md:block">
                        <span class="text-slate-400 text-sm">Total Data: </span>
                        <span class="text-2xl font-bold text-white">{{ props.historyList.total }}</span>
                    </div>
                </div>

                <!-- ROW 2: FILTERS ADMIN -->
                <div class="bg-[#1e293b]/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 md:p-5 shadow-xl">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        
                        <div class="md:col-span-5 flex flex-col gap-2">
                            <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Periode Waktu</label>
                            <div class="flex flex-wrap gap-2 mb-1">
                                <button @click="setDateRange('today')" class="px-3 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 active:scale-95 active:bg-emerald-500/30 transition-all duration-150">Hari Ini</button>
                                <button @click="setDateRange('week')" class="px-3 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 active:scale-95 active:bg-emerald-500/30 transition-all duration-150">Minggu Ini</button>
                                <button @click="setDateRange('yesterday')" class="px-3 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 active:scale-95 active:bg-emerald-500/30 transition-all duration-150">Kemarin</button>
                                <button @click="setDateRange('7days')" class="px-3 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 active:scale-95 active:bg-emerald-500/30 transition-all duration-150">-7 Hari</button>
                                <button @click="setDateRange('30days')" class="px-3 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 active:scale-95 active:bg-emerald-500/30 transition-all duration-150">-30 Hari</button>
                            </div>

                            <div class="relative group/date w-full">
                                <input ref="datePickerRef" type="text" placeholder="Custom Tanggal..." 
                                    class="w-full bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 pl-9 pr-3 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all"
                                >
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                        </div>

                        <div class="md:col-span-3 flex flex-col gap-2">
                            <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Filter Kategori</label>
                            <div class="flex gap-2">
                                <select v-model="form.status" class="w-1/2 bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 px-3 focus:border-emerald-500/50">
                                    <option value="">Semua Status</option>
                                    <option value="SUCCESS">Success</option>
                                    <option value="ERROR">Gagal UD</option>
                                </select>
                                <select v-model="form.section" class="w-1/2 bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 px-3 focus:border-emerald-500/50">
                                    <option value="">Semua Bagian</option>
                                    <option value="Packing">Packing</option>
                                    <option value="Painting">Painting</option>
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-4 flex flex-col gap-2">
                            <label class="text-[0.65rem] uppercase font-bold text-slate-400 tracking-wider">Pencarian & Aksi</label>
                            <div class="relative w-full mb-1">
                                <input type="text" v-model="form.search" placeholder='Cari Lot, Material... gunakan "" untuk spesifik' 
                                    class="w-full bg-black/30 rounded-lg border border-white/10 text-white text-sm py-2 pl-9 pr-3 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all"
                                >
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>

                            <div class="flex gap-2">
                                <button @click="clearFilters" class="flex-1 py-2 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 active:bg-white/20 active:scale-95 text-slate-300 text-xs font-bold transition-all duration-150">
                                    <i class="fa-solid fa-xmark mr-1"></i> Clear
                                </button>
                                <button @click="printHistory" class="flex-1 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 active:scale-95 active:shadow-none text-white text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all duration-150">
                                    <i class="fa-solid fa-print mr-1"></i> Cetak
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ROW 3: TABLE ADMIN -->
                <div class="bg-[#1e293b]/40 backdrop-blur-sm border border-white/5 rounded-2xl overflow-hidden shadow-2xl flex flex-col min-h-[500px]">
                    <div class="flex-grow">
                        <!-- MOBILE ADMIN -->
                        <div class="md:hidden flex flex-col gap-3 p-4">
                            <div v-for="item in props.historyList.data" :key="item.id" class="bg-[#0f172a] border border-white/10 rounded-xl p-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-2">
                                    <span class="text-[0.6rem] font-mono text-slate-500">{{ formatDate(item.created_at) }}</span>
                                </div>
                                <div class="mb-3 pr-16">
                                    <div class="text-xs text-slate-400 uppercase font-bold">Inspection Lot</div>
                                    <div class="text-lg font-bold text-white tracking-wide">{{ item.prueflos }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-y-3 gap-x-2 text-sm mb-3">
                                    <div>
                                        <div class="text-[0.65rem] text-slate-500">Sales Order</div>
                                        <div class="text-indigo-300 font-mono">{{ item.sales_order || '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[0.65rem] text-slate-500">Buyer</div>
                                        <div class="text-slate-300 truncate">{{ item.buyer_name || '-' }}</div>
                                    </div>
                                    <div class="col-span-2">
                                        <div class="text-[0.65rem] text-slate-500">Material</div>
                                        <div class="text-slate-200 font-medium leading-tight">
                                            {{ truncateText(item.material_desc, 40) }}
                                        </div>
                                        <div class="text-xs text-slate-600 font-mono mt-0.5">{{ removeLeadingZeros(item.material_code) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[0.65rem] text-slate-500">Qty</div>
                                        <div class="text-white font-mono">{{ parseFloat(item.quantity) }} {{ item.uom === 'ST' ? 'PC' : item.uom }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-white/5">
                                    <span class="px-2 py-1 rounded text-[0.65rem] font-bold border" :class="getStatusColor(item.status)">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                    <div v-if="item.status === 'ERROR'" class="text-[0.65rem] text-red-400 italic text-right max-w-[60%] truncate">
                                        {{ item.sap_message }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="props.historyList.data.length === 0" class="text-center py-10 text-slate-500">
                                <i class="fa-solid fa-box-open text-3xl mb-2 opacity-50"></i>
                                <p>Tidak ada data ditemukan</p>
                            </div>
                        </div>

                        <!-- DESKTOP ADMIN -->
                        <table class="w-full text-left border-collapse hidden md:table">
                            <thead class="sticky top-0 bg-[#0f172a] z-10 shadow-sm">
                                <tr class="text-xs uppercase tracking-wider text-slate-400 font-bold border-b border-white/10">
                                    <th class="p-4 w-[120px]">Tanggal</th>
                                    <th class="p-4">SO / Item</th>
                                    <th class="p-4">Order / Lot</th>
                                    <th class="p-4 w-[25%]">Material</th>
                                    <th class="p-4">Buyer / PO</th>
                                    <th class="p-4 text-right">Qty</th>
                                    <th class="p-4 text-center w-[120px]">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-for="item in props.historyList.data" :key="item.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4 whitespace-nowrap text-slate-400 font-mono text-sm align-top">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="text-indigo-400 font-mono text-sm font-bold">{{ item.sales_order || '-' }}</div>
                                        <div class="text-white text-xs mt-0.5">Item: {{ item.sales_item || '-' }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="text-white font-bold text-sm">{{ item.prueflos }}</div>
                                        <div class="text-emerald-500 text-xs mt-0.5" v-if="item.batch">Batch: {{ item.batch }}</div>
                                        <div class="text-emerald-500 text-xs" v-if="item.order_number">Order: {{ item.order_number }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="text-slate-200 font-medium text-sm leading-relaxed" :title="item.material_desc">
                                            {{ truncateText(item.material_desc, 40) }}
                                        </div>
                                        <div class="text-xs text-emerald-500 font-mono mt-1">{{ removeLeadingZeros(item.material_code) }}</div>
                                    </td>
                                    <td class="p-4 align-top max-w-[150px]">
                                        <div class="truncate text-slate-300 text-sm font-bold" :title="item.buyer_name">{{ item.buyer_name || '-' }}</div>
                                        <div class="text-xs text-emerald-500 font-mono truncate">{{ item.customer_po || '-' }}</div>
                                    </td>
                                    <td class="p-4 align-top text-right font-mono text-slate-300 text-sm">
                                        {{ parseFloat(item.quantity) }} <span class="text-xs text-white">{{ item.uom === 'ST' ? 'PC' : item.uom }}</span>
                                    </td>
                                    <td class="p-4 align-top text-center">
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold border" :class="getStatusColor(item.status)">
                                            {{ getStatusLabel(item.status) }}
                                        </span>
                                        <div v-if="item.status === 'ERROR' && item.sap_message" class="relative group/tooltip mt-1 flex justify-center">
                                            <i class="fa-solid fa-circle-info text-red-500/50 hover:text-red-400 cursor-help"></i>
                                            <div class="hidden group-hover/tooltip:block absolute right-full top-0 w-48 bg-slate-800 border border-red-500/30 p-2 rounded shadow-xl z-50 text-[0.6rem] text-red-200 text-left">
                                                {{ item.sap_message }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="props.historyList.data.length === 0">
                                    <td colspan="7" class="p-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fa-solid fa-filter-circle-xmark text-3xl opacity-50"></i>
                                            <span>Tidak ada data yang sesuai filter</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- FOOTER PAGINATION -->
                    <div class="bg-[#0f172a] p-3 border-t border-white/5 flex justify-between items-center px-4">
                        <span class="text-[0.65rem] text-slate-500">
                            Menampilkan {{ props.historyList.from || 0 }} - {{ props.historyList.to || 0 }} dari {{ props.historyList.total }} data
                        </span>
                        <div class="flex items-center gap-1">
                            <template v-for="(link, k) in props.historyList.links" :key="k">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs rounded border transition-all duration-150"
                                    :class="{
                                        'bg-emerald-500 border-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/20': link.active,
                                        'bg-white/5 border-white/10 text-slate-400 hover:bg-white/10 active:scale-95 active:bg-white/20': !link.active
                                    }"
                                    preserve-scroll
                                    preserve-state
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-3 py-1 text-xs rounded border border-transparent text-slate-600 opacity-50 cursor-not-allowed"
                                ></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAMPILAN USER BIASA (Dikembalikan)         -->
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

                <!-- VIEW 2: HISTORY LIST (User Biasa - Now using Paginated Table) -->
                <div v-else class="animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <button @click="toggleViewMode('mrp')" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Area Kerja
                        </button>
                    </div>

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
                                        <!-- Mengganti Bulan Ini menjadi Kemarin untuk User Biasa juga -->
                                        <button @click="setDateRange('yesterday')" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[0.6rem] text-slate-400 hover:text-white transition-colors border border-white/5">Kemarin</button>
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
                                    <select v-model="form.status" class="w-full h-full appearance-none bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-4 pr-10 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all cursor-pointer">
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
                                    <input type="text" v-model="form.search" placeholder="Lot A, Lot B, Material..." class="w-full h-full bg-black/40 rounded-xl border border-white/10 text-white text-sm pl-10 pr-4 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all">
                                    <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within/search:text-indigo-400 transition-colors"></i>
                                </div>
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
                                        <th class="p-4">Lot / Order</th>
                                        <th class="p-4">Material</th>
                                        <th class="p-4">Qty</th>
                                        <th class="p-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-sm">
                                    <tr v-for="item in props.historyList.data" :key="item.id" class="hover:bg-white/5 transition-colors">
                                        <td class="p-4 whitespace-nowrap text-slate-300 font-mono text-xs">{{ formatDate(item.created_at) }}</td>
                                        <td class="p-4">
                                            <div class="text-white font-bold">{{ item.prueflos }}</div>
                                            <div class="text-xs text-slate-500">{{ item.sales_order || item.order_number || '-' }}</div>
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
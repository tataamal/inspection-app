<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    initialLots: Array,
    dispoCode: String,
    plantCode: String,
    authUser: Object,
    errorMessage: String
});

const searchQuery = ref('');
const isRefreshing = ref(false);
const selectedLots = ref([]);
const isProcessingBulk = ref(false);
const isSyncing = ref(false);
const showProgressModal = ref(false);
const progressLogs = ref([]);
const progressStats = ref({ success: 0, fail: 0, total: 0 });
const logContainerRef = ref(null);
const showModal = ref(false);
const isLoadingComponents = ref(false);
const selectedComponents = ref([]);
const selectedLotNumber = ref('');
const selectedOrderNumber = ref('');

// --- 1. CONFIG STATUS (Logic Warna & Action) ---
const getStatusConfig = (stats) => {
    if (!stats) return { class: 'bg-slate-500/20 text-slate-400', label: 'N/A', action: 'unknown' };
    
    const s = stats.trim();

    // Prioritas Pengecekan String
    if (s.includes('REL') && s.includes('REL')) {
        return { class: 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20', label: s, action: 'allow' };
    }
    if (s.includes('TECO') || (s.includes('UD') && !s.includes('REL'))) { 
        // Logic: Jika ada TECO, atau ada UD tapi TIDAK ada REL (Pure UD)
        return { class: 'bg-rose-500/20 text-rose-400 border border-rose-500/20', label: s, action: 'block_teco' };
    }
    if (s.includes('REL') && s.includes('UD')) {
        return { class: 'bg-amber-500/20 text-amber-400 border border-amber-500/20', label: s, action: 'block_rel_ud' };
    }
    if (s.includes('CRTD')) {
        return { class: 'bg-blue-500/20 text-blue-400 border border-blue-500/20', label: s, action: 'block_crtd' };
    }

    // Default fallback
    return { class: 'bg-slate-500/20 text-slate-400', label: s, action: 'unknown' };
};

// --- 2. HANDLER TOMBOL INSPECT (Individual) ---
const handleInspect = (lot) => {
    const config = getStatusConfig(lot.STATS);
    if (config.action === 'allow') {
        router.visit(`/inspection/form/${lot.PRUEFLOS}?plant=${props.plantCode}&dispo=${props.dispoCode}`);
    } else {
        // Tampilkan pesan error sesuai status
        showStatusAlert(lot.PRUEFLOS, lot.STATS, config.action);
    }
};

// --- 3. HELPER ALERT (Supaya tidak duplikat kodingan alert) ---
const showStatusAlert = (lotNumber, stats, actionType) => {
    if (actionType === 'block_teco') {
        Swal.fire({
            icon: 'error',
            title: 'Proses Diblokir',
            html: `Lot <b>${lotNumber}</b> memiliki status <b>${stats}</b> (TECO/UD).<br>Proses sudah selesai atau ditutup.`,
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        });
    } else if (actionType === 'block_rel_ud') {
        Swal.fire({
            icon: 'warning',
            title: 'Lot Tidak Valid',
            html: `Lot <b>${lotNumber}</b> memiliki status <b>${stats}</b>.<br>Inspection Lot ini tidak bisa dikerjakan dari aplikasi Laravel.`,
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#f59e0b'
        });
    } else if (actionType === 'block_crtd') {
        Swal.fire({
            icon: 'info',
            title: 'Hubungi Admin',
            html: `Lot <b>${lotNumber}</b> memiliki status <b>${stats}</b> (CRTD).<br>Silakan hubungi administrator untuk release status lot.`,
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
        });
    } else {
        Swal.fire({
            icon: 'question',
            title: 'Status Tidak Dikenal',
            text: `Lot ${lotNumber} Status: ${stats}`,
            background: '#1e293b',
            color: '#fff'
        });
    }
};

const filteredLots = computed(() => {
    if (!searchQuery.value) return props.initialLots;
    const lowerSearch = searchQuery.value.toLowerCase();
    return props.initialLots.filter(lot => 
        (lot.PRUEFLOS && lot.PRUEFLOS.toLowerCase().includes(lowerSearch)) ||
        (lot.MATNR && lot.MATNR.toLowerCase().includes(lowerSearch)) ||
        (lot.KTEXTMAT && lot.KTEXTMAT.toLowerCase().includes(lowerSearch)) ||
        (lot.CHARG && lot.CHARG.toLowerCase().includes(lowerSearch)) ||
        (lot.AUFNR && lot.AUFNR.toLowerCase().includes(lowerSearch))
    );
});

const isAllSelected = computed(() => filteredLots.value.length > 0 && selectedLots.value.length === filteredLots.value.length);
const isIndeterminate = computed(() => selectedLots.value.length > 0 && selectedLots.value.length < filteredLots.value.length);

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({ only: ['initialLots', 'errorMessage'], onFinish: () => { isRefreshing.value = false; } });
};

const formatDate = (dateStr) => {
    if(!dateStr) return '-';
    return `${dateStr.substring(6,8)}/${dateStr.substring(4,6)}/${dateStr.substring(0,4)}`;
};

const removeLeadingZeros = (str) => {
    if (!str) return '';
    return parseInt(str, 10).toString();
};

const toggleSelection = (id) => {
    if (selectedLots.value.includes(id)) {
        selectedLots.value = selectedLots.value.filter(lotId => lotId !== id);
    } else {
        selectedLots.value.push(id);
    }
};

const toggleSelectAll = () => selectedLots.value = isAllSelected.value ? [] : filteredLots.value.map(lot => lot.PRUEFLOS);
const clearSelection = () => selectedLots.value = [];

// Helper Cookie Token
const getXsrfToken = () => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; XSRF-TOKEN=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    return null;
};

// --- 4. LOGIKA UTAMA: BULK PASS ---
const bulkPass = async () => {
    if (selectedLots.value.length === 0) return;

    // A. Ambil Data Lengkap Lot yang dipilih
    const fullLotsData = props.initialLots.filter(lot => selectedLots.value.includes(lot.PRUEFLOS));

    // B. VALIDASI KETAT SEBELUM POPUP KONFIRMASI
    // Kita loop satu per satu. Jika ketemu 1 saja yang invalid, batalkan proses.
    for (const lot of fullLotsData) {
        const config = getStatusConfig(lot.STATS);

        if (config.action !== 'allow') {
            // Jika status BUKAN 'allow', panggil alert error dan HENTIKAN eksekusi
            showStatusAlert(lot.PRUEFLOS, lot.STATS, config.action);
            return; // <--- INI KUNCINYA: Stop di sini, jangan lanjut ke confirm popup
        }
    }

    // C. Jika Lolos Validasi (Semua Hijau), Baru Tampilkan Konfirmasi
    const result = await Swal.fire({
        title: 'Konfirmasi Usage Decision',
        html: `
            <p class="text-sm text-slate-300">Anda akan memproses <b>${selectedLots.value.length} lot</b> inspeksi.</p>
            <p class="text-xs text-slate-400 mt-2">Stok akan otomatis posting ke <i>Unrestricted Use</i>.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#ef4444', 
        confirmButtonText: 'Ya, Submit UD!',
        cancelButtonText: 'Batal',
        background: '#1e293b',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-white/10 rounded-2xl shadow-2xl',
            title: 'text-xl font-bold text-white'
        }
    });

    if (!result.isConfirmed) return;

    // D. Proses Eksekusi ke Server (Streaming)
    isProcessingBulk.value = true;
    isSyncing.value = false;
    showProgressModal.value = true;
    progressLogs.value = [];
    progressStats.value = { success: 0, fail: 0, total: selectedLots.value.length };

    try {
        const xsrfToken = getXsrfToken();
        if (!xsrfToken) { window.location.reload(); return; }

        const response = await fetch('/inspection/bulk-pass', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': xsrfToken
            },
            body: JSON.stringify({
                lots: fullLotsData,
                plant: props.plantCode
            })
        });

        if (response.status === 419) throw new Error("Sesi Kadaluarsa. Silakan refresh halaman.");
        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
        
        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");
        let buffer = "";

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split("\n");
            buffer = lines.pop();

            for (const line of lines) {
                if (!line.trim()) continue;
                try {
                    const data = JSON.parse(line);
                    if (data.status === 'DONE') {
                        isSyncing.value = true;
                        router.reload({
                            only: ['initialLots'],
                            onFinish: () => {
                                isProcessingBulk.value = false;
                                isSyncing.value = false;
                                showProgressModal.value = false;
                                clearSelection();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai!',
                                    text: 'Seluruh proses UD berhasil dicatat.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    background: '#1e293b',
                                    color: '#fff'
                                });
                            }
                        });
                    } else {
                        progressLogs.value.push(data);
                        if (data.status === 'SUCCESS') progressStats.value.success++;
                        else progressStats.value.fail++;
                        nextTick(() => {
                            if (logContainerRef.value) {
                                logContainerRef.value.scrollTop = logContainerRef.value.scrollHeight;
                            }
                        });
                    }
                } catch (e) { 
                    console.error("Parse Error:", e); 
                }
            }
        }
    } catch (error) {
        progressLogs.value.push({ 
            lot: 'SYSTEM', 
            status: 'ERROR', 
            message: `Connection Failed: ${error.message}` 
        });
        isProcessingBulk.value = false;
    }
};

const closeProgressModal = () => {
    if(!isProcessingBulk.value && !isSyncing.value) {
        showProgressModal.value = false;
    }
};

const openComponentModal = async (lot) => {
    selectedLotNumber.value = lot.PRUEFLOS;
    selectedOrderNumber.value = lot.AUFNR;
    selectedComponents.value = [];
    isLoadingComponents.value = true;
    showModal.value = true;
    if (!lot.AUFNR) { isLoadingComponents.value = false; return; }
    try {
        const response = await axios.get(`/inspection/components/${lot.AUFNR}`);
        selectedComponents.value = response.data.data;
    } catch (e) { console.error(e); } 
    finally { isLoadingComponents.value = false; }
};

const closeModal = () => { showModal.value = false; setTimeout(() => selectedComponents.value = [], 300); };
</script>

<template>
    <Head title="Inspection List" />

    <div class="relative h-[100dvh] w-full bg-[#0B1120] font-sans text-slate-200 flex flex-col overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#111827] to-[#064e3b] opacity-80"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] bg-emerald-500/10 rounded-full blur-[80px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-indigo-500/10 rounded-full blur-[60px] animate-pulse delay-1000"></div>
            <div class="absolute inset-0 grid-pattern opacity-20"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-50 shrink-0 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-white/5 h-16 flex items-center justify-between px-4 md:px-8">
            <div class="flex items-center gap-3">
                <img src="/images/KMI.png" alt="KMI Logo" class="h-10 w-auto drop-shadow-[0_2px_8px_rgba(16,185,129,0.3)]" />
                <div class="flex flex-col">
                    <h3 class="text-white font-bold text-sm md:text-base leading-none tracking-tight">KMI Inspection</h3>
                    <span class="text-emerald-500 text-[0.6rem] font-bold uppercase tracking-widest mt-0.5">Quality Control System</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="hidden flex-col items-end mr-2 md:flex">
                    <span class="text-[0.65rem] text-slate-400 uppercase font-bold tracking-wider">Operator</span>
                    <span class="text-sm font-bold text-emerald-400 leading-none">{{ props.authUser.username }}</span>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-white/10 flex items-center justify-center text-xs font-bold text-white shadow-inner">
                    {{ props.authUser.username.charAt(0) }}
                </div>
            </div>
        </nav>

        <!-- Header Tools -->
        <div class="relative z-40 shrink-0 bg-[#0f172a]/95 backdrop-blur-md border-b border-white/5 shadow-xl">
            <div class="max-w-[1400px] mx-auto px-4 py-4 md:px-8 md:py-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-3">
                        <Link href="/dashboard" class="w-8 h-8 rounded-full bg-white/5 hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400 flex items-center justify-center transition-all">
                            <i class="fa-solid fa-arrow-left"></i>
                        </Link>
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-white leading-none">Inspection List</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[0.65rem] font-mono text-slate-500">MRP:</span>
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">{{ props.dispoCode }}</span>
                                <span class="text-[0.65rem] font-mono text-slate-500 ml-2">PLANT:</span>
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">{{ props.plantCode }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <button @click="refreshData" :disabled="isRefreshing" class="w-9 h-9 md:w-auto md:h-auto md:px-4 md:py-2 rounded-xl bg-slate-800 border border-white/10 text-white flex items-center justify-center gap-2 hover:bg-slate-700 transition-all active:scale-95">
                        <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isRefreshing }"></i>
                        <span class="hidden md:inline text-sm font-semibold">{{ isRefreshing ? 'Syncing...' : 'Refresh' }}</span>
                    </button>
                </div>

                <div class="flex gap-3">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-500 group-focus-within:text-emerald-500 transition-colors"></i>
                        </div>
                        <input v-model="searchQuery" type="text" placeholder="Search Lot, Material, Batch..." class="block w-full pl-10 pr-3 py-2.5 bg-black/20 border border-white/10 rounded-xl leading-5 text-slate-300 placeholder-slate-500 focus:outline-none focus:bg-black/40 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 sm:text-sm transition-all shadow-inner">
                    </div>
                    <button @click="toggleSelectAll" class="md:hidden px-3 rounded-xl border border-white/10 bg-white/5 text-slate-300 active:bg-emerald-500/20 active:border-emerald-500/50 active:text-emerald-400 transition-colors flex items-center justify-center min-w-[3rem]">
                        <i :class="isAllSelected ? 'fa-solid fa-check-square text-emerald-500' : 'fa-regular fa-square'"></i>
                    </button>
                </div>

                <div class="flex justify-between items-center mt-3 text-[0.7rem] font-medium text-slate-400 uppercase tracking-wider">
                    <span>Total Data: <b class="text-white">{{ filteredLots.length }}</b></span>
                    <span v-if="selectedLots.length > 0" class="text-emerald-400 animate-pulse">{{ selectedLots.length }} Selected</span>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto relative z-10 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-slate-700" id="scrollContainer">
            <div class="max-w-[1400px] mx-auto px-4 py-4 md:px-8 pb-32"> 

                <div v-if="filteredLots.length === 0" class="flex flex-col items-center justify-center h-64 text-slate-500">
                    <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <p class="text-sm">No inspection lots found.</p>
                </div>

                <div v-else>
                    <!-- Mobile View -->
                    <div class="md:hidden space-y-3 mt-4">
                        <div v-for="(lot, index) in filteredLots" :key="lot.PRUEFLOS" class="relative bg-[#162032] rounded-2xl p-4 border border-white/5 shadow-lg active:scale-[0.99] transition-all duration-200" :class="{'ring-1 ring-emerald-500 bg-emerald-900/10': selectedLots.includes(lot.PRUEFLOS)}">
                            <div class="absolute top-0 right-0 p-4 z-10" @click.stop="toggleSelection(lot.PRUEFLOS)">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors bg-[#0f172a]" :class="selectedLots.includes(lot.PRUEFLOS) ? 'border-emerald-500 bg-emerald-500 text-black' : 'border-slate-600 text-transparent'">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                            </div>

                            <div class="pr-8 mb-3">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-lg font-bold text-white tracking-wide">{{ lot.PRUEFLOS }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[0.6rem] font-bold" :class="getStatusConfig(lot.STATS).class">
                                        {{ lot.STATS || 'N/A' }}
                                    </span>
                                </div>

                                <div class="bg-black/20 rounded-lg p-2.5 mb-3 border border-white/5 grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[0.6rem] text-slate-500 uppercase font-bold tracking-wider">Sales Order</div>
                                        <div class="text-sm font-mono text-indigo-300 font-bold">
                                            {{ lot.KDAUF || '-' }} 
                                            <span v-if="lot.KDPOS" class="text-slate-400 text-xs font-normal">/ {{ removeLeadingZeros(lot.KDPOS) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[0.6rem] text-slate-500 uppercase font-bold tracking-wider">Production Ord</div>
                                        <div class="text-sm font-mono text-slate-300">{{ lot.AUFNR }}</div>
                                    </div>
                                    <div class="col-span-2 border-t border-white/5 pt-2">
                                        <div class="text-[0.6rem] text-slate-500 uppercase font-bold tracking-wider">Buyer / PO</div>
                                        <div class="text-sm font-bold text-white truncate">{{ lot.NAME1 || '-' }}</div>
                                        <div class="text-xs font-mono text-emerald-400 mt-0.5 flex items-center gap-1">
                                            <i class="fa-solid fa-file-invoice text-[0.6rem]"></i> {{ lot.BSTNK || '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm font-semibold text-slate-100 line-clamp-2 leading-snug">{{ lot.KTEXTMAT }}</p>
                                    <div class="mt-2 flex items-center gap-2 overflow-x-auto no-scrollbar">
                                        <span class="px-2 py-1 rounded-md bg-white/5 border border-white/5 text-[0.65rem] text-slate-300 whitespace-nowrap font-mono">{{ lot.MATNR }}</span>
                                        <span class="px-2 py-1 rounded-md bg-sky-500/10 border border-sky-500/10 text-[0.65rem] text-sky-400 whitespace-nowrap font-mono flex items-center gap-1">
                                            <i class="fa-solid fa-box text-[0.6rem]"></i> {{ lot.CHARG }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                                        <div class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> {{ formatDate(lot.ENSTEHDAT) }}</div>
                                        <div class="font-bold text-white bg-slate-800 px-2 py-0.5 rounded border border-white/5">{{ parseInt(lot.LOSMENGE) }} <span class="text-[0.6rem] font-normal text-slate-500">{{ lot.MENGENEINH === 'ST' ? 'PC' : lot.MENGENEINH }}</span></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-[3rem_1fr] gap-2">
                                    <button @click.stop="openComponentModal(lot)" class="h-10 rounded-xl bg-[#1e293b] border border-white/5 flex items-center justify-center text-indigo-400 hover:bg-indigo-500 hover:text-white transition-colors"><i class="fa-solid fa-boxes-stacked"></i></button>
                                    <button @click.stop="handleInspect(lot)" class="h-10 rounded-xl bg-emerald-600 flex items-center justify-center gap-2 text-white font-bold text-sm shadow-[0_4px_20px_rgba(16,185,129,0.3)] active:translate-y-0.5 transition-all w-full hover:bg-emerald-500">
                                        <span>Inspect</span><i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop View -->
                    <div class="hidden md:block mt-6">
                        <div class="bg-[#162032]/50 rounded-2xl border border-white/5 pb-2">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 w-12 text-center rounded-tl-2xl border-b border-white/10 shadow-sm">
                                            <input type="checkbox" :checked="isAllSelected" :indeterminate="isIndeterminate" @change="toggleSelectAll" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                                        </th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Inspection Number</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Status</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">SO / Item</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Buyer / PO</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Components</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Material</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Batch</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Qty</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider rounded-tr-2xl border-b border-white/10 shadow-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-sm">
                                    <tr v-for="(lot, index) in filteredLots" :key="lot.PRUEFLOS" class="hover:bg-white/5 transition-colors group relative" :class="{'bg-emerald-500/5': selectedLots.includes(lot.PRUEFLOS)}">
                                        <td class="py-4 px-4 text-center">
                                            <input type="checkbox" v-model="selectedLots" :value="lot.PRUEFLOS" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-bold text-white">{{ lot.PRUEFLOS }}</div>
                                            <div class="text-xs text-slate-500 font-mono mt-0.5" title="Production Order">{{ lot.AUFNR }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="px-2 py-1 rounded text-[0.65rem] font-bold uppercase tracking-wider whitespace-nowrap" :class="getStatusConfig(lot.STATS).class">
                                                {{ lot.STATS || '-' }}
                                            </span>
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-mono text-indigo-300 font-bold text-sm">{{ lot.KDAUF || '-' }}</div>
                                            <div v-if="lot.KDPOS" class="text-xs text-slate-500 mt-0.5">
                                                Item: <span class="text-slate-300 font-mono">{{ removeLeadingZeros(lot.KDPOS) }}</span>
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 max-w-[180px]">
                                            <div class="text-white font-bold truncate text-xs" :title="lot.NAME1">{{ lot.NAME1 || '-' }}</div>
                                            <div class="text-[0.65rem] text-emerald-400 font-mono mt-0.5 truncate flex items-center gap-1" :title="lot.BSTNK">
                                                <i class="fa-solid fa-file-invoice opacity-50"></i> {{ lot.BSTNK || '-' }}
                                            </div>
                                        </td>

                                        <td class="py-4 px-4">
                                            <button @click="openComponentModal(lot)" class="px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-500 hover:text-white text-xs font-bold transition-all flex items-center gap-2">
                                                <i class="fa-solid fa-boxes-stacked"></i> View
                                            </button>
                                        </td>
                                        <td class="py-4 px-4 max-w-[250px]">
                                            <div class="text-slate-200 font-medium truncate" :title="lot.KTEXTMAT">{{ lot.KTEXTMAT }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ lot.MATNR }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="px-2 py-1 rounded bg-sky-500/10 text-sky-400 text-xs font-mono border border-sky-500/10">{{ lot.CHARG }}</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="text-white font-bold">{{ parseInt(lot.LOSMENGE) }}</span> 
                                            <span class="text-xs text-slate-500 ml-1">{{ lot.MENGENEINH === 'ST' ? 'PC' : lot.MENGENEINH }}</span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <div @click.stop class="inline-block">
                                                <button @click="handleInspect(lot)" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all hover:pr-5 group/btn cursor-pointer">
                                                    <span>Inspect</span> <i class="fa-solid fa-arrow-right opacity-0 group-hover/btn:opacity-100 -translate-x-2 group-hover/btn:translate-x-0 transition-all"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Selection Bar -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-full opacity-0">
            <div v-if="selectedLots.length > 0" class="absolute bottom-6 left-0 right-0 z-50 flex justify-center px-4">
                <div class="bg-[#1e293b]/90 backdrop-blur-xl border border-emerald-500/30 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.5)] py-2 pl-4 pr-2 flex items-center gap-4 animate-bounce-subtle">
                    <div class="flex items-center gap-3 border-r border-white/10 pr-4">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-black flex items-center justify-center text-xs font-bold">{{ selectedLots.length }}</span>
                        <span class="text-sm font-medium text-white hidden md:inline">Selected</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="clearSelection" class="w-8 h-8 rounded-full hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button @click="bulkPass" class="px-5 py-2 rounded-full bg-emerald-600 text-white text-sm font-bold shadow-lg hover:bg-emerald-500 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Submit UD
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Components Modal & Progress Modal (Tidak berubah, hanya styling progress bar tetap dipertahankan) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-full md:translate-y-10 md:scale-95" enter-to-class="opacity-100 translate-y-0 md:scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 md:scale-100" leave-to-class="opacity-0 translate-y-full md:translate-y-10 md:scale-95">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
                <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
                <div class="relative w-full md:max-w-2xl bg-[#0f172a] border-t md:border border-white/10 rounded-t-3xl md:rounded-2xl shadow-2xl flex flex-col max-h-[85dvh] overflow-hidden">
                    <div class="md:hidden w-full flex justify-center pt-3 pb-1">
                        <div class="w-12 h-1.5 rounded-full bg-white/20"></div>
                    </div>
                    <div class="px-6 py-4 border-b border-white/5 bg-white/[0.02] flex justify-between items-center shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                <i class="fa-solid fa-boxes-stacked text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white leading-none">Order Components</h3>
                                <p class="text-xs text-slate-500 mt-1 font-mono">Lot: {{ selectedLotNumber }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="w-8 h-8 rounded-full bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 bg-[#0B1120] scrollbar-thin">
                        <div v-if="isLoadingComponents" class="flex flex-col items-center justify-center py-10 text-emerald-500/50 animate-pulse">
                            <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-widest">Loading SAP Data...</span>
                        </div>
                        <div v-else-if="selectedComponents.length === 0" class="py-10 text-center text-slate-500">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 opacity-50"></i>
                            <p class="text-sm">No components found.</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="(comp, i) in selectedComponents" :key="i" class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="mt-0.5 w-6 h-6 rounded bg-[#0f172a] border border-white/10 flex items-center justify-center text-[0.65rem] font-mono text-slate-400 shrink-0">{{ comp.RSPOS }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-sm font-bold text-white leading-snug">{{ comp.MAKTX }}</h4>
                                        <div class="text-right shrink-0 ml-2">
                                            <div class="text-sm font-bold text-emerald-400">{{ parseFloat(comp.BDMNG) }}</div>
                                            <div class="text-[0.6rem] font-bold text-slate-500 uppercase">{{ comp.MEINS === 'ST' ? 'PC' : comp.MEINS }}</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="px-1.5 py-0.5 rounded bg-black/40 border border-white/5 text-[0.6rem] font-mono text-slate-400">{{ comp.MATNR }}</span>
                                        <span v-if="comp.CHARGX2" class="px-1.5 py-0.5 rounded bg-sky-900/20 border border-sky-500/20 text-[0.6rem] font-mono text-sky-400">Batch: {{ comp.CHARGX2 }}</span>
                                        <span v-if="comp.inspector_details" class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/20 text-[0.6rem] text-indigo-300">
                                            <i class="fa-solid fa-user-clock text-[0.55rem]"></i> {{ comp.inspector_details.nama }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Progress Modal (Sama) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showProgressModal" class="fixed inset-0 z-[120] flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
                
                <div class="relative w-full max-w-lg bg-[#0f172a] border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] ring-1 ring-white/10">
                    <div class="p-4 border-b border-white/10 bg-white/5 flex justify-between items-center">
                        <div>
                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                <span v-if="isProcessingBulk" class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <i v-else class="fa-solid fa-check-circle text-emerald-500"></i>
                                {{ isProcessingBulk ? 'Executing Batch...' : 'Execution Finished' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-1 font-mono">
                                PROCESSED: <span class="text-white font-bold">{{ progressStats.success + progressStats.fail }}</span> / {{ progressStats.total }}
                            </p>
                        </div>
                        <div class="text-3xl font-black text-white/5 tracking-tighter">
                            {{ Math.round(((progressStats.success + progressStats.fail) / progressStats.total) * 100) }}%
                        </div>
                    </div>

                    <div class="h-1.5 w-full bg-slate-900/50">
                        <div class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.5)] transition-all duration-300 ease-out" 
                             :style="{ width: `${((progressStats.success + progressStats.fail) / progressStats.total) * 100}%` }">
                        </div>
                    </div>

                    <div ref="logContainerRef" class="flex-1 overflow-y-auto p-4 space-y-2 font-mono text-sm bg-[#0B1120] scroll-smooth">
                        <div v-if="progressLogs.length === 0" class="flex flex-col items-center justify-center h-32 text-slate-600 space-y-2">
                            <i class="fa-solid fa-terminal text-2xl animate-pulse"></i>
                            <span class="text-xs italic">Initializing secure connection to SAP...</span>
                        </div>

                        <div v-for="(log, idx) in progressLogs" :key="idx" 
                             class="flex items-start gap-3 p-2 rounded border border-l-4 transition-all animate-in fade-in slide-in-from-bottom-2 duration-300"
                             :class="log.status === 'SUCCESS' ? 'bg-emerald-950/20 border-white/5 border-l-emerald-500' : 'bg-red-950/20 border-white/5 border-l-red-500'">
                            
                            <div class="mt-0.5 shrink-0">
                                <i v-if="log.status === 'SUCCESS'" class="fa-solid fa-check text-emerald-500"></i>
                                <i v-else class="fa-solid fa-triangle-exclamation text-red-500"></i>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-bold text-slate-200 tracking-wide">{{ log.lot }}</span>
                                    <span :class="log.status === 'SUCCESS' ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10'" class="text-[0.6rem] font-bold px-1.5 py-0.5 rounded border border-white/5">
                                        {{ log.status }}
                                    </span>
                                </div>
                                <p class="text-xs break-all leading-relaxed" :class="log.status === 'SUCCESS' ? 'text-slate-400' : 'text-red-400'">
                                    <span class="text-slate-600 select-none">> </span> {{ log.message }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-white/5 bg-white/5 flex justify-between items-center">
                        <div class="text-xs text-slate-500">
                           <span v-if="isProcessingBulk && !isSyncing">
                               <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Executing transactions...
                           </span>
                           <span v-else-if="isSyncing" class="text-indigo-400">
                               <i class="fa-solid fa-arrows-rotate fa-spin mr-1"></i> Syncing latest data...
                           </span>
                           <span v-else class="text-emerald-500">
                               <i class="fa-solid fa-circle-check mr-1"></i> All Done.
                           </span>
                        </div>
                        
                        <button @click="closeProgressModal" 
                                :disabled="isProcessingBulk || isSyncing" 
                                class="px-6 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                                :class="(isProcessingBulk || isSyncing) ? 'bg-slate-800 text-slate-600 cursor-not-allowed border border-white/5' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg'">
                            
                            <span v-if="isSyncing">Syncing...</span>
                            <span v-else-if="isProcessingBulk">Processing...</span>
                            <span v-else>Close</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.grid-pattern {
    background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), 
                      linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 30px 30px;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-track-transparent::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thumb-slate-700::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
.animate-bounce-subtle { animation: bounce-subtle 2s infinite; }
@keyframes bounce-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5%); }
}
</style>
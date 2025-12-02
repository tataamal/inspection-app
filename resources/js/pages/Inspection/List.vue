<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    initialLots: Array,
    dispoCode: String,
    plantCode: String,
    authUser: Object,
    errorMessage: String
});

// --- STATE VARIABLES ---
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

// --- MINIMALIST LOADER STATE ---
const isPageLoading = ref(false);
const loadingMessage = ref('');

// --- 1. CONFIG STATUS & DATE LOCK LOGIC ---
const isMonthlyLocked = computed(() => {
    const today = new Date();
    const date = today.getDate();
    const hour = today.getHours();
    // Locked hanya jika Tanggal 1 DAN Jam < 10 pagi
    return date === 1 && hour < 10;
});

const showLockAlert = () => {
    Swal.fire({
        icon: 'warning',
        title: 'Periode Restricted',
        html: `
            <p>Transaksi dibatasi pada setiap tanggal 1 (Awal Bulan).</p>
            <p class="mt-2 text-sm text-slate-300">Sistem akan dibuka kembali pukul <b>10:00</b>.</p>
        `,
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#f59e0b'
    });
};

const getStatusConfig = (stats) => {
    if (!stats) return { class: 'bg-slate-500/20 text-slate-400', label: 'N/A', action: 'unknown' };
    
    const s = stats.trim();
    if (s.includes('REL') && s.includes('REL')) {
        return { class: 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20', label: s, action: 'allow' };
    }
    if (s.includes('TECO') || (s.includes('UD') && !s.includes('REL'))) { 
        return { class: 'bg-rose-500/20 text-rose-400 border border-rose-500/20', label: s, action: 'block_teco' };
    }
    if (s.includes('REL') && s.includes('UD')) {
        return { class: 'bg-amber-500/20 text-amber-400 border border-amber-500/20', label: s, action: 'block_rel_ud' };
    }
    if (s.includes('CRTD')) {
        return { class: 'bg-blue-500/20 text-blue-400 border border-blue-500/20', label: s, action: 'block_crtd' };
    }
    return { class: 'bg-slate-500/20 text-slate-400', label: s, action: 'unknown' };
};

// --- 2. HANDLER TOMBOL INSPECT ---
const handleInspect = (lot) => {
    // Cek Lock Tanggal 1
    if (isMonthlyLocked.value) {
        showLockAlert();
        return;
    }

    const config = getStatusConfig(lot.STATS);
    
    if (config.action === 'allow') {
        // Set pesan loading spesifik sebelum pindah halaman
        loadingMessage.value = `ACCESSING LOT ${lot.PRUEFLOS}...`;
        router.visit(`/inspection/form/${lot.PRUEFLOS}?plant=${props.plantCode}&dispo=${props.dispoCode}`);
    } else {
        showStatusAlert(lot.PRUEFLOS, lot.STATS, config.action);
    }
};

const showStatusAlert = (lotNumber, stats, actionType) => {
    // ... logic alert sama seperti sebelumnya ...
    if (actionType === 'block_teco') {
        Swal.fire({ icon: 'error', title: 'Proses Diblokir', html: `Lot <b>${lotNumber}</b> status <b>${stats}</b> (TECO/UD).`, background: '#1e293b', color: '#fff' });
    } else if (actionType === 'block_rel_ud') {
        Swal.fire({ icon: 'warning', title: 'Lot Tidak Valid', html: `Lot <b>${lotNumber}</b> status <b>${stats}</b>.`, background: '#1e293b', color: '#fff' });
    } else if (actionType === 'block_crtd') {
        Swal.fire({ icon: 'info', title: 'Hubungi Admin', html: `Lot <b>${lotNumber}</b> status <b>${stats}</b> (CRTD).`, background: '#1e293b', color: '#fff' });
    } else {
        Swal.fire({ icon: 'question', title: 'Status Tidak Dikenal', text: `Lot ${lotNumber} Status: ${stats}`, background: '#1e293b', color: '#fff' });
    }
};

// --- 3. FILTERING & UTILS ---
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

const removeLeadingZeros = (str) => parseInt(str, 10).toString();

const toggleSelection = (id) => {
    if (selectedLots.value.includes(id)) {
        selectedLots.value = selectedLots.value.filter(lotId => lotId !== id);
    } else {
        selectedLots.value.push(id);
    }
};

const toggleSelectAll = () => selectedLots.value = isAllSelected.value ? [] : filteredLots.value.map(lot => lot.PRUEFLOS);
const clearSelection = () => selectedLots.value = [];

// --- 4. BULK PASS LOGIC ---
const getXsrfToken = () => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; XSRF-TOKEN=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    return null;
};

const progressPercentage = computed(() => {
    const { total, success, fail } = progressStats.value;
    if (total === 0) return 0;
    const processed = success + fail;
    return Math.round((processed / total) * 100);
});

const processedCount = computed(() => {
    return progressStats.value.success + progressStats.value.fail;
});

const bulkPass = async () => {
    // 1. Cek Lock Tanggal 1
    if (isMonthlyLocked.value) {
        showLockAlert();
        return;
    }

    // 2. Validasi Seleksi
    if (selectedLots.value.length === 0) return;
    
    // Ambil data lengkap berdasarkan ID yang dipilih
    const fullLotsData = props.initialLots.filter(lot => selectedLots.value.includes(lot.PRUEFLOS));

    // 3. Validasi Status Lot (TECO/UD)
    for (const lot of fullLotsData) {
        const config = getStatusConfig(lot.STATS);
        if (config.action !== 'allow') {
            showStatusAlert(lot.PRUEFLOS, lot.STATS, config.action);
            return;
        }
    }

    // 4. Konfirmasi SweetAlert
    const result = await Swal.fire({
        title: 'Konfirmasi Usage Decision',
        html: `<p class="text-sm text-slate-300">Memproses <b>${selectedLots.value.length} lot</b>.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        confirmButtonText: 'Ya, Submit UD!',
        background: '#1e293b', color: '#f8fafc'
    });

    if (!result.isConfirmed) return;

    // 5. Setup UI State (Buka Modal Hitam)
    isProcessingBulk.value = true;
    isSyncing.value = false;
    showProgressModal.value = true;
    progressLogs.value = [];
    progressStats.value = { success: 0, fail: 0, total: selectedLots.value.length };

    try {
        const xsrfToken = getXsrfToken();
        
        // --- REQUEST KE BACKEND ---
        const response = await fetch('/inspection/bulk-pass', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-XSRF-TOKEN': xsrfToken,
                // Header ini memberi tahu browser/server kita mengharapkan stream
                'Accept': 'application/x-ndjson' 
            },
            body: JSON.stringify({ lots: fullLotsData, plant: props.plantCode })
        });

        // --- STREAM READER LOGIC (INI YANG HILANG SEBELUMNYA) ---
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            // Baca chunk data dari server
            const { done, value } = await reader.read();
            if (done) break;

            // Decode binary ke text dan tambahkan ke buffer
            buffer += decoder.decode(value, { stream: true });
            
            // Pecah berdasarkan baris baru (\n) karena Controller mengirim NDJSON
            const lines = buffer.split('\n');
            
            // Simpan potongan terakhir yang mungkin belum lengkap untuk loop berikutnya
            buffer = lines.pop(); 

            for (const line of lines) {
                if (!line.trim()) continue;
                try {
                    const data = JSON.parse(line);

                    // Cek Sinyal Selesai dari Controller
                    if (data.status === 'DONE') {
                        isProcessingBulk.value = false; // Matikan Loader Spinner
                        
                        // Auto refresh data agar list terupdate
                        refreshData(); 
                    } else {
                        // Tambah Log ke Array (Langsung tampil di Modal)
                        progressLogs.value.push(data);
                        
                        // Update Counter Success/Fail
                        if(data.status === 'SUCCESS') progressStats.value.success++;
                        else progressStats.value.fail++;

                        // Auto Scroll ke bawah modal log
                        await nextTick();
                        if (logContainerRef.value) {
                            logContainerRef.value.scrollTop = logContainerRef.value.scrollHeight;
                        }
                    }
                } catch (err) {
                    console.error("Error parsing JSON stream:", err);
                }
            }
        }

    } catch (e) {
        console.error("Fetch Error:", e);
        progressLogs.value.push({ 
            lot: 'SYSTEM', 
            status: 'ERROR', 
            message: 'Koneksi terputus atau terjadi kesalahan server.' 
        });
        isProcessingBulk.value = false; 
    }
};

const closeProgressModal = () => {
    if(!isProcessingBulk.value && !isSyncing.value) showProgressModal.value = false;
};

const openComponentModal = async (lot) => {
    selectedLotNumber.value = lot.PRUEFLOS;
    selectedOrderNumber.value = lot.AUFNR;
    selectedComponents.value = [];
    isLoadingComponents.value = true;
    showModal.value = true;
    if (!lot.AUFNR) { isLoadingComponents.value = false; return; }
    try {
        // Gunakan axios biasa untuk data ringan, tidak perlu trigger global loader
        const response = await fetch(`/inspection/components/${lot.AUFNR}`); 
        const json = await response.json();
        selectedComponents.value = json.data;
    } catch (e) { console.error(e); } 
    finally { isLoadingComponents.value = false; }
};

const closeModal = () => { showModal.value = false; setTimeout(() => selectedComponents.value = [], 300); };

// --- LIFECYCLE: ULTRA FAST LOADER ---
onMounted(() => {
    let startTimer = null;

    const removeStartListener = router.on('start', () => {
        // Tunda 200ms. Jika respon server < 200ms, loader tidak muncul sama sekali.
        startTimer = setTimeout(() => {
            isPageLoading.value = true;
            loadingMessage.value = 'LOADING...'; 
        }, 200);
    });

    const removeFinishListener = router.on('finish', () => {
        if (startTimer) clearTimeout(startTimer);
        isPageLoading.value = false;
    });

    onUnmounted(() => {
        removeStartListener();
        removeFinishListener();
        if (startTimer) clearTimeout(startTimer);
    });
});
</script>

<template>
    <Head title="Inspection List" />

    <div class="relative h-[100dvh] w-full bg-[#0B1120] font-sans text-slate-200 flex flex-col overflow-hidden">
        
        <!-- Background Effects -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#111827] to-[#064e3b] opacity-80"></div>
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
                    
                    <button @click="refreshData" :disabled="isRefreshing" class="relative overflow-hidden w-9 h-9 md:w-auto md:h-auto md:px-4 md:py-2 rounded-xl border transition-all active:scale-95 group" :class="isRefreshing ? 'bg-emerald-900/20 border-emerald-500/50 text-emerald-400 cursor-wait' : 'bg-slate-800 border-white/10 text-white hover:bg-slate-700'">
                        <div v-if="isRefreshing" class="absolute inset-0 bg-emerald-500/10 w-full h-full animate-pulse"></div>
                        <div class="relative flex items-center justify-center gap-2 z-10">
                            <i class="fa-solid fa-arrows-rotate" :class="{ 'animate-spin': isRefreshing }"></i>
                            <span class="hidden md:inline text-sm font-semibold">{{ isRefreshing ? 'Syncing...' : 'Refresh' }}</span>
                        </div>
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

        <!-- Main Content (List) -->
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
                                        <div class="text-[0.6rem] text-slate-500 uppercase font-bold tracking-wider">Production Ord</div>
                                        <div class="text-sm font-mono text-slate-300">{{ lot.AUFNR }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[0.6rem] text-slate-500 uppercase font-bold tracking-wider">Sales Order</div>
                                        <div class="text-sm font-mono text-indigo-300 font-bold">
                                            {{ lot.KDAUF || '-' }} <span v-if="lot.KDPOS" class="text-slate-400 text-xs font-normal">/ {{ removeLeadingZeros(lot.KDPOS) }}</span>
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
                                    <button @click.stop="handleInspect(lot)" :disabled="isMonthlyLocked" class="h-10 rounded-xl flex items-center justify-center gap-2 text-white font-bold text-sm shadow-[0_4px_20px_rgba(16,185,129,0.3)] transition-all w-full" :class="isMonthlyLocked ? 'bg-slate-700 cursor-not-allowed opacity-50 grayscale' : 'bg-emerald-600 hover:bg-emerald-500 active:translate-y-0.5'">
                                        <span>{{ isMonthlyLocked ? 'Locked until 10:00' : 'Inspect' }}</span>
                                        <i v-if="!isMonthlyLocked" class="fa-solid fa-arrow-right text-xs"></i>
                                        <i v-else class="fa-solid fa-lock text-xs"></i>
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
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Inspection Lot</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">Status</th>
                                        <th class="sticky top-0 z-40 bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10 shadow-sm">SO / Buyer</th>
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
                                        <td class="py-4 px-4 max-w-[180px]">
                                            <div class="font-mono text-indigo-300 font-bold text-xs">{{ lot.KDAUF || '-' }}</div>
                                            <div class="text-[0.65rem] text-slate-500 mt-0.5 truncate" :title="lot.NAME1">{{ lot.NAME1 || '-' }}</div>
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
                                                <button @click="handleInspect(lot)" :disabled="isMonthlyLocked" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-xs font-bold shadow-lg transition-all group/btn" :class="isMonthlyLocked ? 'bg-slate-700 cursor-not-allowed opacity-50 grayscale shadow-none' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/20 hover:pr-5 cursor-pointer'">
                                                    <span>{{ isMonthlyLocked ? 'Locked' : 'Inspect' }}</span> 
                                                    <i v-if="!isMonthlyLocked" class="fa-solid fa-arrow-right opacity-0 group-hover/btn:opacity-100 -translate-x-2 group-hover/btn:translate-x-0 transition-all"></i>
                                                    <i v-else class="fa-solid fa-lock"></i>
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
                <div class="bg-[#1e293b]/90 backdrop-blur-xl border border-emerald-500/30 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.5)] py-2 pl-4 pr-2 flex items-center gap-4">
                    <div class="flex items-center gap-3 border-r border-white/10 pr-4">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-black flex items-center justify-center text-xs font-bold">{{ selectedLots.length }}</span>
                        <span class="text-sm font-medium text-white hidden md:inline">Selected</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="clearSelection" class="w-8 h-8 rounded-full hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button @click="bulkPass" :disabled="isMonthlyLocked" class="px-5 py-2 rounded-full text-white text-sm font-bold shadow-lg transition-all flex items-center gap-2" :class="isMonthlyLocked ? 'bg-slate-600 cursor-not-allowed opacity-70' : 'bg-emerald-600 hover:bg-emerald-500 active:scale-95'">
                            <i :class="isMonthlyLocked ? 'fa-solid fa-lock' : 'fa-solid fa-check-double'"></i> {{ isMonthlyLocked ? 'Locked' : 'Submit UD' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Components Modal -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-full md:translate-y-10 md:scale-95" enter-to-class="opacity-100 translate-y-0 md:scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 md:scale-100" leave-to-class="opacity-0 translate-y-full md:translate-y-10 md:scale-95">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-end md:items-center justify-center">
                <div @click="closeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
                <div class="relative w-full md:max-w-2xl bg-[#0f172a] border-t md:border border-white/10 rounded-t-3xl md:rounded-2xl shadow-2xl flex flex-col max-h-[85dvh] overflow-hidden">
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
                        <div v-if="isLoadingComponents" class="flex flex-col items-center justify-center py-16">
                            <div class="relative w-12 h-12 mb-4">
                                <div class="absolute inset-0 rounded-full border-2 border-t-emerald-500 border-r-emerald-500 border-b-transparent border-l-transparent animate-spin"></div>
                                <div class="absolute inset-2 rounded-full border-2 border-b-indigo-400 border-l-indigo-400 border-t-transparent border-r-transparent animate-[spin_1s_linear_infinite_reverse]"></div>
                            </div>
                            <span class="text-sm font-bold text-emerald-400 tracking-widest animate-pulse">RETRIEVING BOM...</span>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- NEW MINIMALIST LOADER (For Page Navigation) -->
        <!-- 1. Top Progress Bar -->
        <div v-if="isPageLoading" class="fixed top-0 left-0 w-full h-1 z-[9999] overflow-hidden">
            <div class="h-full bg-emerald-400 animate-progress-indeterminate shadow-[0_0_10px_#34d399]"></div>
        </div>

        <!-- 2. Minimalist Status Pill (Right Bottom) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
            <div v-if="isPageLoading" class="fixed bottom-6 right-6 z-[9999] bg-[#0f172a]/90 border border-white/10 px-4 py-2 rounded-full flex items-center gap-3 shadow-2xl backdrop-blur-sm">
                <i class="fa-solid fa-circle-notch fa-spin text-emerald-400 text-sm"></i>
                <span class="text-xs font-bold text-white tracking-wider font-mono uppercase">{{ loadingMessage || 'Processing' }}</span>
            </div>
        </Transition>

        <!-- Progress Modal (For Bulk Actions - Keeping this for detailed logs) -->
        <Transition 
            enter-active-class="transition duration-300 ease-out" 
            enter-from-class="opacity-0 scale-95" 
            enter-to-class="opacity-100 scale-100" 
            leave-active-class="transition duration-200 ease-in" 
            leave-from-class="opacity-100 scale-100" 
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showProgressModal" class="fixed inset-0 z-[120] flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-[#0B1120]/90 backdrop-blur-md"></div>
                
                <div class="relative w-full max-w-lg bg-[#0f172a] border border-white/10 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] ring-1 ring-white/10">
                    
                    <div class="p-6 bg-gradient-to-b from-[#1e293b] to-[#0f172a] border-b border-white/5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="relative z-10">
                            <div class="flex justify-between items-end mb-4">
                                <div>
                                    <h3 class="text-white font-bold text-xl flex items-center gap-2">
                                        <span v-if="isProcessingBulk" class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                        </span>
                                        <i v-else class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                                        
                                        {{ isProcessingBulk ? 'Processing Bulk UD...' : 'Process Completed' }}
                                    </h3>
                                    <p class="text-xs text-slate-400 mt-1 font-mono">
                                        {{ isProcessingBulk ? 'Sending data to SAP & updating local DB...' : 'All tasks finished.' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-3xl font-black text-white tracking-tighter">{{ progressPercentage }}%</span>
                                </div>
                            </div>

                            <div class="relative h-2 w-full bg-slate-700/50 rounded-full overflow-hidden">
                                <div 
                                    class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-600 to-emerald-400 transition-all duration-300 ease-out shadow-[0_0_10px_rgba(16,185,129,0.5)]"
                                    :style="{ width: `${progressPercentage}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 divide-x divide-white/5 border-b border-white/5 bg-[#162032]">
                        <div class="p-4 text-center">
                            <div class="text-[0.65rem] uppercase font-bold text-slate-500 tracking-wider mb-1">Total Lots</div>
                            <div class="text-xl font-bold text-white">{{ progressStats.total }}</div>
                        </div>
                        <div class="p-4 text-center bg-emerald-500/5">
                            <div class="text-[0.65rem] uppercase font-bold text-emerald-500/80 tracking-wider mb-1">Success</div>
                            <div class="text-xl font-bold text-emerald-400">{{ progressStats.success }}</div>
                        </div>
                        <div class="p-4 text-center bg-red-500/5">
                            <div class="text-[0.65rem] uppercase font-bold text-red-500/80 tracking-wider mb-1">Failed</div>
                            <div class="text-xl font-bold text-red-400">{{ progressStats.fail }}</div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto bg-[#0B1120] relative scroll-smooth" ref="logContainerRef">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03]">
                            <i class="fa-solid fa-terminal text-9xl text-white"></i>
                        </div>

                        <div class="p-4 space-y-2 relative z-10 font-mono text-sm">
                            <TransitionGroup name="list">
                                <div 
                                    v-for="(log, idx) in progressLogs" 
                                    :key="idx" 
                                    class="flex items-start gap-3 p-3 rounded-lg border border-l-[3px] transition-all duration-300" 
                                    :class="log.status === 'SUCCESS' 
                                        ? 'bg-emerald-950/10 border-white/5 border-l-emerald-500' 
                                        : 'bg-red-950/10 border-white/5 border-l-red-500'"
                                >
                                    <div class="mt-0.5 shrink-0">
                                        <i :class="log.status === 'SUCCESS' ? 'fa-solid fa-check text-emerald-500' : 'fa-solid fa-xmark text-red-500'"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex justify-between items-center mb-0.5">
                                            <span class="font-bold text-slate-200 tracking-wide">{{ log.lot }}</span>
                                            <span class="text-[0.6rem] px-1.5 py-0.5 rounded bg-white/5 text-slate-500">{{ log.status }}</span>
                                        </div>
                                        <p class="text-xs text-slate-400 leading-relaxed break-words">{{ log.message }}</p>
                                    </div>
                                </div>
                            </TransitionGroup>

                            <div class="h-4"></div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-white/5 bg-[#162032] flex justify-between items-center">
                        <span class="text-xs text-slate-500 animate-pulse" v-if="isProcessingBulk">
                            Processing item {{ processedCount + 1 }} of {{ progressStats.total }}...
                        </span>
                        <span class="text-xs text-emerald-500 font-bold" v-else>
                            <i class="fa-solid fa-thumbs-up mr-1"></i> Job Done
                        </span>

                        <button 
                            @click="closeProgressModal" 
                            :disabled="isProcessingBulk || isSyncing" 
                            class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg transform active:scale-95" 
                            :class="(isProcessingBulk || isSyncing) 
                                ? 'bg-slate-700 text-slate-500 cursor-not-allowed opacity-50' 
                                : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/20'"
                        >
                            {{ isProcessingBulk ? 'Please Wait...' : 'Close Window' }}
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

/* Indeterminate Progress Bar */
@keyframes progress-indeterminate {
  0% { left: -100%; width: 100%; }
  50% { left: 100%; width: 20%; }
  100% { left: -100%; width: 100%; }
}
.animate-progress-indeterminate {
  position: relative;
  animation: progress-indeterminate 1.5s infinite linear;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>
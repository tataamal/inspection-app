<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Button from '@/components/Button.vue';
import Card from '@/components/Card.vue';
import Modal from '@/components/Modal.vue';
import LoadingOverlay from '@/components/LoadingOverlay.vue';

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

// --- OPTIMIZED LOADER STATE ---
// Hanya show loading untuk navigasi jika > 300ms (untuk UX yang cepat)
const isPageLoading = ref(false);
const loadingMessage = ref('');
let navigationTimer = null;

// Loading untuk API calls (langsung show)
const isApiLoading = ref(false);
const apiLoadingMessage = ref('');

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
        // Navigasi cepat - loading hanya muncul jika > 300ms
        loadingMessage.value = `Accessing Lot ${lot.PRUEFLOS}...`;
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
    // API call - langsung show loading
    isApiLoading.value = true;
    apiLoadingMessage.value = 'Fetching inspection data...';
    
    router.reload({ 
        only: ['initialLots', 'errorMessage'], 
        onFinish: () => { 
            isRefreshing.value = false;
            isApiLoading.value = false;
        } 
    });
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

const bulkPass = async () => {
    if (isMonthlyLocked.value) {
        showLockAlert();
        return;
    }

    if (selectedLots.value.length === 0) return;
    const fullLotsData = props.initialLots.filter(lot => selectedLots.value.includes(lot.PRUEFLOS));

    for (const lot of fullLotsData) {
        const config = getStatusConfig(lot.STATS);
        if (config.action !== 'allow') {
            showStatusAlert(lot.PRUEFLOS, lot.STATS, config.action);
            return;
        }
    }

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

    isProcessingBulk.value = true;
    isSyncing.value = false;
    showProgressModal.value = true;
    progressLogs.value = [];
    progressStats.value = { success: 0, fail: 0, total: selectedLots.value.length };

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) throw new Error("CSRF Token missing.");
        
        const response = await fetch('/inspection/bulk-pass', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
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
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Terjadi kesalahan saat memproses bulk UD',
            background: '#1e293b',
            color: '#fff'
        });
    }
};

const closeProgressModal = () => {
    if(!isProcessingBulk.value && !isSyncing.value) showProgressModal.value = false;
};

const openComponentModal = async (lot) => {
    selectedLotNumber.value = lot.PRUEFLOS;
    selectedOrderNumber.value = lot.AUFNR;
    selectedComponents.value = [];
    showModal.value = true;
    
    if (!lot.AUFNR) { 
        isLoadingComponents.value = false; 
        return; 
    }
    
    // API call - langsung show loading
    isLoadingComponents.value = true;
    try {
        const response = await fetch(`/inspection/components/${lot.AUFNR}`); 
        const json = await response.json();
        selectedComponents.value = json.data;
    } catch (e) { 
        console.error(e);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memuat komponen',
            background: '#1e293b',
            color: '#fff'
        });
    } 
    finally { 
        isLoadingComponents.value = false; 
    }
};

const closeModal = () => { showModal.value = false; setTimeout(() => selectedComponents.value = [], 300); };

// --- OPTIMIZED LOADER: Fast Navigation, Real-time API ---
onMounted(() => {
    const removeStartListener = router.on('start', () => {
        // Hanya show loading untuk navigasi jika > 300ms (untuk UX cepat)
        navigationTimer = setTimeout(() => {
            isPageLoading.value = true;
            loadingMessage.value = 'Loading...'; 
        }, 300);
    });

    const removeFinishListener = router.on('finish', () => {
        if (navigationTimer) {
            clearTimeout(navigationTimer);
            navigationTimer = null;
        }
        isPageLoading.value = false;
        isApiLoading.value = false;
    });

    onUnmounted(() => {
        removeStartListener();
        removeFinishListener();
        if (navigationTimer) {
            clearTimeout(navigationTimer);
        }
    });
});
</script>

<template>
    <Head title="Inspection List" />

    <div class="relative min-h-screen w-full bg-slate-900 font-sans text-slate-200 flex flex-col overflow-hidden">
        
        <!-- Background Effects (Design System) -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 shrink-0 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-emerald-500/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] h-16 flex items-center justify-between px-4 sm:px-6 md:px-8">
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
        <div class="relative z-40 shrink-0 bg-white/5 backdrop-blur-md border-b border-white/10 shadow-xl">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 py-4 md:px-8 md:py-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-3">
                        <Link 
                            href="/dashboard" 
                            class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/30 flex items-center justify-center transition-all duration-300"
                        >
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
                    
                    <Button
                        variant="secondary"
                        icon="fa-solid fa-arrows-rotate"
                        :label="isRefreshing ? 'Syncing...' : 'Refresh'"
                        :processing="isRefreshing"
                        size="sm"
                        @click="refreshData"
                    />
                </div>

                <div class="flex gap-3">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-500 group-focus-within:text-emerald-500 transition-colors"></i>
                        </div>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search Lot, Material, Batch..." 
                            class="block w-full pl-10 pr-3 py-2.5 bg-white/10 backdrop-blur-md border border-white/10 rounded-xl leading-5 text-white placeholder-slate-500 focus:outline-none focus:bg-white/10 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 sm:text-sm transition-all"
                        >
                    </div>
                    <Button
                        variant="icon"
                        :icon="isAllSelected ? 'fa-solid fa-check-square text-emerald-500' : 'fa-regular fa-square'"
                        size="sm"
                        @click="toggleSelectAll"
                        class="md:hidden"
                    />
                </div>

                <div class="flex justify-between items-center mt-3 text-[0.7rem] font-medium text-slate-400 uppercase tracking-wider">
                    <span>Total Data: <b class="text-white">{{ filteredLots.length }}</b></span>
                    <span v-if="selectedLots.length > 0" class="text-emerald-400 animate-pulse">{{ selectedLots.length }} Selected</span>
                </div>
            </div>
        </div>

        <!-- API Loading Overlay -->
        <LoadingOverlay
            :show="isApiLoading"
            :message="apiLoadingMessage"
            full-screen
        />

        <!-- Main Content (List) -->
        <div class="flex-1 overflow-y-auto relative z-10 custom-scrollbar" id="scrollContainer">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 py-4 md:px-8 pb-32"> 

                <div v-if="filteredLots.length === 0" class="flex flex-col items-center justify-center h-64 text-slate-500">
                    <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <p class="text-sm">No inspection lots found.</p>
                </div>

                <div v-else>
                    <!-- Mobile View -->
                    <div class="md:hidden space-y-3 mt-4">
                        <Card
                            v-for="(lot, index) in filteredLots"
                            :key="lot.PRUEFLOS"
                            variant="standard"
                            padding="md"
                            :clickable="false"
                            :hover="false"
                            :class="{'ring-1 ring-emerald-500 bg-emerald-900/10': selectedLots.includes(lot.PRUEFLOS)}"
                            class="relative"
                        >
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
                                    <Button
                                        variant="icon"
                                        icon="fa-solid fa-boxes-stacked"
                                        size="sm"
                                        @click.stop="openComponentModal(lot)"
                                    />
                                    <Button
                                        variant="primary"
                                        :label="isMonthlyLocked ? 'Locked until 10:00' : 'Inspect'"
                                        :icon="isMonthlyLocked ? 'fa-solid fa-lock' : 'fa-solid fa-arrow-right'"
                                        :disabled="isMonthlyLocked"
                                        size="sm"
                                        full-width
                                        @click.stop="handleInspect(lot)"
                                    />
                                </div>
                            </Card>
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
                                            <Button
                                                variant="secondary"
                                                icon="fa-solid fa-boxes-stacked"
                                                label="View"
                                                size="sm"
                                                @click="openComponentModal(lot)"
                                            />
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
                                            <Button
                                                variant="primary"
                                                :label="isMonthlyLocked ? 'Locked' : 'Inspect'"
                                                :icon="isMonthlyLocked ? 'fa-solid fa-lock' : 'fa-solid fa-arrow-right'"
                                                :disabled="isMonthlyLocked"
                                                size="sm"
                                                @click="handleInspect(lot)"
                                            />
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
                        <Button
                            variant="icon"
                            icon="fa-solid fa-xmark"
                            size="sm"
                            @click="clearSelection"
                        />
                        <Button
                            variant="primary"
                            :icon="isMonthlyLocked ? 'fa-solid fa-lock' : 'fa-solid fa-check-double'"
                            :label="isMonthlyLocked ? 'Locked' : 'Submit UD'"
                            :disabled="isMonthlyLocked"
                            size="sm"
                            @click="bulkPass"
                        />
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Components Modal -->
        <Modal
            v-model:show="showModal"
            title="Order Components"
            size="lg"
            @close="closeModal"
        >
            <template #header>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                        <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-none">Order Components</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">Lot: {{ selectedLotNumber }}</p>
                    </div>
                </div>
            </template>

            <div class="relative min-h-[200px]">
                <LoadingOverlay
                    :show="isLoadingComponents"
                    message="Retrieving BOM..."
                />
                
                <div v-if="!isLoadingComponents && selectedComponents.length === 0" class="py-10 text-center text-slate-500">
                    <i class="fa-regular fa-folder-open text-3xl mb-2 opacity-50"></i>
                    <p class="text-sm">No components found.</p>
                </div>
                
                <div v-else-if="!isLoadingComponents" class="space-y-3">
                    <Card
                        v-for="(comp, i) in selectedComponents"
                        :key="i"
                        padding="sm"
                        class="flex items-start gap-3"
                    >
                        <div class="mt-0.5 w-6 h-6 rounded bg-[#0f172a] border border-white/10 flex items-center justify-center text-[0.65rem] font-mono text-slate-400 shrink-0">
                            {{ comp.RSPOS }}
                        </div>
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
                    </Card>
                </div>
            </div>
        </Modal>

        <!-- Minimalist Navigation Loader (Only shows if > 300ms) -->
        <Transition name="fade">
            <div v-if="isPageLoading" class="fixed top-0 left-0 w-full h-1 z-[9999] overflow-hidden">
                <div class="h-full bg-emerald-400 animate-progress-indeterminate shadow-[0_0_10px_#34d399]"></div>
            </div>
        </Transition>

        <!-- Progress Modal (For Bulk Actions - Keeping this for detailed logs) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showProgressModal" class="fixed inset-0 z-[120] flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
                <div class="relative w-full max-w-lg bg-[#0f172a] border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] ring-1 ring-white/10">
                    <div class="p-4 border-b border-white/10 bg-white/5 flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg flex items-center gap-2">
                            <i v-if="isProcessingBulk" class="fa-solid fa-circle-notch fa-spin text-emerald-500"></i>
                            <i v-else class="fa-solid fa-check-circle text-emerald-500"></i>
                            {{ isProcessingBulk ? 'Processing Bulk UD...' : 'Execution Finished' }}
                        </h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-2 font-mono text-sm bg-[#0B1120] scroll-smooth" ref="logContainerRef">
                        <div v-for="(log, idx) in progressLogs" :key="idx" class="flex items-start gap-3 p-2 rounded border border-l-4 transition-all" :class="log.status === 'SUCCESS' ? 'bg-emerald-950/20 border-white/5 border-l-emerald-500' : 'bg-red-950/20 border-white/5 border-l-red-500'">
                             <div class="mt-0.5 shrink-0"><i :class="log.status === 'SUCCESS' ? 'fa-solid fa-check text-emerald-500' : 'fa-solid fa-triangle-exclamation text-red-500'"></i></div>
                             <div>
                                 <div class="flex justify-between items-center mb-0.5"><span class="font-bold text-slate-200">{{ log.lot }}</span></div>
                                 <p class="text-xs text-slate-400">{{ log.message }}</p>
                             </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-white/5 bg-white/5 flex justify-end">
                        <Button
                            variant="primary"
                            label="Close"
                            :disabled="isProcessingBulk || isSyncing"
                            size="sm"
                            @click="closeProgressModal"
                        />
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* Grid Pattern (Design System) */
.grid-pattern {
    background-image: linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px), 
                      linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: gridMove 20s linear infinite;
}

@keyframes gridMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

@keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(30px, -30px); }
}

.animate-float-1 {
    animation: float 20s ease-in-out infinite;
}

.animate-float-2 {
    animation: float 20s ease-in-out infinite 5s;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
/* Custom Scrollbar (Design System) */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.3);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.5);
}

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

/* Fade Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

// 1. Props
const props = defineProps({
    initialLots: Array,
    dispoCode: String,
    plantCode: String,
    authUser: Object,
    errorMessage: String
});

// 2. State
const searchQuery = ref('');
const isRefreshing = ref(false);

// 3. Filtering
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

// 4. Actions
const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['initialLots', 'errorMessage'],
        onFinish: () => { isRefreshing.value = false; }
    });
};

const formatDate = (dateStr) => {
    if(!dateStr) return '-';
    const y = dateStr.substring(0,4);
    const m = dateStr.substring(4,6);
    const d = dateStr.substring(6,8);
    return `${d}/${m}/${y}`;
};

const processLot = (lot) => {
    router.get(`/inspection/form/${lot.PRUEFLOS}`, {
        plant: props.plantCode,
        dispo: props.dispoCode 
    });
};
</script>

<template>
    <Head title="Daftar Inspeksi" />

    <div class="relative min-h-screen bg-slate-900 font-sans text-slate-200 overflow-hidden selection:bg-emerald-500 selection:text-white flex flex-col">
        
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <nav class="sticky top-0 z-50 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-emerald-500/10 shadow-md">
            <div class="max-w-[1400px] mx-auto px-6 py-3 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img src="/images/KMI.png" alt="Logo" class="h-8 w-auto" />
                    <div class="flex flex-col">
                        <h3 class="text-white font-bold text-base leading-tight">KMI Inspection</h3>
                        <span class="text-emerald-500 text-[0.6rem] font-bold uppercase tracking-widest">Quality Control</span>
                    </div>
                </div>
                <div class="text-sm font-medium text-slate-400">
                    Hello, <span class="text-emerald-400">{{ props.authUser.username }}</span>
                </div>
            </div>
        </nav>

        <main class="relative z-10 flex-1 flex flex-col max-w-[1400px] w-full mx-auto p-4 md:p-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 shrink-0 gap-4">
                <div class="space-y-3">
                    <Link href="/dashboard" class="group inline-flex items-center gap-2 text-slate-400 hover:text-emerald-400 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center group-hover:bg-emerald-500/20 group-hover:-translate-x-1 transition-all">
                            <i class="fa-solid fa-arrow-left text-sm"></i>
                        </div>
                        <span class="font-medium text-sm">Kembali ke Dashboard</span>
                    </Link>
                    
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Daftar Inspeksi</h1>
                        <div class="inline-flex items-center gap-2 mt-2 px-3 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                            <i class="fa-solid fa-layer-group"></i>
                            MRP Controller: {{ props.dispoCode }}
                        </div>
                    </div>
                </div>

                <button 
                    @click="refreshData" 
                    :disabled="isRefreshing"
                    class="px-5 py-2.5 rounded-xl bg-slate-800 border border-white/10 text-white font-semibold text-sm shadow-lg hover:border-emerald-500/50 hover:bg-slate-700 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                >
                    <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': isRefreshing }"></i>
                    <span>{{ isRefreshing ? 'Sinkronisasi...' : 'Refresh Data' }}</span>
                </button>
            </div>

            <div class="flex flex-col bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl overflow-hidden w-full">
                
                <div class="p-4 border-b border-white/5 bg-[#0f172a]/40 flex flex-col md:flex-row justify-between items-center gap-4 shrink-0">
                    <div class="relative w-full max-w-md group">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-emerald-400 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Cari Lot, Material, Batch..." 
                            class="w-full bg-black/20 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500/50 focus:bg-black/30 transition-all"
                        >
                    </div>
                    <div class="text-xs font-medium text-slate-400">
                        Total: <b class="text-white">{{ filteredLots.length }}</b> Item
                    </div>
                </div>

                <div class="overflow-y-auto max-h-[500px] scrollbar-thin scrollbar-track-slate-800/20 scrollbar-thumb-emerald-500/30 hover:scrollbar-thumb-emerald-500/50">
                    
                    <div v-if="props.errorMessage" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                        <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center text-red-400 text-2xl mb-4">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-1">Terjadi Kesalahan</h3>
                        <p class="text-sm max-w-xs">{{ props.errorMessage }}</p>
                    </div>

                    <div v-else-if="props.initialLots.length === 0" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                        <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center text-slate-600 text-3xl mb-4">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-1">Tidak Ada Data</h3>
                        <p class="text-sm">Tidak ditemukan inspection lot aktif untuk MRP <b>{{ props.dispoCode }}</b>.</p>
                    </div>

                    <div v-else-if="filteredLots.length === 0" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center text-slate-600 text-2xl mb-4">
                            <i class="fa-solid fa-filter-circle-xmark"></i>
                        </div>
                        <p class="text-sm">Tidak ditemukan data dengan kata kunci "<b>{{ searchQuery }}</b>"</p>
                    </div>

                    <table v-else class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-20 bg-[#0f172a] shadow-md border-b border-white/10">
                            <tr>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Inspection Lot</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">PRO</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Material</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Batch Info</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Qty</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap text-right">Aksi</th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-white/5">
                            <tr 
                                v-for="(lot, index) in filteredLots" 
                                :key="lot.PRUEFLOS" 
                                class="hover:bg-emerald-500/5 transition-colors group animate-fade-in-up"
                                :style="{ animationDelay: `${index * 0.03}s` }"
                            >
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white text-sm">{{ lot.PRUEFLOS }}</span>
                                        <span class="text-[0.65rem] text-slate-500 bg-white/5 px-1.5 py-0.5 rounded w-fit">{{ props.plantCode }}</span>
                                    </div>
                                </td>
                                
                                <td class="py-4 px-6 text-sm text-slate-300 font-mono">{{ lot.AUFNR }}</td>
                                
                                <td class="py-4 px-6">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-200 text-sm">{{ lot.KTEXTMAT }}</span>
                                        <span class="text-xs text-slate-500 font-mono">{{ lot.MATNR }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-medium">
                                        <i class="fa-solid fa-box"></i> {{ lot.CHARG }}
                                    </div>
                                </td>

                                <td class="py-4 px-6 text-sm text-slate-300 font-mono font-bold">
                                    {{ parseInt(lot.LOSMENGE) }} <span class="text-xs font-normal text-slate-500">{{ lot.MENGENEINH === 'ST' ? 'PC' : lot.MENGENEINH }}</span>
                                </td>

                                <td class="py-4 px-6 text-sm text-slate-400 whitespace-nowrap">
                                    <i class="fa-regular fa-calendar mr-1.5 text-slate-600"></i> {{ formatDate(lot.ENSTEHDAT) }}
                                </td>

                                <td class="py-4 px-6">
                                    <span 
                                        class="px-3 py-1 rounded-full text-[0.7rem] font-bold border"
                                        :class="{
                                            'bg-emerald-500/10 border-emerald-500/20 text-emerald-400': !lot.STATS || lot.STATS === 'REL',
                                            'bg-indigo-500/10 border-indigo-500/20 text-indigo-400': lot.STATS === 'UD',
                                            'bg-slate-500/10 border-slate-500/20 text-slate-400': lot.STATS !== 'REL' && lot.STATS !== 'UD'
                                        }"
                                    >
                                        {{ lot.STATS || 'REL' }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right right-0 z-10 bg-[#162032] group-hover:bg-[#1a283f] border-l border-white/5 shadow-[-2px_0_5px_rgba(0,0,0,0.1)]">
                                    <button 
                                        @click="processLot(lot)"
                                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all hover:pr-5 group/btn"
                                    >
                                        <span>Inspect</span> <i class="fa-solid fa-magnifying-glass text-[0.6rem] transition-transform group-hover/btn:scale-110"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
/* Animations (Sama) */
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
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-float-1 { animation: float 20s ease-in-out infinite; }
.animate-float-2 { animation: float 20s ease-in-out infinite 5s; }
.animate-fade-in-up { animation: fadeInUp 0.4s ease-out backwards; }

/* Scrollbar Style */
.scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
.scrollbar-track-slate-800\/20::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.2); }
.scrollbar-thumb-emerald-500\/30::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 10px; }
.scrollbar-thumb-emerald-500\/30::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }
</style>
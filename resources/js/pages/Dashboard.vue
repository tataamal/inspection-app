<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';

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

// [MODIFIKASI]: Computed untuk mendeteksi User Khusus
const isSpecialUser = computed(() => {
    return props.authUser?.username === 'KMI-U124' && props.authUser?.nik === '10001069';
    // return props.authUser?.username === 'auto_email' && props.authUser?.nik === '10000424';
});

const filteredMrp = computed(() => {
    if (!searchQuery.value) return props.mrpList;
    const lowerSearch = searchQuery.value.toLowerCase();
    return props.mrpList.filter(mrp => 
        mrp.code.toLowerCase().includes(lowerSearch) || 
        (mrp.name && mrp.name.toLowerCase().includes(lowerSearch))
    );
});

const filteredHistory = computed(() => {
    return props.historyList; 
});

const goToInspectionList = (mrpItem) => {
    processingMrp.value = mrpItem.code;
    router.get(`/inspection/${mrpItem.code}`, { 
        plant: mrpItem.plant 
    }, {
        onFinish: () => {
            processingMrp.value = null;
        }
    });
};

const logout = () => {
    router.post('/logout');
};

// [MODIFIKASI FINAL]: Fungsi Print History yang sebenarnya
const printHistory = () => {
    // Membuka tab baru ke endpoint export PDF
    // Pastikan route '/inspection/history/export' sudah didefinisikan di web.php
    window.open('/inspection/history/export', '_blank');
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

    <div class="relative min-h-screen bg-slate-900 font-sans text-slate-200 overflow-x-hidden selection:bg-emerald-500 selection:text-white">
        
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            
            <div class="absolute -top-[10%] -right-[10%] w-[500px] h-[500px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute top-1/2 left-1/2 w-[350px] h-[350px] bg-emerald-400 rounded-full blur-[80px] opacity-15 -translate-x-1/2 -translate-y-1/2 animate-float-3"></div>
            
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <nav class="sticky top-0 z-50 w-full bg-[#0f172a]/80 backdrop-blur-xl border-b border-emerald-500/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)]">
            <div class="max-w-[1400px] mx-auto px-6 py-4 flex justify-between items-center">
                
                <div class="flex items-center gap-4">
                    <img src="/images/KMI.png" alt="KMI Logo" class="h-10 w-auto drop-shadow-[0_2px_8px_rgba(16,185,129,0.3)]" />
                    <div class="flex flex-col">
                        <h3 class="text-white font-extrabold text-lg tracking-tight leading-tight">KMI Inspection</h3>
                        <span class="text-emerald-500 text-[0.65rem] font-bold uppercase tracking-widest">Quality Control System</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-white font-bold text-sm">{{ authUser.username }}</span>
                            <span class="text-slate-400 text-xs font-medium">NIK: {{ authUser.nik }}</span>
                        </div>
                    </div>

                    <button 
                        @click="logout" 
                        class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] transition-all duration-300 flex items-center justify-center"
                        title="Keluar"
                    >
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </nav>

        <main class="relative z-10 max-w-[1400px] mx-auto px-6 py-12">
            
            <!-- SECTION 1: MRP LIST (Area Kerja) -->
            <!-- [MODIFIKASI]: Hanya tampil jika BUKAN special user -->
            <div v-if="!isSpecialUser">
                <div class="text-center mb-12">
                    <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold backdrop-blur-md mb-4">
                        <i class="fa-solid fa-briefcase"></i>
                        Area Kerja
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-3 tracking-tight drop-shadow-2xl">
                        Manufacturing Resource Planning
                    </h1>
                    <p class="text-slate-400 text-lg">Kelola dan monitor area MRP yang menjadi tanggung jawab Anda</p>
                    
                    <div class="flex flex-wrap justify-center gap-5 mt-10 mb-10">
                        <div class="group min-w-[200px] bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 flex items-center gap-4 hover:-translate-y-1 hover:bg-white/10 hover:border-emerald-500/30 hover:shadow-[0_10px_30px_rgba(16,185,129,0.1)] transition-all duration-300">
                            <div class="w-12 h-12 rounded-xl bg-slate-400/10 flex items-center justify-center text-slate-400 text-xl group-hover:bg-emerald-500/10 group-hover:text-emerald-500 transition-colors">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-3xl font-extrabold text-white leading-none">{{ props.mrpList.length }}</span>
                                <span class="text-slate-400 text-sm font-medium mt-1">Total MRP</span>
                            </div>
                        </div>
                        
                        <div class="min-w-[200px] bg-white/5 backdrop-blur-md border border-emerald-500/30 rounded-2xl p-5 flex items-center gap-4 shadow-[0_10px_30px_rgba(16,185,129,0.1)]">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-xl">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-3xl font-extrabold text-white leading-none">{{ filteredMrp.length }}</span>
                                <span class="text-slate-400 text-sm font-medium mt-1">Ditampilkan</span>
                            </div>
                        </div>
                    </div>

                    <div class="relative max-w-2xl mx-auto group">
                        <i class="fa-solid fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-emerald-500 transition-colors duration-300 z-10"></i>
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            placeholder="Cari berdasarkan kode atau nama MRP..."
                            class="w-full bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl py-4 pl-14 pr-20 text-white placeholder-slate-500 outline-none focus:border-emerald-500 focus:bg-white/10 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-300"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/5 border border-white/10 text-slate-500 px-2.5 py-1 rounded-lg text-xs font-bold">
                            Filter
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-20">
                    
                    <div 
                        v-for="(mrp, index) in filteredMrp" 
                        :key="mrp.id"
                        @click="goToInspectionList(mrp)"
                        class="group relative bg-white/5 backdrop-blur-md border border-white/10 rounded-[20px] p-6 cursor-pointer overflow-hidden hover:-translate-y-2 hover:bg-white/10 hover:border-emerald-500/40 hover:shadow-[0_20px_40px_rgba(16,185,129,0.2)] transition-all duration-300 animate-fade-in-up"
                        :class="{ 'pointer-events-none opacity-80': processingMrp === mrp.code }"
                        :style="{ animationDelay: `${index * 0.05}s` }"
                    >
                        <div v-if="processingMrp === mrp.code" class="absolute inset-0 z-20 bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center text-emerald-400 transition-all">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-2"></i>
                            <span class="text-xs font-bold uppercase tracking-widest animate-pulse">Syncing SAP...</span>
                        </div>

                        <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-emerald-500 to-emerald-300 transform scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out"></div>

                        <div class="flex justify-between items-center mb-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-emerald-500/30 group-hover:rotate-6 group-hover:scale-110 transition-transform duration-300">
                                {{ mrp.code.substring(0, 1) }}
                            </div>
                            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                                <i class="fa-solid fa-industry"></i>
                                Plant {{ mrp.plant }}
                            </span>
                        </div>

                        <div class="mb-5">
                            <h3 class="text-white text-2xl font-extrabold mb-2 tracking-tight">{{ mrp.code }}</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">{{ mrp.name || 'Nama area belum didefinisikan' }}</p>
                        </div>

                        <div class="pt-4 border-t border-white/5">
                            <span class="flex items-center gap-2 text-emerald-400 text-sm font-bold opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                                Lihat Detail
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="filteredMrp.length === 0" class="mt-16 text-center p-12 bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl animate-fade-in-up">
                    <div v-if="props.mrpList.length === 0">
                        <div class="text-5xl mb-4 grayscale opacity-50">⚠️</div>
                        <p class="text-white text-lg font-semibold mb-1">Data MRP Tidak Ditemukan</p>
                        <p class="text-slate-400">Tidak ada data MRP yang terdaftar untuk NIK <span class="text-white font-mono">{{ authUser.nik }}</span>.</p>
                    </div>
                    <div v-else>
                        <div class="text-5xl mb-4 text-slate-600">🔍</div>
                        <p class="text-white text-lg font-semibold">Pencarian Tidak Ditemukan</p>
                        <p class="text-slate-400">Tidak ada hasil untuk kata kunci "<span class="text-emerald-400">{{ searchQuery }}</span>"</p>
                    </div>
                </div>
            </div>

            <!-- [MODIFIKASI]: SECTION 2: HISTORY (Riwayat Inspeksi) -->
            <!-- Tampil JIKA user adalah 'KMI-U124' dan NIK '10001069' -->
            <div 
                v-if="isSpecialUser"
                class="animate-fade-in-up"
            >
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold mb-3">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Log Aktivitas
                        </div>
                        <h2 class="text-3xl font-extrabold text-white tracking-tight">Riwayat Inspeksi</h2>
                        <p class="text-slate-400 text-sm mt-1">
                            Riwayat Usage Decision yang dilakukan oleh <span class="text-white font-mono">{{ authUser.username }}</span>
                        </p>
                    </div>

                    <!-- Cards Summary History -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Card Total History -->
                        <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center gap-4 min-w-[200px]">
                            <div class="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-list-check"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white leading-none">{{ historyList.length }}</div>
                                <div class="text-xs text-slate-400 mt-1 font-medium uppercase tracking-wider">Total Riwayat</div>
                            </div>
                        </div>

                        <!-- Card Print History -->
                        <button 
                            @click="printHistory"
                            class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center gap-4 min-w-[200px] hover:bg-emerald-500/10 hover:border-emerald-500/50 transition-all group text-left"
                        >
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-print"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white leading-tight">Cetak Laporan</div>
                                <div class="text-xs text-slate-400 mt-0.5 group-hover:text-emerald-400 transition-colors">Export to PDF/Excel</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Table History -->
                <div class="bg-[#1e293b]/50 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white/5 border-b border-white/10 text-xs uppercase tracking-wider text-slate-400 font-bold">
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Inspection Lot</th>
                                    <th class="p-4">Material</th>
                                    <th class="p-4">Order / Batch</th>
                                    <th class="p-4">Qty</th>
                                    <th class="p-4">UD Code</th>
                                    <th class="p-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-sm">
                                <tr v-if="filteredHistory.length === 0">
                                    <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                        Belum ada riwayat inspeksi untuk user ini.
                                    </td>
                                </tr>
                                <tr 
                                    v-else
                                    v-for="item in filteredHistory" 
                                    :key="item.id" 
                                    class="hover:bg-white/5 transition-colors"
                                >
                                    <td class="p-4 whitespace-nowrap text-slate-300 font-mono text-xs">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-white">{{ item.prueflos }}</div>
                                        <div class="text-xs text-slate-500">Plant: {{ item.plant }}</div>
                                    </td>
                                    <td class="p-4 max-w-[200px]">
                                        <div class="truncate font-medium text-slate-200" :title="item.material_desc">
                                            {{ item.material_desc || '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono">{{ item.material_code }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-xs text-slate-400">Ord: <span class="text-slate-300">{{ item.order_number || '-' }}</span></div>
                                        <div class="text-xs text-slate-400">Bch: <span class="text-slate-300">{{ item.batch || '-' }}</span></div>
                                    </td>
                                    <td class="p-4 font-mono">
                                        {{ parseFloat(item.quantity) }} <span class="text-[0.65rem] text-slate-500">{{ item.uom }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-bold text-white bg-slate-800 px-2 py-1 rounded border border-white/10 text-xs">
                                            {{ item.ud_code }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span 
                                            class="px-2 py-1 rounded text-[0.65rem] font-bold border"
                                            :class="getHistoryStatusColor(item.status)"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Footer Table Info (Optional) -->
                    <div class="p-3 border-t border-white/10 bg-white/5 text-[0.65rem] text-slate-500 text-right">
                        Menampilkan {{ filteredHistory.length }} data terakhir
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
/* Custom Animations & Pattern */

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
.animate-float-3 { animation: float 20s ease-in-out infinite 10s; }
.animate-fade-in-up { animation: fadeInUp 0.6s ease-out backwards; }
</style>
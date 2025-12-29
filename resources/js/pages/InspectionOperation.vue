<script setup>
import { reactive, watch, ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3'; 
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    authUser: Object,
});

const form = reactive({
    pro: '',
    mrp: ''
});

const isLoading = ref(false);
const simulatedData = ref(null);
let abortController = null;
let removeGuard = null;
const isFormExpanded = ref(true);
const selectedItems = ref([]);

// Watch input changes to enforce numeric + semicolon + space only for PRO
watch(() => form.pro, (newVal) => {
    // Allows digits (0-9), semicolon (;), and space ( )
    if (newVal) {
        form.pro = newVal.replace(/[^0-9; ]/g, '');
    }
});

// Computed for Select All
const isAllSelected = computed(() => {
    return simulatedData.value?.items?.length > 0 && selectedItems.value.length === simulatedData.value.items.length;
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = simulatedData.value.items.map((_, index) => index);
    }
};

const toggleSelection = (index) => {
    const i = selectedItems.value.indexOf(index);
    if (i === -1) {
        selectedItems.value.push(index);
    } else {
        selectedItems.value.splice(i, 1);
    }
};

const submitQM = async () => {
    if (selectedItems.value.length === 0) return;

    // Filter selected data
    const selectedData = simulatedData.value.items.filter((_, idx) => selectedItems.value.includes(idx));
    
    // Create HTML list for verification
    let htmlContent = '<div class="text-left text-sm max-h-60 overflow-y-auto pr-2 custom-scrollbar">';
    selectedData.forEach(item => {
        htmlContent += `
            <div class="mb-3 p-3 bg-white/5 rounded-lg border border-white/10">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-bold text-emerald-400">PRO: ${item.AUFNR}</span>
                    <span class="text-xs text-slate-500">RUECK: ${item.RUECK || '-'}</span>
                </div>
                <div class="text-xs text-slate-300">Desc: ${item.MAKTX}</div>
                <div class="text-xs text-slate-500 mt-1">RMZHL: ${item.RMZHL || '-'}</div>
            </div>`;
    });
    htmlContent += '</div>';

    await Swal.fire({
        title: 'Konfirmasi Submit QM',
        html: htmlContent,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Submit',
        cancelButtonText: 'Batal',
        background: '#1e293b',
        color: '#fff',
        customClass: {
            popup: 'border border-emerald-500/30 shadow-2xl'
        }
    });
};

const submit = async () => {
    if (!form.pro && !form.mrp) return;

    // Confirm for MRP (Potential Long Wait)
    if (form.mrp) {
        const result = await Swal.fire({
            title: 'Konfirmasi Pencarian',
            text: 'Pencarian berdasarkan MRP mungkin memakan waktu lama karena banyaknya data. Apakah Anda ingin melanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            background: '#1e293b',
            color: '#fff'
        });

        if (!result.isConfirmed) return;
    }
    
    isLoading.value = true;
    simulatedData.value = null; // Reset previous data
    selectedItems.value = []; // Reset selection

    // Create new AbortController
    if (abortController) abortController.abort();
    abortController = new AbortController();

    try {
        const response = await axios.post('/inspection-operation/simulate', form, {
            signal: abortController.signal
        });
        
        if (response.data.status === 'success') {
            simulatedData.value = response.data.data;
            isFormExpanded.value = false; // Collapse form on success
            
            Swal.fire({
                icon: 'success',
                title: 'Data Ditemukan',
                text: `${response.data.data.count} item ditemukan.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                background: '#1e293b',
                color: '#fff'
            });
        }
    } catch (error) {
        if (axios.isCancel(error)) {
            console.log('Request canceled by user');
            return;
        }

        let msg = 'Terjadi kesalahan validasi.';
        if (error.response && error.response.data.errors) {
            msg = Object.values(error.response.data.errors).flat().join('<br>');
        } else if (error.response && error.response.data.message) {
            msg = error.response.data.message;
        }

        Swal.fire({
            icon: 'error',
            title: 'Gagal Mengambil Data',
            html: msg,
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        });
    } finally {
        // Only stop loading if component is still active (guard might have handled it otherwise)
        if (isLoading.value) isLoading.value = false;
    }
};

const reOpenForm = () => {
    isFormExpanded.value = true;
};

const handleBeforeUnload = (e) => {
    if (isLoading.value) {
        e.preventDefault();
        e.returnValue = ''; // Chrome requires returnValue to be set
    }
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);

    removeGuard = router.on('before', (event) => {
        if (isLoading.value) {
            // Cancel the visit
            event.preventDefault();

            Swal.fire({
                title: 'Sedang Memproses Data',
                text: 'Proses penarikan data sedang berjalan. Jika Anda pindah halaman, proses akan dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Batalkan & Keluar',
                cancelButtonText: 'Tetap di Sini',
                background: '#1e293b',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed to leave -> Abort request & Navigate
                    if (abortController) abortController.abort();
                    isLoading.value = false; // Turn off loading to pass the guard check
                    
                    // Manually retry the visit
                    router.visit(event.detail.visit.url, event.detail.visit);
                }
            });
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    if (removeGuard) removeGuard();
    
    // Safety abort if unmounted while loading (e.g. abrupt change)
    if (isLoading.value && abortController) {
        abortController.abort();
    }
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Inspection Operation" />

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
                <!-- Navbar Right -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-lg text-xs">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="flex flex-col text-right" v-if="authUser">
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
            
            <!-- HEADER SECTION -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 animate-fade-in-up">
                
                <!-- Left: Title -->
                <div class="text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold backdrop-blur-md mb-3">
                        <i class="fa-solid fa-clipboard-check"></i>
                        Inspection Operation
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                        Input Operation Details
                    </h1>
                </div>

                <!-- Right: Back Button -->
                <div class="flex justify-center md:justify-end">
                    <Link href="/dashboard" class="px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 hover:text-white transition-all flex items-center gap-2 text-sm font-bold group">
                        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        Kembali ke Dashboard
                    </Link>
                </div>
            </div>

            <!-- FORM CARD -->
            <div class="max-w-xl md:max-w-4xl mx-auto animate-fade-in-up mb-12">
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 md:p-10 shadow-2xl relative overflow-hidden transition-all duration-500">
                    
                    <!-- EXPANDED VIEW: Full Form -->
                    <form v-if="isFormExpanded" @submit.prevent="submit" class="relative z-10 transition-all duration-300">
                        
                        <!-- INPUT GROUP CONTAINER -->
                        <div class="flex flex-col md:flex-row gap-8 items-stretch mb-8">
                            
                            <!-- PRO Input Container -->
                            <div class="flex-1 flex flex-col gap-2 transition-opacity duration-300" :class="{ 'opacity-50 grayscale': form.mrp.length > 0 }">
                                <label for="pro" class="text-xs uppercase font-bold text-emerald-400 tracking-wider flex justify-between">
                                    PRO
                                    <span v-if="form.mrp.length > 0" class="text-[0.6rem] text-slate-500 italic lowercase normal-case self-end">*disabled (MRP terisi)</span>
                                </label>
                                <div class="relative group/input flex-1">
                                    <input 
                                        id="pro"
                                        type="text" 
                                        v-model="form.pro"
                                        :disabled="form.mrp.length > 0"
                                        placeholder="Contoh: 343000001911; 155000092191 ..." 
                                        class="w-full h-full min-h-[56px] bg-black/40 rounded-xl border border-white/10 text-white text-lg py-3 pl-12 pr-4 focus:border-emerald-500/50 focus:bg-black/20 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-600 font-mono disabled:cursor-not-allowed disabled:bg-black/20"
                                    >
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 rounded flex items-center justify-center bg-white/5 text-slate-500 group-focus-within/input:text-emerald-400 transition-colors">
                                        <i class="fa-solid fa-hashtag text-xs"></i>
                                    </div>
                                </div>
                                <p class="text-[0.65rem] text-slate-500 pl-1">
                                    *Gunakan spasi atau titik koma (;) untuk banyak data.
                                </p>
                            </div>
    
                            <!-- DIVIDER OR -->
                            <div class="relative flex items-center justify-center md:flex-col">
                                <div class="w-full h-px bg-white/10 md:hidden"></div>
                                <div class="hidden md:block w-px h-full bg-white/10"></div>
                                <span class="absolute px-3 py-1 bg-[#111c2e] text-xs font-bold text-slate-500 uppercase tracking-widest rounded-full border border-white/5">ATAU</span>
                            </div>
    
                            <!-- MRP Input Container -->
                            <div class="flex-1 flex flex-col gap-2 transition-opacity duration-300" :class="{ 'opacity-50 grayscale': form.pro.length > 0 }">
                                <label for="mrp" class="text-xs uppercase font-bold text-emerald-400 tracking-wider flex justify-between">
                                    MRP
                                    <span v-if="form.pro.length > 0" class="text-[0.6rem] text-slate-500 italic lowercase normal-case self-end">*disabled (PRO terisi)</span>
                                </label>
                                <div class="relative group/input flex-1">
                                    <input 
                                        id="mrp"
                                        type="text" 
                                        v-model="form.mrp"
                                        :disabled="form.pro.length > 0"
                                        placeholder="Contoh: WW2; D21 ..." 
                                        class="w-full h-full min-h-[56px] bg-black/40 rounded-xl border border-white/10 text-white text-lg py-3 pl-12 pr-4 focus:border-emerald-500/50 focus:bg-black/20 focus:ring-1 focus:ring-emerald-500/50 transition-all placeholder:text-slate-600 font-mono disabled:cursor-not-allowed disabled:bg-black/20"
                                    >
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6 rounded flex items-center justify-center bg-white/5 text-slate-500 group-focus-within/input:text-emerald-400 transition-colors">
                                        <i class="fa-solid fa-layer-group text-xs"></i>
                                    </div>
                                </div>
                                <p class="text-[0.65rem] text-slate-500 pl-1">
                                    *Gunakan spasi atau titik koma (;) untuk banyak data.
                                </p>
                            </div>

                        </div>

                        <!-- SUBMIT BUTTON -->
                        <div>
                            <button 
                                type="submit" 
                                :disabled="(!form.pro && !form.mrp) || isLoading"
                                class="w-full py-4 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-lg shadow-lg shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                            >
                                <i v-if="isLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                                <i v-else class="fa-solid fa-paper-plane"></i>
                                {{ isLoading ? 'Memproses...' : 'Proses Data' }}
                            </button>
                        </div>
                    </form>

                    <!-- COLLAPSED VIEW: Summary -->
                    <div v-else class="flex items-center justify-between gap-4 animate-fade-in">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                                <i v-if="form.pro" class="fa-solid fa-hashtag text-xl"></i>
                                <i v-else class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">
                                    {{ form.pro ? 'Production Order (PRO)' : 'MRP Controller' }}
                                </h3>
                                <p class="text-white font-mono font-medium text-lg truncate max-w-md">
                                    {{ form.pro || form.mrp }}
                                </p>
                            </div>
                        </div>
                        
                        <button 
                            @click="reOpenForm"
                            class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white font-bold text-sm transition-all flex items-center gap-2 group"
                        >
                            <span>Cari Ulang</span>
                            <i class="fa-solid fa-pen-to-square group-hover:rotate-12 transition-transform text-slate-400 group-hover:text-emerald-400"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- RESULT TABLE SECTION (REAL API DATA) -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0">
                <div v-if="simulatedData && simulatedData.count > 0" class="max-w-[1600px] mx-auto animate-fade-in-up">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-emerald-500"></i>
                            Hasil Pencarian
                            <span class="text-sm font-normal text-slate-400 font-mono">({{ simulatedData.type }})</span>
                        </h2>
                        <span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-full">
                            {{ simulatedData.count }} Data Ditemukan
                        </span>
                    </div>

                    <div class="bg-[#162032]/50 rounded-2xl border border-white/5 pb-2 overflow-hidden shadow-xl">
                        <div class="overflow-auto max-h-[450px] custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-[#162032] sticky top-0 z-10 shadow-md">
                                    <tr>
                                        <!-- CHECKBOX ALL -->
                                        <th class="bg-[#0f172a] py-4 px-4 w-12 border-b border-white/10 text-center">
                                            <input 
                                                type="checkbox" 
                                                :checked="isAllSelected" 
                                                @change="toggleSelectAll"
                                                class="w-4 h-4 rounded border-slate-600 bg-[#0f172a] text-emerald-500 focus:ring-emerald-500/50 cursor-pointer"
                                            >
                                        </th>
                                        <th class="bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10">NIK (PERNR)</th>
                                        <th class="bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10">PRO (AUFNR)</th>
                                        <th class="bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10">Description (MAKTX)</th>
                                        <th class="bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10">Qty</th>
                                        <th class="bg-[#0f172a] py-4 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/10">Posting Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-sm">
                                    <tr 
                                        v-for="(item, idx) in simulatedData.items" 
                                        :key="idx" 
                                        class="hover:bg-white/5 transition-colors group cursor-pointer" 
                                        :class="{ 'bg-emerald-500/5': selectedItems.includes(idx) }"
                                        @click="toggleSelection(idx)"
                                    >
                                        <!-- CHECKBOX ROW -->
                                        <td class="py-4 px-4 text-center">
                                            <input 
                                                type="checkbox" 
                                                :checked="selectedItems.includes(idx)"
                                                class="w-4 h-4 rounded border-slate-600 bg-[#0f172a] text-emerald-500 focus:ring-emerald-500/50 cursor-pointer"
                                                @click.stop="toggleSelection(idx)"
                                            >
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-bold text-white">{{ item.PERNR }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-mono text-emerald-400">{{ item.AUFNR }}</div>
                                        </td>
                                        <td class="py-4 px-4 max-w-[300px]">
                                            <div class="text-slate-200 font-medium truncate" :title="item.MAKTX">{{ item.MAKTX }}</div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="text-white font-bold">{{ item.QTY }}</span> 
                                            <span class="text-xs text-slate-500 ml-1">{{ item.UOM }}</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2 text-slate-300">
                                                <i class="fa-regular fa-calendar text-[0.65rem]"></i> 
                                                {{ item.BUDAT }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </transition>

        </main>

        <!-- FLOATING ACTION BAR -->
        <Transition 
            enter-active-class="transition ease-out duration-300" 
            enter-from-class="translate-y-full opacity-0" 
            enter-to-class="translate-y-0 opacity-100" 
            leave-active-class="transition ease-in duration-200" 
            leave-from-class="translate-y-0 opacity-100" 
            leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="selectedItems.length > 0" class="fixed bottom-6 left-0 right-0 z-50 flex justify-center px-4">
                <div class="bg-[#1e293b]/90 backdrop-blur-xl border border-emerald-500/30 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.5)] py-3 pl-6 pr-3 flex items-center gap-6">
                    <div class="flex items-center gap-3 border-r border-white/10 pr-6">
                        <span class="w-8 h-8 rounded-full bg-emerald-500 text-black flex items-center justify-center text-sm font-bold">{{ selectedItems.length }}</span>
                        <div class="flex flex-col">
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Selected</span>
                            <span class="text-sm font-bold text-white">Items</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button 
                            @click="selectedItems = []" 
                            class="w-10 h-10 rounded-full hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors"
                            title="Clear Selection"
                        >
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                        <button 
                            @click="submitQM" 
                            class="px-6 py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center gap-2"
                        >
                            <i class="fa-solid fa-check-double"></i> 
                            Submit QM
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

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

/* Custom Scrollbar Logic */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;       /* Vertical scrollbar width */
    height: 6px;      /* Horizontal scrollbar height */
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155; /* slate-700 */
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569; /* slate-600 */
}
.custom-scrollbar::-webkit-scrollbar-corner {
    background: transparent;
}
</style>

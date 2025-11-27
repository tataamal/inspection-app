<script setup>
import { ref, onMounted, onBeforeUnmount, reactive, nextTick } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    lotNumber: String,
    plantCode: String,
    dispoCode: String,
    authUser: Object,
    lotData: Object
});

const inspectionData = ref({});
const isValidSession = ref(false);
const currentDate = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

const form = useForm({
    nik_qc: props.authUser.nik || '',
    cause_effect: '',
    correction: '',
    aql_critical_found: '', aql_critical_allowed: '',
    aql_major_found: '', aql_major_allowed: '',
    aql_minor_found: '', aql_minor_allowed: '',
    qty_accepted: '',
    qty_reject: '',
    defects: [], 
    images: { front: null, back: null, top: null, bottom: null }
});

const inspectionItems = [
    "Dimension", "Oversize", "Undersize", "Misalignment", "Distortion",
    "Surface", "Roughness", "Color", "Telegraphic", "Dislamination",
    "Dirtiness", "Tidiness", "Material Defect", "Wrong Material",
    "Damage", "Wave", "Hairlaine", "Corrosion", "Chipping",
    "Operator Error", "Machine Malfunction", "Tool Wear", "Setup Machine"
];

const toggleDefect = (item) => {
    if (form.defects.includes(item)) {
        form.defects = form.defects.filter(i => i !== item);
    } else {
        form.defects.push(item);
    }
};

// 4. Camera Logic
const cameraViews = [
    { slug: 'front', label: 'Tampilan Depan' },
    { slug: 'back', label: 'Tampilan Belakang' },
    { slug: 'top', label: 'Tampilan Atas' },
    { slug: 'bottom', label: 'Tampilan Bawah' },
];

const cameraActive = reactive({ front: false, back: false, top: false, bottom: false });
const videoRefs = ref({}); // Menyimpan referensi elemen video
const activeStreams = {};  // Menyimpan stream media

const selectAllDefects = () => {
    form.defects = [...inspectionItems]; 
};

const clearAllDefects = () => {
    form.defects = [];
};

const startCamera = async (slug) => {
    cameraActive[slug] = true;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        activeStreams[slug] = stream;
        
        await nextTick(); // Tunggu DOM update agar ref video tersedia
        if (videoRefs.value[slug]) {
            videoRefs.value[slug].srcObject = stream;
        }
    } catch (err) {
        cameraActive[slug] = false;
        Swal.fire({ icon: 'error', text: 'Gagal akses kamera.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    }
};

const captureImage = (slug) => {
    const video = videoRefs.value[slug];
    if (video) {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        // Simpan ke form data
        form.images[slug] = canvas.toDataURL('image/jpeg', 0.8);
        stopCamera(slug);
    }
};

const stopCamera = (slug) => {
    if (activeStreams[slug]) {
        activeStreams[slug].getTracks().forEach(t => t.stop());
        delete activeStreams[slug];
    }
    cameraActive[slug] = false;
};

const retakeImage = (slug) => {
    form.images[slug] = null;
    startCamera(slug);
};

onMounted(() => {
    // Cek apakah data dikirim dari Controller
    if (props.lotData) {
        inspectionData.value = props.lotData; // Load data dari Props
        isValidSession.value = true;
    } else {
        // Fallback jika controller gagal kirim data
        isValidSession.value = false;
        Swal.fire({
            icon: 'error',
            title: 'Data Tidak Ditemukan',
            text: 'Silakan kembali ke daftar inspeksi.',
            confirmButtonColor: '#10b981',
            background: '#1e293b',
            color: '#fff',
            allowOutsideClick: false
        }).then(() => goBack());
    }
});

onBeforeUnmount(() => {
    Object.keys(activeStreams).forEach(slug => stopCamera(slug));
});

const goBack = () => {
    if (props.dispoCode) {
        // Gunakan router.get standard
        router.get(`/inspection/${props.dispoCode}`, { plant: props.plantCode });
    } else {
        router.get('/dashboard');
    }
};

// 6. Submit Logic
const showModal = ref(false);

const openSubmitModal = () => {
    if (!form.cause_effect && form.defects.length > 0) {
        Swal.fire({ 
            icon: 'info', 
            text: 'Mohon isi Catatan/Penyebab Defect karena ada defect yang dipilih.', 
            confirmButtonColor: '#10b981',
            background: '#1e293b', color: '#fff'
        });
        return;
    }
    showModal.value = true;
};

const submitFinal = () => {
    // Simulasi Submit ke Backend
    form.post('/inspection/submit', {
        onSuccess: () => {
            showModal.value = false;
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data inspeksi tersimpan.', timer: 1500, showConfirmButton: false })
                .then(() => goBack());
        },
        onError: () => {
            // Handle error
            showModal.value = false;
        }
    });
};
</script>

<template>
    <Head title="Form Inspeksi" />

    <div class="relative min-h-screen bg-slate-900 font-sans text-slate-200 overflow-x-hidden selection:bg-emerald-500 selection:text-white pb-32">
        
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <nav class="sticky top-0 z-50 w-full bg-[#0f172a]/95 backdrop-blur-xl border-b border-white/10 shadow-lg">
            <div class="max-w-[1400px] mx-auto px-6 py-3 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img src="/images/KMI.png" alt="Logo" class="h-9 w-auto" />
                    <div class="flex flex-col">
                        <h3 class="text-white font-bold text-base leading-tight">Inspection Form</h3>
                        <span class="text-emerald-500 text-[0.65rem] font-bold uppercase tracking-widest">Plant {{ props.plantCode }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center font-bold text-white shadow-lg shadow-emerald-500/20">
                        {{ (props.authUser.username || 'G').charAt(0) }}
                    </div>
                    <span class="hidden md:inline text-sm font-semibold text-white">{{ props.authUser.username }}</span>
                </div>
            </div>
        </nav>

        <main v-if="isValidSession" class="relative z-10 max-w-[1400px] mx-auto p-4 md:p-6 lg:p-8">
            
            <div class="flex justify-between items-center mb-8">
                <button @click="goBack" class="group flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/5 text-slate-300 hover:text-white transition-all">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    <span class="text-sm font-semibold">Kembali ke Daftar</span>
                </button>
                <div class="flex items-center gap-2 text-slate-400 text-sm font-medium bg-white/5 px-4 py-2 rounded-full border border-white/5">
                    <i class="fa-regular fa-calendar"></i>
                    {{ currentDate }}
                </div>
            </div>

            <form @submit.prevent="openSubmitModal" class="space-y-6">

                <div class="relative bg-slate-800/60 backdrop-blur-md rounded-2xl border border-white/10 overflow-hidden shadow-xl">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-sky-500"></div>
                    
                    <div class="p-6 md:p-8 border-b border-white/5 bg-white/5 flex flex-col md:flex-row justify-between md:items-center gap-4">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Inspection Lot</span>
                            <span class="text-3xl font-black text-white font-mono tracking-wide">{{ inspectionData.PRUEFLOS }}</span>
                        </div>
                        <div class="px-4 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono font-bold text-lg">
                            {{ inspectionData.CHARG }}
                        </div>
                    </div>

                    <div class="p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="col-span-1 md:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Material</label>
                            <p class="text-white font-bold text-lg leading-tight">{{ inspectionData.KTEXTMAT }}</p>
                            <span class="text-sm text-slate-400 font-mono mt-1 block">{{ inspectionData.MATNR }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Quantity</label>
                            <p class="text-white font-bold text-xl font-mono">
                                {{ parseInt(inspectionData.LMENGEZUB || inspectionData.LOSMENGE) }} 
                                <span class="text-sm text-slate-400">{{ inspectionData.MENGENEINH === 'ST' ? 'PC' : inspectionData.MENGENEINH }}</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Lokasi</label>
                            <p class="text-white font-bold text-lg flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-emerald-500"></i> {{ inspectionData.ARBPL || '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Dispo MRP</label>
                            <p class="text-white font-bold text-lg">{{ inspectionData.DISPO || '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 md:p-8">
                    
                    <div class="flex flex-wrap justify-between items-end mb-6 pb-4 border-b border-white/5 gap-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-list-check text-emerald-500 text-xl"></i>
                            <h3 class="text-white font-bold text-lg">Poin Inspeksi Visual</h3>
                        </div>

                        <div class="flex items-center gap-2">
                            <button 
                                type="button"
                                @click="selectAllDefects"
                                class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 border border-emerald-500/20 text-xs font-bold transition-all flex items-center gap-1.5"
                                title="Pilih Semua"
                            >
                                <i class="fa-solid fa-check-double"></i> Select All
                            </button>
                            <button 
                                type="button"
                                @click="clearAllDefects"
                                v-show="form.defects.length > 0"
                                class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 text-xs font-bold transition-all flex items-center gap-1.5 animate-fade-in"
                                title="Hapus Semua Pilihan"
                            >
                                <i class="fa-solid fa-trash-can"></i> Clear
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div 
                            v-for="(item, index) in inspectionItems" 
                            :key="index"
                            @click="toggleDefect(item)"
                            class="group relative flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer select-none"
                            :class="form.defects.includes(item) 
                                ? 'bg-emerald-500/20 border-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.15)]' 
                                : 'bg-black/20 border-white/5 hover:bg-white/5 hover:border-white/20'"
                        >
                            <div class="text-xl transition-colors" :class="form.defects.includes(item) ? 'text-emerald-400' : 'text-slate-600'">
                                <i class="fa-solid" :class="form.defects.includes(item) ? 'fa-square-check' : 'fa-square'"></i>
                            </div>
                            <span class="text-sm font-medium leading-tight" :class="form.defects.includes(item) ? 'text-white font-bold' : 'text-slate-400'">{{ item }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-right">
                         <span class="text-xs font-medium text-slate-500">
                            Terpilih: <b class="text-emerald-400">{{ form.defects.length }}</b> dari {{ inspectionItems.length }} item
                         </span>
                    </div>

                </div>

                <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                        <i class="fa-solid fa-camera text-emerald-500 text-xl"></i>
                        <h3 class="text-white font-bold text-lg">Dokumentasi Foto</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                        <div v-for="view in cameraViews" :key="view.slug" class="bg-slate-900 rounded-2xl overflow-hidden border border-white/10 flex flex-col">
                            
                            <div class="px-4 py-3 bg-white/5 flex justify-between items-center text-sm">
                                <span class="font-bold text-slate-300">{{ view.label }}</span>
                                <span v-if="form.images[view.slug]" class="text-emerald-400 font-bold flex items-center gap-1.5 text-xs">
                                    <i class="fa-solid fa-check-circle"></i> OK
                                </span>
                            </div>

                            <div class="relative h-48 bg-black flex items-center justify-center overflow-hidden group">
                                <div 
                                    v-if="!form.images[view.slug] && !cameraActive[view.slug]" 
                                    @click="startCamera(view.slug)"
                                    class="flex flex-col items-center justify-center cursor-pointer text-slate-600 hover:text-emerald-500 transition-colors w-full h-full"
                                >
                                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center text-2xl mb-2 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                    <span class="text-xs font-semibold">Buka Kamera</span>
                                </div>

                                <video 
                                    v-show="cameraActive[view.slug]" 
                                    :ref="(el) => videoRefs[view.slug] = el" 
                                    autoplay playsinline muted 
                                    class="w-full h-full object-cover"
                                ></video>

                                <img 
                                    v-if="form.images[view.slug]" 
                                    :src="form.images[view.slug]" 
                                    class="w-full h-full object-cover"
                                />
                            </div>

                            <div class="p-3 bg-slate-800/50 border-t border-white/5">
                                <div v-if="cameraActive[view.slug]" class="flex gap-2">
                                    <button type="button" @click="stopCamera(view.slug)" class="flex-1 py-2 rounded-lg border border-red-500/50 text-red-500 hover:bg-red-500/10 text-xs font-bold">
                                        Batal
                                    </button>
                                    <button type="button" @click="captureImage(view.slug)" class="flex-1 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold">
                                        Ambil
                                    </button>
                                </div>
                                <div v-else-if="form.images[view.slug]" class="flex">
                                    <button type="button" @click="retakeImage(view.slug)" class="flex-1 py-2 rounded-lg border border-yellow-500/50 text-yellow-500 hover:bg-yellow-500/10 text-xs font-bold flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-rotate-right"></i> Foto Ulang
                                    </button>
                                </div>
                                <div v-else class="flex">
                                    <button type="button" @click="startCamera(view.slug)" class="flex-1 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 text-xs font-bold">
                                        Mulai Foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 md:p-8 flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                            <i class="fa-solid fa-file-pen text-yellow-500 text-xl"></i>
                            <h3 class="text-white font-bold text-lg">Catatan & Perbaikan</h3>
                        </div>
                        
                        <div class="space-y-5 flex-1">
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Penyebab (Cause Effect)</label>
                                <textarea 
                                    v-model="form.cause_effect"
                                    placeholder="Jelaskan penyebab jika ada defect..."
                                    class="w-full h-32 bg-black/20 border border-white/10 rounded-xl p-4 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50 focus:bg-black/30 resize-none transition-all"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-2">Tindakan Perbaikan</label>
                                <textarea 
                                    v-model="form.correction"
                                    placeholder="Tindakan perbaikan yang dilakukan..."
                                    class="w-full h-32 bg-black/20 border border-white/10 rounded-xl p-4 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50 focus:bg-black/30 resize-none transition-all"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 md:p-8 h-full">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/5">
                            <i class="fa-solid fa-chart-pie text-sky-500 text-xl"></i>
                            <h3 class="text-white font-bold text-lg">Summary AQL Defect</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center mb-2">
                                <span class="text-left">Type</span>
                                <span>Found</span>
                                <span>Allowed</span>
                            </div>

                            <div class="grid grid-cols-3 gap-4 items-center p-3 rounded-xl bg-red-500/5 border border-red-500/10">
                                <span class="font-bold text-red-400">Critical</span>
                                <input type="number" v-model="form.aql_critical_found" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-red-500/50 focus:outline-none">
                                <input type="number" v-model="form.aql_critical_allowed" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-red-500/50 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-3 gap-4 items-center p-3 rounded-xl bg-yellow-500/5 border border-yellow-500/10">
                                <span class="font-bold text-yellow-400">Major</span>
                                <input type="number" v-model="form.aql_major_found" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-yellow-500/50 focus:outline-none">
                                <input type="number" v-model="form.aql_major_allowed" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-yellow-500/50 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-3 gap-4 items-center p-3 rounded-xl bg-sky-500/5 border border-sky-500/10">
                                <span class="font-bold text-sky-400">Minor</span>
                                <input type="number" v-model="form.aql_minor_found" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-sky-500/50 focus:outline-none">
                                <input type="number" v-model="form.aql_minor_allowed" placeholder="0" class="w-full bg-black/20 border border-white/10 rounded-lg py-2 px-3 text-center text-white font-bold focus:border-sky-500/50 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </main>

        <div v-else class="h-screen flex flex-col items-center justify-center text-slate-500">
            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-emerald-500 mb-4"></i>
            <p>Memvalidasi Data...</p>
        </div>

        <div v-if="isValidSession" class="fixed bottom-0 left-0 w-full bg-[#0f172a]/90 backdrop-blur-xl border-t border-white/10 z-40 shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
            <div class="max-w-[1400px] mx-auto px-4 md:px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                
                <div class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">QC Inspector ID</span>
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input 
                            type="text" 
                            v-model="form.nik_qc" 
                            placeholder="NIK Anda"
                            class="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-11 pr-4 text-white font-bold transition-all"
                            readonly
                        >
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <button 
                        @click="openSubmitModal"
                        class="w-full md:w-auto flex items-center justify-center gap-3 px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-extrabold shadow-lg shadow-emerald-500/30 hover:-translate-y-1 transition-all"
                    >
                        <span>Submit UD</span>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>

            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            
            <div class="relative w-full max-w-md bg-slate-800 rounded-3xl overflow-hidden border border-white/10 shadow-2xl transform transition-all scale-100">
                
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-500 p-6 text-center text-white">
                    <i class="fa-solid fa-clipboard-check text-4xl mb-3 drop-shadow-md"></i>
                    <h3 class="text-xl font-bold">Konfirmasi Hasil</h3>
                </div>

                <div class="p-6 md:p-8">
                    <p class="text-center text-slate-400 text-sm mb-6">Masukkan jumlah quantity keputusan akhir.</p>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-center text-xs font-bold text-emerald-500 uppercase mb-2">Accepted (OK)</label>
                            <div class="bg-black/20 rounded-xl p-1 focus-within:ring-2 focus-within:ring-emerald-500/50 transition-all">
                                <input 
                                    type="number" 
                                    v-model="form.qty_accepted" 
                                    placeholder="0"
                                    class="w-full bg-transparent border-none text-center text-3xl font-bold text-emerald-400 focus:ring-0 p-2"
                                >
                            </div>
                        </div>
                        <div>
                            <label class="block text-center text-xs font-bold text-red-500 uppercase mb-2">Rejected (NG)</label>
                            <div class="bg-black/20 rounded-xl p-1 focus-within:ring-2 focus-within:ring-red-500/50 transition-all">
                                <input 
                                    type="number" 
                                    v-model="form.qty_reject" 
                                    placeholder="0"
                                    class="w-full bg-transparent border-none text-center text-3xl font-bold text-red-400 focus:ring-0 p-2"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="text-center bg-white/5 rounded-xl p-3">
                        <span class="text-slate-400 text-sm">Inspector:</span>
                        <strong class="text-white ml-2">{{ form.nik_qc || '-' }}</strong>
                    </div>
                </div>

                <div class="p-4 bg-slate-900/50 flex gap-3">
                    <button @click="showModal = false" class="flex-1 py-3.5 rounded-xl border border-slate-600 text-slate-400 font-bold hover:bg-slate-700 hover:text-white transition-colors">
                        Batal
                    </button>
                    <button 
                        @click="submitFinal" 
                        :disabled="form.processing"
                        class="flex-1 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.grid-pattern {
    background-image: linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px), 
                      linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
}
/* Hide number input spinners */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

// --- LOGIC ---
// Menggunakan Inertia Form Helper untuk manajemen state otomatis
const form = useForm({
    username: '', // SAP User ID
    password: '',
    nik: '',
});

const showPassword = ref(false);

const submit = () => {
    // Post ke route login Laravel. 
    // Backend akan menangani validasi & redirect.
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="QM-APP Login" />

    <div class="relative min-h-screen flex items-center justify-center bg-slate-900 overflow-hidden font-sans selection:bg-emerald-500 selection:text-white">

        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0a3d2e]"></div>
            
            <div class="absolute -top-[20%] -right-[20%] w-[600px] h-[600px] bg-emerald-500 rounded-full blur-[80px] opacity-15 animate-float-1"></div>
            <div class="absolute -bottom-[20%] -left-[10%] w-[500px] h-[500px] bg-emerald-600 rounded-full blur-[80px] opacity-15 animate-float-2"></div>
            <div class="absolute top-[40%] left-[40%] w-[300px] h-[300px] bg-emerald-400 rounded-full blur-[80px] opacity-15 -translate-x-1/2 -translate-y-1/2 animate-float-3"></div>
            
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 w-full max-w-[480px] px-5 sm:px-0 animate-fade-in-up">
            
            <div class="bg-slate-800/70 backdrop-blur-[20px] border border-white/10 border-t-white/20 rounded-3xl p-8 sm:p-10 shadow-2xl shadow-black/50">
                
                <div class="text-center mb-9">
                    <div class="relative w-[90px] h-[90px] mx-auto mb-5 flex items-center justify-center bg-white/5 rounded-[22px] border border-white/10 shadow-lg group">
                        <div class="absolute inset-0 rounded-[22px] bg-radial-glow opacity-60 blur-md"></div>
                        <img src="/images/KMI.png" alt="Logo KMI" class="w-[60px] h-auto relative z-10 drop-shadow-md" />
                    </div>
                    
                    <div class="space-y-1">
                        <h2 class="text-white text-[1.8rem] font-extrabold tracking-tight">Welcome Back</h2>
                        <p class="text-slate-400 text-[0.95rem]">KMI Quality Inspection System</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    
                    <div v-if="Object.keys(form.errors).length > 0" class="flex items-center gap-3 p-3 px-4 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300 text-sm animate-shake">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>
                            {{ form.errors.username || form.errors.password || 'Login Gagal. Periksa User ID/Password.' }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2 ml-1">SAP User ID</label>
                        <div class="group relative flex items-center bg-[#0f172a]/60 border border-white/10 rounded-[14px] transition-all duration-300 focus-within:border-emerald-500 focus-within:bg-[#0f172a]/80 focus-within:shadow-[0_0_0_4px_rgba(16,185,129,0.15)] overflow-hidden">
                            <div class="w-12 h-12 flex items-center justify-center text-slate-500 text-lg border-r border-white/5 transition-colors duration-300 group-focus-within:text-emerald-500">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input 
                                v-model="form.username"
                                type="text" 
                                class="w-full bg-transparent border-none text-white px-4 py-3.5 outline-none placeholder-slate-500 text-[0.95rem] focus:ring-0"
                                placeholder="Masukan User ID SAP..."
                                required
                                autofocus
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2 ml-1">Password</label>
                        <div class="group relative flex items-center bg-[#0f172a]/60 border border-white/10 rounded-[14px] transition-all duration-300 focus-within:border-emerald-500 focus-within:bg-[#0f172a]/80 focus-within:shadow-[0_0_0_4px_rgba(16,185,129,0.15)] overflow-hidden">
                            <div class="w-12 h-12 flex items-center justify-center text-slate-500 text-lg border-r border-white/5 transition-colors duration-300 group-focus-within:text-emerald-500">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input 
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full bg-transparent border-none text-white px-4 py-3.5 outline-none placeholder-slate-500 text-[0.95rem] focus:ring-0"
                                placeholder="Masukan Password..."
                                required
                            />
                            <button type="button" @click="showPassword = !showPassword" class="h-12 px-4 text-slate-500 hover:text-slate-300 transition-colors focus:outline-none">
                                <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 text-sm font-semibold mb-2 ml-1">NIK Karyawan</label>
                        <div class="group relative flex items-center bg-[#0f172a]/60 border border-white/10 rounded-[14px] transition-all duration-300 focus-within:border-emerald-500 focus-within:bg-[#0f172a]/80 focus-within:shadow-[0_0_0_4px_rgba(16,185,129,0.15)] overflow-hidden">
                            <div class="w-12 h-12 flex items-center justify-center text-slate-500 text-lg border-r border-white/5 transition-colors duration-300 group-focus-within:text-emerald-500">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <input 
                                v-model="form.nik"
                                type="text" 
                                class="w-full bg-transparent border-none text-white px-4 py-3.5 outline-none placeholder-slate-500 text-[0.95rem] focus:ring-0"
                                placeholder="Contoh: 10000096"
                                required
                            />
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-3.5 rounded-[14px] bg-gradient-to-br from-emerald-500 to-emerald-600 text-white font-bold text-base shadow-lg shadow-emerald-500/30 transition-all duration-300 hover:from-emerald-400 hover:to-emerald-600 hover:-translate-y-[2px] hover:shadow-emerald-500/40 disabled:opacity-70 disabled:cursor-not-allowed disabled:grayscale disabled:hover:translate-y-0"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                <span>Authenticating...</span>
                            </span>
                            <span v-else class="flex items-center gap-2">
                                Masuk Sistem <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </div>
                    </button>

                </form>

                <div class="mt-8 pt-5 border-t border-white/5 text-center">
                    <p class="text-slate-500 text-xs">
                        &copy; 2025 IT Team - PT. Kayu Mebel Indonesia
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Animations & Utilities 
  Beberapa hal lebih bersih ditulis di sini daripada memaksakan 
  tailwind.config.js yang kompleks untuk satu halaman.
*/

/* 1. Grid Pattern Animation */
.grid-pattern {
    background-image: linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px), 
                      linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: gridMove 20s linear infinite;
}

/* 2. Glow untuk Logo */
.bg-radial-glow {
    background: radial-gradient(circle, rgba(16, 185, 129, 0.4) 0%, transparent 70%);
}

/* 3. Keyframes (Sama persis dengan original) */
@keyframes float { 
    0%, 100% { transform: translate(0, 0); } 
    50% { transform: translate(30px, -30px); } 
}
@keyframes gridMove { 
    0% { transform: translate(0, 0); } 
    100% { transform: translate(50px, 50px); } 
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

/* 4. Utility Classes for Animations */
.animate-float-1 { animation: float 20s ease-in-out infinite; }
.animate-float-2 { animation: float 20s ease-in-out infinite 5s; } /* Delay 5s */
.animate-float-3 { animation: float 20s ease-in-out infinite 10s; } /* Delay 10s */
.animate-fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1); }
.animate-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
</style>
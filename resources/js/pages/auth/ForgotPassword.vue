<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowRight } from '@lucide/vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Kata Sandi - CITECH 2026" />

    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-2 sm:p-6 md:p-8 font-sans">
        <div class="bg-white rounded-3xl w-full max-w-7xl shadow-[0_25px_60px_rgba(0,0,0,0.08)] border border-slate-200/50 p-2.5 md:p-3 flex flex-col lg:flex-row gap-4 md:gap-10 min-h-[720px] transition-all duration-300 animate-card">
            
            <!-- Left Side Panel (Blue Gradient Banner) -->
            <div class="lg:w-[55%] bg-gradient-to-br from-[#1e3c72] via-[#2a5298] to-[#1e4d8c] rounded-2xl p-8 md:p-12 text-white flex flex-col justify-end relative overflow-hidden min-h-[350px] lg:min-h-0 animate-left-panel">
                <!-- Decorative Glow Blobs -->
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-white/5 blur-2xl"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl"></div>
                
                <!-- Brand Content -->
                <div class="relative z-10 space-y-4">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Carnival Technology</h2>
                    <h3 class="text-xl md:text-2xl font-bold text-[#eab308]">Wujudkan Ide, Ciptakan Inovasi</h3>
                    <p class="text-white/80 text-xs md:text-sm leading-relaxed font-medium">
                        Lupa kata sandi Anda? Tidak masalah. Beritahu kami alamat email Anda dan kami akan mengirimkan email berisi tautan penyetelan ulang kata sandi yang memungkinkan Anda memilih kata sandi baru.
                    </p>
                </div>
            </div>

            <!-- Right Side Panel (Form) -->
            <div class="lg:w-[45%] flex flex-col justify-center px-4 md:px-8 py-6 animate-right-panel">
                <div class="w-full max-w-md mx-auto space-y-8">
                    
                    <!-- Header Text -->
                    <div class="space-y-2 animate-fade-in-up">
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Lupa Kata Sandi</h1>
                        <p class="text-slate-400 text-xs md:text-sm font-medium">Masukkan email terdaftar untuk menerima link setel ulang kata sandi.</p>
                    </div>

                    <!-- Session Status Alert -->
                    <div v-if="status" class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl text-sm font-semibold animate-fade-in-up delay-100">
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        
                        <!-- Email Input -->
                        <div class="space-y-2 animate-fade-in-up delay-100">
                            <label for="email" class="text-sm font-bold text-slate-800 block">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <Mail class="w-5 h-5" />
                                </span>
                                <input 
                                    id="email" 
                                    type="email" 
                                    v-model="form.email" 
                                    placeholder="Masukkan email kamu" 
                                    required 
                                    autofocus 
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-[#1e4d8c] transition duration-300 text-sm font-medium"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-[#1e4d8c] hover:bg-[#153a6b] text-white font-bold text-sm py-3.5 rounded-xl transition duration-300 shadow-md hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.98] disabled:opacity-50 flex items-center justify-center space-x-2 animate-fade-in-up delay-200"
                        >
                            <span>Kirim Link Setel Ulang</span>
                            <ArrowRight class="w-4 h-4" />
                        </button>
                    </form>

                    <!-- Links -->
                    <div class="pt-4 text-center animate-fade-in-up delay-300">
                        <p class="text-xs font-bold text-slate-500">
                            Sudah ingat password? 
                            <Link :href="route('login')" class="text-blue-600 hover:text-blue-800 hover:underline font-bold">
                                Masuk Sekarang
                            </Link>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@keyframes cardPop {
    from { opacity: 0; transform: scale(0.97) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-32px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(32px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-card {
    animation: cardPop 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.animate-left-panel {
    animation: slideInLeft 1.4s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
    opacity: 0;
}
.animate-right-panel {
    animation: slideInRight 1.4s cubic-bezier(0.16, 1, 0.3, 1) 0.25s forwards;
    opacity: 0;
}
.animate-fade-in-up {
    animation: fadeInUp 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}
.delay-100 {
    animation-delay: 0.1s;
}
.delay-200 {
    animation-delay: 0.2s;
}
.delay-300 {
    animation-delay: 0.3s;
}
</style>

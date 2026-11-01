<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowLeft, SendHorizonal, AlertCircle, KeyRound } from 'lucide-vue-next';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Lupa Password - LilieFlow" />

    <div class="min-h-screen flex flex-col md:flex-row bg-[#FBF9F9]">
        <!-- Left Side: Brand Visual -->
        <div class="concentric-bg hidden md:flex md:w-5/12 lg:w-9/20 flex-col justify-between p-12 lg:p-16 text-white relative">
            <!-- Top: Brand Logo -->
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md p-1.5 flex items-center justify-center border border-white/20 shadow-inner">
                    <img
                        src="/logo_liliebelle.png"
                        alt="LilieFlow Logo"
                        class="w-full h-full object-contain rounded-lg"
                        onerror="this.onerror=null; this.src='/images/logo_liliebelle.png';"
                    />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white leading-none">LilieFlow</h1>
                    <span class="text-xs text-white/70 font-medium tracking-wide">Operational workspace</span>
                </div>
            </div>

            <!-- Middle: Slogan -->
            <div class="relative z-10 my-auto py-12">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.18] max-w-md">
                    Kami bantu kamu masuk kembali ke sistem.
                </h2>
                <p class="text-white/60 text-base mt-4 max-w-xs leading-relaxed">
                    Hubungi Administrator agar akses Anda segera dipulihkan.
                </p>
            </div>

            <!-- Bottom -->
            <div class="relative z-10 text-xs text-white/40 tracking-widest font-medium uppercase">
                PT LILIE BELLE
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="flex-1 flex items-center justify-center p-6 md:p-12">
            <div class="w-full max-w-md">

                <!-- Back to login -->
                <Link
                    href="/login"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#7A1228] hover:text-[#600d1e] mb-8 transition-colors"
                >
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Kembali ke halaman login
                </Link>

                <!-- Header -->
                <div class="mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-[#FDF1F3] flex items-center justify-center mb-4 border border-[#F5D5DC]">
                        <KeyRound class="w-5 h-5 text-[#7A1228]" />
                    </div>
                    <p class="text-xs font-bold tracking-widest text-[#7A1228] uppercase mb-1">RESET AKSES</p>
                    <h2 class="text-2xl font-extrabold text-[#2A0711] tracking-tight">Lupa Password?</h2>
                    <p class="text-sm text-[#6C555C] mt-2 leading-relaxed">
                        Masukkan email yang terdaftar. Administrator akan memproses permintaan reset password Anda secepatnya.
                    </p>
                </div>

                <!-- Error global -->
                <div
                    v-if="form.errors.email || Object.keys(form.errors).length > 0"
                    class="mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200"
                >
                    <AlertCircle class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                    <p class="text-xs text-rose-700 font-medium">
                        {{ form.errors.email || 'Terjadi kesalahan, silakan coba lagi.' }}
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-[#443339] mb-1.5">
                            Alamat Email <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9E8B90]" />
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="nama@liliebelle.co.id"
                                :class="[
                                    'w-full pl-10 pr-4 py-3 rounded-xl border text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]',
                                    form.errors.email
                                        ? 'border-rose-300 bg-rose-50'
                                        : 'border-[#DFD1D5] bg-white hover:border-[#C4A8B0]'
                                ]"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Info box -->
                    <div class="flex items-start gap-2.5 rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-3">
                        <span class="text-amber-500 text-base leading-none mt-0.5">ℹ️</span>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Permintaan akan diteruskan ke <strong>Administrator</strong>. Setelah diproses, Anda akan dihubungi untuk mendapatkan password baru.
                        </p>
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        :disabled="form.processing || !form.email"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#7A1228] hover:bg-[#600d1e] text-white text-sm font-bold tracking-wide shadow-md transition-all disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <SendHorizonal v-else class="w-4 h-4" />
                        <span>{{ form.processing ? 'Mengirim...' : 'Kirim Permintaan Reset' }}</span>
                    </button>
                </form>

                <!-- Help text -->
                <p class="text-xs text-center text-[#9E8B90] mt-6 leading-relaxed">
                    Ingat password Anda?
                    <Link href="/login" class="text-[#7A1228] font-semibold hover:underline ml-0.5">Masuk di sini</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.concentric-bg {
    background: radial-gradient(ellipse at 70% 40%, #9a1535 0%, #7A1228 40%, #4A0D1B 100%);
}

.concentric-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-radial-gradient(
        circle at 70% 50%,
        transparent 10%,
        rgba(255,255,255,0.03) 11%,
        transparent 12%
    );
}
</style>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { User, Lock, Eye, EyeOff, AlertCircle, ShieldAlert, CheckCircle2 } from 'lucide-vue-next';

const page = usePage();
const flashError   = computed(() => page.props.flash?.error   || null);
const flashSuccess = computed(() => page.props.flash?.success || null);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const fillAdminCredentials = () => {
    form.email = 'admin@liliebelle.co.id';
    form.password = 'password';
};

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke LilieFlow" />

    <div class="min-h-screen flex flex-col md:flex-row bg-[#FBF9F9]">
        <!-- Left Side: Brand Visual & concentric rings -->
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
                    <h1 class="text-2xl font-bold tracking-tight text-white leading-none">
                        LilieFlow
                    </h1>
                    <span class="text-xs text-white/70 font-medium tracking-wide">
                        Operational workspace
                    </span>
                </div>
            </div>

            <!-- Middle: Slogan matching design exactly -->
            <div class="relative z-10 my-auto py-12">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.18] max-w-md">
                    Setiap pesanan, terpantau dari awal hingga selesai.
                </h2>
            </div>

            <!-- Bottom: PT Lilie Belle signature -->
            <div class="relative z-10">
                <p class="text-xs font-bold tracking-[0.2em] text-white/80 uppercase">
                    PT LILIE BELLE
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 md:px-16 lg:px-24 py-12 bg-white">
            <div class="max-w-md w-full mx-auto">
                <!-- Mobile Logo header -->
                <div class="flex md:hidden items-center gap-3 mb-8">
                    <img src="/logo_liliebelle.png" alt="LilieFlow Logo" class="w-10 h-10 object-contain rounded-lg border border-[#F5D5DC]" />
                    <div>
                        <div class="text-xl font-bold text-[#4A0D1B]">LilieFlow</div>
                        <div class="text-xs text-[#9E8B90]">Operational workspace</div>
                    </div>
                </div>

                <!-- Section Subtitle & Heading -->
                <div class="mb-8">
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase inline-block mb-2">
                        SELAMAT DATANG
                    </span>
                    <h2 class="text-3xl font-extrabold text-[#2A0711] tracking-tight">
                        Masuk ke LilieFlow
                    </h2>
                    <p class="mt-2 text-sm text-[#736368] leading-relaxed">
                        Gunakan akun yang telah didaftarkan oleh administrator.
                    </p>
                </div>

                <!-- Quick Demo Login Helper Card -->
                <div class="mb-6 p-3.5 rounded-xl bg-[#FDF1F3] border border-[#F7D2DA] flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-[#7A1228] block">Akun Administrator Demo:</span>
                        <span class="text-[#5A3F46]">admin@liliebelle.co.id / password</span>
                    </div>
                    <button
                        type="button"
                        @click="fillAdminCredentials"
                        class="px-3 py-1.5 rounded-lg bg-[#7A1228] text-white font-semibold text-xs hover:bg-[#600d1e] transition-colors shrink-0 ml-2"
                    >
                        Isi Cepat
                    </button>
                </div>

                <!-- Flash Error (dari middleware / session) -->
                <div v-if="flashError" class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-2.5">
                    <ShieldAlert class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-rose-700">Akses Ditolak</p>
                        <p class="text-xs mt-0.5 leading-relaxed">{{ flashError }}</p>
                    </div>
                </div>

                <!-- Flash Success -->
                <div v-if="flashSuccess" class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2.5">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" />
                    <p>{{ flashSuccess }}</p>
                </div>

                <!-- Error Alert if any -->
                <div v-if="form.errors.email || form.errors.password" class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-2.5">
                    <AlertCircle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
                    <div>
                        <p v-if="form.errors.email">{{ form.errors.email }}</p>
                        <p v-if="form.errors.password">{{ form.errors.password }}</p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#443339] mb-1.5">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                <User class="w-5 h-5" />
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="nama@liliebelle.co.id"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                                :class="{ 'border-rose-400': form.errors.email }"
                            />
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#443339] mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                <Lock class="w-5 h-5" />
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan password akun"
                                class="w-full pl-11 pr-11 py-3 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                                :class="{ 'border-rose-400': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9E8B90] hover:text-[#7A1228] transition-colors"
                            >
                                <EyeOff v-if="showPassword" class="w-5 h-5" />
                                <Eye v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 rounded text-[#7A1228] border-gray-300 focus:ring-[#7A1228] transition"
                            />
                            <span class="text-sm text-[#5C4A50]">Ingat saya</span>
                        </label>

                        <Link href="/forgot-password" class="text-sm font-semibold text-[#7A1228] hover:underline">
                            Lupa Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 px-4 rounded-xl bg-[#7A1228] hover:bg-[#630c1f] active:bg-[#4d0817] text-white font-semibold text-sm shadow-md transition-all duration-150 disabled:opacity-60 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Masuk ke aplikasi</span>
                        </button>
                    </div>
                </form>

                <!-- Footer Register Link -->
                <div class="mt-8 text-center text-sm text-[#736368]">
                    <span>Belum punya akun terdaftar? </span>
                    <Link href="/register" class="font-bold text-[#7A1228] hover:underline ml-1">
                        Daftar akun baru
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

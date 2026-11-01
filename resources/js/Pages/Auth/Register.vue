<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, AlertCircle, CheckCircle2, ChevronDown } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    requested_role: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const toggleConfirmPasswordVisibility = () => {
    showConfirmPassword.value = !showConfirmPassword.value;
};

// Password strength calculation
const hasMinLength = computed(() => form.password.length >= 8);
const hasUpperCase = computed(() => /[A-Z]/.test(form.password));
const hasNumber = computed(() => /[0-9]/.test(form.password));
const hasSymbol = computed(() => /[^A-Za-z0-9]/.test(form.password));

const strengthScore = computed(() => {
    if (!form.password) return 0;
    let score = 0;
    if (hasMinLength.value) score++;
    if (hasUpperCase.value) score++;
    if (hasNumber.value) score++;
    if (hasSymbol.value) score++;
    return score;
});

const strengthLabel = computed(() => {
    if (!form.password) return 'Belum Terisi';
    switch (strengthScore.value) {
        case 1:
            return 'Sangat Lemah';
        case 2:
            return 'Lemah';
        case 3:
            return 'Sedang';
        case 4:
            return 'Kuat';
        default:
            return 'Belum Terisi';
    }
});

const strengthColor = computed(() => {
    switch (strengthScore.value) {
        case 1:
            return 'bg-red-500';
        case 2:
            return 'bg-amber-500';
        case 3:
            return 'bg-yellow-500';
        case 4:
            return 'bg-emerald-600';
        default:
            return 'bg-[#EADBE0]';
    }
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Buat Akun Baru - LilieFlow" />

    <div class="min-h-screen flex flex-col md:flex-row bg-[#FBF9F9]">
        <!-- Left Side: Brand Visual -->
        <div class="concentric-bg hidden md:flex md:w-5/12 lg:w-9/20 flex-col justify-between p-12 lg:p-16 text-white relative">
            <!-- Brand Logo -->
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

            <!-- Middle Slogan -->
            <div class="relative z-10 my-auto py-12">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.18] max-w-md">
                    Setiap pesanan, terpantau dari awal hingga selesai.
                </h2>
            </div>

            <!-- Bottom Brand -->
            <div class="relative z-10">
                <p class="text-xs font-bold tracking-[0.2em] text-white/80 uppercase">
                    PT LILIE BELLE
                </p>
            </div>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 md:px-16 lg:px-20 py-10 bg-white overflow-y-auto">
            <div class="max-w-md w-full mx-auto my-auto">
                <!-- Mobile Logo header -->
                <div class="flex md:hidden items-center gap-3 mb-6">
                    <img src="/logo_liliebelle.png" alt="LilieFlow Logo" class="w-9 h-9 object-contain rounded-lg border border-[#F5D5DC]" />
                    <div>
                        <div class="text-xl font-bold text-[#4A0D1B]">LilieFlow</div>
                        <div class="text-xs text-[#9E8B90]">Operational workspace</div>
                    </div>
                </div>

                <!-- Heading -->
                <div class="mb-6">
                    <h2 class="text-3xl font-extrabold text-[#2A0711] tracking-tight">
                        Buat Akun Baru
                    </h2>
                    <p class="mt-2 text-sm text-[#736368] leading-relaxed">
                        Daftarkan data diri Anda untuk pengajuan akses ke portal terpadu PT Lilie Belle.
                    </p>
                </div>

                <!-- Validation Alerts -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <AlertCircle class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>Mohon periksa data yang Anda masukkan:</span>
                    </div>
                    <p v-for="(error, key) in form.errors" :key="key" class="pl-6">
                        • {{ error }}
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-[#443339] mb-1">
                            Nama Lengkap <span class="text-rose-600">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Contoh: Budi Prasetyo"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                            :class="{ 'border-rose-400': form.errors.name }"
                        />
                    </div>

                    <!-- Email Perusahaan -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-[#443339] mb-1">
                            Email Perusahaan <span class="text-rose-600">*</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="nama@liliebelle.co.id"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                            :class="{ 'border-rose-400': form.errors.email }"
                        />
                    </div>

                    <!-- Permohonan Unit / Role -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="requested_role" class="text-xs font-bold text-[#443339]">
                                Permohonan Unit / Role
                            </label>
                            <span class="text-[11px] text-[#A6243E] font-medium">
                                Keputusan final oleh Administrator
                            </span>
                        </div>
                        <div class="relative">
                            <select
                                id="requested_role"
                                v-model="form.requested_role"
                                required
                                class="w-full appearance-none px-4 py-2.5 rounded-xl border border-[#DFD1D5] text-[#2A0711] bg-white focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm cursor-pointer pr-10"
                                :class="{ 'text-[#B5A3A8]': !form.requested_role, 'border-rose-400': form.errors.requested_role }"
                            >
                                <option value="" disabled selected>Pilih Unit/Role</option>
                                <option value="admin_toko" class="text-gray-800">Admin Toko</option>
                                <option value="admin_gudang" class="text-gray-800">Admin Gudang</option>
                                <option value="admin_konveksi" class="text-gray-800">Admin Konveksi</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9E8B90]">
                                <ChevronDown class="w-4 h-4" />
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-[#443339] mb-1">
                            Password <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full px-4 pr-11 py-2.5 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                                :class="{ 'border-rose-400': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9E8B90] hover:text-[#7A1228] transition-colors"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Password Strength Meter Box (Matching screenshot 3) -->
                    <div class="p-3.5 rounded-xl bg-[#FDF2F5] border border-[#F6D0D8] space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#5A3F46]">Tingkat Keamanan:</span>
                            <span class="font-bold text-[#7A1228]">{{ strengthLabel }}</span>
                        </div>

                        <!-- 4 Progress Segments -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <div
                                v-for="i in 4"
                                :key="i"
                                class="h-1.5 rounded-full transition-colors duration-200"
                                :class="[i <= strengthScore ? strengthColor : 'bg-[#EBD5DB]']"
                            ></div>
                        </div>

                        <!-- 4 Criteria Checklist -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-[11px] text-[#695057]">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                    :class="hasMinLength ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-[#C2ABB2] bg-white'"
                                >
                                    <span v-if="hasMinLength" class="text-[9px] font-bold">✓</span>
                                </span>
                                <span>Min 8 karakter</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span
                                    class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                    :class="hasUpperCase ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-[#C2ABB2] bg-white'"
                                >
                                    <span v-if="hasUpperCase" class="text-[9px] font-bold">✓</span>
                                </span>
                                <span>Huruf besar</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span
                                    class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                    :class="hasNumber ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-[#C2ABB2] bg-white'"
                                >
                                    <span v-if="hasNumber" class="text-[9px] font-bold">✓</span>
                                </span>
                                <span>Angka</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span
                                    class="w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 transition-colors"
                                    :class="hasSymbol ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-[#C2ABB2] bg-white'"
                                >
                                    <span v-if="hasSymbol" class="text-[9px] font-bold">✓</span>
                                </span>
                                <span>Simbol</span>
                            </div>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-[#443339] mb-1">
                            Konfirmasi Password <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                required
                                placeholder="Masukkan ulang password"
                                class="w-full px-4 pr-11 py-2.5 rounded-xl border border-[#DFD1D5] text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all text-sm"
                            />
                            <button
                                type="button"
                                @click="toggleConfirmPasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9E8B90] hover:text-[#7A1228] transition-colors"
                            >
                                <EyeOff v-if="showConfirmPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                v-model="form.terms"
                                required
                                class="w-4 h-4 rounded text-[#7A1228] border-gray-300 focus:ring-[#7A1228] mt-0.5"
                            />
                            <span class="text-xs text-[#5C4A50] leading-snug">
                                Saya menyetujui <span class="font-bold text-[#7A1228]">Ketentuan Penggunaan</span> dan <span class="font-bold text-[#7A1228]">Kebijakan Privasi PT Lilie Belle</span>.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button
                            type="submit"
                            :disabled="form.processing || !form.terms"
                            class="w-full py-3.5 px-4 rounded-xl bg-[#7A1228] hover:bg-[#630c1f] active:bg-[#4d0817] text-white font-semibold text-sm shadow-md transition-all duration-150 disabled:opacity-60 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Daftar Akun</span>
                        </button>
                    </div>
                </form>

                <!-- Footer Link -->
                <div class="mt-6 text-center text-xs text-[#736368]">
                    <span>Sudah memiliki akun? </span>
                    <Link href="/login" class="font-bold text-[#7A1228] hover:underline ml-1">
                        Masuk
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TokoLayout from '@/Layouts/TokoLayout.vue';
import {
    User,
    Mail,
    Phone,
    Lock,
    CheckCircle2,
    Shield,
    ShieldCheck,
    KeyRound,
    Eye,
    EyeOff,
    Calendar,
    BadgeCheck,
    LockKeyhole,
    Activity,
    Store
} from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Live password strength calculation
const passwordStrength = computed(() => {
    const pwd = form.new_password || '';
    if (!pwd) return { score: 0, text: '', color: '', width: 'w-0' };
    let score = 0;
    if (pwd.length >= 8) score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;

    switch (score) {
        case 1:
            return { score: 1, text: 'Sangat Lemah', color: 'bg-rose-500', textColor: 'text-rose-600', width: 'w-1/4' };
        case 2:
            return { score: 2, text: 'Cukup', color: 'bg-amber-500', textColor: 'text-amber-600', width: 'w-2/4' };
        case 3:
            return { score: 3, text: 'Kuat', color: 'bg-emerald-500', textColor: 'text-emerald-600', width: 'w-3/4' };
        case 4:
            return { score: 4, text: 'Sangat Kuat', color: 'bg-emerald-600', textColor: 'text-emerald-600', width: 'w-full' };
        default:
            return { score: 0, text: 'Terlalu Pendek (Min 8 Karakter)', color: 'bg-rose-400', textColor: 'text-rose-500', width: 'w-1/12' };
    }
});

const submit = () => {
    form.put('/toko/profile', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
            showCurrentPassword.value = false;
            showNewPassword.value = false;
            showConfirmPassword.value = false;
        },
    });
};
</script>

<template>
    <TokoLayout title="Profil" :breadcrumbs="['Profil']">
        <Head title="Profil Admin Toko - LilieFlow" />

        <div class="space-y-6 max-w-6xl">
            <!-- Header Section -->
            <div>
                <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase">
                    PENGATURAN AKUN TOKO
                </span>
                <h1 class="text-3xl font-extrabold text-[#2A0711] tracking-tight mt-1">
                    Profil Pengguna
                </h1>
                <p class="text-sm text-[#736368] mt-1">
                    Kelola data identitas diri dan pengaturan keamanan akun operasional Toko Lilie Belle.
                </p>
            </div>

            <!-- Profile Hero Banner Card (Sama Persis seperti Admin) -->
            <div class="bg-white rounded-3xl border border-[#F0E6E8] shadow-xs overflow-hidden">
                <!-- Decorative Cover Gradient -->
                <div class="h-32 sm:h-36 bg-gradient-to-r from-[#7A1228] via-[#8E1B33] to-[#590B1B] relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                    <div class="absolute bottom-0 left-1/3 w-64 h-32 rounded-full bg-rose-400/10 blur-xl pointer-events-none"></div>

                    <div class="absolute right-6 bottom-3 text-white/15 font-black text-2xl tracking-widest select-none uppercase hidden sm:block">
                        LilieFlow Toko
                    </div>
                </div>

                <!-- Profile Info Row Overlapping Cover -->
                <div class="px-6 sm:px-8 pb-6 pt-0">
                    <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 -mt-14 sm:-mt-16">
                        <!-- Avatar & Core Info -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                            <div class="relative">
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white p-1.5 shadow-md shrink-0">
                                    <div class="w-full h-full rounded-xl bg-gradient-to-br from-[#FCE7EB] to-[#F5D0D8] text-[#7A1228] text-3xl sm:text-4xl font-black flex items-center justify-center border border-[#F0B8C4] shadow-inner">
                                        {{ user.initials || 'NP' }}
                                    </div>
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold flex items-center gap-1 shadow-xs"
                                    title="Status akun aktif saat ini"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Aktif</span>
                                </div>
                            </div>

                            <div class="pt-2 sm:pt-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h2 class="text-xl sm:text-2xl font-black text-[#2A0711] tracking-tight">
                                        {{ user.name }}
                                    </h2>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FDF1F3] text-[#7A1228] border border-[#F6D0D8]">
                                        <Store class="w-3.5 h-3.5" />
                                        <span>Admin Toko</span>
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#736368] mt-1.5">
                                    <span class="flex items-center gap-1 text-[#5A454C]">
                                        <Mail class="w-3.5 h-3.5 text-[#9E8B90]" />
                                        <span>{{ user.email }}</span>
                                    </span>

                                    <span v-if="user.phone" class="flex items-center gap-1 text-[#5A454C]">
                                        <Phone class="w-3.5 h-3.5 text-[#9E8B90]" />
                                        <span>{{ user.phone }}</span>
                                    </span>

                                    <span class="flex items-center gap-1 text-[#8C7A81]">
                                        <Calendar class="w-3.5 h-3.5 text-[#9E8B90]" />
                                        <span>Bergabung {{ user.formatted_created_at || 'Juni 2025' }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Pill: Security Status (Dibatasi untuk Admin Toko) -->
                        <div class="self-stretch sm:self-end flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-0 border-[#F5ECF0]">
                            <div class="text-left sm:text-right">
                                <span class="block text-[11px] font-bold text-[#8C7A81] uppercase tracking-wider">
                                    Tingkat Akses
                                </span>
                                <span class="text-xs font-bold text-[#7A1228]">
                                    Operasional Toko & Pesanan
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-[#FDF1F3] border border-[#F6D0D8] text-[#7A1228] flex items-center justify-center shrink-0">
                                <Shield class="w-5 h-5" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Grid: 2-Columns Layout -->
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left Column (4 cols): Ringkasan Keamanan & Info Akun -->
                <div class="lg:col-span-4 space-y-5">
                    <!-- Card 1: Ringkasan Otorisasi -->
                    <div class="bg-white rounded-2xl p-5 border border-[#F0E6E8] shadow-xs space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-[#F0E6E8]">
                            <div class="w-9 h-9 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                                <BadgeCheck class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#2A0711]">Status Akun</h3>
                                <p class="text-[11px] text-[#8C7A81]">Ringkasan kredensial operasional toko</p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between py-1 border-b border-[#FAF0F3]">
                                <span class="text-[#8C7A81]">Peran Sistem</span>
                                <span class="font-bold text-[#7A1228] bg-[#FDF1F3] px-2 py-0.5 rounded-md">
                                    Admin Toko
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-b border-[#FAF0F3]">
                                <span class="text-[#8C7A81]">Status Akun</span>
                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif & Terverifikasi
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1 border-b border-[#FAF0F3]">
                                <span class="text-[#8C7A81]">Sesi Login</span>
                                <span class="font-semibold text-[#2A0711] flex items-center gap-1">
                                    <Activity class="w-3.5 h-3.5 text-emerald-500" />
                                    {{ user.formatted_last_active || 'Aktif saat ini' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-1">
                                <span class="text-[#8C7A81]">ID Akun</span>
                                <span class="font-mono text-xs text-[#5A454C]">#LF-00{{ user.id }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Security Advice Banner Toko -->
                    <div class="bg-gradient-to-br from-[#FFF9FA] to-[#FDF1F3] rounded-2xl p-5 border border-[#F6D0D8] shadow-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#7A1228] text-white flex items-center justify-center shrink-0 shadow-2xs">
                                <LockKeyhole class="w-4 h-4" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-[#2A0711]">Keamanan Akun Admin Toko</h4>
                                <p class="text-[11px] text-[#6C555C] mt-1 leading-relaxed">
                                    Akun Admin Toko bertindak sebagai perwakilan pelanggan dalam pembuatan nota dan negosiasi jadwal konveksi. Pastikan kerahasiaan kata sandi Anda tetap terjaga.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (8 cols): Form Edit Profil & Kata Sandi -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Section 1: Informasi Pribadi & Kontak -->
                    <div class="bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs space-y-5">
                        <div class="flex items-center justify-between pb-4 border-b border-[#F0E6E8]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                                    <User class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#2A0711]">Informasi Pribadi</h3>
                                    <p class="text-xs text-[#8C7A81]">Perbarui nama lengkap, email, dan nomor telepon aktif</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Grid: Nama & Email -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nama Lengkap -->
                                <div>
                                    <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                        Nama Lengkap <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                            <User class="w-4 h-4" />
                                        </div>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            placeholder="Masukkan nama lengkap"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                        />
                                    </div>
                                    <span v-if="form.errors.name" class="text-xs text-rose-600 mt-1 block font-medium">
                                        {{ form.errors.name }}
                                    </span>
                                </div>

                                <!-- Email Perusahaan -->
                                <div>
                                    <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                        Email Toko <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                            <Mail class="w-4 h-4" />
                                        </div>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            required
                                            placeholder="toko@liliebelle.co.id"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                        />
                                    </div>
                                    <span v-if="form.errors.email" class="text-xs text-rose-600 mt-1 block font-medium">
                                        {{ form.errors.email }}
                                    </span>
                                </div>
                            </div>

                            <!-- Nomor Telepon -->
                            <div>
                                <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                    Nomor Telepon / WhatsApp
                                </label>
                                <div class="relative max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                        <Phone class="w-4 h-4" />
                                    </div>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="Contoh: 081234567810"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Keamanan & Ubah Password -->
                    <div class="bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs space-y-5">
                        <div class="flex items-center justify-between pb-4 border-b border-[#F0E6E8]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0">
                                    <KeyRound class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#2A0711]">Pengaturan Kata Sandi</h3>
                                    <p class="text-xs text-[#8C7A81]">Kosongkan seluruh bidang di bawah jika tidak ingin mengganti kata sandi</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Password Saat Ini -->
                            <div>
                                <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                    Password Saat Ini <span v-if="form.new_password" class="text-rose-500">* (Wajib diisi untuk konfirmasi)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                        <LockKeyhole class="w-4 h-4" />
                                    </div>
                                    <input
                                        v-model="form.current_password"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        placeholder="Masukkan password Anda saat ini"
                                        class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                    />
                                    <button
                                        type="button"
                                        @click="showCurrentPassword = !showCurrentPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#9E8B90] hover:text-[#7A1228] cursor-pointer"
                                    >
                                        <Eye v-if="!showCurrentPassword" class="w-4 h-4" />
                                        <EyeOff v-else class="w-4 h-4" />
                                    </button>
                                </div>
                                <span v-if="form.errors.current_password" class="text-xs text-rose-600 mt-1 block font-medium">
                                    {{ form.errors.current_password }}
                                </span>
                            </div>

                            <!-- Grid: Password Baru & Konfirmasi Password Baru -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <!-- Password Baru -->
                                <div>
                                    <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                        Password Baru
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                            <Lock class="w-4 h-4" />
                                        </div>
                                        <input
                                            v-model="form.new_password"
                                            :type="showNewPassword ? 'text' : 'password'"
                                            placeholder="Minimal 8 karakter"
                                            class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                        />
                                        <button
                                            type="button"
                                            @click="showNewPassword = !showNewPassword"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#9E8B90] hover:text-[#7A1228] cursor-pointer"
                                        >
                                            <Eye v-if="!showNewPassword" class="w-4 h-4" />
                                            <EyeOff v-else class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <span v-if="form.errors.new_password" class="text-xs text-rose-600 mt-1 block font-medium">
                                        {{ form.errors.new_password }}
                                    </span>

                                    <!-- Live Password Strength Meter -->
                                    <div v-if="form.new_password" class="mt-2 space-y-1">
                                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                            <div
                                                class="h-full transition-all duration-300"
                                                :class="[passwordStrength.color, passwordStrength.width]"
                                            ></div>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-[#8C7A81]">Kekuatan Sandi:</span>
                                            <span class="font-bold" :class="passwordStrength.textColor">
                                                {{ passwordStrength.text }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Konfirmasi Password Baru -->
                                <div>
                                    <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                        Konfirmasi Password Baru
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                                            <CheckCircle2 class="w-4 h-4" />
                                        </div>
                                        <input
                                            v-model="form.new_password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            placeholder="Ulangi password baru"
                                            class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#DFD1D5] bg-[#FCFBFB] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/15 focus:border-[#7A1228] focus:bg-white transition-all"
                                        />
                                        <button
                                            type="button"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#9E8B90] hover:text-[#7A1228] cursor-pointer"
                                        >
                                            <Eye v-if="!showConfirmPassword" class="w-4 h-4" />
                                            <EyeOff v-else class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#7A1228] hover:bg-[#630c1f] transition shadow-md shadow-[#7A1228]/20 flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                            >
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan Profil' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </TokoLayout>
</template>

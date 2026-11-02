<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import {
    Lock,
    CheckCircle2,
    X,
    Eye,
    EyeOff,
    ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    accountSince: {
        type: String,
        default: 'Januari 2025',
    },
});

// Form Informasi Pribadi
const personalForm = useForm({
    name: props.user.name || 'Siti Rahma',
    email: props.user.email || 'siti@liliebelle.co.id',
    phone: props.user.phone || '0812 3456 7890',
});

// Form Ubah Kata Sandi
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// State Pop-up Modals
const showPasswordModal = ref(false);
const showSuccessModal = ref(false);
const successMessage = ref('');

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const submitPersonalForm = () => {
    personalForm.put('/konveksi/profil', {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Informasi profil Anda telah berhasil diperbarui dan disimpan ke dalam sistem.';
            showSuccessModal.value = true;
        },
    });
};

const submitPasswordForm = () => {
    passwordForm.put('/konveksi/profil/password', {
        preserveScroll: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
            successMessage.value = 'Kata sandi akun Anda telah berhasil diperbarui. Silakan gunakan kata sandi baru untuk login selanjutnya.';
            showSuccessModal.value = true;
        },
    });
};

const closePasswordModal = () => {
    if (passwordForm.processing) return;
    showPasswordModal.value = false;
    passwordForm.reset();
    passwordForm.clearErrors();
};
</script>

<template>
    <Head title="Profil Pengguna - Admin Konveksi" />

    <KonveksiLayout title="Profil">
        <!-- Page Header matching screenshot exactly -->
        <div class="mb-6">
            <h1 class="text-3xl font-extrabold text-[#1F2937] tracking-tight">
                Profil pengguna
            </h1>
            <p class="text-sm text-[#6B7280] mt-1">
                Kelola informasi akun yang digunakan dalam LilieFlow.
            </p>
        </div>

        <!-- Upper Profile Grid (Left Avatar Card & Right Form Card) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- Left Card: Avatar & User Identity (~35%) -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-8 border border-[#F0E6E8] shadow-xs flex flex-col items-center justify-center text-center">
                <!-- Large Round Avatar with Initials -->
                <div class="w-24 h-24 rounded-full bg-[#F6E5E8] text-[#7A1228] font-bold text-2xl flex items-center justify-center mb-4 border border-[#F2D0D8]">
                    {{ user.initials || 'SR' }}
                </div>

                <!-- User Name -->
                <h2 class="text-lg font-bold text-[#1F2937]">
                    {{ user.name }}
                </h2>

                <!-- Role Badge with Dot -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F1F3F5] text-[#495057] text-xs font-medium mt-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#868E96]"></span>
                    <span>{{ user.role_label || 'Admin Konveksi' }}</span>
                </div>

                <!-- Account Active Since Subtext -->
                <p class="text-xs text-[#9E8B90] mt-6">
                    Akun aktif sejak {{ accountSince }}
                </p>
            </div>

            <!-- Right Card: Form Informasi Pribadi (~65%) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-[#F0E6E8] shadow-xs flex flex-col justify-between overflow-hidden">
                <form @submit.prevent="submitPersonalForm" class="flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Header Section -->
                        <div class="p-6 pb-4 border-b border-[#F0E6E8]">
                            <h3 class="text-base font-bold text-[#1F2937]">
                                Informasi pribadi
                            </h3>
                            <p class="text-xs text-[#8E7E84] mt-0.5">
                                Pastikan informasi kontak Anda tetap terkini.
                            </p>
                        </div>

                        <!-- 2x2 Form Fields Grid -->
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                                    Nama lengkap
                                </label>
                                <input
                                    v-model="personalForm.name"
                                    type="text"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-[#1F2937] focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] transition-colors"
                                    required
                                />
                                <p v-if="personalForm.errors.name" class="text-xs text-rose-600 mt-1">
                                    {{ personalForm.errors.name }}
                                </p>
                            </div>

                            <!-- Alamat Email -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                                    Alamat email
                                </label>
                                <input
                                    v-model="personalForm.email"
                                    type="email"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-[#1F2937] focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] transition-colors"
                                    required
                                />
                                <p v-if="personalForm.errors.email" class="text-xs text-rose-600 mt-1">
                                    {{ personalForm.errors.email }}
                                </p>
                            </div>

                            <!-- Nomor Telepon -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                                    Nomor telepon
                                </label>
                                <input
                                    v-model="personalForm.phone"
                                    type="text"
                                    placeholder="0812 3456 7890"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-[#1F2937] focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] transition-colors"
                                />
                                <p v-if="personalForm.errors.phone" class="text-xs text-rose-600 mt-1">
                                    {{ personalForm.errors.phone }}
                                </p>
                            </div>

                            <!-- Peran (Read-only) -->
                            <div>
                                <label class="block text-xs font-semibold text-[#1F2937] mb-1.5">
                                    Peran
                                </label>
                                <input
                                    type="text"
                                    :value="user.role_label || 'Admin Konveksi'"
                                    disabled
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-[#F8F9FA] text-xs font-medium text-[#6C757D] cursor-not-allowed select-none"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Right Action: Simpan Perubahan -->
                    <div class="px-6 py-4 border-t border-[#F0E6E8] flex justify-end">
                        <button
                            type="submit"
                            :disabled="personalForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors disabled:opacity-60 shadow-xs"
                        >
                            {{ personalForm.processing ? 'Menyimpan...' : 'Simpan perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lower Card: Keamanan Akun (matching Image 2) -->
        <div class="mt-6 bg-white rounded-2xl border border-[#F0E6E8] shadow-xs overflow-hidden">
            <div class="p-6 pb-4 border-b border-[#F0E6E8]">
                <h3 class="text-base font-bold text-[#1F2937]">
                    Keamanan akun
                </h3>
                <p class="text-xs text-[#8E7E84] mt-0.5">
                    Kata sandi tidak pernah ditampilkan dalam bentuk teks biasa.
                </p>
            </div>

            <div class="p-6">
                <button
                    type="button"
                    @click="showPasswordModal = true"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-[#1F2937] hover:bg-[#FDF1F3] hover:border-[#7A1228] hover:text-[#7A1228] transition-colors flex items-center gap-2 shadow-2xs"
                >
                    <Lock class="w-4 h-4 text-[#5A4B50]" />
                    <span>Ubah kata sandi</span>
                </button>
            </div>
        </div>

        <!-- Modal Ubah Kata Sandi -->
        <div
            v-if="showPasswordModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#F0E6E8] animate-in fade-in zoom-in-95">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-[#F0E6E8]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                            <Lock class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1F2937]">
                                Ubah Kata Sandi Akun
                            </h3>
                            <p class="text-[11px] text-[#8E7E84]">
                                Amankan akun Anda dengan kata sandi yang kuat.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closePasswordModal"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitPasswordForm" class="py-4 space-y-4">
                    <!-- Kata Sandi Saat Ini -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1F2937] mb-1">
                            Kata Sandi Saat Ini
                        </label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] pr-10"
                                placeholder="Masukkan kata sandi saat ini"
                                required
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeOff v-if="showCurrentPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="text-xs text-rose-600 mt-1">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <!-- Kata Sandi Baru -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1F2937] mb-1">
                            Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] pr-10"
                                placeholder="Minimal 8 karakter"
                                required
                            />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeOff v-if="showNewPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.password" class="text-xs text-rose-600 mt-1">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>

                    <!-- Konfirmasi Kata Sandi Baru -->
                    <div>
                        <label class="block text-xs font-semibold text-[#1F2937] mb-1">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] pr-10"
                                placeholder="Ulangi kata sandi baru"
                                required
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeOff v-if="showConfirmPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="closePasswordModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="px-4 py-2 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors disabled:opacity-60 shadow-xs"
                        >
                            {{ passwordForm.processing ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pop-Up Modal Sukses Disimpan (sesuai permintaan user) -->
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4 animate-in fade-in"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-[#F0E6E8] text-center animate-in zoom-in-95">
                <!-- Icon Centang Hijau Besar -->
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-xs">
                    <CheckCircle2 class="w-9 h-9 stroke-[2.2]" />
                </div>

                <!-- Judul -->
                <h3 class="text-lg font-bold text-[#1F2937] mb-1.5">
                    Perubahan Berhasil Disimpan!
                </h3>

                <!-- Pesan Deskripsi -->
                <p class="text-xs text-[#6B7280] leading-relaxed mb-6">
                    {{ successMessage }}
                </p>

                <!-- Tombol Tutup / Mengerti -->
                <button
                    type="button"
                    @click="showSuccessModal = false"
                    class="w-full py-2.5 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors shadow-xs"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </KonveksiLayout>
</template>

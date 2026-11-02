<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import {
    Lock,
    X,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Form Data Pribadi (Nama, Email, Telepon)
const profileForm = useForm({
    name: props.user.name || 'Raka Pratama',
    email: props.user.email || 'raka@liliebelle.co.id',
    phone: props.user.phone || '0812 3456 7890',
});

const submitProfile = () => {
    profileForm.put('/gudang/profile', {
        preserveScroll: true,
    });
};

// Modal & Form Ubah Kata Sandi
const showPasswordModal = ref(false);

const passwordForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const openPasswordModal = () => {
    passwordForm.name = profileForm.name;
    passwordForm.email = profileForm.email;
    passwordForm.phone = profileForm.phone;
    passwordForm.current_password = '';
    passwordForm.new_password = '';
    passwordForm.new_password_confirmation = '';
    passwordForm.clearErrors();
    showPasswordModal.value = true;
};

const closePasswordModal = () => {
    showPasswordModal.value = false;
    passwordForm.reset();
    passwordForm.clearErrors();
};

const submitPassword = () => {
    passwordForm.name = profileForm.name;
    passwordForm.email = profileForm.email;
    passwordForm.phone = profileForm.phone;

    passwordForm.put('/gudang/profile', {
        preserveScroll: true,
        onSuccess: () => {
            closePasswordModal();
        },
    });
};
</script>

<template>
    <Head title="Profil Pengguna - LilieFlow" />

    <GudangLayout title="Profil" :breadcrumbs="['Profil']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section Sesuai Mockup Figma -->
            <div class="pt-1">
                <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                    AKUN SAYA
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Profil pengguna
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Kelola informasi akun yang digunakan dalam LilieFlow.
                </p>
            </div>

            <!-- Baris Utama: Kartu Avatar (Kiri) & Kartu Informasi Pribadi (Kanan) -->
            <div class="flex flex-col lg:flex-row items-stretch gap-6">
                <!-- Kartu Avatar & Ringkasan Akun Sesuai Figma -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-8 flex flex-col items-center justify-center text-center w-full lg:w-80 shrink-0">
                    <!-- Lingkaran Avatar Inisial RP -->
                    <div class="w-20 h-20 rounded-full bg-[#FDF1F3] text-[#7A1228] font-bold text-xl flex items-center justify-center select-none shadow-2xs">
                        {{ user.initials || 'RP' }}
                    </div>

                    <!-- Nama Pengguna -->
                    <h2 class="text-base font-bold text-slate-900 mt-4">
                        {{ user.name }}
                    </h2>

                    <!-- Pill Role: Admin Gudang -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium text-slate-600 bg-slate-50 border border-slate-100 mt-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span>{{ user.role_label || 'Admin Gudang' }}</span>
                    </div>

                    <!-- Status Akun Aktif -->
                    <p class="text-[11px] text-slate-400 mt-3 font-normal">
                        Akun aktif sejak {{ user.formatted_created_at || 'Januari 2025' }}
                    </p>
                </div>

                <!-- Kartu Form Informasi Pribadi Sesuai Figma -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-7 flex-1 flex flex-col justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Informasi pribadi
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Pastikan informasi kontak Anda tetap terkini.
                        </p>

                        <!-- Form Grid 2x2 -->
                        <form @submit.prevent="submitProfile" class="mt-5 space-y-4">
                            <!-- Baris 1: Nama lengkap & Alamat email -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Nama lengkap
                                    </label>
                                    <input
                                        v-model="profileForm.name"
                                        type="text"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                                    />
                                    <p v-if="profileForm.errors.name" class="text-[11px] text-rose-600 mt-1">
                                        {{ profileForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Alamat email
                                    </label>
                                    <input
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                                    />
                                    <p v-if="profileForm.errors.email" class="text-[11px] text-rose-600 mt-1">
                                        {{ profileForm.errors.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Baris 2: Nomor telepon & Peran -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Nomor telepon
                                    </label>
                                    <input
                                        v-model="profileForm.phone"
                                        type="text"
                                        placeholder="0812 3456 7890"
                                        class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                                    />
                                    <p v-if="profileForm.errors.phone" class="text-[11px] text-rose-600 mt-1">
                                        {{ profileForm.errors.phone }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                        Peran
                                    </label>
                                    <input
                                        type="text"
                                        :value="user.role_label || 'Admin Gudang'"
                                        disabled
                                        class="w-full px-3.5 py-2.5 text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed outline-none select-none"
                                    />
                                </div>
                            </div>

                            <!-- Tombol Simpan Perubahan di Kanan Bawah Sesuai Figma -->
                            <div class="flex justify-end pt-3">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="px-5 py-2.5 bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer disabled:opacity-50"
                                >
                                    <span>{{ profileForm.processing ? 'Menyimpan...' : 'Simpan perubahan' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Baris Kedua: Kartu Keamanan Akun Sesuai Figma -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-7">
                <h2 class="text-sm font-bold text-slate-900">
                    Keamanan akun
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Kata sandi tidak pernah ditampilkan dalam bentuk teks biasa.
                </p>

                <!-- Tombol Ubah Kata Sandi Sesuai Figma -->
                <div class="pt-4">
                    <button
                        type="button"
                        @click="openPasswordModal"
                        class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-2 transition cursor-pointer shadow-2xs"
                    >
                        <Lock class="w-3.5 h-3.5 text-slate-600" />
                        <span>Ubah kata sandi</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Pop-up Ubah Kata Sandi -->
        <div
            v-if="showPasswordModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4 overflow-y-auto"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 transition-all animate-in fade-in zoom-in-95 duration-150">
                <!-- Header Modal -->
                <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Ubah kata sandi
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pastikan kata sandi baru Anda kuat dan aman.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closePasswordModal"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Form Password -->
                <form @submit.prevent="submitPassword" class="space-y-4 mt-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Password Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            placeholder="Masukkan password saat ini"
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                        />
                        <p v-if="passwordForm.errors.current_password" class="text-[11px] text-rose-600 mt-1">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.new_password"
                            type="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                        />
                        <p v-if="passwordForm.errors.new_password" class="text-[11px] text-rose-600 mt-1">
                            {{ passwordForm.errors.new_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.new_password_confirmation"
                            type="password"
                            required
                            placeholder="Ketik ulang password baru"
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                        />
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-3">
                        <button
                            type="button"
                            @click="closePasswordModal"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="px-5 py-2.5 bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer disabled:opacity-50"
                        >
                            <span>{{ passwordForm.processing ? 'Menyimpan...' : 'Simpan kata sandi' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GudangLayout>
</template>

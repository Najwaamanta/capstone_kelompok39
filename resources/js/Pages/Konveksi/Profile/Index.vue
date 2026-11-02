<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import {
    User,
    Mail,
    Phone,
    Shield,
    Lock,
    Save,
    CheckCircle2,
    Building2
} from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put('/konveksi/profil', {
        onSuccess: () => {
            form.reset('current_password', 'password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Profil Saya - Admin Konveksi" />

    <KonveksiLayout title="Profil">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-[#1F2937] tracking-tight">
                Profil Saya
            </h1>
            <p class="text-sm text-[#6B7280] mt-0.5">
                Kelola informasi akun operasional dan kata sandi Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Info Card -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs text-center flex flex-col items-center">
                <div class="w-20 h-20 rounded-full bg-[#F6E5E8] text-[#7A1228] text-2xl font-bold flex items-center justify-center mb-4 border-2 border-[#F2D7DD] shadow-xs">
                    {{ user.initials || 'SR' }}
                </div>
                <h2 class="text-lg font-bold text-[#1F2937]">
                    {{ user.name }}
                </h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FDF1F3] text-[#7A1228] text-xs font-bold mt-2">
                    <Building2 class="w-3.5 h-3.5" />
                    {{ user.role_label }}
                </div>

                <div class="w-full border-t border-gray-100 mt-6 pt-5 text-left space-y-3 text-xs text-gray-600">
                    <div>
                        <span class="text-gray-400 block mb-0.5">Email Perusahaan</span>
                        <span class="font-semibold text-gray-800">{{ user.email }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-0.5">Nomor Telepon</span>
                        <span class="font-semibold text-gray-800">{{ user.phone || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block mb-0.5">Status Akun</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                            <CheckCircle2 class="w-3.5 h-3.5" />
                            Aktif & Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Edit Form -->
            <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs">
                <h3 class="text-base font-bold text-[#1F2937] mb-4 pb-3 border-b border-[#F7EBEF]/60">
                    Perbarui Informasi Akun
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Nama Lengkap
                        </label>
                        <div class="relative">
                            <User class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                                required
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Email
                        </label>
                        <div class="relative">
                            <Mail class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                                required
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Telepon -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Nomor Telepon
                        </label>
                        <div class="relative">
                            <Phone class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="08..."
                                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                            />
                        </div>
                        <p v-if="form.errors.phone" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Section Ganti Password -->
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">
                            Ubah Kata Sandi (Opsional)
                        </h4>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Biarkan kosong jika tidak diubah"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                                />
                                <p v-if="form.errors.password" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Konfirmasi Kata Sandi Baru
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Ulangi kata sandi baru"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2.5 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors flex items-center gap-2 disabled:opacity-60 shadow-xs"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KonveksiLayout>
</template>


<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Users,
    Activity,
    Clock,
    UserX,
    Bell,
    ArrowRight,
    UserCheck,
    CheckCircle2,
    Shield,
    Store,
    Warehouse,
    Scissors,
    XCircle,
    AlertTriangle,
    X
} from 'lucide-vue-next';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    distribusi: {
        type: Array,
        required: true,
    },
    pendingUsers: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    formattedDate: {
        type: String,
        default: 'SENIN, 23 JUNI 2025',
    },
});

const selectedActionUser = ref(null);
const actionType = ref(''); // 'approve' | 'reject'
const isProcessing = ref(false);

const openConfirmModal = (user, type) => {
    selectedActionUser.value = user;
    actionType.value = type;
};

const closeConfirmModal = () => {
    if (isProcessing.value) return;
    selectedActionUser.value = null;
    actionType.value = '';
};

const handleConfirmAction = () => {
    if (!selectedActionUser.value) return;
    isProcessing.value = true;

    const userId = selectedActionUser.value.id;
    const role = selectedActionUser.value.requested_role || selectedActionUser.value.role;

    if (actionType.value === 'approve') {
        router.post(`/admin/users/${userId}/activate`, {
            role,
        }, {
            preserveScroll: true,
            onFinish: () => {
                isProcessing.value = false;
                selectedActionUser.value = null;
                actionType.value = '';
            },
        });
    } else if (actionType.value === 'reject') {
        router.post(`/admin/users/${userId}/reject`, {}, {
            preserveScroll: true,
            onFinish: () => {
                isProcessing.value = false;
                selectedActionUser.value = null;
                actionType.value = '';
            },
        });
    }
};
</script>

<template>
    <AdminLayout title="Dashboard">
        <Head title="Dashboard Administrator - LilieFlow" />

        <div class="space-y-8">
            <!-- Top Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase">
                        ADMINISTRATOR · {{ formattedDate }}
                    </span>
                    <h1 class="text-3xl font-extrabold text-[#2A0711] tracking-tight mt-1">
                        Dashboard Administrator
                    </h1>
                    <p class="text-sm text-[#736368] mt-1">
                        Kelola akun dan peran pengguna sistem.
                    </p>
                </div>

                <Link
                    href="/admin/users"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600d1e] text-white text-sm font-semibold shadow-xs transition-colors self-start sm:self-auto cursor-pointer"
                >
                    <Users class="w-4 h-4" />
                    <span>Kelola pengguna</span>
                </Link>
            </div>

            <!-- Banner: Akun Baru Membutuhkan Aktivasi (Matching screenshot 2) -->
            <div
                v-if="stats.menunggu_aktivasi > 0"
                class="rounded-2xl bg-[#FDF1F3] border border-[#F6D0D8] p-5 sm:p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#7A1228] text-white flex items-center justify-center shrink-0 shadow-sm">
                        <Bell class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#2A0711]">
                            {{ stats.menunggu_aktivasi }} Akun Baru Membutuhkan Aktivasi & Penetapan Role
                        </h3>
                        <p class="text-xs sm:text-sm text-[#6C555C] mt-1 max-w-2xl leading-relaxed">
                            Pengguna telah menyelesaikan pendaftaran mandiri. Otorisasi diperlukan sebelum akses diaktifkan.
                        </p>
                    </div>
                </div>

                <Link
                    href="/admin/users?status=perlu_aktivasi"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600d1e] text-white text-xs sm:text-sm font-semibold transition-colors shrink-0 shadow-xs cursor-pointer"
                >
                    <span>Tinjau Permohonan Sekarang</span>
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>

            <!-- 4 Stat Cards (Matching screenshot 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- 1. PENGGUNA AKTIF -->
                <Link
                    href="/admin/users?status=aktif"
                    class="bg-white rounded-2xl p-5 border border-[#F0E6E8] shadow-xs flex items-center justify-between group hover:border-[#7A1228]/40 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#F4F0FD] text-[#6D28D9] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Users class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-[#8C7A81] uppercase tracking-wider group-hover:text-[#7A1228] transition-colors">
                                PENGGUNA AKTIF
                            </span>
                            <div class="text-3xl font-extrabold text-[#2A0711] my-0.5">
                                {{ stats.pengguna_aktif }}
                            </div>
                            <span class="text-xs text-[#9E8B90]">
                                Pengguna sistem
                            </span>
                        </div>
                    </div>
                    <ChevronRight class="w-4 h-4 text-[#C2B5B8] group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all shrink-0" />
                </Link>

                <!-- 2. AKTIF HARI INI -->
                <Link
                    href="/admin/activity-logs?date=today"
                    class="bg-white rounded-2xl p-5 border border-[#F0E6E8] shadow-xs flex items-center justify-between group hover:border-[#7A1228]/40 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#ECFDF5] text-[#059669] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Activity class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-[#8C7A81] uppercase tracking-wider group-hover:text-[#7A1228] transition-colors">
                                AKTIF HARI INI
                            </span>
                            <div class="text-3xl font-extrabold text-[#2A0711] my-0.5">
                                {{ stats.aktif_hari_ini }}
                            </div>
                            <span class="text-xs text-[#9E8B90]">
                                Terakhir aktif hari ini
                            </span>
                        </div>
                    </div>
                    <ChevronRight class="w-4 h-4 text-[#C2B5B8] group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all shrink-0" />
                </Link>

                <!-- 3. MENUNGGU AKTIVASI -->
                <Link
                    href="/admin/users?status=perlu_aktivasi"
                    class="bg-white rounded-2xl p-5 border border-[#F0E6E8] shadow-xs flex items-center justify-between group hover:border-[#7A1228]/40 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Clock class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-[#8C7A81] uppercase tracking-wider group-hover:text-[#7A1228] transition-colors">
                                MENUNGGU AKTIVASI
                            </span>
                            <div class="text-3xl font-extrabold text-[#2A0711] my-0.5">
                                {{ stats.menunggu_aktivasi }}
                            </div>
                            <span class="text-xs text-[#9E8B90]">
                                Butuh Verifikasi Admin
                            </span>
                        </div>
                    </div>
                    <ChevronRight class="w-4 h-4 text-[#C2B5B8] group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all shrink-0" />
                </Link>

                <!-- 4. AKUN NONAKTIF -->
                <Link
                    href="/admin/users?status=nonaktif"
                    class="bg-white rounded-2xl p-5 border border-[#F0E6E8] shadow-xs flex items-center justify-between group hover:border-[#7A1228]/40 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#FEE2E2] text-[#DC2626] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <UserX class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="block text-[11px] font-bold text-[#8C7A81] uppercase tracking-wider group-hover:text-[#7A1228] transition-colors">
                                AKUN NONAKTIF
                            </span>
                            <div class="text-3xl font-extrabold text-[#2A0711] my-0.5">
                                {{ stats.akun_nonaktif }}
                            </div>
                            <span class="text-xs text-[#9E8B90]">
                                Akses ditangguhkan
                            </span>
                        </div>
                    </div>
                    <ChevronRight class="w-4 h-4 text-[#C2B5B8] group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all shrink-0" />
                </Link>
            </div>

            <!-- Section: Distribusi Pengguna (Matching screenshot 2) -->
            <div class="bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-[#2A0711]">
                        Distribusi pengguna
                    </h2>
                    <p class="text-xs text-[#8C7A81] mt-0.5">
                        Hak akses aktif berdasarkan peran
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link
                        v-for="role in distribusi"
                        :key="role.id"
                        :href="'/admin/users?role=' + role.id"
                        class="p-4 rounded-xl border border-[#F2E8EB] bg-[#FCFBFB] flex items-center justify-between group hover:border-[#7A1228]/40 hover:bg-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer"
                    >
                        <div class="flex items-center gap-3.5">
                            <!-- Role Icon -->
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-2xs',
                                    role.id === 'admin_toko' ? 'bg-[#E0F2FE] text-[#0284C7]' : '',
                                    role.id === 'admin_gudang' ? 'bg-[#EDE9FE] text-[#6D28D9]' : '',
                                    role.id === 'admin_konveksi' ? 'bg-[#F6EFEA] text-[#8C4A27]' : '',
                                ]"
                            >
                                <Store v-if="role.id === 'admin_toko'" class="w-5 h-5" />
                                <Warehouse v-else-if="role.id === 'admin_gudang'" class="w-5 h-5" />
                                <Scissors v-else-if="role.id === 'admin_konveksi'" class="w-5 h-5" />
                                <Shield v-else class="w-5 h-5" />
                            </div>

                            <div>
                                <h4 class="text-sm font-bold text-[#2A0711] group-hover:text-[#7A1228] transition-colors">
                                    {{ role.name }}
                                </h4>
                                <span class="text-xs text-[#7E6E74]">
                                    {{ role.count }} pengguna aktif
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Pill Aktif -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            <ChevronRight class="w-4 h-4 text-[#C2B5B8] group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all shrink-0" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Permohonan Pendaftaran Terbaru Menunggu Aktivasi (Table) -->
            <div v-if="pendingUsers.length > 0" class="bg-white rounded-2xl border border-[#F0E6E8] p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-[#2A0711]">
                            Permohonan Pendaftaran Baru
                        </h2>
                        <p class="text-xs text-[#8C7A81] mt-0.5">
                            Verifikasi pendaftaran mandiri pengguna untuk memberikan hak akses
                        </p>
                    </div>

                    <Link
                        href="/admin/users?status=perlu_aktivasi"
                        class="text-xs font-bold text-[#7A1228] hover:underline flex items-center gap-1"
                    >
                        <span>Lihat Semua</span>
                        <ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-[11px] font-bold text-[#8C7A81] uppercase bg-[#FAF5F6] border-b border-[#F0E6E8]">
                            <tr>
                                <th class="py-3 px-4 rounded-l-xl">Nama Pengguna</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Permohonan Role</th>
                                <th class="py-3 px-4">Tanggal Daftar</th>
                                <th class="py-3 px-4 text-right rounded-r-xl">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5ECF0]">
                            <tr v-for="user in pendingUsers" :key="user.id" class="hover:bg-[#FCFBFB]">
                                <td class="py-3.5 px-4 font-bold text-[#2A0711]">
                                    {{ user.name }}
                                </td>
                                <td class="py-3.5 px-4 text-[#5A454C]">
                                    {{ user.email }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border',
                                            user.role === 'admin_toko' ? 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]' : '',
                                            user.role === 'admin_gudang' ? 'bg-[#EDE9FE] text-[#6D28D9] border-[#DDD6FE]' : '',
                                            user.role === 'admin_konveksi' ? 'bg-[#F6EFEA] text-[#8C4A27] border-[#E5D5CA]' : '',
                                            user.role === 'administrator' ? 'bg-[#FDF2F4] text-[#7A1228] border-[#F6D0D8]' : '',
                                        ]"
                                    >
                                        {{ user.role_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-[#8C7A81]">
                                    {{ new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Tombol Setujui -->
                                        <button
                                            type="button"
                                            @click="openConfirmModal(user, 'approve')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                                        >
                                            <CheckCircle2 class="w-3.5 h-3.5" />
                                            <span>Setujui</span>
                                        </button>

                                        <!-- Tombol Tolak -->
                                        <button
                                            type="button"
                                            @click="openConfirmModal(user, 'reject')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 hover:border-rose-300 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                        >
                                            <XCircle class="w-3.5 h-3.5" />
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Pop Up Konfirmasi Aksi Cepat (Setujui / Tolak) -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
            <div
                v-if="selectedActionUser"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50 backdrop-blur-sm"
                @click.self="closeConfirmModal"
            >
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                <div v-if="selectedActionUser" class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">

                    <!-- Colored Header Banner -->
                    <div
                        :class="[
                            'relative px-6 pt-6 pb-8 overflow-hidden',
                            actionType === 'approve'
                                ? 'bg-gradient-to-br from-emerald-500 to-emerald-700'
                                : 'bg-gradient-to-br from-rose-500 to-rose-700'
                        ]"
                    >
                        <!-- Decorative circles -->
                        <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-8 -left-4 w-20 h-20 rounded-full bg-white/10"></div>

                        <!-- Top Row: Icon + Close -->
                        <div class="relative flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner">
                                <CheckCircle2 v-if="actionType === 'approve'" class="w-5 h-5 text-white" />
                                <AlertTriangle v-else class="w-5 h-5 text-white" />
                            </div>
                            <button
                                type="button"
                                @click="closeConfirmModal"
                                :disabled="isProcessing"
                                class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50"
                            >
                                <X class="w-4 h-4 text-white" />
                            </button>
                        </div>

                        <!-- Title & Subtitle -->
                        <div class="relative">
                            <h3 class="text-lg font-bold text-white leading-tight">
                                {{ actionType === 'approve' ? 'Setujui Permohonan?' : 'Tolak Permohonan?' }}
                            </h3>
                            <p class="text-xs text-white/80 mt-1 leading-relaxed">
                                {{
                                    actionType === 'approve'
                                        ? 'Pengguna akan mendapat akses penuh ke sistem sesuai role yang dipilih.'
                                        : 'Permohonan akan ditolak dan pengguna tidak dapat mengakses sistem.'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Applicant Card (overlapping the banner) -->
                    <div class="px-6">
                        <div
                            :class="[
                                'relative -mt-4 rounded-xl p-4 shadow-md border flex items-center gap-4',
                                actionType === 'approve'
                                    ? 'bg-white border-emerald-100'
                                    : 'bg-white border-rose-100'
                            ]"
                        >
                            <!-- Avatar Inisial -->
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center text-base font-bold flex-shrink-0 shadow-sm',
                                    actionType === 'approve'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-rose-100 text-rose-700'
                                ]"
                            >
                                {{ selectedActionUser.name ? selectedActionUser.name.charAt(0).toUpperCase() : '?' }}
                            </div>
                            <!-- User Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-[#2A0711] truncate">{{ selectedActionUser.name }}</p>
                                <p class="text-xs text-[#8C7A81] truncate mt-0.5">{{ selectedActionUser.email }}</p>
                            </div>
                            <!-- Role Badge -->
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border flex-shrink-0',
                                    selectedActionUser.role === 'admin_toko' ? 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]' : '',
                                    selectedActionUser.role === 'admin_gudang' ? 'bg-[#EDE9FE] text-[#6D28D9] border-[#DDD6FE]' : '',
                                    selectedActionUser.role === 'admin_konveksi' ? 'bg-[#F6EFEA] text-[#8C4A27] border-[#E5D5CA]' : '',
                                    selectedActionUser.role === 'administrator' ? 'bg-[#FDF2F4] text-[#7A1228] border-[#F6D0D8]' : '',
                                ]"
                            >
                                {{ selectedActionUser.role_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Consequence Warning -->
                    <div class="px-6 pt-4 pb-2">
                        <div
                            :class="[
                                'flex items-start gap-2.5 rounded-xl px-3.5 py-3 text-xs leading-relaxed',
                                actionType === 'approve'
                                    ? 'bg-emerald-50 text-emerald-800'
                                    : 'bg-rose-50 text-rose-800'
                            ]"
                        >
                            <CheckCircle2 v-if="actionType === 'approve'" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-600" />
                            <AlertTriangle v-else class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-600" />
                            <span>
                                <span v-if="actionType === 'approve'">
                                    Akun akan <strong>langsung aktif</strong> dan pengguna dapat login ke sistem.
                                </span>
                                <span v-else>
                                    Tindakan ini <strong>tidak dapat dibatalkan</strong>. Pengguna harus mendaftar ulang jika ingin mengajukan permohonan kembali.
                                </span>
                            </span>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 pt-3 pb-6 flex items-center gap-3">
                        <button
                            type="button"
                            @click="closeConfirmModal"
                            :disabled="isProcessing"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-white hover:bg-gray-50 active:bg-gray-100 text-sm font-semibold text-[#5A454C] transition-all duration-150 cursor-pointer disabled:opacity-50"
                        >
                            Batal
                        </button>

                        <button
                            v-if="actionType === 'approve'"
                            type="button"
                            @click="handleConfirmAction"
                            :disabled="isProcessing"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold shadow-sm transition-all duration-150 cursor-pointer disabled:opacity-70"
                        >
                            <svg v-if="isProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <CheckCircle2 v-else class="w-4 h-4" />
                            <span>{{ isProcessing ? 'Memproses...' : 'Setujui & Aktifkan' }}</span>
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="handleConfirmAction"
                            :disabled="isProcessing"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-semibold shadow-sm transition-all duration-150 cursor-pointer disabled:opacity-70"
                        >
                            <svg v-if="isProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <XCircle v-else class="w-4 h-4" />
                            <span>{{ isProcessing ? 'Memproses...' : 'Ya, Tolak Permohonan' }}</span>
                        </button>
                    </div>

                </div>
                </Transition>
            </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Search,
    UserPlus,
    Settings,
    ChevronDown,
    X,
    RotateCcw,
    CheckCircle2,
    AlertCircle,
    AlertTriangle,
    XCircle,
    Trash2,
    Shield,
    Phone,
    Mail,
    ChevronLeft,
    ChevronRight,
    KeyRound
} from 'lucide-vue-next';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    rolesList: {
        type: Array,
        default: () => [],
    },
    statusList: {
        type: Array,
        default: () => [],
    },
    totalUsersCount: {
        type: Number,
        default: 0,
    },
});

// Search & filter states
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const activeToday = ref(Boolean(props.filters.active_today));

let searchDebounceTimeout = null;

// Check if any filter is currently active
const hasActiveFilters = computed(() => {
    return Boolean(
        (search.value && search.value.trim() !== '') ||
        (selectedRole.value && selectedRole.value !== 'all') ||
        (selectedStatus.value && selectedStatus.value !== 'all') ||
        activeToday.value
    );
});

const applyFilters = () => {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: selectedRole.value !== 'all' ? selectedRole.value : undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            active_today: activeToday.value ? 1 : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Reset all filters to default
const resetFilters = () => {
    search.value = '';
    selectedRole.value = 'all';
    selectedStatus.value = 'all';
    activeToday.value = false;
    router.get(
        '/admin/users',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Clear individual filter
const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const clearRoleFilter = () => {
    selectedRole.value = 'all';
    applyFilters();
};

const clearStatusFilter = () => {
    selectedStatus.value = 'all';
    applyFilters();
};

const clearActiveToday = () => {
    activeToday.value = false;
    applyFilters();
};

watch(search, () => {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch([selectedRole, selectedStatus], () => {
    applyFilters();
});

watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        search.value = newFilters.search || '';
        selectedRole.value = newFilters.role || 'all';
        selectedStatus.value = newFilters.status || 'all';
        activeToday.value = Boolean(newFilters.active_today);
    }
}, { deep: true });

// Create User Modal
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    role: 'admin_toko',
    status: 'aktif',
    phone: '',
    password: '',
});

const submitCreate = () => {
    createForm.post('/admin/users', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

// Manage / Edit User Modal
const showManageModal = ref(false);
const selectedUser = ref(null);
const manageTab = ref('role_status'); // 'role_status' | 'edit_profile' | 'security'

const editForm = useForm({
    name: '',
    email: '',
    role: '',
    status: '',
    phone: '',
    notes: '',
    password: '',
});

const openManageModal = (user) => {
    selectedUser.value = user;
    manageTab.value = 'role_status';
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.status = user.status;
    editForm.phone = user.phone || '';
    editForm.notes = user.notes || '';
    editForm.password = '';
    showManageModal.value = true;
};

const submitUpdate = () => {
    if (!selectedUser.value) return;
    editForm.put(`/admin/users/${selectedUser.value.id}`, {
        onSuccess: () => {
            showManageModal.value = false;
        },
    });
};

const quickActivateFromModal = () => {
    if (!selectedUser.value) return;
    router.post(`/admin/users/${selectedUser.value.id}/activate`, {
        role: editForm.role || selectedUser.value.role,
    }, {
        onSuccess: () => {
            showManageModal.value = false;
        },
    });
};

const deleteUser = () => {
    if (!selectedUser.value) return;
    if (confirm(`Apakah Anda yakin ingin menghapus akun ${selectedUser.value.name}? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                showManageModal.value = false;
            },
        });
    }
};

// ── Confirm Action Modal (Setujui / Tolak dari banner permohonan) ─────────────
const showConfirmModal = ref(false);
const confirmActionType = ref(''); // 'approve' | 'reject'
const confirmIsProcessing = ref(false);

const openConfirmAction = (type) => {
    confirmActionType.value = type;
    showConfirmModal.value = true;
};

const closeConfirmAction = () => {
    if (confirmIsProcessing.value) return;
    showConfirmModal.value = false;
    confirmActionType.value = '';
};

const handleConfirmAction = () => {
    if (!selectedUser.value) return;
    confirmIsProcessing.value = true;
    const userId = selectedUser.value.id;
    const role = editForm.role || selectedUser.value.role;

    if (confirmActionType.value === 'approve') {
        router.post(`/admin/users/${userId}/activate`, { role }, {
            preserveScroll: true,
            onSuccess: () => {
                confirmIsProcessing.value = false;
                showConfirmModal.value = false;
                showManageModal.value = false;
            },
            onError: () => { confirmIsProcessing.value = false; },
        });
    } else {
        router.post(`/admin/users/${userId}/reject`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                confirmIsProcessing.value = false;
                showConfirmModal.value = false;
                showManageModal.value = false;
            },
            onError: () => { confirmIsProcessing.value = false; },
        });
    }
};

// Helper: role color palette (untuk modal konfirmasi)
const getConfirmRoleColor = (role) => ({
    admin_toko:     { badgeBg: 'bg-[#E0F2FE]', badgeText: 'text-[#0369A1]', badgeBorder: 'border-[#BAE6FD]' },
    admin_gudang:   { badgeBg: 'bg-[#EDE9FE]', badgeText: 'text-[#6D28D9]', badgeBorder: 'border-[#DDD6FE]' },
    admin_konveksi: { badgeBg: 'bg-[#F6EFEA]', badgeText: 'text-[#8C4A27]', badgeBorder: 'border-[#E5D5CA]' },
    administrator:  { badgeBg: 'bg-[#FDF2F4]', badgeText: 'text-[#7A1228]', badgeBorder: 'border-[#F6D0D8]' },
}[role] || { badgeBg: 'bg-gray-100', badgeText: 'text-gray-700', badgeBorder: 'border-gray-200' });

// Helper style classes for role pills (Unique colors that DO NOT clash with Aktif [green], Perlu Aktivasi [yellow], or Nonaktif [red])
const getRolePillClass = (role) => {
    switch (role) {
        case 'admin_toko':
            // Sky Blue: sangat berbeda dari hijau, kuning, dan merah
            return {
                wrapper: 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]',
                dot: 'bg-[#0284C7]',
            };
        case 'admin_gudang':
            // Purple: sangat berbeda dari status mana pun
            return {
                wrapper: 'bg-[#EDE9FE] text-[#6D28D9] border border-[#DDD6FE]',
                dot: 'bg-[#7C3AED]',
            };
        case 'admin_konveksi':
            // Warm Mocca / Terracotta: warna hangat khas garmen/tekstil yang 100% berbeda dari status kuning/oranye dan merah
            return {
                wrapper: 'bg-[#F6EFEA] text-[#8C4A27] border border-[#E5D5CA]',
                dot: 'bg-[#8C4A27]',
            };
        case 'administrator':
            // Maroon signature LilieFlow
            return {
                wrapper: 'bg-[#FDF2F4] text-[#7A1228] border border-[#F6D0D8]',
                dot: 'bg-[#7A1228]',
            };
        default:
            return {
                wrapper: 'bg-gray-100 text-gray-700 border border-gray-200',
                dot: 'bg-gray-400',
            };
    }
};

// Helper style classes for status pills
const getStatusPillClass = (status) => {
    switch (status) {
        case 'aktif':
            return {
                wrapper: 'bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0]',
                dot: 'bg-[#22C55E]',
            };
        case 'perlu_aktivasi':
            return {
                wrapper: 'bg-[#FEF3C7] text-[#B45309] border border-[#FDE68A]',
                dot: 'bg-[#F59E0B]',
            };
        case 'nonaktif':
            return {
                wrapper: 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FECACA]',
                dot: 'bg-[#EF4444]',
            };
        default:
            return {
                wrapper: 'bg-gray-100 text-gray-700 border border-gray-200',
                dot: 'bg-gray-400',
            };
    }
};

// Helper label for active filter chips
const getRoleLabel = (val) => {
    const item = props.rolesList.find(r => r.value === val);
    return item ? item.label : val;
};

const getStatusLabel = (val) => {
    const item = props.statusList.find(s => s.value === val);
    return item ? item.label : val;
};
</script>

<template>
    <AdminLayout title="Kelola Pengguna">
        <Head title="Kelola Pengguna - LilieFlow" />

        <div class="space-y-6">
            <!-- Header Section (Matching screenshot 1) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase">
                        ADMINISTRASI
                    </span>
                    <h1 class="text-3xl font-extrabold text-[#2A0711] tracking-tight mt-1">
                        Kelola pengguna
                    </h1>
                    <p class="text-sm text-[#736368] mt-1 max-w-3xl leading-relaxed">
                        Kelola seluruh akun pengguna, verifikasi pendaftaran baru, penetapan role, dan status hak akses sistem LilieFlow.
                    </p>
                </div>

                <button
                    type="button"
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600d1e] text-white text-sm font-semibold shadow-xs transition-colors self-start md:self-auto cursor-pointer"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>Tambah pengguna</span>
                </button>
            </div>

            <!-- Filters Bar (Matching screenshot 1 + Reset Filter Feature) -->
            <div class="bg-white rounded-2xl p-4 border border-[#F0E6E8] shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                            <Search class="w-4 h-4" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-[#DFD1D5] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all bg-[#FCFBFB]"
                        />
                        <!-- Clear Search button -->
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#9E8B90] hover:text-[#7A1228] cursor-pointer"
                            title="Hapus kata kunci"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Dropdown Filters and Reset Button -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Role Filter -->
                        <div class="relative min-w-[160px]">
                            <select
                                v-model="selectedRole"
                                class="w-full appearance-none px-4 py-2.5 rounded-xl border text-sm font-medium transition-all pr-9 cursor-pointer"
                                :class="[
                                    selectedRole !== 'all'
                                        ? 'border-[#7A1228] bg-[#FDF1F3] text-[#7A1228] font-semibold'
                                        : 'border-[#DFD1D5] bg-[#FCFBFB] text-[#443339]'
                                ]"
                            >
                                <option
                                    v-for="role in rolesList"
                                    :key="role.value"
                                    :value="role.value"
                                >
                                    {{ role.label }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" :class="selectedRole !== 'all' ? 'text-[#7A1228]' : 'text-[#9E8B90]'">
                                <ChevronDown class="w-4 h-4" />
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative min-w-[160px]">
                            <select
                                v-model="selectedStatus"
                                class="w-full appearance-none px-4 py-2.5 rounded-xl border text-sm font-medium transition-all pr-9 cursor-pointer"
                                :class="[
                                    selectedStatus !== 'all'
                                        ? 'border-[#7A1228] bg-[#FDF1F3] text-[#7A1228] font-semibold'
                                        : 'border-[#DFD1D5] bg-[#FCFBFB] text-[#443339]'
                                ]"
                            >
                                <option
                                    v-for="status in statusList"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" :class="selectedStatus !== 'all' ? 'text-[#7A1228]' : 'text-[#9E8B90]'">
                                <ChevronDown class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filter Tags Row (Muncul saat filter aktif) -->
                <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 pt-2 border-t border-[#F5ECF0] text-xs">
                    <span class="text-[#8C7A81] font-semibold">Filter aktif:</span>

                    <!-- Search tag -->
                    <span
                        v-if="search"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-medium"
                    >
                        <span>Cari: "<strong>{{ search }}</strong>"</span>
                        <button type="button" @click="clearSearch" class="hover:text-rose-600 cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Role tag -->
                    <span
                        v-if="selectedRole !== 'all'"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#E0F2FE] text-[#0369A1] font-medium border border-[#BAE6FD]"
                    >
                        <span>Peran: <strong>{{ getRoleLabel(selectedRole) }}</strong></span>
                        <button type="button" @click="clearRoleFilter" class="hover:text-rose-600 cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Status tag -->
                    <span
                        v-if="selectedStatus !== 'all'"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FEF3C7] text-[#B45309] font-medium border border-[#FDE68A]"
                    >
                        <span>Status: <strong>{{ getStatusLabel(selectedStatus) }}</strong></span>
                        <button type="button" @click="clearStatusFilter" class="hover:text-rose-600 cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Active Today tag -->
                    <span
                        v-if="activeToday"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-medium border border-emerald-200"
                    >
                        <span>Aktivitas: <strong>Aktif Hari Ini</strong></span>
                        <button type="button" @click="clearActiveToday" class="hover:text-rose-600 cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Clear All link -->
                    <button
                        type="button"
                        @click="resetFilters"
                        class="text-[#7A1228] font-bold hover:underline cursor-pointer ml-1"
                    >
                        Hapus Semua
                    </button>
                </div>
            </div>

            <!-- Users Table (Matching screenshot 1) -->
            <div class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <!-- Table Header with Light Pink Tone -->
                        <thead class="bg-[#FDEEF1] text-[#9E8B90] text-[11px] font-bold uppercase tracking-wider border-b border-[#F5D5DC]">
                            <tr>
                                <th class="py-4 px-6 text-[#7E6E74]">PENGGUNA</th>
                                <th class="py-4 px-6 text-[#7E6E74]">EMAIL</th>
                                <th class="py-4 px-6 text-[#7E6E74]">PERAN</th>
                                <th class="py-4 px-6 text-[#7E6E74]">STATUS</th>
                                <th class="py-4 px-6 text-[#7E6E74]">TANGGAL REGISTRASI</th>
                                <th class="py-4 px-6 text-center text-[#7E6E74]">AKSI</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="divide-y divide-[#F5ECF0]">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-[#FCF8F9] transition-colors"
                            >
                                <!-- PENGGUNA -->
                                <td class="py-4 px-6 font-bold text-[#2A0711]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#FCE7EB] text-[#7A1228] font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ user.initials }}
                                        </div>
                                        <span>{{ user.name }}</span>
                                    </div>
                                </td>

                                <!-- EMAIL -->
                                <td class="py-4 px-6 text-[#5A454C] font-normal">
                                    {{ user.email }}
                                </td>

                                <!-- PERAN (Unique colors per role) -->
                                <td class="py-4 px-6">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
                                            getRolePillClass(user.role).wrapper
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'w-1.5 h-1.5 rounded-full',
                                                getRolePillClass(user.role).dot
                                            ]"
                                        ></span>
                                        {{ user.role_label }}
                                    </span>
                                </td>

                                <!-- STATUS -->
                                <td class="py-4 px-6">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
                                            getStatusPillClass(user.status).wrapper
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'w-1.5 h-1.5 rounded-full',
                                                getStatusPillClass(user.status).dot
                                            ]"
                                        ></span>
                                        {{ user.status_label }}
                                    </span>
                                </td>

                                <!-- TANGGAL REGISTRASI -->
                                <td class="py-4 px-6 text-[#6C555C] text-xs">
                                    {{ user.formatted_created_at || new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </td>

                                <!-- AKSI -->
                                <td class="py-4 px-6 text-center">
                                    <button
                                        type="button"
                                        @click="openManageModal(user)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-[#7A1228] hover:bg-[#FDF1F3] transition-colors cursor-pointer"
                                    >
                                        <Settings class="w-4 h-4 text-[#7A1228]" />
                                        <span>Kelola</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="max-w-xs mx-auto">
                                        <Search class="w-8 h-8 mx-auto text-gray-300 mb-2" />
                                        <p class="text-sm font-semibold text-gray-600">Tidak ada pengguna ditemukan</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter peran dan status.</p>
                                        <button
                                            v-if="hasActiveFilters"
                                            type="button"
                                            @click="resetFilters"
                                            class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#7A1228] text-white text-xs font-semibold hover:bg-[#600d1e] cursor-pointer"
                                        >
                                            <RotateCcw class="w-3.5 h-3.5" />
                                            <span>Reset Filter Sekarang</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer (Matching screenshot 1) -->
                <div class="px-6 py-4 bg-[#FDF7F8] border-t border-[#F5D5DC] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#7E6E74]">
                    <div>
                        Menampilkan <span class="font-bold text-[#2A0711]">{{ users.from || 0 }} - {{ users.to || 0 }}</span> dari <span class="font-bold text-[#2A0711]">{{ users.total }}</span> Pengguna
                    </div>

                    <!-- Pagination Links -->
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="users.prev_page_url"
                            :href="users.prev_page_url"
                            class="px-3 py-1.5 rounded-lg border border-[#E8D4D9] bg-white text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228] transition-colors font-medium flex items-center gap-1"
                        >
                            <ChevronLeft class="w-3.5 h-3.5" />
                            <span>Prev</span>
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 font-medium flex items-center gap-1 cursor-not-allowed"
                        >
                            <ChevronLeft class="w-3.5 h-3.5" />
                            <span>Prev</span>
                        </span>

                        <!-- Page items -->
                        <template v-for="(link, idx) in users.links" :key="idx">
                            <template v-if="!link.label.includes('Previous') && !link.label.includes('Next')">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'w-8 h-8 rounded-lg flex items-center justify-center font-bold transition-colors',
                                        link.active
                                            ? 'bg-[#7A1228] text-white shadow-xs'
                                            : 'bg-white border border-[#E8D4D9] text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228]'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="w-8 h-8 flex items-center justify-center text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </template>

                        <Link
                            v-if="users.next_page_url"
                            :href="users.next_page_url"
                            class="px-3 py-1.5 rounded-lg border border-[#E8D4D9] bg-white text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228] transition-colors font-medium flex items-center gap-1"
                        >
                            <span>Next</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 font-medium flex items-center gap-1 cursor-not-allowed"
                        >
                            <span>Next</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Tambah Pengguna Baru -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-[#F0E6E8]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center">
                            <UserPlus class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#2A0711]">Tambah Pengguna Baru</h3>
                            <p class="text-xs text-gray-500">Buat akun staf dengan penugasan role dan status langsung</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4 pt-4">
                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1">
                            Nama Lengkap <span class="text-rose-600">*</span>
                        </label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            required
                            placeholder="Contoh: Rina Wijaya"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1">
                            Email Perusahaan <span class="text-rose-600">*</span>
                        </label>
                        <input
                            v-model="createForm.email"
                            type="email"
                            required
                            placeholder="nama@liliebelle.co.id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        />
                        <span v-if="createForm.errors.email" class="text-xs text-rose-600 mt-1 block">
                            {{ createForm.errors.email }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Role / Peran <span class="text-rose-600">*</span>
                            </label>
                            <select
                                v-model="createForm.role"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            >
                                <option value="admin_toko">Admin Toko</option>
                                <option value="admin_gudang">Admin Gudang</option>
                                <option value="admin_konveksi">Admin Konveksi</option>
                                <option value="administrator">Administrator</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Status Akun <span class="text-rose-600">*</span>
                            </label>
                            <select
                                v-model="createForm.status"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            >
                                <option value="aktif">Aktif</option>
                                <option value="perlu_aktivasi">Perlu Aktivasi</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1">
                            Nomor Telepon (Opsional)
                        </label>
                        <input
                            v-model="createForm.phone"
                            type="text"
                            placeholder="081234567890"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1">
                            Password Sementara <span class="text-rose-600">*</span>
                        </label>
                        <input
                            v-model="createForm.password"
                            type="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#F0E6E8]">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#7A1228] hover:bg-[#600d1e] shadow-xs"
                        >
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Kelola Pengguna (Detailed actions, role assignment, status toggle, edit, delete) -->
        <div
            v-if="showManageModal && selectedUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
                <!-- User Summary Header -->
                <div class="flex items-start justify-between pb-5 border-b border-[#F0E6E8]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full bg-[#FCE7EB] text-[#7A1228] font-extrabold text-base flex items-center justify-center shrink-0 border border-[#F5D5DC]">
                            {{ selectedUser.initials }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#2A0711] leading-tight">
                                {{ selectedUser.name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-[#6C555C]">{{ selectedUser.email }}</span>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                                        getRolePillClass(selectedUser.role).wrapper
                                    ]"
                                >
                                    <span :class="['w-1 h-1 rounded-full', getRolePillClass(selectedUser.role).dot]"></span>
                                    {{ selectedUser.role_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="showManageModal = false"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Banner if Perlu Aktivasi -->
                <div
                    v-if="selectedUser.status === 'perlu_aktivasi'"
                    class="mt-4 p-4 rounded-xl bg-[#FEF3C7] border border-[#FDE68A] flex items-center justify-between gap-3 text-xs"
                >
                    <div>
                        <span class="font-bold text-[#B45309] block">Permohonan Registrasi Mandiri</span>
                        <span class="text-[#92400E]">
                            Pengguna mengajukan role: <strong class="underline">{{ selectedUser.role_label }}</strong>
                        </span>
                    </div>

                    <!-- Two action buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            @click="openConfirmAction('approve')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                        >
                            <CheckCircle2 class="w-3.5 h-3.5" />
                            <span>Setujui</span>
                        </button>
                        <button
                            type="button"
                            @click="openConfirmAction('reject')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white hover:bg-rose-50 text-rose-600 border border-rose-300 font-bold text-xs transition-colors cursor-pointer"
                        >
                            <XCircle class="w-3.5 h-3.5" />
                            <span>Tolak</span>
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 mt-5">
                    <button
                        type="button"
                        @click="manageTab = 'role_status'"
                        :class="[
                            'py-2 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer',
                            manageTab === 'role_status'
                                ? 'border-[#7A1228] text-[#7A1228]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Penetapan Role & Status
                    </button>
                    <button
                        type="button"
                        @click="manageTab = 'edit_profile'"
                        :class="[
                            'py-2 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer',
                            manageTab === 'edit_profile'
                                ? 'border-[#7A1228] text-[#7A1228]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Edit Profil
                    </button>
                    <button
                        type="button"
                        @click="manageTab = 'security'"
                        :class="[
                            'py-2 px-4 text-xs font-bold border-b-2 transition-colors cursor-pointer',
                            manageTab === 'security'
                                ? 'border-[#7A1228] text-[#7A1228]'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Keamanan & Sandi
                    </button>
                </div>

                <!-- Tab 1: Penetapan Role & Status -->
                <form @submit.prevent="submitUpdate" class="pt-5 space-y-4">
                    <template v-if="manageTab === 'role_status'">
                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                Penetapan Role / Peran
                            </label>
                            <select
                                v-model="editForm.role"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            >
                                <option value="admin_toko">Admin Toko (Akses Operasional Toko)</option>
                                <option value="admin_gudang">Admin Gudang (Akses Manajemen Stok & Logistik)</option>
                                <option value="admin_konveksi">Admin Konveksi (Akses Vendor & Produksi)</option>
                                <option value="administrator">Administrator (Hak Akses Penuh Sistem)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                Status Hak Akses
                            </label>
                            <select
                                v-model="editForm.status"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            >
                                <option value="aktif">Aktif (Dapat Masuk ke Sistem)</option>
                                <option value="perlu_aktivasi">Perlu Aktivasi (Menunggu Persetujuan)</option>
                                <option value="nonaktif">Nonaktif (Akses Ditangguhkan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1.5">
                                Catatan Administrator
                            </label>
                            <textarea
                                v-model="editForm.notes"
                                rows="3"
                                placeholder="Catatan internal mengenai pengguna atau alasan perubahan status..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            ></textarea>
                        </div>
                    </template>

                    <!-- Tab 2: Edit Profil -->
                    <template v-if="manageTab === 'edit_profile'">
                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Nama Lengkap
                            </label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Email Perusahaan
                            </label>
                            <input
                                v-model="editForm.email"
                                type="email"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Nomor Telepon
                            </label>
                            <input
                                v-model="editForm.phone"
                                type="text"
                                placeholder="081234567890"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            />
                        </div>
                    </template>

                    <!-- Tab 3: Security -->
                    <template v-if="manageTab === 'security'">
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 mb-3">
                            <span class="font-bold block">Reset Password:</span>
                            Masukkan password baru di bawah ini hanya jika Anda ingin mengatur ulang password pengguna ini.
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#443339] mb-1">
                                Password Baru
                            </label>
                            <input
                                v-model="editForm.password"
                                type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah password"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            />
                        </div>
                    </template>

                    <!-- Modal Actions Bottom -->
                    <div class="flex items-center justify-between pt-5 border-t border-[#F0E6E8]">
                        <button
                            type="button"
                            @click="deleteUser"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                        >
                            <Trash2 class="w-4 h-4" />
                            <span>Hapus Akun</span>
                        </button>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="showManageModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 cursor-pointer"
                            >
                                Tutup
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-[#7A1228] hover:bg-[#600d1e] shadow-xs cursor-pointer"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>

    <!-- ── Modal Konfirmasi Setujui / Tolak Registrasi ─────────────────────── -->
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
            v-if="showConfirmModal && selectedUser"
            class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50 backdrop-blur-sm"
            @click.self="closeConfirmAction"
        >
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
            <div v-if="showConfirmModal && selectedUser" class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">

                <!-- Colored Header Banner -->
                <div
                    :class="[
                        'relative px-6 pt-6 pb-8 overflow-hidden',
                        confirmActionType === 'approve'
                            ? 'bg-gradient-to-br from-emerald-500 to-emerald-700'
                            : 'bg-gradient-to-br from-rose-500 to-rose-700'
                    ]"
                >
                    <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-8 -left-4 w-20 h-20 rounded-full bg-white/10"></div>

                    <div class="relative flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner">
                            <CheckCircle2 v-if="confirmActionType === 'approve'" class="w-5 h-5 text-white" />
                            <AlertTriangle v-else class="w-5 h-5 text-white" />
                        </div>
                        <button
                            type="button"
                            @click="closeConfirmAction"
                            :disabled="confirmIsProcessing"
                            class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50"
                        >
                            <X class="w-4 h-4 text-white" />
                        </button>
                    </div>

                    <div class="relative">
                        <h3 class="text-lg font-bold text-white leading-tight">
                            {{ confirmActionType === 'approve' ? 'Setujui Permohonan?' : 'Tolak Permohonan?' }}
                        </h3>
                        <p class="text-xs text-white/80 mt-1 leading-relaxed">
                            {{
                                confirmActionType === 'approve'
                                    ? 'Pengguna akan mendapat akses penuh ke sistem sesuai role yang dipilih.'
                                    : 'Permohonan akan ditolak dan pengguna tidak dapat mengakses sistem.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Applicant Card -->
                <div class="px-6">
                    <div
                        :class="[
                            'relative -mt-4 rounded-xl p-4 shadow-md border flex items-center gap-4',
                            confirmActionType === 'approve' ? 'bg-white border-emerald-100' : 'bg-white border-rose-100'
                        ]"
                    >
                        <div
                            :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center text-base font-bold flex-shrink-0 shadow-sm',
                                confirmActionType === 'approve' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'
                            ]"
                        >
                            {{ selectedUser.initials || selectedUser.name?.charAt(0)?.toUpperCase() || '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-[#2A0711] truncate">{{ selectedUser.name }}</p>
                            <p class="text-xs text-[#8C7A81] truncate mt-0.5">{{ selectedUser.email }}</p>
                        </div>
                        <span
                            :class="[
                                'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border flex-shrink-0',
                                getConfirmRoleColor(selectedUser.role).badgeBg,
                                getConfirmRoleColor(selectedUser.role).badgeText,
                                getConfirmRoleColor(selectedUser.role).badgeBorder,
                            ]"
                        >
                            {{ selectedUser.role_label }}
                        </span>
                    </div>
                </div>

                <!-- Consequence Warning -->
                <div class="px-6 pt-4 pb-2">
                    <div
                        :class="[
                            'flex items-start gap-2.5 rounded-xl px-3.5 py-3 text-xs leading-relaxed',
                            confirmActionType === 'approve' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800'
                        ]"
                    >
                        <CheckCircle2 v-if="confirmActionType === 'approve'" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-600" />
                        <AlertTriangle v-else class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-600" />
                        <span>
                            <span v-if="confirmActionType === 'approve'">
                                Akun akan <strong>langsung aktif</strong> dan pengguna dapat login ke sistem.
                            </span>
                            <span v-else>
                                Tindakan ini <strong>tidak dapat dibatalkan</strong>. Pengguna harus mendaftar ulang jika ingin mengajukan permohonan kembali.
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 pt-3 pb-6 flex items-center gap-3">
                    <button
                        type="button"
                        @click="closeConfirmAction"
                        :disabled="confirmIsProcessing"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-white hover:bg-gray-50 text-sm font-semibold text-[#5A454C] transition-all cursor-pointer disabled:opacity-50"
                    >
                        Batal
                    </button>

                    <button
                        v-if="confirmActionType === 'approve'"
                        type="button"
                        @click="handleConfirmAction"
                        :disabled="confirmIsProcessing"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70"
                    >
                        <svg v-if="confirmIsProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <CheckCircle2 v-else class="w-4 h-4" />
                        <span>{{ confirmIsProcessing ? 'Memproses...' : 'Setujui & Aktifkan' }}</span>
                    </button>

                    <button
                        v-else
                        type="button"
                        @click="handleConfirmAction"
                        :disabled="confirmIsProcessing"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70"
                    >
                        <svg v-if="confirmIsProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <XCircle v-else class="w-4 h-4" />
                        <span>{{ confirmIsProcessing ? 'Memproses...' : 'Ya, Tolak Permohonan' }}</span>
                    </button>
                </div>

            </div>
            </Transition>
        </div>
        </Transition>
    </Teleport>
</template>


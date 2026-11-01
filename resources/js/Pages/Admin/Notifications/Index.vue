<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    Bell, CheckCircle2, Shield, XCircle, AlertTriangle, X,
    Activity, UserPlus, LogIn, LogOut, UserCog, Trash2,
    ToggleRight, ChevronLeft, ChevronRight, Inbox, Store, Warehouse, Scissors, Info, KeyRound,
} from 'lucide-vue-next';

const props = defineProps({
    pendingUsers:           { type: Object, required: true },
    activityLogs:           { type: Array,  required: true },
    passwordResetRequests:  { type: Array,  default: () => [] },
    counts:                 { type: Object, required: true },
});

// ── Tab ──────────────────────────────────────────────────────────────────────
const activeTab = ref('all');

// ── "Sudah Dilihat" tracking via localStorage ────────────────────────────────
// Key = jumlah item saat ini, untuk reset otomatis jika ada item baru
const LS_KEY_ALL      = 'notif_seen_all';
const LS_KEY_ACTIVITY = 'notif_seen_activity';

// Signature unik berdasarkan jumlah item (berubah jika ada item baru)
const allSignature      = computed(() => String(props.counts.total));
const activitySignature = computed(() => String(props.counts.activity));

const allSeen      = ref(false);
const activitySeen = ref(false);

onMounted(() => {
    // Cek apakah tampilan ini sudah dilihat sebelumnya
    allSeen.value      = localStorage.getItem(LS_KEY_ALL)      === allSignature.value;
    activitySeen.value = localStorage.getItem(LS_KEY_ACTIVITY) === activitySignature.value;

    // Tab 'all' adalah tab default — otomatis tandai sebagai sudah dilihat
    if (activeTab.value === 'all' && !allSeen.value) {
        markSeen('all');
    }
});

const markSeen = (tab) => {
    if (tab === 'all') {
        localStorage.setItem(LS_KEY_ALL, allSignature.value);
        allSeen.value = true;
    } else if (tab === 'activity') {
        localStorage.setItem(LS_KEY_ACTIVITY, activitySignature.value);
        activitySeen.value = true;
    }
};

const switchTab = (tab) => {
    activeTab.value = tab;
    markSeen(tab);
};

// Badge yang ditampilkan: 0 jika sudah dilihat
const badgeAll          = computed(() => (allSeen.value      ? 0 : props.counts.total));
const badgeActivity     = computed(() => (activitySeen.value ? 0 : props.counts.activity));
// Badge registrasi & reset selalu dari server (hanya turun jika admin bertindak)
const badgePending      = computed(() => props.counts.pending);
const badgePasswordReset = computed(() => props.counts.passwordReset || 0);

// ── Merge all items for "Semua" tab ─────────────────────────────────────────
const allItems = computed(() => {
    const regs  = (props.pendingUsers.data || []).map(u => ({ ...u, _type: 'registration' }));
    const acts  = (props.activityLogs || []).map(l => ({ ...l, _type: 'activity' }));
    const resets = (props.passwordResetRequests || []).map(r => ({ ...r, _type: 'password_reset' }));
    return [...regs, ...acts, ...resets].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

// ── Modal: Aksi registrasi ────────────────────────────────────────────────────
const selectedActionUser = ref(null);
const actionType = ref('');
const isProcessing = ref(false);

const openConfirmModal  = (user, type) => { selectedActionUser.value = user; actionType.value = type; };
const closeConfirmModal = () => { if (isProcessing.value) return; selectedActionUser.value = null; actionType.value = ''; };

const handleConfirmAction = () => {
    if (!selectedActionUser.value) return;
    isProcessing.value = true;
    const userId = selectedActionUser.value.id;
    const role   = selectedActionUser.value.requested_role || selectedActionUser.value.role;
    if (actionType.value === 'approve') {
        router.post(`/admin/users/${userId}/activate`, { role }, {
            preserveScroll: true,
            onFinish: () => { isProcessing.value = false; selectedActionUser.value = null; actionType.value = ''; },
        });
    } else {
        router.post(`/admin/users/${userId}/reject`, {}, {
            preserveScroll: true,
            onFinish: () => { isProcessing.value = false; selectedActionUser.value = null; actionType.value = ''; },
        });
    }
};

// ── Modal: Reset Password Request ──────────────────────────────────────────
const selectedResetRequest  = ref(null);
const resetModalType        = ref(''); // 'resolve' | 'cancel'
const resetNewPassword      = ref('');
const resetConfirmPassword  = ref('');
const resetPasswordError    = ref('');
const resetProcessing       = ref(false);

const openResetModal = (req, type) => {
    selectedResetRequest.value = req;
    resetModalType.value       = type;
    resetNewPassword.value     = '';
    resetConfirmPassword.value = '';
    resetPasswordError.value   = '';
};

const closeResetModal = () => {
    if (resetProcessing.value) return;
    selectedResetRequest.value = null;
    resetModalType.value       = '';
};

const handleResolve = () => {
    resetPasswordError.value = '';
    if (!resetNewPassword.value || resetNewPassword.value.length < 8) {
        resetPasswordError.value = 'Password minimal 8 karakter.';
        return;
    }
    if (resetNewPassword.value !== resetConfirmPassword.value) {
        resetPasswordError.value = 'Konfirmasi password tidak cocok.';
        return;
    }
    resetProcessing.value = true;
    router.post(`/admin/password-reset-requests/${selectedResetRequest.value.id}/resolve`, {
        new_password:              resetNewPassword.value,
        new_password_confirmation: resetConfirmPassword.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { resetProcessing.value = false; closeResetModal(); },
        onError:   () => { resetProcessing.value = false; },
    });
};

const handleCancelReset = () => {
    resetProcessing.value = true;
    router.post(`/admin/password-reset-requests/${selectedResetRequest.value.id}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => { resetProcessing.value = false; closeResetModal(); },
        onError:   () => { resetProcessing.value = false; },
    });
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const roleColor = (role) => ({
    admin_toko:     { bg: 'bg-sky-100',    text: 'text-sky-700',    badgeBg: 'bg-[#E0F2FE]', badgeText: 'text-[#0369A1]', badgeBorder: 'border-[#BAE6FD]' },
    admin_gudang:   { bg: 'bg-violet-100', text: 'text-violet-700', badgeBg: 'bg-[#EDE9FE]', badgeText: 'text-[#6D28D9]', badgeBorder: 'border-[#DDD6FE]' },
    admin_konveksi: { bg: 'bg-orange-100', text: 'text-orange-700', badgeBg: 'bg-[#F6EFEA]', badgeText: 'text-[#8C4A27]', badgeBorder: 'border-[#E5D5CA]' },
    administrator:  { bg: 'bg-rose-100',   text: 'text-rose-700',   badgeBg: 'bg-[#FDF2F4]', badgeText: 'text-[#7A1228]', badgeBorder: 'border-[#F6D0D8]' },
}[role] || { bg: 'bg-gray-100', text: 'text-gray-700', badgeBg: 'bg-gray-100', badgeText: 'text-gray-700', badgeBorder: 'border-gray-200' });

const roleAccent = (role) => ({
    admin_toko:     'bg-gradient-to-b from-sky-400 to-sky-600',
    admin_gudang:   'bg-gradient-to-b from-violet-400 to-violet-700',
    admin_konveksi: 'bg-gradient-to-b from-orange-400 to-orange-600',
}[role] || 'bg-gradient-to-b from-gray-300 to-gray-400');

const roleIcon  = (role) => ({ admin_toko: Store, admin_gudang: Warehouse, admin_konveksi: Scissors, administrator: Shield }[role] || UserCog);

const actionMeta = (action) => ({
    LOGIN:                { label: 'Login',          icon: LogIn,        bg: 'bg-blue-50',    text: 'text-blue-700',    border: 'border-blue-200'    },
    LOGOUT:               { label: 'Logout',         icon: LogOut,       bg: 'bg-gray-100',   text: 'text-gray-700',    border: 'border-gray-200'    },
    REGISTER:             { label: 'Registrasi',     icon: UserPlus,     bg: 'bg-amber-50',   text: 'text-amber-700',   border: 'border-amber-200'   },
    ACTIVATED:            { label: 'Diaktifkan',     icon: CheckCircle2, bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200' },
    USER_CREATED:         { label: 'Buat Pengguna',  icon: UserPlus,     bg: 'bg-purple-50',  text: 'text-purple-700',  border: 'border-purple-200'  },
    USER_UPDATED:         { label: 'Edit Pengguna',  icon: UserCog,      bg: 'bg-indigo-50',  text: 'text-indigo-700',  border: 'border-indigo-200'  },
    USER_DELETED:         { label: 'Hapus Pengguna', icon: Trash2,       bg: 'bg-rose-50',    text: 'text-rose-700',    border: 'border-rose-200'    },
    STATUS_CHANGED:       { label: 'Ubah Status',    icon: ToggleRight,  bg: 'bg-orange-50',  text: 'text-orange-700',  border: 'border-orange-200'  },
    REGISTRATION_REJECTED:{ label: 'Ditolak',        icon: XCircle,      bg: 'bg-rose-50',    text: 'text-rose-700',    border: 'border-rose-200'    },
}[action] || { label: action, icon: Info, bg: 'bg-gray-50', text: 'text-gray-700', border: 'border-gray-200' });
</script>

<template>
    <AdminLayout title="Notifikasi">
        <Head title="Notifikasi - LilieFlow" />

        <div class="space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase">PUSAT PEMBERITAHUAN</span>
                    <h1 class="text-3xl font-extrabold text-[#2A0711] tracking-tight mt-1">Notifikasi</h1>
                    <p class="text-sm text-[#736368] mt-1">Pantau aktivitas pengguna dan permohonan registrasi akun baru.</p>
                </div>
                <!-- Badge total hanya muncul jika ada yang belum dilihat -->
                <div v-if="(badgeAll + badgePending + badgePasswordReset) > 0" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#FDF2F4] border border-[#F6D0D8] text-sm font-bold text-[#7A1228] self-start sm:self-auto">
                    <Bell class="w-4 h-4" />
                    {{ badgeAll + badgePending + badgePasswordReset }} belum dibaca
                </div>
            </div>

            <!-- Tab Bar -->
            <div class="flex items-center gap-1 bg-[#FAF7F8] rounded-xl p-1 border border-[#F0E6E8]">
                <!-- Semua -->
                <button @click="switchTab('all')" :class="['flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer', activeTab === 'all' ? 'bg-white text-[#7A1228] shadow-sm border border-[#F0E6E8]' : 'text-[#8C7A81] hover:text-[#2A0711]']">
                    <Inbox class="w-4 h-4" />
                    <span class="hidden sm:inline">Semua</span>
                    <span v-if="badgeAll > 0" :class="['text-[11px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'all' ? 'bg-[#7A1228] text-white' : 'bg-[#DFD1D5] text-[#5A454C]']">{{ badgeAll }}</span>
                </button>
                <!-- Aktivitas Pengguna -->
                <button @click="switchTab('activity')" :class="['flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer', activeTab === 'activity' ? 'bg-white text-[#7A1228] shadow-sm border border-[#F0E6E8]' : 'text-[#8C7A81] hover:text-[#2A0711]']">
                    <Activity class="w-4 h-4" />
                    <span class="hidden sm:inline">Aktivitas Pengguna</span>
                    <span v-if="badgeActivity > 0" :class="['text-[11px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'activity' ? 'bg-[#7A1228] text-white' : 'bg-[#DFD1D5] text-[#5A454C]']">{{ badgeActivity }}</span>
                </button>
                <!-- Permohonan Registrasi (badge selalu dari server) -->
                <button @click="switchTab('registrations')" :class="['flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer', activeTab === 'registrations' ? 'bg-white text-[#7A1228] shadow-sm border border-[#F0E6E8]' : 'text-[#8C7A81] hover:text-[#2A0711]']">
                    <UserPlus class="w-4 h-4" />
                    <span class="hidden sm:inline">Permohonan Registrasi</span>
                    <span v-if="badgePending > 0" :class="['text-[11px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'registrations' ? 'bg-[#7A1228] text-white' : 'bg-amber-500 text-white']">{{ badgePending }}</span>
                </button>
                <!-- Reset Password (badge selalu dari server) -->
                <button @click="switchTab('password_reset')" :class="['flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer', activeTab === 'password_reset' ? 'bg-white text-[#7A1228] shadow-sm border border-[#F0E6E8]' : 'text-[#8C7A81] hover:text-[#2A0711]']">
                    <KeyRound class="w-4 h-4" />
                    <span class="hidden sm:inline">Reset Password</span>
                    <span v-if="badgePasswordReset > 0" :class="['text-[11px] font-bold px-1.5 py-0.5 rounded-full', activeTab === 'password_reset' ? 'bg-[#7A1228] text-white' : 'bg-violet-500 text-white']">{{ badgePasswordReset }}</span>
                </button>
            </div>

            <!-- ════ TAB: SEMUA ════ -->
            <div v-if="activeTab === 'all'" class="space-y-3">
                <div v-if="allItems.length === 0" class="bg-white rounded-2xl p-14 text-center border border-[#F0E6E8]">
                    <div class="w-14 h-14 rounded-full bg-[#ECFDF5] flex items-center justify-center mx-auto mb-4"><CheckCircle2 class="w-7 h-7 text-emerald-500" /></div>
                    <h3 class="text-base font-bold text-[#2A0711]">Tidak Ada Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-1">Semua aktivitas dan permohonan sudah ditangani.</p>
                </div>

                <template v-for="item in allItems" :key="item._type + '-' + item.id">
                    <!-- Registration -->
                    <div v-if="item._type === 'registration'" class="bg-white rounded-2xl border border-amber-100 shadow-xs hover:shadow-md hover:border-amber-300 transition-all duration-200 overflow-hidden">
                        <div class="flex">
                            <div class="w-1 bg-gradient-to-b from-amber-400 to-amber-600 flex-shrink-0"></div>
                            <div class="flex-1 p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div :class="['w-11 h-11 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0', roleColor(item.role).bg, roleColor(item.role).text]">
                                        {{ item.name ? item.name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-[#2A0711]">{{ item.name }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-amber-50 text-amber-700 border-amber-200">⏳ Permohonan Baru</span>
                                            <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border', roleColor(item.role).badgeBg, roleColor(item.role).badgeText, roleColor(item.role).badgeBorder]">{{ item.role_label }}</span>
                                        </div>
                                        <p class="text-xs text-[#8C7A81] mt-0.5 truncate">{{ item.email }} · {{ item.time_ago }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0 self-end sm:self-center">
                                    <button @click="openConfirmModal(item, 'approve')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors cursor-pointer">
                                        <CheckCircle2 class="w-3.5 h-3.5" /><span>Setujui</span>
                                    </button>
                                    <button @click="openConfirmModal(item, 'reject')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 text-xs font-bold transition-colors cursor-pointer">
                                        <XCircle class="w-3.5 h-3.5" /><span>Tolak</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div v-else-if="item._type === 'activity'" class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs hover:shadow-md hover:border-[#F6D0D8] transition-all duration-200 overflow-hidden">
                        <div class="flex">
                            <div :class="['w-1 flex-shrink-0', roleAccent(item.user?.role)]"></div>
                            <div class="flex-1 p-4 flex items-center gap-3">
                                <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0', roleColor(item.user?.role).bg, roleColor(item.user?.role).text]">
                                    <component :is="roleIcon(item.user?.role)" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                        <span class="text-sm font-bold text-[#2A0711]">{{ item.user?.name ?? 'Pengguna' }}</span>
                                        <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border', roleColor(item.user?.role).badgeBg, roleColor(item.user?.role).badgeText, roleColor(item.user?.role).badgeBorder]">{{ item.user?.role_label ?? item.user?.role }}</span>
                                        <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold border', actionMeta(item.action).bg, actionMeta(item.action).text, actionMeta(item.action).border]">
                                            <component :is="actionMeta(item.action).icon" class="w-3 h-3" />{{ actionMeta(item.action).label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#5A454C] line-clamp-1">{{ item.description }}</p>
                                    <p class="text-[11px] text-[#8C7A81] mt-0.5">{{ item.time_ago }} · {{ item.formatted_created_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ════ TAB: AKTIVITAS PENGGUNA ════ -->
            <div v-if="activeTab === 'activity'" class="space-y-3">
                <div v-if="activityLogs.length === 0" class="bg-white rounded-2xl p-14 text-center border border-[#F0E6E8]">
                    <div class="w-14 h-14 rounded-full bg-sky-50 flex items-center justify-center mx-auto mb-4"><Activity class="w-7 h-7 text-sky-500" /></div>
                    <h3 class="text-base font-bold text-[#2A0711]">Belum Ada Aktivitas</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">Aktivitas dari Admin Toko, Admin Gudang, dan Admin Konveksi akan muncul di sini.</p>
                </div>
                <div v-for="log in activityLogs" :key="log.id" class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs hover:shadow-md hover:border-[#F6D0D8] transition-all duration-200 overflow-hidden">
                    <div class="flex">
                        <div :class="['w-1 flex-shrink-0', roleAccent(log.user?.role)]"></div>
                        <div class="flex-1 p-4 flex items-center gap-3">
                            <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0', roleColor(log.user?.role).bg, roleColor(log.user?.role).text]">
                                <component :is="roleIcon(log.user?.role)" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                    <span class="text-sm font-bold text-[#2A0711]">{{ log.user?.name ?? 'Pengguna' }}</span>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border', roleColor(log.user?.role).badgeBg, roleColor(log.user?.role).badgeText, roleColor(log.user?.role).badgeBorder]">{{ log.user?.role_label ?? log.user?.role }}</span>
                                    <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold border', actionMeta(log.action).bg, actionMeta(log.action).text, actionMeta(log.action).border]">
                                        <component :is="actionMeta(log.action).icon" class="w-3 h-3" />{{ actionMeta(log.action).label }}
                                    </span>
                                </div>
                                <p class="text-xs text-[#5A454C] line-clamp-2">{{ log.description }}</p>
                                <p class="text-[11px] text-[#8C7A81] mt-0.5">{{ log.time_ago }} · {{ log.formatted_created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ TAB: PERMOHONAN REGISTRASI ════ -->
            <div v-if="activeTab === 'registrations'" class="space-y-4">
                <div v-if="pendingUsers.data.length > 0" class="flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                    <AlertTriangle class="w-4 h-4 text-amber-500 flex-shrink-0" />
                    <span>Terdapat <strong>{{ counts.pending }} permohonan</strong> registrasi akun baru yang menunggu persetujuan Administrator.</span>
                </div>
                <div v-if="pendingUsers.data.length === 0" class="bg-white rounded-2xl p-14 text-center border border-[#F0E6E8]">
                    <div class="w-14 h-14 rounded-full bg-[#ECFDF5] flex items-center justify-center mx-auto mb-4"><CheckCircle2 class="w-7 h-7 text-emerald-500" /></div>
                    <h3 class="text-base font-bold text-[#2A0711]">Semua Permohonan Selesai</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Saat ini tidak ada permohonan registrasi baru yang menunggu aktivasi.</p>
                </div>
                <div v-for="user in pendingUsers.data" :key="user.id" class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs hover:shadow-md hover:border-amber-200 transition-all duration-200 overflow-hidden">
                    <div class="flex">
                        <div class="w-1.5 bg-gradient-to-b from-amber-400 to-amber-600 flex-shrink-0"></div>
                        <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-base font-bold flex-shrink-0 shadow-sm', roleColor(user.role).bg, roleColor(user.role).text]">
                                    {{ user.name ? user.name.charAt(0).toUpperCase() : '?' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                        <h3 class="text-sm font-bold text-[#2A0711]">{{ user.name }}</h3>
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border', roleColor(user.role).badgeBg, roleColor(user.role).badgeText, roleColor(user.role).badgeBorder]">{{ user.role_label }}</span>
                                    </div>
                                    <p class="text-xs text-[#5A454C]">{{ user.email }}</p>
                                    <p class="text-[11px] text-[#8C7A81] mt-0.5">Mendaftar {{ user.time_ago }} · {{ user.formatted_created_at }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                <button @click="openConfirmModal(user, 'approve')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
                                    <CheckCircle2 class="w-4 h-4" /><span>Setujui</span>
                                </button>
                                <button @click="openConfirmModal(user, 'reject')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 hover:border-rose-300 text-xs font-bold transition-all cursor-pointer">
                                    <XCircle class="w-4 h-4" /><span>Tolak</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div v-if="pendingUsers.last_page > 1" class="flex items-center justify-between pt-2">
                    <p class="text-xs text-[#8C7A81]">Menampilkan {{ pendingUsers.from }}-{{ pendingUsers.to }} dari {{ pendingUsers.total }} permohonan</p>
                    <div class="flex items-center gap-1">
                        <a v-if="pendingUsers.prev_page_url" :href="pendingUsers.prev_page_url" class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#DFD1D5] text-[#5A454C] hover:bg-[#FAF7F8] transition-colors"><ChevronLeft class="w-4 h-4" /></a>
                        <span class="px-3 py-1 text-xs font-semibold text-[#5A454C]">{{ pendingUsers.current_page }} / {{ pendingUsers.last_page }}</span>
                        <a v-if="pendingUsers.next_page_url" :href="pendingUsers.next_page_url" class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#DFD1D5] text-[#5A454C] hover:bg-[#FAF7F8] transition-colors"><ChevronRight class="w-4 h-4" /></a>
                    </div>
                </div>
            </div>

            <!-- ════ TAB: RESET PASSWORD ════ -->
            <div v-if="activeTab === 'password_reset'" class="space-y-3">
                <div v-if="passwordResetRequests.length === 0" class="bg-white rounded-2xl p-14 text-center border border-[#F0E6E8]">
                    <div class="w-14 h-14 rounded-full bg-violet-50 flex items-center justify-center mx-auto mb-4"><KeyRound class="w-7 h-7 text-violet-400" /></div>
                    <h3 class="text-base font-bold text-[#2A0711]">Tidak Ada Permintaan</h3>
                    <p class="text-xs text-gray-500 mt-1">Tidak ada permintaan reset password yang menunggu diproses.</p>
                </div>

                <div v-for="req in passwordResetRequests" :key="req.id" class="bg-white rounded-2xl border border-violet-100 shadow-xs hover:shadow-md hover:border-violet-200 transition-all duration-200 overflow-hidden">
                    <div class="flex">
                        <div class="w-1.5 bg-gradient-to-b from-violet-400 to-violet-700 flex-shrink-0"></div>
                        <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center text-base font-bold flex-shrink-0 shadow-sm">
                                    {{ req.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                        <h3 class="text-sm font-bold text-[#2A0711]">{{ req.user?.name }}</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700 border border-violet-200">Reset Password</span>
                                    </div>
                                    <p class="text-xs text-[#5A454C]">{{ req.user?.email }}</p>
                                    <p class="text-[11px] text-[#8C7A81] mt-0.5">Diminta {{ req.time_ago }} · {{ req.formatted_created_at }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                <button @click="openResetModal(req, 'resolve')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-sm transition-all cursor-pointer">
                                    <KeyRound class="w-4 h-4" /><span>Set Password</span>
                                </button>
                                <button @click="openResetModal(req, 'cancel')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 hover:border-rose-300 text-xs font-bold transition-all cursor-pointer">
                                    <XCircle class="w-4 h-4" /><span>Batalkan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Modal Konfirmasi ──────────────────────────── -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="selectedActionUser" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50 backdrop-blur-sm" @click.self="closeConfirmModal">
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div v-if="selectedActionUser" class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">
                    <div :class="['relative px-6 pt-6 pb-8 overflow-hidden', actionType === 'approve' ? 'bg-gradient-to-br from-emerald-500 to-emerald-700' : 'bg-gradient-to-br from-rose-500 to-rose-700']">
                        <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-8 -left-4 w-20 h-20 rounded-full bg-white/10"></div>
                        <div class="relative flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner">
                                <CheckCircle2 v-if="actionType === 'approve'" class="w-5 h-5 text-white" />
                                <AlertTriangle v-else class="w-5 h-5 text-white" />
                            </div>
                            <button type="button" @click="closeConfirmModal" :disabled="isProcessing" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50">
                                <X class="w-4 h-4 text-white" />
                            </button>
                        </div>
                        <div class="relative">
                            <h3 class="text-lg font-bold text-white leading-tight">{{ actionType === 'approve' ? 'Setujui Permohonan?' : 'Tolak Permohonan?' }}</h3>
                            <p class="text-xs text-white/80 mt-1 leading-relaxed">{{ actionType === 'approve' ? 'Pengguna akan mendapat akses penuh ke sistem sesuai role yang dipilih.' : 'Permohonan akan ditolak dan pengguna tidak dapat mengakses sistem.' }}</p>
                        </div>
                    </div>
                    <div class="px-6">
                        <div :class="['relative -mt-4 rounded-xl p-4 shadow-md border flex items-center gap-4', actionType === 'approve' ? 'bg-white border-emerald-100' : 'bg-white border-rose-100']">
                            <div :class="['w-12 h-12 rounded-full flex items-center justify-center text-base font-bold flex-shrink-0 shadow-sm', actionType === 'approve' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700']">
                                {{ selectedActionUser.name ? selectedActionUser.name.charAt(0).toUpperCase() : '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-[#2A0711] truncate">{{ selectedActionUser.name }}</p>
                                <p class="text-xs text-[#8C7A81] truncate mt-0.5">{{ selectedActionUser.email }}</p>
                            </div>
                            <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border flex-shrink-0', roleColor(selectedActionUser.role).badgeBg, roleColor(selectedActionUser.role).badgeText, roleColor(selectedActionUser.role).badgeBorder]">
                                {{ selectedActionUser.role_label }}
                            </span>
                        </div>
                    </div>
                    <div class="px-6 pt-4 pb-2">
                        <div :class="['flex items-start gap-2.5 rounded-xl px-3.5 py-3 text-xs leading-relaxed', actionType === 'approve' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800']">
                            <CheckCircle2 v-if="actionType === 'approve'" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-600" />
                            <AlertTriangle v-else class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-600" />
                            <span>
                                <span v-if="actionType === 'approve'">Akun akan <strong>langsung aktif</strong> dan pengguna dapat login ke sistem.</span>
                                <span v-else>Tindakan ini <strong>tidak dapat dibatalkan</strong>. Pengguna harus mendaftar ulang jika ingin mengajukan permohonan kembali.</span>
                            </span>
                        </div>
                    </div>
                    <div class="px-6 pt-3 pb-6 flex items-center gap-3">
                        <button type="button" @click="closeConfirmModal" :disabled="isProcessing" class="flex-1 px-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-white hover:bg-gray-50 text-sm font-semibold text-[#5A454C] transition-all cursor-pointer disabled:opacity-50">Batal</button>
                        <button v-if="actionType === 'approve'" type="button" @click="handleConfirmAction" :disabled="isProcessing" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70">
                            <svg v-if="isProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <CheckCircle2 v-else class="w-4 h-4" />
                            <span>{{ isProcessing ? 'Memproses...' : 'Setujui & Aktifkan' }}</span>
                        </button>
                        <button v-else type="button" @click="handleConfirmAction" :disabled="isProcessing" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70">
                            <svg v-if="isProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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

    <!-- ── Modal Reset Password (Admin Set New Password) ─────────────────────── -->
    <Teleport to="body">
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="selectedResetRequest" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50 backdrop-blur-sm" @click.self="closeResetModal">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-4 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0 translate-y-4">
            <div v-if="selectedResetRequest" class="w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden">

                <!-- Header gradient -->
                <div :class="['relative px-6 pt-6 pb-8 overflow-hidden', resetModalType === 'resolve' ? 'bg-gradient-to-br from-violet-500 to-violet-700' : 'bg-gradient-to-br from-rose-500 to-rose-700']">
                    <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-8 -left-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center justify-between mb-4">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center shadow-inner">
                            <KeyRound v-if="resetModalType === 'resolve'" class="w-5 h-5 text-white" />
                            <XCircle v-else class="w-5 h-5 text-white" />
                        </div>
                        <button type="button" @click="closeResetModal" :disabled="resetProcessing" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50">
                            <X class="w-4 h-4 text-white" />
                        </button>
                    </div>
                    <div class="relative">
                        <h3 class="text-lg font-bold text-white leading-tight">{{ resetModalType === 'resolve' ? 'Set Password Baru' : 'Batalkan Permintaan?' }}</h3>
                        <p class="text-xs text-white/80 mt-1 leading-relaxed">
                            {{ resetModalType === 'resolve' ? 'Masukkan password baru untuk pengguna ini.' : 'Permintaan reset password akan dibatalkan.' }}
                        </p>
                    </div>
                </div>

                <!-- User card -->
                <div class="px-6">
                    <div class="relative -mt-4 rounded-xl p-4 shadow-md border border-gray-100 bg-white flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-violet-100 text-violet-700 font-bold flex items-center justify-center text-sm shrink-0">
                            {{ selectedResetRequest.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-[#2A0711] truncate">{{ selectedResetRequest.user?.name }}</p>
                            <p class="text-xs text-[#8C7A81] truncate">{{ selectedResetRequest.user?.email }}</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 font-semibold border border-violet-200">Reset Password</span>
                    </div>
                </div>

                <!-- Form (only when resolve) -->
                <div v-if="resetModalType === 'resolve'" class="px-6 pt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1.5">Password Baru <span class="text-rose-600">*</span></label>
                        <input
                            v-model="resetNewPassword"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#443339] mb-1.5">Konfirmasi Password <span class="text-rose-600">*</span></label>
                        <input
                            v-model="resetConfirmPassword"
                            type="password"
                            placeholder="Ulangi password baru"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#DFD1D5] text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500"
                        />
                    </div>
                    <div v-if="resetPasswordError" class="flex items-center gap-2 text-xs text-rose-600 bg-rose-50 rounded-lg px-3 py-2 border border-rose-200">
                        <AlertTriangle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ resetPasswordError }}
                    </div>
                </div>

                <!-- Cancel warning -->
                <div v-else class="px-6 pt-4">
                    <div class="flex items-start gap-2.5 rounded-xl bg-rose-50 border border-rose-200 px-3.5 py-3 text-xs text-rose-800">
                        <AlertTriangle class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-600" />
                        <span>Permintaan akan ditandai <strong>dibatalkan</strong>. Pengguna dapat mengajukan permintaan baru kapanpun.</span>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="px-6 pt-4 pb-6 flex items-center gap-3">
                    <button type="button" @click="closeResetModal" :disabled="resetProcessing" class="flex-1 px-4 py-2.5 rounded-xl border border-[#DFD1D5] bg-white hover:bg-gray-50 text-sm font-semibold text-[#5A454C] transition-all cursor-pointer disabled:opacity-50">Batal</button>
                    <button v-if="resetModalType === 'resolve'" type="button" @click="handleResolve" :disabled="resetProcessing" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70">
                        <svg v-if="resetProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <KeyRound v-else class="w-4 h-4" />
                        <span>{{ resetProcessing ? 'Menyimpan...' : 'Simpan Password Baru' }}</span>
                    </button>
                    <button v-else type="button" @click="handleCancelReset" :disabled="resetProcessing" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold shadow-sm transition-all cursor-pointer disabled:opacity-70">
                        <svg v-if="resetProcessing" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <XCircle v-else class="w-4 h-4" />
                        <span>{{ resetProcessing ? 'Memproses...' : 'Ya, Batalkan' }}</span>
                    </button>
                </div>

            </div>
            </Transition>
        </div>
        </Transition>
    </Teleport>
</template>

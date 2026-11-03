<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    X,
    Calendar,
    CheckCircle2,
    Check,
    AlertTriangle,
    Clock,
    Factory,
    MessageSquare,
    FileText,
    AlertCircle,
    CalendarClock,
    RefreshCw
} from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    order: {
        type: Object,
        default: null,
    },
    konveksis: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const activeTab = ref('ringkasan'); // 'ringkasan', 'jadwal', 'riwayat'

// Form Persetujuan Pesanan & Penetapan Konveksi
const approveForm = useForm({
    konveksi_id: '',
});

// Modal Pop-up Negosiasi Tanggal oleh Admin Gudang
const showNegotiateModal = ref(false);
const negotiateForm = useForm({
    estimasi_usulan: '',
    catatan: '',
});

// Banner Negosiasi Aktif dari Admin Toko
const showNegotiationBanner = ref(false);
const respondMode = ref(null); // null | 'usul_balik'
const respondGudangForm = useForm({
    action: '',
    estimasi_usulan: '',
    catatan: '',
});

// Negosiasi menunggu dari Admin Toko
const activeTokoNegotiation = computed(() => {
    if (!props.order) return null;
    let neg = props.order.active_negotiation;
    if (!neg && props.order.negotiations && Array.isArray(props.order.negotiations)) {
        neg = props.order.negotiations.find(n => n.status === 'menunggu');
    }
    if (!neg) return null;
    return neg.role_pengaju === 'admin_toko' && neg.status === 'menunggu' ? neg : null;
});

const hasTokoNegotiation = computed(() => !!activeTokoNegotiation.value);

// Form Penjadwalan 4 Tahap Produksi
const scheduleForm = useForm({
    konveksi_id: '',
    stages: [
        { tahap: 'pemotongan', mulai: '', estimasi_selesai: '', status_tahap: 'belum_dimulai', catatan: '' },
        { tahap: 'sablon', mulai: '', estimasi_selesai: '', status_tahap: 'belum_dimulai', catatan: '' },
        { tahap: 'konveksi', mulai: '', estimasi_selesai: '', status_tahap: 'belum_dimulai', catatan: '' },
        { tahap: 'qc_gudang', mulai: '', estimasi_selesai: '', status_tahap: 'belum_dimulai', catatan: '' },
    ],
});

// Inisialisasi data form saat order berubah (JANGAN default-kan ke konveksi pertama jika belum dipilih!)
watch(
    () => props.order,
    (newOrder) => {
        if (!newOrder) return;
        approveForm.konveksi_id = newOrder.konveksi_id || '';
        scheduleForm.konveksi_id = newOrder.konveksi_id || '';

        const stageKeys = ['pemotongan', 'sablon', 'konveksi', 'qc_gudang'];
        scheduleForm.stages = stageKeys.map((key) => {
            const found = newOrder.stages?.find((s) => s.tahap === key);
            return {
                tahap: key,
                mulai: found?.mulai ? found.mulai.substring(0, 10) : '',
                estimasi_selesai: found?.estimasi_selesai ? found.estimasi_selesai.substring(0, 10) : '',
                status_tahap: found?.status_tahap || 'belum_dimulai',
                catatan: found?.catatan || '',
            };
        });

        // Auto-buka banner negosiasi jika ada negosiasi menunggu dari Admin Toko
        if (newOrder.active_negotiation?.role_pengaju === 'admin_toko' &&
            newOrder.active_negotiation?.status === 'menunggu') {
            showNegotiationBanner.value = true;
        }
    },
    { immediate: true }
);

// Submit Setujui Pesanan
const handleApprove = () => {
    if (!props.order) return;
    approveForm.post(`/gudang/orders/${props.order.id}/approve`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

// Buka Modal Negosiasi
const openNegotiateModal = () => {
    negotiateForm.estimasi_usulan = props.order?.estimasi_gudang || props.order?.target_selesai || '';
    negotiateForm.catatan = '';
    showNegotiateModal.value = true;
};

// Submit Negosiasi Tanggal
const handleNegotiateSubmit = () => {
    if (!props.order) return;
    negotiateForm.post(`/gudang/orders/${props.order.id}/negotiate`, {
        preserveScroll: true,
        onSuccess: () => {
            showNegotiateModal.value = false;
            emit('close');
        },
    });
};

// 6 Tahapan untuk Stepper (Identik dengan Admin Toko)
const steps = [
    { key: 'pesanan', label: 'Pesanan' },
    { key: 'pemotongan', label: 'Pemotongan' },
    { key: 'sablon', label: 'Sablon' },
    { key: 'konveksi', label: 'Konveksi' },
    { key: 'qc_gudang', label: 'QC Gudang' },
    { key: 'selesai', label: 'Selesai' },
];

const currentStepIndex = computed(() => {
    if (!props.order) return 0;
    if (props.order.current_step_index !== undefined) {
        return props.order.current_step_index;
    }
    const map = {
        menunggu_persetujuan: 0,
        pemotongan: 1,
        sablon: 2,
        dalam_produksi: 3,
        qc_gudang: 4,
        selesai: 5,
        dibatalkan: -1,
    };
    return map[props.order.status] ?? 3;
});

// Metadata 4 Tahap Produksi
const stageInfo = [
    { key: 'pemotongan', number: 1, name: 'Pemotongan', sub: 'Diperbarui oleh Admin Gudang' },
    { key: 'sablon', number: 2, name: 'Sablon', sub: 'Diperbarui oleh Admin Gudang' },
    { key: 'konveksi', number: 3, name: 'Produksi Konveksi', sub: 'Dikerjakan mitra konveksi' },
    { key: 'qc_gudang', number: 4, name: 'QC Gudang', sub: 'Pemeriksaan di gudang' },
];

// Validasi Tanggal Sekuensial & Internal (Client-side)
const dateErrors = computed(() => {
    const errs = [];
    const stageTitles = {
        pemotongan: 'Pemotongan',
        sablon: 'Sablon',
        konveksi: 'Produksi Konveksi',
        qc_gudang: 'QC Gudang',
    };

    // 1. Internal check: estimasi_selesai >= mulai
    for (let i = 0; i < scheduleForm.stages.length; i++) {
        const st = scheduleForm.stages[i];
        if (st.mulai && st.estimasi_selesai && st.estimasi_selesai < st.mulai) {
            errs.push({
                stageIndex: i,
                message: `Estimasi selesai ${stageTitles[st.tahap]} tidak boleh lebih awal dari tanggal mulai.`
            });
        }
    }

    // 2. Sequential check: mulai tahap i tidak boleh mendahului estimasi_selesai tahap i-1
    for (let i = 1; i < scheduleForm.stages.length; i++) {
        const prev = scheduleForm.stages[i - 1];
        const curr = scheduleForm.stages[i];
        if (prev.estimasi_selesai && curr.mulai && curr.mulai < prev.estimasi_selesai) {
            errs.push({
                stageIndex: i,
                message: `Tanggal mulai ${stageTitles[curr.tahap]} (${curr.mulai}) tidak boleh mendahului tanggal selesainya ${stageTitles[prev.tahap]} (${prev.estimasi_selesai}).`
            });
        }
    }

    return errs;
});

// Cek apakah ada tanggal tahap yang belum diisi (masih kosong)
const hasEmptyDates = computed(() => {
    return scheduleForm.stages.some(st => !st.mulai || !st.estimasi_selesai);
});

// Cek apakah jadwal produksi dapat disimpan
const canSaveSchedule = computed(() => {
    if (!props.order) return false;
    if (props.order.status === 'dibatalkan') return false;
    if (props.order.status === 'negosiasi') return false;
    if (props.order.status === 'menunggu_persetujuan') return false;
    if (!scheduleForm.konveksi_id) return false;
    if (hasEmptyDates.value) return false;
    if (dateErrors.value.length > 0) return false;
    return true;
});

const saveDisabledReason = computed(() => {
    if (!props.order) return '';
    if (props.order.status === 'dibatalkan') return 'Pesanan sudah dibatalkan.';
    if (props.order.status === 'negosiasi') return 'Jadwal belum dapat disimpan karena tanggal pesanan masih dalam proses negosiasi.';
    if (props.order.status === 'menunggu_persetujuan') return 'Pesanan belum disetujui. Setujui pesanan terlebih dahulu sebelum mengatur jadwal produksi.';
    if (!scheduleForm.konveksi_id) return 'Pilih tempat konveksi terlebih dahulu.';
    if (hasEmptyDates.value) return 'Lengkapi seluruh tanggal mulai dan estimasi selesai pada 4 tahapan produksi.';
    if (dateErrors.value.length > 0) return dateErrors.value[0].message;
    return '';
});

const getMinStartDate = (index) => {
    if (index === 0) return '';
    const prev = scheduleForm.stages[index - 1];
    return prev?.estimasi_selesai || '';
};

// Submit Simpan Jadwal Produksi
const handleSaveSchedule = () => {
    if (!props.order) return;
    if (!canSaveSchedule.value) {
        alert(saveDisabledReason.value || 'Jadwal produksi belum lengkap.');
        return;
    }
    scheduleForm.post(`/gudang/orders/${props.order.id}/schedule`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'Belum ditetapkan';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return dateStr;
    }
};

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    try {
        const d = new Date(timeStr);
        const datePart = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        const timePart = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');
        return `${datePart} · ${timePart}`;
    } catch {
        return timeStr;
    }
};

const getRelativeTime = (timeStr) => {
    if (!timeStr) return 'baru saja';
    try {
        const diffMs = new Date() - new Date(timeStr);
        const diffMins = Math.floor(diffMs / 60000);
        if (diffMins < 1) return 'baru saja';
        if (diffMins < 60) return `${diffMins} menit lalu`;
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return `${diffHours} jam lalu`;
        const diffDays = Math.floor(diffHours / 24);
        return `${diffDays} hari lalu`;
    } catch {
        return 'baru saja';
    }
};

const getHistoryIcon = (actionType) => {
    if (!actionType) return Factory;
    const lower = actionType.toLowerCase();
    if (lower.includes('jadwal') || lower.includes('progres') || lower.includes('tahap') || lower.includes('status')) return Factory;
    if (lower.includes('negosiasi') || lower.includes('tanggal')) return MessageSquare;
    return FileText;
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'dalam_produksi':
            return { label: 'Produksi Konveksi', bg: 'bg-sky-50 text-sky-700 border-sky-200/60', dot: 'bg-sky-500' };
        case 'negosiasi':
            return { label: 'Negosiasi', bg: 'bg-amber-50 text-amber-700 border-amber-200/60', dot: 'bg-amber-500' };
        case 'qc_gudang':
            return { label: 'QC Gudang', bg: 'bg-sky-50 text-sky-700 border-sky-200/60', dot: 'bg-sky-500' };
        case 'selesai':
            return { label: 'Selesai', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200/60', dot: 'bg-emerald-500' };
        case 'dibatalkan':
            return { label: 'Cancel', bg: 'bg-rose-50 text-rose-500 border-rose-200/60', dot: 'bg-rose-400' };
        case 'menunggu_persetujuan':
        default:
            return { label: 'Menunggu Persetujuan', bg: 'bg-amber-50 text-amber-700 border-amber-200/60', dot: 'bg-amber-500' };
    }
};
</script>

<template>
    <div
        v-if="show && order"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-hidden"
    >
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 md:p-8 shadow-2xl border border-gray-100 max-h-[90vh] flex flex-col transition-all">
            <!-- Modal Header (Fixed at top) -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                        {{ order.id_pesanan }}
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ order.nama_customer }} · {{ order.kode_seri }} · {{ order.jumlah }} pcs
                    </p>
                    <div class="flex items-center gap-2.5 mt-2.5">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border"
                            :class="getStatusBadge(order.status).bg"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusBadge(order.status).dot"></span>
                            {{ getStatusBadge(order.status).label }}
                        </span>
                        <span class="text-xs text-slate-400">
                            Terakhir diperbarui {{ getRelativeTime(order.updated_at) }}
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="p-2 border border-slate-200 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-50 transition cursor-pointer"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Tab Navigasi Garis Bawah Merah Marun (Fixed at top) -->
            <div class="flex items-center gap-6 border-b border-slate-100 mt-4 mb-4 text-sm shrink-0">
                <button
                    type="button"
                    @click="activeTab = 'ringkasan'"
                    class="pb-3 font-semibold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'ringkasan' ? 'border-[#7A1228] text-[#7A1228] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Ringkasan
                </button>
                <button
                    type="button"
                    @click="activeTab = 'jadwal'"
                    class="pb-3 font-semibold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'jadwal' ? 'border-[#7A1228] text-[#7A1228] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Jadwal & progres
                </button>
                <button
                    type="button"
                    @click="activeTab = 'riwayat'"
                    class="pb-3 font-semibold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'riwayat' ? 'border-[#7A1228] text-[#7A1228] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Riwayat
                </button>
            </div>

            <!-- Konten Modal (Scrollable di dalam modal saja) -->
            <div class="flex-1 overflow-y-auto pr-2 space-y-6">
                <!-- ============================================== -->
                <!-- BANNER NEGOSIASI AKTIF DARI ADMIN TOKO         -->
                <!-- ============================================== -->
                <div v-if="hasTokoNegotiation && showNegotiationBanner" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 space-y-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-amber-800 font-bold text-xs">
                            <CalendarClock class="w-4 h-4 shrink-0" />
                            <span>Permintaan Perubahan Tanggal dari Admin Toko</span>
                        </div>
                        <button
                            type="button"
                            @click="showNegotiationBanner = false"
                            class="p-1 text-amber-600 hover:text-amber-900 rounded-lg hover:bg-amber-100/60 transition cursor-pointer"
                            title="Tutup banner"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Info usulan -->
                    <div class="text-xs text-amber-900 space-y-0.5">
                        <p>
                            Usulan tanggal baru:
                            <strong>{{ activeTokoNegotiation ? new Date(activeTokoNegotiation.estimasi_usulan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '' }}</strong>
                        </p>
                        <p v-if="activeTokoNegotiation?.catatan" class="italic text-amber-700">
                            "{{ activeTokoNegotiation.catatan }}"
                        </p>
                    </div>

                    <!-- Tombol aksi -->
                    <div v-if="!respondMode" class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            :disabled="respondGudangForm.processing"
                            @click="() => {
                                respondGudangForm.action = 'setuju';
                                respondGudangForm.post(`/gudang/orders/${order.id}/negotiations/${activeTokoNegotiation.id}/respond`, {
                                    preserveScroll: true,
                                    onSuccess: () => {
                                        showNegotiationBanner = false;
                                        activeTab = 'jadwal';
                                        emit('close');
                                    }
                                });
                            }"
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl flex items-center gap-1.5 cursor-pointer disabled:opacity-50 transition"
                        >
                            <CheckCircle2 class="w-3.5 h-3.5" />
                            {{ respondGudangForm.processing ? 'Memproses...' : 'Setujui Perubahan Tanggal' }}
                        </button>
                        <button
                            type="button"
                            @click="respondMode = 'usul_balik'"
                            class="px-4 py-2 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-xl flex items-center gap-1.5 cursor-pointer transition"
                        >
                            <RefreshCw class="w-3.5 h-3.5" />
                            Usul Balik
                        </button>
                    </div>

                    <!-- Form Usul Balik -->
                    <div v-if="respondMode === 'usul_balik'" class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">
                                Tanggal Usulan Balik <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="respondGudangForm.estimasi_usulan"
                                type="date"
                                required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-amber-300 focus:border-amber-400"
                            />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">
                                Catatan <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                v-model="respondGudangForm.catatan"
                                rows="2"
                                required
                                placeholder="Jelaskan alasan usulan balik..."
                                class="w-full px-3 py-2 text-xs rounded-xl border border-amber-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-amber-300 focus:border-amber-400"
                            ></textarea>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button
                                type="button"
                                @click="respondMode = null"
                                class="px-3 py-1.5 text-xs text-amber-700 bg-white border border-amber-200 rounded-lg cursor-pointer hover:bg-amber-50 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                :disabled="respondGudangForm.processing"
                                @click="() => {
                                    respondGudangForm.action = 'usul_balik';
                                    respondGudangForm.post(`/gudang/orders/${order.id}/negotiations/${activeTokoNegotiation.id}/respond`, {
                                        preserveScroll: true,
                                        onSuccess: () => { respondMode = null; showNegotiationBanner = false; emit('close'); }
                                    });
                                }"
                                class="px-4 py-1.5 text-xs font-bold text-white bg-amber-700 hover:bg-amber-800 rounded-lg cursor-pointer disabled:opacity-50 transition"
                            >
                                {{ respondGudangForm.processing ? 'Mengirim...' : 'Kirim Usulan Balik' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tombol re-open banner jika ditutup sementara -->
                <div v-if="hasTokoNegotiation && !showNegotiationBanner" class="flex">
                    <button
                        type="button"
                        @click="showNegotiationBanner = true"
                        class="px-3 py-1.5 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 cursor-pointer transition flex items-center gap-1.5"
                    >
                        <CalendarClock class="w-3.5 h-3.5" />
                        Ada permintaan negosiasi dari Toko — Lihat
                    </button>
                </div>

                <!-- ============================================== -->
                <!-- TAB 1: RINGKASAN (Dibuat sama kaya Admin Toko)  -->
                <!-- ============================================== -->
                <div v-if="activeTab === 'ringkasan'" class="space-y-6">
                    <!-- 6 Grey Rounded Info Boxes (Identik dengan Admin Toko) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Nama customer</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.nama_customer }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Kode seri</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.kode_seri }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Jumlah</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.jumlah }} pcs</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Target penyelesaian</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ formatDate(order.target_selesai) }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Estimasi gudang</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ formatDate(order.estimasi_gudang) }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Tempat konveksi</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.konveksi ? order.konveksi.nama : 'Belum ditetapkan' }}</span>
                        </div>
                    </div>

                    <!-- Section: ALUR PENGERJAAN (Identik dengan Admin Toko) -->
                    <div class="pt-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-4">
                            ALUR PENGERJAAN
                        </span>

                        <div class="relative flex items-center justify-between px-2 pt-2 pb-4">
                            <div class="absolute left-6 right-6 top-[22px] h-[2px] bg-slate-200 z-0"></div>

                            <div
                                v-for="(st, idx) in steps"
                                :key="st.key"
                                class="relative z-10 flex flex-col items-center"
                            >
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-2xs"
                                    :class="[
                                        idx < currentStepIndex
                                            ? 'bg-emerald-700 text-white'
                                            : idx === currentStepIndex
                                            ? 'bg-[#7A1228] text-white shadow-sm'
                                            : 'bg-white text-slate-400 border border-slate-200'
                                    ]"
                                >
                                    <Check v-if="idx < currentStepIndex" class="w-4 h-4 stroke-[3]" />
                                    <span v-else>{{ idx + 1 }}</span>
                                </div>

                                <span
                                    class="text-[11px] mt-2 block text-center"
                                    :class="idx === currentStepIndex ? 'font-bold text-slate-900' : 'text-slate-500 font-medium'"
                                >
                                    {{ st.label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 2: JADWAL & PROGRES                        -->
                <!-- ============================================== -->
                <div v-if="activeTab === 'jadwal'" class="space-y-5">
                    <!-- Alert: tanggal berubah akibat negosiasi disetujui -->
                    <div
                        v-if="order.histories && order.histories.some(h => h.aksi === 'NEGOSIASI_DISETUJUI')"
                        class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-start gap-3 text-xs text-amber-800"
                    >
                        <AlertTriangle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                        <span class="font-semibold">
                            Tanggal pesanan telah berubah akibat negosiasi yang disetujui. Perbarui jadwal tahap produksi yang <strong>belum selesai</strong> agar sesuai dengan tanggal baru.
                        </span>
                    </div>

                    <!-- Top Banner: Jadwal produksi gudang -->
                    <div class="bg-[#FDF6F7] border border-[#F6DCE1] rounded-2xl p-4 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-[#7A1228]">Jadwal produksi gudang</h4>
                            <p class="text-xs text-[#8A3A4A] mt-0.5">Isi estimasi dan status untuk setiap tahap operasional.</p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border"
                            :class="getStatusBadge(order.status).bg"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusBadge(order.status).dot"></span>
                            {{ getStatusBadge(order.status).label }}
                        </span>
                    </div>

                    <!-- Input Pilihan Tempat Konveksi Full Width -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">
                            Tempat Konveksi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="scheduleForm.konveksi_id"
                            class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-white text-xs font-medium text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] cursor-pointer"
                        >
                            <option value="">-- Pilih Tempat Konveksi --</option>
                            <option v-for="kv in konveksis" :key="kv.id" :value="kv.id">
                                {{ kv.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Form 4 Tahapan Produksi Berurutan -->
                    <div class="space-y-3">
                        <div
                            v-for="(info, idx) in stageInfo"
                            :key="info.key"
                            class="p-4 rounded-2xl border border-slate-100 bg-white shadow-2xs hover:border-slate-200 transition"
                        >
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-3 md:w-1/3">
                                    <div class="w-8 h-8 rounded-full bg-[#FDF2F4] text-[#7A1228] font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ info.number }}
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900">{{ info.name }}</h5>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ info.sub }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:w-2/3">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            MULAI
                                        </label>
                                        <input
                                            v-model="scheduleForm.stages[idx].mulai"
                                            type="date"
                                            :min="getMinStartDate(idx)"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            ESTIMASI SELESAI
                                        </label>
                                        <input
                                            v-model="scheduleForm.stages[idx].estimasi_selesai"
                                            type="date"
                                            :min="scheduleForm.stages[idx].mulai"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                            STATUS TAHAP
                                        </label>
                                        <select
                                            v-model="scheduleForm.stages[idx].status_tahap"
                                            class="w-full px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] cursor-pointer"
                                        >
                                            <option value="belum_dimulai">Belum Dimulai</option>
                                            <option value="sedang_dikerjakan">Sedang Dikerjakan</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="dateErrors.some(e => e.stageIndex === idx)"
                                class="mt-2 text-rose-600 text-[11px] font-semibold flex items-center gap-1.5"
                            >
                                <AlertCircle class="w-3.5 h-3.5" />
                                <span>{{ dateErrors.find(e => e.stageIndex === idx)?.message }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan Jadwal Produksi -->
                    <div class="flex flex-col sm:flex-row items-end sm:items-center justify-between gap-3 pt-2">
                        <p v-if="!canSaveSchedule && saveDisabledReason" class="text-[11px] font-medium text-amber-700 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200/70">
                            ℹ️ {{ saveDisabledReason }}
                        </p>
                        <div v-else></div>

                        <button
                            type="button"
                            @click="handleSaveSchedule"
                            :disabled="!canSaveSchedule || scheduleForm.processing"
                            class="px-6 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-xs shrink-0"
                        >
                            <Check class="w-4 h-4" />
                            <span>{{ scheduleForm.processing ? 'Menyimpan...' : 'Simpan jadwal produksi' }}</span>
                        </button>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 3: RIWAYAT                                 -->
                <!-- ============================================== -->
                <div v-if="activeTab === 'riwayat'" class="space-y-4">
                    <div
                        v-if="!order.histories || order.histories.length === 0"
                        class="text-center py-12 text-slate-400 text-xs"
                    >
                        Belum ada riwayat aktivitas yang tercatat.
                    </div>

                    <div
                        v-for="hist in order.histories"
                        :key="hist.id"
                        class="flex items-start gap-4 py-2 border-b border-slate-50 last:border-b-0"
                    >
                        <div class="w-10 h-10 rounded-full bg-[#FDF2F4] text-[#7A1228] flex items-center justify-center shrink-0">
                            <component :is="getHistoryIcon(hist.aksi)" class="w-4 h-4" />
                        </div>

                        <div class="flex-1">
                            <h5 class="text-xs font-bold text-slate-900">{{ hist.aksi }}</h5>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ hist.deskripsi }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ formatTime(hist.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal (Fixed at bottom, Tanpa Tombol Batalkan) -->
            <div class="pt-5 mt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
                <!-- Kiri: Negosiasi Tanggal (Berfungsi di tab mana pun) -->
                <button
                    type="button"
                    @click="openNegotiateModal"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs"
                >
                    <Calendar class="w-4 h-4 text-slate-700" />
                    <span>Negosiasi tanggal</span>
                </button>

                <!-- Kanan: Pilih Konveksi + Setujui Pesanan -->
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <select
                        v-model="approveForm.konveksi_id"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] cursor-pointer"
                    >
                        <option value="">Belum ditetapkan</option>
                        <option v-for="kv in konveksis" :key="kv.id" :value="kv.id">
                            {{ kv.nama }}
                        </option>
                    </select>

                    <button
                        type="button"
                        @click="handleApprove"
                        :disabled="!approveForm.konveksi_id || approveForm.processing || order.status === 'dibatalkan'"
                        class="px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 shadow-xs whitespace-nowrap"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>{{ approveForm.processing ? 'Menyimpan...' : 'Setujui pesanan' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Pop-up Negosiasi Tanggal oleh Admin Gudang -->
        <div
            v-if="showNegotiateModal"
            class="fixed inset-0 z-60 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Negosiasi Tanggal Penyelesaian</h3>
                    <button
                        type="button"
                        @click="showNegotiateModal = false"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="handleNegotiateSubmit" class="space-y-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Usulan Tanggal Selesai Gudang <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="negotiateForm.estimasi_usulan"
                            type="date"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan / Catatan Negosiasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="negotiateForm.catatan"
                            rows="3"
                            required
                            placeholder="Jelaskan alasan penyesuaian tanggal (kapasitas konveksi, kendala bahan, dll)..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showNegotiateModal = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="negotiateForm.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-[#7A1228] hover:bg-[#600e1f] rounded-xl transition shadow-xs disabled:opacity-50 cursor-pointer"
                        >
                            {{ negotiateForm.processing ? 'Mengirim...' : 'Kirim Usulan Negosiasi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

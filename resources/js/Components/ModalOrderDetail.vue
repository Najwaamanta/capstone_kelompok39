<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    X,
    Calendar,
    XCircle,
    Check,
    Factory,
    MessageSquare,
    FileText,
    AlertCircle,
    Clock,
    CalendarClock,
    CheckCircle2,
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
});

const emit = defineEmits(['close']);

const activeTab = ref('ringkasan'); // 'ringkasan', 'jadwal', 'riwayat'

// Form Batalkan Pesanan (US-T06)
const showCancelConfirm = ref(false);
const cancelForm = useForm({
    alasan: '',
});

// Modal Pop-up Negosiasi Tanggal (US-T07)
const showNegotiateModal = ref(false);
const negotiateForm = useForm({
    estimasi_usulan: '',
    catatan: '',
});

// Modal Respons negosiasi aktif dari Gudang
const showNegotiationBanner = ref(false);
const respondForm = useForm({
    action: '',
    estimasi_usulan: '',
    catatan: '',
});
const respondMode = ref(null); // null | 'usul_balik'

// Buka banner negosiasi secara otomatis jika ada negosiasi menunggu dari Gudang
const activeNegotiation = computed(() => {
    if (!props.order) return null;
    if (props.order.active_negotiation) return props.order.active_negotiation;
    if (props.order.negotiations && Array.isArray(props.order.negotiations)) {
        return props.order.negotiations.find(n => n.status === 'menunggu') || null;
    }
    return null;
});

const hasGudangNegotiation = computed(() => {
    return activeNegotiation.value &&
        activeNegotiation.value.role_pengaju === 'admin_gudang' &&
        activeNegotiation.value.status === 'menunggu';
});

// Dinamis hitung badge status berdasarkan order.status agar selalu tepat di semua view
const statusPill = computed(() => {
    if (!props.order) return { label: 'Produksi Konveksi', class: 'bg-blue-50 text-blue-700' };
    const status = props.order.status;
    switch (status) {
        case 'negosiasi':
            return { label: 'Negosiasi', class: 'bg-amber-50 text-amber-700' };
        case 'menunggu_persetujuan':
            return { label: 'Menunggu Persetujuan', class: 'bg-amber-50 text-amber-700' };
        case 'qc_gudang':
            return { label: 'QC Gudang', class: 'bg-purple-50 text-purple-700' };
        case 'selesai':
            return { label: 'Selesai', class: 'bg-emerald-50 text-emerald-700' };
        case 'dibatalkan':
            return { label: 'Cancel', class: 'bg-rose-50 text-rose-700' };
        case 'dalam_produksi':
        default:
            return { label: 'Produksi Konveksi', class: 'bg-blue-50 text-blue-700' };
    }
});

watch(
    () => props.order,
    (newOrder) => {
        if (!newOrder) return;
        negotiateForm.estimasi_usulan = newOrder.estimasi_gudang || newOrder.target_selesai || '';
        negotiateForm.catatan = '';
        // Auto-buka banner negosiasi jika ada negosiasi menunggu
        if (newOrder.active_negotiation?.role_pengaju === 'admin_gudang' &&
            newOrder.active_negotiation?.status === 'menunggu') {
            showNegotiationBanner.value = true;
        }
    },
    { immediate: true }
);

const openNegotiateModal = () => {
    negotiateForm.estimasi_usulan = props.order?.estimasi_gudang || props.order?.target_selesai || '';
    negotiateForm.catatan = '';
    showNegotiateModal.value = true;
};

const handleCancelOrder = () => {
    if (!props.order) return;
    cancelForm.post(`/toko/orders/${props.order.id}/cancel`, {
        preserveScroll: true,
        onSuccess: () => {
            showCancelConfirm.value = false;
            emit('close');
        },
    });
};

const handleProposeNegotiation = () => {
    if (!props.order) return;
    negotiateForm.post(`/toko/orders/${props.order.id}/negotiate`, {
        preserveScroll: true,
        onSuccess: () => {
            showNegotiateModal.value = false;
            emit('close');
        },
    });
};

// 6 Tahapan untuk Stepper
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

// Helper 4 stages list for Tab 2
const scheduleStages = computed(() => {
    if (!props.order) return [];
    const stages = props.order.stages || [];

    const getStageData = (key, defaultTitle) => {
        const found = stages.find((s) => s.tahap === key);
        return {
            title: defaultTitle,
            status: found?.status_label || (found?.status_tahap === 'selesai' ? 'Selesai' : found?.status_tahap === 'sedang_dikerjakan' ? 'Sedang Dikerjakan' : 'Belum Dimulai'),
            estimate: found?.estimasi_selesai
                ? new Date(found.estimasi_selesai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                : null,
            rawStatus: found?.status_tahap || 'belum_dimulai',
        };
    };

    return [
        getStageData('pemotongan', 'Pemotongan'),
        getStageData('sablon', 'Sablon'),
        getStageData('konveksi', 'Produksi Konveksi'),
        getStageData('qc_gudang', 'QC Gudang'),
    ];
});

// Helper timeline for Tab 3
const timelineItems = computed(() => {
    if (!props.order) return [];
    if (props.order.histories && props.order.histories.length > 0) {
        return props.order.histories.map((h, i) => {
            let icon = Factory;
            if (i === 1 || h.aksi?.toLowerCase().includes('estimasi') || h.aksi?.toLowerCase().includes('negosiasi')) {
                icon = MessageSquare;
            } else if (i === 2 || h.aksi?.toLowerCase().includes('nota') || h.aksi?.toLowerCase().includes('pesanan')) {
                icon = FileText;
            }
            return {
                id: h.id,
                icon,
                title: h.aksi,
                subtitle: h.deskripsi,
                time: h.created_at ? new Date(h.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(' pukul ', ' · ') : '',
            };
        });
    }

    return [
        {
            id: 1,
            icon: Factory,
            title: 'Status diperbarui menjadi Produksi Konveksi',
            subtitle: 'Oleh Nadia Putri',
            time: '23 Jun 2025 · 09.12',
        },
        {
            id: 2,
            icon: MessageSquare,
            title: 'Estimasi penyelesaian disepakati',
            subtitle: 'Estimasi: 27 Jun 2025',
            time: '22 Jun 2025 · 15.40',
        },
        {
            id: 3,
            icon: FileText,
            title: 'Pesanan dan nota digital dibuat',
            subtitle: 'Oleh Admin Toko',
            time: '21 Jun 2025 · 10.04',
        },
    ];
});
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
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1.5"
                            :class="statusPill.class"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
                            {{ statusPill.label }}
                        </span>
                        <span class="text-xs text-slate-400">
                            Terakhir diperbarui 8 menit lalu
                        </span>
                    </div>
                </div>

                <!-- Close button -->
                <button
                    type="button"
                    @click="emit('close')"
                    class="p-2 border border-slate-200 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-50 transition cursor-pointer"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Tabs Navigasi (Fixed at top) -->
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

                <!-- ================================================= -->
                <!-- BANNER NEGOSIASI AKTIF DARI GUDANG                 -->
                <!-- Muncul di tab mana pun saat ada negosiasi menunggu -->
                <!-- ================================================= -->
                <div v-if="hasGudangNegotiation && showNegotiationBanner" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 space-y-3">
                    <!-- Header banner -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-amber-800 font-bold text-xs">
                            <CalendarClock class="w-4 h-4 shrink-0" />
                            <span>Permintaan Perubahan Tanggal dari Admin Gudang</span>
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
                            <strong>{{ activeNegotiation ? new Date(activeNegotiation.estimasi_usulan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '' }}</strong>
                        </p>
                        <p v-if="activeNegotiation?.catatan" class="italic text-amber-700">
                            "{{ activeNegotiation.catatan }}"
                        </p>
                    </div>

                    <!-- Tombol aksi (saat belum di mode usul_balik) -->
                    <div v-if="!respondMode" class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            :disabled="respondForm.processing"
                            @click="() => {
                                respondForm.action = 'setuju';
                                respondForm.post(`/toko/orders/${order.id}/negotiations/${activeNegotiation.id}/respond`, {
                                    preserveScroll: true,
                                    onSuccess: () => { showNegotiationBanner = false; emit('close'); }
                                });
                            }"
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl flex items-center gap-1.5 cursor-pointer disabled:opacity-50 transition"
                        >
                            <CheckCircle2 class="w-3.5 h-3.5" />
                            {{ respondForm.processing ? 'Memproses...' : 'Setujui Perubahan Tanggal' }}
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
                                v-model="respondForm.estimasi_usulan"
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
                                v-model="respondForm.catatan"
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
                                :disabled="respondForm.processing"
                                @click="() => {
                                    respondForm.action = 'usul_balik';
                                    respondForm.post(`/toko/orders/${order.id}/negotiations/${activeNegotiation.id}/respond`, {
                                        preserveScroll: true,
                                        onSuccess: () => { respondMode = null; showNegotiationBanner = false; emit('close'); }
                                    });
                                }"
                                class="px-4 py-1.5 text-xs font-bold text-white bg-amber-700 hover:bg-amber-800 rounded-lg cursor-pointer disabled:opacity-50 transition"
                            >
                                {{ respondForm.processing ? 'Mengirim...' : 'Kirim Usulan Balik' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tombol re-open banner jika ditutup sementara -->
                <div v-if="hasGudangNegotiation && !showNegotiationBanner" class="flex">
                    <button
                        type="button"
                        @click="showNegotiationBanner = true"
                        class="px-3 py-1.5 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 cursor-pointer transition flex items-center gap-1.5"
                    >
                        <CalendarClock class="w-3.5 h-3.5" />
                        Ada permintaan negosiasi dari Gudang — Lihat
                    </button>
                </div>

                <!-- TAB 1: RINGKASAN -->
                <div v-if="activeTab === 'ringkasan'" class="space-y-6">
                    <!-- 6 Grey Rounded Info Boxes -->
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
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.formatted_target || '28 Jun 2025' }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Estimasi gudang</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.formatted_estimasi_gudang || '27 Jun 2025' }}</span>
                        </div>

                        <div class="bg-[#FBF9F9] p-4 rounded-2xl border border-slate-100">
                            <span class="text-[11px] text-slate-400 block font-medium">Tempat konveksi</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ order.konveksi_nama || order.konveksi?.nama || 'Belum ditetapkan' }}</span>
                        </div>
                    </div>

                    <!-- Section: ALUR PENGERJAAN -->
                    <div class="pt-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-4">
                            ALUR PENGERJAAN
                        </span>

                        <!-- 6 Steps Stepper -->
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

                <!-- TAB 2: JADWAL & PROGRES -->
                <div v-if="activeTab === 'jadwal'" class="space-y-1">
                    <div
                        v-for="(stg, sIdx) in scheduleStages"
                        :key="sIdx"
                        class="flex items-center justify-between py-4 border-b border-slate-100 last:border-b-0"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                :class="[
                                    stg.rawStatus === 'selesai'
                                        ? 'bg-emerald-700 text-white'
                                        : stg.rawStatus === 'sedang_dikerjakan'
                                        ? 'bg-[#7A1228] text-white'
                                        : 'bg-slate-100 text-slate-400 font-semibold'
                                ]"
                            >
                                <Check v-if="stg.rawStatus === 'selesai'" class="w-4 h-4 stroke-[3]" />
                                <span v-else>{{ sIdx + 1 }}</span>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold text-slate-900 leading-tight">
                                    {{ stg.title }}
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5 leading-tight">
                                    {{ stg.status }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block font-medium">Estimasi</span>
                            <span
                                class="text-xs font-bold block mt-0.5"
                                :class="stg.estimate ? 'text-slate-900' : 'text-slate-400 italic'"
                            >
                                {{ stg.estimate ?? 'Belum diisi' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: RIWAYAT -->
                <div v-if="activeTab === 'riwayat'" class="space-y-1">
                    <div
                        v-for="item in timelineItems"
                        :key="item.id"
                        class="flex items-start gap-3.5 py-4 border-b border-slate-100 last:border-b-0"
                    >
                        <div class="w-9 h-9 rounded-full bg-[#FCE7EB] text-[#7A1228] flex items-center justify-center shrink-0 mt-0.5">
                            <component :is="item.icon" class="w-4 h-4" />
                        </div>

                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                {{ item.title }}
                            </h4>
                            <p class="text-xs text-slate-400 mt-0.5 leading-snug">
                                {{ item.subtitle }}
                            </p>
                            <span class="text-[11px] text-slate-400 block mt-1.5 font-medium">
                                {{ item.time }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer: Action Buttons (Fixed at bottom) -->
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center gap-3 shrink-0">
                <!-- Negosiasi Tanggal Button (Berfungsi di tab mana pun) -->
                <button
                    type="button"
                    @click="openNegotiateModal"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs flex items-center gap-2 shadow-2xs cursor-pointer"
                >
                    <Calendar class="w-4 h-4 text-slate-600" />
                    <span>Negosiasi tanggal</span>
                </button>

                <!-- Batalkan Button -->
                <button
                    type="button"
                    @click="showCancelConfirm = true"
                    class="px-4 py-2.5 rounded-xl bg-[#FDF1F3] hover:bg-[#fae2e7] text-[#7A1228] font-bold text-xs flex items-center gap-2 cursor-pointer transition"
                >
                    <XCircle class="w-4 h-4" />
                    <span>Batalkan</span>
                </button>
            </div>
        </div>

        <!-- Modal Pop-up: Negosiasi Tanggal oleh Toko -->
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

                <form @submit.prevent="handleProposeNegotiation" class="space-y-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Usulan Estimasi Tanggal Penyelesaian Baru <span class="text-rose-500">*</span>
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
                            Catatan / Alasan Penyesuaian <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="negotiateForm.catatan"
                            rows="3"
                            required
                            placeholder="Jelaskan alasan penyesuaian tanggal ke Admin Gudang..."
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

        <!-- Confirm Modal Pembatalan Pesanan -->
        <div
            v-if="showCancelConfirm"
            class="fixed inset-0 z-60 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-rose-100">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                    <AlertCircle class="w-6 h-6 stroke-[2.5]" />
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Batalkan Pesanan Ini?</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Pesanan <strong class="text-slate-800">{{ order.id_pesanan }}</strong> akan dihentikan dari sistem produksi dan statusnya menjadi <strong class="text-rose-600">Cancel</strong>. Aksi ini tidak dapat dibatalkan kembali.
                </p>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan Pembatalan</label>
                    <textarea
                        v-model="cancelForm.alasan"
                        rows="2"
                        placeholder="Contoh: Permintaan customer..."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-hidden focus:border-rose-500"
                    ></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="showCancelConfirm = false"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 cursor-pointer"
                    >
                        Kembali
                    </button>
                    <button
                        type="button"
                        @click="handleCancelOrder"
                        :disabled="cancelForm.processing"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow-sm shadow-rose-600/20 cursor-pointer"
                    >
                        {{ cancelForm.processing ? 'Membatalkan...' : 'Ya, Batalkan Pesanan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

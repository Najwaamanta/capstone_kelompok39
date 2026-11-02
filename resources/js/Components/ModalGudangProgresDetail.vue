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

// Tab bawaan default adalah Jadwal & progres sesuai Figma
const activeTab = ref('jadwal'); // 'ringkasan', 'jadwal', 'riwayat'

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

// Modal Pop-up Batalkan Pesanan oleh Admin Gudang
const showCancelModal = ref(false);
const cancelForm = useForm({
    alasan_pembatalan: '',
});

const handleCancelSubmit = () => {
    if (!props.order) return;
    showCancelModal.value = false;
    alert(`Pesanan ${props.order.id_pesanan} dibatalkan.`);
    emit('close');
};

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

// Inisialisasi data form saat order berubah
watch(
    () => props.order,
    (newOrder) => {
        if (!newOrder) return;
        activeTab.value = 'jadwal';
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
        negosiasi: 0,
        dibatalkan: -1,
    };
    return map[props.order.status] ?? 0;
});

// Metadata 4 Tahap Produksi Sesuai Figma
const stageInfo = [
    { key: 'pemotongan', number: 1, name: 'Pemotongan', sub: 'Operasional oleh Admin Gudang' },
    { key: 'sablon', number: 2, name: 'Sablon', sub: 'Operasional oleh Admin Gudang' },
    { key: 'konveksi', number: 3, name: 'Produksi Konveksi', sub: 'Dikerjakan mitra konveksi' },
    { key: 'qc_gudang', number: 4, name: 'QC Gudang', sub: 'Pemeriksaan di gudang' },
];

// Validasi Form Jadwal
const canSaveSchedule = computed(() => {
    if (!scheduleForm.konveksi_id) return false;
    for (const st of scheduleForm.stages) {
        if (!st.mulai || !st.estimasi_selesai) return false;
    }
    return true;
});

const handleSaveSchedule = () => {
    if (!props.order || !canSaveSchedule.value) return;
    scheduleForm.post(`/gudang/orders/${props.order.id}/schedule`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'menunggu_persetujuan':
            return { label: 'Menunggu Persetujuan', bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200/60' };
        case 'negosiasi':
            return { label: 'Negosiasi', bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200/60' };
        case 'dalam_produksi':
            return { label: 'Produksi Konveksi', bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200/60' };
        case 'qc_gudang':
            return { label: 'QC Gudang', bg: 'bg-sky-50', text: 'text-sky-700', border: 'border-sky-200/60' };
        case 'selesai':
            return { label: 'Selesai', bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200/60' };
        case 'dibatalkan':
            return { label: 'Dibatalkan', bg: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200/60' };
        default:
            return { label: status, bg: 'bg-slate-50', text: 'text-slate-700', border: 'border-slate-200/60' };
    }
};
</script>

<template>
    <div
        v-if="show && order"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/40 backdrop-blur-xs overflow-y-auto"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150 p-6 sm:p-7">
            <!-- Header Modal Sesuai Figma -->
            <div class="flex items-start justify-between pb-3 shrink-0">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ order.id_pesanan }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ order.nama_customer }} &bull; {{ order.kode_seri }} &bull; {{ order.jumlah }} pcs
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>{{ getStatusBadge(order.status).label }} &bull; Terakhir diperbarui 8 menit lalu</span>
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition cursor-pointer"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Tab Navigasi: Ringkasan, Jadwal & progres, Riwayat -->
            <div class="flex items-center gap-6 border-b border-slate-100 mt-2 shrink-0">
                <button
                    type="button"
                    @click="activeTab = 'ringkasan'"
                    class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'ringkasan' ? 'border-[#7A1228] text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700'"
                >
                    Ringkasan
                </button>
                <button
                    type="button"
                    @click="activeTab = 'jadwal'"
                    class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'jadwal' ? 'border-[#7A1228] text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700'"
                >
                    Jadwal & progres
                </button>
                <button
                    type="button"
                    @click="activeTab = 'riwayat'"
                    class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
                    :class="activeTab === 'riwayat' ? 'border-[#7A1228] text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-700'"
                >
                    Riwayat
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="overflow-y-auto flex-1 py-4 space-y-5 pr-1">
                <!-- TAB 1: RINGKASAN -->
                <div v-if="activeTab === 'ringkasan'" class="space-y-4">
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3">
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Customer</span>
                                <span class="font-bold text-slate-800">{{ order.nama_customer }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Model & Seri</span>
                                <span class="font-bold text-slate-800">{{ order.kode_seri }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Jumlah Total</span>
                                <span class="font-bold text-slate-800">{{ order.jumlah }} pcs</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Target Selesai</span>
                                <span class="font-bold text-slate-800">{{ order.target_selesai }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: JADWAL & PROGRES (SESUAI GAMBAR FIGMA IMAGE 2) -->
                <div v-if="activeTab === 'jadwal'" class="space-y-4">
                    <!-- Header Section Jadwal -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">
                                Jadwal produksi gudang
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Isi estimasi dan status untuk setiap tahap operasional.
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>Produksi Konveksi</span>
                        </span>
                    </div>

                    <!-- Dropdown Tempat Konveksi * Sesuai Figma -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tempat Konveksi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="scheduleForm.konveksi_id"
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition cursor-pointer"
                        >
                            <option value="">Pilih Konveksi</option>
                            <option v-for="kv in konveksis" :key="kv.id" :value="kv.id">
                                {{ kv.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- 4 Kartu Tahap Operasional Sesuai Figma -->
                    <div class="space-y-3">
                        <div
                            v-for="(info, index) in stageInfo"
                            :key="info.key"
                            class="p-4 rounded-2xl border border-slate-200/90 bg-white flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs"
                        >
                            <!-- Nama Tahap & Nomor -->
                            <div class="flex items-center gap-3 min-w-[190px]">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ info.number }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">
                                        {{ info.name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ info.sub }}
                                    </div>
                                </div>
                            </div>

                            <!-- Input Tanggal & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 flex-1 max-w-xl">
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase font-bold block mb-1">MULAI</label>
                                    <input
                                        v-model="scheduleForm.stages[index].mulai"
                                        type="date"
                                        class="w-full px-2.5 py-1.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-lg focus:border-[#7A1228] outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase font-bold block mb-1">ESTIMASI SELESAI</label>
                                    <input
                                        v-model="scheduleForm.stages[index].estimasi_selesai"
                                        type="date"
                                        class="w-full px-2.5 py-1.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-lg focus:border-[#7A1228] outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 uppercase font-bold block mb-1">STATUS TAHAP</label>
                                    <select
                                        v-model="scheduleForm.stages[index].status_tahap"
                                        class="w-full px-2.5 py-1.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-lg focus:border-[#7A1228] outline-none cursor-pointer"
                                    >
                                        <option value="belum_dimulai">Belum Dimulai</option>
                                        <option value="sedang_dikerjakan">Sedang Dikerjakan</option>
                                        <option value="selesai">Selesai</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Peringatan Divisi Lapangan Sesuai Mockup Figma -->
                    <div class="bg-[#FFFDF5] border border-amber-200/80 rounded-xl p-3 flex items-center gap-2.5 text-xs text-amber-800">
                        <AlertTriangle class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Status Pemotongan dan Sablon diperbarui oleh Admin Gudang karena divisi lapangan tidak memiliki komputer khusus.</span>
                    </div>

                    <!-- Tombol Simpan Jadwal Produksi -->
                    <div class="flex justify-end pt-1">
                        <button
                            type="button"
                            @click="handleSaveSchedule"
                            :disabled="!canSaveSchedule || scheduleForm.processing"
                            class="px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-xs"
                        >
                            <Check class="w-4 h-4 stroke-[2.5]" />
                            <span>{{ scheduleForm.processing ? 'Menyimpan...' : 'Simpan jadwal produksi' }}</span>
                        </button>
                    </div>
                </div>

                <!-- TAB 3: RIWAYAT -->
                <div v-if="activeTab === 'riwayat'" class="space-y-3">
                    <div v-if="!order.histories || order.histories.length === 0" class="text-center py-8 text-xs text-slate-400">
                        Belum ada riwayat aktivitas tercatat.
                    </div>
                    <div v-for="h in order.histories" :key="h.id" class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-3 text-xs">
                        <div class="w-2 h-2 rounded-full bg-[#7A1228] mt-1.5 shrink-0"></div>
                        <div class="flex-1">
                            <div class="font-bold text-slate-800">{{ h.aksi }}</div>
                            <div class="text-slate-500 mt-0.5">{{ h.deskripsi }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ h.created_at ? h.created_at.substring(0, 10) : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal Sesuai UI Mockup Figma (Image 2) -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <!-- Kiri: Negosiasi Tanggal -->
                <button
                    type="button"
                    @click="openNegotiateModal"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                >
                    <CalendarClock class="w-4 h-4 text-slate-600" />
                    <span>Negosiasi tanggal</span>
                </button>

                <!-- Kanan: Pilih Konveksi + Setujui Pesanan + Batalkan -->
                <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap justify-end">
                    <select
                        v-model="approveForm.konveksi_id"
                        class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 outline-none focus:border-[#7A1228] cursor-pointer"
                    >
                        <option value="">Pilih Konveksi</option>
                        <option v-for="kv in konveksis" :key="kv.id" :value="kv.id">
                            {{ kv.nama }}
                        </option>
                    </select>

                    <button
                        type="button"
                        @click="handleApprove"
                        :disabled="!approveForm.konveksi_id || approveForm.processing || order.status === 'dibatalkan'"
                        class="px-5 py-2.5 rounded-xl bg-[#7A1228] hover:bg-[#600e1f] text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 shadow-xs whitespace-nowrap"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>Setujui pesanan</span>
                    </button>

                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs whitespace-nowrap"
                    >
                        <X class="w-4 h-4 text-rose-500" />
                        <span>Batalkan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Pop-up Negosiasi Tanggal -->
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
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-none focus:border-[#7A1228]"
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
                            placeholder="Jelaskan alasan penyesuaian tanggal..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 outline-none focus:border-[#7A1228]"
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
                            Kirim Usulan Negosiasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


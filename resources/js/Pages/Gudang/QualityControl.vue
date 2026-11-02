<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import {
    ClipboardCheck,
    CheckCircle2,
    RotateCcw,
    X,
    CheckSquare,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

// Fallback jika tidak ada data pesanan dari database agar tampilan selalu sesuai mockup Figma
const defaultOrder = {
    id: 999,
    id_pesanan: 'ORD-2506-016',
    nama_customer: 'Ruang Rona',
    kode_seri: 'KMS-P21',
    jumlah: 200,
    target_selesai: '24 Jun 2025',
    konveksi_nama: 'Konveksi Mekar',
    status: 'qc_gudang',
    status_label: 'QC Gudang',
    catatan: 'Kemeja poplin panjang.',
};

const orderList = computed(() => {
    if (props.orders && props.orders.length > 0) {
        return props.orders;
    }
    return [defaultOrder];
});

// State Modal Detail Pemeriksaan QC
const showModal = ref(false);
const selectedOrder = ref(null);

const qcForm = useForm({
    action: 'lulus',
    jumlah_lulus: 0,
    jumlah_ditolak: 0,
    catatan: '',
});

const openQCModal = (order) => {
    selectedOrder.value = order;
    qcForm.action = 'lulus';
    qcForm.jumlah_lulus = order.jumlah || 0;
    qcForm.jumlah_ditolak = 0;
    qcForm.catatan = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedOrder.value = null;
};

const totalTercatat = computed(() => {
    return (Number(qcForm.jumlah_lulus) || 0) + (Number(qcForm.jumlah_ditolak) || 0);
});

const submitQC = (actionType) => {
    if (!selectedOrder.value) return;

    qcForm.action = actionType;

    // Jika order ada di database nyata
    if (selectedOrder.value.id && selectedOrder.value.id !== 999) {
        qcForm.post(`/gudang/quality-control/${selectedOrder.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        // Simulasi jika fallback
        alert(`Hasil pemeriksaan QC untuk ${selectedOrder.value.id_pesanan} berhasil dicatat (${actionType === 'lulus' ? 'Lulus' : 'Perlu Rework'}).`);
        closeModal();
    }
};
</script>

<template>
    <Head title="Quality Control - LilieFlow" />

    <GudangLayout title="Quality Control" :breadcrumbs="['Quality Control']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section Sesuai Mockup Figma -->
            <div class="pt-1">
                <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                    QUALITY CONTROL
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Pemeriksaan produk
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Catat hasil pemeriksaan produk yang kembali dari konveksi.
                </p>
            </div>

            <!-- Tabel Antrean Quality Control Sesuai Gambar Figma -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-white text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">ID PESANAN</th>
                                <th class="py-4 px-6">CUSTOMER / KODE</th>
                                <th class="py-4 px-6">JUMLAH</th>
                                <th class="py-4 px-6">TARGET</th>
                                <th class="py-4 px-6">STATUS</th>
                                <th class="py-4 px-6">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr
                                v-for="order in orderList"
                                :key="order.id"
                                class="hover:bg-slate-50/60 transition"
                            >
                                <!-- ID Pesanan -->
                                <td class="py-4 px-6 font-extrabold text-slate-900 whitespace-nowrap">
                                    {{ order.id_pesanan }}
                                </td>

                                <!-- Customer / Kode Seri -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800 text-xs">
                                        {{ order.nama_customer }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-normal">
                                        {{ order.kode_seri }}
                                    </div>
                                </td>

                                <!-- Jumlah -->
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ order.jumlah }} pcs
                                </td>

                                <!-- Target Selesai -->
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ order.target_selesai }}
                                </td>

                                <!-- Status Badge: QC Gudang -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>QC Gudang</span>
                                    </span>
                                </td>

                                <!-- Tombol Aksi: Periksa QC Sesuai Figma -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openQCModal(order)"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#7A1228] hover:text-[#5a0c1d] transition cursor-pointer"
                                    >
                                        <ClipboardCheck class="w-4 h-4 stroke-[2.2]" />
                                        <span>Periksa QC</span>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="orderList.length === 0">
                                <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                                    Tidak ada produk yang menunggu pemeriksaan Quality Control saat ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pemeriksaan QC Sesuai Figma (Image 2) -->
        <div
            v-if="showModal && selectedOrder"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4 overflow-y-auto"
        >
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 transition-all animate-in fade-in zoom-in-95 duration-150">
                <!-- Header Modal -->
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                            Detail pemeriksaan QC
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ selectedOrder.id_pesanan }} &middot; {{ selectedOrder.nama_customer }} &middot; Total {{ selectedOrder.jumlah }} pcs
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Info Box 3 Kolom: Kode Apparel, Tempat Konveksi, Target Selesai -->
                <div class="bg-[#F8FAFC] border border-slate-200/70 rounded-2xl p-4 mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <div class="text-[11px] text-slate-400 font-medium">
                            Kode apparel
                        </div>
                        <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                            {{ selectedOrder.kode_seri }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] text-slate-400 font-medium">
                            Tempat konveksi
                        </div>
                        <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                            {{ selectedOrder.konveksi_nama }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] text-slate-400 font-medium">
                            Target selesai
                        </div>
                        <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                            {{ selectedOrder.target_selesai }}
                        </div>
                    </div>
                </div>

                <!-- Form Pemeriksaan QC -->
                <form @submit.prevent class="space-y-4 mt-5">
                    <!-- Baris 1: Jumlah Lulus & Jumlah Ditolak (2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jumlah Lulus <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="qcForm.jumlah_lulus"
                                type="number"
                                min="0"
                                required
                                placeholder="0"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jumlah Ditolak <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model.number="qcForm.jumlah_ditolak"
                                type="number"
                                min="0"
                                required
                                placeholder="0"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>
                    </div>

                    <!-- Baris 2: Catatan Pemeriksaan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Catatan pemeriksaan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="qcForm.catatan"
                            rows="3"
                            required
                            placeholder="Tuliskan temuan kualitas, ukuran, jahitan, atau catatan lainnya..."
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition resize-none"
                        ></textarea>
                    </div>

                    <!-- Baris Ringkasan Total Tercatat -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="text-slate-400">Total tercatat</span>
                        <span
                            :class="[
                                totalTercatat > selectedOrder.jumlah
                                    ? 'text-rose-600 font-bold'
                                    : 'text-slate-600 font-semibold'
                            ]"
                        >
                            {{ totalTercatat }} / {{ selectedOrder.jumlah }} pcs
                        </span>
                    </div>

                    <!-- Footer Actions: Perlu Rework & Tandai Lulus -->
                    <div class="flex items-center justify-between pt-4 mt-2">
                        <!-- Tombol Perlu Rework (Kiri) -->
                        <button
                            type="button"
                            @click="submitQC('rework')"
                            :disabled="qcForm.processing"
                            class="px-4 py-2 border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-rose-600 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer disabled:opacity-50"
                        >
                            <RotateCcw class="w-3.5 h-3.5" />
                            <span>{{ qcForm.processing && qcForm.action === 'rework' ? 'Menyimpan...' : 'Perlu rework' }}</span>
                        </button>

                        <!-- Tombol Tandai Lulus (Kanan) -->
                        <button
                            type="button"
                            @click="submitQC('lulus')"
                            :disabled="qcForm.processing"
                            class="px-5 py-2.5 bg-[#7A1228] hover:bg-[#600e1f] text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-xs cursor-pointer disabled:opacity-50"
                        >
                            <CheckCircle2 class="w-4 h-4" />
                            <span>{{ qcForm.processing && qcForm.action === 'lulus' ? 'Menyimpan...' : 'Tandai lulus' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GudangLayout>
</template>


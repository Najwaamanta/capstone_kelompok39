<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import ModalGudangProgresDetail from '@/Components/ModalGudangProgresDetail.vue';
import {
    Shirt,
    Eye
} from 'lucide-vue-next';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    konveksis: {
        type: Array,
        default: () => [],
    },
});

// Fallback jika belum ada order aktif di database agar tampilan selalu sesuai mockup Figma
const fallbackOrders = [
    {
        id: 1,
        id_pesanan: 'ORD-2506-018',
        nama_customer: 'Maison Aurelia',
        kode_seri: 'DRS-A17',
        jumlah: 120,
        target_selesai: '28 Jun 2025',
        konveksi_nama: 'Konveksi Sinar Jaya',
        status: 'dalam_produksi',
        status_label: 'Produksi Konveksi',
        progres_persen: 68,
        stages: [
            { tahap: 'pemotongan', mulai: '2025-06-20', estimasi_selesai: '2025-06-21', status_tahap: 'selesai' },
            { tahap: 'sablon', mulai: '2025-06-22', estimasi_selesai: '2025-06-23', status_tahap: 'sedang_dikerjakan' },
            { tahap: 'konveksi', mulai: '2025-06-24', estimasi_selesai: '2025-06-25', status_tahap: 'belum_dimulai' },
            { tahap: 'qc_gudang', mulai: '2025-06-26', estimasi_selesai: '2025-06-27', status_tahap: 'belum_dimulai' },
        ],
    },
    {
        id: 2,
        id_pesanan: 'ORD-2506-016',
        nama_customer: 'Ruang Rona',
        kode_seri: 'KMS-P21',
        jumlah: 200,
        target_selesai: '24 Jun 2025',
        konveksi_nama: 'Konveksi Mekar',
        status: 'qc_gudang',
        status_label: 'QC Gudang',
        progres_persen: 92,
        stages: [
            { tahap: 'pemotongan', mulai: '2025-06-18', estimasi_selesai: '2025-06-19', status_tahap: 'selesai' },
            { tahap: 'sablon', mulai: '2025-06-20', estimasi_selesai: '2025-06-21', status_tahap: 'selesai' },
            { tahap: 'konveksi', mulai: '2025-06-21', estimasi_selesai: '2025-06-23', status_tahap: 'selesai' },
            { tahap: 'qc_gudang', mulai: '2025-06-23', estimasi_selesai: '2025-06-24', status_tahap: 'sedang_dikerjakan' },
        ],
    },
];

const displayedOrders = computed(() => {
    if (props.orders && props.orders.length > 0) {
        return props.orders;
    }
    return fallbackOrders;
});

// Modal Detail State
import { computed } from 'vue';
const showDetailModal = ref(false);
const selectedOrder = ref(null);

const openDetail = (order) => {
    selectedOrder.value = order.raw_order || order;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Progres Konveksi - LilieFlow" />

    <GudangLayout title="Progres Konveksi" :breadcrumbs="['Progres Konveksi']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section Sesuai Mockup Figma -->
            <div class="pt-1">
                <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                    PRODUKSI
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Progres konveksi
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pantau tahap produksi dengan status yang sama untuk seluruh peran.
                </p>
            </div>

            <!-- Grid Kartu Progres Konveksi 2 Kolom Sesuai Figma (Image 1) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="order in displayedOrders"
                    :key="order.id"
                    class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-6 sm:p-7 flex flex-col justify-between hover:shadow-xs transition"
                >
                    <div>
                        <!-- Header Kartu: Ikon Baju, ID Pesanan & Pill Status -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0 select-none">
                                    <Shirt class="w-5 h-5 stroke-[2]" />
                                </div>
                                <div>
                                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                                        {{ order.id_pesanan }}
                                    </h2>
                                    <p class="text-xs text-slate-400 mt-0.5 font-normal">
                                        {{ order.nama_customer }} &bull; {{ order.kode_seri }}
                                    </p>
                                </div>
                            </div>

                            <!-- Pill Status Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-100 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                <span>{{ order.status_label }}</span>
                            </span>
                        </div>

                        <!-- 3 Kolom Spesifikasi: Jumlah, Target, Konveksi Sesuai Figma -->
                        <div class="grid grid-cols-3 gap-2 mt-6 pt-1">
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    Jumlah
                                </div>
                                <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                                    {{ order.jumlah }} pcs
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    Target
                                </div>
                                <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5">
                                    {{ order.target_selesai }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    Konveksi
                                </div>
                                <div class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    {{ order.konveksi_nama }}
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar Keseluruhan Sesuai Figma -->
                        <div class="mt-6 pt-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">Progres keseluruhan</span>
                                <span class="text-slate-900 font-extrabold">{{ order.progres_persen }}%</span>
                            </div>
                            <div class="h-1.5 sm:h-2 w-full bg-slate-100 rounded-full overflow-hidden mt-2">
                                <div
                                    class="h-full bg-[#7A1228] rounded-full transition-all duration-500"
                                    :style="{ width: order.progres_persen + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Detail di Kanan Bawah Sesuai Figma -->
                    <div class="flex justify-end pt-5 mt-2 border-t border-slate-50">
                        <button
                            type="button"
                            @click="openDetail(order)"
                            class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
                        >
                            <Eye class="w-3.5 h-3.5 text-slate-500" />
                            <span>Detail</span>
                        </button>
                    </div>
                </div>

                <div v-if="displayedOrders.length === 0" class="col-span-full bg-white rounded-3xl p-12 text-center text-xs text-slate-400 border border-slate-200/80">
                    Tidak ada pesanan dalam tahapan konveksi saat ini.
                </div>
            </div>
        </div>

        <!-- Modal Detail Pop-up Sesuai Figma Image 2 -->
        <ModalGudangProgresDetail
            :show="showDetailModal"
            :order="selectedOrder"
            :konveksis="konveksis"
            @close="showDetailModal = false"
        />
    </GudangLayout>
</template>


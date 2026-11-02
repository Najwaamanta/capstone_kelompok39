<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import TokoLayout from '@/Layouts/TokoLayout.vue';
import ModalCreateOrder from '@/Components/ModalCreateOrder.vue';
import ModalOrderDetail from '@/Components/ModalOrderDetail.vue';
import {
    Plus,
    FileText,
    Factory,
    Clock,
    Package,
    Shirt,
    ChevronRight,
    Bell,
    Calendar,
    CheckCircle2,
    CalendarClock
} from 'lucide-vue-next';

const props = defineProps({
    greeting: {
        type: String,
        default: 'Selamat pagi, Nadia',
    },
    metrics: {
        type: Object,
        default: () => ({
            pesanan_aktif: 12,
            dalam_produksi: 8,
            menunggu_persetujuan: 3,
            selesai_bulan_ini: 24,
            persentase_tepat_waktu: 96,
        }),
    },
    perlu_perhatian: {
        type: Array,
        default: () => [],
    },
    feed_aktivitas: {
        type: Array,
        default: () => [],
    },
    next_order_id: {
        type: String,
        default: 'ORD-2506-019',
    },
    konveksis: {
        type: Array,
        default: () => [],
    },
});

const showCreateModal = ref(false);
const showDetailModal = ref(false);
const selectedOrder = ref(null);

const openDetail = (order) => {
    selectedOrder.value = order;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Dashboard - LilieFlow" />

    <TokoLayout title="Dashboard" :breadcrumbs="['Dashboard']">
        <div class="space-y-6">
            <!-- Header Section: Greeting & "+ Buat pesanan" Button (Sesuai Foto 1) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ greeting }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Berikut ringkasan operasional LilieFlow hari ini.
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="px-5 py-2.5 bg-[#7A1228] text-white font-bold rounded-xl hover:bg-[#630c1f] transition shadow-xs flex items-center justify-center gap-2 text-sm cursor-pointer"
                    >
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                        <span>Buat pesanan</span>
                    </button>
                </div>
            </div>

            <!-- 4 Kartu Metrik Utama (Sesuai Foto 1) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Pesanan aktif -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#FCE7EB] text-[#7A1228] flex items-center justify-center shrink-0">
                            <FileText class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Pesanan aktif</span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 leading-none">
                        {{ metrics.pesanan_aktif }}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ metrics.pesanan_aktif_sub || '+0 minggu ini' }}
                    </p>
                </div>

                <!-- 2. Dalam produksi -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <Factory class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Dalam produksi</span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 leading-none">
                        {{ metrics.dalam_produksi }}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ metrics.dalam_produksi_sub || '0 perlu perhatian' }}
                    </p>
                </div>

                <!-- 3. Menunggu persetujuan -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <Clock class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Menunggu persetujuan</span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 leading-none">
                        {{ metrics.menunggu_persetujuan }}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ metrics.menunggu_persetujuan_sub || '0 negosiasi aktif' }}
                    </p>
                </div>

                <!-- 4. Selesai bulan ini -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <Package class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Selesai bulan ini</span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 leading-none">
                        {{ metrics.selesai_bulan_ini }}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ metrics.selesai_sub || 'Belum ada pesanan selesai' }}
                    </p>
                </div>
            </div>

            <!-- Bagian Dua Kolom: Pesanan yang perlu perhatian & Aktivitas terbaru (Sesuai Foto 1) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kolom Kiri: Pesanan yang perlu perhatian (8 Cols) -->
                <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                Pesanan yang perlu perhatian
                            </h3>
                            <p class="text-xs text-slate-400">
                                Urut berdasarkan target penyelesaian terdekat
                            </p>
                        </div>
                        <Link
                            href="/toko/orders"
                            class="text-xs font-bold text-[#7A1228] hover:underline flex items-center gap-1"
                        >
                            <span>Lihat semua</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <!-- List Card Pesanan -->
                    <div class="space-y-3 pt-1">
                        <div
                            v-for="order in perlu_perhatian"
                            :key="order.id"
                            @click="openDetail(order)"
                            class="p-4 rounded-2xl bg-white border border-slate-200/70 hover:border-slate-300 hover:shadow-2xs transition-all cursor-pointer group"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <!-- Left side: Shirt icon + Info -->
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
                                        :class="order.icon_bg"
                                    >
                                        <Shirt class="w-5 h-5" />
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-slate-900 text-sm">
                                                {{ order.id_pesanan }}
                                            </span>
                                            <!-- Pill Badge -->
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold flex items-center gap-1.5"
                                                :class="order.pill_class"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
                                                {{ order.pill_label }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                                            {{ order.nama_customer }} · {{ order.kode_seri }} · {{ order.jumlah }} pcs
                                        </p>
                                    </div>
                                </div>

                                <!-- Right side: Target + Date + Chevron -->
                                <div class="flex items-center gap-3 shrink-0 text-right">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block font-medium">Target</span>
                                        <span class="text-xs font-bold text-slate-900 block leading-tight">
                                            {{ order.day_month }}
                                        </span>
                                    </div>
                                    <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
                                </div>
                            </div>

                            <!-- Maroon Progress Bar Indicator at bottom -->
                            <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden mt-3.5">
                                <div
                                    class="bg-[#7A1228] h-full rounded-full transition-all duration-500"
                                    :style="{ width: `${order.progress_percent || 40}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Aktivitas terbaru (4 Cols) -->
                <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Aktivitas terbaru
                        </h3>
                        <p class="text-xs text-slate-400">
                            Pembaruan lintas divisi
                        </p>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div
                            v-for="act in feed_aktivitas"
                            :key="act.id"
                            class="p-3.5 rounded-2xl bg-white border border-slate-100 hover:bg-slate-50/50 transition flex items-start gap-3"
                        >
                            <!-- Icon Box -->
                            <div
                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                :class="act.box_bg"
                            >
                                <CalendarClock v-if="act.icon_type === 'calendar'" class="w-4 h-4" />
                                <CheckCircle2 v-else-if="act.icon_type === 'check'" class="w-4 h-4" />
                                <Bell v-else class="w-4 h-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                    {{ act.judul }}
                                </h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                    {{ act.pesan }}
                                </p>
                                <span class="text-[10px] text-slate-400 block mt-1 font-medium">
                                    {{ act.time_ago }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Buat Pesanan Baru -->
        <ModalCreateOrder
            :show="showCreateModal"
            :next-order-id="next_order_id"
            @close="showCreateModal = false"
        />

        <!-- Modal Detail Pesanan Toko (Sesuai Foto 3, 4, 5) -->
        <ModalOrderDetail
            :show="showDetailModal"
            :order="selectedOrder"
            @close="showDetailModal = false"
        />
    </TokoLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import ModalGudangOrderDetail from '@/Components/ModalGudangOrderDetail.vue';
import {
    Package,
    Layers,
    AlertTriangle,
    ClipboardCheck,
    Shirt,
    ChevronRight,
    Bell,
    Calendar,
    CheckCircle2,
    CheckSquare,
    X,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    greeting: {
        type: String,
        default: 'Selamat pagi, Raka',
    },
    tanggalHariIni: {
        type: String,
        default: 'SENIN, 23 JUNI 2025',
    },
    metrics: {
        type: Object,
        default: () => ({
            pesanan_masuk: 5,
            pesanan_masuk_sub: '2 baru hari ini',
            produksi_berjalan: 14,
            produksi_berjalan_sub: '6 di konveksi',
            stok_perhatian: 3,
            stok_perhatian_sub: '1 bahan habis',
            menunggu_qc: 4,
            menunggu_qc_sub: '320 pcs diperiksa',
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
    konveksis: {
        type: Array,
        default: () => [],
    },
});

const showDetailModal = ref(false);
const selectedOrder = ref(null);
const showStockModal = ref(false);

const openDetail = (orderItem) => {
    selectedOrder.value = orderItem.raw_order || orderItem;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Dashboard - Admin Gudang" />

    <GudangLayout title="Dashboard" :breadcrumbs="['Dashboard']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section: Subtitle Tanggal, Greeting & Button Buka Antrean QC -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                        ADMIN GUDANG &bull; {{ tanggalHariIni }}
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ greeting }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Berikut ringkasan operasional LilieFlow hari ini.
                    </p>
                </div>

                <div>
                    <Link
                        href="/gudang/quality-control"
                        class="px-5 py-2.5 bg-[#7A1228] text-white font-semibold rounded-xl hover:bg-[#630c1f] transition shadow-xs flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer"
                    >
                        <CheckSquare class="w-4 h-4 stroke-[2.2]" />
                        <span>Buka antrean QC</span>
                    </Link>
                </div>
            </div>

            <!-- 4 Kartu Metrik Utama Sesuai Figma -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Pesanan masuk -->
                <div class="bg-white p-5 rounded-2xl border border-[#F0E6E8] shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FDF2F4] text-[#7A1228] flex items-center justify-center shrink-0">
                            <Package class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Pesanan masuk</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ metrics.pesanan_masuk }}
                        </div>
                        <div class="text-[11px] font-medium text-slate-400 mt-1">
                            {{ metrics.pesanan_masuk_sub }}
                        </div>
                    </div>
                </div>

                <!-- 2. Produksi berjalan -->
                <div class="bg-white p-5 rounded-2xl border border-[#F0E6E8] shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <Layers class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Produksi berjalan</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ metrics.produksi_berjalan }}
                        </div>
                        <div class="text-[11px] font-medium text-slate-400 mt-1">
                            {{ metrics.produksi_berjalan_sub }}
                        </div>
                    </div>
                </div>

                <!-- 3. Stok perlu perhatian -->
                <div class="bg-white p-5 rounded-2xl border border-[#F0E6E8] shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <AlertTriangle class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Stok perlu perhatian</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ metrics.stok_perhatian }}
                        </div>
                        <div class="text-[11px] font-medium text-amber-600 font-semibold mt-1">
                            {{ metrics.stok_perhatian_sub }}
                        </div>
                    </div>
                </div>

                <!-- 4. Menunggu QC -->
                <div class="bg-white p-5 rounded-2xl border border-[#F0E6E8] shadow-2xs hover:shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <ClipboardCheck class="w-5 h-5" />
                        </div>
                        <span class="text-xs font-medium text-slate-500">Menunggu QC</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ metrics.menunggu_qc }}
                        </div>
                        <div class="text-[11px] font-medium text-slate-400 mt-1">
                            {{ metrics.menunggu_qc_sub }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Columns Section: Pesanan yang perlu perhatian & Aktivitas terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kolom Kiri: Pesanan yang perlu perhatian (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-[#F0E6E8] p-5 shadow-2xs flex flex-col justify-between">
                    <div>
                        <!-- Header Kartu -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">
                                    Pesanan yang perlu perhatian
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Urut berdasarkan target penyelesaian terdekat
                                </p>
                            </div>
                            <Link
                                href="/gudang/orders"
                                class="text-xs font-semibold text-[#7A1228] hover:text-[#630c1f] flex items-center gap-1 transition"
                            >
                                <span>Lihat semua</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        <!-- Daftar Pesanan -->
                        <div class="divide-y divide-slate-100">
                            <div
                                v-for="item in perlu_perhatian"
                                :key="item.id"
                                @click="openDetail(item)"
                                class="py-3.5 first:pt-1 last:pb-1 group cursor-pointer hover:bg-slate-50/70 rounded-xl px-2.5 transition -mx-2.5"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Apparel Icon Box -->
                                        <div
                                            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition group-hover:scale-105"
                                            :class="item.icon_bg"
                                        >
                                            <Shirt class="w-5 h-5" />
                                        </div>

                                        <!-- Informasi Order -->
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight">
                                                    {{ item.id_pesanan }}
                                                </span>
                                                <span
                                                    class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-md"
                                                    :class="item.badge_class"
                                                >
                                                    {{ item.badge_label }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                                {{ item.nama_customer }} &bull; {{ item.kode_seri }} &bull; {{ item.jumlah }} pcs
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Target Tanggal & Arrow -->
                                    <div class="flex items-center gap-1.5 shrink-0 text-right">
                                        <div class="text-right">
                                            <span class="text-[11px] text-slate-400 block sm:inline">Target </span>
                                            <span class="text-xs font-bold text-slate-700">{{ item.target_formatted }}</span>
                                        </div>
                                        <ChevronRight class="w-4 h-4 text-slate-300 group-hover:text-slate-600 transition" />
                                    </div>
                                </div>

                                <!-- Progress Bar Horisontal -->
                                <div class="mt-2.5 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 bg-[#7A1228]"
                                        :style="{ width: `${item.progress}%` }"
                                    ></div>
                                </div>
                            </div>

                            <div v-if="perlu_perhatian.length === 0" class="py-8 text-center text-xs text-slate-400">
                                Tidak ada pesanan yang memerlukan perhatian khusus saat ini.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Aktivitas terbaru (5 cols) -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-[#F0E6E8] p-5 shadow-2xs flex flex-col justify-between">
                    <div>
                        <!-- Header Kartu -->
                        <div class="mb-4">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">
                                Aktivitas terbaru
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Pembaruan lintas divisi
                            </p>
                        </div>

                        <!-- Timeline Aktivitas -->
                        <div class="space-y-4">
                            <div
                                v-for="act in feed_aktivitas"
                                :key="act.id"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                    :class="act.icon_bg"
                                >
                                    <Bell v-if="act.icon_type === 'bell'" class="w-4 h-4" />
                                    <Calendar v-else-if="act.icon_type === 'calendar'" class="w-4 h-4" />
                                    <Package v-else-if="act.icon_type === 'package'" class="w-4 h-4" />
                                    <CheckCircle2 v-else class="w-4 h-4" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 leading-tight">
                                        {{ act.judul }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                                        {{ act.pesan }}
                                    </p>
                                    <span class="text-[10px] text-slate-400 mt-1 block">
                                        {{ act.waktu }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Peringatan Stok Sesuai Figma -->
            <div class="bg-[#FFFDF5] border border-amber-200/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100/70 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                        <AlertTriangle class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">
                            Katun Combed 24s tidak mencukupi untuk ORD-2506-018
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                            Dibutuhkan 250 meter &bull; Tersedia 35 meter &bull; <span class="text-amber-700 font-semibold">Kekurangan 215 meter</span>. Mekanisme pengadaan tidak termasuk dalam prototype.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 sm:self-center">
                    <button
                        type="button"
                        @click="showStockModal = true"
                        class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50 transition shadow-2xs cursor-pointer"
                    >
                        Periksa stok
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pesanan Gudang (Komponen Lengkap) -->
        <ModalGudangOrderDetail
            :show="showDetailModal"
            :order="selectedOrder"
            :konveksis="konveksis"
            @close="showDetailModal = false"
        />

        <!-- Modal Popup Periksa Stok -->
        <div
            v-if="showStockModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
        >
            <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl p-6 relative animate-in fade-in zoom-in-95 duration-150">
                <button
                    type="button"
                    @click="showStockModal = false"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                >
                    <X class="w-5 h-5" />
                </button>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <AlertTriangle class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Periksa Ketersediaan Stok</h3>
                        <p class="text-xs text-slate-500">Status bahan baku untuk produksi aktif</p>
                    </div>
                </div>

                <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Nama Bahan:</span>
                        <span class="font-bold text-slate-800">Katun Combed 24s</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Pesanan Terkait:</span>
                        <span class="font-bold text-[#7A1228]">ORD-2506-018 (Maison Aurelia)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Kebutuhan Produksi:</span>
                        <span class="font-semibold text-slate-700">250 meter</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Stok Tersedia di Gudang:</span>
                        <span class="font-bold text-rose-600">35 meter</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500 font-medium">Defisit / Kekurangan:</span>
                        <span class="font-extrabold text-amber-700">215 meter</span>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl text-[11px] text-amber-800">
                    Catatan: Fitur modul pengadaan bahan baku lanjutan berada di luar cakupan alur prototype pesanan saat ini.
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="showStockModal = false"
                        class="px-4 py-2 bg-[#7A1228] text-white font-bold text-xs rounded-xl hover:bg-[#630c1f] transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </GudangLayout>
</template>


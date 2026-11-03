<script setup>
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import TokoLayout from '@/Layouts/TokoLayout.vue';
import ModalOrderDetail from '@/Components/ModalOrderDetail.vue';
import {
    Bell,
    CheckCheck,
    CalendarClock,
    Package,
    Layers,
    CheckCircle2,
    AlertCircle,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    notifications: {
        type: Object,
        default: () => ({ data: [] }),
    },
    tab: {
        type: String,
        default: 'semua',
    },
    unread_count: {
        type: Number,
        default: 0,
    },
    total_count: {
        type: Number,
        default: 0,
    },
});

const selectedOrder = ref(null);
const showDetailModal = ref(false);

const changeTab = (tabName) => {
    router.get('/toko/notifications', { tab: tabName }, { preserveState: true });
};

const markAllRead = () => {
    router.post('/toko/notifications/mark-all-read', {}, { preserveScroll: true });
};

const handleNotificationClick = (item) => {
    if (!item.dibaca) {
        router.post(`/toko/notifications/${item.id}/read`, {}, { preserveScroll: true });
    }
    if (item.order) {
        selectedOrder.value = item.order;
        showDetailModal.value = true;
    }
};

const getNotificationIcon = (tipe) => {
    switch (tipe) {
        case 'negosiasi_masuk':
        case 'negosiasi_disetujui':
            return CalendarClock;
        case 'pesanan_baru':
            return Package;
        case 'hasil_qc':
            return CheckCircle2;
        default:
            return Layers;
    }
};

const getNotificationBadgeColor = (tipe) => {
    switch (tipe) {
        case 'negosiasi_masuk':
            return 'bg-orange-50 text-orange-600 border-orange-200';
        case 'pesanan_baru':
            return 'bg-blue-50 text-blue-600 border-blue-200';
        case 'hasil_qc':
            return 'bg-emerald-50 text-emerald-600 border-emerald-200';
        default:
            return 'bg-[#FDF1F3] text-[#7A1228] border-[#F7D6DC]';
    }
};
</script>

<template>
    <Head title="Pusat Notifikasi Toko" />

    <TokoLayout title="Pusat Notifikasi" :breadcrumbs="['Notifikasi']">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pemberitahuan & Aktivitas</h2>
                    <p class="text-xs text-slate-500">Pembaruan real-time terkait progres konveksi, negosiasi, dan hasil QC</p>
                </div>

                <button
                    v-if="unread_count > 0"
                    type="button"
                    @click="markAllRead"
                    class="px-4 py-2 bg-white text-[#7A1228] border border-[#F7D6DC] hover:bg-[#FDF1F3] rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
                >
                    <CheckCheck class="w-4 h-4" />
                    <span>Tandai Semua Dibaca</span>
                </button>
            </div>

            <!-- Tab Filter Controls (US-T08) -->
            <div class="flex items-center gap-2 border-b border-slate-200 text-sm">
                <button
                    type="button"
                    @click="changeTab('semua')"
                    class="pb-3 px-4 font-semibold transition border-b-2 flex items-center gap-2"
                    :class="tab === 'semua' ? 'border-[#7A1228] text-[#7A1228]' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    <span>Semua Notifikasi</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        {{ total_count }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="changeTab('belum_dibaca')"
                    class="pb-3 px-4 font-semibold transition border-b-2 flex items-center gap-2"
                    :class="tab === 'belum_dibaca' ? 'border-[#7A1228] text-[#7A1228]' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    <span>Belum Dibaca</span>
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-bold"
                        :class="unread_count > 0 ? 'bg-[#7A1228] text-white' : 'bg-slate-100 text-slate-600'"
                    >
                        {{ unread_count }}
                    </span>
                </button>
            </div>

            <!-- Notification Items List -->
            <div class="space-y-3">
                <div v-if="notifications.data.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200/80">
                    <Bell class="w-12 h-12 text-slate-300 mx-auto mb-2 stroke-1" />
                    <p class="font-bold text-slate-700 text-sm">Tidak ada notifikasi</p>
                    <p class="text-xs text-slate-400 mt-0.5">Semua pembaruan pesanan Anda sudah terbaca.</p>
                </div>

                <div
                    v-for="item in notifications.data"
                    :key="item.id"
                    @click="handleNotificationClick(item)"
                    class="bg-white rounded-2xl p-4 md:p-5 border transition-all cursor-pointer hover:shadow-md flex items-start gap-4 group"
                    :class="[
                        item.dibaca
                            ? 'border-slate-200/80 hover:border-slate-300'
                            : 'border-[#F7D6DC] bg-[#FDF1F3]/20 shadow-xs'
                    ]"
                >
                    <!-- Type Icon -->
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 border"
                        :class="getNotificationBadgeColor(item.tipe)"
                    >
                        <component :is="getNotificationIcon(item.tipe)" class="w-5 h-5" />
                    </div>

                    <!-- Body -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h4
                                class="text-sm tracking-tight truncate"
                                :class="item.dibaca ? 'font-semibold text-slate-800' : 'font-extrabold text-[#7A1228]'"
                            >
                                {{ item.judul }}
                            </h4>
                            <span class="text-[11px] text-slate-400 shrink-0 font-medium">
                                {{ item.time_ago }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ item.pesan }}
                        </p>

                        <div v-if="item.order" class="mt-2.5 flex items-center gap-2">
                            <span class="font-mono text-[11px] font-bold text-[#7A1228] bg-[#FDF1F3] px-2 py-0.5 rounded-md border border-[#F7D6DC]">
                                {{ item.order.id_pesanan }}
                            </span>
                            <span class="text-xs font-semibold text-slate-700">
                                {{ item.order.nama_customer }}
                            </span>
                            <span class="text-xs text-slate-400 ml-auto flex items-center gap-1 group-hover:text-[#7A1228] font-medium">
                                Buka Pesanan <ChevronRight class="w-3.5 h-3.5" />
                            </span>
                        </div>
                    </div>

                    <!-- Unread dot -->
                    <div v-if="!item.dibaca" class="w-2.5 h-2.5 rounded-full bg-[#7A1228] shrink-0 mt-2"></div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="notifications.links && notifications.links.length > 3"
                class="pt-4 flex items-center justify-center gap-1 text-xs"
            >
                <template v-for="(link, idx) in notifications.links" :key="idx">
                    <button
                        type="button"
                        v-if="link.url"
                        @click="router.visit(link.url)"
                        class="px-3 py-1.5 rounded-lg font-medium border"
                        :class="link.active ? 'bg-[#7A1228] text-white border-[#7A1228]' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                        v-html="link.label"
                    ></button>
                </template>
            </div>
        </div>

        <!-- Modal Detail Pesanan Toko -->
        <ModalOrderDetail
            :show="showDetailModal"
            :order="selectedOrder"
            @close="showDetailModal = false"
        />
    </TokoLayout>
</template>


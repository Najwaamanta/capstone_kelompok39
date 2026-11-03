<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import ModalGudangOrderDetail from '@/Components/ModalGudangOrderDetail.vue';
import {
    Check,
    CalendarClock,
    Layers,
    FileText,
    ClipboardCheck,
    Bell
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

// Fallback data bawaan jika belum ada record di database
const fallbackNotifications = [
    {
        id: 1,
        judul: 'Progres konveksi diperbarui',
        pesan: 'ORD-2506-018 masuk tahap Produksi Konveksi.',
        time_ago: '8 menit yang lalu',
        dibaca: false,
        category_type: 'progres',
    },
    {
        id: 2,
        judul: 'Negosiasi tanggal menunggu respons',
        pesan: 'Estimasi ORD-2506-017 diusulkan menjadi 26 Jun 2025.',
        time_ago: '32 menit yang lalu',
        dibaca: false,
        category_type: 'negosiasi',
    },
    {
        id: 3,
        judul: 'Pesanan baru diterima',
        pesan: 'ORD-2506-018 dari Maison Aurelia telah diteruskan.',
        time_ago: 'Kemarin, 16:42',
        dibaca: true,
        category_type: 'pesanan',
    },
    {
        id: 4,
        judul: 'Hasil QC: Lulus',
        pesan: 'Seluruh 60 pcs ORD-2506-015 lolos pemeriksaan.',
        time_ago: 'Kemarin, 14:20',
        dibaca: true,
        category_type: 'qc',
    },
];

const formatTimeAgo = (timeStr) => {
    if (!timeStr) return '';
    // Membulatkan jika ada desimal seperti "15.815395533333 menit lalu" menjadi "16 menit yang lalu"
    return timeStr.replace(/(\d+)\.(\d+)\s*menit(\s*yang)?\s*lalu/i, (_, intPart, decPart) => {
        const rounded = Math.round(parseFloat(`${intPart}.${decPart}`));
        return `${rounded} menit yang lalu`;
    });
};

const currentTab = ref(props.tab || 'semua');
const localUnreadMarked = ref(false);

const displayedNotifications = computed(() => {
    let list = [];
    if (props.notifications?.data && props.notifications.data.length > 0) {
        list = [...props.notifications.data];
    } else {
        list = [...fallbackNotifications];
    }

    if (localUnreadMarked.value) {
        list = list.map(item => ({ ...item, dibaca: true }));
    }

    if (currentTab.value === 'belum_dibaca') {
        return list.filter(item => !item.dibaca);
    }
    return list;
});

const effectiveTotalCount = computed(() => {
    if (props.total_count > 0) return props.total_count;
    return fallbackNotifications.length;
});

const effectiveUnreadCount = computed(() => {
    if (localUnreadMarked.value) return 0;
    if (props.unread_count > 0) return props.unread_count;
    if (props.notifications?.data && props.notifications.data.length > 0) {
        return props.notifications.data.filter(i => !i.dibaca).length;
    }
    return 2;
});

const selectedOrder = ref(null);
const showDetailModal = ref(false);

const changeTab = (tabName) => {
    currentTab.value = tabName;
    router.get('/gudang/notifications', { tab: tabName }, { preserveState: true, preserveScroll: true });
};

const markAllRead = () => {
    localUnreadMarked.value = true;
    router.post('/gudang/notifications/mark-all-read', {}, {
        preserveScroll: true,
        onSuccess: () => {
            localUnreadMarked.value = true;
        },
    });
};

const handleNotificationClick = (item) => {
    if (!item.dibaca) {
        item.dibaca = true;
        router.post(`/gudang/notifications/${item.id}/read`, {}, { preserveScroll: true });
    }
    if (item.order) {
        selectedOrder.value = item.order;
        showDetailModal.value = true;
    }
};

const getItemStyles = (item) => {
    const judul = (item.judul || '').toLowerCase();
    const type = item.category_type || '';

    if (type === 'progres' || judul.includes('konveksi') || judul.includes('progres')) {
        return {
            icon: Layers,
            boxClass: 'bg-sky-50 text-sky-600',
        };
    }
    if (type === 'negosiasi' || judul.includes('negosiasi')) {
        return {
            icon: CalendarClock,
            boxClass: 'bg-amber-50 text-amber-600',
        };
    }
    if (type === 'pesanan' || judul.includes('pesanan') || judul.includes('diterima')) {
        return {
            icon: FileText,
            boxClass: 'bg-rose-50 text-[#7A1228]',
        };
    }
    if (type === 'qc' || judul.includes('qc') || judul.includes('lulus')) {
        return {
            icon: ClipboardCheck,
            boxClass: 'bg-emerald-50 text-emerald-600',
        };
    }

    return {
        icon: Bell,
        boxClass: 'bg-slate-100 text-slate-600',
    };
};
</script>

<template>
    <Head title="Notifikasi - LilieFlow" />

    <GudangLayout title="Notifikasi" :breadcrumbs="['Notifikasi']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section: Subtitle PUSAT INFORMASI, Title & Button Tandai Semua Dibaca -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                        PUSAT INFORMASI
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Notifikasi
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Pembaruan pesanan dan aktivitas operasional terbaru.
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        @click="markAllRead"
                        class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-50 text-xs font-semibold rounded-xl flex items-center gap-1.5 transition shadow-2xs cursor-pointer"
                    >
                        <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                        <span>Tandai semua dibaca</span>
                    </button>
                </div>
            </div>

            <!-- Card Utama Notifikasi Sesuai Mockup Figma -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <!-- Tab Filter: Semua & Belum dibaca Sesuai Figma -->
                <div class="flex items-center gap-6 px-6 pt-4 border-b border-slate-100 bg-white">
                    <!-- Tab Semua -->
                    <button
                        type="button"
                        @click="changeTab('semua')"
                        class="pb-3 text-xs font-bold transition border-b-2 flex items-center gap-1.5 cursor-pointer"
                        :class="currentTab === 'semua'
                            ? 'border-[#7A1228] text-slate-900'
                            : 'border-transparent text-slate-400 hover:text-slate-700'"
                    >
                        <span>Semua</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                            :class="currentTab === 'semua' ? 'bg-[#FDF1F3] text-[#7A1228]' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ effectiveTotalCount }}
                        </span>
                    </button>

                    <!-- Tab Belum Dibaca -->
                    <button
                        type="button"
                        @click="changeTab('belum_dibaca')"
                        class="pb-3 text-xs font-bold transition border-b-2 flex items-center gap-1.5 cursor-pointer"
                        :class="currentTab === 'belum_dibaca'
                            ? 'border-[#7A1228] text-slate-900'
                            : 'border-transparent text-slate-400 hover:text-slate-700'"
                    >
                        <span>Belum dibaca</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                            :class="currentTab === 'belum_dibaca' ? 'bg-[#FDF1F3] text-[#7A1228]' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ effectiveUnreadCount }}
                        </span>
                    </button>
                </div>

                <!-- Daftar Notifikasi Sesuai Desain Figma -->
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="item in displayedNotifications"
                        :key="item.id"
                        @click="handleNotificationClick(item)"
                        class="p-5 flex items-start gap-4 hover:bg-slate-50/60 transition cursor-pointer"
                    >
                        <!-- Box Ikon dengan Warna Kategori Masing-masing -->
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                            :class="getItemStyles(item).boxClass"
                        >
                            <component :is="getItemStyles(item).icon" class="w-4 h-4 stroke-[2.2]" />
                        </div>

                        <!-- Konten Teks Notifikasi -->
                        <div class="flex-1 min-w-0">
                            <div class="text-xs sm:text-sm font-bold text-slate-900 leading-snug">
                                {{ item.judul }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 font-normal">
                                {{ item.pesan }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 font-normal">
                                {{ formatTimeAgo(item.time_ago) }}
                            </div>
                        </div>

                        <!-- Indikator Titik Belum Dibaca Sesuai Figma -->
                        <div v-if="!item.dibaca" class="shrink-0 self-center pr-2">
                            <span class="w-2 h-2 rounded-full bg-[#7A1228] block"></span>
                        </div>
                    </div>

                    <!-- State Kosong jika tidak ada data -->
                    <div v-if="displayedNotifications.length === 0" class="py-12 text-center text-xs text-slate-400">
                        Tidak ada notifikasi pada kategori ini.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pesanan jika notifikasi diklik dan memiliki order terkait -->
        <ModalGudangOrderDetail
            :show="showDetailModal"
            :order="selectedOrder"
            @close="showDetailModal = false"
        />
    </GudangLayout>
</template>

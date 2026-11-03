<script setup>
import { ref, computed } from 'vue';
import {
    X,
    Check,
    Factory,
    MessageSquare,
    FileText,
    Calendar,
    ClipboardCheck
} from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    job: {
        type: Object,
        default: () => null,
    },
});

const emit = defineEmits(['close']);

const activeTab = ref('ringkasan'); // 'ringkasan' | 'jadwal' | 'riwayat'

const defaultSteps = [
    { name: 'Pesanan', status: 'done' },
    { name: 'Pemotongan', status: 'done' },
    { name: 'Sablon', status: 'done' },
    { name: 'Konveksi', status: 'active' },
    { name: 'QC Gudang', status: 'pending' },
    { name: 'Selesai', status: 'pending' },
];

const steps = computed(() => {
    return props.job?.steps || defaultSteps;
});

const defaultSchedules = [
    { name: 'Pemotongan', status: 'Selesai', estimate: '21 Jun 2025', type: 'done' },
    { name: 'Sablon', status: 'Sedang Dikerjakan', estimate: '23 Jun 2025', type: 'active' },
    { name: 'Produksi Konveksi', status: 'Belum Dimulai', estimate: '25 Jun 2025', type: 'pending' },
    { name: 'QC Gudang', status: 'Belum Dimulai', estimate: '27 Jun 2025', type: 'pending' },
];

const schedules = computed(() => {
    return props.job?.schedules || defaultSchedules;
});

const defaultHistory = [
    {
        title: 'Status diperbarui menjadi Produksi Konveksi',
        author: 'Oleh Siti Rahma',
        time: '23 Jun 2025 · 09.12',
        type: 'factory',
    },
    {
        title: 'Estimasi penyelesaian disepakati',
        author: 'Estimasi: 27 Jun 2025',
        time: '22 Jun 2025 · 15.40',
        type: 'message',
    },
    {
        title: 'Pesanan dan nota digital dibuat',
        author: 'Oleh Admin Toko',
        time: '21 Jun 2025 · 10.04',
        type: 'file',
    },
];

const history = computed(() => {
    return props.job?.history || defaultHistory;
});

const getHistoryIcon = (type) => {
    switch (type) {
        case 'factory':
            return Factory;
        case 'message':
            return MessageSquare;
        case 'file':
            return FileText;
        default:
            return Factory;
    }
};

const closeModal = () => {
    emit('close');
    activeTab.value = 'ringkasan';
};
</script>

<template>
    <div
        v-if="show && job"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 backdrop-blur-xs p-4 animate-in fade-in duration-200"
    >
        <div
            class="bg-white rounded-3xl max-w-4xl w-full p-7 sm:p-8 shadow-2xl border border-[#F0E6E8] overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[92vh]"
        >
            <!-- Top Header (No. Order + Customer Info + Status Badge + Close Button) -->
            <div class="flex items-start justify-between gap-4 pb-5 border-b border-[#F0E6E8]">
                <div class="min-w-0">
                    <h2 class="text-2xl font-extrabold text-[#1F2937] tracking-tight">
                        {{ job.order_number }}
                    </h2>
                    <p class="text-xs text-[#8E7E84] mt-1">
                        {{ job.item_name }} · {{ job.sku }} · {{ job.quantity }} pcs
                    </p>

                    <div class="flex items-center gap-2.5 mt-2.5 flex-wrap">
                        <!-- Cyan Status Badge with Dot -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E0F2FE] text-[#0369A1] text-xs font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0284C7]"></span>
                            <span>{{ job.status }}</span>
                        </div>
                        <span class="text-xs text-[#8E7E84]">
                            {{ job.last_updated || 'Terakhir diperbarui 8 menit lalu' }}
                        </span>
                    </div>
                </div>

                <!-- Close Button matching screenshot -->
                <button
                    type="button"
                    @click="closeModal"
                    class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors shrink-0"
                    title="Tutup"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Tab Navigation Header (Ringkasan | Jadwal & progres | Riwayat) -->
            <div class="border-b border-[#F0E6E8] mt-4">
                <div class="flex items-center gap-6 sm:gap-8">
                    <!-- Tab 1: Ringkasan -->
                    <button
                        type="button"
                        @click="activeTab = 'ringkasan'"
                        :class="[
                            'pb-3 text-sm transition-all relative font-semibold',
                            activeTab === 'ringkasan'
                                ? 'text-[#7A1228] font-bold'
                                : 'text-[#5A4B50] hover:text-[#7A1228]'
                        ]"
                    >
                        <span>Ringkasan</span>
                        <span
                            v-if="activeTab === 'ringkasan'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#7A1228] rounded-full"
                        ></span>
                    </button>

                    <!-- Tab 2: Jadwal & progres -->
                    <button
                        type="button"
                        @click="activeTab = 'jadwal'"
                        :class="[
                            'pb-3 text-sm transition-all relative font-semibold',
                            activeTab === 'jadwal'
                                ? 'text-[#7A1228] font-bold'
                                : 'text-[#5A4B50] hover:text-[#7A1228]'
                        ]"
                    >
                        <span>Jadwal & progres</span>
                        <span
                            v-if="activeTab === 'jadwal'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#7A1228] rounded-full"
                        ></span>
                    </button>

                    <!-- Tab 3: Riwayat -->
                    <button
                        type="button"
                        @click="activeTab = 'riwayat'"
                        :class="[
                            'pb-3 text-sm transition-all relative font-semibold',
                            activeTab === 'riwayat'
                                ? 'text-[#7A1228] font-bold'
                                : 'text-[#5A4B50] hover:text-[#7A1228]'
                        ]"
                    >
                        <span>Riwayat</span>
                        <span
                            v-if="activeTab === 'riwayat'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#7A1228] rounded-full"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="py-6 overflow-y-auto flex-1 pr-1 space-y-6">
                <!-- TAB 1: RINGKASAN (Matching Image 1) -->
                <div v-if="activeTab === 'ringkasan'" class="space-y-7">
                    <!-- 6 Grid Cards (2 rows x 3 cols) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Card 1: Nama Customer -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Nama customer
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1">
                                {{ job.item_name }}
                            </span>
                        </div>

                        <!-- Card 2: Kode Seri -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Kode seri
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1 font-mono">
                                {{ job.sku }}
                            </span>
                        </div>

                        <!-- Card 3: Jumlah -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Jumlah
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1">
                                {{ job.quantity }} pcs
                            </span>
                        </div>

                        <!-- Card 4: Target Penyelesaian -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Target penyelesaian
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1">
                                {{ job.target_date_full || job.target_date || '28 Jun 2025' }}
                            </span>
                        </div>

                        <!-- Card 5: Estimasi Gudang -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Estimasi gudang
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1">
                                {{ job.estimated_warehouse || '27 Jun 2025' }}
                            </span>
                        </div>

                        <!-- Card 6: Tempat Konveksi -->
                        <div class="p-4 rounded-2xl bg-[#FBF7F8] border border-[#F2ECEE]">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Tempat konveksi
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-1 truncate" :title="job.konveksi_name">
                                {{ job.konveksi_name || 'Konveksi Sinar Jaya' }}
                            </span>
                        </div>
                    </div>

                    <!-- ALUR PENGERJAAN Stepper -->
                    <div>
                        <h4 class="text-xs font-bold text-[#5A4B50] tracking-wider uppercase mb-6">
                            ALUR PENGERJAAN
                        </h4>

                        <!-- Horizontal Stepper Row -->
                        <div class="relative flex items-center justify-between px-3 sm:px-6">
                            <!-- Background Connecting Line -->
                            <div class="absolute left-8 right-8 top-4 -translate-y-1/2 h-0.5 bg-gray-200 z-0"></div>

                            <!-- Steps Items -->
                            <div
                                v-for="(step, idx) in steps"
                                :key="idx"
                                class="relative z-10 flex flex-col items-center text-center group"
                            >
                                <!-- Step Circle Icon -->
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all',
                                        step.status === 'done'
                                            ? 'bg-[#15803D] text-white shadow-xs'
                                            : step.status === 'active'
                                                ? 'bg-[#7A1228] text-white ring-4 ring-[#7A1228]/15 shadow-xs'
                                                : 'bg-white border-2 border-gray-200 text-gray-400'
                                    ]"
                                >
                                    <Check v-if="step.status === 'done'" class="w-4 h-4 stroke-[2.5]" />
                                    <span v-else>{{ idx + 1 }}</span>
                                </div>

                                <!-- Step Label -->
                                <span
                                    :class="[
                                        'text-xs mt-2.5 font-medium whitespace-nowrap',
                                        step.status === 'done' || step.status === 'active'
                                            ? 'text-[#1F2937] font-semibold'
                                            : 'text-gray-400'
                                    ]"
                                >
                                    {{ step.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: JADWAL & PROGRES (Matching Image 2) -->
                <div v-else-if="activeTab === 'jadwal'" class="divide-y divide-gray-100">
                    <div
                        v-for="(sched, idx) in schedules"
                        :key="idx"
                        class="py-4.5 first:pt-1 last:pb-1 flex items-center justify-between gap-4"
                    >
                        <!-- Left: Circle Icon + Titles -->
                        <div class="flex items-center gap-3.5">
                            <!-- Icon Circle -->
                            <div
                                :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                                    sched.type === 'done'
                                        ? 'bg-[#15803D] text-white'
                                        : sched.type === 'active'
                                            ? 'bg-[#7A1228] text-white'
                                            : 'bg-[#F1F3F5] text-gray-500'
                                ]"
                            >
                                <Check v-if="sched.type === 'done'" class="w-4 h-4 stroke-[2.5]" />
                                <span v-else>{{ idx + 1 }}</span>
                            </div>

                            <!-- Name & Status Subtext -->
                            <div>
                                <h4 class="text-sm font-bold text-[#1F2937]">
                                    {{ sched.name }}
                                </h4>
                                <p class="text-xs text-[#6B7280] mt-0.5">
                                    {{ sched.status }}
                                </p>
                            </div>
                        </div>

                        <!-- Right: Estimasi Label & Date -->
                        <div class="text-right shrink-0">
                            <span class="text-[10px] uppercase font-semibold text-gray-400 tracking-wider block">
                                Estimasi
                            </span>
                            <span class="text-xs font-bold text-gray-900 block mt-0.5">
                                {{ sched.estimate }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: RIWAYAT (Matching Image 3) -->
                <div v-else-if="activeTab === 'riwayat'" class="divide-y divide-gray-100">
                    <div
                        v-for="(hist, idx) in history"
                        :key="idx"
                        class="py-4.5 first:pt-1 last:pb-1 flex items-start gap-4"
                    >
                        <!-- Left: Soft Pink Icon Box -->
                        <div class="w-10 h-10 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0 mt-0.5">
                            <component :is="getHistoryIcon(hist.type)" class="w-5 h-5" />
                        </div>

                        <!-- Right: Title, Author, Timestamp -->
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-[#1F2937]">
                                {{ hist.title }}
                            </h4>
                            <p class="text-xs text-[#6B7280] mt-0.5">
                                {{ hist.author }}
                            </p>
                            <div class="text-[11px] text-[#9CA3AF] mt-1.5 font-medium">
                                {{ hist.time }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


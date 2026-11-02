<script setup>
import { Head, Link } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import {
    Factory,
    Shirt,
    ClipboardCheck,
    Truck,
    ChevronRight,
    Bell,
    CheckCircle2,
    Clock,
    Package
} from 'lucide-vue-next';

const props = defineProps({
    greeting: {
        type: String,
        default: 'Selamat pagi, Siti',
    },
    formattedDate: {
        type: String,
        default: 'SENIN, 23 JUNI 2025',
    },
    stats: {
        type: Object,
        required: true,
    },
    priorityJobs: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

const getBadgeStyle = (color) => {
    switch (color) {
        case 'cyan':
            return 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]';
        case 'blue':
            return 'bg-blue-50 text-blue-700 border border-blue-200';
        case 'amber':
            return 'bg-amber-50 text-amber-700 border border-amber-200';
        case 'emerald':
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        default:
            return 'bg-gray-100 text-gray-700 border border-gray-200';
    }
};

const getDotColor = (color) => {
    switch (color) {
        case 'cyan':
            return 'bg-[#0284C7]';
        case 'blue':
            return 'bg-blue-600';
        case 'amber':
            return 'bg-amber-600';
        case 'emerald':
            return 'bg-emerald-600';
        default:
            return 'bg-gray-500';
    }
};
</script>

<template>
    <Head title="Dashboard Admin Konveksi" />

    <KonveksiLayout title="Dashboard">
        <!-- Page Header -->
        <div class="mb-7">
            <!-- Eyebrow Tag -->
            <div class="text-[11px] font-bold tracking-wider text-[#7A1228] uppercase">
                ADMIN KONVEKSI · {{ formattedDate }}
            </div>

            <!-- Main Greeting -->
            <h1 class="text-3xl font-extrabold text-[#1F2937] tracking-tight mt-1">
                {{ greeting }}
            </h1>

            <!-- Subtitle -->
            <p class="text-sm text-[#6B7280] mt-1">
                Berikut pekerjaan yang perlu diperbarui hari ini.
            </p>
        </div>

        <!-- 4 Stat Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <!-- Card 1: Pekerjaan Aktif -->
            <div class="bg-white rounded-2xl p-5 border border-[#F0E6E8] flex items-center gap-4 shadow-xs hover:border-[#E8D4D8] transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                    <Factory class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500">
                        {{ stats.pekerjaan_aktif.label }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900 leading-tight mt-0.5">
                        {{ stats.pekerjaan_aktif.value }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ stats.pekerjaan_aktif.subtext }}
                    </div>
                </div>
            </div>

            <!-- Card 2: Dalam Produksi -->
            <div class="bg-white rounded-2xl p-5 border border-[#F0E6E8] flex items-center gap-4 shadow-xs hover:border-[#E8D4D8] transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#E0F2FE] text-[#0284C7] flex items-center justify-center shrink-0">
                    <Shirt class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500">
                        {{ stats.dalam_produksi.label }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900 leading-tight mt-0.5">
                        {{ stats.dalam_produksi.value }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ stats.dalam_produksi.subtext }}
                    </div>
                </div>
            </div>

            <!-- Card 3: Menunggu QC -->
            <div class="bg-white rounded-2xl p-5 border border-[#F0E6E8] flex items-center gap-4 shadow-xs hover:border-[#E8D4D8] transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0">
                    <ClipboardCheck class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500">
                        {{ stats.menunggu_qc.label }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900 leading-tight mt-0.5">
                        {{ stats.menunggu_qc.value }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ stats.menunggu_qc.subtext }}
                    </div>
                </div>
            </div>

            <!-- Card 4: Siap Dikirim -->
            <div class="bg-white rounded-2xl p-5 border border-[#F0E6E8] flex items-center gap-4 shadow-xs hover:border-[#E8D4D8] transition-all">
                <div class="w-12 h-12 rounded-xl bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center shrink-0">
                    <Truck class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500">
                        {{ stats.siap_dikirim.label }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900 leading-tight mt-0.5">
                        {{ stats.siap_dikirim.value }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ stats.siap_dikirim.subtext }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Columns Layout (Pekerjaan Prioritas & Aktivitas Terbaru) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Pekerjaan Prioritas (~65%) -->
            <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs">
                <!-- Card Header -->
                <div class="flex items-center justify-between pb-5 border-b border-[#F7EBEF]/60">
                    <div>
                        <h2 class="text-lg font-bold text-[#1F2937]">
                            Pekerjaan prioritas
                        </h2>
                        <p class="text-xs text-[#8E7E84] mt-0.5">
                            Urut berdasarkan target penyelesaian terdekat
                        </p>
                    </div>
                    <Link
                        href="/konveksi/pekerjaan"
                        class="inline-flex items-center gap-1 text-xs font-bold text-[#7A1228] hover:text-[#9A1633] transition-colors group"
                    >
                        <span>Lihat semua</span>
                        <ChevronRight class="w-4 h-4 transition-transform group-hover:translate-x-0.5" />
                    </Link>
                </div>

                <!-- Priority Jobs List -->
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="job in priorityJobs"
                        :key="job.id"
                        class="py-4.5 first:pt-4 last:pb-1 group transition-colors"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <!-- Left: Icon + Info -->
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                                    <Shirt class="w-5 h-5" />
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-[#1F2937]">
                                            {{ job.order_number }}
                                        </span>
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold',
                                                getBadgeStyle(job.status_color)
                                            ]"
                                        >
                                            <span :class="['w-1.5 h-1.5 rounded-full', getDotColor(job.status_color)]"></span>
                                            {{ job.status }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-[#6B7280] mt-1">
                                        {{ job.item_name }} · {{ job.sku }} · {{ job.quantity }} pcs
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Target Date & Arrow -->
                            <div class="flex items-center gap-3 shrink-0 text-right">
                                <div>
                                    <div class="text-[10px] uppercase font-semibold tracking-wider text-gray-400">
                                        Target
                                    </div>
                                    <div class="text-xs font-bold text-gray-900">
                                        {{ job.target_date }}
                                    </div>
                                </div>
                                <Link
                                    :href="`/konveksi/pekerjaan?highlight=${job.id}`"
                                    class="p-1 rounded-lg text-gray-400 group-hover:text-[#7A1228] group-hover:translate-x-0.5 transition-all"
                                >
                                    <ChevronRight class="w-5 h-5" />
                                </Link>
                            </div>
                        </div>

                        <!-- Progress Bar Underneath -->
                        <div class="mt-3.5 flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-[#F2EAEC] rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-[#7A1228] rounded-full transition-all duration-500"
                                    :style="{ width: `${job.progress_percentage}%` }"
                                ></div>
                            </div>
                            <span class="text-[11px] font-semibold text-[#8E7E84] shrink-0">
                                {{ job.progress_percentage }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Aktivitas Terbaru (~35%) -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs">
                <!-- Card Header -->
                <div class="pb-5 border-b border-[#F7EBEF]/60">
                    <h2 class="text-lg font-bold text-[#1F2937]">
                        Aktivitas terbaru
                    </h2>
                    <p class="text-xs text-[#8E7E84] mt-0.5">
                        Pembaruan lintas divisi
                    </p>
                </div>

                <!-- Activities List -->
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="act in recentActivities"
                        :key="act.id"
                        class="py-4 first:pt-4 last:pb-1 flex items-start gap-3.5"
                    >
                        <!-- Icon -->
                        <div
                            :class="[
                                'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5',
                                act.type === 'progress' ? 'bg-[#E0F2FE] text-[#0284C7]' :
                                act.type === 'material' ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF3C7] text-[#D97706]'
                            ]"
                        >
                            <Bell v-if="act.type === 'progress'" class="w-4 h-4" />
                            <Package v-else-if="act.type === 'material'" class="w-4 h-4" />
                            <ClipboardCheck v-else class="w-4 h-4" />
                        </div>

                        <!-- Content -->
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-[#1F2937]">
                                {{ act.title }}
                            </div>
                            <div class="text-xs text-[#6B7280] mt-0.5 leading-relaxed">
                                {{ act.description }}
                            </div>
                            <div class="text-[11px] text-[#9CA3AF] mt-1.5 flex items-center gap-1">
                                <Clock class="w-3 h-3 text-gray-400" />
                                <span>{{ act.time_ago }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </KonveksiLayout>
</template>


<script setup>
import { computed } from 'vue';
import {
    ClipboardList,
    Scissors,
    Palette,
    Shirt,
    CheckCircle,
    CheckCircle2,
    XCircle,
    Clock
} from 'lucide-vue-next';

const props = defineProps({
    currentStep: {
        type: Number,
        default: 0, // 0: Pesanan, 1: Pemotongan, 2: Sablon, 3: Konveksi, 4: QC Gudang, 5: Selesai
    },
    status: {
        type: String,
        default: 'menunggu_persetujuan',
    },
    stages: {
        type: Array,
        default: () => [],
    },
});

const steps = [
    { key: 'pesanan', label: 'Pesanan Diterima', icon: ClipboardList },
    { key: 'pemotongan', label: 'Pemotongan', icon: Scissors },
    { key: 'sablon', label: 'Sablon', icon: Palette },
    { key: 'konveksi', label: 'Konveksi', icon: Shirt },
    { key: 'qc_gudang', label: 'QC Gudang', icon: CheckCircle },
    { key: 'selesai', label: 'Selesai', icon: CheckCircle2 },
];

const isCancelled = computed(() => props.status === 'dibatalkan');
</script>

<template>
    <div class="w-full py-4 px-2">
        <!-- Cancelled State Banner -->
        <div v-if="isCancelled" class="bg-rose-50 border border-rose-200 rounded-xl p-3 flex items-center gap-3 text-rose-800 text-sm mb-4">
            <XCircle class="w-5 h-5 text-rose-600 shrink-0" />
            <div>
                <span class="font-bold">Pesanan Dibatalkan.</span>
                <span class="text-xs text-rose-600 block mt-0.5">Alur produksi telah dihentikan oleh Admin Toko.</span>
            </div>
        </div>

        <!-- 6-Stage Visual Stepper -->
        <div class="relative flex items-center justify-between">
            <!-- Connecting Line Background -->
            <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-slate-200 z-0"></div>

            <!-- Connecting Line Progress Active -->
            <div
                class="absolute left-6 top-1/2 -translate-y-1/2 h-1 transition-all duration-500 z-0"
                :class="isCancelled ? 'bg-rose-400' : 'bg-[#7A1228]'"
                :style="{
                    width: isCancelled
                        ? '100%'
                        : `${Math.min(100, (Math.max(0, currentStep) / (steps.length - 1)) * 100)}%`
                }"
            ></div>

            <!-- Step Circles -->
            <div
                v-for="(st, idx) in steps"
                :key="st.key"
                class="relative z-10 flex flex-col items-center group cursor-default"
            >
                <div
                    class="w-10 h-10 md:w-11 md:h-11 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 shadow-xs"
                    :class="[
                        isCancelled
                            ? 'bg-rose-100 text-rose-600 border-2 border-rose-300'
                            : idx < currentStep
                            ? 'bg-emerald-600 text-white ring-4 ring-emerald-50'
                            : idx === currentStep
                            ? 'bg-[#7A1228] text-white ring-4 ring-[#FDF1F3] scale-110 shadow-md shadow-[#7A1228]/25'
                            : 'bg-white text-slate-400 border-2 border-slate-200'
                    ]"
                >
                    <CheckCircle2 v-if="!isCancelled && idx < currentStep" class="w-5 h-5" />
                    <component v-else :is="st.icon" class="w-4 h-4 md:w-5 md:h-5" />
                </div>

                <div class="mt-2 text-center">
                    <span
                        class="text-[11px] md:text-xs font-semibold block transition-colors leading-tight"
                        :class="[
                            isCancelled
                                ? 'text-rose-700'
                                : idx === currentStep
                                ? 'text-[#7A1228] font-bold'
                                : idx < currentStep
                                ? 'text-emerald-700'
                                : 'text-slate-400'
                        ]"
                    >
                        {{ st.label }}
                    </span>
                    <span
                        class="text-[9px] font-medium hidden sm:block"
                        :class="idx <= currentStep ? 'text-slate-500' : 'text-slate-400'"
                    >
                        {{ idx < currentStep ? 'Selesai' : (idx === currentStep ? 'Aktif' : 'Menunggu') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>


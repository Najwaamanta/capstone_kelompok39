<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import JobDetailModal from '@/Components/JobDetailModal.vue';
import {
    Shirt,
    Eye,
    PenLine,
    X,
    CheckCircle2,
    Clock,
    Calendar,
    Building2,
    Layers,
    FileText,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    jobs: {
        type: Array,
        default: () => [],
    },
    currentStatus: {
        type: String,
        default: 'semua',
    },
});

// Modals State
const selectedDetailJob = ref(null);
const selectedUpdateJob = ref(null);

const updateForm = useForm({
    status: '',
    progress_percentage: 0,
    notes: '',
});

const openDetailModal = (job) => {
    selectedDetailJob.value = job;
};

const closeDetailModal = () => {
    selectedDetailJob.value = null;
};

const openUpdateModal = (job) => {
    selectedUpdateJob.value = job;
    updateForm.status = job.status;
    updateForm.progress_percentage = job.progress_percentage;
    updateForm.notes = job.notes || '';
};

const closeUpdateModal = () => {
    if (updateForm.processing) return;
    selectedUpdateJob.value = null;
    updateForm.reset();
};

const submitStatusUpdate = () => {
    if (!selectedUpdateJob.value) return;

    updateForm.post(`/konveksi/pekerjaan/${selectedUpdateJob.value.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local state if needed
            selectedUpdateJob.value.status = updateForm.status;
            selectedUpdateJob.value.progress_percentage = Number(updateForm.progress_percentage);
            selectedUpdateJob.value = null;
        },
    });
};

const getBadgeStyle = (status) => {
    if (status.includes('Produksi')) {
        return {
            container: 'bg-[#E0F2FE] text-[#0369A1]',
            dot: 'bg-[#0284C7]',
        };
    }
    if (status.includes('QC')) {
        return {
            container: 'bg-[#F1F3F5] text-[#495057]',
            dot: 'bg-[#868E96]',
        };
    }
    if (status.includes('Potong')) {
        return {
            container: 'bg-[#E0F2FE] text-[#0284C7]',
            dot: 'bg-[#0284C7]',
        };
    }
    return {
        container: 'bg-emerald-50 text-emerald-700',
        dot: 'bg-emerald-600',
    };
};
</script>

<template>
    <Head title="Pekerjaan Saya - Admin Konveksi" />

    <KonveksiLayout title="Pekerjaan Saya">
        <!-- Page Header -->
        <div class="mb-7">
            <!-- Eyebrow Tag -->
            <div class="text-[11px] font-bold tracking-wider text-[#7A1228] uppercase">
                PRODUKSI
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl font-extrabold text-[#1F2937] tracking-tight mt-1">
                Pekerjaan saya
            </h1>

            <!-- Subtitle -->
            <p class="text-sm text-[#6B7280] mt-1">
                Perbarui progres pekerjaan yang ditugaskan kepada konveksi Anda.
            </p>
        </div>

        <!-- 2-Columns Grid of Job Cards matching screenshot -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <div
                v-for="job in jobs"
                :key="job.id"
                class="bg-white rounded-2xl p-6 border border-[#F0E6E8] shadow-xs hover:border-[#E8D4D8] transition-all flex flex-col justify-between"
            >
                <div>
                    <!-- Header Section: Shirt Icon + Order Code + Status Badge -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Shirt Icon in Soft Pink Rounded Box -->
                            <div class="w-12 h-12 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                                <Shirt class="w-6 h-6" />
                            </div>

                            <!-- Order Number and Product Details -->
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-[#1F2937] tracking-tight leading-tight">
                                    {{ job.order_number }}
                                </h3>
                                <div class="text-xs text-[#8E7E84] mt-1 truncate">
                                    {{ job.item_name }} · {{ job.sku }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge Pill with Dot -->
                        <div
                            :class="[
                                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shrink-0',
                                getBadgeStyle(job.status).container
                            ]"
                        >
                            <span :class="['w-1.5 h-1.5 rounded-full', getBadgeStyle(job.status).dot]"></span>
                            <span>{{ job.status }}</span>
                        </div>
                    </div>

                    <!-- 3-Column Metadata Row (Jumlah | Target | Konveksi) -->
                    <div class="bg-[#FAF6F7]/60 rounded-xl p-3.5 my-5 grid grid-cols-3 gap-2 border border-[#F3ECEE]/50">
                        <!-- Column 1: Jumlah -->
                        <div>
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Jumlah
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-0.5">
                                {{ job.quantity }} pcs
                            </span>
                        </div>

                        <!-- Column 2: Target -->
                        <div>
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Target
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-0.5">
                                {{ job.target_date }}
                            </span>
                        </div>

                        <!-- Column 3: Konveksi -->
                        <div class="min-w-0">
                            <span class="text-[11px] font-medium text-[#8E7E84] block">
                                Konveksi
                            </span>
                            <span class="text-sm font-bold text-[#1F2937] block mt-0.5 truncate" :title="job.konveksi_name">
                                {{ job.konveksi_name }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div class="space-y-2 mb-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#5A4B50] font-medium">
                                Progres keseluruhan
                            </span>
                            <span class="font-bold text-[#1F2937]">
                                {{ job.progress_percentage }}%
                            </span>
                        </div>

                        <!-- Progress Bar with Maroon Wine Fill -->
                        <div class="h-2 bg-[#EFE8EA] rounded-full overflow-hidden">
                            <div
                                class="h-full bg-[#7A1228] rounded-full transition-all duration-500"
                                :style="{ width: `${job.progress_percentage}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Action Buttons: Detail & Perbarui Status -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-2">
                    <!-- Detail Button -->
                    <button
                        type="button"
                        @click="openDetailModal(job)"
                        class="px-4 py-2 rounded-xl border border-[#D9CDD0] bg-white text-xs font-bold text-[#3A2227] hover:bg-[#FDF1F3] hover:border-[#7A1228] hover:text-[#7A1228] transition-colors flex items-center gap-1.5 shadow-2xs"
                    >
                        <Eye class="w-4 h-4 text-[#5A4B50]" />
                        <span>Detail</span>
                    </button>

                    <!-- Perbarui Status Button -->
                    <button
                        type="button"
                        @click="openUpdateModal(job)"
                        class="px-4 py-2 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors flex items-center gap-1.5 shadow-xs"
                    >
                        <PenLine class="w-4 h-4 text-white" />
                        <span>Perbarui status</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Modal Component matching exact 3-tabs design -->
        <JobDetailModal
            :show="!!selectedDetailJob"
            :job="selectedDetailJob"
            @close="selectedDetailJob = null"
        />

        <!-- Perbarui Status Modal -->
        <div
            v-if="selectedUpdateJob"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#F0E6E8] animate-in fade-in zoom-in-95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-[#F0E6E8]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center shrink-0">
                            <PenLine class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1F2937]">
                                Perbarui Status Pekerjaan
                            </h3>
                            <p class="text-[11px] text-[#8E7E84]">
                                {{ selectedUpdateJob.order_number }} · {{ selectedUpdateJob.item_name }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="closeUpdateModal"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Form -->
                <form @submit.prevent="submitStatusUpdate" class="py-4 space-y-4">
                    <!-- Status Tahapan Dropdown -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Status Tahapan Produksi
                        </label>
                        <select
                            v-model="updateForm.status"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-gray-800 focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] bg-white"
                            required
                        >
                            <option value="Potong Pola">Potong Pola</option>
                            <option value="Produksi Konveksi">Produksi Konveksi</option>
                            <option value="Finishing Jahit">Finishing Jahit</option>
                            <option value="Menunggu QC">Menunggu QC</option>
                            <option value="QC Gudang">QC Gudang</option>
                            <option value="Siap Dikirim">Siap Dikirim</option>
                        </select>
                    </div>

                    <!-- Slider Persentase Progres -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5 text-xs font-semibold">
                            <label class="text-gray-700">Persentase Progres Keseluruhan</label>
                            <span class="text-[#7A1228] font-bold text-sm">{{ updateForm.progress_percentage }}%</span>
                        </div>
                        <input
                            v-model="updateForm.progress_percentage"
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            class="w-full accent-[#7A1228] cursor-pointer"
                        />
                        <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                            <span>0% (Mulai)</span>
                            <span>50% (Tengah)</span>
                            <span>100% (Selesai)</span>
                        </div>
                    </div>

                    <!-- Catatan Pembaruan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Catatan Pembaruan Progres
                        </label>
                        <textarea
                            v-model="updateForm.notes"
                            rows="3"
                            placeholder="Tuliskan catatan progres atau kendala (opsional)..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228]"
                        ></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="closeUpdateModal"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="updateForm.processing"
                            class="px-4 py-2 rounded-xl bg-[#7A1228] text-white text-xs font-bold hover:bg-[#600D1E] transition-colors disabled:opacity-60 shadow-xs"
                        >
                            {{ updateForm.processing ? 'Menyimpan...' : 'Simpan Pembaruan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KonveksiLayout>
</template>

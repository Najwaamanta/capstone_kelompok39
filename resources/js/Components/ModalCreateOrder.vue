<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { X, Plus, Calendar, Hash, Users, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    nextOrderId: {
        type: String,
        default: 'ORD-2611-001',
    },
});

const emit = defineEmits(['close']);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    nama_customer: '',
    kode_seri: '',
    jumlah: '',
    target_selesai: '',
    catatan: '',
});

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const submitOrder = () => {
    form.post('/toko/orders', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-hidden"
    >
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 md:p-8 shadow-2xl border border-gray-100 max-h-[90vh] flex flex-col transition-all">
            <!-- Modal Header (Fixed at top) -->
            <div class="flex items-center justify-between pb-4 border-b border-[#F7EBEF] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center">
                        <Plus class="w-5 h-5 stroke-[2.5]" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 tracking-tight">Buat Pesanan Baru</h2>
                        <p class="text-xs text-slate-500">Input data pesanan dari Customer untuk diteruskan ke Gudang</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeModal"
                    class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition cursor-pointer"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Form Content (Scrollable internally) -->
            <form @submit.prevent="submitOrder" class="flex-1 overflow-y-auto pr-1 mt-6 space-y-4">
                <!-- ID Pesanan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        ID Pesanan
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            :value="nextOrderId"
                            disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-mono font-semibold text-sm cursor-not-allowed select-none"
                        />
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-[#7A1228] bg-[#FDF1F3] px-2 py-0.5 rounded-md border border-[#F7D6DC]">
                            Nota Digital
                        </div>
                    </div>
                </div>

                <!-- Nama Customer & Kode Seri (2 Kolom) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Customer <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.nama_customer"
                            type="text"
                            placeholder="Contoh: PT Harapan Bangsa"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            :class="form.errors.nama_customer ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 hover:border-slate-300'"
                        />
                        <p v-if="form.errors.nama_customer" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.nama_customer }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kode Seri Produk <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.kode_seri"
                            type="text"
                            placeholder="Contoh: HB-TSHIRT-2026"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm font-mono transition-all outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            :class="form.errors.kode_seri ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 hover:border-slate-300'"
                        />
                        <p v-if="form.errors.kode_seri" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.kode_seri }}
                        </p>
                    </div>
                </div>

                <!-- Jumlah (pcs) & Target Penyelesaian (2 Kolom) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jumlah (Pcs) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.jumlah"
                            type="number"
                            min="1"
                            placeholder="Contoh: 350"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            :class="form.errors.jumlah ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 hover:border-slate-300'"
                        />
                        <p v-if="form.errors.jumlah" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.jumlah }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Target Penyelesaian <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.target_selesai"
                            type="date"
                            :min="today"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                            :class="form.errors.target_selesai ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200 hover:border-slate-300'"
                        />
                        <p v-if="form.errors.target_selesai" class="text-xs text-rose-600 mt-1">
                            {{ form.errors.target_selesai }}
                        </p>
                    </div>
                </div>

                <!-- Tempat Konveksi (Read-only keterangan biasa) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tempat Konveksi
                    </label>
                    <input
                        type="text"
                        value="Ditetapkan Admin Gudang"
                        disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 italic text-sm cursor-not-allowed select-none"
                    />
                </div>

                <!-- Catatan Khusus -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan Spesifikasi Pesanan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea
                        v-model="form.catatan"
                        rows="3"
                        placeholder="Contoh: Bahan combed 30s premium, sablon discharge depan-belakang, jahitan rantai rapi."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm transition-all outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228]"
                    ></textarea>
                </div>

                <!-- Action Buttons (Fixed at bottom of modal) -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#7A1228] hover:bg-[#630c1f] transition-colors shadow-md shadow-[#7A1228]/20 flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Buat Pesanan' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

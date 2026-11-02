<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import ModalGudangOrderDetail from '@/Components/ModalGudangOrderDetail.vue';
import { Search, Eye } from 'lucide-vue-next';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'semua' }),
    },
    konveksis: {
        type: Array,
        default: () => [],
    },
});

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'semua');

const showDetailModal = ref(false);
const selectedOrder = ref(null);

const applyFilters = () => {
    router.get(
        '/gudang/orders',
        {
            search: searchInput.value,
            status: selectedStatus.value,
        },
        { preserveState: true, replace: true }
    );
};

let searchDebounce = null;
watch(searchInput, () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        applyFilters();
    }, 350);
});

const openDetail = (order) => {
    selectedOrder.value = order;
    showDetailModal.value = true;
};

// Helper badge status warna lembut sesuai UI screenshot
const getStatusBadge = (status) => {
    switch (status) {
        case 'dalam_produksi':
            return {
                label: 'Produksi Konveksi',
                bg: 'bg-sky-50 text-sky-700 border border-sky-200/60',
                dot: 'bg-sky-500',
            };
        case 'negosiasi':
            return {
                label: 'Negosiasi',
                bg: 'bg-amber-50 text-amber-700 border border-amber-200/60',
                dot: 'bg-amber-500',
            };
        case 'qc_gudang':
            return {
                label: 'QC Gudang',
                bg: 'bg-sky-50 text-sky-700 border border-sky-200/60',
                dot: 'bg-sky-500',
            };
        case 'selesai':
            return {
                label: 'Selesai',
                bg: 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                dot: 'bg-emerald-500',
            };
        case 'dibatalkan':
            return {
                label: 'Cancel',
                bg: 'bg-rose-50 text-rose-500 border border-rose-200/60',
                dot: 'bg-rose-400',
            };
        case 'menunggu_persetujuan':
        default:
            return {
                label: 'Menunggu Persetujuan',
                bg: 'bg-amber-50 text-amber-700 border border-amber-200/60',
                dot: 'bg-amber-500',
            };
    }
};

const formatTargetDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <Head title="Pesanan Masuk - Admin Gudang" />

    <GudangLayout title="Pesanan masuk" :breadcrumbs="['Pesanan masuk']">
        <div class="space-y-6">
            <!-- Header Halaman Sesuai Mockup UI -->
            <div>
                <p class="text-[12px] font-bold tracking-[0.2em] text-[#7A1228] uppercase mb-1.5">PESANAN</p>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pesanan masuk</h1>
                <p class="text-sm text-slate-500 mt-1">Tinjau pesanan baru, sepakati estimasi, dan atur proses pengerjaan.</p>
            </div>

            <!-- Filter Card: Search & Status Dropdown -->
            <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-96">
                    <Search class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari ID, customer, atau kode seri..."
                        class="w-full pl-11 pr-4 py-2.5 rounded-2xl text-xs border border-slate-200 bg-white placeholder-slate-400 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition"
                    />
                </div>

                <div class="w-full sm:w-auto">
                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-2xl text-xs font-medium border border-slate-200 bg-white text-slate-700 outline-hidden focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] cursor-pointer"
                    >
                        <option value="semua">Semua status</option>
                        <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                        <option value="dalam_produksi">Produksi Konveksi</option>
                        <option value="qc_gudang">QC Gudang</option>
                        <option value="negosiasi">Negosiasi</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Cancel</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Pesanan Sesuai Mockup UI -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-white text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">ID PESANAN</th>
                                <th class="py-4 px-6">CUSTOMER / KODE</th>
                                <th class="py-4 px-6">JUMLAH</th>
                                <th class="py-4 px-6">TARGET</th>
                                <th class="py-4 px-6">TEMPAT KONVEKSI</th>
                                <th class="py-4 px-6">STATUS</th>
                                <th class="py-4 px-6 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                class="hover:bg-slate-50/60 transition-colors"
                            >
                                <!-- ID Pesanan -->
                                <td class="py-4 px-6 font-bold text-slate-900 whitespace-nowrap">
                                    {{ order.id_pesanan }}
                                </td>

                                <!-- Customer / Kode Seri -->
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ order.nama_customer }}</p>
                                    <p class="text-slate-400 text-[11px] mt-0.5">{{ order.kode_seri }}</p>
                                </td>

                                <!-- Jumlah -->
                                <td class="py-4 px-6 font-medium text-slate-700 whitespace-nowrap">
                                    {{ order.jumlah }} pcs
                                </td>

                                <!-- Target -->
                                <td class="py-4 px-6 font-medium text-slate-700 whitespace-nowrap">
                                    {{ order.formatted_target || formatTargetDate(order.target_selesai) }}
                                </td>

                                <!-- Tempat Konveksi -->
                                <td class="py-4 px-6 font-medium text-slate-700 whitespace-nowrap">
                                    <span v-if="order.konveksi">{{ order.konveksi.nama }}</span>
                                    <span v-else-if="order.status === 'dibatalkan'" class="text-slate-400">—</span>
                                    <span v-else class="text-slate-600">Belum ditetapkan</span>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="getStatusBadge(order.status).bg"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getStatusBadge(order.status).dot"></span>
                                        {{ getStatusBadge(order.status).label }}
                                    </span>
                                </td>

                                <!-- Aksi Detail -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openDetail(order)"
                                        class="inline-flex items-center gap-1.5 font-bold text-[#7A1228] hover:text-[#5c0d1e] transition cursor-pointer"
                                    >
                                        <Eye class="w-4 h-4" />
                                        <span>Detail</span>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!orders.data || orders.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Belum ada pesanan yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination jika ada link -->
                <div
                    v-if="orders.links && orders.links.length > 3"
                    class="p-4 border-t border-slate-100 flex items-center justify-between"
                >
                    <span class="text-xs text-slate-500">
                        Menampilkan {{ orders.from || 0 }} - {{ orders.to || 0 }} dari {{ orders.total || 0 }} pesanan
                    </span>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in orders.links" :key="i">
                            <button
                                v-if="link.url"
                                type="button"
                                @click="router.get(link.url)"
                                v-html="link.label"
                                class="px-3 py-1 text-xs rounded-lg transition"
                                :class="link.active ? 'bg-[#7A1228] text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
                            ></button>
                            <span v-else v-html="link.label" class="px-3 py-1 text-xs text-slate-300"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Pesanan Gudang Sesuai UI Mockup -->
        <ModalGudangOrderDetail
            :show="showDetailModal"
            :order="selectedOrder"
            :konveksis="konveksis"
            @close="showDetailModal = false"
        />
    </GudangLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import TokoLayout from '@/Layouts/TokoLayout.vue';
import ModalCreateOrder from '@/Components/ModalCreateOrder.vue';
import ModalOrderDetail from '@/Components/ModalOrderDetail.vue';
import {
    Plus,
    Search,
    Eye,
    ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: 'semua' }),
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

const searchInput = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'semua');

const showCreateModal = ref(false);
const showDetailModal = ref(false);
const selectedOrder = ref(null);

const applyFilters = () => {
    router.get(
        '/toko/orders',
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

const handleStatusChange = () => {
    applyFilters();
};

const openDetail = (order) => {
    selectedOrder.value = order;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Pesanan Pelanggan - LilieFlow" />

    <TokoLayout title="Pesanan" :breadcrumbs="['Pesanan']">
        <div class="space-y-6">
            <!-- Header Section (Sesuai Foto 2) -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase block">
                        PESANAN
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                        Kelola pesanan pelanggan
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Buat, pantau, dan kelola seluruh pesanan pelanggan.
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="px-5 py-2.5 bg-[#7A1228] text-white font-bold rounded-xl hover:bg-[#630c1f] transition shadow-xs flex items-center justify-center gap-2 text-sm cursor-pointer"
                    >
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                        <span>Pesanan baru</span>
                    </button>
                </div>
            </div>

            <!-- Filter Card (Sesuai Foto 2) -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Search Input on Left -->
                <div class="relative w-full sm:w-96">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari ID, customer, atau kode seri..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-transparent bg-transparent outline-hidden placeholder:text-slate-400 text-slate-700"
                    />
                </div>

                <!-- Dropdown Select on Right (Semua status) -->
                <div class="relative w-full sm:w-auto">
                    <select
                        v-model="selectedStatus"
                        @change="handleStatusChange"
                        class="appearance-none w-full sm:w-48 pl-3.5 pr-8 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 outline-hidden hover:border-slate-300 cursor-pointer"
                    >
                        <option value="semua">Semua status</option>
                        <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                        <option value="dalam_produksi">Produksi Konveksi</option>
                        <option value="negosiasi">Negosiasi</option>
                        <option value="qc_gudang">QC Gudang</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Cancel</option>
                    </select>
                    <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>

            <!-- Table Card (Sesuai Foto 2) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 font-bold">ID PESANAN</th>
                                <th class="px-5 py-4 font-bold">CUSTOMER / KODE</th>
                                <th class="px-5 py-4 font-bold">JUMLAH</th>
                                <th class="px-5 py-4 font-bold">TARGET</th>
                                <th class="px-5 py-4 font-bold">TEMPAT KONVEKSI</th>
                                <th class="px-5 py-4 font-bold">STATUS</th>
                                <th class="px-5 py-4 font-bold text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                @click="openDetail(order)"
                                class="hover:bg-slate-50/60 cursor-pointer transition-colors"
                                :class="order.status === 'dibatalkan' ? 'opacity-60 text-slate-400' : ''"
                            >
                                <!-- ID Pesanan -->
                                <td class="px-5 py-4.5 font-bold" :class="order.status === 'dibatalkan' ? 'text-slate-400' : 'text-slate-900'">
                                    {{ order.id_pesanan }}
                                </td>

                                <!-- Customer / Kode -->
                                <td class="px-5 py-4.5">
                                    <div class="font-bold leading-snug" :class="order.status === 'dibatalkan' ? 'text-slate-500' : 'text-slate-900'">
                                        {{ order.nama_customer }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 leading-snug">
                                        {{ order.kode_seri }}
                                    </div>
                                </td>

                                <!-- Jumlah -->
                                <td class="px-5 py-4.5 font-medium" :class="order.status === 'dibatalkan' ? 'text-slate-400' : 'text-slate-700'">
                                    {{ order.jumlah }} pcs
                                </td>

                                <!-- Target -->
                                <td class="px-5 py-4.5 font-medium" :class="order.status === 'dibatalkan' ? 'text-slate-400' : 'text-slate-700'">
                                    {{ order.formatted_target }}
                                </td>

                                <!-- Tempat Konveksi -->
                                <td class="px-5 py-4.5 font-medium" :class="order.status === 'dibatalkan' ? 'text-slate-400' : 'text-slate-700'">
                                    {{ order.konveksi?.nama || (order.status === 'dibatalkan' ? '—' : 'Belum ditetapkan') }}
                                </td>

                                <!-- Status Badge (Pill with dot) -->
                                <td class="px-5 py-4.5">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold"
                                        :class="order.pill_class"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
                                        {{ order.pill_label }}
                                    </span>
                                </td>

                                <!-- Aksi (Eye + Detail in Maroon) -->
                                <td class="px-5 py-4.5 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#7A1228] hover:underline"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                        <span>Detail</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="orders.data.length === 0" class="text-center py-16 text-slate-400 text-xs">
                    Tidak ada pesanan yang sesuai dengan filter.
                </div>

                <!-- Pagination -->
                <div
                    v-if="orders.links && orders.links.length > 3"
                    class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500"
                >
                    <div>
                        Menampilkan <strong>{{ orders.from }}</strong> - <strong>{{ orders.to }}</strong> dari <strong>{{ orders.total }}</strong> pesanan
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, idx) in orders.links" :key="idx">
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

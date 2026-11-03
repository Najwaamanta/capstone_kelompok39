<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import {
    Plus,
    Search,
    AlertTriangle,
    Pencil,
    Trash2,
    X,
    ChevronDown,
    CheckCircle2
} from 'lucide-vue-next';

// Data bahan baku default sesuai mockup Figma
const initialMaterials = [
    {
        id: 1,
        nama: 'Katun Combed 24s — Maroon',
        satuan: 'meter',
        stok: 86,
        stok_minimum: 120,
    },
    {
        id: 2,
        nama: 'Rayon Viscose — Ivory',
        satuan: 'meter',
        stok: 340,
        stok_minimum: 100,
    },
    {
        id: 3,
        nama: 'Kancing Batok 18L',
        satuan: 'pcs',
        stok: 1350,
        stok_minimum: 400,
    },
    {
        id: 4,
        nama: 'Benang Jahit — Burgundy',
        satuan: 'roll',
        stok: 0,
        stok_minimum: 30,
    },
];

const materials = ref([]);

// Ambil dari localStorage jika ada, agar perubahan edit/tambah tetap tersimpan di sesi browser
onMounted(() => {
    try {
        const saved = localStorage.getItem('lilieflow_gudang_persediaan');
        if (saved) {
            materials.value = JSON.parse(saved);
        } else {
            materials.value = [...initialMaterials];
        }
    } catch {
        materials.value = [...initialMaterials];
    }
});

const saveToLocalStorage = () => {
    try {
        localStorage.setItem('lilieflow_gudang_persediaan', JSON.stringify(materials.value));
    } catch {
        // Fallback
    }
};

// Hitung status setiap item secara otomatis
const getItemStatus = (item) => {
    if (item.stok <= 0) {
        return {
            key: 'habis',
            label: 'Habis',
            dotColor: 'bg-rose-500',
            badgeClass: 'bg-rose-50 text-rose-700 border border-rose-200/60',
        };
    }
    if (item.stok < item.stok_minimum) {
        return {
            key: 'stok_rendah',
            label: 'Stok Rendah',
            dotColor: 'bg-amber-500',
            badgeClass: 'bg-amber-50 text-amber-700 border border-amber-200/60',
        };
    }
    return {
        key: 'tersedia',
        label: 'Tersedia',
        dotColor: 'bg-emerald-500',
        badgeClass: 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
    };
};

// Ringkasan jumlah kondisi stok
const countTersedia = computed(() => {
    return materials.value.filter((m) => m.stok >= m.stok_minimum && m.stok > 0).length;
});

const countRendah = computed(() => {
    return materials.value.filter((m) => m.stok < m.stok_minimum && m.stok > 0).length;
});

const countHabis = computed(() => {
    return materials.value.filter((m) => m.stok <= 0).length;
});

// Search & Filter
const searchQuery = ref('');
const selectedCondition = ref('semua');

const filteredMaterials = computed(() => {
    return materials.value.filter((item) => {
        const matchesSearch = item.nama.toLowerCase().includes(searchQuery.value.toLowerCase());
        const status = getItemStatus(item).key;

        let matchesCondition = true;
        if (selectedCondition.value === 'tersedia') {
            matchesCondition = status === 'tersedia';
        } else if (selectedCondition.value === 'stok_rendah') {
            matchesCondition = status === 'stok_rendah';
        } else if (selectedCondition.value === 'habis') {
            matchesCondition = status === 'habis';
        }

        return matchesSearch && matchesCondition;
    });
});

// Modal Tambah / Edit Bahan Baku
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
    nama: '',
    stok: 0,
    satuan: 'meter',
    stok_minimum: 0,
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
        nama: '',
        stok: 0,
        satuan: 'meter',
        stok_minimum: 0,
    };
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.value = {
        nama: item.nama,
        stok: item.stok,
        satuan: item.satuan,
        stok_minimum: item.stok_minimum,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (!form.value.nama) return;

    if (isEditing.value && editingId.value) {
        const idx = materials.value.findIndex((m) => m.id === editingId.value);
        if (idx !== -1) {
            materials.value[idx] = {
                ...materials.value[idx],
                nama: form.value.nama,
                stok: Number(form.value.stok),
                satuan: form.value.satuan,
                stok_minimum: Number(form.value.stok_minimum),
            };
        }
    } else {
        const nextId = materials.value.length > 0 ? Math.max(...materials.value.map((m) => m.id)) + 1 : 1;
        materials.value.push({
            id: nextId,
            nama: form.value.nama,
            stok: Number(form.value.stok),
            satuan: form.value.satuan,
            stok_minimum: Number(form.value.stok_minimum),
        });
    }

    saveToLocalStorage();
    closeModal();
};

const deleteItem = (item) => {
    if (confirm(`Apakah Anda yakin ingin menghapus bahan "${item.nama}"?`)) {
        materials.value = materials.value.filter((m) => m.id !== item.id);
        saveToLocalStorage();
    }
};
</script>

<template>
    <Head title="Persediaan Bahan Baku - LilieFlow" />

    <GudangLayout title="Persediaan" :breadcrumbs="['Persediaan']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section: Subtitle DATA MASTER, Title, & Button + Tambah bahan Sesuai Figma -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                        DATA MASTER
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Persediaan bahan baku
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Pantau ketersediaan dan kondisi stok bahan produksi.
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        @click="openAddModal"
                        class="px-5 py-2.5 bg-[#7A1228] text-white font-bold rounded-xl hover:bg-[#630c1f] transition shadow-xs flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer"
                    >
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                        <span>Tambah bahan</span>
                    </button>
                </div>
            </div>

            <!-- Summary Status Pills & Disclaimer Note Sesuai Figma -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Group of 3 Summary Badges -->
                <div class="bg-white border border-[#F0E6E8] rounded-2xl px-4 py-2.5 inline-flex items-center gap-5 text-xs font-semibold shadow-2xs w-fit">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-700 font-bold">{{ countTersedia }}</span>
                        <span class="text-slate-500">Stok tersedia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-slate-700 font-bold">{{ countRendah }}</span>
                        <span class="text-slate-500">Stok rendah</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="text-slate-700 font-bold">{{ countHabis }}</span>
                        <span class="text-slate-500">Stok habis</span>
                    </div>
                </div>

                <!-- Right Disclaimer Note Sesuai Figma -->
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                    <AlertTriangle class="w-4 h-4 text-slate-400 shrink-0" />
                    <span>Stok ditampilkan sebagai simulasi/lokal dan belum terhubung ke database real-time.</span>
                </div>
            </div>

            <!-- Search Bar & Dropdown Filter Sesuai Figma -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative w-full sm:w-96">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama bahan..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200/90 rounded-2xl text-xs placeholder-slate-400 outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] shadow-2xs transition"
                    />
                </div>

                <div class="relative w-full sm:w-auto">
                    <select
                        v-model="selectedCondition"
                        class="w-full sm:w-auto px-4 py-2.5 pr-8 bg-white border border-slate-200/90 rounded-2xl text-xs font-medium text-slate-700 outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] shadow-2xs transition cursor-pointer appearance-none"
                    >
                        <option value="semua">Semua kondisi stok</option>
                        <option value="tersedia">Stok tersedia</option>
                        <option value="stok_rendah">Stok rendah</option>
                        <option value="habis">Stok habis</option>
                    </select>
                    <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>

            <!-- Table Persediaan Bahan Baku Sesuai Figma -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-white text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">NAMA BAHAN</th>
                                <th class="py-4 px-6">SATUAN</th>
                                <th class="py-4 px-6">STOK SAAT INI</th>
                                <th class="py-4 px-6">STOK MINIMUM</th>
                                <th class="py-4 px-6">STATUS</th>
                                <th class="py-4 px-6 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr
                                v-for="item in filteredMaterials"
                                :key="item.id"
                                class="hover:bg-slate-50/60 transition"
                            >
                                <!-- Nama Bahan -->
                                <td class="py-4 px-6 font-bold text-slate-900 whitespace-nowrap">
                                    {{ item.nama }}
                                </td>

                                <!-- Satuan -->
                                <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                    {{ item.satuan }}
                                </td>

                                <!-- Stok Saat Ini -->
                                <td class="py-4 px-6 font-extrabold text-slate-900 whitespace-nowrap">
                                    {{ item.stok }}
                                </td>

                                <!-- Stok Minimum -->
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ item.stok_minimum }}
                                </td>

                                <!-- Status Pill -->
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="getItemStatus(item).badgeClass"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getItemStatus(item).dotColor"></span>
                                        {{ getItemStatus(item).label }}
                                    </span>
                                </td>

                                <!-- Aksi Edit & Hapus Sesuai Figma -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer"
                                            title="Edit bahan baku"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteItem(item)"
                                            class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                            title="Hapus bahan"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredMaterials.length === 0">
                                <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                                    Tidak ada data bahan baku yang sesuai dengan pencarian atau filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Bahan Baku Sesuai Mockup Figma (Image 2) -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-hidden"
        >
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 transition-all animate-in fade-in zoom-in-95 duration-150">
                <!-- Header Modal -->
                <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                            {{ isEditing ? 'Edit bahan baku' : 'Tambah bahan baku' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Isi detail bahan dan ambang stok minimum.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="p-1.5 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Form Fields Sesuai Gambar Figma -->
                <form @submit.prevent="submitForm" class="space-y-4 mt-5">
                    <!-- 1. Nama bahan * -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nama bahan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.nama"
                            type="text"
                            required
                            placeholder="Contoh: Katun Combed 24s — Maroon"
                            class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                        />
                    </div>

                    <!-- 2. Jumlah stok * & Satuan * (2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Jumlah stok <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.stok"
                                type="number"
                                min="0"
                                required
                                class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.satuan"
                                    required
                                    class="w-full px-3.5 py-2.5 pr-8 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition cursor-pointer appearance-none"
                                >
                                    <option value="meter">meter</option>
                                    <option value="pcs">pcs</option>
                                    <option value="roll">roll</option>
                                    <option value="yard">yard</option>
                                    <option value="kg">kg</option>
                                    <option value="lusin">lusin</option>
                                </select>
                                <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            </div>
                        </div>
                    </div>

                    <!-- 3. Stok minimum * -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Stok minimum <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.stok_minimum"
                            type="number"
                            min="0"
                            required
                            class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                        />
                    </div>

                    <!-- Footer Modal Actions Sesuai Figma -->
                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-[#7A1228] hover:bg-[#600e1f] rounded-xl transition shadow-xs cursor-pointer"
                        >
                            Simpan bahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GudangLayout>
</template>

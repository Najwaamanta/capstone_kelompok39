<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import GudangLayout from '@/Layouts/GudangLayout.vue';
import {
    Plus,
    Search,
    Pencil,
    Trash2,
    X
} from 'lucide-vue-next';

// Data bawaan sesuai gambar Figma
const initialApparel = [
    {
        id: 1,
        kode: 'DRS-A17',
        nama: 'Aurelia Midi Dress',
        bahan: 'Katun Combed 24s',
        kebutuhan: '2,4 meter / pcs',
    },
    {
        id: 2,
        kode: 'BLS-K04',
        nama: 'Kirana Puff Blouse',
        bahan: 'Rayon Viscose',
        kebutuhan: '1,6 meter / pcs',
    },
    {
        id: 3,
        kode: 'KMS-P21',
        nama: 'Pilar Oversized Shirt',
        bahan: 'Katun Poplin',
        kebutuhan: '1,9 meter / pcs',
    },
];

const apparelList = ref([]);

// Ambil dari localStorage agar perubahan tersimpan di sesi browser
onMounted(() => {
    try {
        const saved = localStorage.getItem('lilieflow_gudang_apparel');
        if (saved) {
            apparelList.value = JSON.parse(saved);
        } else {
            apparelList.value = [...initialApparel];
        }
    } catch {
        apparelList.value = [...initialApparel];
    }
});

const saveToLocalStorage = () => {
    try {
        localStorage.setItem('lilieflow_gudang_apparel', JSON.stringify(apparelList.value));
    } catch {
        // Fallback
    }
};

// Search Filter
const searchQuery = ref('');

const filteredApparel = computed(() => {
    if (!searchQuery.value) return apparelList.value;
    const q = searchQuery.value.toLowerCase();
    return apparelList.value.filter((item) => {
        return (
            item.kode.toLowerCase().includes(q) ||
            item.nama.toLowerCase().includes(q) ||
            item.bahan.toLowerCase().includes(q)
        );
    });
});

// Modal Tambah / Edit Data Apparel
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
    kode: '',
    nama: '',
    bahan: '',
    kebutuhan: '',
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
        kode: '',
        nama: '',
        bahan: '',
        kebutuhan: '',
    };
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.value = {
        kode: item.kode,
        nama: item.nama,
        bahan: item.bahan,
        kebutuhan: item.kebutuhan,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (!form.value.kode || !form.value.nama) return;

    if (isEditing.value && editingId.value) {
        const idx = apparelList.value.findIndex((a) => a.id === editingId.value);
        if (idx !== -1) {
            apparelList.value[idx] = {
                ...apparelList.value[idx],
                kode: form.value.kode,
                nama: form.value.nama,
                bahan: form.value.bahan,
                kebutuhan: form.value.kebutuhan,
            };
        }
    } else {
        const nextId = apparelList.value.length > 0 ? Math.max(...apparelList.value.map((a) => a.id)) + 1 : 1;
        apparelList.value.push({
            id: nextId,
            kode: form.value.kode,
            nama: form.value.nama,
            bahan: form.value.bahan,
            kebutuhan: form.value.kebutuhan,
        });
    }

    saveToLocalStorage();
    closeModal();
};

const deleteItem = (item) => {
    if (confirm(`Apakah Anda yakin ingin menghapus model apparel "${item.kode} - ${item.nama}"?`)) {
        apparelList.value = apparelList.value.filter((a) => a.id !== item.id);
        saveToLocalStorage();
    }
};
</script>

<template>
    <Head title="Data Apparel - LilieFlow" />

    <GudangLayout title="Data Apparel" :breadcrumbs="['Data Apparel']">
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section: Subtitle DATA MASTER, Title, Subtitle, & Button + Tambah apparel Sesuai Figma -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <div class="text-[11px] font-extrabold tracking-wider text-[#7A1228] uppercase mb-1">
                        DATA MASTER
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Data apparel
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Kelola model baju dan kebutuhan bahan per produk.
                    </p>
                </div>

                <div>
                    <button
                        type="button"
                        @click="openAddModal"
                        class="px-5 py-2.5 bg-[#7A1228] text-white font-bold rounded-xl hover:bg-[#630c1f] transition shadow-xs flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer"
                    >
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                        <span>Tambah apparel</span>
                    </button>
                </div>
            </div>

            <!-- Search Bar Sesuai Figma -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative w-full sm:w-96">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari kode atau nama model..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200/90 rounded-2xl text-xs placeholder-slate-400 outline-none focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] shadow-2xs transition"
                    />
                </div>
            </div>

            <!-- Table Data Apparel Sesuai Figma -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-white text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">KODE BAJU</th>
                                <th class="py-4 px-6">NAMA MODEL BAJU</th>
                                <th class="py-4 px-6">NAMA BAHAN</th>
                                <th class="py-4 px-6">KEBUTUHAN BAHAN</th>
                                <th class="py-4 px-6 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr
                                v-for="item in filteredApparel"
                                :key="item.id"
                                class="hover:bg-slate-50/60 transition"
                            >
                                <!-- Kode Baju -->
                                <td class="py-4 px-6 font-extrabold text-slate-900 whitespace-nowrap">
                                    {{ item.kode }}
                                </td>

                                <!-- Nama Model Baju -->
                                <td class="py-4 px-6 text-slate-700 whitespace-nowrap">
                                    {{ item.nama }}
                                </td>

                                <!-- Nama Bahan -->
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ item.bahan }}
                                </td>

                                <!-- Kebutuhan Bahan -->
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ item.kebutuhan }}
                                </td>

                                <!-- Aksi Edit & Hapus Sesuai Figma -->
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                                            title="Edit data apparel"
                                        >
                                            <Pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteItem(item)"
                                            class="w-8 h-8 flex items-center justify-center border border-rose-200 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                            title="Hapus apparel"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredApparel.length === 0">
                                <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                                    Tidak ada data apparel yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit Data Apparel Sesuai Mockup Figma (Image 2) -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4 overflow-hidden"
        >
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-xl border border-slate-100 transition-all animate-in fade-in zoom-in-95 duration-150">
                <!-- Header Modal -->
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            {{ isEditing ? 'Edit data apparel' : 'Tambah data apparel' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            Data kebutuhan bahan digunakan untuk membantu pengecekan stok.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeModal"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Form Fields 2x2 Sesuai Gambar Figma -->
                <form @submit.prevent="submitForm" class="space-y-4 mt-5">
                    <!-- Baris 1: Kode baju * & Nama model baju * (2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kode baju <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.kode"
                                type="text"
                                required
                                placeholder="DRS-A17"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama model baju <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.nama"
                                type="text"
                                required
                                placeholder="Aurelia Midi Dress"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>
                    </div>

                    <!-- Baris 2: Nama bahan * & Kebutuhan bahan * (2 Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama bahan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.bahan"
                                type="text"
                                required
                                placeholder="Katun Combed 24s"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kebutuhan bahan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.kebutuhan"
                                type="text"
                                required
                                placeholder="2,4 meter / pcs"
                                class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-[#7A1228] focus:ring-1 focus:ring-[#7A1228] outline-none transition"
                            />
                        </div>
                    </div>

                    <!-- Footer Modal Actions Sesuai Figma -->
                    <div class="flex items-center justify-end gap-2.5 pt-3">
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
                            Simpan apparel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GudangLayout>
</template>

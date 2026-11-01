<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    History,
    ChevronLeft,
    ChevronRight,
    Search,
    Calendar,
    CalendarDays,
    X,
    RotateCcw,
    ChevronDown,
    Filter,
} from 'lucide-vue-next';

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    actionsList: {
        type: Array,
        default: () => [],
    },
    todayDate: {
        type: String,
        default: '',
    },
    totalCount: {
        type: Number,
        default: 0,
    },
    todayCount: {
        type: Number,
        default: 0,
    },
});

// Search & filter states
const search = ref(props.filters.search || '');
const selectedAction = ref(props.filters.action || 'all');
const selectedDate = ref(props.filters.resolved_date || (props.filters.date === 'today' ? props.todayDate : (props.filters.date || '')));

let searchDebounceTimeout = null;

// Is date filter set specifically to today
const isTodayActive = computed(() => {
    return Boolean(props.filters.is_today || props.filters.date === 'today' || (selectedDate.value && selectedDate.value === props.todayDate));
});

// Check if any filter is currently applied
const hasActiveFilters = computed(() => {
    return Boolean(
        (search.value && search.value.trim() !== '') ||
        (selectedAction.value && selectedAction.value !== 'all') ||
        Boolean(props.filters.date) ||
        Boolean(selectedDate.value)
    );
});

const applyFilters = (customDate = undefined) => {
    const dateVal = customDate !== undefined ? customDate : (selectedDate.value || undefined);

    router.get(
        '/admin/activity-logs',
        {
            search: search.value || undefined,
            action: selectedAction.value !== 'all' ? selectedAction.value : undefined,
            date: dateVal || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const filterToday = () => {
    selectedDate.value = props.todayDate;
    applyFilters('today');
};

const clearDateFilter = () => {
    selectedDate.value = '';
    applyFilters('');
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const clearActionFilter = () => {
    selectedAction.value = 'all';
    applyFilters();
};

const resetFilters = () => {
    search.value = '';
    selectedAction.value = 'all';
    selectedDate.value = '';
    router.get(
        '/admin/activity-logs',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

// Search debounce
watch(search, () => {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

// Watch action change
watch(selectedAction, () => {
    applyFilters();
});

// Watch date change (e.g. from input type="date")
watch(selectedDate, (newVal, oldVal) => {
    if (newVal !== oldVal && newVal !== 'today') {
        applyFilters(newVal);
    }
});

// Sync from backend props updates
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        search.value = newFilters.search || '';
        selectedAction.value = newFilters.action || 'all';
        selectedDate.value = newFilters.resolved_date || (newFilters.date === 'today' ? props.todayDate : (newFilters.date || ''));
    }
}, { deep: true });

const getActionBadgeClass = (action) => {
    switch (action) {
        case 'LOGIN':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'LOGOUT':
            return 'bg-gray-100 text-gray-700 border-gray-200';
        case 'REGISTER':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'ACTIVATED':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'USER_CREATED':
            return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'USER_UPDATED':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        case 'USER_DELETED':
            return 'bg-rose-50 text-rose-700 border-rose-200';
        case 'STATUS_CHANGED':
            return 'bg-orange-50 text-orange-700 border-orange-200';
        case 'PROFILE_UPDATE':
            return 'bg-teal-50 text-teal-700 border-teal-200';
        default:
            return 'bg-gray-50 text-gray-700 border-gray-200';
    }
};

const getActionLabel = (val) => {
    const item = props.actionsList.find(a => a.value === val);
    return item ? item.label : val;
};
</script>

<template>
    <AdminLayout title="Log Aktivitas">
        <Head title="Log Aktivitas - LilieFlow" />

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold tracking-widest text-[#7A1228] uppercase">
                        AUDIT TRAIL
                    </span>
                    <h1 class="text-3xl font-extrabold text-[#2A0711] tracking-tight mt-1">
                        Log Aktivitas Sistem
                    </h1>
                    <p class="text-sm text-[#736368] mt-1 max-w-3xl leading-relaxed">
                        Rekam jejak seluruh aktivitas otentikasi, pendaftaran, dan pengelolaan hak akses pengguna LilieFlow.
                    </p>
                </div>
            </div>

            <!-- Active Date Filter Banner (Muncul bila filter tanggal aktif, misalnya dari klik dashboard) -->
            <div
                v-if="filters.formatted_filter_date"
                class="bg-gradient-to-r from-[#ECFDF5] to-[#F0FDF4] border border-[#A7F3D0] rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm text-[#065F46] shadow-xs"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-[#059669] shrink-0">
                        <CalendarDays class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="font-bold text-[#065F46] flex items-center gap-2">
                            <span>Menampilkan Aktivitas: {{ filters.formatted_filter_date }}</span>
                            <span v-if="filters.is_today" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#059669] text-white">
                                Hari Berlangsung
                            </span>
                        </div>
                        <div class="text-xs text-[#047857] mt-0.5">
                            Ditemukan <strong class="font-bold">{{ logs.total }}</strong> log aktivitas pada periode tanggal ini.
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    @click="clearDateFilter"
                    class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-xs font-semibold text-[#065F46] hover:bg-[#E6F8F0] border border-[#A7F3D0] shadow-2xs transition-colors cursor-pointer"
                >
                    <RotateCcw class="w-3.5 h-3.5" />
                    <span>Tampilkan Semua Hari</span>
                </button>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl p-4 border border-[#F0E6E8] shadow-xs space-y-3">
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9E8B90]">
                            <Search class="w-4 h-4" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari aktivitas, pengguna, atau IP..."
                            class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-[#DFD1D5] text-sm text-[#2A0711] placeholder-[#B5A3A8] focus:outline-none focus:ring-2 focus:ring-[#7A1228]/20 focus:border-[#7A1228] transition-all bg-[#FCFBFB]"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#9E8B90] hover:text-[#7A1228] cursor-pointer"
                            title="Hapus kata kunci"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Filter Controls (Action & Date) -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Date Filter Buttons & Input -->
                        <div class="flex items-center bg-[#F8F5F6] p-1 rounded-xl border border-[#DFD1D5]">
                            <!-- Quick Button: Semua -->
                            <button
                                type="button"
                                @click="clearDateFilter"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                                :class="[
                                    !filters.date && !selectedDate
                                        ? 'bg-white text-[#7A1228] shadow-2xs'
                                        : 'text-[#736368] hover:text-[#2A0711]'
                                ]"
                            >
                                Semua
                            </button>

                            <!-- Quick Button: Hari Ini -->
                            <button
                                type="button"
                                @click="filterToday"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                                :class="[
                                    isTodayActive
                                        ? 'bg-[#059669] text-white shadow-2xs'
                                        : 'text-[#736368] hover:text-[#2A0711]'
                                ]"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="isTodayActive ? 'bg-white' : 'bg-[#059669]'"></span>
                                <span>Hari Ini</span>
                            </button>

                            <!-- Custom Date Picker -->
                            <div class="relative flex items-center pl-1 border-l border-[#DFD1D5] ml-1">
                                <input
                                    type="date"
                                    v-model="selectedDate"
                                    class="py-1 px-2 text-xs font-medium bg-transparent text-[#2A0711] focus:outline-none cursor-pointer"
                                    title="Pilih tanggal tertentu"
                                />
                                <button
                                    v-if="selectedDate"
                                    type="button"
                                    @click="clearDateFilter"
                                    class="pr-1 text-[#9E8B90] hover:text-rose-600 cursor-pointer"
                                    title="Hapus filter tanggal"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Action Filter Dropdown -->
                        <div class="relative min-w-[170px]">
                            <select
                                v-model="selectedAction"
                                class="w-full appearance-none px-4 py-2.5 rounded-xl border text-sm font-medium transition-all pr-9 cursor-pointer"
                                :class="[
                                    selectedAction !== 'all'
                                        ? 'border-[#7A1228] bg-[#FDF1F3] text-[#7A1228] font-semibold'
                                        : 'border-[#DFD1D5] bg-[#FCFBFB] text-[#443339]'
                                ]"
                            >
                                <option
                                    v-for="act in actionsList"
                                    :key="act.value"
                                    :value="act.value"
                                >
                                    {{ act.label }}
                                </option>
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                                :class="selectedAction !== 'all' ? 'text-[#7A1228]' : 'text-[#9E8B90]'"
                            >
                                <ChevronDown class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filter Tags Row -->
                <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 pt-2 border-t border-[#F5ECF0] text-xs">
                    <span class="text-[#8C7A81] font-semibold">Filter aktif:</span>

                    <!-- Date Tag -->
                    <span
                        v-if="filters.formatted_filter_date || selectedDate"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#ECFDF5] text-[#059669] font-medium border border-[#A7F3D0]"
                    >
                        <CalendarDays class="w-3.5 h-3.5 text-[#059669]" />
                        <span>Tanggal: <strong>{{ filters.formatted_filter_date || selectedDate }}</strong></span>
                        <button type="button" @click="clearDateFilter" class="hover:text-rose-600 cursor-pointer ml-0.5">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Search Tag -->
                    <span
                        v-if="search"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-medium"
                    >
                        <span>Cari: "<strong>{{ search }}</strong>"</span>
                        <button type="button" @click="clearSearch" class="hover:text-rose-600 cursor-pointer ml-0.5">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Action Tag -->
                    <span
                        v-if="selectedAction !== 'all'"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#E0F2FE] text-[#0369A1] font-medium border border-[#BAE6FD]"
                    >
                        <span>Aksi: <strong>{{ getActionLabel(selectedAction) }}</strong></span>
                        <button type="button" @click="clearActionFilter" class="hover:text-rose-600 cursor-pointer ml-0.5">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </span>

                    <!-- Reset All Filters Button -->
                    <button
                        type="button"
                        @click="resetFilters"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[#7A1228] hover:bg-[#FDF1F3] font-semibold transition-colors cursor-pointer ml-auto"
                    >
                        <RotateCcw class="w-3 h-3" />
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-[#FDEEF1] text-[#7E6E74] text-[11px] font-bold uppercase tracking-wider border-b border-[#F5D5DC]">
                            <tr>
                                <th class="py-3.5 px-6">WAKTU</th>
                                <th class="py-3.5 px-6">PENGGUNA</th>
                                <th class="py-3.5 px-6">JENIS AKSI</th>
                                <th class="py-3.5 px-6">DESKRIPSI AKTIVITAS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5ECF0]">
                            <!-- Empty State -->
                            <tr v-if="logs.data.length === 0">
                                <td colspan="4" class="py-12 px-6 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2 max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-full bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center">
                                            <History class="w-6 h-6" />
                                        </div>
                                        <p class="text-sm font-bold text-[#2A0711] mt-1">
                                            Tidak Ada Aktivitas Ditemukan
                                        </p>
                                        <p class="text-xs text-[#9E8B90] leading-relaxed">
                                            {{
                                                filters.formatted_filter_date
                                                    ? `Belum ada log aktivitas yang tercatat untuk ${filters.formatted_filter_date}.`
                                                    : 'Tidak ada rekam jejak aktivitas yang sesuai dengan filter yang dipilih.'
                                            }}
                                        </p>
                                        <button
                                            v-if="hasActiveFilters"
                                            type="button"
                                            @click="resetFilters"
                                            class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-[#7A1228] bg-[#FDF1F3] hover:bg-[#FBE4E8] transition-colors cursor-pointer"
                                        >
                                            <RotateCcw class="w-3.5 h-3.5" />
                                            <span>Lihat Seluruh Log Aktivitas</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Data Rows -->
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="hover:bg-[#FCF8F9] transition-colors"
                            >
                                <td class="py-3.5 px-6 text-xs text-[#6C555C] whitespace-nowrap">
                                    <div class="font-semibold text-[#2A0711]">{{ log.formatted_created_at }}</div>
                                    <div class="text-[11px] text-[#9E8B90]">{{ log.time_ago }}</div>
                                </td>
                                <td class="py-3.5 px-6 font-bold text-[#2A0711]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#FCE7EB] text-[#7A1228] font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ log.user ? log.user.initials : 'SYS' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-[#2A0711]">
                                                {{ log.user ? log.user.name : 'Sistem' }}
                                            </div>
                                            <div v-if="log.user" class="text-[11px] font-normal text-[#9E8B90]">
                                                {{ log.user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-6 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border',
                                            getActionBadgeClass(log.action)
                                        ]"
                                    >
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-xs text-[#443339]">
                                    <div class="leading-relaxed">{{ log.description }}</div>
                                    <div v-if="log.ip_address" class="text-[11px] text-[#A8989C] font-mono mt-0.5">
                                        IP: {{ log.ip_address }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-[#FDF7F8] border-t border-[#F5D5DC] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#7E6E74]">
                    <div>
                        Menampilkan <span class="font-bold text-[#2A0711]">{{ logs.from || 0 }} - {{ logs.to || 0 }}</span> dari <span class="font-bold text-[#2A0711]">{{ logs.total }}</span> Log
                    </div>

                    <!-- Pagination Links -->
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="logs.prev_page_url"
                            :href="logs.prev_page_url"
                            class="px-3 py-1.5 rounded-lg border border-[#E8D4D9] bg-white text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228] transition-colors font-medium flex items-center gap-1"
                        >
                            <ChevronLeft class="w-3.5 h-3.5" />
                            <span>Prev</span>
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 font-medium flex items-center gap-1 cursor-not-allowed"
                        >
                            <ChevronLeft class="w-3.5 h-3.5" />
                            <span>Prev</span>
                        </span>

                        <!-- Page items -->
                        <template v-for="(link, idx) in logs.links" :key="idx">
                            <template v-if="!link.label.includes('Previous') && !link.label.includes('Next')">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'w-8 h-8 rounded-lg flex items-center justify-center font-bold transition-colors',
                                        link.active
                                            ? 'bg-[#7A1228] text-white shadow-xs'
                                            : 'bg-white border border-[#E8D4D9] text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228]'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="w-8 h-8 flex items-center justify-center text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </template>

                        <Link
                            v-if="logs.next_page_url"
                            :href="logs.next_page_url"
                            class="px-3 py-1.5 rounded-lg border border-[#E8D4D9] bg-white text-[#5A454C] hover:bg-[#FDF1F3] hover:text-[#7A1228] transition-colors font-medium flex items-center gap-1"
                        >
                            <span>Next</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 font-medium flex items-center gap-1 cursor-not-allowed"
                        >
                            <span>Next</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

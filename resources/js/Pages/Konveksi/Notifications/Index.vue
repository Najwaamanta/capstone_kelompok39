<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import {
    Factory,
    Calendar,
    FileText,
    ClipboardCheck,
    Bell
} from 'lucide-vue-next';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

const localNotifications = ref([...props.notifications]);
const activeTab = ref('semua'); // 'semua' | 'unread'

const totalCount = computed(() => localNotifications.value.length);
const unreadCount = computed(() => localNotifications.value.filter(n => n.is_unread).length);

const filteredNotifications = computed(() => {
    if (activeTab.value === 'unread') {
        return localNotifications.value.filter(n => n.is_unread);
    }
    return localNotifications.value;
});

const toggleRead = (notif) => {
    notif.is_unread = !notif.is_unread;
};

const getIconConfig = (type) => {
    switch (type) {
        case 'factory':
            return {
                icon: Factory,
                wrapperClass: 'bg-[#E0F2FE] text-[#0284C7]',
            };
        case 'calendar':
            return {
                icon: Calendar,
                wrapperClass: 'bg-[#FEF3C7] text-[#D97706]',
            };
        case 'file':
            return {
                icon: FileText,
                wrapperClass: 'bg-[#FDF1F3] text-[#7A1228]',
            };
        case 'qc':
            return {
                icon: ClipboardCheck,
                wrapperClass: 'bg-[#DCFCE7] text-[#16A34A]',
            };
        default:
            return {
                icon: Bell,
                wrapperClass: 'bg-[#FDF1F3] text-[#7A1228]',
            };
    }
};
</script>

<template>
    <Head title="Notifikasi - Admin Konveksi" />

    <KonveksiLayout title="Notifikasi">
        <!-- Main Notification Container matching screenshot exactly -->
        <div class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs overflow-hidden">
            <!-- Tabs Bar Header -->
            <div class="px-6 pt-5 border-b border-[#F0E6E8]">
                <div class="flex items-center gap-6">
                    <!-- Tab: Semua -->
                    <button
                        type="button"
                        @click="activeTab = 'semua'"
                        :class="[
                            'pb-3 flex items-center gap-2 text-sm transition-all relative',
                            activeTab === 'semua'
                                ? 'font-bold text-[#7A1228]'
                                : 'font-medium text-[#6B7280] hover:text-[#7A1228]'
                        ]"
                    >
                        <span>Semua</span>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-xs font-semibold',
                                activeTab === 'semua'
                                    ? 'bg-[#FDF1F3] text-[#7A1228]'
                                    : 'bg-gray-100 text-gray-500'
                            ]"
                        >
                            {{ totalCount }}
                        </span>

                        <!-- Active Bottom Border Indicator -->
                        <span
                            v-if="activeTab === 'semua'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#7A1228] rounded-full"
                        ></span>
                    </button>

                    <!-- Tab: Belum dibaca -->
                    <button
                        type="button"
                        @click="activeTab = 'unread'"
                        :class="[
                            'pb-3 flex items-center gap-2 text-sm transition-all relative',
                            activeTab === 'unread'
                                ? 'font-bold text-[#7A1228]'
                                : 'font-medium text-[#6B7280] hover:text-[#7A1228]'
                        ]"
                    >
                        <span>Belum dibaca</span>
                        <span
                            :class="[
                                'px-2 py-0.5 rounded-full text-xs font-semibold',
                                activeTab === 'unread'
                                    ? 'bg-[#FDF1F3] text-[#7A1228]'
                                    : 'bg-gray-100 text-gray-500'
                            ]"
                        >
                            {{ unreadCount }}
                        </span>

                        <!-- Active Bottom Border Indicator -->
                        <span
                            v-if="activeTab === 'unread'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#7A1228] rounded-full"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Notification Items List -->
            <div class="divide-y divide-[#F0E6E8]">
                <div
                    v-for="notif in filteredNotifications"
                    :key="notif.id"
                    @click="toggleRead(notif)"
                    class="p-6 flex items-start justify-between gap-5 transition-colors hover:bg-[#FDF9FA]/70 cursor-pointer group"
                >
                    <!-- Left: Rounded Icon + Texts -->
                    <div class="flex items-start gap-4 min-w-0 flex-1">
                        <!-- Icon Box -->
                        <div
                            :class="[
                                'w-11 h-11 rounded-xl flex items-center justify-center shrink-0 mt-0.5 transition-transform group-hover:scale-105',
                                getIconConfig(notif.type).wrapperClass
                            ]"
                        >
                            <component
                                :is="getIconConfig(notif.type).icon"
                                class="w-5 h-5 stroke-[2]"
                            />
                        </div>

                        <!-- Content Details -->
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-[#1F2937] leading-snug">
                                {{ notif.title }}
                            </h3>
                            <p class="text-xs text-[#6B7280] mt-1 leading-relaxed">
                                {{ notif.description }}
                            </p>
                            <div class="text-[11px] text-[#9CA3AF] mt-2 font-medium">
                                {{ notif.time_ago }}
                            </div>
                        </div>
                    </div>

                    <!-- Right: Red Dot Indicator for Unread -->
                    <div class="shrink-0 self-center pl-2">
                        <span
                            v-if="notif.is_unread"
                            class="w-2 h-2 rounded-full bg-[#7A1228] inline-block"
                            title="Belum dibaca"
                        ></span>
                        <span v-else class="w-2 h-2 inline-block"></span>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredNotifications.length === 0"
                    class="p-16 text-center"
                >
                    <div class="w-12 h-12 rounded-2xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center mx-auto mb-3">
                        <Bell class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Tidak ada notifikasi</h3>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ activeTab === 'unread' ? 'Semua notifikasi telah Anda baca.' : 'Belum ada notifikasi di sini.' }}
                    </p>
                </div>
            </div>
        </div>
    </KonveksiLayout>
</template>

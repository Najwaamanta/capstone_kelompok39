<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import KonveksiLayout from '@/Layouts/KonveksiLayout.vue';
import {
    Bell,
    CheckCircle2,
    Clock,
    AlertCircle,
    Info,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

const localNotifications = ref([...props.notifications]);

const markAsRead = (notif) => {
    notif.is_unread = false;
};

const markAllAsRead = () => {
    localNotifications.value.forEach((notif) => {
        notif.is_unread = false;
    });
};
</script>

<template>
    <Head title="Notifikasi - Admin Konveksi" />

    <KonveksiLayout title="Notifikasi">
        <!-- Page Header matching screenshot exactly -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-[#1F2937] tracking-tight">
                    Notifikasi
                </h1>
                <p class="text-sm text-[#6B7280] mt-0.5">
                    Pembaruan tugas, perubahan jadwal, dan informasi dari gudang maupun administrator.
                </p>
            </div>

            <!-- Optional quick action: Tandai semua telah dibaca -->
            <button
                v-if="localNotifications.some(n => n.is_unread)"
                type="button"
                @click="markAllAsRead"
                class="self-start sm:self-auto text-xs font-semibold text-[#7A1228] hover:text-[#9A1633] hover:underline flex items-center gap-1 transition-colors"
            >
                <Check class="w-3.5 h-3.5" />
                <span>Tandai semua dibaca</span>
            </button>
        </div>

        <!-- Main Card Container matching screenshot -->
        <div class="bg-white rounded-2xl border border-[#F0E6E8] shadow-xs divide-y divide-[#F5ECEF] overflow-hidden">
            <div
                v-for="notif in localNotifications"
                :key="notif.id"
                @click="markAsRead(notif)"
                class="p-6 transition-all duration-150 flex items-start justify-between gap-4 hover:bg-[#FDF9FA]/70 cursor-pointer group"
            >
                <!-- Left: Rounded Icon + Title & Description -->
                <div class="flex items-start gap-4 min-w-0 flex-1">
                    <!-- Icon Box -->
                    <div
                        :class="[
                            'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-105',
                            notif.type === 'urgent' ? 'bg-[#FDF1F3] text-[#7A1228]' :
                            notif.type === 'info' ? 'bg-[#E0F2FE] text-[#0284C7]' :
                            'bg-[#DCFCE7] text-[#16A34A]'
                        ]"
                    >
                        <AlertCircle v-if="notif.type === 'urgent'" class="w-6 h-6 stroke-[2]" />
                        <Info v-else-if="notif.type === 'info'" class="w-6 h-6 stroke-[2]" />
                        <CheckCircle2 v-else class="w-6 h-6 stroke-[2]" />
                    </div>

                    <!-- Texts -->
                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-[#1F2937]">
                                {{ notif.title }}
                            </h3>
                            <!-- Red Unread Indicator Dot matching screenshot -->
                            <span
                                v-if="notif.is_unread"
                                class="w-2 h-2 rounded-full bg-[#7A1228] inline-block shrink-0"
                            ></span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-[#6B7280] mt-1.5 leading-relaxed">
                            {{ notif.description }}
                        </p>
                    </div>
                </div>

                <!-- Right: Timestamp with Clock Icon -->
                <div class="flex items-center gap-1.5 text-xs text-gray-400 whitespace-nowrap shrink-0 pt-1">
                    <Clock class="w-3.5 h-3.5 text-gray-400" />
                    <span>{{ notif.created_at }}</span>
                </div>
            </div>

            <!-- Empty State if no notifications -->
            <div
                v-if="localNotifications.length === 0"
                class="p-16 text-center"
            >
                <div class="w-12 h-12 rounded-2xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center mx-auto mb-3">
                    <Bell class="w-6 h-6" />
                </div>
                <h3 class="text-sm font-bold text-gray-800">Tidak ada notifikasi</h3>
                <p class="text-xs text-gray-400 mt-1">
                    Semua pembaruan operasional akan ditampilkan di sini.
                </p>
            </div>
        </div>
    </KonveksiLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutGrid,
    FileText,
    Activity,
    Bell,
    User,
    LogOut,
    ChevronRight,
    MoreHorizontal,
    CheckCircle2,
    X,
    AlertCircle,
    Menu
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
    breadcrumbs: {
        type: Array,
        default: () => ['Dashboard'],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const unreadCount = computed(() => page.props.unreadNotificationsCount ?? 0);

// Toast Notification System
const activeToast = ref(null);
let toastTimer = null;

const triggerToast = (type, message) => {
    if (!message) return;
    if (toastTimer) clearTimeout(toastTimer);
    activeToast.value = { type, message };
    toastTimer = setTimeout(() => {
        activeToast.value = null;
    }, 4000);
};

const dismissToast = () => {
    if (toastTimer) clearTimeout(toastTimer);
    activeToast.value = null;
};

watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash?.success) {
            triggerToast('success', newFlash.success);
        } else if (newFlash?.error) {
            triggerToast('error', newFlash.error);
        } else if (newFlash?.info) {
            triggerToast('info', newFlash.info);
        }
    },
    { deep: true, immediate: true }
);

const showMobileMenu = ref(false);
const showLogoutModal = ref(false);

const navItems = [
    {
        name: 'Dashboard',
        route: '/toko/dashboard',
        icon: LayoutGrid,
        activeRule: (url) => url === '/toko/dashboard' || url === '/toko',
    },
    {
        name: 'Pesanan',
        route: '/toko/orders',
        icon: FileText,
        activeRule: (url) => url.startsWith('/toko/orders'),
    },
    {
        name: 'Notifikasi',
        route: '/toko/notifications',
        icon: Bell,
        badge: unreadCount,
        activeRule: (url) => url.startsWith('/toko/notifications'),
    },
    {
        name: 'Profil',
        route: '/toko/profile',
        icon: User,
        activeRule: (url) => url.startsWith('/toko/profile'),
    },
];

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[#FBF9F9] flex font-sans text-slate-800 antialiased">
        <!-- Toast Notification Pop-up -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="transform translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="activeToast"
                class="fixed top-5 right-5 z-50 max-w-sm w-full bg-white rounded-2xl shadow-xl border border-gray-100 p-4 flex items-start gap-3 pointer-events-auto"
            >
                <div
                    class="p-2 rounded-xl shrink-0"
                    :class="activeToast.type === 'error' ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'"
                >
                    <CheckCircle2 v-if="activeToast.type === 'success'" class="w-5 h-5" />
                    <AlertCircle v-else class="w-5 h-5" />
                </div>
                <div class="flex-1 pt-0.5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ activeToast.type === 'error' ? 'Peringatan' : 'Berhasil' }}
                    </p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5 leading-snug">
                        {{ activeToast.message }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="dismissToast"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-50 transition"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </transition>

        <!-- Sidebar Desktop (Sesuai Foto 1) -->
        <aside class="hidden lg:flex flex-col w-64 bg-white border-r border-[#F0E6E8] shrink-0 sticky top-0 h-screen z-30 justify-between">
            <div>
                <!-- Brand Header -->
                <div class="px-6 pt-7 pb-4">
                    <Link href="/toko/dashboard" class="flex items-center gap-3">
                        <img
                            src="/logo_liliebelle.png"
                            alt="Lilie Belle"
                            class="w-8 h-8 object-contain rounded-md"
                            onerror="this.onerror=null; this.src='/images/logo_liliebelle.png';"
                        />
                        <div>
                            <div class="text-base font-extrabold tracking-tight text-slate-900 leading-tight">
                                LilieFlow
                            </div>
                            <div class="text-[11px] font-semibold text-[#7A1228] leading-tight">
                                Operational workspace
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Section: RUANG KERJA -->
                <div class="px-6 pt-4 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    RUANG KERJA
                </div>

                <!-- User Profile Summary in Sidebar -->
                <div class="px-4 mb-4">
                    <div class="flex items-center justify-between p-2 rounded-2xl bg-slate-50/60 border border-slate-100 hover:bg-slate-100/70 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[#FCE7EB] text-[#7A1228] font-bold text-xs flex items-center justify-center shrink-0">
                                {{ authUser?.initials || 'NP' }}
                            </div>
                            <div class="min-w-0 truncate">
                                <p class="text-xs font-bold text-slate-800 truncate leading-tight">
                                    {{ authUser?.name || 'Nadia Putri' }}
                                </p>
                                <p class="text-[11px] text-slate-400 leading-tight">
                                    Admin Toko
                                </p>
                            </div>
                        </div>
                        <Link href="/toko/profile" class="text-slate-400 hover:text-slate-600 p-1">
                            <MoreHorizontal class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="px-3 space-y-1">
                    <template v-for="item in navItems" :key="item.name">
                        <Link
                            :href="item.route"
                            :class="[
                                item.activeRule($page.url)
                                    ? 'bg-[#FDF1F3] text-[#7A1228] font-bold shadow-2xs'
                                    : 'text-slate-600 hover:bg-[#FDF1F3]/50 hover:text-[#7A1228] font-medium',
                                'flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs transition-colors'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <component
                                    :is="item.icon"
                                    class="w-4 h-4 shrink-0"
                                    :class="item.activeRule($page.url) ? 'text-[#7A1228]' : 'text-slate-400'"
                                />
                                <span>{{ item.name }}</span>
                            </div>
                            <span
                                v-if="item.badge && item.badge.value > 0"
                                class="w-5 h-5 rounded-full text-[10px] font-bold bg-[#7A1228] text-white flex items-center justify-center shrink-0"
                            >
                                {{ item.badge.value }}
                            </span>
                        </Link>
                    </template>
                </nav>
            </div>

            <!-- Bottom of Sidebar: Sync status & Logout (Sesuai Foto 1) -->
            <div class="p-4 border-t border-[#F0E6E8] space-y-3">
                <div class="flex items-center gap-2.5 px-2">
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                    <div>
                        <div class="text-xs font-bold text-slate-700 leading-tight">Data tersinkron</div>
                        <div class="text-[10px] text-slate-400 leading-tight">Diperbarui baru saja</div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="showLogoutModal = true"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-[#7A1228] hover:bg-[#FDF1F3] transition-colors"
                >
                    <LogOut class="w-4 h-4" />
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Top Navbar (Sesuai Foto 1: LilieFlow > Dashboard + Right Icons) -->
            <header class="flex items-center justify-between px-6 md:px-10 py-5 bg-transparent">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                    <button
                        type="button"
                        @click="showMobileMenu = true"
                        class="lg:hidden mr-2 p-1.5 text-slate-600 hover:text-[#7A1228]"
                    >
                        <Menu class="w-5 h-5" />
                    </button>
                    <span>LilieFlow</span>
                    <ChevronRight class="w-3.5 h-3.5 text-slate-300" />
                    <template v-for="(bc, idx) in breadcrumbs" :key="idx">
                        <span v-if="idx > 0" class="text-slate-300">/</span>
                        <span :class="idx === breadcrumbs.length - 1 ? 'text-slate-800 font-semibold' : 'text-slate-400'">
                            {{ bc }}
                        </span>
                    </template>
                </div>

                <!-- Right Action Icons: Notification bell in rounded box + User avatar NP -->
                <div class="flex items-center gap-3">
                    <Link
                        href="/toko/notifications"
                        class="relative w-9 h-9 rounded-xl bg-white border border-slate-200/90 text-slate-600 hover:text-[#7A1228] hover:border-[#7A1228]/30 flex items-center justify-center transition shadow-2xs"
                        title="Notifikasi"
                    >
                        <Bell class="w-4 h-4" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#7A1228]"
                        ></span>
                    </Link>

                    <Link
                        href="/toko/profile"
                        class="w-9 h-9 rounded-xl bg-[#FCE7EB] text-[#7A1228] font-bold text-xs flex items-center justify-center transition hover:ring-2 hover:ring-[#7A1228]/20"
                        title="Profil Saya"
                    >
                        {{ authUser?.initials || 'NP' }}
                    </Link>
                </div>
            </header>

            <!-- Page Body Slot -->
            <main class="flex-1 px-6 md:px-10 pb-12 pt-2 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>

        <!-- Mobile Drawer -->
        <div v-if="showMobileMenu" class="lg:hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex">
            <div class="w-72 bg-white h-full flex flex-col p-5 shadow-2xl justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <img src="/logo_liliebelle.png" alt="LilieFlow" class="w-7 h-7 object-contain rounded" />
                            <div>
                                <span class="font-extrabold text-sm text-slate-900 block leading-tight">LilieFlow</span>
                                <span class="text-[10px] font-semibold text-[#7A1228] block leading-tight">Operational workspace</span>
                            </div>
                        </div>
                        <button type="button" @click="showMobileMenu = false" class="p-1 text-slate-400">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="px-2 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        RUANG KERJA
                    </div>

                    <nav class="space-y-1">
                        <template v-for="item in navItems" :key="item.name">
                            <Link
                                :href="item.route"
                                @click="showMobileMenu = false"
                                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold"
                                :class="item.activeRule($page.url) ? 'bg-[#FDF1F3] text-[#7A1228]' : 'text-slate-600 hover:bg-slate-50'"
                            >
                                <div class="flex items-center gap-3">
                                    <component :is="item.icon" class="w-4 h-4" />
                                    <span>{{ item.name }}</span>
                                </div>
                                <span v-if="item.badge && item.badge.value > 0" class="w-5 h-5 rounded-full text-[10px] font-bold bg-[#7A1228] text-white flex items-center justify-center">
                                    {{ item.badge.value }}
                                </span>
                            </Link>
                        </template>
                    </nav>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        @click="handleLogout"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-700 hover:bg-rose-50"
                    >
                        <LogOut class="w-4 h-4" />
                        <span>Keluar Sistem</span>
                    </button>
                </div>
            </div>
            <div class="flex-1" @click="showMobileMenu = false"></div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div
            v-if="showLogoutModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 text-center">
                <div class="w-12 h-12 rounded-2xl bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center mb-4 mx-auto">
                    <LogOut class="w-6 h-6" />
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Konfirmasi Keluar</h3>
                <p class="text-xs text-slate-500 mb-6">
                    Apakah Anda yakin ingin mengakhiri sesi Admin Toko di LilieFlow?
                </p>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="showLogoutModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleLogout"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-[#7A1228] hover:bg-[#630c1f] transition shadow-xs"
                    >
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Factory,
    Bell,
    User,
    LogOut,
    ChevronRight,
    X,
    CheckCircle2,
    AlertCircle,
    Menu,
    MoreHorizontal
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    unreadCount: {
        type: Number,
        default: undefined,
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const unreadNotificationCount = computed(() => {
    if (props.unreadCount !== undefined) return props.unreadCount;
    return page.props.unreadNotificationCount ?? 2;
});

// Floating Toast Notification System
const activeToast = ref(null);
let toastTimer = null;

const triggerToast = (type, message) => {
    if (!message) return;
    if (toastTimer) clearTimeout(toastTimer);
    activeToast.value = { type, message };
    toastTimer = setTimeout(() => {
        activeToast.value = null;
    }, 3500);
};

const dismissToast = () => {
    if (toastTimer) clearTimeout(toastTimer);
    activeToast.value = null;
};

// Pantau pesan flash dari backend (success, error, info)
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
const showUserDropdown = ref(false);

const navItems = computed(() => [
    {
        name: 'Dashboard',
        route: '/konveksi/dashboard',
        icon: LayoutGrid,
        activeRule: (url) => url === '/konveksi/dashboard' || url === '/konveksi',
    },
    {
        name: 'Pekerjaan Saya',
        route: '/konveksi/pekerjaan',
        icon: Factory,
        activeRule: (url) => url.startsWith('/konveksi/pekerjaan'),
    },
    {
        name: 'Notifikasi',
        route: '/konveksi/notifikasi',
        icon: Bell,
        badge: unreadNotificationCount.value,
        activeRule: (url) => url.startsWith('/konveksi/notifikasi'),
    },
    {
        name: 'Profil',
        route: '/konveksi/profil',
        icon: User,
        activeRule: (url) => url.startsWith('/konveksi/profil'),
    },
]);

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[#FBF9F9] flex">
        <!-- Sidebar Desktop -->
        <aside class="hidden md:flex flex-col w-68 bg-white border-r border-[#F0E6E8] shrink-0 sticky top-0 h-screen z-30 select-none">
            <!-- Brand Logo -->
            <div class="p-6 pb-6 border-b border-[#F7EBEF]/60">
                <Link href="/konveksi/dashboard" class="flex items-center gap-3.5 group">
                    <img
                        src="/logo_liliebelle.png"
                        alt="LilieFlow Logo"
                        class="w-10 h-10 object-contain rounded-lg shadow-xs border border-[#F5D5DC]/60"
                        onerror="this.onerror=null; this.src='/images/logo_liliebelle.png';"
                    />
                    <div>
                        <div class="text-xl font-bold font-heading tracking-tight text-[#4A0D1B] group-hover:text-[#7A1228] transition-colors leading-tight">
                            LilieFlow
                        </div>
                        <div class="text-[11px] font-medium text-[#9E8B90] tracking-wide">
                            Operational workspace
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 px-4 py-5 overflow-y-auto space-y-6">
                <!-- Ruang Kerja Section -->
                <div>
                    <div class="px-2 mb-3 text-[11px] font-bold tracking-wider text-[#9E8B90] uppercase">
                        RUANG KERJA
                    </div>

                    <!-- User Profile Card -->
                    <div class="relative mb-5 p-3 rounded-2xl bg-[#FBF7F8] border border-[#F2E5E8] flex items-center justify-between transition-all hover:border-[#E8D4D8]">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-[#F6E5E8] text-[#7A1228] font-bold text-sm flex items-center justify-center shrink-0">
                                {{ authUser?.initials || 'SR' }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-[#2A0711] truncate">
                                    {{ authUser?.name || 'Siti Rahma' }}
                                </div>
                                <div class="text-xs text-[#9E8B90] truncate">
                                    {{ authUser?.role_label || 'Admin Konveksi' }}
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown toggle button -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="showUserDropdown = !showUserDropdown"
                                class="p-1.5 rounded-lg text-[#8E7E84] hover:text-[#7A1228] hover:bg-white/80 transition-colors"
                                title="Menu Pengguna"
                            >
                                <MoreHorizontal class="w-5 h-5" />
                            </button>

                            <!-- Mini popup dropdown -->
                            <div
                                v-if="showUserDropdown"
                                class="absolute right-0 top-8 w-44 bg-white rounded-xl shadow-lg border border-[#F0E6E8] py-1 z-50 animate-in fade-in"
                                @click="showUserDropdown = false"
                            >
                                <Link
                                    href="/konveksi/profil"
                                    class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium text-[#5A4B50] hover:bg-[#FDF1F3] hover:text-[#7A1228]"
                                >
                                    <User class="w-4 h-4" />
                                    <span>Lihat Profil</span>
                                </Link>
                                <div class="border-t border-[#F0E6E8] my-1"></div>
                                <button
                                    type="button"
                                    @click="showLogoutModal = true"
                                    class="w-full flex items-center gap-2 px-3.5 py-2 text-xs font-medium text-rose-700 hover:bg-rose-50 text-left"
                                >
                                    <LogOut class="w-4 h-4" />
                                    <span>Keluar</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Sidebar Navigation Menus -->
                    <nav class="space-y-1.5 relative">
                        <div
                            v-for="item in navItems"
                            :key="item.name"
                            class="relative"
                        >
                            <!-- Active indicator pill on the far left edge -->
                            <div
                                v-if="item.activeRule($page.url)"
                                class="absolute -left-4 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-[#7A1228] rounded-r-md"
                            ></div>

                            <Link
                                :href="item.route"
                                :class="[
                                    'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                                    item.activeRule($page.url)
                                        ? 'bg-[#FDF1F3] text-[#7A1228] font-bold shadow-xs'
                                        : 'text-[#5A4B50] hover:bg-[#FBF4F6] hover:text-[#7A1228]'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="item.icon"
                                        :class="[
                                            'w-5 h-5 transition-colors',
                                            item.activeRule($page.url) ? 'text-[#7A1228]' : 'text-[#8E7E84]'
                                        ]"
                                    />
                                    <span>{{ item.name }}</span>
                                </div>

                                <!-- Notification Badge -->
                                <span
                                    v-if="item.badge && item.badge > 0"
                                    class="inline-flex items-center justify-center w-5 h-5 text-[11px] font-bold text-white bg-[#7A1228] rounded-full shadow-xs shrink-0"
                                >
                                    {{ item.badge }}
                                </span>
                            </Link>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Sidebar Bottom: Sync Status & Logout -->
            <div class="p-4 border-t border-[#F0E6E8] space-y-3">
                <!-- Status Sinkronisasi -->
                <div class="flex items-center gap-3 px-2 py-1">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" />
                    <div>
                        <div class="text-xs font-bold text-[#2A0711] leading-tight">
                            Data tersinkron
                        </div>
                        <div class="text-[11px] text-[#9E8B90]">
                            Diperbarui baru saja
                        </div>
                    </div>
                </div>

                <!-- Keluar -->
                <button
                    type="button"
                    @click="showLogoutModal = true"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-[#5A4B50] hover:text-[#7A1228] hover:bg-[#FDF1F3] transition-colors"
                >
                    <LogOut class="w-5 h-5 text-[#8E7E84]" />
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Mobile Drawer Overlay -->
        <div
            v-if="showMobileMenu"
            @click="showMobileMenu = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 md:hidden transition-opacity"
        ></div>

        <!-- Mobile Drawer -->
        <aside
            v-if="showMobileMenu"
            class="fixed inset-y-0 left-0 w-72 bg-white z-50 flex flex-col md:hidden shadow-xl"
        >
            <div class="p-5 flex items-center justify-between border-b border-[#F0E6E8]">
                <div class="flex items-center gap-3">
                    <img src="/logo_liliebelle.png" alt="LilieFlow Logo" class="w-9 h-9 object-contain rounded-md" />
                    <div>
                        <div class="text-lg font-bold text-[#4A0D1B]">LilieFlow</div>
                        <div class="text-[10px] text-[#9E8B90]">Operational workspace</div>
                    </div>
                </div>
                <button @click="showMobileMenu = false" class="p-1 rounded-lg text-gray-500 hover:bg-gray-100">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile User Card -->
            <div class="p-4 border-b border-[#F0E6E8]">
                <div class="p-3 rounded-xl bg-[#FBF7F8] border border-[#F2E5E8] flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#F6E5E8] text-[#7A1228] font-bold text-sm flex items-center justify-center shrink-0">
                        {{ authUser?.initials || 'SR' }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-[#2A0711]">
                            {{ authUser?.name || 'Siti Rahma' }}
                        </div>
                        <div class="text-xs text-[#9E8B90]">
                            {{ authUser?.role_label || 'Admin Konveksi' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Nav Items -->
            <div class="p-4 flex-1 overflow-y-auto space-y-2">
                <div class="px-2 mb-2 text-[11px] font-bold tracking-wider text-[#9E8B90] uppercase">
                    RUANG KERJA
                </div>
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.route"
                    @click="showMobileMenu = false"
                    :class="[
                        'flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium',
                        item.activeRule($page.url)
                            ? 'bg-[#FDF1F3] text-[#7A1228] font-bold'
                            : 'text-[#5A4B50] hover:bg-[#FBF4F6]'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <component :is="item.icon" class="w-5 h-5" />
                        <span>{{ item.name }}</span>
                    </div>
                    <span
                        v-if="item.badge && item.badge > 0"
                        class="w-5 h-5 text-[11px] font-bold text-white bg-[#7A1228] rounded-full flex items-center justify-center"
                    >
                        {{ item.badge }}
                    </span>
                </Link>
            </div>

            <div class="p-4 border-t border-[#F0E6E8] space-y-3">
                <div class="flex items-center gap-3 px-2 py-1">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" />
                    <div>
                        <div class="text-xs font-bold text-[#2A0711]">Data tersinkron</div>
                        <div class="text-[11px] text-[#9E8B90]">Diperbarui baru saja</div>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-[#7A1228] hover:bg-[#FDF1F3]"
                >
                    <LogOut class="w-5 h-5" />
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Navbar (LilieFlow > Dashboard + Right Icons) -->
            <header class="h-16 bg-white border-b border-[#F0E6E8] px-6 sm:px-8 flex items-center justify-between sticky top-0 z-20">
                <!-- Left: Mobile Toggle & Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="showMobileMenu = true"
                        class="md:hidden p-2 rounded-xl text-[#5A4B50] hover:bg-[#FDF1F3]"
                    >
                        <Menu class="w-6 h-6" />
                    </button>

                    <!-- Breadcrumbs matching screenshot -->
                    <div class="flex items-center gap-2 text-xs text-[#8E7E84]">
                        <span class="text-[#8E7E84] font-medium">LilieFlow</span>
                        <ChevronRight class="w-3.5 h-3.5 text-[#C4B6B9]" />
                        <span class="text-[#2A0711] font-bold font-heading">
                            {{ title }}
                        </span>
                    </div>
                </div>

                <!-- Right: Bell Notification & User Avatar -->
                <div class="flex items-center gap-3">
                    <!-- Notification Bell Button -->
                    <Link
                        href="/konveksi/notifikasi"
                        class="relative w-10 h-10 rounded-full border border-[#E9DFE2] bg-white flex items-center justify-center text-[#5A4B50] hover:bg-[#FDF1F3] hover:text-[#7A1228] transition-colors"
                        title="Notifikasi"
                    >
                        <Bell class="w-4 h-4" />
                        <span
                            v-if="unreadNotificationCount > 0"
                            class="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#7A1228]"
                        ></span>
                    </Link>

                    <!-- User Circle Avatar -->
                    <Link
                        href="/konveksi/profil"
                        class="w-10 h-10 rounded-full bg-[#F6E5E8] text-[#7A1228] font-bold text-xs flex items-center justify-center hover:ring-2 hover:ring-[#7A1228]/20 transition-all border border-[#F2D7DD]"
                        title="Profil Saya"
                    >
                        {{ authUser?.initials || 'SR' }}
                    </Link>
                </div>
            </header>

            <!-- Page Body Slot -->
            <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>

        <!-- Floating Toast Popup -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="activeToast"
                class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white rounded-2xl shadow-xl border border-gray-100 p-4 flex items-start gap-3.5"
            >
                <div
                    :class="[
                        'w-9 h-9 rounded-xl flex items-center justify-center shrink-0',
                        activeToast.type === 'success' ? 'bg-emerald-50 text-emerald-600' :
                        activeToast.type === 'error' ? 'bg-rose-50 text-rose-600' : 'bg-sky-50 text-sky-600'
                    ]"
                >
                    <CheckCircle2 v-if="activeToast.type === 'success'" class="w-5 h-5" />
                    <AlertCircle v-else class="w-5 h-5" />
                </div>
                <div class="flex-1 pt-0.5">
                    <p class="text-xs font-bold text-gray-900 capitalize">
                        {{ activeToast.type === 'success' ? 'Berhasil' : activeToast.type === 'error' ? 'Pemberitahuan' : 'Informasi' }}
                    </p>
                    <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                        {{ activeToast.message }}
                    </p>
                </div>
                <button
                    @click="dismissToast"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </transition>

        <!-- Logout Confirmation Modal -->
        <div
            v-if="showLogoutModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-[#F0E6E8] text-center">
                <div class="w-12 h-12 rounded-full bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center mx-auto mb-4">
                    <LogOut class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-[#2A0711] mb-2">Konfirmasi Keluar</h3>
                <p class="text-sm text-[#736368] mb-6">
                    Apakah Anda yakin ingin keluar dari akun Admin Konveksi?
                </p>
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="showLogoutModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleLogout"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-[#7A1228] text-white text-sm font-semibold hover:bg-[#600D1E] transition-colors"
                    >
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>


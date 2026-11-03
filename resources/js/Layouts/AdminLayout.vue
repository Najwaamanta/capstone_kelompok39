<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    Bell,
    History,
    User,
    LogOut,
    ChevronRight,
    X,
    CheckCircle2,
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
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const pendingCount = computed(() => page.props.pendingActivationCount || 0);

// Floating Toast Notification System (Pop-up yang otomatis hilang dalam ~3.5 detik)
const activeToast = ref(null);
let toastTimer = null;

const triggerToast = (type, message) => {
    if (!message) return;
    if (toastTimer) clearTimeout(toastTimer);
    activeToast.value = { type, message };
    // Otomatis hilang setelah 3.5 detik
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

const navItems = [
    {
        name: 'Dashboard',
        route: '/admin/dashboard',
        icon: LayoutGrid,
        activeRule: (url) => url === '/admin/dashboard' || url === '/admin',
    },
    {
        name: 'Kelola Pengguna',
        route: '/admin/users',
        icon: Users,
        activeRule: (url) => url.startsWith('/admin/users'),
    },
    {
        name: 'Notifikasi',
        route: '/admin/notifications',
        icon: Bell,
        badge: pendingCount,
        activeRule: (url) => url.startsWith('/admin/notifications'),
    },
    {
        name: 'Log Aktivitas',
        route: '/admin/activity-logs',
        icon: History,
        activeRule: (url) => url.startsWith('/admin/activity-logs'),
    },
    {
        name: 'Profil',
        route: '/admin/profile',
        icon: User,
        activeRule: (url) => url.startsWith('/admin/profile'),
    },
];

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[#FBF9F9] flex">
        <!-- Sidebar Desktop -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-[#F0E6E8] shrink-0 sticky top-0 h-screen z-30">
            <!-- Brand Logo -->
            <div class="p-6 pb-8 border-b border-[#F7EBEF]/60">
                <Link href="/admin/dashboard" class="flex items-center gap-3 group">
                    <img
                        src="/logo_liliebelle.png"
                        alt="LilieFlow Logo"
                        class="w-10 h-10 object-contain rounded-lg shadow-sm border border-[#F5D5DC]/60"
                        onerror="this.onerror=null; this.src='/images/logo_liliebelle.png';"
                    />
                    <div>
                        <div class="text-xl font-bold font-heading tracking-tight text-[#4A0D1B] group-hover:text-[#7A1228] transition-colors">
                            LilieFlow
                        </div>
                        <div class="text-[11px] font-medium text-[#9E8B90] tracking-wide">
                            Operational workspace
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 px-4 py-6 overflow-y-auto space-y-6">
                <div>
                    <div class="px-3 mb-3 text-[11px] font-bold tracking-wider text-[#9E8B90] uppercase">
                        SISTEM ADMINISTRATOR
                    </div>
                    <nav class="space-y-1.5">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.route"
                            :class="[
                                'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
                                item.activeRule($page.url)
                                    ? 'bg-[#FDF1F3] text-[#7A1228] font-semibold shadow-xs'
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

                            <!-- Badge -->
                            <span
                                v-if="item.badge && item.badge > 0"
                                class="inline-flex items-center justify-center w-5 h-5 text-[11px] font-bold text-white bg-[#7A1228] rounded-full shadow-xs"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- Sidebar Bottom: Logout -->
            <div class="p-4 border-t border-[#F0E6E8]">
                <button
                    type="button"
                    @click="showLogoutModal = true"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#7A1228] hover:bg-[#FDF1F3] transition-colors"
                >
                    <LogOut class="w-5 h-5 text-[#7A1228]" />
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
                        <div class="text-lg font-bold font-heading text-[#4A0D1B]">LilieFlow</div>
                        <div class="text-[10px] text-[#9E8B90]">Operational workspace</div>
                    </div>
                </div>
                <button @click="showMobileMenu = false" class="p-1 rounded-lg text-gray-500 hover:bg-gray-100">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <div class="p-4 flex-1 overflow-y-auto space-y-2">
                <div class="px-2 mb-2 text-[11px] font-bold tracking-wider text-[#9E8B90] uppercase">
                    SISTEM ADMINISTRATOR
                </div>
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.route"
                    @click="showMobileMenu = false"
                    :class="[
                        'flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium',
                        item.activeRule($page.url)
                            ? 'bg-[#FDF1F3] text-[#7A1228] font-semibold'
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

            <div class="p-4 border-t border-[#F0E6E8]">
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#7A1228] hover:bg-[#FDF1F3]"
                >
                    <LogOut class="w-5 h-5" />
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar -->
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-[#F0E6E8] px-6 py-4 flex items-center justify-between">
                <!-- Left: Mobile Menu Toggle & Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <button
                        @click="showMobileMenu = true"
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Breadcrumbs matching screenshot -->
                    <nav class="flex items-center text-sm font-medium text-[#7E6E74]">
                        <Link href="/admin/dashboard" class="hover:text-[#7A1228] transition-colors">
                            LilieFlow
                        </Link>
                        <span class="mx-2 text-[#C0B2B6]">
                            <ChevronRight class="w-4 h-4 inline" />
                        </span>
                        <span class="text-[#3D2C32] font-semibold">
                            {{ title }}
                        </span>
                    </nav>
                </div>

                <!-- Right: Notification Bell + User Info + Avatar -->
                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    <Link
                        href="/admin/notifications"
                        class="relative p-2 rounded-full text-[#7E6E74] hover:text-[#7A1228] hover:bg-[#FDF1F3] transition-colors border border-[#F0E6E8]"
                        title="Notifikasi"
                    >
                        <Bell class="w-5 h-5" />
                        <span
                            v-if="pendingCount > 0"
                            class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#7A1228] rounded-full ring-2 ring-white"
                        ></span>
                    </Link>

                    <!-- User Name + Role (hidden on small screens) -->
                    <Link href="/admin/profile" class="hidden sm:flex flex-col items-end leading-none gap-0.5 cursor-pointer group" :title="authUser?.name">
                        <span class="text-sm font-bold text-[#2A0711] group-hover:text-[#7A1228] transition-colors truncate max-w-[140px]">
                            {{ authUser?.name || 'Administrator' }}
                        </span>
                        <span class="text-[11px] text-[#8C7A81] truncate max-w-[140px]">
                            {{ authUser?.role_label || 'Administrator' }}
                        </span>
                    </Link>

                    <!-- User Initials Avatar -->
                    <Link
                        href="/admin/profile"
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-[#FCE7EB] text-[#7A1228] font-bold text-sm tracking-wide border border-[#F5D5DC] hover:ring-2 hover:ring-[#7A1228]/20 transition-all cursor-pointer shadow-xs"
                        :title="authUser?.name"
                    >
                        {{ authUser?.initials || 'DS' }}
                    </Link>
                </div>
            </header>

            <!-- Floating Pop-up Toast Notification (Muncul sebentar lalu hilang otomatis) -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transform ease-out duration-300 transition"
                    enter-from-class="translate-y-[-16px] opacity-0 sm:translate-y-0 sm:translate-x-4 scale-95"
                    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0 scale-100"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="translate-y-[-10px] opacity-0 scale-95"
                >
                    <div
                        v-if="activeToast"
                        class="fixed top-5 right-5 z-50 max-w-sm w-full pointer-events-auto"
                    >
                        <div
                            class="p-4 rounded-2xl bg-white shadow-2xl border flex items-start gap-3.5 relative overflow-hidden"
                            :class="[
                                activeToast.type === 'success' ? 'border-emerald-200 shadow-emerald-600/10' : '',
                                activeToast.type === 'error' ? 'border-rose-200 shadow-rose-600/10' : '',
                                activeToast.type === 'info' ? 'border-blue-200 shadow-blue-600/10' : '',
                            ]"
                        >
                            <!-- Icon Circle -->
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
                                :class="[
                                    activeToast.type === 'success' ? 'bg-emerald-50 text-emerald-600' : '',
                                    activeToast.type === 'error' ? 'bg-rose-50 text-rose-600' : '',
                                    activeToast.type === 'info' ? 'bg-blue-50 text-blue-600' : '',
                                ]"
                            >
                                <CheckCircle2 v-if="activeToast.type === 'success'" class="w-5 h-5" />
                                <AlertCircle v-else class="w-5 h-5" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 pr-2 pt-0.5">
                                <h4 class="text-sm font-bold leading-none mb-1 text-[#2A0711]">
                                    {{ activeToast.type === 'success' ? 'Berhasil' : (activeToast.type === 'error' ? 'Peringatan' : 'Informasi') }}
                                </h4>
                                <p class="text-xs text-[#5A454C] leading-relaxed">
                                    {{ activeToast.message }}
                                </p>
                            </div>

                            <!-- Close Button -->
                            <button
                                type="button"
                                @click="dismissToast"
                                class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer shrink-0"
                                title="Tutup"
                            >
                                <X class="w-4 h-4" />
                            </button>

                            <!-- Progress timer line -->
                            <div
                                class="absolute bottom-0 left-0 h-1 bg-gradient-to-r toast-timer-bar"
                                :class="[
                                    activeToast.type === 'success' ? 'from-emerald-400 to-emerald-600' : '',
                                    activeToast.type === 'error' ? 'from-rose-400 to-rose-600' : '',
                                    activeToast.type === 'info' ? 'from-blue-400 to-blue-600' : '',
                                ]"
                            ></div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <!-- Page Content Body -->
            <main class="flex-1 p-6 md:p-8 max-w-7xl w-full">
                <slot />
            </main>
        </div>

        <!-- Logout Confirmation Modal -->
        <div
            v-if="showLogoutModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4"
        >
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
                <div class="w-12 h-12 rounded-full bg-[#FDF1F3] text-[#7A1228] flex items-center justify-center mb-4">
                    <LogOut class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-[#3D101A] mb-2">Konfirmasi Keluar</h3>
                <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                    Apakah Anda yakin ingin keluar dari sistem LilieFlow? Anda perlu memasukkan kredensial akun untuk masuk kembali.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showLogoutModal = false"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="handleLogout"
                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-white bg-[#7A1228] hover:bg-[#600d1e] transition-colors shadow-sm"
                    >
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

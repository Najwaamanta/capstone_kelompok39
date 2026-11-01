<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ArrowLeft, Clock } from 'lucide-vue-next';

defineProps({
    email: { type: String, required: true },
});
</script>

<template>
    <Head title="Permintaan Terkirim - LilieFlow" />

    <div class="min-h-screen flex flex-col md:flex-row bg-[#FBF9F9]">
        <!-- Left Side: Brand Visual -->
        <div class="concentric-bg hidden md:flex md:w-5/12 lg:w-9/20 flex-col justify-between p-12 lg:p-16 text-white relative">
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md p-1.5 flex items-center justify-center border border-white/20 shadow-inner">
                    <img
                        src="/logo_liliebelle.png"
                        alt="LilieFlow Logo"
                        class="w-full h-full object-contain rounded-lg"
                        onerror="this.onerror=null; this.src='/images/logo_liliebelle.png';"
                    />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white leading-none">LilieFlow</h1>
                    <span class="text-xs text-white/70 font-medium tracking-wide">Operational workspace</span>
                </div>
            </div>

            <div class="relative z-10 my-auto py-12">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-[1.18] max-w-md">
                    Permintaan Anda sedang dalam proses.
                </h2>
                <p class="text-white/60 text-base mt-4 max-w-xs leading-relaxed">
                    Administrator akan segera merespons dan memberikan password baru kepada Anda.
                </p>
            </div>

            <div class="relative z-10 text-xs text-white/40 tracking-widest font-medium uppercase">
                PT LILIE BELLE
            </div>
        </div>

        <!-- Right Side: Success state -->
        <div class="flex-1 flex items-center justify-center p-6 md:p-12">
            <div class="w-full max-w-md text-center">

                <!-- Success Icon -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center shadow-lg">
                            <CheckCircle2 class="w-10 h-10 text-emerald-600" />
                        </div>
                        <div class="absolute -inset-1 rounded-full border-2 border-emerald-200 animate-ping opacity-30"></div>
                    </div>
                </div>

                <!-- Text -->
                <p class="text-xs font-bold tracking-widest text-emerald-600 uppercase mb-2">PERMINTAAN TERKIRIM</p>
                <h2 class="text-2xl font-extrabold text-[#2A0711] tracking-tight mb-3">
                    Permintaan Berhasil Dikirim!
                </h2>
                <p class="text-sm text-[#6C555C] leading-relaxed mb-6 max-w-sm mx-auto">
                    Kami telah menerima permintaan reset password untuk akun
                    <strong class="text-[#2A0711]">{{ email }}</strong>.
                </p>

                <!-- Step cards -->
                <div class="space-y-3 text-left mb-8">
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white border border-[#F0E6E8] shadow-xs">
                        <div class="w-7 h-7 rounded-full bg-[#7A1228] text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</div>
                        <div>
                            <p class="text-xs font-bold text-[#2A0711]">Permintaan diterima</p>
                            <p class="text-xs text-[#8C7A81] mt-0.5">Administrator akan melihat notifikasi permintaan Anda di panel admin.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white border border-[#F0E6E8] shadow-xs">
                        <div class="w-7 h-7 rounded-full bg-[#F0E6E8] text-[#7A1228] flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</div>
                        <div>
                            <p class="text-xs font-bold text-[#2A0711]">Administrator memproses</p>
                            <p class="text-xs text-[#8C7A81] mt-0.5">Administrator akan mereset password dan menghubungi Anda secara langsung.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white border border-[#F0E6E8] shadow-xs">
                        <div class="w-7 h-7 rounded-full bg-[#F0E6E8] text-[#7A1228] flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</div>
                        <div>
                            <p class="text-xs font-bold text-[#2A0711]">Masuk dengan password baru</p>
                            <p class="text-xs text-[#8C7A81] mt-0.5">Gunakan password baru yang diberikan Administrator untuk masuk ke sistem.</p>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="flex items-center gap-2 justify-center text-xs text-[#8C7A81] mb-8">
                    <Clock class="w-3.5 h-3.5" />
                    <span>Proses biasanya diselesaikan dalam waktu kerja</span>
                </div>

                <!-- Back to login -->
                <Link
                    href="/login"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#7A1228] hover:bg-[#600d1e] text-white text-sm font-bold tracking-wide shadow-md transition-all"
                >
                    <ArrowLeft class="w-4 h-4" />
                    Kembali ke Halaman Login
                </Link>

                <p class="text-xs text-[#9E8B90] mt-4">
                    Butuh bantuan segera?
                    <Link href="/login" class="text-[#7A1228] font-semibold hover:underline">Hubungi Administrator</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.concentric-bg {
    background: radial-gradient(ellipse at 70% 40%, #9a1535 0%, #7A1228 40%, #4A0D1B 100%);
}
.concentric-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-radial-gradient(
        circle at 70% 50%,
        transparent 10%,
        rgba(255,255,255,0.03) 11%,
        transparent 12%
    );
}
</style>

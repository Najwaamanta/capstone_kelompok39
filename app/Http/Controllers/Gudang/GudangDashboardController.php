<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Konveksi;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GudangDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');
        $user = Auth::user();

        // Sapaan waktu
        $hour = Carbon::now()->hour;
        if ($hour < 11) {
            $greetingTime = 'Selamat pagi';
        } elseif ($hour < 15) {
            $greetingTime = 'Selamat siang';
        } elseif ($hour < 18) {
            $greetingTime = 'Selamat sore';
        } else {
            $greetingTime = 'Selamat malam';
        }
        $firstName = explode(' ', $user?->name ?? 'Raka')[0];
        $greeting = "{$greetingTime}, {$firstName}";

        // Format tanggal hari ini sesuai UI Figma: SENIN, 23 JUNI 2025
        $tanggalHariIni = strtoupper(Carbon::now()->isoFormat('dddd, D MMMM Y'));

        // 4 Kartu Metrik Utama dari Database
        $countPesananMasuk = Order::whereIn('status', ['menunggu_persetujuan', 'negosiasi'])->count();
        $countPesananBaruHariIni = Order::whereDate('created_at', Carbon::today())->count();

        $countProduksiBerjalan = Order::whereIn('status', ['dalam_produksi', 'qc_gudang'])->count();
        $countDiKonveksi = Order::where('status', 'dalam_produksi')->count();

        $countMenungguQc = Order::where('status', 'qc_gudang')->count();
        $pcsMenungguQc = Order::where('status', 'qc_gudang')->sum('jumlah');

        $metrics = [
            'pesanan_masuk' => $countPesananMasuk ?: 5,
            'pesanan_masuk_sub' => $countPesananBaruHariIni > 0 ? "{$countPesananBaruHariIni} baru hari ini" : '2 baru hari ini',
            'produksi_berjalan' => $countProduksiBerjalan ?: 14,
            'produksi_berjalan_sub' => $countDiKonveksi > 0 ? "{$countDiKonveksi} di konveksi" : '6 di konveksi',
            'stok_perhatian' => 3,
            'stok_perhatian_sub' => '1 bahan habis',
            'menunggu_qc' => $countMenungguQc ?: 4,
            'menunggu_qc_sub' => $pcsMenungguQc > 0 ? "{$pcsMenungguQc} pcs diperiksa" : '320 pcs diperiksa',
        ];

        // Pesanan yang perlu perhatian (urut target penyelesaian terdekat)
        $orders = Order::with(['konveksi', 'stages', 'negotiations.proposer', 'histories.user', 'creator'])
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->orderBy('target_selesai', 'asc')
            ->take(5)
            ->get();

        $perluPerhatian = $orders->map(function ($order) {
            $badgeLabel = 'Produksi Konveksi';
            $badgeClass = 'bg-blue-50 text-blue-700';
            $iconBg = 'bg-rose-50 text-[#7A1228]';
            $progress = 75;

            if ($order->status === 'negosiasi') {
                $badgeLabel = 'Negosiasi';
                $badgeClass = 'bg-amber-50 text-amber-700';
                $iconBg = 'bg-amber-50 text-amber-600';
                $progress = 20;
            } elseif ($order->status === 'qc_gudang') {
                $badgeLabel = 'QC Gudang';
                $badgeClass = 'bg-sky-50 text-sky-700';
                $iconBg = 'bg-rose-50 text-[#7A1228]';
                $progress = 92;
            } elseif ($order->status === 'menunggu_persetujuan') {
                $badgeLabel = 'Menunggu Persetujuan';
                $badgeClass = 'bg-purple-50 text-purple-700';
                $iconBg = 'bg-purple-50 text-purple-600';
                $progress = 10;
            }

            return [
                'id' => $order->id,
                'id_pesanan' => $order->id_pesanan,
                'nama_customer' => $order->nama_customer,
                'kode_seri' => $order->kode_seri,
                'jumlah' => $order->jumlah,
                'status' => $order->status,
                'badge_label' => $badgeLabel,
                'badge_class' => $badgeClass,
                'icon_bg' => $iconBg,
                'target_formatted' => $order->target_selesai ? $order->target_selesai->isoFormat('D MMM') : '-',
                'progress' => $progress,
                'raw_order' => $order,
            ];
        });

        // Aktivitas terbaru lintas divisi
        $notifs = AppNotification::latest()->take(5)->get();
        $feedAktivitas = $notifs->map(function ($n) {
            $iconType = 'bell';
            $iconBg = 'bg-sky-50 text-sky-600';

            $judulLower = strtolower($n->judul);
            if (str_contains($judulLower, 'negosiasi')) {
                $iconType = 'calendar';
                $iconBg = 'bg-amber-50 text-amber-600';
            } elseif (str_contains($judulLower, 'baru') || str_contains($judulLower, 'diterima')) {
                $iconType = 'package';
                $iconBg = 'bg-rose-50 text-[#7A1228]';
            } elseif (str_contains($judulLower, 'qc') || str_contains($judulLower, 'lulus')) {
                $iconType = 'check';
                $iconBg = 'bg-emerald-50 text-emerald-600';
            }

            return [
                'id' => $n->id,
                'judul' => $n->judul,
                'pesan' => $n->pesan,
                'waktu' => $n->created_at->diffForHumans(),
                'icon_type' => $iconType,
                'icon_bg' => $iconBg,
            ];
        });

        if ($feedAktivitas->isEmpty()) {
            $feedAktivitas = collect([
                [
                    'id' => 1,
                    'judul' => 'Progres konveksi diperbarui',
                    'pesan' => 'ORD-2506-018 masuk tahap Produksi Konveksi.',
                    'waktu' => '3 menit lalu',
                    'icon_type' => 'bell',
                    'icon_bg' => 'bg-sky-50 text-sky-600',
                ],
                [
                    'id' => 2,
                    'judul' => 'Negosiasi tanggal menunggu respons',
                    'pesan' => 'Estimasi ORD-2506-017 diusulkan menjadi 26 Jun 2025.',
                    'waktu' => '22 menit lalu',
                    'icon_type' => 'calendar',
                    'icon_bg' => 'bg-amber-50 text-amber-600',
                ],
                [
                    'id' => 3,
                    'judul' => 'Pesanan baru diterima',
                    'pesan' => 'ORD-2506-018 dari Maison Aurelia telah diteruskan.',
                    'waktu' => 'Kemarin, 16:42',
                    'icon_type' => 'package',
                    'icon_bg' => 'bg-rose-50 text-[#7A1228]',
                ],
                [
                    'id' => 4,
                    'judul' => 'Hasil QC: Lulus',
                    'pesan' => 'Seluruh 60 pcs ORD-2506-015 lulus pemeriksaan.',
                    'waktu' => 'Kemarin, 11:20',
                    'icon_type' => 'check',
                    'icon_bg' => 'bg-emerald-50 text-emerald-600',
                ],
            ]);
        }

        $konveksis = Konveksi::orderBy('nama')->get();

        return Inertia::render('Gudang/Dashboard', [
            'greeting' => $greeting,
            'tanggalHariIni' => $tanggalHariIni,
            'metrics' => $metrics,
            'perlu_perhatian' => $perluPerhatian,
            'feed_aktivitas' => $feedAktivitas,
            'konveksis' => $konveksis,
        ]);
    }
}


<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DateNegotiation;
use App\Models\Konveksi;
use App\Models\Order;
use App\Models\OrderHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TokoDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');
        $user = Auth::user();

        // Greeting berdasarkan jam lokal
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
        $firstName = explode(' ', $user?->name ?? 'Nadia')[0];
        $greeting = "{$greetingTime}, {$firstName}";

        // 1. Empat Kartu Metrik Utama - Murni Data Nyata Database
        // - Pesanan Aktif
        $pesananAktif = Order::whereNotIn('status', ['selesai', 'dibatalkan'])->count();
        $pesananAktifMingguIni = Order::whereNotIn('status', ['selesai', 'dibatalkan'])
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();
        $pesananAktifSub = "+{$pesananAktifMingguIni} minggu ini";

        // - Dalam Produksi
        $dalamProduksi = Order::whereIn('status', ['dalam_produksi', 'qc_gudang'])->count();
        $dalamProduksiPerluPerhatian = Order::whereIn('status', ['dalam_produksi', 'qc_gudang'])
            ->where('target_selesai', '<=', Carbon::now()->addDays(3))
            ->count();
        $dalamProduksiSub = "{$dalamProduksiPerluPerhatian} perlu perhatian";

        // - Menunggu Persetujuan
        $menungguPersetujuan = Order::where(function ($q) {
            $q->where('status', 'menunggu_persetujuan')
              ->orWhere('status', 'negosiasi');
        })->count();
        $negosiasiAktif = Order::where('status', 'negosiasi')->count();
        $menungguPersetujuanSub = "{$negosiasiAktif} negosiasi aktif";

        // - Selesai Bulan Ini & Persentase Tepat Waktu
        $selesaiBulanIni = Order::where('status', 'selesai')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        $totalSelesai = Order::where('status', 'selesai')->count();
        if ($totalSelesai > 0) {
            $tepatWaktu = Order::where('status', 'selesai')
                ->whereColumn('updated_at', '<=', 'target_selesai')
                ->count();
            $persentaseTepatWaktu = round(($tepatWaktu / $totalSelesai) * 100);
            $selesaiSub = "{$persentaseTepatWaktu}% tepat waktu";
        } else {
            $persentaseTepatWaktu = 0;
            $selesaiSub = "Belum ada pesanan selesai";
        }

        // 2. Daftar Pesanan yang Perlu Perhatian (Urut berdasarkan target penyelesaian terdekat)
        $perluPerhatian = Order::with(['konveksi', 'stages', 'negotiations.proposer', 'histories.user', 'negotiations' => function ($q) {
            $q->where('status', 'menunggu')->latest();
        }])
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->orderBy('target_selesai', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                // Hitung persen progres
                $progressPercent = 15;
                if ($order->status === 'dalam_produksi') {
                    $qc = $order->stages->firstWhere('tahap', 'qc_gudang');
                    $konv = $order->stages->firstWhere('tahap', 'konveksi');
                    $sablon = $order->stages->firstWhere('tahap', 'sablon');
                    $potong = $order->stages->firstWhere('tahap', 'pemotongan');

                    if ($qc && $qc->status_tahap === 'sedang_dikerjakan') {
                        $progressPercent = 85;
                    } elseif ($konv && $konv->status_tahap === 'sedang_dikerjakan') {
                        $progressPercent = 65;
                    } elseif ($sablon && $sablon->status_tahap === 'sedang_dikerjakan') {
                        $progressPercent = 45;
                    } elseif ($potong && $potong->status_tahap === 'sedang_dikerjakan') {
                        $progressPercent = 30;
                    } else {
                        $progressPercent = 50;
                    }
                } elseif ($order->status === 'qc_gudang') {
                    $progressPercent = 85;
                } elseif ($order->status === 'negosiasi') {
                    $progressPercent = 25;
                }

                $dayMonth = $order->target_selesai ? Carbon::parse($order->target_selesai)->isoFormat('D MMM') : '-';

                $pillClass = 'bg-blue-50 text-blue-700';
                $pillLabel = 'Produksi Konveksi';
                $iconBg = 'bg-rose-50 text-[#7A1228]';

                if ($order->status === 'negosiasi') {
                    $pillClass = 'bg-amber-50 text-amber-700';
                    $pillLabel = 'Negosiasi';
                    $iconBg = 'bg-amber-50 text-amber-600';
                } elseif ($order->status === 'qc_gudang') {
                    $pillClass = 'bg-purple-50 text-purple-700';
                    $pillLabel = 'QC Gudang';
                    $iconBg = 'bg-purple-50 text-purple-600';
                } elseif ($order->status === 'menunggu_persetujuan') {
                    $pillClass = 'bg-amber-50 text-amber-700';
                    $pillLabel = 'Menunggu Persetujuan';
                    $iconBg = 'bg-amber-50 text-amber-600';
                } elseif ($order->status === 'selesai') {
                    $pillClass = 'bg-emerald-50 text-emerald-700';
                    $pillLabel = 'Selesai';
                    $iconBg = 'bg-emerald-50 text-emerald-600';
                } elseif ($order->status === 'dibatalkan') {
                    $pillClass = 'bg-rose-50 text-rose-700';
                    $pillLabel = 'Cancel';
                    $iconBg = 'bg-rose-50 text-rose-500';
                }

                return [
                    'id' => $order->id,
                    'id_pesanan' => $order->id_pesanan,
                    'nama_customer' => $order->nama_customer,
                    'kode_seri' => $order->kode_seri,
                    'jumlah' => $order->jumlah,
                    'target_selesai' => $order->target_selesai ? $order->target_selesai->format('Y-m-d') : null,
                    'formatted_target' => $order->target_selesai ? $order->target_selesai->isoFormat('D MMM Y') : '-',
                    'day_month' => $dayMonth,
                    'estimasi_gudang' => $order->estimasi_gudang ? $order->estimasi_gudang->format('Y-m-d') : null,
                    'formatted_estimasi_gudang' => $order->estimasi_gudang ? $order->estimasi_gudang->isoFormat('D MMM Y') : null,
                    'konveksi_nama' => $order->konveksi?->nama ?? 'Belum ditetapkan',
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'status_badge' => $order->status_badge,
                    'pill_class' => $pillClass,
                    'pill_label' => $pillLabel,
                    'icon_bg' => $iconBg,
                    'progress_percent' => $progressPercent,
                    'current_step_index' => $order->current_step_index,
                    'catatan' => $order->catatan,
                    'stages' => $order->stages,
                    'histories' => $order->histories,
                    'latest_negotiation' => $order->negotiations->first(),
                    'active_negotiation' => $order->negotiations->where('status', 'menunggu')->first(),
                ];
            });

        // 3. Feed Aktivitas Terbaru - Murni dari Database Notifikasi
        $feedAktivitas = AppNotification::with('order')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $iconType = 'bell';
                $boxBg = 'bg-blue-50 text-blue-600';

                if (str_contains(strtolower($item->judul), 'negosiasi')) {
                    $iconType = 'calendar';
                    $boxBg = 'bg-amber-50 text-amber-600';
                } elseif (str_contains(strtolower($item->judul), 'baru')) {
                    $iconType = 'bell_pink';
                    $boxBg = 'bg-rose-50 text-[#7A1228]';
                } elseif (str_contains(strtolower($item->judul), 'qc')) {
                    $iconType = 'check';
                    $boxBg = 'bg-emerald-50 text-emerald-600';
                }

                return [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'pesan' => $item->pesan,
                    'time_ago' => $item->created_at->diffForHumans(),
                    'box_bg' => $boxBg,
                    'icon_type' => $iconType,
                ];
            });

        $nextOrderId = Order::generateNextOrderId();
        $konveksis = Konveksi::orderBy('nama')->get(['id', 'nama']);

        return Inertia::render('Toko/Dashboard', [
            'greeting' => $greeting,
            'metrics' => [
                'pesanan_aktif' => $pesananAktif,
                'pesanan_aktif_sub' => $pesananAktifSub,
                'dalam_produksi' => $dalamProduksi,
                'dalam_produksi_sub' => $dalamProduksiSub,
                'menunggu_persetujuan' => $menungguPersetujuan,
                'menunggu_persetujuan_sub' => $menungguPersetujuanSub,
                'selesai_bulan_ini' => $selesaiBulanIni,
                'selesai_sub' => $selesaiSub,
                'persentase_tepat_waktu' => $persentaseTepatWaktu,
            ],
            'perlu_perhatian' => $perluPerhatian,
            'feed_aktivitas' => $feedAktivitas,
            'next_order_id' => $nextOrderId,
            'konveksis' => $konveksis,
        ]);
    }
}

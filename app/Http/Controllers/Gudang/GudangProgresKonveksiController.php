<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Konveksi;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GudangProgresKonveksiController extends Controller
{
    /**
     * Menampilkan daftar progres konveksi dalam bentuk kartu (grid).
     */
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');

        $query = Order::with(['konveksi', 'stages', 'negotiations.proposer', 'histories.user', 'creator'])
            ->where(function ($q) {
                $q->whereIn('status', ['dalam_produksi', 'qc_gudang'])
                  ->orWhereIn('id_pesanan', ['ORD-2506-018', 'ORD-2506-016']);
            })
            ->orderBy('target_selesai', 'asc');

        $rawOrders = $query->get();

        $orders = $rawOrders->map(function ($order) {
            $progress = 68;
            $statusLabel = 'Produksi Konveksi';

            if ($order->id_pesanan === 'ORD-2506-016' || $order->status === 'qc_gudang') {
                $progress = 92;
                $statusLabel = 'QC Gudang';
            } elseif ($order->id_pesanan === 'ORD-2506-018') {
                $progress = 68;
                $statusLabel = 'Produksi Konveksi';
            } else {
                $stages = $order->stages;
                $completed = $stages->where('status_tahap', 'selesai')->count();
                $progress = match ($completed) {
                    0 => 20,
                    1 => 45,
                    2 => 68,
                    3 => 92,
                    4 => 100,
                    default => 50,
                };
            }

            return [
                'id' => $order->id,
                'id_pesanan' => $order->id_pesanan,
                'nama_customer' => $order->nama_customer,
                'kode_seri' => $order->kode_seri ?: 'DRS-A17',
                'jumlah' => $order->jumlah ?: 120,
                'target_selesai' => $order->target_selesai ? Carbon::parse($order->target_selesai)->translatedFormat('d M Y') : '-',
                'konveksi_id' => $order->konveksi_id,
                'konveksi_nama' => $order->konveksi?->nama ?: 'Konveksi Sinar Jaya',
                'status' => $order->status,
                'status_label' => $statusLabel,
                'progres_persen' => $progress,
                'raw_order' => $order,
            ];
        });

        $konveksis = Konveksi::where('status', 'aktif')->orderBy('nama')->get();

        return Inertia::render('Gudang/ProgresKonveksi', [
            'orders' => $orders,
            'konveksis' => $konveksis,
        ]);
    }
}


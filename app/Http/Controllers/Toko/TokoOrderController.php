<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Konveksi;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderStage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TokoOrderController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');

        $query = Order::with(['konveksi', 'stages', 'negotiations.proposer', 'histories.user'])
            ->latest();

        // Search ID Pesanan, Customer, Kode Seri
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%")
                  ->orWhere('kode_seri', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            if ($status !== 'semua') {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(10)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $order->formatted_target = $order->target_selesai ? $order->target_selesai->isoFormat('D MMM Y') : '-';
            $order->formatted_estimasi = $order->estimasi_gudang ? $order->estimasi_gudang->isoFormat('D MMM Y') : '-';
            $order->formatted_created_at = $order->created_at->isoFormat('D MMM Y, HH:mm');
            // Negosiasi aktif yang menunggu respons dari toko (diajukan Gudang)
            $order->active_negotiation = $order->negotiations
                ->where('status', 'menunggu')
                ->first();
            $order->formatted_estimasi_gudang = $order->estimasi_gudang ? $order->estimasi_gudang->isoFormat('D MMM Y') : null;

            // Format pill label & style matching Screenshot 2
            $pillClass = 'bg-blue-50 text-blue-700';
            $pillLabel = 'Produksi Konveksi';
            if ($order->status === 'negosiasi') {
                $pillClass = 'bg-amber-50 text-amber-700';
                $pillLabel = 'Negosiasi';
            } elseif ($order->status === 'qc_gudang') {
                $pillClass = 'bg-cyan-50 text-cyan-700';
                $pillLabel = 'QC Gudang';
            } elseif ($order->status === 'selesai') {
                $pillClass = 'bg-emerald-50 text-emerald-700';
                $pillLabel = 'Selesai';
            } elseif ($order->status === 'dibatalkan') {
                $pillClass = 'bg-rose-50 text-rose-600';
                $pillLabel = 'Cancel';
            } elseif ($order->status === 'menunggu_persetujuan') {
                $pillClass = 'bg-amber-50 text-amber-700';
                $pillLabel = 'Menunggu Persetujuan';
            }

            $order->pill_class = $pillClass;
            $order->pill_label = $pillLabel;
            return $order;
        });

        $nextOrderId = Order::generateNextOrderId();
        $konveksis = Konveksi::orderBy('nama')->get(['id', 'nama']);

        return Inertia::render('Toko/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'semua'),
            ],
            'next_order_id' => $nextOrderId,
            'konveksis' => $konveksis,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_customer' => ['required', 'string', 'max:255'],
            'kode_seri' => ['required', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'target_selesai' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_customer.required' => 'Nama Customer wajib diisi.',
            'kode_seri.required' => 'Kode Seri produk wajib diisi.',
            'jumlah.required' => 'Jumlah (pcs) wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa bilangan bulat.',
            'jumlah.min' => 'Jumlah minimal 1 pcs.',
            'target_selesai.required' => 'Target Penyelesaian wajib diisi.',
            'target_selesai.after_or_equal' => 'Target Penyelesaian tidak boleh tanggal lampau.',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Generate ID otomatis (ORD-YYMM-XXX)
            $idPesanan = Order::generateNextOrderId();

            $order = Order::create([
                'id_pesanan' => $idPesanan,
                'nama_customer' => $validated['nama_customer'],
                'kode_seri' => $validated['kode_seri'],
                'jumlah' => $validated['jumlah'],
                'target_selesai' => $validated['target_selesai'],
                'estimasi_gudang' => null,
                'konveksi_id' => null, // Tempat konveksi ditetapkan oleh Admin Gudang
                'status' => 'menunggu_persetujuan',
                'dibuat_oleh' => $user->id,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Buat default 4 tahapan produksi
            $stages = ['pemotongan', 'sablon', 'konveksi', 'qc_gudang'];
            foreach ($stages as $stageKey) {
                OrderStage::create([
                    'order_id' => $order->id,
                    'tahap' => $stageKey,
                    'status_tahap' => 'belum_dimulai',
                ]);
            }

            // Catat Riwayat
            OrderHistory::record(
                $order->id,
                $user->id,
                'admin_toko',
                'PESANAN_DIBUAT',
                "Pesanan {$idPesanan} dan nota digital dibuat oleh {$user->name}. Status awal: Menunggu Persetujuan Gudang."
            );

            // Notifikasi untuk Admin Toko (konfirmasi diterima)
            AppNotification::send(
                $user->id,
                'admin_toko',
                $order->id,
                'pesanan_baru',
                'Pesanan baru diterima',
                "Pesanan {$idPesanan} untuk {$order->nama_customer} telah tersimpan dan diteruskan ke Gudang."
            );

            // Notifikasi untuk Admin Gudang
            AppNotification::send(
                null,
                'admin_gudang',
                $order->id,
                'pesanan_baru',
                'Pesanan baru masuk',
                "Pesanan baru {$idPesanan} dari {$order->nama_customer} ({$order->jumlah} pcs) menunggu persetujuan dan penetapan konveksi."
            );

            DB::commit();

            return redirect()->back()->with('success', "Pesanan {$idPesanan} berhasil dibuat dan diteruskan ke Admin Gudang!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Order $order)
    {
        $user = Auth::user();

        // Hanya Admin Toko yang boleh membatalkan pesanan (US-T07 & NFR-04)
        if ($user->role !== 'admin_toko') {
            abort(403, 'Hanya Admin Toko yang memiliki wewenang untuk membatalkan pesanan.');
        }

        if (in_array($order->status, ['selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Pesanan yang sudah Selesai atau Dibatalkan tidak dapat diubah lagi.');
        }

        $validated = $request->validate([
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => 'dibatalkan',
            'dibatalkan_pada' => Carbon::now(),
            'alasan_pembatalan' => $validated['alasan'] ?? 'Dibatalkan atas permintaan Customer.',
        ]);

        // Catat di Riwayat
        OrderHistory::record(
            $order->id,
            $user->id,
            'admin_toko',
            'PESANAN_DIBATALKAN',
            "Pesanan dibatalkan oleh {$user->name} (Admin Toko). Alasan: " . ($validated['alasan'] ?? 'Tidak ada keterangan.')
        );

        // Notifikasi ke Admin Gudang
        AppNotification::send(
            null,
            'admin_gudang',
            $order->id,
            'pesanan_dibatalkan',
            'Pesanan dibatalkan oleh Toko',
            "Pesanan {$order->id_pesanan} ({$order->nama_customer}) telah dibatalkan oleh Admin Toko."
        );

        return redirect()->back()->with('success', "Pesanan {$order->id_pesanan} berhasil dibatalkan.");
    }
}


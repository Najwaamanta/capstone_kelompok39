<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DateNegotiation;
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

class GudangOrderController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');

        $query = Order::with(['konveksi', 'stages', 'negotiations.proposer', 'histories.user', 'creator'])
            ->latest();

        // Search bar: ID Pesanan, Nama Customer, Kode Seri
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pesanan', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%")
                  ->orWhere('kode_seri', 'like', "%{$search}%");
            });
        }

        // Filter status: Semua Status, Menunggu Persetujuan, Produksi Konveksi, Negosiasi, QC Gudang, Selesai, Cancel (Dibatalkan)
        if ($status = $request->input('status')) {
            if ($status !== 'semua') {
                $query->where('status', $status);
            }
        }

        $orders = $query->paginate(12)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $order->formatted_target = $order->target_selesai ? $order->target_selesai->isoFormat('D MMM Y') : '-';
            $order->formatted_estimasi = $order->estimasi_gudang ? $order->estimasi_gudang->isoFormat('D MMM Y') : '-';
            $order->formatted_created_at = $order->created_at->isoFormat('D MMM Y, HH:mm');
            // Negosiasi aktif yang menunggu respons dari gudang (diajukan Toko)
            $order->active_negotiation = $order->negotiations
                ->where('status', 'menunggu')
                ->first();

            // Format pill label & style matching Screenshot
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

        $konveksis = Konveksi::orderBy('nama')->get(['id', 'nama', 'penanggung_jawab', 'telepon', 'alamat']);

        return Inertia::render('Gudang/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'semua'),
            ],
            'konveksis' => $konveksis,
        ]);
    }

    /**
     * US-G02: Menetapkan tempat konveksi dan menyetujui pesanan
     */
    public function approve(Request $request, Order $order)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'konveksi_id' => ['required', 'exists:konveksis,id'],
        ], [
            'konveksi_id.required' => 'Tempat konveksi wajib dipilih sebelum menyetujui pesanan.',
            'konveksi_id.exists' => 'Tempat konveksi yang dipilih tidak valid.',
        ]);

        $konveksi = Konveksi::findOrFail($validated['konveksi_id']);

        DB::beginTransaction();
        try {
            $order->update([
                'konveksi_id' => $konveksi->id,
                'status' => 'dalam_produksi',
            ]);

            // Jika ada negosiasi yang menunggu, tandai disetujui/selesai
            DateNegotiation::where('order_id', $order->id)
                ->where('status', 'menunggu')
                ->update(['status' => 'disetujui', 'direspons_oleh' => $user->id]);

            OrderHistory::record(
                $order->id,
                $user->id,
                'admin_gudang',
                'PESANAN_DISETUJUI',
                "Pesanan disetujui oleh {$user->name} (Admin Gudang). Tempat konveksi ditetapkan ke {$konveksi->nama}. Status: Dalam Produksi."
            );

            AppNotification::send(
                null,
                'admin_toko',
                $order->id,
                'progres_diperbarui',
                'Pesanan disetujui Gudang',
                "Pesanan {$order->id_pesanan} ({$order->nama_customer}) telah disetujui Admin Gudang dan dialokasikan ke {$konveksi->nama}."
            );

            DB::commit();

            return redirect()->back()->with('success', "Pesanan {$order->id_pesanan} berhasil disetujui dan dialokasikan ke {$konveksi->nama}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyetujui pesanan: ' . $e->getMessage());
        }
    }

    /**
     * US-G03: Mengajukan negosiasi tanggal oleh Admin Gudang
     */
    public function negotiate(Request $request, Order $order)
    {
        $user = Auth::user();

        if (in_array($order->status, ['selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Negosiasi tidak dapat diajukan untuk pesanan yang sudah Selesai atau Dibatalkan.');
        }

        $validated = $request->validate([
            'estimasi_usulan' => ['required', 'date'],
            'catatan' => ['required', 'string', 'max:1000'],
        ], [
            'estimasi_usulan.required' => 'Estimasi penyelesaian usulan wajib diisi.',
            'catatan.required' => 'Catatan alasan negosiasi tanggal wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            DateNegotiation::create([
                'order_id' => $order->id,
                'diajukan_oleh' => $user->id,
                'role_pengaju' => 'admin_gudang',
                'estimasi_usulan' => $validated['estimasi_usulan'],
                'catatan' => $validated['catatan'],
                'status' => 'menunggu',
            ]);

            $order->update([
                'status' => 'negosiasi',
                'estimasi_gudang' => $validated['estimasi_usulan'],
            ]);

            $formattedDate = Carbon::parse($validated['estimasi_usulan'])->isoFormat('D MMMM Y');

            OrderHistory::record(
                $order->id,
                $user->id,
                'admin_gudang',
                'NEGOSIASI_GUDANG',
                "Admin Gudang ({$user->name}) mengajukan penyesuaian tanggal ke: {$formattedDate}. Catatan: {$validated['catatan']}"
            );

            AppNotification::send(
                null,
                'admin_toko',
                $order->id,
                'negosiasi_masuk',
                'Negosiasi tanggal menunggu respons',
                "Admin Gudang mengajukan penyesuaian target tanggal untuk pesanan {$order->id_pesanan} ({$formattedDate})."
            );

            DB::commit();

            return redirect()->back()->with('success', 'Usulan negosiasi tanggal berhasil dikirim ke Admin Toko!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengajukan negosiasi: ' . $e->getMessage());
        }
    }

    /**
     * US-G04: Mengatur jadwal dan status 4 tahap produksi
     */
    public function updateSchedule(Request $request, Order $order)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'konveksi_id' => ['nullable', 'exists:konveksis,id'],
            'stages' => ['required', 'array'],
            'stages.*.tahap' => ['required', 'string', 'in:pemotongan,sablon,konveksi,qc_gudang'],
            'stages.*.mulai' => ['nullable', 'date'],
            'stages.*.estimasi_selesai' => ['nullable', 'date'],
            'stages.*.status_tahap' => ['required', 'string', 'in:belum_dimulai,sedang_dikerjakan,selesai'],
            'stages.*.catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $stageNames = [
            'pemotongan' => 'Pemotongan',
            'sablon' => 'Sablon',
            'konveksi' => 'Produksi Konveksi',
            'qc_gudang' => 'QC Gudang',
        ];

        // 1. Validasi internal: Estimasi Selesai tidak boleh lebih awal dari Mulai
        foreach ($validated['stages'] as $st) {
            if (!empty($st['mulai']) && !empty($st['estimasi_selesai'])) {
                if ($st['estimasi_selesai'] < $st['mulai']) {
                    $name = $stageNames[$st['tahap']] ?? ucfirst($st['tahap']);
                    return redirect()->back()->with('error', "Estimasi Selesai untuk tahap {$name} tidak boleh lebih awal dari tanggal Mulai.");
                }
            }
        }

        // 2. Validasi Sekuensial: Tanggal mulai suatu tahapan tidak boleh mendahului tanggal selesainya tahapan sebelumnya
        $orderedStages = ['pemotongan', 'sablon', 'konveksi', 'qc_gudang'];
        $stagesByKey = collect($validated['stages'])->keyBy('tahap');

        for ($i = 1; $i < count($orderedStages); $i++) {
            $prevKey = $orderedStages[$i - 1];
            $currKey = $orderedStages[$i];

            $prevStage = $stagesByKey->get($prevKey);
            $currStage = $stagesByKey->get($currKey);

            if ($prevStage && $currStage) {
                $prevEnd = $prevStage['estimasi_selesai'] ?? null;
                $currStart = $currStage['mulai'] ?? null;

                if (!empty($prevEnd) && !empty($currStart)) {
                    if ($currStart < $prevEnd) {
                        $currName = $stageNames[$currKey];
                        $prevName = $stageNames[$prevKey];
                        return redirect()->back()->with(
                            'error',
                            "Validasi Sekuensial: Tanggal mulai proses {$currName} ({$currStart}) tidak boleh mendahului tanggal selesainya proses {$prevName} ({$prevEnd})."
                        );
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            if (!empty($validated['konveksi_id'])) {
                $order->konveksi_id = $validated['konveksi_id'];
            }

            $allFinished = true;
            $anyInProgress = false;
            $qcFinished = false;

            foreach ($validated['stages'] as $stageData) {
                $stageModel = OrderStage::firstOrNew([
                    'order_id' => $order->id,
                    'tahap' => $stageData['tahap'],
                ]);

                $stageModel->mulai = $stageData['mulai'] ?? null;
                $stageModel->estimasi_selesai = $stageData['estimasi_selesai'] ?? null;
                $stageModel->status_tahap = $stageData['status_tahap'];
                $stageModel->catatan = $stageData['catatan'] ?? null;
                $stageModel->save();

                if ($stageData['status_tahap'] !== 'selesai') {
                    $allFinished = false;
                }
                if ($stageData['status_tahap'] === 'sedang_dikerjakan') {
                    $anyInProgress = true;
                }
                if ($stageData['tahap'] === 'qc_gudang' && $stageData['status_tahap'] === 'selesai') {
                    $qcFinished = true;
                }
            }

            // Update status pesanan berdasarkan tahap
            if ($qcFinished) {
                $order->status = 'selesai';
            } elseif ($order->status !== 'dibatalkan' && $order->status !== 'negosiasi') {
                $qcStage = collect($validated['stages'])->firstWhere('tahap', 'qc_gudang');
                if ($qcStage && $qcStage['status_tahap'] === 'sedang_dikerjakan') {
                    $order->status = 'qc_gudang';
                } else {
                    $order->status = 'dalam_produksi';
                }
            }

            $order->save();

            OrderHistory::record(
                $order->id,
                $user->id,
                'admin_gudang',
                'JADWAL_DIPERBARUI',
                "Jadwal dan status tahapan produksi diperbarui oleh {$user->name} (Admin Gudang)."
            );

            // Notifikasi ke Admin Toko
            if ($qcFinished) {
                AppNotification::send(
                    null,
                    'admin_toko',
                    $order->id,
                    'hasil_qc',
                    'Hasil QC Lulus - Pesanan Selesai',
                    "Pesanan {$order->id_pesanan} telah lolos QC Gudang dan dinyatakan Selesai."
                );
            } else {
                AppNotification::send(
                    null,
                    'admin_toko',
                    $order->id,
                    'progres_diperbarui',
                    'Progres konveksi diperbarui',
                    "Jadwal dan status pengerjaan pesanan {$order->id_pesanan} telah diperbarui oleh Admin Gudang."
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Jadwal dan status progres produksi berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jadwal produksi: ' . $e->getMessage());
        }
    }
}


<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DateNegotiation;
use App\Models\Order;
use App\Models\OrderHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GudangNegotiationController extends Controller
{
    /**
     * US-G03: Admin Gudang menjawab usulan negosiasi dari Admin Toko
     * action: setuju | usul_balik
     */
    public function respond(Request $request, Order $order, DateNegotiation $negotiation)
    {
        $user = Auth::user();

        if ($negotiation->order_id !== $order->id) {
            return redirect()->back()->with('error', 'Data negosiasi tidak sesuai dengan pesanan ini.');
        }

        if ($negotiation->status !== 'menunggu') {
            return redirect()->back()->with('error', 'Negosiasi ini sudah direspons sebelumnya.');
        }

        $validated = $request->validate([
            'action'           => ['required', 'in:setuju,usul_balik'],
            'estimasi_usulan'  => ['required_if:action,usul_balik', 'nullable', 'date'],
            'catatan'          => ['required_if:action,usul_balik', 'nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'setuju') {
                // Tandai negosiasi ini sebagai disetujui
                $negotiation->update([
                    'status'          => 'disetujui',
                    'direspons_oleh'  => $user->id,
                    'catatan_respons' => 'Disetujui oleh Admin Gudang.',
                ]);

                $formattedDate = Carbon::parse($negotiation->estimasi_usulan)->isoFormat('D MMMM Y');

                // Update tanggal pesanan & kembalikan ke dalam_produksi
                $nextStatus = $order->konveksi_id ? 'dalam_produksi' : 'menunggu_persetujuan';
                $order->update([
                    'target_selesai'  => $negotiation->estimasi_usulan,
                    'estimasi_gudang' => $negotiation->estimasi_usulan,
                    'status'          => $nextStatus,
                ]);

                OrderHistory::record(
                    $order->id,
                    $user->id,
                    'admin_gudang',
                    'NEGOSIASI_DISETUJUI',
                    "Admin Gudang ({$user->name}) menyetujui usulan tanggal. Tanggal baru disepakati: {$formattedDate}. Gudang wajib memperbarui jadwal tahap produksi."
                );

                AppNotification::send(
                    null,
                    'admin_toko',
                    $order->id,
                    'negosiasi_disetujui',
                    'Negosiasi tanggal disepakati',
                    "Admin Gudang menyetujui target penyelesaian baru ({$formattedDate}) untuk pesanan {$order->id_pesanan}. Produksi dilanjutkan."
                );

                $msg = "Negosiasi disetujui. Tanggal baru ({$formattedDate}) ditetapkan. Harap segera perbarui jadwal tahap produksi yang belum selesai!";
            } else {
                // Usul balik ke Admin Toko
                $negotiation->update([
                    'status'          => 'ditolak',
                    'direspons_oleh'  => $user->id,
                    'catatan_respons' => 'Admin Gudang mengajukan usulan balik.',
                ]);

                DateNegotiation::create([
                    'order_id'        => $order->id,
                    'diajukan_oleh'   => $user->id,
                    'role_pengaju'    => 'admin_gudang',
                    'estimasi_usulan' => $validated['estimasi_usulan'],
                    'catatan'         => $validated['catatan'],
                    'status'          => 'menunggu',
                ]);

                $formattedDate = Carbon::parse($validated['estimasi_usulan'])->isoFormat('D MMMM Y');

                OrderHistory::record(
                    $order->id,
                    $user->id,
                    'admin_gudang',
                    'NEGOSIASI_USULAN_BALIK',
                    "Admin Gudang ({$user->name}) mengajukan usulan balik tanggal: {$formattedDate}. Catatan: {$validated['catatan']}"
                );

                AppNotification::send(
                    null,
                    'admin_toko',
                    $order->id,
                    'negosiasi_masuk',
                    'Usulan tanggal balik dari Gudang',
                    "Admin Gudang mengajukan usulan tanggal baru ({$formattedDate}) untuk pesanan {$order->id_pesanan}."
                );

                $msg = 'Usulan balik berhasil dikirim ke Admin Toko!';
            }

            DB::commit();
            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses respons negosiasi: ' . $e->getMessage());
        }
    }
}


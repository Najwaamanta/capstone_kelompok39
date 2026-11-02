<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DateNegotiation;
use App\Models\Order;
use App\Models\OrderHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TokoNegotiationController extends Controller
{
    /**
     * US-N01: Mengajukan estimasi tanggal baru oleh Admin Toko
     */
    public function propose(Request $request, Order $order)
    {
        $user = Auth::user();

        if (in_array($order->status, ['selesai', 'dibatalkan'])) {
            return redirect()->back()->with('error', 'Negosiasi tidak dapat dilakukan pada pesanan yang sudah Selesai atau Dibatalkan.');
        }

        $validated = $request->validate([
            'estimasi_usulan' => ['required', 'date'],
            'catatan' => ['required', 'string', 'max:1000'],
        ], [
            'estimasi_usulan.required' => 'Estimasi penyelesaian wajib ditentukan.',
            'catatan.required' => 'Catatan / alasan penyesuaian tanggal wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $negotiation = DateNegotiation::create([
                'order_id' => $order->id,
                'diajukan_oleh' => $user->id,
                'role_pengaju' => 'admin_toko',
                'estimasi_usulan' => $validated['estimasi_usulan'],
                'catatan' => $validated['catatan'],
                'status' => 'menunggu',
            ]);

            $order->update([
                'status' => 'negosiasi',
            ]);

            $formattedDate = Carbon::parse($validated['estimasi_usulan'])->isoFormat('D MMMM Y');

            OrderHistory::record(
                $order->id,
                $user->id,
                'admin_toko',
                'NEGOSIASI_DIAJUKAN',
                "Admin Toko ({$user->name}) mengajukan usulan tanggal penyelesaian baru: {$formattedDate}. Catatan: {$validated['catatan']}"
            );

            AppNotification::send(
                null,
                'admin_gudang',
                $order->id,
                'negosiasi_masuk',
                'Negosiasi tanggal menunggu respons',
                "Admin Toko mengajukan penyesuaian tanggal untuk pesanan {$order->id_pesanan} ({$formattedDate})."
            );

            DB::commit();

            return redirect()->back()->with('success', 'Usulan tanggal baru berhasil dikirim ke Admin Gudang!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim usulan negosiasi: ' . $e->getMessage());
        }
    }

    /**
     * US-N02: Admin Toko menjawab usulan dari Admin Gudang (Menyetujui atau Usulan Balik)
     */
    public function respond(Request $request, Order $order, DateNegotiation $negotiation)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'action' => ['required', 'in:setuju,usul_balik'],
            'estimasi_usulan' => ['required_if:action,usul_balik', 'nullable', 'date'],
            'catatan' => ['required_if:action,usul_balik', 'nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'setuju') {
                $negotiation->update([
                    'status' => 'disetujui',
                    'direspons_oleh' => $user->id,
                    'catatan_respons' => 'Disetujui oleh Admin Toko.',
                ]);

                // Tanggal kesepakatan menjadi acuan baru
                $formattedDate = Carbon::parse($negotiation->estimasi_usulan)->isoFormat('D MMMM Y');
                $nextStatus = $order->konveksi_id ? 'dalam_produksi' : 'menunggu_persetujuan';

                $order->update([
                    'target_selesai' => $negotiation->estimasi_usulan,
                    'estimasi_gudang' => $negotiation->estimasi_usulan,
                    'status' => $nextStatus,
                ]);

                OrderHistory::record(
                    $order->id,
                    $user->id,
                    'admin_toko',
                    'NEGOSIASI_DISETUJUI',
                    "Admin Toko ({$user->name}) menyetujui usulan tanggal Gudang. Tanggal kesepakatan baru: {$formattedDate}."
                );

                AppNotification::send(
                    null,
                    'admin_gudang',
                    $order->id,
                    'negosiasi_disetujui',
                    'Negosiasi tanggal disepakati',
                    "Admin Toko telah menyetujui target penyelesaian baru ({$formattedDate}) untuk pesanan {$order->id_pesanan}."
                );

                $msg = "Tanggal kesepakatan baru ({$formattedDate}) berhasil ditetapkan!";
            } else {
                // Usulan balik
                $negotiation->update([
                    'status' => 'ditolak',
                    'direspons_oleh' => $user->id,
                    'catatan_respons' => 'Admin Toko mengajukan usulan balik.',
                ]);

                $newNeg = DateNegotiation::create([
                    'order_id' => $order->id,
                    'diajukan_oleh' => $user->id,
                    'role_pengaju' => 'admin_toko',
                    'estimasi_usulan' => $validated['estimasi_usulan'],
                    'catatan' => $validated['catatan'],
                    'status' => 'menunggu',
                ]);

                $formattedDate = Carbon::parse($validated['estimasi_usulan'])->isoFormat('D MMMM Y');

                OrderHistory::record(
                    $order->id,
                    $user->id,
                    'admin_toko',
                    'NEGOSIASI_USULAN_BALIK',
                    "Admin Toko ({$user->name}) mengajukan usulan balik tanggal: {$formattedDate}. Catatan: {$validated['catatan']}"
                );

                AppNotification::send(
                    null,
                    'admin_gudang',
                    $order->id,
                    'negosiasi_masuk',
                    'Usulan tanggal balik dari Toko',
                    "Admin Toko mengajukan usulan tanggal baru ({$formattedDate}) untuk pesanan {$order->id_pesanan}."
                );

                $msg = 'Usulan balik berhasil dikirim ke Admin Gudang!';
            }

            DB::commit();

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses respon negosiasi: ' . $e->getMessage());
        }
    }
}


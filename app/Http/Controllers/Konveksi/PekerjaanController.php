<?php

namespace App\Http\Controllers\Konveksi;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PekerjaanController extends Controller
{
    /**
     * Display list of tasks / jobs for konveksi matching design.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'semua');

        $jobs = [
            [
                'id' => 1,
                'order_number' => 'ORD-2506-018',
                'item_name' => 'Maison Aurelia',
                'sku' => 'DRS-A17',
                'status' => 'Produksi Konveksi',
                'status_type' => 'cyan',
                'quantity' => 120,
                'target_date' => '28 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'progress_percentage' => 68,
                'category' => 'Dress Casual',
                'fabric' => 'Katun Rayon Twill Premium (400m)',
                'notes' => 'Pola badan depan dan lengan selesai dipotong, proses penjahitan utama sedang berlangsung.',
                'timeline' => [
                    ['stage' => 'Potong Bahan Pola', 'status' => 'Selesai', 'date' => '20 Jun 2025'],
                    ['stage' => 'Jahit Obras & Sambung', 'status' => 'Sedang Berjalan', 'date' => '23 Jun 2025'],
                    ['stage' => 'Finishing & Pasang Kancing', 'status' => 'Menunggu', 'date' => '26 Jun 2025'],
                    ['stage' => 'Pemeriksaan QC Pabrik', 'status' => 'Menunggu', 'date' => '27 Jun 2025'],
                ],
            ],
            [
                'id' => 2,
                'order_number' => 'ORD-2506-016',
                'item_name' => 'Ruang Rona',
                'sku' => 'KMS-P21',
                'status' => 'QC Gudang',
                'status_type' => 'gray',
                'quantity' => 200,
                'target_date' => '24 Jun 2025',
                'konveksi_name' => 'Konveksi Mekar',
                'progress_percentage' => 92,
                'category' => 'Kemeja Linen',
                'fabric' => 'Linen Rami Pure White (550m)',
                'notes' => 'Seluruh batch 200 pcs telah selesai dijahit dan dipress. Sedang dalam tahap akhir QC gudang penerimaan.',
                'timeline' => [
                    ['stage' => 'Potong Bahan Pola', 'status' => 'Selesai', 'date' => '15 Jun 2025'],
                    ['stage' => 'Jahit & Perakitan', 'status' => 'Selesai', 'date' => '21 Jun 2025'],
                    ['stage' => 'Finishing & Steam Ironing', 'status' => 'Selesai', 'date' => '22 Jun 2025'],
                    ['stage' => 'Pemeriksaan QC Gudang', 'status' => 'Sedang Berjalan', 'date' => '23 Jun 2025'],
                ],
            ],
            [
                'id' => 3,
                'order_number' => 'ORD-2506-019',
                'item_name' => 'Floral Blouse',
                'sku' => 'BLS-B04',
                'status' => 'Potong Pola',
                'status_type' => 'blue',
                'quantity' => 150,
                'target_date' => '30 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'progress_percentage' => 35,
                'category' => 'Atasan Wanita',
                'fabric' => 'Sifon Motif Floral (300m)',
                'notes' => 'Bahan baku telah diinspeksi. Pemotongan pola kain motif dimulai pagi ini.',
                'timeline' => [
                    ['stage' => 'Penerimaan Kain', 'status' => 'Selesai', 'date' => '21 Jun 2025'],
                    ['stage' => 'Potong Pola Bahan', 'status' => 'Sedang Berjalan', 'date' => '23 Jun 2025'],
                    ['stage' => 'Jahit Lipit & Kerah', 'status' => 'Menunggu', 'date' => '26 Jun 2025'],
                    ['stage' => 'Inspeksi Kualitas', 'status' => 'Menunggu', 'date' => '29 Jun 2025'],
                ],
            ],
            [
                'id' => 4,
                'order_number' => 'ORD-2506-015',
                'item_name' => 'Casual Outer',
                'sku' => 'OUT-C02',
                'status' => 'Menunggu QC',
                'status_type' => 'amber',
                'quantity' => 100,
                'target_date' => '25 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'progress_percentage' => 85,
                'category' => 'Outerwear',
                'fabric' => 'Canvas Twill Khaki (280m)',
                'notes' => 'Perakitan kancing dan jahitan bartack selesai. Menunggu verifikasi inspector QC konveksi.',
                'timeline' => [
                    ['stage' => 'Potong Bahan Pola', 'status' => 'Selesai', 'date' => '17 Jun 2025'],
                    ['stage' => 'Jahit & Obras', 'status' => 'Selesai', 'date' => '21 Jun 2025'],
                    ['stage' => 'Pasang Kancing & Saku', 'status' => 'Selesai', 'date' => '22 Jun 2025'],
                    ['stage' => 'Inspeksi QC', 'status' => 'Sedang Berjalan', 'date' => '23 Jun 2025'],
                ],
            ],
        ];

        return Inertia::render('Konveksi/Pekerjaan/Index', [
            'jobs' => $jobs,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * Handle progress / status update for a job.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        ActivityLog::record(
            $user ? $user->id : null,
            'STATUS_UPDATE',
            "Status pekerjaan #{$id} diperbarui menjadi {$validated['status']} ({$validated['progress_percentage']}%) oleh {$user->name}."
        );

        return back()->with('success', "Progres pekerjaan berhasil diperbarui menjadi {$validated['progress_percentage']}% ({$validated['status']}).");
    }
}

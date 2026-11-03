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
                'target_date_full' => '28 Jun 2025',
                'estimated_warehouse' => '27 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 8 menit lalu',
                'progress_percentage' => 68,
                'category' => 'Dress Casual',
                'fabric' => 'Katun Rayon Twill Premium (400m)',
                'notes' => 'Pola badan depan dan lengan selesai dipotong, proses penjahitan utama sedang berlangsung.',
                'current_step' => 4,
                'steps' => [
                    ['name' => 'Pesanan', 'status' => 'done'],
                    ['name' => 'Pemotongan', 'status' => 'done'],
                    ['name' => 'Sablon', 'status' => 'done'],
                    ['name' => 'Konveksi', 'status' => 'active'],
                    ['name' => 'QC Gudang', 'status' => 'pending'],
                    ['name' => 'Selesai', 'status' => 'pending'],
                ],
                'schedules' => [
                    ['name' => 'Pemotongan', 'status' => 'Selesai', 'estimate' => '21 Jun 2025', 'type' => 'done'],
                    ['name' => 'Sablon', 'status' => 'Sedang Dikerjakan', 'estimate' => '23 Jun 2025', 'type' => 'active'],
                    ['name' => 'Produksi Konveksi', 'status' => 'Belum Dimulai', 'estimate' => '25 Jun 2025', 'type' => 'pending'],
                    ['name' => 'QC Gudang', 'status' => 'Belum Dimulai', 'estimate' => '27 Jun 2025', 'type' => 'pending'],
                ],
                'history' => [
                    [
                        'title' => 'Status diperbarui menjadi Produksi Konveksi',
                        'author' => 'Oleh Siti Rahma',
                        'time' => '23 Jun 2025 · 09.12',
                        'type' => 'factory',
                    ],
                    [
                        'title' => 'Estimasi penyelesaian disepakati',
                        'author' => 'Estimasi: 27 Jun 2025',
                        'time' => '22 Jun 2025 · 15.40',
                        'type' => 'message',
                    ],
                    [
                        'title' => 'Pesanan dan nota digital dibuat',
                        'author' => 'Oleh Admin Toko',
                        'time' => '21 Jun 2025 · 10.04',
                        'type' => 'file',
                    ],
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
                'target_date_full' => '24 Jun 2025',
                'estimated_warehouse' => '23 Jun 2025',
                'konveksi_name' => 'Konveksi Mekar',
                'last_updated' => 'Terakhir diperbarui 25 menit lalu',
                'progress_percentage' => 92,
                'category' => 'Kemeja Linen',
                'fabric' => 'Linen Rami Pure White (550m)',
                'notes' => 'Seluruh batch 200 pcs telah selesai dijahit dan dipress. Sedang dalam tahap akhir QC gudang penerimaan.',
                'current_step' => 5,
                'steps' => [
                    ['name' => 'Pesanan', 'status' => 'done'],
                    ['name' => 'Pemotongan', 'status' => 'done'],
                    ['name' => 'Sablon', 'status' => 'done'],
                    ['name' => 'Konveksi', 'status' => 'done'],
                    ['name' => 'QC Gudang', 'status' => 'active'],
                    ['name' => 'Selesai', 'status' => 'pending'],
                ],
                'schedules' => [
                    ['name' => 'Pemotongan', 'status' => 'Selesai', 'estimate' => '17 Jun 2025', 'type' => 'done'],
                    ['name' => 'Sablon', 'status' => 'Selesai', 'estimate' => '19 Jun 2025', 'type' => 'done'],
                    ['name' => 'Produksi Konveksi', 'status' => 'Selesai', 'estimate' => '22 Jun 2025', 'type' => 'done'],
                    ['name' => 'QC Gudang', 'status' => 'Sedang Dikerjakan', 'estimate' => '23 Jun 2025', 'type' => 'active'],
                ],
                'history' => [
                    [
                        'title' => 'Batch 200 pcs dikirim ke QC Gudang',
                        'author' => 'Oleh Hendra Setiawan',
                        'time' => '23 Jun 2025 · 08.30',
                        'type' => 'factory',
                    ],
                    [
                        'title' => 'Penjahitan dan kancing selesai 100%',
                        'author' => 'Oleh Konveksi Mekar',
                        'time' => '22 Jun 2025 · 17.15',
                        'type' => 'message',
                    ],
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
                'target_date_full' => '30 Jun 2025',
                'estimated_warehouse' => '29 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 1 jam lalu',
                'progress_percentage' => 35,
                'category' => 'Atasan Wanita',
                'fabric' => 'Sifon Motif Floral (300m)',
                'notes' => 'Bahan baku telah diinspeksi. Pemotongan pola kain motif dimulai pagi ini.',
                'current_step' => 2,
                'steps' => [
                    ['name' => 'Pesanan', 'status' => 'done'],
                    ['name' => 'Pemotongan', 'status' => 'active'],
                    ['name' => 'Sablon', 'status' => 'pending'],
                    ['name' => 'Konveksi', 'status' => 'pending'],
                    ['name' => 'QC Gudang', 'status' => 'pending'],
                    ['name' => 'Selesai', 'status' => 'pending'],
                ],
                'schedules' => [
                    ['name' => 'Pemotongan', 'status' => 'Sedang Dikerjakan', 'estimate' => '24 Jun 2025', 'type' => 'active'],
                    ['name' => 'Sablon', 'status' => 'Belum Dimulai', 'estimate' => '26 Jun 2025', 'type' => 'pending'],
                    ['name' => 'Produksi Konveksi', 'status' => 'Belum Dimulai', 'estimate' => '28 Jun 2025', 'type' => 'pending'],
                    ['name' => 'QC Gudang', 'status' => 'Belum Dimulai', 'estimate' => '29 Jun 2025', 'type' => 'pending'],
                ],
                'history' => [
                    [
                        'title' => 'Pola kain sifon mulai dipotong',
                        'author' => 'Oleh Siti Rahma',
                        'time' => '23 Jun 2025 · 07.45',
                        'type' => 'factory',
                    ],
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
                'target_date_full' => '25 Jun 2025',
                'estimated_warehouse' => '24 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 3 jam lalu',
                'progress_percentage' => 85,
                'category' => 'Outerwear',
                'fabric' => 'Canvas Twill Khaki (280m)',
                'notes' => 'Perakitan kancing dan jahitan bartack selesai. Menunggu verifikasi inspector QC konveksi.',
                'current_step' => 4,
                'steps' => [
                    ['name' => 'Pesanan', 'status' => 'done'],
                    ['name' => 'Pemotongan', 'status' => 'done'],
                    ['name' => 'Sablon', 'status' => 'done'],
                    ['name' => 'Konveksi', 'status' => 'active'],
                    ['name' => 'QC Gudang', 'status' => 'pending'],
                    ['name' => 'Selesai', 'status' => 'pending'],
                ],
                'schedules' => [
                    ['name' => 'Pemotongan', 'status' => 'Selesai', 'estimate' => '18 Jun 2025', 'type' => 'done'],
                    ['name' => 'Sablon', 'status' => 'Selesai', 'estimate' => '20 Jun 2025', 'type' => 'done'],
                    ['name' => 'Produksi Konveksi', 'status' => 'Selesai', 'estimate' => '22 Jun 2025', 'type' => 'done'],
                    ['name' => 'QC Gudang', 'status' => 'Sedang Dikerjakan', 'estimate' => '24 Jun 2025', 'type' => 'active'],
                ],
                'history' => [
                    [
                        'title' => 'Pemeriksaan jahitan saku disetujui',
                        'author' => 'Oleh QC Inspector',
                        'time' => '23 Jun 2025 · 06.30',
                        'type' => 'factory',
                    ],
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

<?php

namespace App\Http\Controllers\Konveksi;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the Admin Konveksi dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $firstName = explode(' ', trim($user->name ?? 'Siti'))[0];

        // Format waktu salam (Pagi, Siang, Sore, Malam)
        $hour = Carbon::now()->format('H');
        if ($hour < 11) {
            $greetingTime = 'pagi';
        } elseif ($hour < 15) {
            $greetingTime = 'siang';
        } elseif ($hour < 18) {
            $greetingTime = 'sore';
        } else {
            $greetingTime = 'malam';
        }

        // Tanggal terformat bahasa Indonesia (e.g. SENIN, 23 JUNI 2025)
        Carbon::setLocale('id');
        $formattedDate = strtoupper(Carbon::now()->isoFormat('dddd, D MMMM Y'));

        // Statistik ringkasan sesuai dengan desain
        $stats = [
            'pekerjaan_aktif' => [
                'label' => 'Pekerjaan aktif',
                'value' => 4,
                'subtext' => '470 pcs total',
            ],
            'dalam_produksi' => [
                'label' => 'Dalam produksi',
                'value' => 2,
                'subtext' => 'Target minggu ini',
            ],
            'menunggu_qc' => [
                'label' => 'Menunggu QC',
                'value' => 1,
                'subtext' => 'Siap diperiksa',
            ],
            'siap_dikirim' => [
                'label' => 'Siap dikirim',
                'value' => 1,
                'subtext' => 'Ke Gudang Lilie Belle',
            ],
        ];

        // Pekerjaan Prioritas lengkap dengan data modal detail
        $priorityJobs = [
            [
                'id' => 1,
                'order_number' => 'ORD-2506-018',
                'item_name' => 'Maison Aurelia',
                'sku' => 'DRS-A17',
                'quantity' => 120,
                'status' => 'Produksi Konveksi',
                'status_color' => 'cyan',
                'target_date' => '28 Jun',
                'target_date_full' => '28 Jun 2025',
                'estimated_warehouse' => '27 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 8 menit lalu',
                'progress_percentage' => 68,
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
                'quantity' => 200,
                'status' => 'QC Gudang',
                'status_color' => 'gray',
                'target_date' => '24 Jun',
                'target_date_full' => '24 Jun 2025',
                'estimated_warehouse' => '23 Jun 2025',
                'konveksi_name' => 'Konveksi Mekar',
                'last_updated' => 'Terakhir diperbarui 25 menit lalu',
                'progress_percentage' => 92,
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
                'quantity' => 150,
                'status' => 'Potong Pola',
                'status_color' => 'blue',
                'target_date' => '30 Jun',
                'target_date_full' => '30 Jun 2025',
                'estimated_warehouse' => '29 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 1 jam lalu',
                'progress_percentage' => 30,
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
                'quantity' => 100,
                'status' => 'Menunggu QC',
                'status_color' => 'amber',
                'target_date' => '25 Jun',
                'target_date_full' => '25 Jun 2025',
                'estimated_warehouse' => '24 Jun 2025',
                'konveksi_name' => 'Konveksi Sinar Jaya',
                'last_updated' => 'Terakhir diperbarui 3 jam lalu',
                'progress_percentage' => 85,
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

        // Aktivitas Terbaru
        $recentActivities = [
            [
                'id' => 1,
                'title' => 'Progres konveksi diperbarui',
                'description' => 'ORD-2506-018 masuk tahap Produksi Konveksi.',
                'time_ago' => '8 menit lalu',
                'type' => 'progress',
            ],
            [
                'id' => 2,
                'title' => 'Bahan baku diterima',
                'description' => 'Kain katun rayon 400m dari Gudang Lilie Belle telah diverifikasi.',
                'time_ago' => '1 jam lalu',
                'type' => 'material',
            ],
            [
                'id' => 3,
                'title' => 'Instruksi revisi QC',
                'description' => 'Catatan perbaikan jahitan kerah batch ORD-2506-012 disetujui.',
                'time_ago' => '3 jam lalu',
                'type' => 'qc',
            ],
        ];

        return Inertia::render('Konveksi/Dashboard', [
            'greeting' => "Selamat {$greetingTime}, {$firstName}",
            'formattedDate' => $formattedDate,
            'stats' => $stats,
            'priorityJobs' => $priorityJobs,
            'recentActivities' => $recentActivities,
            'unreadNotificationCount' => 2,
        ]);
    }
}

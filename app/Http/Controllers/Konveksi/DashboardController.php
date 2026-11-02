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

        // Pekerjaan Prioritas (sesuai screenshot)
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
                'progress_percentage' => 65,
            ],
            [
                'id' => 2,
                'order_number' => 'ORD-2506-019',
                'item_name' => 'Floral Blouse',
                'sku' => 'BLS-B04',
                'quantity' => 150,
                'status' => 'Potong Pola',
                'status_color' => 'blue',
                'target_date' => '30 Jun',
                'progress_percentage' => 30,
            ],
            [
                'id' => 3,
                'order_number' => 'ORD-2506-015',
                'item_name' => 'Casual Outer',
                'sku' => 'OUT-C02',
                'quantity' => 100,
                'status' => 'Menunggu QC',
                'status_color' => 'amber',
                'target_date' => '25 Jun',
                'progress_percentage' => 85,
            ],
            [
                'id' => 4,
                'order_number' => 'ORD-2506-014',
                'item_name' => 'Basic Shirt',
                'sku' => 'SHT-S09',
                'quantity' => 100,
                'status' => 'Siap Dikirim',
                'status_color' => 'emerald',
                'target_date' => '24 Jun',
                'progress_percentage' => 100,
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


<?php

namespace App\Http\Controllers\Konveksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display notifications for Admin Konveksi matching design.
     */
    public function index(Request $request): Response
    {
        $notifications = [
            [
                'id' => 1,
                'title' => 'Progres konveksi diperbarui',
                'description' => 'ORD-2506-018 masuk tahap Produksi Konveksi.',
                'time_ago' => '8 menit lalu',
                'is_unread' => true,
                'type' => 'factory',
            ],
            [
                'id' => 2,
                'title' => 'Negosiasi tanggal menunggu respons',
                'description' => 'Estimasi ORD-2506-017 diusulkan menjadi 26 Jun 2025.',
                'time_ago' => '32 menit lalu',
                'is_unread' => true,
                'type' => 'calendar',
            ],
            [
                'id' => 3,
                'title' => 'Pesanan baru diterima',
                'description' => 'ORD-2506-018 dari Maison Aurelia telah diteruskan.',
                'time_ago' => 'Kemarin, 16.42',
                'is_unread' => false,
                'type' => 'file',
            ],
            [
                'id' => 4,
                'title' => 'Hasil QC: Lulus',
                'description' => 'Seluruh 60 pcs ORD-2506-015 lulus pemeriksaan.',
                'time_ago' => 'Kemarin, 14.20',
                'is_unread' => false,
                'type' => 'qc',
            ],
        ];

        return Inertia::render('Konveksi/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }
}

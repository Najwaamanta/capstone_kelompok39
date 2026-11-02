<?php

namespace App\Http\Controllers\Konveksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display notifications for Admin Konveksi.
     */
    public function index(Request $request): Response
    {
        $notifications = [
            [
                'id' => 1,
                'title' => 'Perubahan Jadwal Prioritas ORD-2506-018',
                'description' => 'Target penyelesaian dimajukan menjadi 28 Juni 2025 sesuai permintaan toko pusat.',
                'created_at' => '10 menit lalu',
                'is_unread' => true,
                'type' => 'urgent',
            ],
            [
                'id' => 2,
                'title' => 'Pengiriman Bahan Baku Diterima',
                'description' => 'Gudang Pusat telah mengirim 400m kain katun rayon untuk pemenuhan PO ORD-2506-018.',
                'created_at' => '1 jam lalu',
                'is_unread' => true,
                'type' => 'info',
            ],
            [
                'id' => 3,
                'title' => 'Catatan Revisi QC Disetujui',
                'description' => 'Inspektur QC menyetujui perbaikan lipatan lengan pada batch ORD-2506-012.',
                'created_at' => 'Kemarin, 16:30',
                'is_unread' => false,
                'type' => 'success',
            ],
        ];

        return Inertia::render('Konveksi/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }
}


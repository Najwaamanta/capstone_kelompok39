<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GudangNotificationController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');
        $user = Auth::user();
        $tab = $request->input('tab', 'semua');

        $baseQuery = AppNotification::with([
            'order.konveksi',
            'order.stages',
            'order.histories.user',
            'order.negotiations',
        ])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('target_role', 'admin_gudang')
                  ->orWhereNull('target_role');
            });

        $totalCount = (clone $baseQuery)->count();
        $unreadCount = (clone $baseQuery)->where('dibaca', false)->count();

        $query = (clone $baseQuery)->latest();

        if ($tab === 'belum_dibaca') {
            $query->where('dibaca', false);
        }

        $notifications = $query->paginate(20)->withQueryString();

        $notifications->getCollection()->transform(function ($item) {
            $created = $item->created_at;
            if ($created->isToday()) {
                $diffMin = (int) round($created->diffInMinutes());
                if ($diffMin < 60) {
                    $item->time_ago = max(1, $diffMin) . ' menit yang lalu';
                } else {
                    $item->time_ago = 'Hari ini, ' . $created->format('H:i');
                }
            } elseif ($created->isYesterday()) {
                $item->time_ago = 'Kemarin, ' . $created->format('H:i');
            } else {
                $item->time_ago = $created->translatedFormat('d M Y, H:i');
            }

            $judul = strtolower($item->judul);
            if (str_contains($judul, 'konveksi') || str_contains($judul, 'progres')) {
                $item->category_type = 'progres';
            } elseif (str_contains($judul, 'negosiasi')) {
                $item->category_type = 'negosiasi';
            } elseif (str_contains($judul, 'pesanan') || str_contains($judul, 'baru') || str_contains($judul, 'diterima')) {
                $item->category_type = 'pesanan';
            } elseif (str_contains($judul, 'qc') || str_contains($judul, 'lulus')) {
                $item->category_type = 'qc';
            } else {
                $item->category_type = 'default';
            }

            if ($item->order) {
                $item->order->konveksi_nama = $item->order->konveksi?->nama ?? 'Belum ditetapkan';
            }

            return $item;
        });

        return Inertia::render('Gudang/Notifications/Index', [
            'notifications' => $notifications,
            'tab'           => $tab,
            'unread_count'  => $unreadCount,
            'total_count'   => $totalCount,
        ]);
    }

    public function markAsRead(Request $request, AppNotification $notification)
    {
        $notification->update(['dibaca' => true]);
        return redirect()->back();
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        AppNotification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('target_role', 'admin_gudang')
              ->orWhereNull('target_role');
        })->where('dibaca', false)->update(['dibaca' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi Admin Gudang telah ditandai dibaca.');
    }
}

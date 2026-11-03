<?php

namespace App\Http\Controllers\Toko;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TokoNotificationController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');
        $user = Auth::user();
        $tab = $request->input('tab', 'semua'); // 'semua' atau 'belum_dibaca'

        $query = AppNotification::with([
            'order.konveksi',
            'order.stages',
            'order.histories.user',
            'order.negotiations',
        ])
            ->forUserOrRole($user)
            ->latest();

        if ($tab === 'belum_dibaca') {
            $query->unread();
        }

        $notifications = $query->paginate(15)->withQueryString();

        $notifications->getCollection()->transform(function ($item) {
            $item->time_ago = $item->created_at->diffForHumans();
            $item->formatted_created_at = $item->created_at->isoFormat('D MMMM Y, HH:mm');

            if ($item->order) {
                $item->order->konveksi_nama = $item->order->konveksi?->nama ?? 'Belum ditetapkan';
            }

            return $item;
        });

        $unreadCount = AppNotification::forUserOrRole($user)->unread()->count();
        $totalCount  = AppNotification::forUserOrRole($user)->count();

        return Inertia::render('Toko/Notifications/Index', [
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
        AppNotification::forUserOrRole($user)->unread()->update(['dibaca' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}

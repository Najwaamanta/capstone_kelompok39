<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');

        // 1. Registration requests (perlu_aktivasi)
        $pendingUsers = User::where('status', 'perlu_aktivasi')
            ->latest()
            ->paginate(15);

        $pendingUsers->getCollection()->transform(function ($u) {
            $u->formatted_created_at = $u->created_at->isoFormat('D MMMM Y, HH:mm');
            $u->time_ago = $u->created_at->diffForHumans();
            return $u;
        });

        // 2. Activity notifications from non-administrator roles (last 50)
        $activityLogs = ActivityLog::with(['user', 'targetUser'])
            ->whereHas('user', function ($q) {
                $q->whereIn('role', ['admin_toko', 'admin_gudang', 'admin_konveksi']);
            })
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($log) {
                $log->formatted_created_at = $log->created_at->isoFormat('D MMMM Y, HH:mm');
                $log->time_ago = $log->created_at->diffForHumans();
                return $log;
            });

        // 3. Password reset requests (pending)
        $passwordResetRequests = PasswordResetRequest::with('user')
            ->pending()
            ->latest()
            ->get()
            ->map(function ($req) {
                $req->formatted_created_at = $req->created_at->isoFormat('D MMMM Y, HH:mm');
                $req->time_ago = $req->created_at->diffForHumans();
                return $req;
            });

        $totalCount = $pendingUsers->total()
            + $activityLogs->count()
            + $passwordResetRequests->count();

        return Inertia::render('Admin/Notifications/Index', [
            'pendingUsers'           => $pendingUsers,
            'activityLogs'           => $activityLogs,
            'passwordResetRequests'  => $passwordResetRequests,
            'counts' => [
                'pending'        => $pendingUsers->total(),
                'activity'       => $activityLogs->count(),
                'passwordReset'  => $passwordResetRequests->count(),
                'total'          => $totalCount,
            ],
        ]);
    }
}


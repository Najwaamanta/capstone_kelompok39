<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        Carbon::setLocale('id');

        $query = ActivityLog::with(['user', 'targetUser'])->latest();

        // 1. Search Filter (description, action, ip_address, user name/email)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('targetUser', function ($tuq) use ($search) {
                      $tuq->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Action Filter
        if ($action = $request->input('action')) {
            if ($action !== 'all') {
                $query->where('action', $action);
            }
        }

        // 3. Date Filter (e.g. 'today' or 'YYYY-MM-DD')
        $dateParam = $request->input('date');
        $resolvedDate = null;
        $formattedFilterDate = null;
        $isTodayFilter = false;

        if ($dateParam) {
            if ($dateParam === 'today') {
                $targetCarbon = Carbon::today();
                $query->whereDate('created_at', $targetCarbon);
                $resolvedDate = $targetCarbon->toDateString();
                $formattedFilterDate = 'Hari Ini (' . $targetCarbon->isoFormat('D MMMM Y') . ')';
                $isTodayFilter = true;
            } else {
                try {
                    $targetCarbon = Carbon::parse($dateParam);
                    $query->whereDate('created_at', $targetCarbon->toDateString());
                    $resolvedDate = $targetCarbon->toDateString();
                    if ($targetCarbon->isToday()) {
                        $formattedFilterDate = 'Hari Ini (' . $targetCarbon->isoFormat('D MMMM Y') . ')';
                        $isTodayFilter = true;
                    } else {
                        $formattedFilterDate = $targetCarbon->isoFormat('D MMMM Y');
                    }
                } catch (\Exception $e) {
                    // Ignore date parsing errors
                }
            }
        }

        $logs = $query->paginate(10)->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            Carbon::setLocale('id');
            $log->formatted_created_at = $log->created_at->isoFormat('D MMMM Y, HH:mm');
            $log->time_ago = $log->created_at->diffForHumans();
            return $log;
        });

        $actionsList = [
            ['value' => 'all', 'label' => 'Semua Jenis Aksi'],
            ['value' => 'LOGIN', 'label' => 'Login'],
            ['value' => 'LOGOUT', 'label' => 'Logout'],
            ['value' => 'REGISTER', 'label' => 'Registrasi Akun'],
            ['value' => 'ACTIVATED', 'label' => 'Aktivasi Akun'],
            ['value' => 'USER_CREATED', 'label' => 'Pembuatan Akun'],
            ['value' => 'USER_UPDATED', 'label' => 'Pembaruan Akun'],
            ['value' => 'USER_DELETED', 'label' => 'Penghapusan Akun'],
            ['value' => 'STATUS_CHANGED', 'label' => 'Perubahan Status'],
            ['value' => 'PROFILE_UPDATE', 'label' => 'Pembaruan Profil'],
        ];

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $request->input('search', ''),
                'action' => $request->input('action', 'all'),
                'date' => $dateParam ?? '',
                'resolved_date' => $resolvedDate ?? '',
                'formatted_filter_date' => $formattedFilterDate,
                'is_today' => $isTodayFilter,
            ],
            'actionsList' => $actionsList,
            'todayDate' => Carbon::today()->toDateString(),
            'totalCount' => ActivityLog::count(),
            'todayCount' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
        ]);
    }
}

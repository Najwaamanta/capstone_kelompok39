<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the Administrator dashboard.
     */
    public function index(Request $request): Response
    {
        // 1. Stat cards
        $penggunaAktif = User::where('status', 'aktif')->count();
        $aktifHariIni = User::where('status', 'aktif')
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '>=', Carbon::today())
            ->count();
        $menungguAktivasi = User::where('status', 'perlu_aktivasi')->count();
        $akunNonaktif = User::where('status', 'nonaktif')->count();

        // 2. Role distribution
        $adminTokoCount = User::where('role', 'admin_toko')->where('status', 'aktif')->count();
        $adminGudangCount = User::where('role', 'admin_gudang')->where('status', 'aktif')->count();
        $adminKonveksiCount = User::where('role', 'admin_konveksi')->where('status', 'aktif')->count();

        // Sample avatars/names for distribution cards
        $adminTokoSample = User::where('role', 'admin_toko')->where('status', 'aktif')->latest()->first();
        $adminGudangSample = User::where('role', 'admin_gudang')->where('status', 'aktif')->latest()->first();
        $adminKonveksiSample = User::where('role', 'admin_konveksi')->where('status', 'aktif')->latest()->first();

        $distribusi = [
            [
                'id' => 'admin_toko',
                'name' => 'Admin Toko',
                'count' => $adminTokoCount,
                'status' => 'Aktif',
                'avatar_initials' => $adminTokoSample ? $adminTokoSample->initials : 'NP',
                'color' => 'rose',
            ],
            [
                'id' => 'admin_gudang',
                'name' => 'Admin Gudang',
                'count' => $adminGudangCount,
                'status' => 'Aktif',
                'avatar_initials' => $adminGudangSample ? $adminGudangSample->initials : 'RP',
                'color' => 'indigo',
            ],
            [
                'id' => 'admin_konveksi',
                'name' => 'Admin Konveksi',
                'count' => $adminKonveksiCount,
                'status' => 'Aktif',
                'avatar_initials' => $adminKonveksiSample ? $adminKonveksiSample->initials : 'SR',
                'color' => 'amber',
            ],
        ];

        // 3. Pending activation users list for quick review
        $pendingUsers = User::where('status', 'perlu_aktivasi')
            ->latest()
            ->take(5)
            ->get();

        // 4. Recent activities
        $recentActivities = ActivityLog::with(['user', 'targetUser'])
            ->latest()
            ->take(6)
            ->get();

        // Formatted Indonesian Date e.g. "SENIN, 23 JUNI 2025"
        Carbon::setLocale('id');
        $currentDateIndo = strtoupper(Carbon::now()->isoFormat('dddd, D MMMM Y'));

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pengguna_aktif' => $penggunaAktif,
                'aktif_hari_ini' => $aktifHariIni,
                'menunggu_aktivasi' => $menungguAktivasi,
                'akun_nonaktif' => $akunNonaktif,
            ],
            'distribusi' => $distribusi,
            'pendingUsers' => $pendingUsers,
            'recentActivities' => $recentActivities,
            'formattedDate' => $currentDateIndo,
        ]);
    }
}

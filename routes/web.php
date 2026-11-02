<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PasswordResetRequestController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Gudang\GudangDashboardController;
use App\Http\Controllers\Gudang\GudangNegotiationController;
use App\Http\Controllers\Gudang\GudangNotificationController;
use App\Http\Controllers\Gudang\GudangOrderController;
use App\Http\Controllers\Gudang\GudangProfileController;
use App\Http\Controllers\Gudang\GudangProgresKonveksiController;
use App\Http\Controllers\Gudang\GudangQualityControlController;
use App\Http\Controllers\Toko\TokoDashboardController;
use App\Http\Controllers\Toko\TokoNegotiationController;
use App\Http\Controllers\Toko\TokoNotificationController;
use App\Http\Controllers\Toko\TokoOrderController;
use App\Http\Controllers\Toko\TokoProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin_toko') {
            return redirect()->route('toko.dashboard');
        } elseif ($user->role === 'admin_gudang') {
            return redirect()->route('gudang.dashboard');
        }
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Lupa Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'submitRequest'])->name('password.forgot.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Administrator Routes
    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Notifications (pending requests)
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Password Reset Requests (dari admin)
        Route::post('/password-reset-requests/{passwordResetRequest}/resolve', [PasswordResetRequestController::class, 'resolve'])->name('password-reset.resolve');
        Route::post('/password-reset-requests/{passwordResetRequest}/cancel', [PasswordResetRequestController::class, 'cancel'])->name('password-reset.cancel');
    });

    // ==========================================
    // Modul Admin Toko (Develop 2)
    // ==========================================
    Route::prefix('toko')->name('toko.')->middleware('role:admin_toko')->group(function () {
        // Dashboard
        Route::get('/dashboard', [TokoDashboardController::class, 'index'])->name('dashboard');

        // Pesanan (US-T05, US-T06, US-T07)
        Route::get('/orders', [TokoOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [TokoOrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{order}/cancel', [TokoOrderController::class, 'cancel'])->name('orders.cancel');

        // Negosiasi Tanggal (US-N01, US-N02)
        Route::post('/orders/{order}/negotiate', [TokoNegotiationController::class, 'propose'])->name('orders.negotiate');
        Route::post('/orders/{order}/negotiations/{negotiation}/respond', [TokoNegotiationController::class, 'respond'])->name('orders.negotiations.respond');

        // Notifikasi (US-T08)
        Route::get('/notifications', [TokoNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [TokoNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [TokoNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

        // Profil (US-T09)
        Route::get('/profile', [TokoProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [TokoProfileController::class, 'update'])->name('profile.update');
    });

    // ==========================================
    // Modul Admin Gudang (Dashboard & Operasional)
    // ==========================================
    Route::prefix('gudang')->name('gudang.')->middleware('role:admin_gudang')->group(function () {
        // Dashboard Gudang
        Route::get('/dashboard', [GudangDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', function () { return redirect()->route('gudang.dashboard'); });

        // Pesanan Gudang (US-G01, US-G02, US-G03, US-G04, US-G05)
        Route::get('/orders', [GudangOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/approve', [GudangOrderController::class, 'approve'])->name('orders.approve');
        Route::post('/orders/{order}/schedule', [GudangOrderController::class, 'updateSchedule'])->name('orders.schedule');
        Route::post('/orders/{order}/negotiate', [GudangOrderController::class, 'negotiate'])->name('orders.negotiate');
        // Respons negosiasi dari Toko (US-G03)
        Route::post('/orders/{order}/negotiations/{negotiation}/respond', [GudangNegotiationController::class, 'respond'])->name('orders.negotiations.respond');

        // Navigasi Ruang Kerja Sesuai Figma
        Route::get('/persediaan', function () { return \Inertia\Inertia::render('Gudang/Persediaan'); })->name('persediaan');
        Route::get('/apparel', function () { return \Inertia\Inertia::render('Gudang/Apparel'); })->name('apparel');
        Route::get('/progres-konveksi', [GudangProgresKonveksiController::class, 'index'])->name('progres-konveksi');
        Route::get('/quality-control', [GudangQualityControlController::class, 'index'])->name('quality-control');
        Route::post('/quality-control/{order}', [GudangQualityControlController::class, 'store'])->name('quality-control.store');

        // Notifikasi & Profil Admin Gudang
        Route::get('/notifications', [GudangNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-all-read', [GudangNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{notification}/read', [GudangNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/profile', [GudangProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [GudangProfileController::class, 'update'])->name('profile.update');
    });
});


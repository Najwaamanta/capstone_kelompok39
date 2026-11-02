<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PasswordResetRequestController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Konveksi\DashboardController as KonveksiDashboardController;
use App\Http\Controllers\Konveksi\NotificationController as KonveksiNotificationController;
use App\Http\Controllers\Konveksi\PekerjaanController as KonveksiPekerjaanController;
use App\Http\Controllers\Konveksi\ProfileController as KonveksiProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin_konveksi') {
            return redirect()->route('konveksi.dashboard');
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

    // Admin Konveksi Routes
    Route::prefix('konveksi')->name('konveksi.')->middleware('role:admin_konveksi')->group(function () {
        Route::get('/dashboard', [KonveksiDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pekerjaan', [KonveksiPekerjaanController::class, 'index'])->name('pekerjaan.index');
        Route::post('/pekerjaan/{id}/status', [KonveksiPekerjaanController::class, 'updateStatus'])->name('pekerjaan.update-status');
        Route::get('/notifikasi', [KonveksiNotificationController::class, 'index'])->name('notifikasi.index');
        Route::get('/profil', [KonveksiProfileController::class, 'index'])->name('profil.index');
        Route::put('/profil', [KonveksiProfileController::class, 'update'])->name('profil.update');
    });
});


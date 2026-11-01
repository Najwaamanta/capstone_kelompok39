<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan halaman form "Lupa Password"
     */
    public function showForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Proses pengiriman permintaan reset password
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Selalu tampilkan halaman sukses meski email tidak ditemukan
        // (untuk keamanan — hindari user enumeration)
        if ($user) {
            // Batalkan permintaan pending sebelumnya jika ada
            PasswordResetRequest::where('user_id', $user->id)
                ->where('status', PasswordResetRequest::STATUS_PENDING)
                ->update(['status' => PasswordResetRequest::STATUS_CANCELLED]);

            // Buat permintaan baru
            PasswordResetRequest::create([
                'user_id'    => $user->id,
                'status'     => PasswordResetRequest::STATUS_PENDING,
                'ip_address' => $request->ip(),
            ]);
        }

        return Inertia::render('Auth/ForgotPasswordSent', [
            'email' => $request->email,
        ]);
    }
}

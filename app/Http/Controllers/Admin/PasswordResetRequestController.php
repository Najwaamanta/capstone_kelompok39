<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetRequestController extends Controller
{
    /**
     * Ambil daftar permintaan reset password (pending)
     * — digunakan oleh NotificationController
     */
    public static function getPendingRequests(int $limit = 20)
    {
        Carbon::setLocale('id');

        return PasswordResetRequest::with('user')
            ->pending()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($req) {
                $req->formatted_created_at = $req->created_at->isoFormat('D MMMM Y, HH:mm');
                $req->time_ago = $req->created_at->diffForHumans();
                return $req;
            });
    }

    /**
     * Reset password pengguna dan tandai permintaan sebagai resolved
     */
    public function resolve(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = $passwordResetRequest->user;

        // Reset password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Tandai permintaan sebagai resolved
        $passwordResetRequest->update([
            'status'      => PasswordResetRequest::STATUS_RESOLVED,
            'admin_notes' => $request->admin_notes,
            'resolved_at' => now(),
        ]);

        return redirect()->back()->with('success', "Password {$user->name} berhasil direset.");
    }

    /**
     * Batalkan permintaan reset password
     */
    public function cancel(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        $passwordResetRequest->update([
            'status'      => PasswordResetRequest::STATUS_CANCELLED,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()->with('success', 'Permintaan reset password telah dibatalkan.');
    }
}

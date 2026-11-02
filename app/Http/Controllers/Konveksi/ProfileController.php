<?php

namespace App\Http\Controllers\Konveksi;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display Konveksi Admin profile matching design.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        Carbon::setLocale('id');
        $accountSince = $user->created_at
            ? $user->created_at->isoFormat('MMMM Y')
            : 'Januari 2025';

        return Inertia::render('Konveksi/Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '0812 3456 7890',
                'role' => $user->role,
                'role_label' => $user->role_label,
                'initials' => $user->initials,
            ],
            'accountSince' => $accountSince,
        ]);
    }

    /**
     * Update Konveksi Admin personal information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar oleh pengguna lain.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        ActivityLog::record($user->id, 'PROFILE_UPDATE', "Informasi profil {$user->name} berhasil diperbarui.");

        return back()->with('success', 'Perubahan informasi pribadi berhasil disimpan!');
    }

    /**
     * Update password from Keamanan Akun modal.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record($user->id, 'PASSWORD_CHANGE', "Kata sandi akun {$user->name} berhasil diubah.");

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui!');
    }
}

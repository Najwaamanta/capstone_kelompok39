<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class GudangProfileController extends Controller
{
    public function index(): Response
    {
        Carbon::setLocale('id');
        $user = Auth::user();

        return Inertia::render('Gudang/Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '0812 3456 7890',
                'role' => $user->role,
                'role_label' => 'Admin Gudang',
                'status' => $user->status,
                'status_label' => $user->status_label,
                'avatar' => $user->avatar,
                'initials' => $user->initials ?: 'RP',
                'formatted_created_at' => $user->created_at ? $user->created_at->isoFormat('MMMM Y') : 'Januari 2025',
                'formatted_last_active' => 'Aktif saat ini',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:25'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengubah password.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil Admin Gudang Anda berhasil diperbarui!');
    }
}


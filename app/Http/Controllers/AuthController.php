<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin_konveksi') {
                return redirect()->route('konveksi.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        if ($user->status === 'perlu_aktivasi') {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah terdaftar namun masih menunggu verifikasi dan penetapan role oleh Administrator.',
            ]);
        }

        if ($user->status === 'nonaktif') {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan atau ditangguhkan. Silakan hubungi Administrator.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update([
            'last_active_at' => Carbon::now(),
        ]);

        ActivityLog::record($user->id, 'LOGIN', "Pengguna {$user->name} ({$user->role_label}) berhasil masuk.");

        if ($user->role === 'administrator') {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->role === 'admin_konveksi') {
            return redirect()->intended(route('konveksi.dashboard'));
        }

        // Default fallback
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Show registration page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin_konveksi') {
                return redirect()->route('konveksi.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Auth/Register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'requested_role' => ['required', 'string', 'in:admin_toko,admin_gudang,admin_konveksi'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'requested_role.required' => 'Permohonan Unit / Role wajib dipilih.',
            'requested_role.in' => 'Pilihan role tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui Ketentuan Penggunaan dan Kebijakan Privasi.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['requested_role'],
            'requested_role' => $validated['requested_role'],
            'status' => 'perlu_aktivasi',
            'created_at' => Carbon::now(),
        ]);

        ActivityLog::record(
            null,
            'REGISTER',
            "Pendaftaran mandiri baru oleh {$user->name} ({$user->email}) untuk permohonan role {$user->role_label}.",
            $user->id
        );

        return redirect()->route('login')->with(
            'success',
            'Pendaftaran berhasil! Akun Anda telah disimpan dan menunggu aktivasi serta penetapan hak akses oleh Administrator.'
        );
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record($user->id, 'LOGOUT', "Pengguna {$user->name} keluar dari sistem.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari akun.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user status is active
        if ($user->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'perlu_aktivasi'
                ? 'Akun Anda sedang menunggu persetujuan dari Administrator.'
                : 'Akun Anda telah dinonaktifkan oleh Administrator.';

            return redirect()->route('login')->with('error', $message);
        }

        // Check role permission
        if (!empty($roles) && !in_array($user->role, $roles)) {
            if ($user->role === 'admin_konveksi') {
                return redirect()->route('konveksi.dashboard')->with(
                    'error',
                    'Akun Anda tidak memiliki hak akses ke halaman tersebut.'
                );
            }

            if ($user->role === 'administrator') {
                return redirect()->route('admin.dashboard')->with(
                    'error',
                    'Akun Anda tidak memiliki hak akses ke halaman tersebut.'
                );
            }

            if ($user->role === 'admin_toko') {
                return redirect()->route('toko.dashboard')->with(
                    'error',
                    'Akun Anda tidak memiliki hak akses ke halaman tersebut.'
                );
            }

            if ($user->role === 'admin_gudang') {
                return redirect()->route('gudang.dashboard')->with(
                    'error',
                    'Akun Anda tidak memiliki hak akses ke halaman tersebut.'
                );
            }

            // For unauthorized user roles
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'error',
                'Akun Anda (' . $user->name . ') tidak memiliki hak akses ke halaman tersebut.'
            );
        }

        return $next($request);
    }
}

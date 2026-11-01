<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users with search, role and status filter, and pagination.
     */
    public function index(Request $request): Response
    {
        $query = User::query()->latest();

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Active Today filter
        $activeToday = $request->boolean('active_today');
        if ($activeToday) {
            $query->where('status', 'aktif')
                  ->whereNotNull('last_active_at')
                  ->where('last_active_at', '>=', Carbon::today());
        }

        $users = $query->paginate(10)->withQueryString();

        // Format dates in Indonesian format for table presentation
        $users->getCollection()->transform(function ($user) {
            Carbon::setLocale('id');
            $user->formatted_created_at = $user->created_at->isoFormat('D MMMM Y');
            return $user;
        });

        $rolesList = [
            ['value' => 'all', 'label' => 'Semua Role'],
            ['value' => 'administrator', 'label' => 'Administrator'],
            ['value' => 'admin_toko', 'label' => 'Admin Toko'],
            ['value' => 'admin_gudang', 'label' => 'Admin Gudang'],
            ['value' => 'admin_konveksi', 'label' => 'Admin Konveksi'],
        ];

        $statusList = [
            ['value' => 'all', 'label' => 'Semua Status'],
            ['value' => 'aktif', 'label' => 'Aktif'],
            ['value' => 'perlu_aktivasi', 'label' => 'Perlu Aktivasi'],
            ['value' => 'nonaktif', 'label' => 'Nonaktif'],
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search', ''),
                'role' => $request->input('role', 'all'),
                'status' => $request->input('status', 'all'),
                'active_today' => $activeToday,
            ],
            'rolesList' => $rolesList,
            'statusList' => $statusList,
            'totalUsersCount' => User::count(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:administrator,admin_toko,admin_gudang,admin_konveksi'],
            'status' => ['required', 'string', 'in:aktif,perlu_aktivasi,nonaktif'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan.',
            'role.required' => 'Role wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'requested_role' => $validated['role'],
            'status' => $validated['status'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'created_at' => Carbon::now(),
        ]);

        ActivityLog::record(
            Auth::id(),
            'USER_CREATED',
            "Administrator membuat akun baru untuk {$user->name} ({$user->email}) dengan role {$user->role_label}.",
            $user->id
        );

        return redirect()->back()->with('success', "Pengguna {$user->name} berhasil ditambahkan!");
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'in:administrator,admin_toko,admin_gudang,admin_konveksi'],
            'status' => ['required', 'string', 'in:aktif,perlu_aktivasi,nonaktif'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'role.required' => 'Role wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $oldRole = $user->role_label;
        $oldStatus = $user->status_label;

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];
        $user->phone = $validated['phone'] ?? null;
        $user->notes = $validated['notes'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLog::record(
            Auth::id(),
            'USER_UPDATED',
            "Memperbarui data {$user->name} (Role: {$oldRole} -> {$user->role_label}, Status: {$oldStatus} -> {$user->status_label}).",
            $user->id
        );

        return redirect()->back()->with('success', "Perubahan akun {$user->name} berhasil disimpan!");
    }

    /**
     * Quickly activate a user and set role.
     */
    public function activate(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:administrator,admin_toko,admin_gudang,admin_konveksi'],
        ]);

        $roleToAssign = $validated['role'] ?? $user->requested_role ?? $user->role;

        $user->update([
            'status' => 'aktif',
            'role' => $roleToAssign,
        ]);

        ActivityLog::record(
            Auth::id(),
            'ACTIVATED',
            "Aktivasi akun disetujui untuk {$user->name} dengan penetapan role {$user->role_label}.",
            $user->id
        );

        return redirect()->back()->with('success', "Akun {$user->name} telah berhasil diaktifkan dengan role {$user->role_label}!");
    }

    /**
     * Reject a user registration request and set status to nonaktif.
     */
    public function reject(Request $request, User $user)
    {
        $user->update([
            'status' => 'nonaktif',
        ]);

        ActivityLog::record(
            Auth::id(),
            'REGISTRATION_REJECTED',
            "Permohonan pendaftaran akun {$user->name} ditolak (Status akun disetel Nonaktif).",
            $user->id
        );

        return redirect()->back()->with('success', "Permohonan registrasi akun {$user->name} berhasil ditolak.");
    }

    /**
     * Toggle status between aktif and nonaktif.
     */
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        ActivityLog::record(
            Auth::id(),
            'STATUS_CHANGED',
            "Status akun {$user->name} diubah menjadi {$user->status_label}.",
            $user->id
        );

        return redirect()->back()->with('success', "Status akun {$user->name} diubah menjadi {$user->status_label}.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record(
            Auth::id(),
            'USER_DELETED',
            "Akun {$name} telah dihapus dari sistem.",
            null
        );

        return redirect()->back()->with('success', "Akun {$name} berhasil dihapus.");
    }
}

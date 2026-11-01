<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Administrator Utama
        $admin = User::create([
            'name' => 'Dian Sastro',
            'email' => 'admin@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'administrator',
            'requested_role' => 'administrator',
            'status' => 'aktif',
            'phone' => '081234567890',
            'last_active_at' => Carbon::now(),
            'created_at' => Carbon::create(2025, 1, 10, 8, 0, 0),
        ]);

        ActivityLog::record($admin->id, 'SYSTEM_INIT', 'Sistem LilieFlow diinisialisasi dan Administrator dibuat.');

        // 2. Admin Toko (2 Aktif)
        $toko1 = User::create([
            'name' => 'Ayu Lestari',
            'email' => 'ayu.toko@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_toko',
            'requested_role' => 'admin_toko',
            'status' => 'aktif',
            'phone' => '081234567801',
            'last_active_at' => Carbon::now()->subMinutes(12),
            'created_at' => Carbon::create(2025, 2, 1, 16, 45, 0),
        ]);

        $toko2 = User::create([
            'name' => 'Bambang Sutrisno',
            'email' => 'bambang.toko@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_toko',
            'requested_role' => 'admin_toko',
            'status' => 'aktif',
            'phone' => '081234567802',
            'last_active_at' => Carbon::now()->subMinutes(45),
            'created_at' => Carbon::create(2025, 2, 15, 10, 0, 0),
        ]);

        // 3. Admin Gudang (3 Aktif)
        $gudang1 = User::create([
            'name' => 'Raka Pratama',
            'email' => 'raka@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
            'requested_role' => 'admin_gudang',
            'status' => 'aktif',
            'phone' => '081234567803',
            'last_active_at' => Carbon::now()->subMinutes(8),
            'created_at' => Carbon::create(2025, 3, 13, 9, 30, 0),
        ]);

        $gudang2 = User::create([
            'name' => 'Fajar Nugraha',
            'email' => 'fajar.gudang@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
            'requested_role' => 'admin_gudang',
            'status' => 'aktif',
            'phone' => '081234567804',
            'last_active_at' => Carbon::now()->subHours(2),
            'created_at' => Carbon::create(2025, 3, 20, 11, 15, 0),
        ]);

        $gudang3 = User::create([
            'name' => 'Bayu Wicaksono',
            'email' => 'bayu.gudang@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
            'requested_role' => 'admin_gudang',
            'status' => 'aktif',
            'phone' => '081234567805',
            'last_active_at' => Carbon::now()->subMinutes(25),
            'created_at' => Carbon::create(2025, 4, 5, 14, 0, 0),
        ]);

        // 4. Admin Konveksi (4 Aktif)
        $konveksi1 = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@sinarjaya.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'aktif',
            'phone' => '081234567806',
            'last_active_at' => Carbon::now()->subMinutes(18),
            'created_at' => Carbon::create(2025, 3, 13, 14, 20, 0),
        ]);

        $konveksi2 = User::create([
            'name' => 'Hendra Setiawan',
            'email' => 'hendra@berkahkonveksi.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'aktif',
            'phone' => '081234567807',
            'last_active_at' => Carbon::now()->subMinutes(35),
            'created_at' => Carbon::create(2025, 3, 25, 13, 0, 0),
        ]);

        $konveksi3 = User::create([
            'name' => 'Dewi Sartika',
            'email' => 'dewi@kreasiutama.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'aktif',
            'phone' => '081234567808',
            'last_active_at' => Carbon::now()->subHours(1),
            'created_at' => Carbon::create(2025, 4, 1, 9, 0, 0),
        ]);

        $konveksi4 = User::create([
            'name' => 'Agus Pratama',
            'email' => 'agus@mitragarmen.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'aktif',
            'phone' => '081234567809',
            'last_active_at' => Carbon::now()->subMinutes(50),
            'created_at' => Carbon::create(2025, 4, 18, 15, 30, 0),
        ]);

        // 5. Akun Perlu Aktivasi (Pendaftar baru yang menunggu verifikasi admin)
        $pending1 = User::create([
            'name' => 'Nadia Putri',
            'email' => 'nadia@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_toko',
            'requested_role' => 'admin_toko',
            'status' => 'perlu_aktivasi',
            'phone' => '081234567810',
            'created_at' => Carbon::create(2026, 1, 25, 10, 15, 0),
        ]);

        $pending2 = User::create([
            'name' => 'Dimas Anggara',
            'email' => 'dimas@liliebelle.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin_gudang',
            'requested_role' => 'admin_gudang',
            'status' => 'perlu_aktivasi',
            'phone' => '081234567811',
            'created_at' => Carbon::create(2026, 2, 1, 14, 20, 0),
        ]);

        $pending3 = User::create([
            'name' => 'Citra Kirana',
            'email' => 'citra@primakonveksi.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'perlu_aktivasi',
            'phone' => '081234567812',
            'created_at' => Carbon::create(2026, 2, 10, 9, 45, 0),
        ]);

        // 6. Akun Nonaktif (Ditangguhkan)
        $nonaktif1 = User::create([
            'name' => 'Ayu Lestari',
            'email' => 'ayu@mekarkonveksi.id',
            'password' => Hash::make('password'),
            'role' => 'admin_konveksi',
            'requested_role' => 'admin_konveksi',
            'status' => 'nonaktif',
            'phone' => '081234567813',
            'notes' => 'Akses ditangguhkan sementara karena pergantian vendor.',
            'created_at' => Carbon::create(2025, 2, 9, 11, 0, 0),
        ]);

        // Activity Logs
        ActivityLog::record($admin->id, 'ROLE_ASSIGNED', 'Role admin_toko disetujui untuk Ayu Lestari', $toko1->id);
        ActivityLog::record($admin->id, 'ROLE_ASSIGNED', 'Role admin_gudang disetujui untuk Raka Pratama', $gudang1->id);
        ActivityLog::record($admin->id, 'USER_STATUS', 'Akun Ayu Lestari (ayu@mekarkonveksi.id) ditangguhkan', $nonaktif1->id);
        ActivityLog::record(null, 'REGISTER', 'Pendaftaran mandiri akun baru Nadia Putri (Permohonan: Admin Toko)', $pending1->id);
        ActivityLog::record(null, 'REGISTER', 'Pendaftaran mandiri akun baru Dimas Anggara (Permohonan: Admin Gudang)', $pending2->id);
        ActivityLog::record(null, 'REGISTER', 'Pendaftaran mandiri akun baru Citra Kirana (Permohonan: Admin Konveksi)', $pending3->id);
    }
}

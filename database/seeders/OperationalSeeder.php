<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\DateNegotiation;
use App\Models\Konveksi;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderStage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OperationalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Mitra Konveksi
        $konveksis = [
            [
                'nama' => 'Konveksi Sinar Jaya',
                'penanggung_jawab' => 'Siti Rahma',
                'telepon' => '081234567806',
                'alamat' => 'Kawasan Industri Maju Jaya Blok B4, Bandung',
            ],
            [
                'nama' => 'Konveksi Mekar',
                'penanggung_jawab' => 'Ayu Lestari',
                'telepon' => '081234567813',
                'alamat' => 'Jl. Cibaduyut No. 88, Bandung',
            ],
            [
                'nama' => 'Mitra Garmen',
                'penanggung_jawab' => 'Agus Pratama',
                'telepon' => '081234567809',
                'alamat' => 'Jl. Raya Soreang Km 12, Kab. Bandung',
            ],
            [
                'nama' => 'Berkah Konveksi',
                'penanggung_jawab' => 'Hendra Setiawan',
                'telepon' => '081234567807',
                'alamat' => 'Jl. Cigondewah Rahayu No. 45, Bandung',
            ],
            [
                'nama' => 'Kreasi Utama',
                'penanggung_jawab' => 'Dewi Sartika',
                'telepon' => '081234567808',
                'alamat' => 'Jl. Soekarno Hatta No. 210, Bandung',
            ],
        ];

        foreach ($konveksis as $data) {
            Konveksi::firstOrCreate(['nama' => $data['nama']], $data);
        }

        $sinarJaya = Konveksi::where('nama', 'Konveksi Sinar Jaya')->first();
        $mekar = Konveksi::where('nama', 'Konveksi Mekar')->first();

        // Cari user Toko & Gudang (utamakan Nadia Putri)
        $tokoUser = User::where('name', 'like', '%Nadia%')->first()
            ?? User::where('role', 'admin_toko')->first()
            ?? User::first();

        $gudangUser = User::where('role', 'admin_gudang')->first() ?? User::first();

        if (!$tokoUser || !$gudangUser) {
            return;
        }

        // ==============================================================
        // Sesuai UI Mockup:
        // ORD-2506-018: Maison Aurelia · DRS-A17 · 120 pcs · Produksi Konveksi · 28 Jun 2025
        // ORD-2506-017: Aluna Studio · BLS-K04 · 80 pcs · Negosiasi · 25 Jun 2025
        // ORD-2506-016: Ruang Rona · KMS-P21 · 200 pcs · QC Gudang · 24 Jun 2025
        // ORD-2506-015: Nara Boutique · SKT-M09 · 60 pcs · Selesai · 20 Jun 2025
        // ORD-2506-014: Kirana Mode · DRS-C12 · 90 pcs · Cancel · 18 Jun 2025
        // ==============================================================

        // 1. ORD-2506-018 (Maison Aurelia)
        $order18 = Order::updateOrCreate(
            ['id_pesanan' => 'ORD-2506-018'],
            [
                'nama_customer' => 'Maison Aurelia',
                'kode_seri' => 'DRS-A17',
                'jumlah' => 120,
                'target_selesai' => '2025-06-28',
                'estimasi_gudang' => '2025-06-27',
                'konveksi_id' => $sinarJaya->id,
                'status' => 'dalam_produksi',
                'dibuat_oleh' => $tokoUser->id,
                'catatan' => 'Bahan linen premium warna sage green dengan kancing bungkus rapi.',
                'created_at' => Carbon::parse('2025-06-21 10:04:00'),
                'updated_at' => Carbon::now()->subMinutes(8),
            ]
        );

        $this->createStagesForOrder($order18, [
            'pemotongan' => ['status' => 'selesai', 'mulai' => Carbon::parse('2025-06-21'), 'selesai' => Carbon::parse('2025-06-21')],
            'sablon' => ['status' => 'selesai', 'mulai' => Carbon::parse('2025-06-22'), 'selesai' => Carbon::parse('2025-06-23')],
            'konveksi' => ['status' => 'sedang_dikerjakan', 'mulai' => Carbon::parse('2025-06-23'), 'selesai' => Carbon::parse('2025-06-25')],
            'qc_gudang' => ['status' => 'belum_dimulai', 'mulai' => null, 'selesai' => Carbon::parse('2025-06-27')],
        ]);

        OrderHistory::firstOrCreate(
            ['order_id' => $order18->id, 'aksi' => 'Pesanan dan nota digital dibuat'],
            [
                'user_id' => $tokoUser->id,
                'role' => 'Admin Toko',
                'deskripsi' => 'Pesanan dan nota digital dibuat oleh Admin Toko.',
                'created_at' => Carbon::parse('2025-06-21 10:04:00'),
            ]
        );

        OrderHistory::firstOrCreate(
            ['order_id' => $order18->id, 'aksi' => 'Estimasi penyelesaian disepakati'],
            [
                'user_id' => $gudangUser->id,
                'role' => 'Admin Gudang',
                'deskripsi' => 'Estimasi: 27 Jun 2025',
                'created_at' => Carbon::parse('2025-06-22 15:40:00'),
            ]
        );

        OrderHistory::firstOrCreate(
            ['order_id' => $order18->id, 'aksi' => 'Status diperbarui menjadi Produksi Konveksi'],
            [
                'user_id' => $tokoUser->id,
                'role' => 'Admin Toko',
                'deskripsi' => 'Oleh Nadia Putri',
                'created_at' => Carbon::parse('2025-06-23 09:12:00'),
            ]
        );

        // 2. ORD-2506-017 (Aluna Studio)
        $order17 = Order::updateOrCreate(
            ['id_pesanan' => 'ORD-2506-017'],
            [
                'nama_customer' => 'Aluna Studio',
                'kode_seri' => 'BLS-K04',
                'jumlah' => 80,
                'target_selesai' => '2025-06-25',
                'estimasi_gudang' => '2025-06-26',
                'konveksi_id' => null,
                'status' => 'negosiasi',
                'dibuat_oleh' => $tokoUser->id,
                'catatan' => 'Blouse katun bordir leher.',
                'created_at' => Carbon::parse('2025-06-20 11:20:00'),
                'updated_at' => Carbon::now()->subMinutes(32),
            ]
        );

        $this->createStagesForOrder($order17);

        DateNegotiation::firstOrCreate(
            ['order_id' => $order17->id, 'role_pengaju' => 'admin_gudang'],
            [
                'diajukan_oleh' => $gudangUser->id,
                'estimasi_usulan' => '2025-06-26',
                'catatan' => 'Estimasi ORD-2506-017 diusulkan menjadi 26 Jun 2025.',
                'status' => 'menunggu',
                'created_at' => Carbon::now()->subMinutes(32),
            ]
        );

        // 3. ORD-2506-016 (Ruang Rona)
        $order16 = Order::updateOrCreate(
            ['id_pesanan' => 'ORD-2506-016'],
            [
                'nama_customer' => 'Ruang Rona',
                'kode_seri' => 'KMS-P21',
                'jumlah' => 200,
                'target_selesai' => '2025-06-24',
                'estimasi_gudang' => '2025-06-23',
                'konveksi_id' => $mekar->id,
                'status' => 'qc_gudang',
                'dibuat_oleh' => $tokoUser->id,
                'catatan' => 'Kemeja poplin panjang.',
                'created_at' => Carbon::parse('2025-06-18 14:00:00'),
                'updated_at' => Carbon::now()->subHours(2),
            ]
        );

        $this->createStagesForOrder($order16, [
            'pemotongan' => ['status' => 'selesai'],
            'sablon' => ['status' => 'selesai'],
            'konveksi' => ['status' => 'selesai'],
            'qc_gudang' => ['status' => 'sedang_dikerjakan', 'selesai' => Carbon::parse('2025-06-24')],
        ]);

        // 4. ORD-2506-015 (Nara Boutique)
        $order15 = Order::updateOrCreate(
            ['id_pesanan' => 'ORD-2506-015'],
            [
                'nama_customer' => 'Nara Boutique',
                'kode_seri' => 'SKT-M09',
                'jumlah' => 60,
                'target_selesai' => '2025-06-20',
                'estimasi_gudang' => '2025-06-20',
                'konveksi_id' => $sinarJaya->id,
                'status' => 'selesai',
                'dibuat_oleh' => $tokoUser->id,
                'created_at' => Carbon::parse('2025-06-15 09:30:00'),
                'updated_at' => Carbon::parse('2025-06-20 14:20:00'),
            ]
        );

        $this->createStagesForOrder($order15, [
            'pemotongan' => ['status' => 'selesai'],
            'sablon' => ['status' => 'selesai'],
            'konveksi' => ['status' => 'selesai'],
            'qc_gudang' => ['status' => 'selesai'],
        ]);

        // 5. ORD-2506-014 (Kirana Mode) - Cancel
        $order14 = Order::updateOrCreate(
            ['id_pesanan' => 'ORD-2506-014'],
            [
                'nama_customer' => 'Kirana Mode',
                'kode_seri' => 'DRS-C12',
                'jumlah' => 90,
                'target_selesai' => '2025-06-18',
                'estimasi_gudang' => null,
                'konveksi_id' => null,
                'status' => 'dibatalkan',
                'dibuat_oleh' => $tokoUser->id,
                'alasan_pembatalan' => 'Dibatalkan atas permintaan customer.',
                'created_at' => Carbon::parse('2025-06-12 10:00:00'),
                'updated_at' => Carbon::parse('2025-06-13 11:00:00'),
            ]
        );

        $this->createStagesForOrder($order14);

        // Notifikasi Sesuai Screenshot Figma
        AppNotification::firstOrCreate(
            ['order_id' => $order18->id, 'judul' => 'Progres konveksi diperbarui'],
            [
                'user_id' => null,
                'target_role' => null,
                'tipe' => 'progres_diperbarui',
                'pesan' => 'ORD-2506-018 masuk tahap Produksi Konveksi.',
                'dibaca' => false,
                'created_at' => Carbon::now()->subMinutes(8),
            ]
        );

        AppNotification::firstOrCreate(
            ['order_id' => $order17->id, 'judul' => 'Negosiasi tanggal menunggu respons'],
            [
                'user_id' => null,
                'target_role' => null,
                'tipe' => 'negosiasi_masuk',
                'pesan' => 'Estimasi ORD-2506-017 diusulkan menjadi 26 Jun 2025.',
                'dibaca' => false,
                'created_at' => Carbon::now()->subMinutes(32),
            ]
        );

        AppNotification::firstOrCreate(
            ['order_id' => $order18->id, 'judul' => 'Pesanan baru diterima'],
            [
                'user_id' => null,
                'target_role' => null,
                'tipe' => 'pesanan_baru',
                'pesan' => 'ORD-2506-018 dari Maison Aurelia telah diteruskan.',
                'dibaca' => true,
                'created_at' => Carbon::yesterday()->setTime(16, 42),
            ]
        );

        AppNotification::firstOrCreate(
            ['order_id' => $order15->id, 'judul' => 'Hasil QC: Lulus'],
            [
                'user_id' => null,
                'target_role' => null,
                'tipe' => 'hasil_qc',
                'pesan' => 'Seluruh 60 pcs ORD-2506-015 lolos pemeriksaan.',
                'dibaca' => true,
                'created_at' => Carbon::yesterday()->setTime(14, 20),
            ]
        );
    }

    private function createStagesForOrder(Order $order, array $custom = []): void
    {
        $stages = ['pemotongan', 'sablon', 'konveksi', 'qc_gudang'];
        foreach ($stages as $stageKey) {
            $status = $custom[$stageKey]['status'] ?? 'belum_dimulai';
            $mulai = isset($custom[$stageKey]['mulai']) ? $custom[$stageKey]['mulai']->format('Y-m-d') : null;
            $selesai = isset($custom[$stageKey]['selesai']) ? $custom[$stageKey]['selesai']->format('Y-m-d') : null;

            OrderStage::updateOrCreate(
                ['order_id' => $order->id, 'tahap' => $stageKey],
                [
                    'status_tahap' => $status,
                    'mulai' => $mulai,
                    'estimasi_selesai' => $selesai,
                ]
            );
        }
    }
}

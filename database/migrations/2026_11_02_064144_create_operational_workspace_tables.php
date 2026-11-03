<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Mitra Konveksi
        Schema::create('konveksis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('penanggung_jawab')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        // 2. Orders (Pesanan)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesanan')->unique(); // ORD-YYMM-XXX
            $table->string('nama_customer');
            $table->string('kode_seri');
            $table->unsignedInteger('jumlah');
            $table->date('target_selesai');
            $table->date('estimasi_gudang')->nullable();
            $table->foreignId('konveksi_id')->nullable()->constrained('konveksis')->nullOnDelete();
            $table->string('status')->default('menunggu_persetujuan'); // menunggu_persetujuan, negosiasi, dalam_produksi, qc_gudang, selesai, dibatalkan
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->timestamps();
        });

        // 3. Tahapan Produksi Pesanan
        Schema::create('order_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('tahap'); // pemotongan, sablon, konveksi, qc_gudang
            $table->date('mulai')->nullable();
            $table->date('estimasi_selesai')->nullable();
            $table->string('status_tahap')->default('belum_dimulai'); // belum_dimulai, sedang_dikerjakan, selesai
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 4. Negosiasi Tanggal
        Schema::create('date_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('role_pengaju'); // admin_toko, admin_gudang
            $table->date('estimasi_usulan');
            $table->text('catatan');
            $table->string('status')->default('menunggu'); // menunggu, disetujui, ditolak
            $table->foreignId('direspons_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_respons')->nullable();
            $table->timestamps();
        });

        // 5. Riwayat Aktivitas Pesanan (Audit Trail)
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role');
            $table->string('aksi');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 6. Notifikasi Aplikasi
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('target_role')->nullable(); // admin_toko, admin_gudang
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->string('tipe'); // pesanan_baru, progres_diperbarui,  siasi_masuk, negosiasi_disetujui, hasil_qc, pesanan_dibatalkan
            $table->string('judul');
            $table->text('pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('order_histories');
        Schema::dropIfExists('date_negotiations');
        Schema::dropIfExists('order_stages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('konveksis');
    }
};

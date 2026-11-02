<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_routes(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
        $this->get('/forgot-password')->assertStatus(200);
    }

    public function test_admin_routes(): void
    {
        $admin = User::where('role', 'administrator')->first();
        $this->actingAs($admin);

        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/users')->assertStatus(200);
        $this->get('/admin/activity-logs')->assertStatus(200);
        $this->get('/admin/notifications')->assertStatus(200);
        $this->get('/admin/profile')->assertStatus(200);
    }

    public function test_toko_routes(): void
    {
        $toko = User::where('role', 'admin_toko')->where('status', 'aktif')->first();
        $this->actingAs($toko);

        $this->get('/toko/dashboard')->assertStatus(200);
        $this->get('/toko/orders')->assertStatus(200);
        $this->get('/toko/notifications')->assertStatus(200);
        $this->get('/toko/profile')->assertStatus(200);
    }

    public function test_gudang_routes(): void
    {
        $gudang = User::where('role', 'admin_gudang')->where('status', 'aktif')->first();
        $this->actingAs($gudang);

        $this->get('/gudang/dashboard')->assertStatus(200);
        $this->get('/gudang/orders')->assertStatus(200);
        $this->get('/gudang/persediaan')->assertStatus(200);
        $this->get('/gudang/apparel')->assertStatus(200);
        $this->get('/gudang/quality-control')->assertStatus(200);
        $this->get('/gudang/notifications')->assertStatus(200);
        $this->get('/gudang/profile')->assertStatus(200);
    }

    public function test_quality_control_actions(): void
    {
        $gudang = User::where('role', 'admin_gudang')->where('status', 'aktif')->first();
        $this->actingAs($gudang);

        $order = \App\Models\Order::where('id_pesanan', 'ORD-2506-016')->first();

        // Test QC Tandai Lulus
        $response = $this->post("/gudang/quality-control/{$order->id}", [
            'action' => 'lulus',
            'jumlah_lulus' => 200,
            'jumlah_ditolak' => 0,
            'catatan' => 'Semua jahitan rapi dan lulus standar.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('selesai', $order->fresh()->status);
    }

    public function test_role_protection(): void
    {
        $toko = User::where('role', 'admin_toko')->where('status', 'aktif')->first();
        $this->actingAs($toko);

        // Toko user cannot access administrator dashboard
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
}

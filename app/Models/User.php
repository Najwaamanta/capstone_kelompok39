<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'requested_role',
        'status',
        'phone',
        'avatar',
        'notes',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Appended attributes.
     */
    protected $appends = [
        'role_label',
        'status_label',
        'initials',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'administrator' => 'Administrator',
            'admin_toko' => 'Admin Toko',
            'admin_gudang' => 'Admin Gudang',
            'admin_konveksi' => 'Admin Konveksi',
            default => ucfirst(str_replace('_', ' ', $this->role ?? 'User')),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif' => 'Aktif',
            'perlu_aktivasi' => 'Perlu Aktivasi',
            'nonaktif' => 'Nonaktif',
            default => ucfirst($this->status ?? 'Perlu Aktivasi'),
        };
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split("/\s+/", trim($this->name ?? ''));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name ?? 'LF', 0, 2));
    }

    public function isAdministrator(): bool
    {
        return $this->role === 'administrator';
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}

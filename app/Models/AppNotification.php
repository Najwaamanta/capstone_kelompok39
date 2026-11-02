<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'target_role',
        'order_id',
        'tipe',
        'judul',
        'pesan',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function scopeForUserOrRole(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($sub) use ($user) {
                  $sub->whereNull('user_id')
                      ->where('target_role', $user->role);
              });
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('dibaca', false);
    }

    public static function send(
        ?int $userId,
        ?string $targetRole,
        ?int $orderId,
        string $tipe,
        string $judul,
        string $pesan
    ): self {
        return self::create([
            'user_id' => $userId,
            'target_role' => $targetRole,
            'order_id' => $orderId,
            'tipe' => $tipe,
            'judul' => $judul,
            'pesan' => $pesan,
            'dibaca' => false,
        ]);
    }
}


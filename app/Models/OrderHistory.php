<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_histories';

    protected $fillable = [
        'order_id',
        'user_id',
        'role',
        'aksi',
        'deskripsi',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(int $orderId, int $userId, string $role, string $aksi, ?string $deskripsi = null): self
    {
        return self::create([
            'order_id' => $orderId,
            'user_id' => $userId,
            'role' => $role,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
        ]);
    }
}


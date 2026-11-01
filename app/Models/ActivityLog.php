<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_user_id',
        'action',
        'description',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public static function record(?int $userId, string $action, string $description, ?int $targetUserId = null, ?string $ip = null): self
    {
        return self::create([
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DateNegotiation extends Model
{
    use HasFactory;

    protected $table = 'date_negotiations';

    protected $fillable = [
        'order_id',
        'diajukan_oleh',
        'role_pengaju',
        'estimasi_usulan',
        'catatan',
        'status',
        'direspons_oleh',
        'catatan_respons',
    ];

    protected $casts = [
        'estimasi_usulan' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direspons_oleh');
    }
}


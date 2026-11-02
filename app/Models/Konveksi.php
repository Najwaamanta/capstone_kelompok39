<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konveksi extends Model
{
    use HasFactory;

    protected $table = 'konveksis';

    protected $fillable = [
        'nama',
        'penanggung_jawab',
        'telepon',
        'alamat',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'konveksi_id');
    }
}


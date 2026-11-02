<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStage extends Model
{
    use HasFactory;

    protected $table = 'order_stages';

    protected $fillable = [
        'order_id',
        'tahap',
        'mulai',
        'estimasi_selesai',
        'status_tahap',
        'catatan',
    ];

    protected $casts = [
        'mulai' => 'date',
        'estimasi_selesai' => 'date',
    ];

    protected $appends = [
        'tahap_label',
        'status_label',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    protected function tahapLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->tahap) {
                    'pemotongan' => 'Pemotongan',
                    'sablon' => 'Sablon',
                    'konveksi' => 'Produksi Konveksi',
                    'qc_gudang' => 'QC Gudang',
                    default => ucfirst($this->tahap),
                };
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status_tahap) {
                    'belum_dimulai' => 'Belum Dimulai',
                    'sedang_dikerjakan' => 'Sedang Dikerjakan',
                    'selesai' => 'Selesai',
                    default => ucfirst(str_replace('_', ' ', $this->status_tahap)),
                };
            }
        );
    }
}


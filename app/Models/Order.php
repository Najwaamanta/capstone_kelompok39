<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'id_pesanan',
        'nama_customer',
        'kode_seri',
        'jumlah',
        'target_selesai',
        'estimasi_gudang',
        'konveksi_id',
        'status',
        'dibuat_oleh',
        'catatan',
        'dibatalkan_pada',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'target_selesai' => 'date',
        'estimasi_gudang' => 'date',
        'dibatalkan_pada' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_badge',
        'current_step_index',
        'pill_label',
        'pill_class',
        'formatted_target',
        'formatted_estimasi_gudang',
        'active_negotiation',
    ];

    /**
     * Generate unique Order ID with format ORD-YYMM-XXX
     */
    public static function generateNextOrderId(): string
    {
        $yearMonth = Carbon::now()->format('ym'); // e.g. 2611
        $prefix = "ORD-{$yearMonth}-";

        $latestOrder = self::where('id_pesanan', 'like', "{$prefix}%")
            ->orderByDesc('id_pesanan')
            ->first();

        if (!$latestOrder) {
            $number = 1;
        } else {
            $lastSegment = substr($latestOrder->id_pesanan, -3);
            $number = intval($lastSegment) + 1;
        }

        return sprintf('%s%03d', $prefix, $number);
    }

    public function konveksi(): BelongsTo
    {
        return $this->belongsTo(Konveksi::class, 'konveksi_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(OrderStage::class, 'order_id');
    }

    public function negotiations(): HasMany
    {
        return $this->hasMany(DateNegotiation::class, 'order_id')->orderByDesc('created_at');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'order_id')->orderByDesc('created_at');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'order_id');
    }

    /**
     * Status Label
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'menunggu_persetujuan' => 'Menunggu Persetujuan Gudang',
                    'negosiasi' => 'Negosiasi Tanggal',
                    'dalam_produksi' => 'Dalam Produksi',
                    'qc_gudang' => 'QC Gudang',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                    default => ucfirst(str_replace('_', ' ', $this->status ?? 'menunggu_persetujuan')),
                };
            }
        );
    }

    /**
     * Status Badge Configuration
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'menunggu_persetujuan' => [
                        'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'dot' => 'bg-amber-500',
                    ],
                    'negosiasi' => [
                        'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                        'dot' => 'bg-orange-500',
                    ],
                    'dalam_produksi' => [
                        'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'dot' => 'bg-blue-500',
                    ],
                    'qc_gudang' => [
                        'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'dot' => 'bg-purple-500',
                    ],
                    'selesai' => [
                        'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'dot' => 'bg-emerald-500',
                    ],
                    'dibatalkan' => [
                        'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'dot' => 'bg-rose-500',
                    ],
                    default => [
                        'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
                        'dot' => 'bg-slate-400',
                    ],
                };
            }
        );
    }

    /**
     * Current step in 6-stage Stepper (0: Pesanan, 1: Pemotongan, 2: Sablon, 3: Konveksi, 4: QC Gudang, 5: Selesai)
     */
    protected function currentStepIndex(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->status === 'dibatalkan') return -1;
                if ($this->status === 'selesai') return 5;
                if ($this->status === 'qc_gudang') return 4;

                // Check stages if available
                if ($this->relationLoaded('stages') && $this->stages->isNotEmpty()) {
                    $qc = $this->stages->firstWhere('tahap', 'qc_gudang');
                    if ($qc && $qc->status_tahap === 'selesai') return 5;
                    if ($qc && $qc->status_tahap === 'sedang_dikerjakan') return 4;

                    $konv = $this->stages->firstWhere('tahap', 'konveksi');
                    if ($konv && $konv->status_tahap === 'sedang_dikerjakan') return 3;

                    $sablon = $this->stages->firstWhere('tahap', 'sablon');
                    if ($sablon && $sablon->status_tahap === 'sedang_dikerjakan') return 2;

                    $potong = $this->stages->firstWhere('tahap', 'pemotongan');
                    if ($potong && $potong->status_tahap === 'sedang_dikerjakan') return 1;

                    if ($this->status === 'dalam_produksi') return 1;
                }

                if ($this->status === 'dalam_produksi') return 1;
                return 0; // Pesanan dibuat (menunggu persetujuan / negosiasi)
            }
        );
    }

    protected function pillLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'negosiasi' => 'Negosiasi',
                    'qc_gudang' => 'QC Gudang',
                    'menunggu_persetujuan' => 'Menunggu Persetujuan',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Cancel',
                    'dalam_produksi' => 'Produksi Konveksi',
                    default => 'Produksi Konveksi',
                };
            }
        );
    }

    protected function pillClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'negosiasi' => 'bg-amber-50 text-amber-700',
                    'qc_gudang' => 'bg-purple-50 text-purple-700',
                    'menunggu_persetujuan' => 'bg-amber-50 text-amber-700',
                    'selesai' => 'bg-emerald-50 text-emerald-700',
                    'dibatalkan' => 'bg-rose-50 text-rose-700',
                    'dalam_produksi' => 'bg-blue-50 text-blue-700',
                    default => 'bg-blue-50 text-blue-700',
                };
            }
        );
    }

    protected function formattedTarget(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->target_selesai ? Carbon::parse($this->target_selesai)->isoFormat('D MMM Y') : '-';
            }
        );
    }

    protected function formattedEstimasiGudang(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->estimasi_gudang ? Carbon::parse($this->estimasi_gudang)->isoFormat('D MMM Y') : null;
            }
        );
    }

    protected function activeNegotiation(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('negotiations')) {
                    return $this->negotiations->firstWhere('status', 'menunggu');
                }
                return $this->negotiations()->where('status', 'menunggu')->latest()->first();
            }
        );
    }
}


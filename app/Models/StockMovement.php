<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class StockMovement extends Model
{
    use HasFactory;

    // Tabel hanya punya created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'type',
        'qty',
        'balance',
        'reference_type',
        'reference_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'qty' => 'integer',
            'balance' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Kartu stok bersifat immutable dan tidak boleh diubah.');
        });

        static::deleting(function () {
            throw new LogicException('Kartu stok bersifat immutable dan tidak boleh dihapus.');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundItem extends Model
{
    use HasFactory;

    protected $fillable = ['outbound_id', 'product_id', 'qty'];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
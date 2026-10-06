<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundItem extends Model
{
    use HasFactory;

    protected $fillable = ['inbound_id', 'product_id', 'qty'];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
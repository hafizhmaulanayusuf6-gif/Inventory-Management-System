<?php

namespace App\Exceptions;

use App\Models\Product;
use PhpParser\Builder\Function_;
use PHPUnit\Event\Test\FailedSubscriber;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Stok {$product->name} tidak cukup. Tersedia {$available}, diminta {$requested}."
        );
    }
}
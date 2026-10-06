<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cacelled';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
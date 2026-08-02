<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Rendah',
            self::Normal => 'Normal',
            self::High => 'Tinggi',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

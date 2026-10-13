<?php

namespace App\Enums;

enum LocationColor: string
{
    case Blue = 'blue';
    case Red = 'red';
    case Green = 'green';
    case Orange = 'orange';
    case Violet = 'violet';
    case Teal = 'teal';
    case Pink = 'pink';
    case Lime = 'lime';
    case Slate = 'slate';
    case Gold = 'gold';

    public function label(): string
    {
        return match ($this) {
            self::Blue => 'Blue',
            self::Red => 'Red',
            self::Green => 'Green',
            self::Orange => 'Orange',
            self::Violet => 'Violet',
            self::Teal => 'Teal',
            self::Pink => 'Pink',
            self::Lime => 'Lime',
            self::Slate => 'Slate',
            self::Gold => 'Gold',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::Blue => '#2563EB',
            self::Red => '#DC2626',
            self::Green => '#059669',
            self::Orange => '#EA580C',
            self::Violet => '#7C3AED',
            self::Teal => '#0891B2',
            self::Pink => '#BE185D',
            self::Lime => '#65A30D',
            self::Slate => '#475569',
            self::Gold => '#CA8A04',
        };
    }

    public static function tryFromHex(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        foreach (self::cases() as $color) {
            if ($color->hex() === $normalized) {
                return $color;
            }
        }

        return null;
    }

    public static function resolve(self|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        return self::tryFrom(strtolower($trimmed))
            ?? self::tryFromHex($trimmed);
    }

    public static function normalize(self|string|null $value): ?string
    {
        if ($value instanceof self) {
            return $value->value;
        }

        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        return self::resolve($trimmed)?->value ?? $trimmed;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $color): string => $color->value,
            self::cases()
        );
    }
}

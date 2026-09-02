<?php

namespace App\Enums;

enum PlotStatus: string
{
    case DISPONIBLE = 'disponible';
    case RESERVE = 'reserve';
    case VENDU = 'vendu';
    case OPTION = 'option';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'Disponible',
            self::RESERVE => 'Réservé',
            self::VENDU => 'Vendu',
            self::OPTION => 'Option',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'emerald',
            self::RESERVE => 'amber',
            self::VENDU => 'red',
            self::OPTION => 'sky',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::DISPONIBLE;
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}

<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case EN_COURS = 'en_cours';
    case LIVRE = 'livre';
    case A_VENIR = 'a_venir';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours',
            self::LIVRE => 'Livré',
            self::A_VENIR => 'À venir',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::EN_COURS => 'amber',
            self::LIVRE => 'emerald',
            self::A_VENIR => 'zinc',
        };
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

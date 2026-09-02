<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case NOUVEAU = 'nouveau';
    case EN_COURS = 'en_cours';
    case TRAITE = 'traite';
    case ARCHIVE = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::NOUVEAU => 'Nouveau',
            self::EN_COURS => 'En cours',
            self::TRAITE => 'Traité',
            self::ARCHIVE => 'Archivé',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NOUVEAU => 'sky',
            self::EN_COURS => 'amber',
            self::TRAITE => 'emerald',
            self::ARCHIVE => 'zinc',
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

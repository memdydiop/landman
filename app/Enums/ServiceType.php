<?php

namespace App\Enums;

enum ServiceType: string
{
    case BTP = 'btp';
    case AMENAGEMENT = 'amenagement';
    case LOTISSEMENT = 'lotissement';
    case RENOVATION = 'renovation';
    case ARCHITECTURE = 'architecture';
    case ELECTRICITE = 'electricite';

    public function label(): string
    {
        return match ($this) {
            self::BTP => 'BTP & Génie Civil',
            self::AMENAGEMENT => 'Aménagement & VRD',
            self::LOTISSEMENT => 'Lotissement & Promotion',
            self::RENOVATION => 'Rénovation & Réhabilitation',
            self::ARCHITECTURE => 'Architecture & Maîtrise d\'œuvre',
            self::ELECTRICITE => 'Électricité & Courants faibles',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::BTP => 'BTP',
            self::AMENAGEMENT => 'Aménagement',
            self::LOTISSEMENT => 'Lotissement',
            self::RENOVATION => 'Rénovation',
            self::ARCHITECTURE => 'Architecture',
            self::ELECTRICITE => 'Électricité',
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

    public function icon(): string
    {
        return match ($this) {
            self::BTP => 'building-office-2',
            self::AMENAGEMENT => 'map-pin',
            self::LOTISSEMENT => 'pencil-square',
            self::RENOVATION => 'wrench-screwdriver',
            self::ARCHITECTURE => 'clipboard-document-check',
            self::ELECTRICITE => 'sparkles',
        };
    }
}

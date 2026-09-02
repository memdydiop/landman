<?php

namespace App\Enums;

enum InquiryType: string
{
    case DEVIS_BTP = 'devis_btp';
    case ACHAT_LOT = 'achat_lot';
    case PARTENARIAT = 'partenariat';
    case CONTACT = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::DEVIS_BTP => 'Devis BTP',
            self::ACHAT_LOT => 'Achat de lot',
            self::PARTENARIAT => 'Partenariat',
            self::CONTACT => 'Contact général',
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

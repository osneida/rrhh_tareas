<?php

namespace App\Enums;

enum PaginacionEnum: int
{
    case Cinco = 5;
    case Diez  = 10;
    case VeintiCinco = 25;
    case Cincuenta   = 50;

    public function label(): string
    {
        return match ($this) {
            self::Cinco => 5,
            self::Diez  => 10,
            self::VeintiCinco => 25,
            self::Cincuenta   => 50,
        };
    }
}

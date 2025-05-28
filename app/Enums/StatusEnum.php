<?php

namespace App\Enums;

enum StatusEnum: int
{
    case ACTIVE   = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => __('Active'),
            self::INACTIVE => __('Inactive'),
        };
    }

}


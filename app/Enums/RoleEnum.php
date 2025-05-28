<?php

namespace App\Enums;

enum RoleEnum: string
{
    case admin = 'admin';
    case empleado = 'empleado';

    public function label(): string
    {
        return match ($this) {
            self::admin => __('Administrator'),
            self::empleado => __('Employee'),
        };
    }
}

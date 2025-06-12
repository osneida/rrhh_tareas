<?php

namespace App\Enums;

enum EstatusTareaEnum: string
{
    case Pendiente  = 'Pendiente';
    case Iniciada   = 'Iniciada';
    case Finalizada = 'Finalizada';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente  => __('Pending'),
            self::Iniciada   => __('Started'),
            self::Finalizada => __('Completed'),
        };
    }
}

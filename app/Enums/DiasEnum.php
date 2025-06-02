<?php

namespace App\Enums;

enum DiasEnum: string
{
    case Monday    = 'Monday';
    case Tuesday   = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday  = 'Thursday';
    case Friday    = 'Friday';
    case Saturday  = 'saturday';
    case Sunday    = 'sunday';

    public function label(): string
    {
        return match ($this) {
            self::Monday    => __('Monday'),
            self::Tuesday   => __('Tuesday'),
            self::Wednesday => __('Wednesday'),
            self::Thursday  => __('Thursday'),
            self::Friday    => __('Friday'),
            self::Saturday  => __('Saturday'),
            self::Sunday    => __('Sunday'),

        };
    }
}

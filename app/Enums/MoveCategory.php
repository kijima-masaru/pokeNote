<?php

namespace App\Enums;

enum MoveCategory: string
{
    case Physical = 'physical';
    case Special = 'special';
    case Status = 'status';

    public function label(): string
    {
        return match($this) {
            self::Physical => '物理',
            self::Special => '特殊',
            self::Status => '変化',
        };
    }
}

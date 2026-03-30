<?php

namespace App\Enums;

enum BattleResult: string
{
    case Win = 'win';
    case Lose = 'lose';
    case Draw = 'draw';

    public function label(): string
    {
        return match($this) {
            self::Win => '勝ち',
            self::Lose => '負け',
            self::Draw => '引き分け',
        };
    }
}

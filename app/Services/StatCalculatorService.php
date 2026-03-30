<?php

namespace App\Services;

use App\Enums\Nature;
use App\Models\CustomPokemon;

class StatCalculatorService
{
    /**
     * カスタムポケモンの全ステータス実数値を計算して返す
     */
    public function calculate(CustomPokemon $cp): array
    {
        $pokemon = $cp->pokemon;
        $level = $cp->level;
        $nature = Nature::from($cp->nature);

        return [
            'hp'         => $this->calcHp($pokemon->base_hp, $cp->iv_hp, $cp->ev_hp, $level),
            'attack'     => $this->calcStat($pokemon->base_attack, $cp->iv_attack, $cp->ev_attack, $level, $nature, 'attack'),
            'defense'    => $this->calcStat($pokemon->base_defense, $cp->iv_defense, $cp->ev_defense, $level, $nature, 'defense'),
            'sp_attack'  => $this->calcStat($pokemon->base_sp_attack, $cp->iv_sp_attack, $cp->ev_sp_attack, $level, $nature, 'sp_attack'),
            'sp_defense' => $this->calcStat($pokemon->base_sp_defense, $cp->iv_sp_defense, $cp->ev_sp_defense, $level, $nature, 'sp_defense'),
            'speed'      => $this->calcStat($pokemon->base_speed, $cp->iv_speed, $cp->ev_speed, $level, $nature, 'speed'),
        ];
    }

    /**
     * HP実数値計算
     * 式: floor((base*2 + iv + floor(ev/4)) * level/100) + level + 10
     */
    public function calcHp(int $base, int $iv, int $ev, int $level): int
    {
        return (int)floor(($base * 2 + $iv + floor($ev / 4)) * $level / 100) + $level + 10;
    }

    /**
     * HP以外のステータス実数値計算
     * 式: floor((floor((base*2 + iv + floor(ev/4)) * level/100) + 5) * nature_modifier)
     */
    public function calcStat(int $base, int $iv, int $ev, int $level, Nature $nature, string $statName): int
    {
        $raw = (int)floor((int)floor(($base * 2 + $iv + floor($ev / 4)) * $level / 100) + 5);
        $modifier = $this->getNatureModifier($nature, $statName);
        return (int)floor($raw * $modifier);
    }

    private function getNatureModifier(Nature $nature, string $statName): float
    {
        if ($nature->boostedStat() === $statName) {
            return 1.1;
        }
        if ($nature->reducedStat() === $statName) {
            return 0.9;
        }
        return 1.0;
    }
}

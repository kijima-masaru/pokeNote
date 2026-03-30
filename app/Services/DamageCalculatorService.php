<?php

namespace App\Services;

use App\Models\CustomPokemon;
use App\Models\Move;

class DamageCalculatorService
{
    /**
     * ダメージ計算を行い16本の乱数ロールを返す（マイポケモン使用）
     */
    public function calculate(
        CustomPokemon $attacker,
        CustomPokemon $defender,
        Move $move,
        array $attackerRanks = [],
        array $defenderRanks = [],
        string $weather = 'none',
        string $terrain = 'none',
        bool $isCritical = false,
        array $otherModifiers = []
    ): array {
        $attackerTypes = $attacker->pokemon->types->pluck('type')->toArray();
        $defenderTypes = $defender->pokemon->types->pluck('type')->toArray();

        return $this->calculateFromRaw(
            $attacker->actual_stats,
            $attackerTypes,
            $attacker->level,
            $defender->actual_stats,
            $defenderTypes,
            $move,
            $attackerRanks,
            $defenderRanks,
            $weather,
            $terrain,
            $isCritical,
            $otherModifiers
        );
    }

    /**
     * ダメージ計算を行い16本の乱数ロールを返す（アドホック入力使用）
     */
    public function calculateFromRaw(
        array $attackerStats,
        array $attackerTypes,
        int $attackerLevel,
        array $defenderStats,
        array $defenderTypes,
        Move $move,
        array $attackerRanks = [],
        array $defenderRanks = [],
        string $weather = 'none',
        string $terrain = 'none',
        bool $isCritical = false,
        array $otherModifiers = []
    ): array {
        if ($move->category === 'status' || $move->power === null) {
            return $this->emptyResult();
        }

        $isPhysical = $move->category === 'physical';
        $atkStat = $isPhysical ? 'attack' : 'sp_attack';
        $defStat = $isPhysical ? 'defense' : 'sp_defense';

        $atk = $attackerStats[$atkStat] ?? 0;
        $def = $defenderStats[$defStat] ?? 0;
        $defHp = $defenderStats['hp'] ?? 1;

        // ランク補正
        $atk = (int)floor($atk * $this->rankMultiplier($attackerRanks[$atkStat] ?? 0));
        if (!$isCritical) {
            $def = (int)floor($def * $this->rankMultiplier($defenderRanks[$defStat] ?? 0));
        }
        if ($isCritical && ($defenderRanks[$defStat] ?? 0) > 0) {
            $def = $defenderStats[$defStat];
        }

        // 基本ダメージ式
        $baseDamage = (int)floor(
            (int)floor(
                (int)floor((int)floor($attackerLevel * 2 / 5) + 2)
                * $move->power * $atk / $def
            ) / 50
        ) + 2;

        // 天気補正
        $baseDamage = (int)floor($baseDamage * $this->weatherModifier($weather, $move->type, $isPhysical));

        // フィールド補正（ひこうタイプは無効）
        if (!in_array('flying', $attackerTypes, true)) {
            $baseDamage = (int)floor($baseDamage * $this->terrainModifierRaw($terrain, $move->type));
        }

        // 急所補正
        if ($isCritical) {
            $baseDamage = (int)floor($baseDamage * 1.5);
        }

        // STAB (タイプ一致)
        if (in_array($move->type, $attackerTypes, true)) {
            $baseDamage = (int)floor($baseDamage * 1.5);
        }

        // タイプ相性
        $typeEffectiveness = $this->typeEffectiveness($move->type, $defenderTypes);
        $baseDamage = (int)floor($baseDamage * $typeEffectiveness);

        // やけど補正
        if ($isPhysical && in_array('burned', $otherModifiers, true)) {
            $baseDamage = (int)floor($baseDamage * 0.5);
        }

        // 乱数16本 (0.85 ~ 1.00)
        $rolls = [];
        for ($i = 85; $i <= 100; $i++) {
            $rolls[] = (int)floor($baseDamage * $i / 100);
        }

        $minDamage = min($rolls);
        $maxDamage = max($rolls);

        return [
            'damage_min' => $minDamage,
            'damage_max' => $maxDamage,
            'damage_percent_min' => round($minDamage / $defHp * 100, 1),
            'damage_percent_max' => round($maxDamage / $defHp * 100, 1),
            'one_shot' => $minDamage >= $defHp,
            'two_shot' => $minDamage * 2 >= $defHp,
            'type_effectiveness' => $typeEffectiveness,
            'rolls' => $rolls,
        ];
    }

    private function rankMultiplier(int $rank): float
    {
        if ($rank > 0) return (2 + $rank) / 2;
        if ($rank < 0) return 2 / (2 - $rank);
        return 1.0;
    }

    private function weatherModifier(string $weather, string $moveType, bool $isPhysical): float
    {
        return match(true) {
            $weather === 'sunny' && $moveType === 'fire' => 1.5,
            $weather === 'sunny' && $moveType === 'water' => 0.5,
            $weather === 'rainy' && $moveType === 'water' => 1.5,
            $weather === 'rainy' && $moveType === 'fire' => 0.5,
            default => 1.0,
        };
    }

    private function terrainModifier(string $terrain, string $moveType, CustomPokemon $attacker): float
    {
        $attackerTypes = $attacker->pokemon->types->pluck('type')->toArray();
        if (in_array('flying', $attackerTypes, true)) return 1.0;
        return $this->terrainModifierRaw($terrain, $moveType);
    }

    private function terrainModifierRaw(string $terrain, string $moveType): float
    {
        return match(true) {
            $terrain === 'grassy' && $moveType === 'grass' => 1.3,
            $terrain === 'electric' && $moveType === 'electric' => 1.3,
            $terrain === 'psychic' && $moveType === 'psychic' => 1.3,
            default => 1.0,
        };
    }

    private function typeEffectiveness(string $attackType, array $defenderTypes): float
    {
        $chart = $this->typeChart();
        $multiplier = 1.0;
        foreach ($defenderTypes as $defType) {
            $multiplier *= $chart[$attackType][$defType] ?? 1.0;
        }
        return $multiplier;
    }

    private function typeChart(): array
    {
        return [
            'normal'   => ['rock' => 0.5, 'ghost' => 0, 'steel' => 0.5],
            'fire'     => ['fire' => 0.5, 'water' => 0.5, 'grass' => 2, 'ice' => 2, 'bug' => 2, 'rock' => 0.5, 'dragon' => 0.5, 'steel' => 2],
            'water'    => ['fire' => 2, 'water' => 0.5, 'grass' => 0.5, 'ground' => 2, 'rock' => 2, 'dragon' => 0.5],
            'electric' => ['water' => 2, 'electric' => 0.5, 'grass' => 0.5, 'ground' => 0, 'flying' => 2, 'dragon' => 0.5],
            'grass'    => ['fire' => 0.5, 'water' => 2, 'grass' => 0.5, 'poison' => 0.5, 'ground' => 2, 'flying' => 0.5, 'bug' => 0.5, 'rock' => 2, 'dragon' => 0.5, 'steel' => 0.5],
            'ice'      => ['water' => 0.5, 'grass' => 2, 'ice' => 0.5, 'ground' => 2, 'flying' => 2, 'dragon' => 2, 'steel' => 0.5],
            'fighting' => ['normal' => 2, 'ice' => 2, 'poison' => 0.5, 'flying' => 0.5, 'psychic' => 0.5, 'bug' => 0.5, 'rock' => 2, 'ghost' => 0, 'dark' => 2, 'steel' => 2, 'fairy' => 0.5],
            'poison'   => ['grass' => 2, 'poison' => 0.5, 'ground' => 0.5, 'rock' => 0.5, 'ghost' => 0.5, 'steel' => 0, 'fairy' => 2],
            'ground'   => ['fire' => 2, 'electric' => 2, 'grass' => 0.5, 'poison' => 2, 'flying' => 0, 'bug' => 0.5, 'rock' => 2, 'steel' => 2],
            'flying'   => ['electric' => 0.5, 'grass' => 2, 'ice' => 0.5, 'fighting' => 2, 'bug' => 2, 'rock' => 0.5, 'steel' => 0.5],
            'psychic'  => ['fighting' => 2, 'poison' => 2, 'psychic' => 0.5, 'dark' => 0, 'steel' => 0.5],
            'bug'      => ['fire' => 0.5, 'grass' => 2, 'fighting' => 0.5, 'flying' => 0.5, 'psychic' => 2, 'ghost' => 0.5, 'dark' => 2, 'steel' => 0.5, 'fairy' => 0.5],
            'rock'     => ['fire' => 2, 'ice' => 2, 'fighting' => 0.5, 'ground' => 0.5, 'flying' => 2, 'bug' => 2, 'steel' => 0.5],
            'ghost'    => ['normal' => 0, 'psychic' => 2, 'ghost' => 2, 'dark' => 0.5],
            'dragon'   => ['dragon' => 2, 'steel' => 0.5, 'fairy' => 0],
            'dark'     => ['fighting' => 0.5, 'psychic' => 2, 'ghost' => 2, 'dark' => 0.5, 'fairy' => 0.5],
            'steel'    => ['fire' => 0.5, 'water' => 0.5, 'electric' => 0.5, 'ice' => 2, 'rock' => 2, 'steel' => 0.5, 'fairy' => 2],
            'fairy'    => ['fire' => 0.5, 'fighting' => 2, 'poison' => 0.5, 'dragon' => 2, 'dark' => 2, 'steel' => 0.5],
        ];
    }

    private function emptyResult(): array
    {
        return [
            'damage_min' => 0,
            'damage_max' => 0,
            'damage_percent_min' => 0,
            'damage_percent_max' => 0,
            'one_shot' => false,
            'two_shot' => false,
            'type_effectiveness' => 1.0,
            'rolls' => array_fill(0, 16, 0),
        ];
    }
}

<?php

namespace App\Enums;

enum PokemonType: string
{
    case Normal = 'normal';
    case Fire = 'fire';
    case Water = 'water';
    case Electric = 'electric';
    case Grass = 'grass';
    case Ice = 'ice';
    case Fighting = 'fighting';
    case Poison = 'poison';
    case Ground = 'ground';
    case Flying = 'flying';
    case Psychic = 'psychic';
    case Bug = 'bug';
    case Rock = 'rock';
    case Ghost = 'ghost';
    case Dragon = 'dragon';
    case Dark = 'dark';
    case Steel = 'steel';
    case Fairy = 'fairy';

    public function label(): string
    {
        return match($this) {
            self::Normal => 'ノーマル',
            self::Fire => 'ほのお',
            self::Water => 'みず',
            self::Electric => 'でんき',
            self::Grass => 'くさ',
            self::Ice => 'こおり',
            self::Fighting => 'かくとう',
            self::Poison => 'どく',
            self::Ground => 'じめん',
            self::Flying => 'ひこう',
            self::Psychic => 'エスパー',
            self::Bug => 'むし',
            self::Rock => 'いわ',
            self::Ghost => 'ゴースト',
            self::Dragon => 'ドラゴン',
            self::Dark => 'あく',
            self::Steel => 'はがね',
            self::Fairy => 'フェアリー',
        };
    }
}

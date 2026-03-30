<?php

namespace App\Enums;

enum Nature: string
{
    case Hardy = 'hardy';
    case Lonely = 'lonely';
    case Brave = 'brave';
    case Adamant = 'adamant';
    case Naughty = 'naughty';
    case Bold = 'bold';
    case Docile = 'docile';
    case Relaxed = 'relaxed';
    case Impish = 'impish';
    case Lax = 'lax';
    case Timid = 'timid';
    case Hasty = 'hasty';
    case Serious = 'serious';
    case Jolly = 'jolly';
    case Naive = 'naive';
    case Modest = 'modest';
    case Mild = 'mild';
    case Quiet = 'quiet';
    case Bashful = 'bashful';
    case Rash = 'rash';
    case Calm = 'calm';
    case Gentle = 'gentle';
    case Sassy = 'sassy';
    case Careful = 'careful';
    case Quirky = 'quirky';

    public function label(): string
    {
        return match($this) {
            self::Hardy => 'がんばりや',
            self::Lonely => 'さみしがり',
            self::Brave => 'ゆうかん',
            self::Adamant => 'いじっぱり',
            self::Naughty => 'やんちゃ',
            self::Bold => 'ずぶとい',
            self::Docile => 'すなお',
            self::Relaxed => 'のんき',
            self::Impish => 'わんぱく',
            self::Lax => 'のうてんき',
            self::Timid => 'おくびょう',
            self::Hasty => 'せっかち',
            self::Serious => 'まじめ',
            self::Jolly => 'ようき',
            self::Naive => 'むじゃき',
            self::Modest => 'ひかえめ',
            self::Mild => 'おっとり',
            self::Quiet => 'れいせい',
            self::Bashful => 'てれや',
            self::Rash => 'うっかりや',
            self::Calm => 'おだやか',
            self::Gentle => 'おとなしい',
            self::Sassy => 'なまいき',
            self::Careful => 'しんちょう',
            self::Quirky => 'きまぐれ',
        };
    }

    public function boostedStat(): ?string
    {
        return match($this) {
            self::Lonely, self::Brave, self::Adamant, self::Naughty => 'attack',
            self::Bold, self::Relaxed, self::Impish, self::Lax => 'defense',
            self::Modest, self::Mild, self::Quiet, self::Rash => 'sp_attack',
            self::Calm, self::Gentle, self::Sassy, self::Careful => 'sp_defense',
            self::Timid, self::Hasty, self::Jolly, self::Naive => 'speed',
            default => null,
        };
    }

    public function reducedStat(): ?string
    {
        return match($this) {
            self::Bold, self::Timid, self::Modest, self::Calm => 'attack',
            self::Lonely, self::Hasty, self::Mild, self::Gentle => 'defense',
            self::Adamant, self::Impish, self::Jolly, self::Careful => 'sp_attack',
            self::Naughty, self::Lax, self::Rash, self::Naive => 'sp_defense',
            self::Brave, self::Relaxed, self::Quiet, self::Sassy => 'speed',
            default => null,
        };
    }
}

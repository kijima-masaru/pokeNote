<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleOpponentPokemon extends Model
{
    protected $table = 'battle_opponent_pokemon';

    protected $fillable = ['battle_id', 'pokemon_id', 'slot', 'nickname'];

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }
}

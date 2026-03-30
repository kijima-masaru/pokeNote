<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turn extends Model
{
    protected $fillable = [
        'battle_id', 'turn_number',
        'my_pokemon_id', 'opponent_pokemon_name',
        'my_move_id', 'opponent_move_id',
        'my_hp_remaining', 'opponent_hp_remaining',
        'description',
    ];

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    public function myPokemon(): BelongsTo
    {
        return $this->belongsTo(CustomPokemon::class, 'my_pokemon_id');
    }

    public function myMove(): BelongsTo
    {
        return $this->belongsTo(Move::class, 'my_move_id');
    }

    public function opponentMove(): BelongsTo
    {
        return $this->belongsTo(Move::class, 'opponent_move_id');
    }
}

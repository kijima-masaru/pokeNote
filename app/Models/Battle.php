<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battle extends Model
{
    protected $fillable = [
        'user_id', 'title', 'opponent_name', 'result', 'format', 'memo', 'tags', 'played_at',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turns(): HasMany
    {
        return $this->hasMany(Turn::class)->orderBy('turn_number');
    }

    public function opponentPokemon(): HasMany
    {
        return $this->hasMany(BattleOpponentPokemon::class)->orderBy('slot')->with('pokemon.types');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Move extends Model
{
    protected $fillable = [
        'name_ja', 'name_en', 'type', 'category',
        'power', 'accuracy', 'pp', 'priority', 'description', 'makes_contact',
    ];

    protected $casts = [
        'makes_contact' => 'boolean',
    ];

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_moves');
    }

    public function customPokemon(): BelongsToMany
    {
        return $this->belongsToMany(CustomPokemon::class, 'custom_pokemon_moves')
            ->withPivot('slot');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ability extends Model
{
    protected $fillable = ['name_ja', 'name_en', 'description'];

    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_abilities')
            ->withPivot('slot');
    }

    public function customPokemon(): HasMany
    {
        return $this->hasMany(CustomPokemon::class);
    }
}

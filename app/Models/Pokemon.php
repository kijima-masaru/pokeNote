<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pokemon extends Model
{
    protected $table = 'pokemon';

    protected $fillable = [
        'pokedex_number', 'name_ja', 'name_en', 'form_name',
        'base_hp', 'base_attack', 'base_defense',
        'base_sp_attack', 'base_sp_defense', 'base_speed',
        'sprite_url', 'is_mega', 'base_pokemon_id',
    ];

    protected $appends = ['base_total'];

    public function types(): HasMany
    {
        return $this->hasMany(PokemonType::class)->orderBy('slot');
    }

    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'pokemon_abilities')
            ->withPivot('slot')
            ->orderBy('pokemon_abilities.slot');
    }

    public function moves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'pokemon_moves')
            ->withPivot('learn_method', 'level_learned');
    }

    public function customPokemon(): HasMany
    {
        return $this->hasMany(CustomPokemon::class);
    }

    public function getBaseTotalAttribute(): int
    {
        return $this->base_hp + $this->base_attack + $this->base_defense
            + $this->base_sp_attack + $this->base_sp_defense + $this->base_speed;
    }
}

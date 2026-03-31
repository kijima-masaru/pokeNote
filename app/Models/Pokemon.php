<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** 進化先（このポケモンが進化元） */
    public function evolvesTo(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_evolutions', 'from_pokemon_id', 'to_pokemon_id')
            ->withPivot('method', 'min_level', 'trigger_item');
    }

    /** 進化前（このポケモンが進化先） */
    public function evolvesFrom(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_evolutions', 'to_pokemon_id', 'from_pokemon_id')
            ->withPivot('method', 'min_level', 'trigger_item');
    }

    public function getBaseTotalAttribute(): int
    {
        return $this->base_hp + $this->base_attack + $this->base_defense
            + $this->base_sp_attack + $this->base_sp_defense + $this->base_speed;
    }
}

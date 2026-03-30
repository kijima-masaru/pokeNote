<?php

namespace App\Models;

use App\Services\StatCalculatorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomPokemon extends Model
{
    protected $table = 'custom_pokemon';

    protected $fillable = [
        'user_id', 'nickname', 'pokemon_id', 'ability_id', 'item_id', 'nature', 'level',
        'iv_hp', 'iv_attack', 'iv_defense', 'iv_sp_attack', 'iv_sp_defense', 'iv_speed',
        'ev_hp', 'ev_attack', 'ev_defense', 'ev_sp_attack', 'ev_sp_defense', 'ev_speed',
        'memo',
    ];

    protected $appends = ['actual_stats', 'display_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }

    public function ability(): BelongsTo
    {
        return $this->belongsTo(Ability::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function moves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'custom_pokemon_moves')
            ->withPivot('slot')
            ->orderBy('custom_pokemon_moves.slot');
    }

    public function turns(): HasMany
    {
        return $this->hasMany(Turn::class, 'my_pokemon_id');
    }

    public function getActualStatsAttribute(): array
    {
        if (!$this->pokemon) {
            return [];
        }
        $service = new StatCalculatorService();
        return $service->calculate($this);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?? ($this->pokemon ? $this->pokemon->name_ja : '');
    }
}

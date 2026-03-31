<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    public $timestamps = false;
    protected $fillable = ['team_id', 'custom_pokemon_id', 'slot'];

    public function customPokemon(): BelongsTo
    {
        return $this->belongsTo(CustomPokemon::class);
    }
}

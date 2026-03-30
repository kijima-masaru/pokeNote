<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PokemonType extends Model
{
    protected $table = 'pokemon_types';
    public $timestamps = false;

    protected $fillable = ['pokemon_id', 'type', 'slot'];

    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }
}

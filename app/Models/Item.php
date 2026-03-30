<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['name_ja', 'name_en', 'category', 'description', 'image_url'];

    public function customPokemon(): HasMany
    {
        return $this->hasMany(CustomPokemon::class);
    }
}

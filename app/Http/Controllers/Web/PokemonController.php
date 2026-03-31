<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\PokemonType;
use App\Models\Pokemon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PokemonController extends Controller
{
    public function index(Request $request): View
    {
        $types = PokemonType::cases();
        return view('pokemon.index', compact('types'));
    }

    public function show(int $id): View
    {
        $pokemon = Pokemon::with(['types', 'abilities', 'moves'])->findOrFail($id);
        return view('pokemon.show', compact('pokemon'));
    }

    public function typeChart(): View
    {
        $types = PokemonType::cases();
        return view('pokemon.type-chart', compact('types'));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Enums\PokemonType;
use App\Enums\MoveCategory;
use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\Item;
use App\Models\Move;
use App\Models\Pokemon;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function abilities(): View
    {
        $abilities = Ability::orderBy('id')->paginate(50);
        return view('master.abilities', compact('abilities'));
    }

    public function items(): View
    {
        $items = Item::orderBy('id')->paginate(50);
        return view('master.items', compact('items'));
    }

    public function moves(): View
    {
        $types = PokemonType::cases();
        $categories = MoveCategory::cases();
        $moves = Move::orderBy('id')->paginate(50);
        return view('master.moves', compact('moves', 'types', 'categories'));
    }

    public function pokemon(): View
    {
        $types = PokemonType::cases();
        $pokemon = Pokemon::with('types')->orderBy('pokedex_number')->paginate(50);
        return view('master.pokemon', compact('pokemon', 'types'));
    }

    public function import(): View
    {
        $pokemonCount = Pokemon::count();
        $moveCount    = Move::count();
        return view('master.import', compact('pokemonCount', 'moveCount'));
    }
}

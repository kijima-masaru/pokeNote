<?php

namespace App\Http\Controllers\Web;

use App\Enums\Nature;
use App\Http\Controllers\Controller;
use App\Models\CustomPokemon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DamageCalcController extends Controller
{
    public function index(Request $request): View
    {
        $attackerId = $request->get('attacker');
        $defenderId = $request->get('defender');

        $attackerPokemon = $attackerId
            ? CustomPokemon::with(['pokemon.types', 'ability', 'moves'])->find($attackerId)
            : null;
        $defenderPokemon = $defenderId
            ? CustomPokemon::with(['pokemon.types', 'ability', 'moves'])->find($defenderId)
            : null;

        $myPokemonList = CustomPokemon::with(['pokemon'])->latest()->get();
        $natures = Nature::cases();

        return view('damage-calc.index', compact('attackerPokemon', 'defenderPokemon', 'myPokemonList', 'natures'));
    }

    public function formula(): View
    {
        return view('damage-calc.formula');
    }
}

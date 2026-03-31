<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomPokemon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $myPokemonList = CustomPokemon::with(['pokemon.types', 'ability'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        return view('teams.index', compact('myPokemonList'));
    }
}

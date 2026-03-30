<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\Nature;
use App\Models\CustomPokemon;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomPokemonController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $query = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->latest();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nickname', 'like', "%{$search}%")
                  ->orWhereHas('pokemon', fn($q2) => $q2->where('name_ja', 'like', "%{$search}%"));
            });
        }
        $customPokemon = $query->paginate(12)->withQueryString();
        return view('custom-pokemon.index', compact('customPokemon', 'search'));
    }

    public function create(): View
    {
        $natures = Nature::cases();
        $items = Item::orderBy('name_ja')->get();
        return view('custom-pokemon.create', compact('natures', 'items'));
    }

    public function show(int $id): View
    {
        $cp = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return view('custom-pokemon.show', compact('cp'));
    }

    public function edit(int $id): View
    {
        $cp = CustomPokemon::with(['pokemon', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $natures = Nature::cases();
        $items = Item::orderBy('name_ja')->get();
        return view('custom-pokemon.edit', compact('cp', 'natures', 'items'));
    }
}

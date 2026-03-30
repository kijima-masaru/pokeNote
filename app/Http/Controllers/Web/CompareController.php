<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\PokemonType;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function index(): View
    {
        $types = PokemonType::cases();
        return view('compare.index', compact('types'));
    }
}

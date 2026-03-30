<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Move;
use Illuminate\View\View;

class MoveController extends Controller
{
    public function index(): View
    {
        return view('moves.index');
    }

    public function show(int $id): View
    {
        $move = Move::with(['pokemon.types'])->findOrFail($id);
        return view('moves.show', compact('move'));
    }
}

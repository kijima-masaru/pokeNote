<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTurnRequest;
use App\Models\Battle;
use App\Models\Turn;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TurnController extends Controller
{
    public function index(int $battleId): JsonResponse
    {
        $battle = Battle::where('user_id', Auth::id())->findOrFail($battleId);
        $turns = $battle->turns()->with(['myPokemon.pokemon', 'myMove', 'opponentMove'])->get();
        return response()->json($turns);
    }

    public function store(StoreTurnRequest $request, int $battleId): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($battleId);
        $turn = Turn::create(array_merge($request->validated(), ['battle_id' => $battleId]));
        $turn->load(['myPokemon.pokemon', 'myMove', 'opponentMove']);
        return response()->json($turn, 201);
    }

    public function update(StoreTurnRequest $request, int $battleId, int $turnNumber): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($battleId);
        $turn = Turn::where('battle_id', $battleId)->where('turn_number', $turnNumber)->firstOrFail();
        $turn->update($request->validated());
        $turn->load(['myPokemon.pokemon', 'myMove', 'opponentMove']);
        return response()->json($turn);
    }

    public function destroy(int $battleId, int $turnNumber): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($battleId);
        Turn::where('battle_id', $battleId)
            ->where('turn_number', $turnNumber)
            ->firstOrFail()
            ->delete();
        return response()->json(null, 204);
    }
}

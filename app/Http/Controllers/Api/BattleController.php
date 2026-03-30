<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBattleRequest;
use App\Models\Battle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{
    public function index(): JsonResponse
    {
        $battles = Battle::withCount('turns')
            ->where('user_id', Auth::id())
            ->latest('played_at')
            ->paginate(20);
        return response()->json($battles);
    }

    public function store(StoreBattleRequest $request): JsonResponse
    {
        $battle = Battle::create(array_merge($request->validated(), ['user_id' => Auth::id()]));
        return response()->json($battle, 201);
    }

    public function show(int $id): JsonResponse
    {
        $battle = Battle::with(['turns.myPokemon.pokemon', 'turns.myMove', 'turns.opponentMove'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return response()->json($battle);
    }

    public function update(StoreBattleRequest $request, int $id): JsonResponse
    {
        $battle = Battle::where('user_id', Auth::id())->findOrFail($id);
        $battle->update($request->validated());
        return response()->json($battle);
    }

    public function destroy(int $id): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}

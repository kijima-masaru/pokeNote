<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\BattleOpponentPokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BattleOpponentPokemonController extends Controller
{
    /**
     * 対戦相手のポケモン一覧
     * GET /api/v1/battles/{battleId}/opponent-pokemon
     */
    public function index(int $battleId): JsonResponse
    {
        $battle = Battle::where('user_id', Auth::id())->findOrFail($battleId);
        return response()->json($battle->opponentPokemon);
    }

    /**
     * 対戦相手のポケモンを追加/更新（スロット単位）
     * POST /api/v1/battles/{battleId}/opponent-pokemon
     */
    public function store(Request $request, int $battleId): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($battleId);

        $data = $request->validate([
            'slot'       => 'required|integer|min:1|max:6',
            'pokemon_id' => 'nullable|integer|exists:pokemon,id',
            'nickname'   => 'nullable|string|max:50',
        ]);
        $data['battle_id'] = $battleId;

        $record = BattleOpponentPokemon::updateOrCreate(
            ['battle_id' => $battleId, 'slot' => $data['slot']],
            ['pokemon_id' => $data['pokemon_id'] ?? null, 'nickname' => $data['nickname'] ?? null]
        );

        $record->load('pokemon.types');
        return response()->json($record, 201);
    }

    /**
     * スロットのポケモンを削除（スロットをクリア）
     * DELETE /api/v1/battles/{battleId}/opponent-pokemon/{slot}
     */
    public function destroy(int $battleId, int $slot): JsonResponse
    {
        Battle::where('user_id', Auth::id())->findOrFail($battleId);

        BattleOpponentPokemon::where('battle_id', $battleId)
            ->where('slot', $slot)
            ->delete();

        return response()->json(null, 204);
    }
}

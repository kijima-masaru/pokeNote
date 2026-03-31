<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = Team::with(['members.customPokemon.pokemon.types'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        return response()->json($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'memo' => 'nullable|string|max:500',
        ]);
        $team = Team::create(array_merge($data, ['user_id' => Auth::id()]));
        return response()->json($team->load('members'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $team = Team::with(['members.customPokemon.pokemon.types',
                            'members.customPokemon.moves',
                            'members.customPokemon.ability'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return response()->json($team);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $team = Team::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'memo' => 'nullable|string|max:500',
        ]);
        $team->update($data);
        return response()->json($team);
    }

    public function destroy(int $id): JsonResponse
    {
        Team::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * スロットにカスタムポケモンをセット
     * PUT /api/v1/teams/{id}/members/{slot}
     */
    public function setMember(Request $request, int $id, int $slot): JsonResponse
    {
        $team = Team::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate([
            'custom_pokemon_id' => 'nullable|integer|exists:custom_pokemon,id',
        ]);

        TeamMember::updateOrCreate(
            ['team_id' => $team->id, 'slot' => $slot],
            ['custom_pokemon_id' => $data['custom_pokemon_id'] ?? null]
        );

        $team->load('members.customPokemon.pokemon.types');
        return response()->json($team);
    }
}

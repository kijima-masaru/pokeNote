<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomPokemonRequest;
use App\Models\CustomPokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomPokemonController extends Controller
{
    public function index(): JsonResponse
    {
        $list = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);
        return response()->json($list);
    }

    public function store(StoreCustomPokemonRequest $request): JsonResponse
    {
        $data = $request->validated();
        $cp = CustomPokemon::create([
            'user_id'       => Auth::id(),
            'pokemon_id'    => $data['pokemon_id'],
            'ability_id'    => $data['ability_id'],
            'item_id'       => $data['item_id'] ?? null,
            'nature'        => $data['nature'],
            'level'         => $data['level'],
            'iv_hp'         => $data['ivs']['hp'],
            'iv_attack'     => $data['ivs']['attack'],
            'iv_defense'    => $data['ivs']['defense'],
            'iv_sp_attack'  => $data['ivs']['sp_attack'],
            'iv_sp_defense' => $data['ivs']['sp_defense'],
            'iv_speed'      => $data['ivs']['speed'],
            'ev_hp'         => $data['evs']['hp'],
            'ev_attack'     => $data['evs']['attack'],
            'ev_defense'    => $data['evs']['defense'],
            'ev_sp_attack'  => $data['evs']['sp_attack'],
            'ev_sp_defense' => $data['evs']['sp_defense'],
            'ev_speed'      => $data['evs']['speed'],
            'nickname'      => $data['nickname'] ?? null,
            'memo'          => $data['memo'] ?? null,
        ]);

        if (!empty($data['move_ids'])) {
            $syncData = [];
            foreach (array_values($data['move_ids']) as $slot => $moveId) {
                $syncData[$moveId] = ['slot' => $slot + 1];
            }
            $cp->moves()->sync($syncData);
        }

        $cp->load(['pokemon.types', 'ability', 'item', 'moves']);
        return response()->json($cp, 201);
    }

    public function show(int $id): JsonResponse
    {
        $cp = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return response()->json($cp);
    }

    public function update(StoreCustomPokemonRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $cp = CustomPokemon::where('user_id', Auth::id())->findOrFail($id);

        $cp->update([
            'pokemon_id'    => $data['pokemon_id'],
            'ability_id'    => $data['ability_id'],
            'item_id'       => $data['item_id'] ?? null,
            'nature'        => $data['nature'],
            'level'         => $data['level'],
            'iv_hp'         => $data['ivs']['hp'],
            'iv_attack'     => $data['ivs']['attack'],
            'iv_defense'    => $data['ivs']['defense'],
            'iv_sp_attack'  => $data['ivs']['sp_attack'],
            'iv_sp_defense' => $data['ivs']['sp_defense'],
            'iv_speed'      => $data['ivs']['speed'],
            'ev_hp'         => $data['evs']['hp'],
            'ev_attack'     => $data['evs']['attack'],
            'ev_defense'    => $data['evs']['defense'],
            'ev_sp_attack'  => $data['evs']['sp_attack'],
            'ev_sp_defense' => $data['evs']['sp_defense'],
            'ev_speed'      => $data['evs']['speed'],
            'nickname'      => $data['nickname'] ?? null,
            'memo'          => $data['memo'] ?? null,
        ]);

        if (isset($data['move_ids'])) {
            $syncData = [];
            foreach (array_values($data['move_ids']) as $slot => $moveId) {
                $syncData[$moveId] = ['slot' => $slot + 1];
            }
            $cp->moves()->sync($syncData);
        }

        $cp->load(['pokemon.types', 'ability', 'item', 'moves']);
        return response()->json($cp);
    }

    public function destroy(int $id): JsonResponse
    {
        CustomPokemon::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}

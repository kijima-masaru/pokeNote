<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DamageCalcRequest;
use App\Models\CustomPokemon;
use App\Models\Move;
use App\Services\DamageCalculatorService;
use Illuminate\Http\JsonResponse;

class DamageCalcController extends Controller
{
    public function __construct(private DamageCalculatorService $service) {}

    public function calculate(DamageCalcRequest $request): JsonResponse
    {
        $attacker = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])->findOrFail($request->attacker_id);
        $defender = CustomPokemon::with(['pokemon.types', 'ability', 'item', 'moves'])->findOrFail($request->defender_id);
        $move = Move::findOrFail($request->move_id);

        $result = $this->service->calculate(
            $attacker,
            $defender,
            $move,
            $request->input('attacker_rank', []),
            $request->input('defender_rank', []),
            $request->input('weather', 'none'),
            $request->input('terrain', 'none'),
            (bool) $request->input('is_critical', false),
            $request->input('other_modifiers', []),
        );

        return response()->json($result);
    }
}

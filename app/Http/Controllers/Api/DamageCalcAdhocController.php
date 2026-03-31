<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Move;
use App\Models\Pokemon;
use App\Services\DamageCalculatorService;
use App\Services\StatCalculatorService;
use App\Enums\Nature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DamageCalcAdhocController extends Controller
{
    public function __construct(
        private DamageCalculatorService $damageService,
        private StatCalculatorService $statService,
    ) {}

    /**
     * アドホック入力によるダメージ計算
     *
     * attacker / defender に pokemon_id, level, nature, evs, ivs を受け取り
     * 実数値を計算してからダメージ計算を行う
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'attacker.pokemon_id'  => 'required|exists:pokemon,id',
            'attacker.level'       => 'required|integer|min:1|max:100',
            'attacker.nature'      => 'required|string',
            'attacker.evs'         => 'required|array',
            'attacker.ivs'         => 'required|array',
            'defender.pokemon_id'  => 'required|exists:pokemon,id',
            'defender.level'       => 'required|integer|min:1|max:100',
            'defender.nature'      => 'required|string',
            'defender.evs'         => 'required|array',
            'defender.ivs'         => 'required|array',
            'move_id'              => 'required|exists:moves,id',
            'attacker_rank'        => 'nullable|array',
            'attacker_rank.*'      => 'integer|min:-6|max:6',
            'defender_rank'        => 'nullable|array',
            'defender_rank.*'      => 'integer|min:-6|max:6',
            'weather'              => 'nullable|string|in:none,sunny,rainy,sandstorm,hail,snow',
            'terrain'              => 'nullable|string|in:none,grassy,electric,misty,psychic',
            'is_critical'          => 'nullable|boolean',
            'other_modifiers'      => 'nullable|array',
            'extra_damage'         => 'nullable|array',
            'defender_hp_percent'  => 'nullable|numeric|min:0.01|max:1',
        ]);

        $attackerPokemon = Pokemon::with('types')->findOrFail($request->input('attacker.pokemon_id'));
        $defenderPokemon = Pokemon::with('types')->findOrFail($request->input('defender.pokemon_id'));
        $move = Move::findOrFail($request->move_id);

        $attackerStats = $this->calcStats($attackerPokemon, $request->input('attacker'));
        $defenderStats = $this->calcStats($defenderPokemon, $request->input('defender'));

        $attackerTypes = $attackerPokemon->types->pluck('type')->toArray();
        $defenderTypes = $defenderPokemon->types->pluck('type')->toArray();

        $result = $this->damageService->calculateFromRaw(
            $attackerStats,
            $attackerTypes,
            (int) $request->input('attacker.level'),
            $defenderStats,
            $defenderTypes,
            $move,
            $request->input('attacker_rank', []),
            $request->input('defender_rank', []),
            $request->input('weather', 'none'),
            $request->input('terrain', 'none'),
            (bool) $request->input('is_critical', false),
            $request->input('other_modifiers', []),
            $request->input('extra_damage', []),
            (float) $request->input('defender_hp_percent', 1.0),
        );

        return response()->json(array_merge($result, [
            'attacker_stats' => $attackerStats,
            'defender_stats' => $defenderStats,
        ]));
    }

    private function calcStats(Pokemon $pokemon, array $data): array
    {
        $level  = (int) $data['level'];
        $nature = Nature::from($data['nature']);
        $ivs    = $data['ivs'];
        $evs    = $data['evs'];

        return [
            'hp'         => $this->statService->calcHp($pokemon->base_hp, (int)($ivs['hp'] ?? 31), (int)($evs['hp'] ?? 0), $level),
            'attack'     => $this->statService->calcStat($pokemon->base_attack,     (int)($ivs['attack'] ?? 31),     (int)($evs['attack'] ?? 0),     $level, $nature, 'attack'),
            'defense'    => $this->statService->calcStat($pokemon->base_defense,    (int)($ivs['defense'] ?? 31),    (int)($evs['defense'] ?? 0),    $level, $nature, 'defense'),
            'sp_attack'  => $this->statService->calcStat($pokemon->base_sp_attack,  (int)($ivs['sp_attack'] ?? 31),  (int)($evs['sp_attack'] ?? 0),  $level, $nature, 'sp_attack'),
            'sp_defense' => $this->statService->calcStat($pokemon->base_sp_defense, (int)($ivs['sp_defense'] ?? 31), (int)($evs['sp_defense'] ?? 0), $level, $nature, 'sp_defense'),
            'speed'      => $this->statService->calcStat($pokemon->base_speed,      (int)($ivs['speed'] ?? 31),      (int)($evs['speed'] ?? 0),      $level, $nature, 'speed'),
        ];
    }
}

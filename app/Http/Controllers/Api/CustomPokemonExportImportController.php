<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\CustomPokemon;
use App\Models\Item;
use App\Models\Move;
use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomPokemonExportImportController extends Controller
{
    /**
     * 指定IDのカスタムポケモンをJSON形式でエクスポート
     * GET /api/v1/custom-pokemon/{id}/export
     */
    public function export(int $id): JsonResponse
    {
        $cp = CustomPokemon::with(['pokemon', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $data = [
            'version'      => 1,
            'exported_at'  => now()->toIso8601String(),
            'pokemon_name' => $cp->pokemon->name_ja,
            'pokemon_en'   => $cp->pokemon->name_en,
            'nickname'     => $cp->nickname,
            'nature'       => $cp->nature,
            'ability_name' => $cp->ability->name_ja,
            'ability_en'   => $cp->ability->name_en,
            'item_name'    => $cp->item?->name_ja,
            'item_en'      => $cp->item?->name_en,
            'level'        => $cp->level,
            'ivs' => [
                'hp'         => $cp->iv_hp,
                'attack'     => $cp->iv_attack,
                'defense'    => $cp->iv_defense,
                'sp_attack'  => $cp->iv_sp_attack,
                'sp_defense' => $cp->iv_sp_defense,
                'speed'      => $cp->iv_speed,
            ],
            'evs' => [
                'hp'         => $cp->ev_hp,
                'attack'     => $cp->ev_attack,
                'defense'    => $cp->ev_defense,
                'sp_attack'  => $cp->ev_sp_attack,
                'sp_defense' => $cp->ev_sp_defense,
                'speed'      => $cp->ev_speed,
            ],
            'moves' => $cp->moves->sortBy('pivot.slot')->map(fn($m) => [
                'name_ja' => $m->name_ja,
                'name_en' => $m->name_en,
            ])->values()->toArray(),
            'memo' => $cp->memo,
        ];

        return response()->json($data);
    }

    /**
     * 全カスタムポケモンを一括エクスポート
     * GET /api/v1/custom-pokemon/export-all
     */
    public function exportAll(): JsonResponse
    {
        $list = CustomPokemon::with(['pokemon', 'ability', 'item', 'moves'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $data = [
            'version'     => 1,
            'exported_at' => now()->toIso8601String(),
            'count'       => $list->count(),
            'pokemon'     => $list->map(fn($cp) => $this->buildExportData($cp))->values()->toArray(),
        ];

        return response()->json($data);
    }

    /**
     * JSONからカスタムポケモンをインポート
     * POST /api/v1/custom-pokemon/import
     *
     * Body: { "data": [...] }  ← exportAll の pokemon 配列、または単体の配列
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'data'                     => 'required|array|min:1|max:100',
            'data.*.pokemon_en'        => 'required|string',
            'data.*.nature'            => 'required|string',
            'data.*.ability_en'        => 'required|string',
            'data.*.level'             => 'required|integer|min:1|max:100',
            'data.*.ivs'               => 'required|array',
            'data.*.evs'               => 'required|array',
        ]);

        $userId   = Auth::id();
        $imported = [];
        $failed   = [];

        foreach ($request->data as $index => $entry) {
            try {
                $pokemon = Pokemon::where('name_en', $entry['pokemon_en'])->first();
                if (!$pokemon) {
                    $failed[] = ['index' => $index, 'reason' => "ポケモン '{$entry['pokemon_en']}' が見つかりません"];
                    continue;
                }

                $ability = Ability::where('name_en', $entry['ability_en'])->first();
                if (!$ability) {
                    $failed[] = ['index' => $index, 'reason' => "特性 '{$entry['ability_en']}' が見つかりません"];
                    continue;
                }

                $item = null;
                if (!empty($entry['item_en'])) {
                    $item = Item::where('name_en', $entry['item_en'])->first();
                }

                $ivs = $entry['ivs'];
                $evs = $entry['evs'];

                $cp = CustomPokemon::create([
                    'user_id'       => $userId,
                    'pokemon_id'    => $pokemon->id,
                    'ability_id'    => $ability->id,
                    'item_id'       => $item?->id,
                    'nature'        => $entry['nature'],
                    'level'         => $entry['level'],
                    'nickname'      => $entry['nickname'] ?? null,
                    'memo'          => $entry['memo'] ?? null,
                    'iv_hp'         => $ivs['hp']         ?? 31,
                    'iv_attack'     => $ivs['attack']     ?? 31,
                    'iv_defense'    => $ivs['defense']    ?? 31,
                    'iv_sp_attack'  => $ivs['sp_attack']  ?? 31,
                    'iv_sp_defense' => $ivs['sp_defense'] ?? 31,
                    'iv_speed'      => $ivs['speed']      ?? 31,
                    'ev_hp'         => $evs['hp']         ?? 0,
                    'ev_attack'     => $evs['attack']     ?? 0,
                    'ev_defense'    => $evs['defense']    ?? 0,
                    'ev_sp_attack'  => $evs['sp_attack']  ?? 0,
                    'ev_sp_defense' => $evs['sp_defense'] ?? 0,
                    'ev_speed'      => $evs['speed']      ?? 0,
                ]);

                // わざ設定
                if (!empty($entry['moves'])) {
                    $syncData = [];
                    foreach (array_values($entry['moves']) as $slot => $moveData) {
                        $moveName = $moveData['name_en'] ?? $moveData['name_ja'] ?? null;
                        if (!$moveName) continue;
                        $move = Move::where('name_en', $moveName)
                            ->orWhere('name_ja', $moveName)
                            ->first();
                        if ($move) {
                            $syncData[$move->id] = ['slot' => $slot + 1];
                        }
                    }
                    if ($syncData) $cp->moves()->sync($syncData);
                }

                $imported[] = $cp->id;
            } catch (\Throwable $e) {
                $failed[] = ['index' => $index, 'reason' => $e->getMessage()];
            }
        }

        return response()->json([
            'imported_count' => count($imported),
            'failed_count'   => count($failed),
            'imported_ids'   => $imported,
            'failed'         => $failed,
        ], count($imported) > 0 ? 200 : 422);
    }

    /**
     * CSVからカスタムポケモンを一括登録
     * POST /api/v1/custom-pokemon/import-csv
     *
     * CSV形式: pokemon_en,nickname,nature,ability_en,item_en,level,
     *          iv_hp,iv_atk,iv_def,iv_spa,iv_spd,iv_spe,
     *          ev_hp,ev_atk,ev_def,ev_spa,ev_spd,ev_spe,
     *          move1,move2,move3,move4,memo
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = null;
        $rows   = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map('trim', $row);
                continue;
            }
            if (count($row) < 2) continue;
            $rows[] = array_combine(
                array_slice($header, 0, count($row)),
                array_map('trim', $row)
            );
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json(['message' => 'CSVにデータ行がありません'], 422);
        }

        $userId   = Auth::id();
        $imported = [];
        $failed   = [];

        foreach ($rows as $index => $row) {
            try {
                $pokemonEn = $row['pokemon_en'] ?? '';
                $pokemon   = Pokemon::where('name_en', $pokemonEn)
                    ->orWhere('name_ja', $pokemonEn)
                    ->first();
                if (!$pokemon) {
                    $failed[] = ['row' => $index + 2, 'reason' => "ポケモン '{$pokemonEn}' が見つかりません"];
                    continue;
                }

                $abilityEn = $row['ability_en'] ?? '';
                $ability   = Ability::where('name_en', $abilityEn)
                    ->orWhere('name_ja', $abilityEn)
                    ->first();
                if (!$ability) {
                    $failed[] = ['row' => $index + 2, 'reason' => "特性 '{$abilityEn}' が見つかりません"];
                    continue;
                }

                $item = null;
                if (!empty($row['item_en'])) {
                    $item = Item::where('name_en', $row['item_en'])
                        ->orWhere('name_ja', $row['item_en'])
                        ->first();
                }

                $cp = CustomPokemon::create([
                    'user_id'       => $userId,
                    'pokemon_id'    => $pokemon->id,
                    'ability_id'    => $ability->id,
                    'item_id'       => $item?->id,
                    'nature'        => $row['nature']  ?? 'hardy',
                    'level'         => (int)($row['level'] ?? 50),
                    'nickname'      => $row['nickname'] ?? null,
                    'memo'          => $row['memo']     ?? null,
                    'iv_hp'         => (int)($row['iv_hp']  ?? 31),
                    'iv_attack'     => (int)($row['iv_atk'] ?? 31),
                    'iv_defense'    => (int)($row['iv_def'] ?? 31),
                    'iv_sp_attack'  => (int)($row['iv_spa'] ?? 31),
                    'iv_sp_defense' => (int)($row['iv_spd'] ?? 31),
                    'iv_speed'      => (int)($row['iv_spe'] ?? 31),
                    'ev_hp'         => (int)($row['ev_hp']  ?? 0),
                    'ev_attack'     => (int)($row['ev_atk'] ?? 0),
                    'ev_defense'    => (int)($row['ev_def'] ?? 0),
                    'ev_sp_attack'  => (int)($row['ev_spa'] ?? 0),
                    'ev_sp_defense' => (int)($row['ev_spd'] ?? 0),
                    'ev_speed'      => (int)($row['ev_spe'] ?? 0),
                ]);

                // わざ
                $syncData = [];
                foreach (['move1','move2','move3','move4'] as $slot => $key) {
                    if (empty($row[$key])) continue;
                    $move = Move::where('name_en', $row[$key])
                        ->orWhere('name_ja', $row[$key])
                        ->first();
                    if ($move) $syncData[$move->id] = ['slot' => $slot + 1];
                }
                if ($syncData) $cp->moves()->sync($syncData);

                $imported[] = $cp->id;
            } catch (\Throwable $e) {
                $failed[] = ['row' => $index + 2, 'reason' => $e->getMessage()];
            }
        }

        return response()->json([
            'imported_count' => count($imported),
            'failed_count'   => count($failed),
            'imported_ids'   => $imported,
            'failed'         => $failed,
        ], count($imported) > 0 ? 200 : 422);
    }

    private function buildExportData(CustomPokemon $cp): array
    {
        return [
            'pokemon_name' => $cp->pokemon->name_ja,
            'pokemon_en'   => $cp->pokemon->name_en,
            'nickname'     => $cp->nickname,
            'nature'       => $cp->nature,
            'ability_name' => $cp->ability->name_ja,
            'ability_en'   => $cp->ability->name_en,
            'item_name'    => $cp->item?->name_ja,
            'item_en'      => $cp->item?->name_en,
            'level'        => $cp->level,
            'ivs' => [
                'hp'         => $cp->iv_hp,
                'attack'     => $cp->iv_attack,
                'defense'    => $cp->iv_defense,
                'sp_attack'  => $cp->iv_sp_attack,
                'sp_defense' => $cp->iv_sp_defense,
                'speed'      => $cp->iv_speed,
            ],
            'evs' => [
                'hp'         => $cp->ev_hp,
                'attack'     => $cp->ev_attack,
                'defense'    => $cp->ev_defense,
                'sp_attack'  => $cp->ev_sp_attack,
                'sp_defense' => $cp->ev_sp_defense,
                'speed'      => $cp->ev_speed,
            ],
            'moves' => $cp->moves->sortBy('pivot.slot')->map(fn($m) => [
                'name_ja' => $m->name_ja,
                'name_en' => $m->name_en,
            ])->values()->toArray(),
            'memo' => $cp->memo,
        ];
    }
}

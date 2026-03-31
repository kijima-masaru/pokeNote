<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\Move;
use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PokeApiImportController extends Controller
{
    private const BASE_URL = 'https://pokeapi.co/api/v2';

    /**
     * PokeAPIからポケモンを1体インポート
     * POST /api/v1/import/pokemon  { "id_or_name": "6" }
     */
    public function importPokemon(Request $request): JsonResponse
    {
        $request->validate(['id_or_name' => 'required|string']);
        $idOrName = strtolower(trim($request->id_or_name));

        $response = Http::timeout(10)->get(self::BASE_URL . "/pokemon/{$idOrName}");
        if ($response->failed()) {
            return response()->json(['error' => "ポケモン「{$idOrName}」が見つかりませんでした"], 404);
        }
        $data = $response->json();

        // 種名（日本語）を取得
        $speciesRes = Http::timeout(10)->get($data['species']['url']);
        $nameJa = $idOrName;
        if ($speciesRes->ok()) {
            $nameJa = collect($speciesRes->json('names'))
                ->firstWhere('language.name', 'ja-Hrkt')['name']
                ?? collect($speciesRes->json('names'))->firstWhere('language.name', 'ja')['name']
                ?? $data['name'];
        }

        $types = collect($data['types'])->sortBy('slot')->pluck('type.name')->toArray();
        $spriteUrl = $data['sprites']['front_default'] ?? null;

        $stats = collect($data['stats'])->mapWithKeys(fn($s) => [
            str_replace('-', '_', $s['stat']['name']) => $s['base_stat']
        ]);

        DB::beginTransaction();
        try {
            $pokemon = Pokemon::updateOrCreate(
                ['pokedex_number' => $data['id']],
                [
                    'name_ja'         => $nameJa,
                    'name_en'         => $data['name'],
                    'base_hp'         => $stats['hp'] ?? 50,
                    'base_attack'     => $stats['attack'] ?? 50,
                    'base_defense'    => $stats['defense'] ?? 50,
                    'base_sp_attack'  => $stats['special_attack'] ?? 50,
                    'base_sp_defense' => $stats['special_defense'] ?? 50,
                    'base_speed'      => $stats['speed'] ?? 50,
                    'sprite_url'      => $spriteUrl,
                ]
            );

            // タイプを更新
            $pokemon->types()->delete();
            foreach ($types as $slot => $type) {
                $pokemon->types()->create(['type' => $type, 'slot' => $slot + 1]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $pokemon->load('types');
        return response()->json($pokemon, 201);
    }

    /**
     * PokeAPIから指定範囲のポケモンを一括インポート
     * POST /api/v1/import/pokemon/bulk  { "from": 1, "to": 20 }
     */
    public function importPokemonBulk(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|integer|min:1',
            'to'   => 'required|integer|min:1|max:1025',
        ]);

        $from = (int) $request->from;
        $to   = min((int) $request->to, $from + 49); // 最大50件ずつ

        $results = ['success' => [], 'failed' => []];

        for ($i = $from; $i <= $to; $i++) {
            try {
                $response = Http::timeout(10)->get(self::BASE_URL . "/pokemon/{$i}");
                if ($response->failed()) {
                    $results['failed'][] = $i;
                    continue;
                }
                $data = $response->json();

                $speciesRes = Http::timeout(10)->get($data['species']['url']);
                $nameJa = $data['name'];
                if ($speciesRes->ok()) {
                    $nameJa = collect($speciesRes->json('names'))
                        ->firstWhere('language.name', 'ja-Hrkt')['name']
                        ?? collect($speciesRes->json('names'))->firstWhere('language.name', 'ja')['name']
                        ?? $data['name'];
                }

                $types = collect($data['types'])->sortBy('slot')->pluck('type.name')->toArray();
                $stats = collect($data['stats'])->mapWithKeys(fn($s) => [
                    str_replace('-', '_', $s['stat']['name']) => $s['base_stat']
                ]);

                DB::beginTransaction();
                $pokemon = Pokemon::updateOrCreate(
                    ['pokedex_number' => $data['id']],
                    [
                        'name_ja'         => $nameJa,
                        'name_en'         => $data['name'],
                        'base_hp'         => $stats['hp'] ?? 50,
                        'base_attack'     => $stats['attack'] ?? 50,
                        'base_defense'    => $stats['defense'] ?? 50,
                        'base_sp_attack'  => $stats['special_attack'] ?? 50,
                        'base_sp_defense' => $stats['special_defense'] ?? 50,
                        'base_speed'      => $stats['speed'] ?? 50,
                        'sprite_url'      => $data['sprites']['front_default'] ?? null,
                    ]
                );
                $pokemon->types()->delete();
                foreach ($types as $slot => $type) {
                    $pokemon->types()->create(['type' => $type, 'slot' => $slot + 1]);
                }
                DB::commit();

                $results['success'][] = ['id' => $data['id'], 'name' => $nameJa];
            } catch (\Exception $e) {
                DB::rollBack();
                $results['failed'][] = $i;
            }
        }

        return response()->json($results);
    }

    /**
     * PokeAPIから進化チェーンをインポート
     * POST /api/v1/import/evolutions  { "pokemon_id": 4 }  (pokedex_number)
     */
    public function importEvolutions(Request $request): JsonResponse
    {
        $request->validate(['pokemon_id' => 'required|integer|min:1']);
        $pokedexNum = (int) $request->pokemon_id;

        // まずpokemon speciesを取得
        $speciesRes = Http::timeout(10)->get(self::BASE_URL . "/pokemon-species/{$pokedexNum}");
        if ($speciesRes->failed()) {
            return response()->json(['error' => "ポケモンが見つかりませんでした"], 404);
        }
        $speciesData = $speciesRes->json();

        // 進化チェーンURLを取得
        $chainUrl = $speciesData['evolution_chain']['url'] ?? null;
        if (!$chainUrl) {
            return response()->json(['message' => '進化チェーンなし', 'count' => 0]);
        }

        $chainRes = Http::timeout(10)->get($chainUrl);
        if ($chainRes->failed()) {
            return response()->json(['error' => '進化チェーン取得失敗'], 500);
        }

        $chainData = $chainRes->json();
        $count = 0;

        // 再帰的に進化チェーンを処理
        $this->processEvolutionChain($chainData['chain'], null, $count);

        return response()->json(['message' => "進化チェーンを{$count}件登録しました", 'count' => $count]);
    }

    private function processEvolutionChain(array $chain, ?int $fromPokedexNum, int &$count): void
    {
        $toPokedexNum = (int) (explode('/', rtrim($chain['species']['url'], '/'))[count(explode('/', rtrim($chain['species']['url'], '/'))) - 1]);

        if ($fromPokedexNum !== null) {
            $fromPokemon = Pokemon::where('pokedex_number', $fromPokedexNum)->first();
            $toPokemon   = Pokemon::where('pokedex_number', $toPokedexNum)->first();

            if ($fromPokemon && $toPokemon) {
                $details = $chain['evolution_details'][0] ?? [];
                $method  = $details['trigger']['name'] ?? null;
                $minLevel = $details['min_level'] ?? null;
                $triggerItem = $details['item']['name'] ?? $details['held_item']['name'] ?? null;

                // PokeAPI trigger名 → 内部名
                $methodMap = ['level-up' => 'level_up', 'use-item' => 'use_item', 'trade' => 'trade'];
                $method = $methodMap[$method] ?? $method;

                DB::table('pokemon_evolutions')->updateOrInsert(
                    ['from_pokemon_id' => $fromPokemon->id, 'to_pokemon_id' => $toPokemon->id],
                    ['method' => $method, 'min_level' => $minLevel, 'trigger_item' => $triggerItem,
                     'created_at' => now(), 'updated_at' => now()]
                );
                $count++;
            }
        }

        foreach ($chain['evolves_to'] as $next) {
            $this->processEvolutionChain($next, $toPokedexNum, $count);
        }
    }

    /**
     * PokeAPIからわざを1つインポート
     * POST /api/v1/import/move  { "id_or_name": "flamethrower" }
     */
    public function importMove(Request $request): JsonResponse
    {
        $request->validate(['id_or_name' => 'required|string']);
        $idOrName = strtolower(trim($request->id_or_name));

        $response = Http::timeout(10)->get(self::BASE_URL . "/move/{$idOrName}");
        if ($response->failed()) {
            return response()->json(['error' => "わざ「{$idOrName}」が見つかりませんでした"], 404);
        }
        $data = $response->json();

        $nameJa = collect($data['names'])->firstWhere('language.name', 'ja-Hrkt')['name']
            ?? collect($data['names'])->firstWhere('language.name', 'ja')['name']
            ?? $data['name'];

        $typeMap = [
            'normal'=>'normal','fire'=>'fire','water'=>'water','electric'=>'electric','grass'=>'grass',
            'ice'=>'ice','fighting'=>'fighting','poison'=>'poison','ground'=>'ground','flying'=>'flying',
            'psychic'=>'psychic','bug'=>'bug','rock'=>'rock','ghost'=>'ghost','dragon'=>'dragon',
            'dark'=>'dark','steel'=>'steel','fairy'=>'fairy',
        ];
        $type = $typeMap[$data['type']['name']] ?? 'normal';

        $categoryMap = ['physical' => 'physical', 'special' => 'special', 'status' => 'status'];
        $category = $categoryMap[$data['damage_class']['name']] ?? 'status';

        $move = Move::updateOrCreate(
            ['name_en' => $data['name']],
            [
                'name_ja'       => $nameJa,
                'type'          => $type,
                'category'      => $category,
                'power'         => $data['power'],
                'accuracy'      => $data['accuracy'],
                'pp'            => $data['pp'] ?? 10,
                'priority'      => $data['priority'] ?? 0,
                'makes_contact' => collect($data['meta']['flags'] ?? [])->contains(fn($f) => $f['name'] === 'contact'),
            ]
        );

        return response()->json($move, 201);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pokemon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PokemonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pokemon::with(['types', 'abilities']);

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ja', 'like', '%' . $request->name . '%')
                  ->orWhere('name_en', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('types', fn($q) => $q->where('type', $request->type));
        }

        if ($request->filled('type2')) {
            $query->whereHas('types', fn($q) => $q->where('type', $request->type2));
        }

        if ($request->filled('bst_min')) {
            $query->whereRaw('(base_hp + base_attack + base_defense + base_sp_attack + base_sp_defense + base_speed) >= ?', [(int)$request->bst_min]);
        }

        if ($request->filled('bst_max')) {
            $query->whereRaw('(base_hp + base_attack + base_defense + base_sp_attack + base_sp_defense + base_speed) <= ?', [(int)$request->bst_max]);
        }

        if ($request->filled('ids')) {
            $ids = array_filter(array_map('intval', (array)$request->get('ids')));
            if (!empty($ids)) $query->whereIn('id', $ids);
        }

        if ($request->filled('is_mega')) {
            $query->where('is_mega', (bool)$request->is_mega);
        }

        $sortable = ['pokedex_number', 'base_hp', 'base_attack', 'base_defense',
                     'base_sp_attack', 'base_sp_defense', 'base_speed'];
        $sortCol  = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'pokedex_number';
        $sortDir  = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        // 図鑑番号はデフォルト昇順
        if ($sortCol === 'pokedex_number' && !$request->filled('sort_dir')) $sortDir = 'asc';

        $pokemon = $query->orderBy($sortCol, $sortDir)
            ->paginate($request->get('per_page', 50));

        return response()->json($pokemon);
    }

    public function show(int $id): JsonResponse
    {
        $pokemon = Pokemon::with(['types', 'abilities', 'moves', 'evolvesFrom', 'evolvesTo'])->findOrFail($id);
        return response()->json($pokemon);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pokedex_number'  => 'required|integer|unique:pokemon',
            'name_ja'         => 'required|string|max:50',
            'name_en'         => 'required|string|max:50',
            'form_name'       => 'nullable|string|max:50',
            'base_hp'         => 'required|integer|min:1',
            'base_attack'     => 'required|integer|min:1',
            'base_defense'    => 'required|integer|min:1',
            'base_sp_attack'  => 'required|integer|min:1',
            'base_sp_defense' => 'required|integer|min:1',
            'base_speed'      => 'required|integer|min:1',
            'sprite_url'      => 'nullable|string|max:255',
            'types'           => 'required|array|min:1|max:2',
            'types.*'         => 'string',
        ]);

        $pokemon = Pokemon::create(collect($data)->except('types')->toArray());

        foreach ($data['types'] as $slot => $type) {
            $pokemon->types()->create(['type' => $type, 'slot' => $slot + 1]);
        }

        $pokemon->load('types');
        return response()->json($pokemon, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pokemon = Pokemon::findOrFail($id);
        $data = $request->validate([
            'pokedex_number'  => 'required|integer|unique:pokemon,pokedex_number,'.$id,
            'name_ja'         => 'required|string|max:50',
            'name_en'         => 'required|string|max:50',
            'form_name'       => 'nullable|string|max:50',
            'base_hp'         => 'required|integer|min:1',
            'base_attack'     => 'required|integer|min:1',
            'base_defense'    => 'required|integer|min:1',
            'base_sp_attack'  => 'required|integer|min:1',
            'base_sp_defense' => 'required|integer|min:1',
            'base_speed'      => 'required|integer|min:1',
            'sprite_url'      => 'nullable|string|max:255',
            'types'           => 'required|array|min:1|max:2',
            'types.*'         => 'string',
        ]);

        $pokemon->update(collect($data)->except('types')->toArray());

        $pokemon->types()->delete();
        foreach ($data['types'] as $slot => $type) {
            $pokemon->types()->create(['type' => $type, 'slot' => $slot + 1]);
        }

        $pokemon->load('types');
        return response()->json($pokemon);
    }

    public function destroy(int $id): JsonResponse
    {
        Pokemon::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * ポケモン画像のアップロード
     * POST /api/v1/pokemon/{id}/image
     */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        $pokemon = Pokemon::findOrFail($id);

        // 既存のローカル画像を削除
        if ($pokemon->sprite_url && str_starts_with($pokemon->sprite_url, '/storage/')) {
            $oldPath = str_replace('/storage/', 'public/', $pokemon->sprite_url);
            Storage::delete($oldPath);
        }

        $path = $request->file('image')->store("public/pokemon");
        $url  = '/storage/' . str_replace('public/', '', $path);

        $pokemon->update(['sprite_url' => $url]);

        return response()->json(['sprite_url' => $url]);
    }
}

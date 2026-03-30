<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ability::query();
        if ($request->filled('name')) {
            $query->where('name_ja', 'like', '%' . $request->name . '%');
        }
        return response()->json($query->orderBy('name_ja')->paginate(100));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Ability::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_ja'     => 'required|string|max:50|unique:abilities',
            'name_en'     => 'required|string|max:50|unique:abilities',
            'description' => 'nullable|string',
        ]);
        return response()->json(Ability::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ability = Ability::findOrFail($id);
        $data = $request->validate([
            'name_ja'     => 'required|string|max:50|unique:abilities,name_ja,'.$id,
            'name_en'     => 'required|string|max:50|unique:abilities,name_en,'.$id,
            'description' => 'nullable|string',
        ]);
        $ability->update($data);
        return response()->json($ability);
    }

    public function destroy(int $id): JsonResponse
    {
        Ability::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}

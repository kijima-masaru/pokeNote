<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Move;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Move::query();

        if ($request->filled('name')) {
            $query->where('name_ja', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->orderBy('name_ja')->paginate($request->get('per_page', 100)));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Move::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_ja'       => 'required|string|max:50|unique:moves',
            'name_en'       => 'required|string|max:50|unique:moves',
            'type'          => 'required|string|max:20',
            'category'      => 'required|in:physical,special,status',
            'power'         => 'nullable|integer|min:1|max:999',
            'accuracy'      => 'nullable|integer|min:1|max:100',
            'pp'            => 'required|integer|min:1|max:64',
            'priority'      => 'nullable|integer|min:-7|max:5',
            'makes_contact' => 'nullable|boolean',
            'description'   => 'nullable|string',
        ]);
        return response()->json(Move::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $move = Move::findOrFail($id);
        $data = $request->validate([
            'name_ja'       => 'required|string|max:50|unique:moves,name_ja,'.$id,
            'name_en'       => 'required|string|max:50|unique:moves,name_en,'.$id,
            'type'          => 'required|string|max:20',
            'category'      => 'required|in:physical,special,status',
            'power'         => 'nullable|integer|min:1|max:999',
            'accuracy'      => 'nullable|integer|min:1|max:100',
            'pp'            => 'required|integer|min:1|max:64',
            'priority'      => 'nullable|integer|min:-7|max:5',
            'makes_contact' => 'nullable|boolean',
            'description'   => 'nullable|string',
        ]);
        $move->update($data);
        return response()->json($move);
    }

    public function destroy(int $id): JsonResponse
    {
        Move::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::query();
        if ($request->filled('name')) {
            $query->where('name_ja', 'like', '%' . $request->name . '%');
        }
        return response()->json($query->orderBy('name_ja')->paginate(100));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Item::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_ja'     => 'required|string|max:50|unique:items',
            'name_en'     => 'required|string|max:50|unique:items',
            'category'    => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        return response()->json(Item::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $data = $request->validate([
            'name_ja'     => 'required|string|max:50|unique:items,name_ja,'.$id,
            'name_en'     => 'required|string|max:50|unique:items,name_en,'.$id,
            'category'    => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $item->update($data);
        return response()->json($item);
    }

    public function destroy(int $id): JsonResponse
    {
        Item::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * 道具画像アップロード
     * POST /api/v1/items/{id}/image
     */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        $item = Item::findOrFail($id);

        if ($item->image_url && str_starts_with($item->image_url, '/storage/')) {
            Storage::delete(str_replace('/storage/', 'public/', $item->image_url));
        }

        $path = $request->file('image')->store('public/items');
        $url  = '/storage/' . str_replace('public/', '', $path);

        $item->update(['image_url' => $url]);

        return response()->json(['image_url' => $url]);
    }
}

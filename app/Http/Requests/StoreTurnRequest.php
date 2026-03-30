<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'turn_number'           => 'required|integer|min:1',
            'my_pokemon_id'         => 'nullable|exists:custom_pokemon,id',
            'opponent_pokemon_name' => 'nullable|string|max:50',
            'my_move_id'            => 'nullable|exists:moves,id',
            'opponent_move_id'      => 'nullable|exists:moves,id',
            'my_hp_remaining'       => 'nullable|integer|min:0|max:100',
            'opponent_hp_remaining' => 'nullable|integer|min:0|max:100',
            'description'           => 'nullable|string',
        ];
    }
}

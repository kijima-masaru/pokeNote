<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomPokemonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pokemon_id'     => 'required|exists:pokemon,id',
            'ability_id'     => 'required|exists:abilities,id',
            'item_id'        => 'nullable|exists:items,id',
            'nature'         => 'required|string',
            'level'          => 'required|integer|min:1|max:100',
            'ivs'            => 'required|array',
            'ivs.hp'         => 'required|integer|min:0|max:31',
            'ivs.attack'     => 'required|integer|min:0|max:31',
            'ivs.defense'    => 'required|integer|min:0|max:31',
            'ivs.sp_attack'  => 'required|integer|min:0|max:31',
            'ivs.sp_defense' => 'required|integer|min:0|max:31',
            'ivs.speed'      => 'required|integer|min:0|max:31',
            'evs'            => 'required|array',
            'evs.hp'         => 'required|integer|min:0|max:252',
            'evs.attack'     => 'required|integer|min:0|max:252',
            'evs.defense'    => 'required|integer|min:0|max:252',
            'evs.sp_attack'  => 'required|integer|min:0|max:252',
            'evs.sp_defense' => 'required|integer|min:0|max:252',
            'evs.speed'      => 'required|integer|min:0|max:252',
            'move_ids'       => 'nullable|array|max:4',
            'move_ids.*'     => 'exists:moves,id',
            'nickname'       => 'nullable|string|max:50',
            'memo'           => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $evs = $this->input('evs', []);
            $total = array_sum($evs);
            if ($total > 510) {
                $validator->errors()->add('evs', '努力値の合計は510以下にしてください。');
            }
        });
    }
}

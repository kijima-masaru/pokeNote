<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DamageCalcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attacker_id'     => 'required|exists:custom_pokemon,id',
            'defender_id'     => 'required|exists:custom_pokemon,id',
            'move_id'         => 'required|exists:moves,id',
            'attacker_rank'   => 'nullable|array',
            'attacker_rank.*' => 'integer|min:-6|max:6',
            'defender_rank'   => 'nullable|array',
            'defender_rank.*' => 'integer|min:-6|max:6',
            'weather'         => 'nullable|string|in:none,sunny,rainy,sandstorm,hail,snow',
            'terrain'         => 'nullable|string|in:none,grassy,electric,misty,psychic',
            'is_critical'        => 'nullable|boolean',
            'other_modifiers'    => 'nullable|array',
            'extra_damage'       => 'nullable|array',
            'defender_hp_percent'=> 'nullable|numeric|min:0.01|max:1',
        ];
    }
}

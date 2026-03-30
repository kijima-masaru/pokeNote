<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBattleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'nullable|string|max:100',
            'opponent_name' => 'nullable|string|max:50',
            'result'        => 'nullable|in:win,lose,draw',
            'format'        => 'nullable|string|max:50',
            'memo'          => 'nullable|string',
            'played_at'     => 'nullable|date',
        ];
    }
}

<?php

namespace Lyre\School\Http\Requests;

use Lyre\Request;

class StoreTaskRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'answers' => 'nullable|array',
            'answers.*.name' => 'required_with:answers|string',
            'answers.*.description' => 'nullable|string',
            'answers.*.is_correct' => 'boolean',
            'answers.*.score' => 'integer|min:0',
        ];
    }
}

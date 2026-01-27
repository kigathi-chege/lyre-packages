<?php

namespace Lyre\School\Http\Requests;

use Lyre\Request;

class UpdateTaskRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'description' => 'nullable|string',

            'answers' => 'required|array',
            'answers.*.id' => 'sometimes|numeric',
            'answers.*.name' => 'required|string',
            'answers.*.description' => 'nullable|string',
            'answers.*.is_correct' => 'boolean',
            'answers.*.score' => 'integer|min:0',
        ];
    }
}

<?php

namespace Lyre\School\Http\Requests;

use Lyre\Request;

class UpdateAssessmentRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'published_at' => 'nullable|date',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'facet_value_ids' => 'nullable|array',
            'facet_value_ids.*' => 'integer|exists:' . config('lyre.table_prefix') . 'facet_values,id',

            'tasks' => 'required|array',
            'tasks.*.id' => 'nullable|numeric',
            'tasks.*.slug' => 'nullable|string',
            'tasks.*.name' => 'required|string',
            'tasks.*.description' => 'nullable|string',

            'tasks.*.answers' => 'required|array',
            'tasks.*.answers.*.id' => 'nullable|numeric',
            'tasks.*.answers.*.slug' => 'nullable|string',
            'tasks.*.answers.*.name' => 'required|string',
            'tasks.*.answers.*.description' => 'nullable|string',
            'tasks.*.answers.*.is_correct' => 'boolean',
            'tasks.*.answers.*.score' => 'integer|min:0',
        ];
    }
}


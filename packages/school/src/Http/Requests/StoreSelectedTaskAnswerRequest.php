<?php

namespace Lyre\School\Http\Requests;

use Lyre\Request;

class StoreSelectedTaskAnswerRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "mode" => 'required|string',
            "task_id" => 'required|integer|exists:tasks,id',
            "task_answer_id" => 'required|integer|exists:task_answers,id',
            "assessment_id" => 'required|integer|exists:assessments,id',
        ];
    }
}


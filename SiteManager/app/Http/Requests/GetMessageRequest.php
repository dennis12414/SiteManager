<?php

namespace App\Http\Requests;

use App\Models\Chat;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class GetMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectModel = get_class(new Project());

        return [
            'projectId' => "required|exists:{$projectModel},projectId",
            'page' => 'required|numeric',
            'pageSize' => 'nullable|numeric',
        ];
    }
}

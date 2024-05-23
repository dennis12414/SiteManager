<?php

namespace App\Http\Requests;

use App\Models\Chat;
use App\Models\Project;
use App\Models\SiteManager;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
        $siteManagerModel = get_class(new SiteManager());

        return [
            'projectId'=>"required|exists:{$projectModel},projectId",
            'siteManagerId'=>"required|exists:{$siteManagerModel},siteManagerId",
            'message'=>'required|string',
        ];
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'email|unique:siteManagers',
            'phoneNumber' => 'required|numeric|unique:siteManagers',
            'profilePicture' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'firstName.required' => 'The first name field is required.',
            'firstName.string' => 'The first name field must be a string.',
            'firstName.max' => 'The first name field must not exceed 255 characters.',
            'lastName.required' => 'The last name field is required.',
            'lastName.string' => 'The last name field must be a string.',
            'lastName.max' => 'The last name field must not exceed 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'phoneNumber.required' => 'The password field is required.',
            'phoneNumber.numeric' => 'The password field must be numeric.',
            'phoneNumber.unique' => 'The phone number is already in use.',
        
        ];
   }
}

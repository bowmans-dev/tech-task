<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest 
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
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string',
            'country' => 'required|string',
            'phone' => [
                'required',
                'string',
                'regex:/^(\+?[0-9\- ]+|[0-9]{7,20})$/',
                'min:7',
                'max:20',
            ],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:8', // Password is required
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Please provide your first name.',
            'first_name.max' => 'Your first name must not exceed 255 characters.',
            'last_name.required' => 'Please provide your last name.',
            'last_name.max' => 'Your last name must not exceed 255 characters.',
            'gender.required' => 'Please select your gender.',
            'country.required' => 'Please select your country.',
            'phone.required' => 'Please provide your phone number.',
            'phone.regex' => 'Please provide a valid phone number format.',
            'phone.min' => 'Your phone number must be at least 7 characters long.',
            'phone.max' => 'Your phone number must not exceed 20 characters.',
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already in use. Please choose another one.',
            'password.required' => 'Please create a password.',
            'password.confirmed' => 'Your password confirmation does not match.',
            'password.min' => 'Your password must be at least 8 characters long.',
            'profile_picture.image' => 'The profile picture must be an image file.',
            'profile_picture.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg, gif, webp.',
            'profile_picture.max' => 'The profile picture must not exceed 2MB.',
        ];
    }
}

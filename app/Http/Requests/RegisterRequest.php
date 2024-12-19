<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'email' => 'nullable|string|email|max:150|unique:customers',
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/|unique:customers',
            'referral_code' => 'nullable|string|max:10',
            'password' => 'required|string|min:8|confirmed'
        ];
    }
}

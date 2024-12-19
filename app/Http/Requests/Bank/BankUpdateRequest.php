<?php

namespace App\Http\Requests\Bank;

use Illuminate\Foundation\Http\FormRequest;

class BankUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:banks,code'],
            'name' => ['required', 'string', 'max:100', 'unique:banks,name'],
            'icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:512'],
            'alias' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive']
        ];
    }
}

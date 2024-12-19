<?php

namespace App\Http\Requests\Sosmed;

use Illuminate\Foundation\Http\FormRequest;

class SosmedStoreRequest extends FormRequest
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
            'logo' => ['required', 'file', 'max:512'],
            'name' => ['required', 'string', 'max:50', 'unique:social_media,name'],
            'link' => ['required', 'string', 'max:200']
        ];
    }
}

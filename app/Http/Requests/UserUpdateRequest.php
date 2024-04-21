<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:user,email,' . $this->user()->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'name_kana' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|integer',
            'graduation_term' => 'sometimes|nullable|integer',
            'nickname' => 'sometimes|nullable|string|max:255',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'カテゴリー名は必須です。',
            'content.max' => 'カテゴリー名は255文字以内で入力してください。',
        ];
    }
}

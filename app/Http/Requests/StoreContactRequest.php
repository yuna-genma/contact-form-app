<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->filled(['tel1', 'tel2', 'tel3'])) {
            $this->merge([
                'tel' => $this->tel1 . $this->tel2 . $this->tel3
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|integer|in:1,2,3',
            'email' => 'required|string|email|max:255',
            'tel' => 'required|string|regex:/^[0-9]{10,11}$/',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'detail' => 'required|string|max:120',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => '姓は必須です。',
            'first_name.max' => '姓は255文字以内で入力してください。',
            'last_name.required' => '名前は必須です。',
            'last_name.max' => '名前は255文字以内で入力してください。',
            'gender.required' => '性別を選択してください。',
            'gender.in' => '性別は1〜3の値を選択してください。',
            'email.required' => 'メールアドレスは必須です。',
            'email.max' => 'メールアドレスは255文字以内で入力してください。',
            'email.email' => '有効なメールアドレスの形式で入力してください。',
            'tel.required' => '電話番号は必須です。',
            'tel.regex' => '電話番号はハイフンなしの10~11桁で入力してください。',
            'address.required' => '住所は必須です。',
            'address.max' => '住所は255文字以内で入力してください。',
            'building.max' => '建物は255文字以内で入力してください。',
            'category_id.required' => 'カテゴリーを選択してください。',
            'category_id.exists' => '選択されたカテゴリーは存在しません。',
            'detail.required' => 'お問い合わせ内容は必須です。',
            'detail.max' => 'お問い合わせ内容は255文字以内で入力してください。',
        ];
    }
}

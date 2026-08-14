<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChildProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'child';
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名前',
            'profile_image' => 'プロフィール画像',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '名前を入力してください。',
            'name.max' => '名前は100文字以内で入力してください。',
            'profile_image.image' => 'プロフィール画像には画像ファイルを選択してください。',
            'profile_image.mimes' => 'プロフィール画像はJPEG、PNG、WebP形式を選択してください。',
            'profile_image.max' => 'プロフィール画像は2MB以下にしてください。',
        ];
    }
}

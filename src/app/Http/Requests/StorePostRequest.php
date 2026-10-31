<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ログインの確認はルートの auth ミドルウェアで行うので true にする
        return true;
    }

    public function rules(): array
    {
    return [
        'title' => 'required|max:100',
        'body'  => 'required|max:5000',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB
    ];
    }
}
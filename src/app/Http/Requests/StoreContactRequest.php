<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ログインの確認はルートの auth ミドルウェアで行うので true にする
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|max:50',
            'email' => 'required|email|max:255',
            'body'  => 'required|max:2000',
        ];
    }

    // エラーメッセージで使う項目名(「name は必須です」ではなく「お名前は必須です」にする)
    public function attributes(): array
    {
        return [
            'name'  => 'お名前',
            'email' => 'メールアドレス',
            'body'  => 'お問い合わせ内容',
        ];
    }
}
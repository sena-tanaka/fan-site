<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       return true; // 「自分の記事か」はコントローラーでPolicyを使って確認する
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

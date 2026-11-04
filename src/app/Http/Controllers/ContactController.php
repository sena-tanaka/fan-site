<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    // 入力画面
    public function create()
    {
        return view('contact.create');
    }

    // 保存
    public function store(StoreContactRequest $request)
    {
        Contact::create([
            ...$request->validated(),
            'user_id' => auth()->id(), // 誰から送られたか(退会するとNULLになる)
        ]);

        return redirect()->route('contact.thanks');
    }

    // 完了画面
    public function thanks()
    {
        return view('contact.thanks');
    }
}
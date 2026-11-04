<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-2">CONTACT</h1>
            <p class="text-gray-600 mb-8">BEATcrewへのご質問・出演のご依頼などは、こちらからお送りください。</p>

            <form method="POST" action="{{ route('contact.store') }}" class="bg-white rounded-lg shadow p-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="block font-medium mb-1">お名前</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', Auth::user()->name) }}"
                           class="w-full rounded border-gray-300">
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block font-medium mb-1">メールアドレス</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', Auth::user()->email) }}"
                           class="w-full rounded border-gray-300">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="block font-medium mb-1">お問い合わせ内容(2000文字まで)</label>
                    <textarea id="body" name="body" rows="8"
                              class="w-full rounded border-gray-300">{{ old('body') }}</textarea>
                    @error('body') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="bg-gray-900 text-white px-6 py-2 rounded hover:bg-gray-700">
                    送信する
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
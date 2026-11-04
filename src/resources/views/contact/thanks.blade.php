<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold mb-4">送信しました</h1>
            <p class="text-gray-600 mb-8">
                お問い合わせありがとうございます。<br>
                内容を確認のうえ、必要に応じてご連絡いたします。
            </p>
            <a href="{{ route('home') }}" class="inline-block bg-gray-900 text-white px-6 py-2 rounded hover:bg-gray-700">
                トップに戻る
            </a>
        </div>
    </div>
</x-app-layout>
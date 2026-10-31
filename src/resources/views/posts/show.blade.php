<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
            @endif

            <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>
            <p class="text-gray-500 mb-4">投稿者: {{ $post->user->member->name ?? $post->user->name }}</p>

            @if($post->image_path)
            <img src="{{ Storage::url($post->image_path) }}" class="w-full md:w-1/2 max-h-96 object-cover rounded mb-4">            
            @endif

            <p class="whitespace-pre-line">{{ $post->body }}</p>

            <div class="mt-6">
                <a href="{{ route('posts.index') }}" class="text-blue-600">一覧に戻る</a>
            </div>
        </div>
    </div>
</x-app-layout>

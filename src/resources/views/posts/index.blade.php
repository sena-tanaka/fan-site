<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">ブログ</h1>
                <a href="{{ route('posts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">記事を書く</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($posts as $post)
                <div class="bg-white rounded-lg shadow p-4">
                    @if($post->image)
                    <img src="{{ Storage::url($post->image) }}" class="w-full h-48 object-cover rounded">
                    @endif
                    <h2 class="text-xl font-bold mt-4">{{ $post->title }}</h2>
                    <p class="text-gray-500 text-sm">{{ $post->user->name }}</p>
                    <p class="text-gray-600 mt-2">{{ Str::limit($post->body, 100) }}</p>
                    <div class="mt-4">
                        <a href="{{ route('posts.show', $post) }}" class="text-blue-600">詳細を見る</a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
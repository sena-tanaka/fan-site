<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">ブログ</h1>
                @can('create', App\Models\Post::class)
                <a href="{{ route('posts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">記事を書く</a>
                @endcan
            </div>

            {{-- 検索フォーム(GETで送るので、条件がURLに残る) --}}
            <form method="GET" action="{{ route('posts.index') }}"
                  class="flex flex-col sm:flex-row gap-2 mb-6">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="タイトル・本文で検索"
                       class="flex-1 rounded border-gray-300">

                <select name="sort" class="rounded border-gray-300">
                    <option value="latest" @selected(request('sort') !== 'oldest')>新しい順</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>古い順</option>
                </select>

                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded">検索</button>

                @if(request()->filled('search') || request()->filled('sort'))
                <a href="{{ route('posts.index') }}" class="text-gray-600 px-2 py-2 hover:underline">クリア</a>
                @endif
            </form>

                       <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($posts as $post)
                    <x-post-card :post="$post" />
                @empty
                    <p class="text-gray-500 col-span-full">記事が見つかりませんでした。</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
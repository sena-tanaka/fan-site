@props(['post'])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition flex flex-col">
    @if($post->image_path)
        <img src="{{ Storage::url($post->image_path) }}"
             alt="{{ $post->title }}"
             class="w-full h-48 object-cover">
    @else
        {{-- 画像がない記事もカードの高さがそろうように --}}
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">
            No Image
        </div>
    @endif

    <div class="p-4 flex flex-col flex-1">
        <h2 class="text-lg font-bold text-gray-900 mb-1">{{ $post->title }}</h2>
        <p class="text-gray-500 text-sm mb-2">
            {{ $post->user->member->name ?? $post->user->name }}
            ・{{ $post->created_at->format('Y/m/d') }}
        </p>
        <p class="text-gray-600 text-sm flex-1">{{ Str::limit($post->body, 100) }}</p>

        <div class="mt-4 text-right">
            <a href="{{ route('posts.show', $post) }}"
               class="text-blue-600 hover:text-blue-800 font-medium">
                詳細 →
            </a>
        </div>
    </div>
</div>
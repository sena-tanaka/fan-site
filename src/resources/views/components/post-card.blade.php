@props(['post'])

<div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden hover:border-zinc-500 transition flex flex-col">
    @if($post->image_path)
        <img src="{{ Storage::url($post->image_path) }}"
             alt="{{ $post->title }}"
             class="w-full h-48 object-cover">
    @else
        {{-- 画像がない記事もカードの高さがそろうように --}}
        <div class="w-full h-48 bg-zinc-800 flex items-center justify-center text-zinc-500">
            No Image
        </div>
    @endif

    <div class="p-4 flex flex-col flex-1">
        <h2 class="text-lg font-bold text-white mb-1">{{ $post->title }}</h2>
        <p class="text-gray-500 text-sm mb-2">
            {{ $post->user->member->name ?? $post->user->name }}
            ・{{ $post->created_at->format('Y/m/d') }}
        </p>
        <p class="text-gray-400 text-sm flex-1">{{ Str::limit($post->body, 100) }}</p>

        <div class="mt-4 text-right">
            <a href="{{ route('posts.show', $post) }}"
               class="text-gray-400 hover:text-white font-medium">
                詳細 →
            </a>
        </div>
    </div>
</div>
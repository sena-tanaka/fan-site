@props(['member'])

<div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden flex flex-col">
    {{-- 写真 --}}
    @if ($member->image_path)
        <img src="{{ Storage::url($member->image_path) }}" alt="{{ $member->name }}"
             class="w-full h-56 object-cover">
    @else
        {{-- 写真がないときは頭文字を大きく表示 --}}
        <div class="w-full h-56 bg-zinc-800 flex items-center justify-center text-6xl font-bold text-zinc-500">
            {{ mb_substr($member->name, 0, 1) }}
        </div>
    @endif

    <div class="p-5 flex-1">
        <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-xl font-bold text-white">{{ $member->name }}</h2>
            @if ($member->position)
                <span class="px-2 py-0.5 rounded-full text-xs bg-white text-black">{{ $member->position }}</span>
            @endif
        </div>

        @if ($member->genre)
            <p class="text-sm text-gray-500 mt-1">ジャンル：{{ $member->genre }}</p>
        @endif

        @if ($member->profile)
            <p class="text-gray-300 text-sm mt-3 leading-relaxed whitespace-pre-line">{{ $member->profile }}</p>
        @endif
        
        <a href="{{ route('posts.index', ['member' => $member->id]) }}"
           class="inline-block mt-4 text-sm text-gray-400 hover:text-white font-medium">
            {{ $member->name }}のブログを見る →
        </a>
    </div>
</div>
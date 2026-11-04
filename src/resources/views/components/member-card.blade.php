@props(['member'])

<div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
    {{-- 写真 --}}
    @if ($member->image_path)
        <img src="{{ Storage::url($member->image_path) }}" alt="{{ $member->name }}"
             class="w-full h-56 object-cover">
    @else
        {{-- 写真がないときは頭文字を大きく表示 --}}
        <div class="w-full h-56 bg-gray-200 flex items-center justify-center text-6xl font-bold text-gray-400">
            {{ mb_substr($member->name, 0, 1) }}
        </div>
    @endif

    <div class="p-5 flex-1">
        <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-xl font-bold text-gray-900">{{ $member->name }}</h2>
            @if ($member->position)
                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-900 text-white">{{ $member->position }}</span>
            @endif
        </div>

        @if ($member->genre)
            <p class="text-sm text-gray-500 mt-1">ジャンル：{{ $member->genre }}</p>
        @endif

        @if ($member->profile)
            <p class="text-gray-700 text-sm mt-3 leading-relaxed whitespace-pre-line">{{ $member->profile }}</p>
        @endif
        
        <a href="{{ route('posts.index', ['member' => $member->id]) }}"
           class="inline-block mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">
            {{ $member->name }}のブログを見る →
        </a>
    </div>
</div>
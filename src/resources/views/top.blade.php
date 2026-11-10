<x-app-layout>
    {{-- トップページだけ背景を黒にする --}}
    <x-slot name="mainClass">bg-black text-white</x-slot>

    {{-- ヒーロー(いちばん上の大きな見出し) --}}
    <section>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-14 text-center">
            <p class="text-xs sm:text-sm tracking-[0.4em] text-gray-500 mb-4">OFFICIAL FAN SITE</p>
            <h1 class="font-brush text-white text-6xl sm:text-8xl lg:text-9xl leading-tight tracking-wide -rotate-2">BEATcrew</h1>
            <p class="mt-8 text-gray-400">ようこそ、{{ Auth::user()->name }}さん！</p>
        </div>
    </section>

    {{-- 最新ブログ: 右から左へ流れる --}}
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end justify-between mb-6">
            <h2 class="font-brush text-3xl sm:text-4xl tracking-wide">BLOG</h2>
            <a href="{{ route('posts.index') }}" class="text-sm text-gray-400 hover:text-white">ブログ一覧へ →</a>
        </div>

        @if ($latestPosts->isNotEmpty())
            @php
                // 記事が少なくても画面の端まで埋まるように、6件以上になるまでくり返す
                $slides = $latestPosts;
                while ($slides->count() < 6) {
                    $slides = $slides->concat($latestPosts);
                }
            @endphp

            {{-- 自動で流れつつ、指でスワイプ・マウスでドラッグもできる(resources/js/marquee.js) --}}
            <div class="marquee" data-marquee>
                <div class="marquee-track">
                    {{-- 同じ並びを3回続けて、つなぎ目なくループさせる(2回目が本物、前後はコピー) --}}
                    @foreach ([true, false, true] as $isCopy)
                        @foreach ($slides as $post)
                            <a href="{{ route('posts.show', $post) }}"
                               @if ($isCopy) aria-hidden="true" tabindex="-1" @endif
                               class="group w-64 sm:w-72 shrink-0 mr-6 bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden hover:border-zinc-500 transition">
                                @if ($post->image_path)
                                    <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}"
                                         class="w-full h-40 object-cover group-hover:opacity-80 transition">
                                @else
                                    <div class="w-full h-40 bg-zinc-800 flex items-center justify-center text-zinc-500 text-sm">
                                        No Image
                                    </div>
                                @endif
                                <div class="p-4">
                                    <p class="text-xs text-gray-500">
                                        {{ $post->created_at->format('Y/m/d') }}
                                        ・{{ $post->user->member->name ?? $post->user->name }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-white truncate">{{ $post->title }}</h3>
                                </div>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @else
            <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-500">まだブログの投稿はありません。</p>
        @endif
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 space-y-16">

        {{-- NEXT STAGE (F-04) --}}
        <section>
            <h2 class="font-brush text-3xl sm:text-4xl tracking-wide mb-6">NEXT STAGE</h2>

            @if ($nextStage)
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center gap-6">
                    {{-- 日付 --}}
                    <div class="text-center sm:border-r sm:border-zinc-700 sm:pr-8 shrink-0">
                        <p class="text-sm text-gray-500">{{ $nextStage->starts_at->format('Y') }}</p>
                        <p class="text-4xl font-bold">{{ $nextStage->starts_at->format('n/j') }}</p>
                        <p class="text-sm text-gray-500">{{ $nextStage->starts_at->locale('ja')->isoFormat('ddd') }}曜日</p>
                    </div>

                    {{-- 内容 --}}
                    <div class="flex-1">
                        <h3 class="text-xl font-bold">{{ $nextStage->title }}</h3>
                        <p class="text-gray-400 mt-2">
                            📍 {{ $nextStage->venue }}
                        </p>
                        <p class="text-gray-400">
                            🕒
                            @if ($nextStage->opens_at)
                                開場 {{ $nextStage->opens_at->format('H:i') }} ／
                            @endif
                            開演 {{ $nextStage->starts_at->format('H:i') }}
                        </p>
                    </div>

                    {{-- 詳細へのリンク --}}
                    @if (Route::has('schedules.show'))
                        <a href="{{ route('schedules.show', $nextStage) }}"
                           class="shrink-0 text-center bg-white text-black font-medium px-5 py-2 rounded hover:bg-gray-300">
                            詳しく見る
                        </a>
                    @endif
                </div>
            @else
                <p class="text-gray-500">現在、予定されている出演はありません。</p>
            @endif
        </section>

    </div>

    {{-- チーム紹介 (F-05) --}}
    <section class="pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-brush text-3xl sm:text-4xl tracking-wide mb-4">ABOUT BEATcrew</h2>
            <p class="text-gray-400 leading-relaxed mb-8">
                BEATcrewは、HIPHOP・JAZZ・LOCKなど、ジャンルの違うダンサーが集まったダンスチームです。
                イベントやライブを中心に活動しています。
            </p>
        </div>

        @if ($members->isNotEmpty())
            @php
                // メンバーが少なくても画面の端まで埋まるように、8人以上になるまでくり返す
                $memberSlides = $members;
                while ($memberSlides->count() < 8) {
                    $memberSlides = $memberSlides->concat($members);
                }
            @endphp

            {{-- ブログと同じく、自動で流れつつ指でスワイプ・マウスでドラッグもできる(resources/js/marquee.js) --}}
            <div class="marquee" data-marquee>
                <div class="marquee-track">
                    {{-- 同じ並びを3回続けて、つなぎ目なくループさせる(2回目が本物、前後はコピー) --}}
                    @foreach ([true, false, true] as $isCopy)
                        @foreach ($memberSlides as $member)
                            <div @if ($isCopy) aria-hidden="true" @endif
                                 class="w-40 sm:w-48 shrink-0 mr-6 bg-zinc-900 border border-zinc-800 rounded-lg p-4 text-center">
                                @if ($member->image_path)
                                    <img src="{{ Storage::url($member->image_path) }}" alt="{{ $member->name }}"
                                         class="w-24 h-24 mx-auto rounded-full object-cover">
                                @else
                                    {{-- 写真がないときは頭文字を表示 --}}
                                    <div class="w-24 h-24 mx-auto rounded-full bg-zinc-800 flex items-center justify-center text-2xl font-bold text-zinc-400">
                                        {{ mb_substr($member->name, 0, 1) }}
                                    </div>
                                @endif
                                <p class="mt-3 font-bold truncate">{{ $member->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $member->genre }}</p>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif

        @if (Route::has('members.index'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 text-right">
                <a href="{{ route('members.index') }}" class="text-sm text-gray-400 hover:text-white">
                    メンバー一覧へ →
                </a>
            </div>
        @endif
    </section>
</x-app-layout>

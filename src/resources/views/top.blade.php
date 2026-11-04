<x-app-layout>
    {{-- ヒーロー(いちばん上の大きな見出し) --}}
    <section class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <p class="text-sm tracking-widest text-gray-400 mb-2">OFFICIAL FAN SITE</p>
            <h1 class="text-4xl sm:text-5xl font-bold tracking-wide">BEATcrew</h1>
            <p class="mt-4 text-gray-300">ようこそ、{{ Auth::user()->name }}さん！</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

        {{-- NEXT STAGE (F-04) --}}
        <section>
            <h2 class="text-2xl font-bold mb-6">NEXT STAGE</h2>

            @if ($nextStage)
                <div class="bg-white rounded-lg shadow-md p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center gap-6">
                    {{-- 日付 --}}
                    <div class="text-center sm:border-r sm:pr-8 shrink-0">
                        <p class="text-sm text-gray-500">{{ $nextStage->starts_at->format('Y') }}</p>
                        <p class="text-4xl font-bold">{{ $nextStage->starts_at->format('n/j') }}</p>
                        <p class="text-sm text-gray-500">{{ $nextStage->starts_at->locale('ja')->isoFormat('ddd') }}曜日</p>
                    </div>

                    {{-- 内容 --}}
                    <div class="flex-1">
                        <h3 class="text-xl font-bold">{{ $nextStage->title }}</h3>
                        <p class="text-gray-600 mt-2">
                            📍 {{ $nextStage->venue }}
                        </p>
                        <p class="text-gray-600">
                            🕒
                            @if ($nextStage->opens_at)
                                開場 {{ $nextStage->opens_at->format('H:i') }} ／
                            @endif
                            開演 {{ $nextStage->starts_at->format('H:i') }}
                        </p>
                    </div>

                    {{-- 詳細へのリンク(スケジュールのページを作ったら表示される) --}}
                    @if (Route::has('schedules.show'))
                        <a href="{{ route('schedules.show', $nextStage) }}"
                           class="shrink-0 text-center bg-gray-900 text-white px-5 py-2 rounded hover:bg-gray-700">
                            詳しく見る
                        </a>
                    @endif
                </div>
            @else
                <p class="text-gray-500">現在、予定されている出演はありません。</p>
            @endif
        </section>

        {{-- チーム紹介 (F-05) --}}
        <section>
            <h2 class="text-2xl font-bold mb-4">ABOUT BEATcrew</h2>
            <p class="text-gray-700 leading-relaxed mb-8">
                BEATcrewは、HIPHOP・JAZZ・LOCKなど、ジャンルの違うダンサーが集まったダンスチームです。
                イベントやライブを中心に活動しています。
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($members as $member)
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        @if ($member->image_path)
                            <img src="{{ Storage::url($member->image_path) }}" alt="{{ $member->name }}"
                                 class="w-24 h-24 mx-auto rounded-full object-cover">
                        @else
                            {{-- 写真がないときは頭文字を表示 --}}
                            <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center text-2xl font-bold text-gray-500">
                                {{ mb_substr($member->name, 0, 1) }}
                            </div>
                        @endif
                        <p class="mt-3 font-bold">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->genre }}</p>
                    </div>
                @endforeach
            </div>

            {{-- メンバー一覧へのリンク(タスク7で作ったら表示される) --}}
            @if (Route::has('members.index'))
                <div class="mt-6 text-right">
                    <a href="{{ route('members.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        メンバー一覧へ →
                    </a>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
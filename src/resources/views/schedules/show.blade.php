<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($schedule->starts_at->isPast())
                <span class="inline-block mb-3 px-3 py-1 rounded-full text-xs bg-gray-200 text-gray-600">終了しました</span>
            @endif

            <h1 class="text-3xl font-bold mb-6">{{ $schedule->title }}</h1>

            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-500">日時</p>
                    <p class="font-medium">
                        {{ $schedule->starts_at->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
                        @if ($schedule->opens_at)
                            開場 {{ $schedule->opens_at->format('H:i') }} ／
                        @endif
                        開演 {{ $schedule->starts_at->format('H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">会場</p>
                    <p class="font-medium">{{ $schedule->venue }}</p>
                    @if ($schedule->address)
                        <p class="text-sm text-gray-600">{{ $schedule->address }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($schedule->address) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-sm text-blue-600 hover:underline">
                            地図を見る ↗
                        </a>
                    @endif
                </div>

                @if ($schedule->description)
                    <div>
                        <p class="text-sm text-gray-500">内容</p>
                        <p class="whitespace-pre-line">{{ $schedule->description }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('schedules.index') }}" class="text-blue-600 hover:underline">← スケジュール一覧に戻る</a>
            </div>
        </div>
    </div>
</x-app-layout>
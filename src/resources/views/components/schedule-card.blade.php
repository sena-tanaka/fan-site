@props(['schedule'])

<a href="{{ route('schedules.show', $schedule) }}"
   class="block bg-white rounded-lg shadow hover:shadow-lg transition p-5">
    <div class="flex items-center gap-5">
        {{-- 日付 --}}
        <div class="text-center w-16 shrink-0">
            <p class="text-xs text-gray-500">{{ $schedule->starts_at->format('Y') }}</p>
            <p class="text-2xl font-bold">{{ $schedule->starts_at->format('n/j') }}</p>
            <p class="text-xs text-gray-500">({{ $schedule->starts_at->locale('ja')->isoFormat('ddd') }})</p>
        </div>

        {{-- 内容 --}}
        <div class="flex-1 min-w-0">
            <h3 class="font-bold text-gray-900 truncate">{{ $schedule->title }}</h3>
            <p class="text-sm text-gray-600 mt-1">📍 {{ $schedule->venue }}</p>
            <p class="text-sm text-gray-600">🕒 開演 {{ $schedule->starts_at->format('H:i') }}</p>
        </div>

        <span class="text-gray-400 shrink-0">→</span>
    </div>
</a>
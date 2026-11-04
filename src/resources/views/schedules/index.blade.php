<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-8">SCHEDULE</h1>

            {{-- これからの予定 --}}
            <section>
                <h2 class="text-xl font-bold mb-4">これからの出演</h2>
                <div class="space-y-4">
                    @forelse ($upcoming as $schedule)
                        <x-schedule-card :schedule="$schedule" />
                    @empty
                        <p class="text-gray-500">現在、予定されている出演はありません。</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
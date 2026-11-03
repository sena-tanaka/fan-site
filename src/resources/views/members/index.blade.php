<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-2">MEMBER</h1>
            <p class="text-gray-600 mb-8">BEATcrewのメンバーを紹介します。</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($members as $member)
                    <x-member-card :member="$member" />
                @empty
                    <p class="text-gray-500 col-span-full">メンバーはまだ登録されていません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
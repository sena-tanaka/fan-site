<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-4">BEATcrew 公式ファンサイト</h1>
            <p class="text-gray-600">ようこそ、{{ Auth::user()->name }}さん！</p>

            {{-- NEXT STAGE とチーム紹介はタスク6で作る --}}
        </div>
    </div>
</x-app-layout>
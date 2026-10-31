<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-6">記事を書く</h1>

            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- 文字数カウント: [...text] で数えると絵文字も1文字になり、Laravelの max と数え方がそろう --}}
                <div class="mb-4" x-data="{ text: @js(old('title', '')), max: 100 }">
                    <label>タイトル(100文字まで)</label>
                    <input type="text" name="title" x-model="text" class="w-full">
                    <p class="text-sm text-right" :class="[...text].length > max ? 'text-red-600' : 'text-gray-500'">
                        <span x-text="[...text].length"></span> / 100文字
                    </p>
                    @error('title') <p class="text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4" x-data="{ text: @js(old('body', '')), max: 5000 }">
                    <label>本文(5000文字まで)</label>
                    <textarea name="body" rows="8" x-model="text" class="w-full"></textarea>
                    <p class="text-sm text-right" :class="[...text].length > max ? 'text-red-600' : 'text-gray-500'">
                        <span x-text="[...text].length"></span> / 5000文字
                    </p>
                    @error('body') <p class="text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label>画像(jpg・png・webp、2MBまで)</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                    @error('image') <p class="text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">投稿する</button>
            </form>
        </div>
    </div>
</x-app-layout>
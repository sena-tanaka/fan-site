<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-6">記事を書く</h1>

            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label>タイトル(100文字まで)</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full">
                    @error('title') <p class="text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label>本文(5000文字まで)</label>
                    <textarea name="body" rows="8" class="w-full">{{ old('body') }}</textarea>
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
<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
            @endif

            <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>
            <p class="text-gray-500 mb-4">投稿者: {{ $post->user->member->name ?? $post->user->name }}</p>

            @if($post->image_path)
            <img src="{{ Storage::url($post->image_path) }}" class="w-full md:w-1/2 max-h-96 object-cover rounded mb-4">
            @endif

            <p class="whitespace-pre-line">{{ $post->body }}</p>

            <div class="mt-6 flex items-center gap-4">
                <a href="{{ route('posts.index') }}" class="text-blue-600">一覧に戻る</a>

                {{-- 自分の記事のときだけ表示(PostPolicy の update / delete を使う) --}}
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}"
                       class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                        編集
                    </a>
                @endcan

                @can('delete', $post)
                    {{-- 押すと確認モーダルを開く(Breeze の x-modal を使う) --}}
                    <button type="button"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-post-deletion')"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        削除
                    </button>
                @endcan
            </div>

            @can('delete', $post)
                <x-modal name="confirm-post-deletion" focusable>
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" class="p-6">
                        @csrf
                        @method('DELETE')

                        <h2 class="text-lg font-medium text-gray-900">
                            本当にこの投稿を削除しますか？
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            「{{ $post->title }}」を削除します。この操作は取り消せません。
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                キャンセル
                            </x-secondary-button>

                            <x-danger-button>
                                削除する
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endcan
        </div>
    </div>
</x-app-layout>
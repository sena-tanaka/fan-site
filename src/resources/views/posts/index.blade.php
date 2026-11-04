            {{-- 検索フォーム(GETで送るので、条件がURLに残る) --}}
            <form method="GET" action="{{ route('posts.index') }}"
                  class="flex flex-col sm:flex-row gap-2 mb-6">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="タイトル・本文で検索"
                       class="flex-1 rounded border-gray-300">

                <select name="member" class="rounded border-gray-300">
                    <option value="">すべてのメンバー</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected(request('member') == $member->id)>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>

                <select name="sort" class="rounded border-gray-300">
                    <option value="latest" @selected(request('sort') !== 'oldest')>新しい順</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>古い順</option>
                </select>

                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded">検索</button>

                @if (request()->filled('search') || request()->filled('sort') || request()->filled('member'))
                    <a href="{{ route('posts.index') }}" class="text-gray-600 px-2 py-2 hover:underline">クリア</a>
                @endif
            </form>
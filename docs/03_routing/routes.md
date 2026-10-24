# ルーティング設計（Week 21 確定版）

> 画面ID は[要件定義書 7.1](../requirements.md#71-画面一覧)、画面のつながりは [画面遷移図](../01_screen-design/screen-transition.md) と対応している。
> Laravel 11 以降 / Breeze 前提。ルートは `routes/web.php`（画面・Ajax）と `routes/auth.php`（Breeze）に書く。
> 今回（Week 22〜24）作るルートだけを載せている。今回は作らないルートは 5章を参照。

## 1. 権限の考え方

### 利用者と role

| 利用者 | users.role | できること |
|--------|-----------|-----------|
| 未登録の人（未ログイン） | — | 会員登録・ログインだけ（サイト全体がログイン必須、F-29） |
| 会員のファン | `fan` | トップ・メンバー一覧・出演スケジュールの閲覧、お問い合わせ、ブログの閲覧・いいね |
| ダンサー | `dancer` | ファンの機能に加えて、ブログの投稿と、自分の投稿の編集・削除 |

### 表の「ログイン」「権限」列の読み方

| 表記 | 実装 |
|------|------|
| ログイン：ゲストのみ | `guest` ミドルウェア（ログイン中にアクセスするとトップへ） |
| ログイン：○ | `auth` ミドルウェア。未ログインならログイン画面へリダイレクトし、ログイン後に元のURLへ戻す。Ajax（`Accept: application/json`）のときは 401 の JSON を返す |
| 権限：— | ログインしていれば誰でも使える |
| 権限：`PostPolicy@method` | コントローラーで `Gate::authorize()`（または `$this->authorize()`）を呼ぶ。条件を満たさなければ 403 |

### Policy の一覧

| 名前 | 判定内容 | 使う場所 | 対応 |
|------|---------|---------|------|
| `PostPolicy@create` | `role === dancer`（ダンサーである） | ブログ投稿画面・投稿の保存 | F-19, NF-08 |
| `PostPolicy@update` | `post.user_id === 自分のid`（自分の投稿である） | ブログ編集画面・編集の保存 | F-20, NF-08 |
| `PostPolicy@delete` | `post.user_id === 自分のid`（自分の投稿である） | 削除 | F-20, NF-08 |

Blade では `@can('create', App\Models\Post::class)`・`@can('update', $post)`・`@can('delete', $post)` でボタンの表示を切り替える（NF-22）。

### 課題で確認されるルートの一覧

**Policy で守るルート**

| メソッド | URL | Policy |
|---------|-----|--------|
| GET | `/blog/create` | `PostPolicy@create` |
| POST | `/blog` | `PostPolicy@create` |
| GET | `/blog/{post}/edit` | `PostPolicy@update` |
| PUT | `/blog/{post}` | `PostPolicy@update` |
| DELETE | `/blog/{post}` | `PostPolicy@delete` |

**Ajax のルート**

| メソッド | URL | 内容 |
|---------|-----|------|
| POST | `/blog/{post}/like` | いいねする（F-18） |
| DELETE | `/blog/{post}/like` | いいねを取り消す（F-18） |

---

## 2. ルート一覧

`routes/web.php` の全体の形：

```php
Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    // 2.2〜2.5 のルートをすべてこの中に書く
});

require __DIR__.'/auth.php'; // 2.1 Breeze
```

### 2.1 認証（Laravel Breeze 標準）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-14 | 会員登録 | GET | `/register` | `register` | `Auth\RegisteredUserController@create` | ゲストのみ | — | 画面 |
| — | 会員登録の処理 | POST | `/register` | — | `Auth\RegisteredUserController@store` | ゲストのみ | — | role=fan で作成 → トップへ |
| S-15 | ログイン（チーム紹介文つき） | GET | `/login` | `login` | `Auth\AuthenticatedSessionController@create` | ゲストのみ | — | 画面 |
| — | ログインの処理 | POST | `/login` | — | `Auth\AuthenticatedSessionController@store` | ゲストのみ | — | 元の画面、なければトップへ |
| — | ログアウト | POST | `/logout` | `logout` | `Auth\AuthenticatedSessionController@destroy` | ○ | — | ログイン画面へ |
| — | パスワード再設定 | GET/POST | `/forgot-password` ほか | `password.request` ほか | Breeze 標準 | ゲストのみ | — | 画面 |
| — | 登録情報の変更・退会 | GET/PATCH/DELETE | `/profile` | `profile.edit` ほか | `ProfileController` | ○ | — | 画面（Breeze 標準のまま。画面設計には含めない） |

- Breeze の初期設定ではログイン後に `dashboard` へ移動するため、移動先を `home`（`/`）に変え、`dashboard` のルートとビューは削除する
- 会員登録の処理では、role を指定せずに作成する（DB の初期値 `fan` が入る）。フォームから role を受け取らない

### 2.2 チーム情報（F-04〜F-06, F-08, F-30）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-01 | トップ（NEXT STAGE・チーム紹介） | GET | `/` | `home` | `HomeController@index` | ○ | — | 画面 |
| S-03 | メンバー一覧 | GET | `/members` | `members.index` | `MemberController@index` | ○ | — | 画面（`is_active = true` のみ、`sort_order` 順） |
| S-05 | 出演スケジュール一覧 | GET | `/schedules` | `schedules.index` | `ScheduleController@index` | ○ | — | 画面（今日以降の公演のみ、日付の近い順） |
| S-06 | 出演スケジュール詳細 | GET | `/schedules/{schedule}` | `schedules.show` | `ScheduleController@show` | ○ | — | 画面 |

- 存在しない ID の詳細にアクセスしたら 404（ルートモデルバインディング）
- 終わった公演は一覧に出さないが、詳細の URL を直接開いた場合は表示する

### 2.3 お問い合わせ（F-10）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-11 | 入力 | GET | `/contact` | `contact.create` | `ContactController@create` | ○ | — | 画面（名前・メールにログイン中の値を入れておく） |
| — | 送信 | POST | `/contact` | `contact.store` | `ContactController@store` | ○ | — | DB に保存 → S-13 へリダイレクト（入力エラー時は S-11 に戻す） |
| S-13 | 送信完了 | GET | `/contact/complete` | `contact.complete` | `ContactController@complete` | ○ | — | 画面 |

- 送信後は GET の完了画面にリダイレクトする（PRG パターン）。完了画面で再読み込みしても二重送信にならない
- 送信時にセッションへフラッシュ値を入れ、値がないまま `/contact/complete` を開いた場合は S-11 へリダイレクトする
- メールは送らない（C-07）。確認画面（S-12）は Should

### 2.4 ブログ（F-17〜F-20, F-32）

ブログの CRUD は `Route::resource('blog', PostController::class)->parameters(['blog' => 'post'])` でまとめて定義する。

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-19 | ブログ一覧 | GET | `/blog` | `blog.index` | `PostController@index` | ○ | — | 画面（新しい順・10件ずつ。`?member=ID` でメンバーごとに絞り込み） |
| S-22 | ブログ投稿 | GET | `/blog/create` | `blog.create` | `PostController@create` | ○ | **`PostPolicy@create`** | 画面 |
| — | 投稿の保存 | POST | `/blog` | `blog.store` | `PostController@store` | ○ | **`PostPolicy@create`** | 画像をローカルに保存 → S-20 へ（「投稿しました」） |
| S-20 | ブログ詳細 | GET | `/blog/{post}` | `blog.show` | `PostController@show` | ○ | — | 画面 |
| S-22 | ブログ編集 | GET | `/blog/{post}/edit` | `blog.edit` | `PostController@edit` | ○ | **`PostPolicy@update`** | 画面 |
| — | 編集の保存 | PUT | `/blog/{post}` | `blog.update` | `PostController@update` | ○ | **`PostPolicy@update`** | 画像を差し替えたら古い画像を削除 → S-20 へ（「更新しました」） |
| — | 削除 | DELETE | `/blog/{post}` | `blog.destroy` | `PostController@destroy` | ○ | **`PostPolicy@delete`** | 画像も削除 → S-19 へ |

- 一覧の絞り込みは、`?member=` に members.id を渡し、`users.member_id` が一致するダンサーの記事だけを取得する。ページ送りのリンクに条件を残すため、`paginate(10)->withQueryString()` を使う
- 一覧・詳細では `with('user.member')->withCount('likes')` で、書いた人といいね数をまとめて取得する（NF-03）
- 入力チェックは `StorePostRequest`・`UpdatePostRequest`（FormRequest）で行う（NF-09）

### 2.5 いいね（F-18・Ajax）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| — | いいねする（Ajax） | POST | `/blog/{post}/like` | `blog.like` | `LikeController@store` | ○ | — | **JSON**（下の 3章） |
| — | いいねを取り消す（Ajax） | DELETE | `/blog/{post}/like` | `blog.unlike` | `LikeController@destroy` | ○ | — | **JSON**（下の 3章） |

- ログインしていれば、ファンもダンサーも（自分の記事にも）いいねできる。そのため Policy は使わない
- 1記事につき1人1回は、DB のユニーク制約（`likes` の `UNIQUE(user_id, post_id)`）で守る

---

## 3. Ajax ルートと JSON の形

Ajax は `fetch()` で呼び、ヘッダーに `X-CSRF-TOKEN`（`<meta name="csrf-token">` の値）と `Accept: application/json` を付ける（NF-07）。コントローラーは `response()->json()` で JSON を返す。

### 3.1 POST / DELETE /blog/{post}/like — いいね（S-20）

リクエスト：本文なし

レスポンス `200 OK`（いいねした・取り消した後の状態を返す）
```json
{
  "liked": true,
  "likes_count": 25
}
```

| 状態 | ステータス | 本文 |
|------|-----------|------|
| すでにいいね済みで POST | `200 OK` | 今の状態をそのまま返す（エラーにしない。連打対策） |
| 未いいねで DELETE | `200 OK` | 同上 |
| 存在しない記事 | `404 Not Found` | `{"message": "Not Found"}` |
| 未ログイン（セッション切れ） | `401 Unauthorized` | `{"message": "Unauthenticated."}` |
| CSRF トークンの期限切れ | `419` | Laravel 標準。画面に「ページを再読み込みしてください」と表示する |

画面側の動き（S-20）：
- 押したらすぐ ♡ ⇔ ♥ を切り替え、レスポンスの `likes_count` で数を更新する
- 200 以外が返ったら表示を元に戻し、「通信に失敗しました」と表示する

---

## 4. ルート一覧の確認方法

実装後は次のコマンドで、この表と実際のルートが合っているか確認する。

```bash
docker-compose exec app php artisan route:list --except-vendor
```

---

## 5. 今回は作らないルート

Week 18〜20 の設計で作っていたルート。Should / Could の機能を作るときに追加する。詳しい内容は Week 20 版（Git の履歴）を参照する。

| 機能 | 主な URL | 優先度 |
|------|---------|--------|
| チーム紹介の単独ページ（S-02） | `GET /about` | トップに統合 |
| メンバー詳細（S-04） | `GET /members/{member}` | Should |
| ダンス動画一覧（S-07） | `GET /videos` | Should |
| グッズ一覧・詳細（S-08, S-09） | `GET /goods`、`GET /goods/{product}` | Should |
| お問い合わせ確認（S-12） | `POST /contact/confirm`、`POST /contact/back` | Should |
| 自分の投稿一覧（S-21） | `GET /blog/mine` など | Should |
| カレンダーの予定取得（Ajax） | `GET /api/schedules?month=` | Could |
| グッズ・チケットの購入、Stripe Webhook | `/goods/{product}/checkout`、`/schedules/{schedule}/tickets`、`/checkout/*`、`/stripe/webhook` | Could |
| カート | `/cart`、`/cart/items` | Could |
| マイページ（S-17） | `/mypage`、`/mypage/orders/{order}` | Could |
| おみくじ（S-18・Ajax） | `GET /omikuji`、`POST /omikuji` | Could |
| 管理画面（S-23〜S-29） | `/admin` 以下 | Could |
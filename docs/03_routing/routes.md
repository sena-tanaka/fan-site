# ルーティング設計

> 画面ID は[要件定義書 5.1](../requirements.md#51-画面一覧)、画面のつながりは [画面遷移図](../01_screen-design/screen-transition.md) と対応している。
> Laravel 11 以降 / Breeze 前提（Laravel 10 の場合、CSRF の除外は `app/Http/Middleware/VerifyCsrfToken.php` に書く）。ルートは `routes/web.php`（画面・Ajax）と `routes/auth.php`（Breeze）に書く。

## 1. 権限の考え方

### 利用者と role

| 利用者 | users.role | できること |
|--------|-----------|-----------|
| ゲスト（未ログイン） | — | 公開ページの閲覧、グッズ購入、お問い合わせ |
| 会員 | `fan` | ゲストに加えて、チケット購入・おみくじ・ブログ閲覧・いいね・マイページ |
| ダンサー | `dancer` | 会員の機能に加えて、自分のブログの投稿・編集・削除 |
| 管理者 | `admin` | 会員の機能に加えて、管理画面（/admin） |

### 表の「ログイン」「権限」列の読み方

| 表記 | 実装 |
|------|------|
| ログイン：— | ログイン不要 |
| ログイン：○ | `auth` ミドルウェア。未ログインならログイン画面へリダイレクトし、ログイン後に元のURLへ戻す |
| ログイン：ゲストのみ | `guest` ミドルウェア（ログイン中にアクセスするとマイページへ） |
| 権限：`can:dancer` / `can:admin` | `AppServiceProvider` に Gate を定義し、ルートグループにミドルウェアで付ける。条件を満たさなければ 403 |
| 権限：`XxxPolicy@method` | コントローラーで `$this->authorize()`（または `Gate::authorize()`）を呼ぶ。1件ごとの判定（自分の投稿か、など）に使う |

### Gate と Policy の一覧

| 名前 | 種類 | 判定内容 | 使う場所 |
|------|------|---------|---------|
| `dancer` | Gate | `role === dancer` | /dancer 以下 |
| `admin` | Gate | `role === admin` | /admin 以下 |
| `PostPolicy@view` | Policy | 公開済み、または自分の下書き | ブログ詳細 |
| `PostPolicy@create` | Policy | ダンサーである | 投稿 |
| `PostPolicy@update` / `@delete` | Policy | ダンサー かつ `post.user_id === 自分のid`（NF-08） | 編集・削除 |
| `PostPolicy@like` | Policy | 公開済みの記事 | いいね |
| `SchedulePolicy@purchaseTicket` | Policy | ログイン中、チケット販売あり、公演前、残り枚数あり、自分の購入枚数が2枚未満 | チケット購入 |
| `OrderPolicy@view` | Policy | `order.user_id === 自分のid`（EC-09：他人のチケットは見られない） | 電子チケット表示 |

---

## 2. ルート一覧

### 2.1 公開ページ（誰でも見られる）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-01 | トップ | GET | `/` | `home` | `HomeController@index` | — | — | 画面 |
| S-02 | チーム紹介 | GET | `/about` | `about` | `AboutController` ※1 | — | — | 画面 |
| S-03 | メンバー一覧 | GET | `/members` | `members.index` | `MemberController@index` | — | — | 画面 |
| S-04 | メンバー詳細 | GET | `/members/{member}` | `members.show` | `MemberController@show` | — | — | 画面 |
| S-05 | 出演スケジュール（カレンダー） | GET | `/schedules` | `schedules.index` | `ScheduleController@index` | — | — | 画面 |
| — | カレンダーの予定取得（Ajax） | GET | `/api/schedules?month=2026-11` | `api.schedules.index` | `Api\ScheduleController@index` | — | — | **JSON** |
| S-06 | 出演スケジュール詳細 | GET | `/schedules/{schedule}` | `schedules.show` | `ScheduleController@show` | — | — | 画面 |
| S-07 | ダンス動画一覧 | GET | `/videos` | `videos.index` | `VideoController@index` | — | — | 画面 |
| S-08 | グッズ一覧 | GET | `/goods` | `goods.index` | `ProductController@index` | — | — | 画面 |
| S-09 | グッズ詳細 | GET | `/goods/{product}` | `goods.show` | `ProductController@show` | — | — | 画面 |

※1 `__invoke` だけを持つシングルアクションコントローラー。中身がほぼ固定なので `Route::view('/about', 'about')` でもよい。

- URL は `/products` ではなく `/goods` にした（ファンから見た呼び方に合わせる）。モデル名は `Product` のまま
- 非公開（`is_published = false`）のスケジュール・グッズの詳細にアクセスしたら 404

### 2.2 お問い合わせ（F-10）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-11 | 入力 | GET | `/contact` | `contact.create` | `ContactController@create` | — | — | 画面 |
| S-12 | 確認 | POST | `/contact/confirm` | `contact.confirm` | `ContactController@confirm` | — | — | 画面（入力エラー時は S-11 に戻す） |
| — | 修正する | POST | `/contact/back` | `contact.back` | `ContactController@back` | — | — | S-11 へリダイレクト（入力値を保持） |
| — | 送信 | POST | `/contact` | `contact.store` | `ContactController@store` | — | — | 保存・管理者へ通知メール → S-13 へリダイレクト |
| S-13 | 送信完了 | GET | `/contact/complete` | `contact.complete` | `ContactController@complete` | — | — | 画面 |

- 送信後は GET の完了画面にリダイレクトする（PRG パターン）。完了画面で再読み込みしても二重送信にならない

### 2.3 認証（Laravel Breeze 標準）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-14 | 会員登録 | GET | `/register` | `register` | `Auth\RegisteredUserController@create` | ゲストのみ | — | 画面 |
| — | 会員登録の処理 | POST | `/register` | — | `Auth\RegisteredUserController@store` | ゲストのみ | — | role=fan で作成 → マイページへ |
| S-15 | ログイン | GET | `/login` | `login` | `Auth\AuthenticatedSessionController@create` | ゲストのみ | — | 画面 |
| — | ログインの処理 | POST | `/login` | — | `Auth\AuthenticatedSessionController@store` | ゲストのみ | — | 元の画面、なければ role ごとの画面へ ※2 |
| — | ログアウト | POST | `/logout` | `logout` | `Auth\AuthenticatedSessionController@destroy` | ○ | — | トップへ |
| — | パスワード再設定 | GET/POST | `/forgot-password` ほか | `password.request` ほか | Breeze 標準 | ゲストのみ | — | 画面 |
| — | 登録情報の変更・退会 | GET/PATCH/DELETE | `/profile` | `profile.edit` ほか | `ProfileController` | ○ | — | 画面 |

※2 ファン → `/mypage`、ダンサー → `/dancer/posts`、管理者 → `/admin`（[画面遷移図 4・5](../01_screen-design/screen-transition.md)）

### 2.4 グッズ購入・決済（F-12, F-14）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| — | グッズの決済開始 | POST | `/goods/{product}/checkout` | `goods.checkout` | `ProductCheckoutController@store` | — | — | 在庫を確保し、Stripe 決済画面へリダイレクト |
| S-16 | チケット購入確認 | GET | `/schedules/{schedule}/tickets/create` | `tickets.create` | `TicketController@create` | ○ | `SchedulePolicy@purchaseTicket` | 画面 |
| — | チケットの決済開始 | POST | `/schedules/{schedule}/tickets` | `tickets.store` | `TicketController@store` | ○ | `SchedulePolicy@purchaseTicket` | 枚数を確認・残り枚数を確保し、Stripe 決済画面へリダイレクト |
| S-10 | 購入完了 | GET | `/checkout/success?session_id=...` | `checkout.success` | `CheckoutController@success` | — | 自分のセッションIDの注文のみ表示 ※3 | 画面 |
| — | 決済キャンセル | GET | `/checkout/cancel?session_id=...` | `checkout.cancel` | `CheckoutController@cancel` | — | — | 確保した在庫を戻し、元の商品・公演の画面へリダイレクト |
| — | Stripe Webhook | POST | `/stripe/webhook` | `stripe.webhook` | `StripeWebhookController@handle` | — | Stripe の署名を検証 | **JSON**（`{"received": true}`）。決済完了で注文を paid にしてメール送信、期限切れで在庫を戻す |

※3 未ログインでもグッズは買えるため、ログインでは守れない。Stripe の `session_id` をセッションにも保存しておき、URL の値と一致するときだけ表示する。

- `/stripe/webhook` は Stripe のサーバーから呼ばれるため、`bootstrap/app.php` で **CSRF チェックの対象外** にする（代わりに署名を検証する）
- 金額はフォームから受け取らず、必ずサーバー側で DB の価格から計算する

### 2.5 カート（F-13・任意）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| — | カートの中身 | GET | `/cart` | `cart.index` | `CartController@index` | — | — | 画面 |
| — | カートに追加 | POST | `/cart/items` | `cart.items.store` | `CartItemController@store` | — | — | カート画面へリダイレクト |
| — | カートから削除 | DELETE | `/cart/items/{product}` | `cart.items.destroy` | `CartItemController@destroy` | — | — | カート画面へリダイレクト |
| — | カートの決済開始 | POST | `/cart/checkout` | `cart.checkout` | `CartCheckoutController@store` | — | — | Stripe 決済画面へリダイレクト |

カートを作る場合、2.4 の「グッズの決済開始」は使わなくなる。画面一覧に「カート」画面を追加する。

### 2.6 会員限定（F-15〜F-18）

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-17 | マイページ（電子チケット・購入履歴） | GET | `/mypage` | `mypage` | `MypageController@index` | ○ | 自分の注文だけ取得 | 画面 |
| — | 電子チケット詳細 | GET | `/mypage/orders/{order}` | `mypage.orders.show` | `MypageController@showOrder` | ○ | `OrderPolicy@view` | 画面 |
| S-18 | おみくじ | GET | `/omikuji` | `omikuji.index` | `OmikujiController@index` | ○ | — | 画面（今日引いていれば結果も表示） |
| — | おみくじを引く（Ajax） | POST | `/omikuji` | `omikuji.draw` | `OmikujiController@draw` | ○ | — | **JSON**（下の 3.2） |
| S-19 | ブログ一覧 | GET | `/blog` | `blog.index` | `BlogController@index` | ○ | — | 画面（公開済みのみ、`?member=ID` で絞り込み） |
| S-20 | ブログ詳細 | GET | `/blog/{post}` | `blog.show` | `BlogController@show` | ○ | `PostPolicy@view` | 画面 |
| — | いいねする（Ajax） | POST | `/blog/{post}/like` | `blog.like` | `LikeController@store` | ○ | `PostPolicy@like` | **JSON**（下の 3.3） |
| — | いいねを取り消す（Ajax） | DELETE | `/blog/{post}/like` | `blog.unlike` | `LikeController@destroy` | ○ | `PostPolicy@like` | **JSON**（下の 3.3） |

- おみくじは「ログインしていれば誰でも」なので Policy は使わず、1日1回の判定は `OmikujiService` と DB のユニーク制約（`fortune_draws`）で行う

### 2.7 ダンサー（F-19〜F-21）

ルートグループ：`prefix('dancer')->name('dancer.')->middleware(['auth', 'can:dancer'])`

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | ログイン | 権限 | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|------|---------|
| S-21 | 自分の投稿一覧（いいね数） | GET | `/dancer/posts` | `dancer.posts.index` | `Dancer\PostController@index` | ○ | `can:dancer`（自分の投稿だけ取得） | 画面 |
| S-22 | ブログ投稿 | GET | `/dancer/posts/create` | `dancer.posts.create` | `Dancer\PostController@create` | ○ | `can:dancer` + `PostPolicy@create` | 画面 |
| — | 投稿の保存 | POST | `/dancer/posts` | `dancer.posts.store` | `Dancer\PostController@store` | ○ | `can:dancer` + `PostPolicy@create` | 画像を S3 に保存 → S-21 へ |
| S-22 | ブログ編集 | GET | `/dancer/posts/{post}/edit` | `dancer.posts.edit` | `Dancer\PostController@edit` | ○ | `can:dancer` + `PostPolicy@update` | 画面 |
| — | 編集の保存 | PUT | `/dancer/posts/{post}` | `dancer.posts.update` | `Dancer\PostController@update` | ○ | `can:dancer` + `PostPolicy@update` | S-21 へ |
| — | 削除 | DELETE | `/dancer/posts/{post}` | `dancer.posts.destroy` | `Dancer\PostController@destroy` | ○ | `can:dancer` + `PostPolicy@delete` | S3 の画像も削除 → S-21 へ |

`Route::resource('posts', Dancer\PostController::class)->except('show')` でまとめて定義できる（詳細は公開側の S-20 を使う）。

### 2.8 管理者（F-22〜F-28）

ルートグループ：`prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])`
権限はすべて `can:admin`。表では省略する。

| 画面ID | 画面・処理 | メソッド | URL | ルート名 | コントローラー@アクション | 返すもの |
|--------|-----------|---------|-----|---------|--------------------------|---------|
| S-23 | ダッシュボード | GET | `/admin` | `admin.dashboard` | `Admin\DashboardController` | 画面（未対応の問い合わせ数・直近の注文など） |
| S-24 | スケジュール一覧 | GET | `/admin/schedules` | `admin.schedules.index` | `Admin\ScheduleController@index` | 画面 |
| S-24 | スケジュール登録 | GET / POST | `/admin/schedules/create` ・ `/admin/schedules` | `admin.schedules.create` ・ `.store` | `@create` ・ `@store` | 画面 ／ 一覧へ |
| S-24 | スケジュール編集 | GET / PUT | `/admin/schedules/{schedule}/edit` ・ `/admin/schedules/{schedule}` | `admin.schedules.edit` ・ `.update` | `@edit` ・ `@update` | 画面 ／ 一覧へ |
| — | スケジュール削除 | DELETE | `/admin/schedules/{schedule}` | `admin.schedules.destroy` | `@destroy` | 一覧へ（注文がある公演は削除不可、非公開にする） |
| S-25 | 商品（グッズ）一覧・登録・編集・削除 | GET/POST/PUT/DELETE | `/admin/products` ほか | `admin.products.*` | `Admin\ProductController`（resource, show なし） | 画面 ／ 一覧へ |
| S-26 | メンバー一覧・登録・編集・削除 | GET/POST/PUT/DELETE | `/admin/members` ほか | `admin.members.*` | `Admin\MemberController`（resource, show なし） | 画面 ／ 一覧へ |
| — | ダンサー用アカウント発行（F-25・任意） | GET / POST | `/admin/members/{member}/account/create` ・ `/admin/members/{member}/account` | `admin.members.account.create` ・ `.store` | `Admin\DancerAccountController@create` ・ `@store` | 画面 ／ 一覧へ |
| S-27 | 動画一覧・登録フォーム | GET | `/admin/videos` | `admin.videos.index` | `Admin\VideoController@index` | 画面（一覧の上に URL 入力欄） |
| — | 動画登録 | POST | `/admin/videos` | `admin.videos.store` | `Admin\VideoController@store` | 一覧へ |
| — | 動画削除 | DELETE | `/admin/videos/{video}` | `admin.videos.destroy` | `Admin\VideoController@destroy` | 一覧へ |
| S-28 | お問い合わせ一覧 | GET | `/admin/contacts` | `admin.contacts.index` | `Admin\ContactController@index` | 画面（`?status=unread` で絞り込み） |
| S-28 | お問い合わせ詳細 | GET | `/admin/contacts/{contact}` | `admin.contacts.show` | `Admin\ContactController@show` | 画面 |
| — | 対応済みにする | PATCH | `/admin/contacts/{contact}` | `admin.contacts.update` | `Admin\ContactController@update` | 詳細へ |
| S-29 | 注文一覧 | GET | `/admin/orders` | `admin.orders.index` | `Admin\OrderController@index` | 画面（`?type=goods` で絞り込み） |
| S-29 | 注文詳細（配送先） | GET | `/admin/orders/{order}` | `admin.orders.show` | `Admin\OrderController@show` | 画面 |

`Route::resource()` で作るルート（products・members）は、`index / create / store / edit / update / destroy` の6つ。

---

## 3. Ajax ルートと JSON の形

Ajax はすべて `fetch()`（または axios）で呼び、ヘッダーに `X-CSRF-TOKEN`（`<meta name="csrf-token">` の値）と `Accept: application/json` を付ける。コントローラーは `response()->json()` で JSON を返す。

### 3.1 GET /api/schedules — カレンダーの予定（S-05）

月を切り替えたときに、その月の予定だけを取得する。`routes/web.php` に書き、ログイン不要。

リクエスト：`GET /api/schedules?month=2026-11`

レスポンス `200 OK`
```json
{
  "month": "2026-11",
  "data": [
    {
      "id": 12,
      "title": "BEAT CREW LIVE 2026 WINTER",
      "starts_at": "2026-11-23T18:00:00+09:00",
      "venue": "○○ホール",
      "ticket_status": "on_sale",
      "url": "https://example.com/schedules/12"
    }
  ]
}
```
- `ticket_status`：`on_sale`（販売中）/ `sold_out` / `none`（販売なし）/ `ended`（公演終了）
- `month` の形式が正しくなければ `422`（`{"message": "...", "errors": {...}}`、Laravel 標準のバリデーションエラー）
- 返す項目は API リソースクラス（`ScheduleResource`）で決める（Week 10 と同じ形）

### 3.2 POST /omikuji — おみくじを引く（S-18）

リクエスト：本文なし

レスポンス `201 Created`
```json
{
  "rank": "大吉",
  "message": "今日のステージは君が主役！",
  "member": { "name": "RYO", "image_url": "https://.../members/ryo.webp" },
  "drawn_on": "2026-10-19"
}
```
| 状態 | ステータス | 本文 |
|------|-----------|------|
| 今日すでに引いている | `409 Conflict` | `{"message": "今日はもう引きました。また明日！", "result": { 今日の結果 }}` |
| 未ログイン | `401 Unauthorized` | `{"message": "Unauthenticated."}` |

### 3.3 POST / DELETE /blog/{post}/like — いいね（S-20）

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
| 非公開の記事 | `403 Forbidden` | `{"message": "This action is unauthorized."}` |
| 未ログイン | `401 Unauthorized` | `{"message": "Unauthenticated."}` |

---

## 4. ルート一覧の確認方法

実装後は次のコマンドで、この表と実際のルートが合っているか確認する。

```bash
docker-compose exec app php artisan route:list --except-vendor
```

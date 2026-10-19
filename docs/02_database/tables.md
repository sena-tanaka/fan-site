# DB設計書（テーブル定義）

> ER図は [er-diagram.md](./er-diagram.md) を参照。
> DB：MySQL 8.0（本番は RDS）。文字コード utf8mb4 / 照合順序 utf8mb4_unicode_ci（Laravel の初期設定）。

## 1. テーブル一覧

| No | テーブル名 | 論理名 | 概要 | 対応機能 |
|----|-----------|--------|------|---------|
| 1 | users | ユーザー | 会員・ダンサー・管理者。role で権限を分ける | F-01〜03 |
| 2 | members | メンバー | チームメンバーのプロフィール | F-06, F-24 |
| 3 | schedules | 出演スケジュール | 出演予定。チケットの価格・販売枚数もここに持つ | F-07, F-08, F-14, F-22 |
| 4 | products | グッズ | グッズの商品情報と在庫 | F-11, F-12, F-23 |
| 5 | orders | 注文 | グッズ・チケットの注文（1回の決済＝1行） | F-12, F-14, F-15, F-28 |
| 6 | order_items | 注文明細 | 注文に含まれる商品・チケット | F-12, F-14, F-15, F-28 |
| 7 | videos | ダンス動画 | YouTube 動画の情報 | F-09, F-26 |
| 8 | posts | ブログ記事 | ダンサーが書く会員限定ブログ | F-17, F-19〜21 |
| 9 | likes | いいね | 会員がブログに付けたいいね | F-18, F-21 |
| 10 | fortunes | おみくじ結果マスタ | 大吉〜吉などの結果とメッセージ | F-16 |
| 11 | fortune_draws | おみくじ履歴 | 誰がいつ何を引いたか（1日1回の判定に使う） | F-16 |
| 12 | contacts | お問い合わせ | フォームから届いた問い合わせ | F-10, F-27 |

Laravel が用意する `password_reset_tokens`・`sessions`・`cache`・`jobs`・`failed_jobs` はそのまま使うため、定義は省略する（メール送信をキューで行う場合に jobs を使う）。

チーム紹介（F-05）は内容がほとんど変わらないため、DB には入れず Blade に直接書く。カート（F-13・任意）はセッションに保存するため、テーブルは作らない。

### 共通ルール
- 主キーはすべて `id`（BIGINT UNSIGNED, AUTO_INCREMENT）
- `created_at`・`updated_at` はすべてのテーブルに付ける（下の表では省略）
- 金額は円の整数（INT UNSIGNED）で持つ。Stripe も円は整数で扱うため
- 画像はファイル本体を S3 に置き、DB には S3 上のパス（例：`products/abc.webp`）だけを保存する（NF-04）
- 外部キーの削除時の動作は「参照」列に書く。注文に関わるデータは消さずに残すため、`RESTRICT` か `SET NULL` にしている

---

## 2. テーブル定義

### 2.1 users（ユーザー）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| name | VARCHAR(50) | × | | | 名前（ニックネーム可） |
| email | VARCHAR(255) | × | | UNIQUE | メールアドレス（ログインID） |
| email_verified_at | TIMESTAMP | ○ | NULL | | Breeze 標準 |
| password | VARCHAR(255) | × | | | bcrypt でハッシュ化して保存（NF-06） |
| role | VARCHAR(20) | × | 'fan' | INDEX | `fan`（会員）/ `dancer`（ダンサー）/ `admin`（管理者） |
| member_id | BIGINT UNSIGNED | ○ | NULL | FK → members.id（SET NULL）, UNIQUE | ダンサーのとき、どのメンバーか。会員・管理者は NULL |
| remember_token | VARCHAR(100) | ○ | NULL | | Breeze 標準 |

- 会員登録画面（S-14）からは必ず `fan` で作られる。`dancer`・`admin` はシーダー（任意で F-25 の管理画面）から作る
- role は PHP の Enum（`App\Enums\UserRole`）で扱い、DB には文字列で保存する

### 2.2 members（メンバー）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| name | VARCHAR(50) | × | | | ダンサー名 |
| name_kana | VARCHAR(50) | ○ | NULL | | 読み |
| position | VARCHAR(50) | ○ | NULL | | 担当（リーダー・振付 など） |
| genre | VARCHAR(100) | ○ | NULL | | 得意ジャンル（Breaking, Popping など） |
| profile | TEXT | × | | | 自己紹介 |
| image_path | VARCHAR(255) | ○ | NULL | | プロフィール画像（S3） |
| sort_order | INT UNSIGNED | × | 0 | | 一覧での表示順 |
| is_active | BOOLEAN | × | true | | false にすると一覧に出さない（脱退時。ブログ・おみくじのデータを残すため削除しない） |

### 2.3 schedules（出演スケジュール）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| title | VARCHAR(100) | × | | | 公演名 |
| starts_at | DATETIME | × | | INDEX | 開演日時（カレンダーの月の絞り込みに使うのでインデックス） |
| opens_at | DATETIME | ○ | NULL | | 開場日時 |
| venue | VARCHAR(100) | × | | | 会場名 |
| address | VARCHAR(255) | ○ | NULL | | 会場の住所 |
| description | TEXT | ○ | NULL | | 内容・出演メンバーなど |
| ticket_price | INT UNSIGNED | ○ | NULL | | チケット価格。NULL ならチケット販売なし（入場無料のイベントなど） |
| ticket_capacity | INT UNSIGNED | × | 0 | | 販売枚数 |
| ticket_remaining | INT UNSIGNED | × | 0 | | 残り枚数。購入時に減らす（2.5 の在庫の扱いを参照） |
| is_published | BOOLEAN | × | true | | false にすると公開画面に出さない |

- チケットを別テーブルにせず schedules に持たせた理由：要件では「1公演に1種類のチケット」で、券種（S席・A席など）がないため（EC-11）

### 2.4 products（グッズ）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| name | VARCHAR(100) | × | | | 商品名 |
| description | TEXT | ○ | NULL | | 説明（サイズ・素材など） |
| price | INT UNSIGNED | × | | | 価格（税込） |
| stock | INT UNSIGNED | × | 0 | | 在庫数。UNSIGNED なのでマイナスにならない（NF-11） |
| image_path | VARCHAR(255) | ○ | NULL | | 商品画像（S3） |
| is_published | BOOLEAN | × | true | INDEX | 非公開にすると一覧に出さない。注文がある商品は削除せずこちらを使う |

### 2.5 orders（注文）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| order_number | VARCHAR(20) | × | | UNIQUE | 注文番号・チケットの購入番号（例：`BC-20261023-0012`） |
| user_id | BIGINT UNSIGNED | ○ | NULL | FK → users.id（SET NULL）, INDEX | ログイン中の購入なら会員ID。未ログインのグッズ購入は NULL |
| type | VARCHAR(10) | × | | | `goods` / `ticket` |
| status | VARCHAR(20) | × | 'pending' | INDEX | `pending`（決済待ち）/ `paid`（決済完了）/ `canceled`（決済されずに期限切れ） |
| customer_name | VARCHAR(100) | ○ | NULL | | 購入者名（Stripe で入力された値を決済完了時に保存） |
| email | VARCHAR(255) | ○ | NULL | | 完了メールの送り先 |
| phone | VARCHAR(20) | ○ | NULL | | 電話番号（グッズのみ） |
| postal_code | VARCHAR(10) | ○ | NULL | | 郵便番号（グッズのみ） |
| address | VARCHAR(255) | ○ | NULL | | 配送先住所（グッズのみ） |
| total_amount | INT UNSIGNED | × | | | 合計金額 |
| stripe_session_id | VARCHAR(255) | × | | UNIQUE | Stripe Checkout Session の ID。Webhook で注文を探すのに使う |
| paid_at | DATETIME | ○ | NULL | | 決済完了日時 |

**在庫・残り枚数の扱い（NF-11）**
1. 「決済に進む」を押したとき、トランザクションの中で在庫を確保する
   `UPDATE products SET stock = stock - 1 WHERE id = ? AND stock >= 1`（更新件数が0なら売り切れとしてエラー表示）
   同時に `orders` を `pending` で作る
2. Stripe の Webhook `checkout.session.completed` を受けたら `paid` にして完了メールを送る
3. `checkout.session.expired`（決済画面を閉じて30分経過）を受けたら `canceled` にして在庫を戻す

条件付き UPDATE にしているので、同時に2人が最後の1個を押しても片方しか成功しない。
※ 要件の EC-03 は「決済が完了すると在庫が1つ減る」だが、決済完了時に減らすと「決済できたのに在庫がない」が起こりうるため、決済開始時に確保する形にした。Week 21 で要件側の表現も合わせる。

**チケット1人2枚までの判定**
`orders.status IN ('pending','paid')` かつ同じ `user_id`・同じ `schedule_id` の `order_items.quantity` の合計 + 今回の枚数 ≦ 2。
`pending` も数えるのは、決済画面を2つ開いて合計3枚以上買うのを防ぐため。

### 2.6 order_items（注文明細）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| order_id | BIGINT UNSIGNED | × | | FK → orders.id（CASCADE）, INDEX | |
| product_id | BIGINT UNSIGNED | ○ | NULL | FK → products.id（RESTRICT） | グッズのとき |
| schedule_id | BIGINT UNSIGNED | ○ | NULL | FK → schedules.id（RESTRICT）, INDEX | チケットのとき |
| item_name | VARCHAR(100) | × | | | 購入時点の商品名・公演名（後で名前が変わっても履歴が変わらないように保存） |
| unit_price | INT UNSIGNED | × | | | 購入時点の単価 |
| quantity | INT UNSIGNED | × | | | 数量（グッズは1、チケットは1〜2） |

- product_id と schedule_id は、どちらか一方だけが入る
- 1回の注文で1種類しか買わない今の要件なら orders だけでも足りるが、カート（F-13）を作ったときに1注文で複数商品になるため明細を分けた

### 2.7 videos（ダンス動画）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| title | VARCHAR(100) | × | | | 動画タイトル |
| youtube_video_id | VARCHAR(20) | × | | UNIQUE | YouTube の動画ID（`https://www.youtube.com/watch?v=XXXX` の XXXX）。管理画面で URL を入力し、ID だけ取り出して保存 |
| description | TEXT | ○ | NULL | | 説明 |
| published_on | DATE | ○ | NULL | INDEX | 公開日（新着順に並べる） |

動画ファイルはサーバーに保存しない（C-06）。

### 2.8 posts（ブログ記事）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | × | | FK → users.id（CASCADE）, INDEX | 書いたダンサー |
| title | VARCHAR(100) | × | | | タイトル |
| body | TEXT | × | | | 本文 |
| image_path | VARCHAR(255) | ○ | NULL | | 画像（S3） |
| published_at | DATETIME | ○ | NULL | INDEX | 公開日時。NULL なら下書き |
| likes_count | INT UNSIGNED | × | 0 | | いいね数（いいねのたびに増減させ、一覧で毎回数えなくて済むようにする） |

- 誰の記事かは `posts.user_id → users.member_id → members` でたどり、メンバー名と画像を表示する

### 2.9 likes（いいね）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | × | | FK → users.id（CASCADE） | いいねした会員 |
| post_id | BIGINT UNSIGNED | × | | FK → posts.id（CASCADE） | |

- `UNIQUE(user_id, post_id)`：1記事につき1人1回（F-18）。連打されても DB で弾ける

### 2.10 fortunes（おみくじ結果マスタ）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| rank | VARCHAR(10) | × | | | 大吉 / 中吉 / 小吉 / 吉 |
| message | VARCHAR(255) | × | | | ひとこと |
| member_id | BIGINT UNSIGNED | ○ | NULL | FK → members.id（SET NULL） | ひとことを言うメンバー |
| weight | INT UNSIGNED | × | 1 | | 出やすさ（大きいほど出やすい） |

シーダーで登録する。管理画面は作らない。

### 2.11 fortune_draws（おみくじ履歴）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | × | | FK → users.id（CASCADE） | |
| fortune_id | BIGINT UNSIGNED | × | | FK → fortunes.id（RESTRICT） | 引いた結果 |
| drawn_on | DATE | × | | | 引いた日（日本時間） |

- `UNIQUE(user_id, drawn_on)`：同じ日に2回目は引けない（F-16）。画面側のチェックをすり抜けても DB で弾ける
- アプリのタイムゾーン（`config/app.php` の timezone）を `Asia/Tokyo` にして、日付が日本時間で変わるようにする

### 2.12 contacts（お問い合わせ）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | ○ | NULL | FK → users.id（SET NULL） | ログイン中に送った場合のみ |
| name | VARCHAR(50) | × | | | 名前 |
| email | VARCHAR(255) | × | | | 返信先 |
| category | VARCHAR(20) | × | | | `ticket` / `goods` / `site` / `other` |
| body | TEXT | × | | | 内容（2000文字まで） |
| status | VARCHAR(20) | × | 'unread' | INDEX | `unread`（未対応）/ `done`（対応済み） |

---

## 3. 主なリレーション

| 親 | 子 | 関係 | Laravel のメソッド |
|----|----|------|------------------|
| members | users | 1 対 0..1（ダンサーのアカウント） | `Member::user()` hasOne / `User::member()` belongsTo |
| users | posts | 1 対 多 | `User::posts()` / `Post::user()` |
| users・posts | likes | 多 対 多 | `Post::likedUsers()` belongsToMany（likes が中間テーブル） |
| users | orders | 1 対 多 | `User::orders()` / `Order::user()` |
| orders | order_items | 1 対 多 | `Order::items()` / `OrderItem::order()` |
| products | order_items | 1 対 多 | `OrderItem::product()` |
| schedules | order_items | 1 対 多 | `OrderItem::schedule()` |
| users | fortune_draws | 1 対 多 | `User::fortuneDraws()` |
| fortunes | fortune_draws | 1 対 多 | `FortuneDraw::fortune()` |
| members | fortunes | 1 対 多 | `Fortune::member()` |
| users | contacts | 1 対 多 | `Contact::user()` |

一覧画面では `with()` で関連を先に読み込み、N+1 を出さない（NF-03）。例：ブログ一覧は `Post::with('user.member')`。

## 4. Week 21 で確認すること

- 在庫を「決済開始時に確保」にした点を要件定義書（EC-03）に反映するか
- ダンサーが退団したとき、アカウントとブログをどうするか（今は members.is_active を false にするだけ）
- お問い合わせに返信機能を付けるか（今は一覧で確認し、返信はメールソフトから行う想定）

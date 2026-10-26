# DB設計書（テーブル定義）（Week 21 確定版）

> ER図は [er-diagram.md](./er-diagram.md) を参照。
> DB：MySQL 8.0（本番は RDS）。文字コード utf8mb4 / 照合順序 utf8mb4_unicode_ci（Laravel の初期設定）。
> 今回（Week 22〜24）作るテーブルだけを定義している。今回は作らないテーブルは 4章を参照。

## 1. テーブル一覧

| No | テーブル名 | 論理名 | 概要 | 対応機能 |
|----|-----------|--------|------|---------|
| 1 | users | ユーザー | 会員のファン・ダンサー。role で権限を分ける | F-01〜03, F-29 |
| 2 | members | メンバー | チームメンバーのプロフィール | F-06 |
| 3 | schedules | 出演スケジュール | 出演予定 | F-04, F-30, F-08 |
| 4 | posts | ブログ記事 | ダンサーが書くブログ | F-17, F-19, F-20, F-32 |
| 5 | likes | いいね | 会員がブログに付けたいいね | F-18 |
| 6 | contacts | お問い合わせ | フォームから届いた問い合わせ | F-10 |

Laravel が用意する `password_reset_tokens`・`sessions`・`cache`・`jobs`・`failed_jobs` はそのまま使うため、定義は省略する。

チーム紹介（F-05）は内容がほとんど変わらないため、DB には入れず Blade（トップ）に直接書く。

### 共通ルール
- 主キーはすべて `id`（BIGINT UNSIGNED, AUTO_INCREMENT）
- `created_at`・`updated_at` はすべてのテーブルに付ける（下の表では省略）
- 画像はファイル本体をローカル（`storage/app/public`）に置き、DB にはそこからのパス（例：`posts/abc.webp`）だけを保存する（NF-17）。`php artisan storage:link` で公開する
- 外部キーの削除時の動作は「キー・制約」列に書く
- members・schedules・ダンサーのアカウントは Seeder で登録する（C-10）

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
| role | VARCHAR(20) | × | 'fan' | | `fan`（会員のファン）/ `dancer`（ダンサー） |
| member_id | BIGINT UNSIGNED | ○ | NULL | FK → members.id（SET NULL）, UNIQUE | ダンサーのとき、どのメンバーか。ファンは NULL |
| remember_token | VARCHAR(100) | ○ | NULL | | Breeze 標準 |

- 会員登録画面（S-14）からは必ず `fan` で作られる。`dancer` は Seeder で作る（F-03）
- role は PHP の Enum（`App\Enums\UserRole`）で扱い、DB には文字列で保存する
- PostPolicy の `create` で `role === dancer` を確認する

### 2.2 members（メンバー）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| name | VARCHAR(50) | × | | | ダンサー名 |
| position | VARCHAR(50) | ○ | NULL | | 役割（リーダー・振付 など） |
| genre | VARCHAR(100) | ○ | NULL | | 担当ジャンル（ブレイク・ポップ など）。メンバー一覧に表示する |
| profile | TEXT | ○ | NULL | | 自己紹介（メンバー詳細 S-04 で使う。今回は Seeder で入れておくだけ） |
| image_path | VARCHAR(255) | ○ | NULL | | プロフィール画像 |
| sort_order | INT UNSIGNED | × | 0 | | 一覧での表示順 |
| is_active | BOOLEAN | × | true | | false にすると一覧に出さない（脱退時。ブログのデータを残すため削除しない） |

### 2.3 schedules（出演スケジュール）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| title | VARCHAR(100) | × | | | 公演名 |
| starts_at | DATETIME | × | | INDEX | 開演日時（今日以降の絞り込みと、日付順の並べ替えに使うのでインデックス） |
| opens_at | DATETIME | ○ | NULL | | 開場日時 |
| venue | VARCHAR(100) | × | | | 会場名 |
| address | VARCHAR(255) | ○ | NULL | | 会場の住所 |
| description | TEXT | ○ | NULL | | 内容・出演メンバー・メンバーからのコメント |

- 一覧（S-05）とトップの NEXT STAGE（S-01）は `starts_at >= 今日` の公演だけを、日付の近い順に表示する
- アプリのタイムゾーン（`config/app.php` の timezone）を `Asia/Tokyo` にして、「今日」を日本時間で判定する

### 2.4 posts（ブログ記事）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | × | | FK → users.id（CASCADE）, INDEX | 書いたダンサー |
| title | VARCHAR(100) | × | | | タイトル（100文字まで） |
| body | TEXT | × | | | 本文（5000文字まで） |
| image_path | VARCHAR(255) | ○ | NULL | | 画像（jpg/png/webp・2MBまで、NF-11） |

- 下書き機能は作らないため、保存するとすぐ公開する。一覧は `created_at` の新しい順に並べる
- 誰の記事かは `posts.user_id → users.member_id → members` でたどり、メンバー名と画像を表示する
- 編集・削除できるのは `posts.user_id` がログイン中のユーザーと同じときだけ（PostPolicy の update・delete、F-20）
- 画像を差し替えたとき・記事を削除したときは、古い画像ファイルも削除する
- メンバーで絞り込み（F-32）は `members.id` で行う（`/blog?member={id}`）。`users.member_id` が一致するユーザーの記事だけを表示する

### 2.5 likes（いいね）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | × | | FK → users.id（CASCADE） | いいねした人（ファン・ダンサー） |
| post_id | BIGINT UNSIGNED | × | | FK → posts.id（CASCADE） | |

- `UNIQUE(user_id, post_id)`：1記事につき1人1回（F-18）。連打されても DB で弾ける
- いいね数は列で持たず、`withCount('likes')` で数える。一覧でもまとめて1回で数えられるため、N+1 にならない（NF-03）

### 2.6 contacts（お問い合わせ）

| カラム名 | 型 | NULL | 初期値 | キー・制約 | 説明 |
|---------|----|------|--------|-----------|------|
| id | BIGINT UNSIGNED | × | | PK | |
| user_id | BIGINT UNSIGNED | ○ | NULL | FK → users.id（SET NULL） | 送った会員。退会してもお問い合わせは残すため SET NULL |
| name | VARCHAR(50) | × | | | 名前 |
| email | VARCHAR(255) | × | | | 返信先 |
| body | TEXT | × | | | 内容（1000文字まで） |

- サイト全体がログイン必須なので、保存時は必ず user_id が入る
- 管理画面（F-27）は作らないため、届いた内容は phpMyAdmin で確認する

---

## 3. 主なリレーション

| 親 | 子 | 関係 | Laravel のメソッド |
|----|----|------|------------------|
| members | users | 1 対 0..1（ダンサーのアカウント） | `Member::user()` hasOne / `User::member()` belongsTo |
| users | posts | 1 対 多 | `User::posts()` / `Post::user()` |
| users・posts | likes | 多 対 多 | `Post::likedUsers()` belongsToMany（likes が中間テーブル）/ `Post::likes()` hasMany |
| users | contacts | 1 対 多 | `Contact::user()` |

一覧画面では `with()` で関連を先に読み込み、N+1 を出さない（NF-03）。
例：ブログ一覧は `Post::with('user.member')->withCount('likes')`。

---

## 4. 今回は作らないテーブル

Week 18〜20 の設計で作っていたテーブル。Should / Could の機能を作るときに追加する。

| テーブル名 | 論理名 | 使う機能 | 優先度 |
|-----------|--------|---------|--------|
| products | グッズ | F-11 グッズ一覧・詳細（カテゴリで絞り込み）、F-12 購入 | Should / Could |
| orders | 注文 | F-12 グッズ購入、F-14 チケット購入、F-15 購入履歴 | Could |
| order_items | 注文明細 | 同上 | Could |
| videos | ダンス動画 | F-09 ダンス動画一覧 | Should |
| fortunes | おみくじ結果マスタ | F-16 おみくじ | Could |
| fortune_draws | おみくじ履歴 | F-16 おみくじ | Could |

今あるテーブルに、あとから足す予定の列：

| テーブル | 列 | 使う機能 |
|---------|----|---------|
| schedules | ticket_price・ticket_capacity・ticket_remaining | F-14 チケット購入 |
| posts | published_at（下書き） | 下書き機能を作るとき |
| contacts | category・status | F-27 お問い合わせ一覧（管理者） |
| users.role | `admin` を追加 | F-22〜F-28 管理画面 |

グッズ購入時の在庫の確保（条件付き UPDATE）と、チケット1人2枚までの判定方法は、Week 20 版の設計（Git の履歴）を参照する。
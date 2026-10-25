# BEAT CREW ファンクラブ会員サイト

架空のヒップホップダンスチーム「BEAT CREW」のファンクラブ会員サイトです（チーム名・メンバーは架空）。
チームの情報とダンサー本人の発信を会員限定のサイトにまとめ、ファンが「迷わず出演情報を見つけ、推しの日常に触れ、いいねで応援の気持ちを届けられる」ことを目指します。

## 主な機能（Week 22〜24 で作る範囲）

サイト全体がログイン必須です（会員登録・ログイン画面以外は、ログインしないと開けません）。

- 会員登録・ログイン・ログアウト（ログイン画面にチームの紹介を表示）
- トップ（直近の出演予定・チーム紹介）、メンバー一覧、出演スケジュール一覧・詳細
- お問い合わせ
- ダンサーが書くブログ
  - ダンサーだけが写真付きで投稿でき、自分の記事だけ編集・削除できる
  - メンバーごとに記事を絞り込める
  - ページを再読み込みせずに「いいね」できる

| 利用者 | できること |
|--------|-----------|
| 会員のファン | 閲覧・お問い合わせ・ブログのいいね |
| ダンサー | ファンの機能に加えて、ブログの投稿・自分の記事の編集・削除 |

グッズ・チケット購入、おみくじ、出演スケジュールのカレンダー表示、管理画面は、今後追加する機能として要件定義書に残しています（Should / Could）。

## 使用技術

Laravel / Laravel Breeze（Blade）/ Tailwind CSS / MySQL / Docker / PHPUnit / AWS（EC2・RDS）/ GitHub Actions

詳しくは [技術選定資料](./docs/06_tech-stack/tech-stack.md) を参照。

## 開発の進め方

- 開発期間：Week 22〜24（3スプリント・合計 26 時間）
- 計画：[スプリント計画](./docs/sprint-plan.md)
- タスク管理：GitHub Projects のボード（https://github.com/users/sena-tanaka/projects/1）
- 画面設計：[Figma のリンク](./docs/01_screen-design/figma.md)
- 実装は `src/` に追加していきます（Week 22 から）

## ドキュメント（docs/）

| フォルダ・ファイル | 内容 |
|------|------|
| [requirements.md](./docs/requirements.md) | 要件定義書（ペルソナ・MVP・ユーザーストーリー・機能要件・非機能要件） |
| [sprint-plan.md](./docs/sprint-plan.md) | スプリント計画（3スプリントのスケジュール） |
| [01_screen-design/](./docs/01_screen-design/) | 画面設計（Figma の URL・ワイヤーフレーム・画面遷移図） |
| [02_database/](./docs/02_database/) | DB設計書（テーブル定義・ER図） |
| [03_routing/](./docs/03_routing/) | ルーティング設計（Policy で守るルート・Ajax のルート） |
| [04_estimate/](./docs/04_estimate/) | 工数見積もり |
| [05_infrastructure/](./docs/05_infrastructure/) | インフラ構成図 |
| [06_tech-stack/](./docs/06_tech-stack/) | 技術選定資料（使用技術・デプロイ先の選定理由） |
| [practice/](./docs/practice/) | 練習課題（総合プロジェクトとは別の題材） |
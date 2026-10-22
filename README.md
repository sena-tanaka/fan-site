# BEAT CREW 公式ファンサイト

架空のヒップホップダンスチーム「BEAT CREW」の公式ファンサイトです（チーム名・メンバーは架空）。
チームの情報・グッズやチケットの購入・ファンとの交流の窓口を公式サイトに一本化し、ファンが「迷わず情報を見つけ、安心して買い、毎日チームを身近に感じられる」ことを目指します。

## 主な機能
- チーム紹介・メンバー一覧・出演スケジュール・ダンス動画（YouTube 埋め込み）
- グッズ購入（会員登録なしで購入可）・チケット購入（会員限定、1公演1人2枚まで）
- 1日1回のおみくじ、ダンサーが書く会員限定ブログ（いいね機能つき）
- 管理画面（スケジュール・商品・メンバー・動画・お問い合わせ・注文の管理）

## 使用技術
Laravel / Laravel Breeze / MySQL / Docker / Stripe（テストモード）/ AWS（EC2・RDS・S3）/ GitHub Actions

## ドキュメント（docs/）

| フォルダ・ファイル | 内容 |
|------|------|
| [requirements.md](./docs/requirements.md) | 要件定義書 |
| [01_screen-design/](./docs/01_screen-design/) | 画面設計（Figma の URL・ワイヤーフレーム・画面遷移図） |
| [02_database/](./docs/02_database/) | DB設計書（テーブル定義・ER図） |
| [03_routing/](./docs/03_routing/) | ルーティング設計 |
| [04_estimate/](./docs/04_estimate/) | 工数見積もり・スケジュール |
| [05_infrastructure/](./docs/05_infrastructure/) | インフラ構成図 |
| [practice/](./docs/practice/) | 練習課題（総合プロジェクトとは別の題材） |
# ER図（Week 21 確定版）

> 各カラムの詳しい説明は [テーブル定義](./tables.md) を参照。
> 記法：`||` = ちょうど1、`o|` = 0か1、`|{` = 1以上、`o{` = 0以上。
> 今回（Week 22〜24）作るテーブルだけを描いている。

```mermaid
erDiagram
    members ||--o| users : "ダンサーのアカウント"
    users ||--o{ posts : "書く（ダンサーのみ）"
    users ||--o{ likes : "いいねする"
    posts ||--o{ likes : "いいねされる"
    users |o--o{ contacts : "問い合わせる"

    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar role "fan / dancer"
        bigint member_id FK "ダンサーのみ"
    }
    members {
        bigint id PK
        varchar name
        varchar position
        varchar genre
        text profile
        varchar image_path
        int sort_order
        boolean is_active
    }
    schedules {
        bigint id PK
        varchar title
        datetime starts_at
        datetime opens_at
        varchar venue
        varchar address
        text description
    }
    posts {
        bigint id PK
        bigint user_id FK
        varchar title
        text body
        varchar image_path "NULL可"
    }
    likes {
        bigint id PK
        bigint user_id FK "UK(user_id, post_id)"
        bigint post_id FK
    }
    contacts {
        bigint id PK
        bigint user_id FK "退会時NULL"
        varchar name
        varchar email
        text body
    }
```

`schedules` はほかのテーブルとつながりがないため、図では単独で置いている。`created_at`・`updated_at` は全テーブルにあるが、図では省略している。

今回は作らないテーブル（products・orders・order_items・videos・fortunes・fortune_draws）は、[テーブル定義 4章](./tables.md#4-今回は作らないテーブル) を参照。
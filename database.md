# Database ER Diagram — Social Media Feed App

## Entity Relationship Diagram

```mermaid
erDiagram
    USERS {
        int id PK
        varchar first_name
        varchar last_name
        varchar email
        varchar password_hash
        timestamp created_at
        timestamp updated_at
    }

    POSTS {
        int id PK
        int user_id FK
        text content
        varchar image_url
        enum visibility
        timestamp created_at
        timestamp updated_at
    }

    COMMENTS {
        int id PK
        int post_id FK
        int user_id FK
        int parent_id FK
        text content
        timestamp created_at
        timestamp updated_at
    }

    POST_LIKES {
        int id PK
        int post_id FK
        int user_id FK
        timestamp created_at
    }

    COMMENT_LIKES {
        int id PK
        int comment_id FK
        int user_id FK
        timestamp created_at
    }

    USERS ||--o{ POSTS          : "creates"
    USERS ||--o{ COMMENTS       : "writes"
    USERS ||--o{ POST_LIKES     : "likes"
    USERS ||--o{ COMMENT_LIKES  : "likes"

    POSTS    ||--o{ COMMENTS      : "has"
    POSTS    ||--o{ POST_LIKES    : "receives"

    COMMENTS ||--o{ COMMENTS      : "replies"
    COMMENTS ||--o{ COMMENT_LIKES : "receives"
```

---

## Relationship Summary Table

| Relationship                 | Type        | Description                         |
|------------------------------|-------------|-------------------------------------|
| `USERS` → `POSTS`            | One-to-Many | A user can create many posts        |
| `USERS` → `COMMENTS`         | One-to-Many | A user can write many comments      |
| `USERS` → `POST_LIKES`       | One-to-Many | A user can like many posts          |
| `USERS` → `COMMENT_LIKES`    | One-to-Many | A user can like many comments       |
| `POSTS` → `COMMENTS`         | One-to-Many | A post can have many comments       |
| `POSTS` → `POST_LIKES`       | One-to-Many | A post can receive many likes       |
| `COMMENTS` → `COMMENTS`      | One-to-Many | A comment can have many replies     |
| `COMMENTS` → `COMMENT_LIKES` | One-to-Many | A comment can receive many likes    |

---

## Table Summary

| Table          | Primary Key | Foreign Keys                                      |
|----------------|-------------|---------------------------------------------------|
| USERS          | id          | —                                                 |
| POSTS          | id          | user_id → USERS                                   |
| COMMENTS       | id          | post_id → POSTS, user_id → USERS, parent_id → COMMENTS |
| POST_LIKES     | id          | post_id → POSTS, user_id → USERS                  |
| COMMENT_LIKES  | id          | comment_id → COMMENTS, user_id → USERS            |

---

## Constraints & Key Notes

| Rule                  | Detail                                                         |
|-----------------------|----------------------------------------------------------------|
| Unique Like           | `UNIQUE(post_id, user_id)` in POST_LIKES                       |
| Unique Like           | `UNIQUE(comment_id, user_id)` in COMMENT_LIKES                 |
| Cascade Delete        | All FK → `ON DELETE CASCADE`                                   |
| Comment Threading     | `COMMENTS.parent_id` is `NULL` for top-level comments          |
| Post Visibility       | `ENUM('public', 'private')` in POSTS                           |
| Feed Filter Query     | `WHERE visibility = 'public' OR user_id = :current_user_id`    |

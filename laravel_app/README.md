# くすりサポート (Medication Reminder App)

高齢のご家族など、複数人分のお薬管理と服薬記録を行うためのWebアプリケーションです。
元看護師としての臨床経験をもとに、「飲み忘れを防ぎたい」「誰が・いつ・どの薬を飲んだかを記録したい」というニーズから開発しました。

## 主な機能

- **患者（ご家族）管理**：複数の患者を登録し、それぞれの服薬情報を個別に管理
- **お薬管理**：薬名・分量・服用時刻を登録し、写真も添付可能
- **服薬記録**：「飲んだ」「取り消し」の記録をワンタップで保存
- **プッシュ通知によるリマインダー**：服用時刻にブラウザ通知でお知らせ（Web Push）
- **ゴミ箱機能**：誤って削除したデータを復元できる論理削除（Soft Delete）
- **認証機能**：Laravel Breezeによるログイン・会員登録・パスワードリセット

## 技術構成

| カテゴリ | 使用技術 |
|---|---|
| バックエンド | PHP 8.1 / Laravel 10 |
| データベース | MySQL 8.0 |
| フロントエンド | Blade / Tailwind CSS / Alpine.js |
| 認証 | Laravel Breeze |
| プッシュ通知 | laravel-notification-channels/webpush |
| 開発環境 | Docker / Docker Compose |
| メール確認（開発用） | Mailpit |
| コード整形 | Laravel Pint |
| テスト | PHPUnit |

## データベース構成

| テーブル | 役割 |
|---|---|
| `users` | ログインするユーザー（ご家族の代表者など） |
| `patients` | 服薬管理の対象となる患者（ユーザーに紐づく） |
| `medicines` | 患者ごとに登録するお薬情報（薬名・分量・服用時刻） |
| `adherences` | 服薬記録（いつ・どの薬を飲んだか） |

**リレーション**：`User` 1 - N `Patient` 1 - N `Medicine` 1 - N `Adherence`

## セットアップ手順（Docker）

### 前提

- Docker Desktop がインストール済みであること

### 手順

```bash
# 1. リポジトリをクローン
git clone https://github.com/asuka0120/medication-reminder-app.git
cd medication-reminder-app

# 2. 環境変数ファイルを作成
cp laravel_app/.env.example laravel_app/.env

# 3. コンテナを起動
docker compose up -d

# 4. 依存パッケージをインストール
docker compose exec web composer install

# 5. アプリケーションキーを生成
docker compose exec web php artisan key:generate

# 6. マイグレーションを実行（テーブル作成）
docker compose exec web php artisan migrate
```

起動後、以下のURLにアクセスできます。

- アプリ本体: http://localhost
- Mailpit（開発用メール確認画面）: http://localhost:8025

## テスト

```bash
docker compose exec web php artisan test
```

認証まわりの機能テスト（PHPUnit）に加え、開発初期段階では手動テストケースによる検証も行っています。

## コードスタイル

[Laravel Pint](https://laravel.com/docs/pint) によるコード整形ルールに従っています。コミット前に以下を実行してください。

```bash
docker compose exec web php vendor/bin/pint
```

## ディレクトリ構成（抜粋）
```
laravel_app/
├── app/
│   ├── Http/Controllers/   # 患者・お薬・ゴミ箱などの各種コントローラー
│   ├── Models/             # User, Patient, Medicine, Adherence
│   └── Notifications/      # 服薬リマインダー通知
├── database/migrations/    # テーブル定義
├── resources/views/        # Bladeテンプレート
├── routes/web.php          # ルーティング定義
└── tests/                  # PHPUnitテスト
```
## ライセンス

このプロジェクトは学習・ポートフォリオ目的で作成されています。
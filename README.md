# Symbol Wallet Service

PHPとSQLiteを使用したSymbolブロックチェーンウォレット作成・送金サービス

## 機能

- ユーザーアカウント作成時に自動的にSymbolウォレットを生成
- 共有デポジットウォレットから新規ウォレットに1 XYMを自動送信
- SQLiteデータベースでウォレット情報を管理

## 必要な環境

- PHP 8.1以上
- SQLite3拡張機能
- Composer

## セットアップ

### 1. 依存関係のインストール

```bash
composer install
```

### 2. 環境変数の設定

デポジットウォレットの秘密鍵とアドレスを環境変数に設定：

```bash
export SYMBOL_DEPOSIT_PRIVATE_KEY="あなたの秘密鍵"
export SYMBOL_DEPOSIT_ADDRESS="あなたのアドレス"
```

または `config.php` ファイルを直接編集してください。

### 3. サーバーの起動

```bash
php -S localhost:8000 index.php
```

## API使用方法

### 1. ウォレット作成

新しいユーザーのウォレットを作成し、1 XYMを送信：

```bash
curl -X POST http://localhost:8000/wallet/create \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1}'
```

レスポンス例：
```json
{
  "success": true,
  "message": "ウォレットが作成され、1 XYMが送信されました",
  "wallet": {
    "address": "TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "public_key": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
  }
}
```

### 2. ウォレット情報取得

ユーザーのウォレット情報を取得：

```bash
curl http://localhost:8000/wallet/1
```

レスポンス例：
```json
{
  "success": true,
  "wallet": {
    "address": "TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "public_key": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
  }
}
```

## データベース

SQLiteデータベースは `database/database.sqlite` に自動作成されます。

テーブル構造：
```sql
CREATE TABLE user_wallets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    address TEXT NOT NULL UNIQUE,
    public_key TEXT NOT NULL,
    private_key TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

## 注意事項

⚠️ **重要なセキュリティ注意事項:**

1. **本番環境での使用前に必ず実装を改善してください**
2. 秘密鍵の生成と管理には実際の Symbol SDK を使用してください
3. 現在の実装は簡易版です。本番環境では以下を実装してください：
   - 適切な Symbol SDK の統合（symbol-sdk-php など）
   - トランザクション署名の正しい実装
   - 秘密鍵の暗号化保存
   - エラーハンドリングの強化
   - セキュリティ監査

## 推奨される改善点

1. **Symbol SDK の統合**: 実際のSymbol SDKを使用した鍵生成と署名
2. **秘密鍵の暗号化**: データベース保存時の暗号化
3. **トランザクション確認**: 送信後の確認処理を追加
4. **残高チェック**: デポジットウォレットの残高確認
5. **ログ管理**: 詳細なトランザクションログ
6. **認証機能**: APIアクセスの認証・認可

## テストネット情報

- ノードURL: https://sym-test-01.opening-line.jp:3001
- ネットワークタイプ: 152 (testnet)
- Generation Hash: 7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836

## ライセンス

MIT

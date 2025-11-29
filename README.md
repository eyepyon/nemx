# Symbol Wallet Service

Laravelを使用したSymbolブロックチェーンウォレット作成・送金サービス

## 機能

- ユーザーアカウント作成時に自動的にSymbolウォレットを生成
- 共有デポジットウォレットから新規ウォレットに1 XYMを自動送信

## セットアップ

### 1. 依存関係のインストール

```bash
composer install
```

### 2. 環境設定

`.env.example`を`.env`にコピーして編集：

```bash
cp .env.example .env
```

以下の設定を更新：

```env
SYMBOL_DEPOSIT_PRIVATE_KEY=あなたのデポジットウォレットの秘密鍵
SYMBOL_DEPOSIT_ADDRESS=あなたのデポジットウォレットのアドレス
```

### 3. データベースマイグレーション

```bash
php artisan migrate
```

### 4. アプリケーションキーの生成

```bash
php artisan key:generate
```

## 使用方法

### ウォレット作成API

認証済みユーザーが新しいウォレットを作成：

```bash
POST /wallet/create
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

### ウォレット情報取得API

```bash
GET /wallet
```

## 注意事項

⚠️ **重要なセキュリティ注意事項:**

1. **本番環境での使用前に必ず実装を改善してください**
2. 秘密鍵の生成と管理には実際の Symbol SDK を使用してください
3. 現在の実装は簡易版です。本番環境では以下を実装してください：
   - 適切な Symbol SDK の統合
   - トランザクション署名の正しい実装
   - エラーハンドリングの強化
   - セキュリティ監査

## 推奨される改善点

1. **Symbol SDK の統合**: `symbol-sdk-typescript` または PHP用のSDKを使用
2. **キュー処理**: トランザクション送信を非同期処理に
3. **トランザクション確認**: 送信後の確認処理を追加
4. **残高チェック**: デポジットウォレットの残高確認
5. **ログ管理**: 詳細なトランザクションログ

## テストネット情報

- ノードURL: https://sym-test-01.opening-line.jp:3001
- ネットワークタイプ: 152 (testnet)
- Generation Hash: 7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836

## ライセンス

MIT

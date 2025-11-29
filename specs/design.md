# Symbol Wallet Service - 設計書

## システムアーキテクチャ

### 全体構成

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP
       ↓
┌─────────────────────────────────────┐
│   PHP Web Server (index.php)       │
│   - ルーティング                     │
│   - リクエスト処理                   │
│   - レスポンス生成                   │
└──────┬──────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────┐
│   SymbolWalletService (PHP)         │
│   - ウォレット作成                   │
│   - XYM送信                          │
│   - Node.jsヘルパー呼び出し          │
└──────┬──────────────────────────────┘
       │
       ├─→ ┌──────────────────────────┐
       │   │   Database (SQLite)      │
       │   │   - ウォレット情報保存    │
       │   └──────────────────────────┘
       │
       ↓
┌─────────────────────────────────────┐
│   symbol-helper.js (Node.js)        │
│   - Symbol SDK v2ラッパー            │
│   - ウォレット生成                   │
│   - トランザクション署名・送信        │
└──────┬──────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────┐
│   Symbol SDK v2                     │
│   - 暗号処理                         │
│   - トランザクション作成              │
└──────┬──────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────┐
│   Symbol Blockchain (testnet)       │
└─────────────────────────────────────┘
```

## コンポーネント設計

### 1. Webサーバー (index.php)

**責務:**
- HTTPリクエストの受信とルーティング
- JSONレスポンスの生成
- CORS設定
- 静的ファイルの配信

**主要ルート:**
- `GET /` - Webインターフェース
- `POST /wallet/create` - ウォレット作成
- `GET /wallet/{user_id}` - ウォレット情報取得（公開）
- `GET /wallet/{user_id}/export` - ウォレット情報取得（秘密鍵含む）

### 2. SymbolWalletService (PHP)

**責務:**
- ビジネスロジックの実装
- Node.jsヘルパースクリプトの呼び出し
- エラーハンドリング

**主要メソッド:**
```php
class SymbolWalletService
{
    // ウォレット作成
    public function createWallet(): array
    
    // XYM送信
    public function sendInitialXym(string $recipientAddress): bool
    
    // 残高取得
    public function getBalance(string $address): ?array
    
    // アドレス導出
    public function deriveAddress(string $privateKey): ?array
    
    // Node.jsヘルパー実行
    private function executeHelper(string $command, array $args = []): array
}
```

### 3. Database (SQLite)

**テーブル設計:**

```sql
CREATE TABLE user_wallets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    address TEXT NOT NULL UNIQUE,
    public_key TEXT NOT NULL,
    private_key TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_id ON user_wallets(user_id);
CREATE INDEX idx_address ON user_wallets(address);
```

**制約:**
- `user_id`: ユニーク制約（1ユーザー1ウォレット）
- `address`: ユニーク制約（重複防止）

### 4. symbol-helper.js (Node.js)

**責務:**
- Symbol SDK v2のラッパー
- コマンドライン引数の処理
- JSON形式での結果出力

**コマンド:**
```bash
# ウォレット作成
node symbol-helper.js create

# XYM送信
node symbol-helper.js send <private_key> <recipient_address> <amount>

# 残高取得
node symbol-helper.js balance <address>

# アドレス導出
node symbol-helper.js derive <private_key>

# テスト
node symbol-helper.js test
```

**出力形式:**
```json
{
  "success": true,
  "privateKey": "XXX...",
  "publicKey": "XXX...",
  "address": "TBXXX..."
}
```

### 5. Webインターフェース (public/index.html)

**構成:**
- HTML: 構造
- CSS: スタイリング（グラデーション、モーダル）
- JavaScript: API呼び出し、UI制御

**主要機能:**
- ウォレット作成フォーム
- ウォレット情報表示
- 秘密鍵モーダル
- コピー機能
- Symbol Explorerリンク

## データフロー

### ウォレット作成フロー

```
1. ユーザーがWebインターフェースでuser_idを入力
   ↓
2. POST /wallet/create リクエスト送信
   ↓
3. index.php がリクエストを受信
   ↓
4. SymbolWalletService::createWallet() 呼び出し
   ↓
5. Node.js helper (create) 実行
   ↓
6. Symbol SDK v2 でウォレット生成
   ↓
7. ウォレット情報をJSON形式で返却
   ↓
8. Database にウォレット情報を保存
   ↓
9. SymbolWalletService::sendInitialXym() 呼び出し
   ↓
10. Node.js helper (send) 実行
    ↓
11. Symbol SDK v2 でトランザクション作成・署名
    ↓
12. Symbol testnet にトランザクション送信
    ↓
13. トランザクションハッシュを返却
    ↓
14. レスポンスをJSON形式で返却
    ↓
15. Webインターフェースに表示
```

## セキュリティ設計

### 秘密鍵の管理

**デポジットウォレット:**
- `.env`ファイルに保存
- `.gitignore`に含める
- 環境変数として読み込み

**ユーザーウォレット:**
- SQLiteデータベースに保存
- 現在は平文（本番環境では暗号化推奨）
- Webインターフェースではモーダルで保護

### API セキュリティ

**現在の実装:**
- 認証なし
- CORS: すべてのオリジンを許可

**推奨される改善:**
- JWT認証
- APIキー認証
- レート制限
- CORS制限

## エラーハンドリング

### エラーの種類

1. **バリデーションエラー**
   - user_id未指定
   - 不正なアドレス形式

2. **データベースエラー**
   - 接続失敗
   - 重複エラー

3. **Node.jsヘルパーエラー**
   - 実行失敗
   - JSON解析エラー

4. **Symbol SDKエラー**
   - ウォレット生成失敗
   - トランザクション送信失敗

5. **ネットワークエラー**
   - Symbol testnet接続失敗
   - タイムアウト

### エラーレスポンス形式

```json
{
  "success": false,
  "message": "エラーメッセージ"
}
```

## パフォーマンス最適化

### データベース
- インデックス作成（user_id, address）
- 接続プーリング（将来的に）

### Node.js呼び出し
- 非同期処理（将来的に）
- キャッシング（将来的に）

### トランザクション
- 適切な手数料設定（2,000,000 micro XYM）
- デッドライン設定（2時間）

## 設定管理

### 環境変数 (.env)

```env
# Symbol Blockchain Configuration
SYMBOL_NETWORK=testnet
SYMBOL_NODE_URL=https://sym-test-01.opening-line.jp:3001
SYMBOL_GENERATION_HASH=7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836
SYMBOL_NETWORK_TYPE=152
SYMBOL_DEPOSIT_PRIVATE_KEY=<秘密鍵>
SYMBOL_DEPOSIT_ADDRESS=<アドレス>
```

### 設定ファイル (config.php)

- .envファイルを読み込み
- 設定値を配列で返却
- デフォルト値の設定

## テスト戦略

### 単体テスト
- SymbolWalletService のメソッド
- Database のCRUD操作

### 統合テスト
- API エンドポイント
- Node.js ヘルパー呼び出し

### E2Eテスト
- Webインターフェースからのウォレット作成
- トランザクション送信の確認

### テストスクリプト
- `tests/test-send.php` - XYM送信テスト
- `tests/test-db.php` - データベース確認

## デプロイメント

### 開発環境
- WSL (Windows Subsystem for Linux)
- PHP 8.3.6
- Node.js 18+
- SQLite3

### 本番環境（推奨）
- Linux サーバー
- PHP 8.1+
- Node.js 18+
- PostgreSQL または MySQL（SQLiteの代わり）
- Nginx または Apache
- SSL/TLS証明書

## 監視とログ

### ログ出力
- PHPエラーログ: `error_log()`
- トランザクションログ: データベースまたはファイル

### 監視項目
- API応答時間
- トランザクション成功率
- デポジットウォレット残高
- データベース容量

## 今後の拡張

### Phase 2
- ユーザー認証機能
- ウォレット残高表示
- トランザクション履歴

### Phase 3
- マルチシグウォレット対応
- トークン送信機能
- メッセージ送信機能

### Phase 4
- メインネット対応
- モバイルアプリ
- ウォレットインポート/エクスポート

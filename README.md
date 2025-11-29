# Symbol Wallet Service

PHPとSQLiteを使用したSymbolブロックチェーンウォレット作成・送金サービス

## 機能

- 🎨 **Webインターフェース**: ブラウザから簡単にウォレット作成・管理
- 🔐 **自動ウォレット生成**: ユーザーIDを入力するだけでウォレットを作成
- 💰 **自動送金**: 共有デポジットウォレットから新規ウォレットに1 XYMを自動送信
- 🗄️ **SQLiteデータベース**: ウォレット情報を安全に管理
- 🔑 **秘密鍵保護**: モーダルで秘密鍵を隠し、必要な時だけ表示
- 🔗 **エクスプローラー連携**: ワンクリックでSymbol Explorerでアドレスを確認

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

### 4. ブラウザでアクセス

```
http://localhost:8000
```

## 使い方

### Webインターフェース（推奨）

1. ブラウザで `http://localhost:8000` を開く
2. ユーザーIDを入力（例: 1, 2, 3...）
3. 「新しいウォレットを作成」ボタンをクリック
4. ウォレットが作成され、1 XYMが自動送信されます
5. アドレス、公開鍵が表示されます
6. 「🔑 秘密鍵を表示」ボタンで秘密鍵を確認できます
7. 「エクスプローラーで見る」でSymbol Explorerでトランザクションを確認

**機能:**
- 📋 ワンクリックでアドレス・公開鍵・秘密鍵をコピー
- 🔐 秘密鍵はモーダルで保護（警告メッセージ付き）
- 🔗 Symbol Testnet Explorerへの直接リンク
- ✨ 美しいグラデーションUI

### API使用方法（開発者向け）

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

### 2. ウォレット情報取得（公開情報のみ）

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

### 3. ウォレットのエクスポート（秘密鍵を含む）

⚠️ **注意**: 秘密鍵は非常に重要な情報です。安全に管理してください。

```bash
curl http://localhost:8000/wallet/1/export
```

レスポンス例：
```json
{
  "success": true,
  "wallet": {
    "address": "TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "public_key": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "private_key": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
  }
}
```

## プロジェクト構造

```
symbol-wallet-service/
├── app/
│   ├── Database.php              # データベース管理クラス
│   └── SymbolWalletService.php   # ウォレット作成・送金サービス
├── public/
│   └── index.html                # Webインターフェース
├── database/
│   └── database.sqlite           # SQLiteデータベース（自動作成）
├── config.php                    # 設定ファイル
├── index.php                     # APIエンドポイント
├── composer.json                 # Composer設定
└── README.md                     # このファイル
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

## スクリーンショット

### メイン画面
- ユーザーIDを入力してウォレットを作成
- 作成されたウォレット情報を表示
- エクスプローラーへのリンク

### 秘密鍵モーダル
- 警告メッセージと共に秘密鍵を表示
- コピーボタンで簡単にコピー

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

## セキュリティ機能

- ✅ 秘密鍵はモーダルで隠蔽（デフォルトで非表示）
- ✅ 秘密鍵表示時に警告メッセージを表示
- ✅ データベースに秘密鍵を保存（本番環境では暗号化推奨）
- ✅ CORS設定済み

## 推奨される改善点

### 本番環境への移行前に実装すべき項目

1. **Symbol SDK の統合**: 実際のSymbol SDKを使用した鍵生成と署名
   - 現在は簡易的な実装のため、本番環境では必須
   
2. **秘密鍵の暗号化**: データベース保存時の暗号化
   - AES-256などで暗号化して保存
   
3. **認証機能**: APIアクセスの認証・認可
   - JWT認証やセッション管理の実装
   
4. **トランザクション確認**: 送信後の確認処理を追加
   - トランザクションの承認状態を確認
   
5. **残高チェック**: デポジットウォレットの残高確認
   - 送信前に残高が十分か確認
   
6. **ログ管理**: 詳細なトランザクションログ
   - 監査用のログ記録
   
7. **レート制限**: API呼び出しの制限
   - DDoS攻撃対策

## テストネット情報

- **ノードURL**: https://sym-test-01.opening-line.jp:3001
- **ネットワークタイプ**: 152 (testnet)
- **Generation Hash**: 7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836
- **エクスプローラー**: https://testnet.symbol.fyi

## トラブルシューティング

### ウォレット作成に失敗する

1. デポジットウォレットの秘密鍵とアドレスが正しく設定されているか確認
2. デポジットウォレットに十分な残高があるか確認
3. ノードURLが正しいか確認

### データベースエラー

1. `database/` ディレクトリの書き込み権限を確認
2. SQLite3拡張機能がインストールされているか確認

### ポート8000が使用中

別のポートを使用してください：
```bash
php -S localhost:8080 index.php
```

## よくある質問

**Q: 本番環境で使用できますか？**
A: 現在の実装は簡易版です。本番環境で使用する前に、上記の「推奨される改善点」を実装してください。

**Q: メインネットで使用できますか？**
A: `config.php` のノードURLとネットワークタイプを変更すれば可能ですが、十分なテストを行ってください。

**Q: 秘密鍵は安全ですか？**
A: データベースに平文で保存されています。本番環境では必ず暗号化してください。

**Q: 複数のユーザーで使用できますか？**
A: はい。各ユーザーIDに対して1つのウォレットが作成されます。

## ライセンス

MIT

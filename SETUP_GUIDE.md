# Symbol Wallet Service - セットアップガイド

## デポジットウォレットの準備

このサービスを使用するには、XYMを保有するデポジットウォレットが必要です。

### 手順1: デポジットウォレットにXYMを入金

現在の`.env`ファイルに設定されているデポジットウォレット：
```
アドレス: TBIBPDBSNF5Y6OBKXCNEPKPTJN5YEK3N7Q2JICA
秘密鍵: 0C0F96FA06CE7C584943980584CF635C82A6DBDE4096382EEFC72A1795B0B274
```

**残高: 0 XYM** ⚠️ XYMを入金する必要があります

#### Symbol Testnet Faucetから入金

1. https://testnet.symbol.tools/ にアクセス
2. アドレス `TBIBPDBSNF5Y6OBKXCNEPKPTJN5YEK3N7Q2JICA` を入力
3. 「REQUEST」ボタンをクリック
4. 100 XYMが入金されます（通常1-2分）

#### 残高確認

```bash
node symbol-helper.js balance TBIBPDBSNF5Y6OBKXCNEPKPTJN5YEK3N7Q2JICA
```

### 手順2: 新しいデポジットウォレットを作成（オプション）

既存のウォレットを使用したくない場合、新しいウォレットを作成できます：

```bash
node symbol-helper.js create
```

出力例：
```json
{
  "privateKey": "8B928811FEC167700D047C789B276F562AAEC221C0F8AFFA563687493C3046AE",
  "publicKey": "041B3CEC435E41B15DA69F363E24B422479CE171C0D127FA29AEEA91B5A9114A",
  "address": "TA2K63YE6V3DKFLA7M2JZMI5KFEOAZPAZ23MXVQ"
}
```

`.env`ファイルを更新：
```env
SYMBOL_DEPOSIT_PRIVATE_KEY=8B928811FEC167700D047C789B276F562AAEC221C0F8AFFA563687493C3046AE
SYMBOL_DEPOSIT_ADDRESS=TA2K63YE6V3DKFLA7M2JZMI5KFEOAZPAZ23MXVQ
```

Faucetから新しいアドレスにXYMを入金してください。

### 手順3: サービスを再起動

```bash
# サーバーを停止（Ctrl+C）
# サーバーを再起動
php -S localhost:8000 index.php
```

### 手順4: テスト

1. ブラウザで http://localhost:8000 を開く
2. ユーザーIDを入力（例: 1）
3. 「新しいウォレットを作成」をクリック
4. 1 XYMが自動的に送信されます

## トラブルシューティング

### エラー: "XYMの送信に失敗しました"

**原因:** デポジットウォレットの残高が不足しています

**解決策:**
1. デポジットウォレットの残高を確認
   ```bash
   node symbol-helper.js balance <あなたのアドレス>
   ```
2. 残高が0または不足している場合、Faucetから入金
3. 入金後、1-2分待ってから再試行

### エラー: "Transaction signing requires full Symbol SDK integration"

**原因:** 古いバージョンのsymbol-helper.jsを使用しています

**解決策:**
```bash
git pull  # 最新版を取得
npm install  # 依存関係を更新
```

### デポジットウォレットの推奨残高

- **最小:** 10 XYM（10ユーザー分）
- **推奨:** 100 XYM（100ユーザー分）
- **本番環境:** 必要に応じて

各ユーザーに1 XYM + トランザクション手数料（約0.2 XYM）が必要です。

## セキュリティに関する注意

⚠️ **重要:** 
- デポジットウォレットの秘密鍵は絶対に公開しないでください
- `.env`ファイルをGitにコミットしないでください（.gitignoreに含まれています）
- 本番環境では、秘密鍵を環境変数または暗号化されたストレージに保存してください

## 本番環境への移行

本番環境（mainnet）で使用する場合：

1. `.env`を更新：
```env
SYMBOL_NETWORK=mainnet
SYMBOL_NODE_URL=https://symbol.services:3001
SYMBOL_GENERATION_HASH=57F7DA205008026C776CB6AED843393F04CD458E0AA2D9F1D5F31A402072B2D6
SYMBOL_NETWORK_TYPE=104
```

2. メインネット用のデポジットウォレットを準備
3. 十分なXYMを入金
4. セキュリティ対策を強化（認証、暗号化など）

## サポート

問題が解決しない場合：
1. PHPサーバーのログを確認
2. Node.jsスクリプトを直接テスト
3. Symbol Explorerでトランザクションを確認: https://testnet.symbol.fyi

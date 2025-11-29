# Symbol Wallet Service - API仕様書

## 概要

Symbol Wallet ServiceのREST API仕様書です。

**ベースURL:** `http://localhost:8000`

**Content-Type:** `application/json; charset=utf-8`

## 認証

現在のバージョンでは認証は実装されていません。

## エンドポイント

### 1. ウォレット作成

新しいSymbolウォレットを作成し、デポジットウォレットから1 XYMを送信します。

**エンドポイント:** `POST /wallet/create`

**リクエストヘッダー:**
```
Content-Type: application/json
```

**リクエストボディ:**
```json
{
  "user_id": 1
}
```

**パラメータ:**
| 名前 | 型 | 必須 | 説明 |
|------|-----|------|------|
| user_id | integer | ✓ | ユーザーID（1以上の整数） |

**レスポンス（成功）:**
```json
{
  "success": true,
  "message": "ウォレットが作成され、1 XYMが送信されました",
  "wallet": {
    "address": "TBXRXRWMK45J673GDCT4MLRQHVNDJ46DEQSMGRI",
    "public_key": "342C2F9F2115C57395A66705ABEC31D77CF9B4A24AF3DF5D016B30830017DBAA"
  }
}
```

**レスポンス（失敗）:**
```json
{
  "success": false,
  "message": "XYMの送信に失敗しました"
}
```

**ステータスコード:**
- `200 OK` - 成功
- `400 Bad Request` - user_idが不正
- `500 Internal Server Error` - サーバーエラー

**例:**
```bash
curl -X POST http://localhost:8000/wallet/create \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1}'
```

---

### 2. ウォレット情報取得（公開情報）

ユーザーのウォレット情報（アドレスと公開鍵）を取得します。

**エンドポイント:** `GET /wallet/{user_id}`

**パスパラメータ:**
| 名前 | 型 | 必須 | 説明 |
|------|-----|------|------|
| user_id | integer | ✓ | ユーザーID |

**レスポンス（成功）:**
```json
{
  "success": true,
  "wallet": {
    "address": "TBXRXRWMK45J673GDCT4MLRQHVNDJ46DEQSMGRI",
    "public_key": "342C2F9F2115C57395A66705ABEC31D77CF9B4A24AF3DF5D016B30830017DBAA"
  }
}
```

**レスポンス（失敗）:**
```json
{
  "success": false,
  "message": "ウォレットが見つかりません"
}
```

**ステータスコード:**
- `200 OK` - 成功
- `404 Not Found` - ウォレットが見つからない

**例:**
```bash
curl http://localhost:8000/wallet/1
```

---

### 3. ウォレット情報取得（秘密鍵含む）

ユーザーのウォレット情報（秘密鍵を含む）を取得します。

⚠️ **警告:** 秘密鍵は非常に重要な情報です。このエンドポイントは慎重に使用してください。

**エンドポイント:** `GET /wallet/{user_id}/export`

**パスパラメータ:**
| 名前 | 型 | 必須 | 説明 |
|------|-----|------|------|
| user_id | integer | ✓ | ユーザーID |

**レスポンス（成功）:**
```json
{
  "success": true,
  "wallet": {
    "address": "TBXRXRWMK45J673GDCT4MLRQHVNDJ46DEQSMGRI",
    "public_key": "342C2F9F2115C57395A66705ABEC31D77CF9B4A24AF3DF5D016B30830017DBAA",
    "private_key": "383434AE8D7B11C315A7128BDD39ECCAEFA1ACAA1129E73DAFA0E5B094CD1DAA"
  }
}
```

**レスポンス（失敗）:**
```json
{
  "success": false,
  "message": "ウォレットが見つかりません"
}
```

**ステータスコード:**
- `200 OK` - 成功
- `404 Not Found` - ウォレットが見つからない

**例:**
```bash
curl http://localhost:8000/wallet/1/export
```

---

### 4. Webインターフェース

Webインターフェースを表示します。

**エンドポイント:** `GET /`

**レスポンス:**
HTMLページ

**例:**
ブラウザで `http://localhost:8000` を開く

---

## エラーレスポンス

すべてのエラーレスポンスは以下の形式です：

```json
{
  "success": false,
  "message": "エラーメッセージ"
}
```

### エラーメッセージ一覧

| メッセージ | 原因 | 対処法 |
|-----------|------|--------|
| user_idが必要です | user_idが指定されていない | user_idを指定してください |
| ウォレットが見つかりません | 指定されたuser_idのウォレットが存在しない | 正しいuser_idを指定するか、ウォレットを作成してください |
| XYMの送信に失敗しました | トランザクション送信に失敗 | デポジットウォレットの残高を確認してください |
| Invalid JSON response from helper | Node.jsヘルパーの実行に失敗 | サーバーログを確認してください |

## レート制限

現在のバージョンではレート制限は実装されていません。

## CORS

すべてのオリジンからのアクセスを許可しています：

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

## データ型

### Wallet Object

| フィールド | 型 | 説明 |
|-----------|-----|------|
| address | string | Symbolアドレス（39文字） |
| public_key | string | 公開鍵（64文字の16進数） |
| private_key | string | 秘密鍵（64文字の16進数）※exportのみ |

### Response Object

| フィールド | 型 | 説明 |
|-----------|-----|------|
| success | boolean | 成功/失敗 |
| message | string | メッセージ（オプション） |
| wallet | Wallet | ウォレット情報（オプション） |

## 使用例

### JavaScript (Fetch API)

```javascript
// ウォレット作成
async function createWallet(userId) {
  const response = await fetch('http://localhost:8000/wallet/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ user_id: userId })
  });
  
  const data = await response.json();
  return data;
}

// ウォレット情報取得
async function getWallet(userId) {
  const response = await fetch(`http://localhost:8000/wallet/${userId}`);
  const data = await response.json();
  return data;
}

// ウォレット情報取得（秘密鍵含む）
async function exportWallet(userId) {
  const response = await fetch(`http://localhost:8000/wallet/${userId}/export`);
  const data = await response.json();
  return data;
}
```

### PHP

```php
// ウォレット作成
$ch = curl_init('http://localhost:8000/wallet/create');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['user_id' => 1]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

### Python

```python
import requests

# ウォレット作成
response = requests.post(
    'http://localhost:8000/wallet/create',
    json={'user_id': 1}
)
data = response.json()

# ウォレット情報取得
response = requests.get('http://localhost:8000/wallet/1')
data = response.json()
```

## バージョン履歴

### v1.0.0 (2025-11-29)
- 初回リリース
- ウォレット作成API
- ウォレット情報取得API
- Webインターフェース

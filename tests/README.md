# テストスクリプト

このディレクトリには、開発・デバッグ用のテストスクリプトが含まれています。

## テストスクリプト一覧

### test-send.php
XYM送信機能のテスト

```bash
php tests/test-send.php
```

### test-db.php
データベース内容の確認

```bash
php tests/test-db.php
```

## 使用方法

WSL環境で実行してください：

```bash
bash -c "php tests/test-send.php"
bash -c "php tests/test-db.php"
```

## 注意事項

- これらのスクリプトは開発・デバッグ用です
- 本番環境では使用しないでください
- テストスクリプトはGitにコミットされますが、出力ファイルは無視されます

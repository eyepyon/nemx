#!/bin/bash

echo "Symbol Wallet Service - Installation Script"
echo "============================================="
echo ""

# Node.jsのバージョンチェック
if ! command -v node &> /dev/null; then
    echo "❌ Node.js がインストールされていません"
    echo "Node.js をインストールしてください: https://nodejs.org/"
    exit 1
fi

NODE_VERSION=$(node -v)
echo "✅ Node.js: $NODE_VERSION"

# npmのバージョンチェック
if ! command -v npm &> /dev/null; then
    echo "❌ npm がインストールされていません"
    exit 1
fi

NPM_VERSION=$(npm -v)
echo "✅ npm: $NPM_VERSION"

# Composerのバージョンチェック
if ! command -v composer &> /dev/null; then
    echo "❌ Composer がインストールされていません"
    echo "Composer をインストールしてください: https://getcomposer.org/"
    exit 1
fi

COMPOSER_VERSION=$(composer -V)
echo "✅ $COMPOSER_VERSION"

echo ""
echo "依存関係をインストールしています..."
echo ""

# PHP依存関係のインストール
echo "📦 PHP依存関係をインストール中..."
composer install

# Node.js依存関係のインストール
echo "📦 Node.js依存関係をインストール中..."
npm install

# データベースディレクトリの作成
if [ ! -d "database" ]; then
    mkdir -p database
    echo "✅ database ディレクトリを作成しました"
fi

# .envファイルの作成
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ .env ファイルを作成しました"
    echo ""
    echo "⚠️  重要: .env ファイルを編集して、デポジットウォレットの情報を設定してください"
    echo "   SYMBOL_DEPOSIT_PRIVATE_KEY=あなたの秘密鍵"
    echo "   SYMBOL_DEPOSIT_ADDRESS=あなたのアドレス"
fi

echo ""
echo "============================================="
echo "✅ インストールが完了しました！"
echo ""
echo "次のステップ:"
echo "1. .env ファイルを編集してデポジットウォレットの情報を設定"
echo "2. サーバーを起動: php -S localhost:8000 index.php"
echo "3. ブラウザで開く: http://localhost:8000"
echo ""

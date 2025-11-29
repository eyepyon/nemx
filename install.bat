@echo off
echo Symbol Wallet Service - Installation Script
echo =============================================
echo.

REM Node.jsのバージョンチェック
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo X Node.js がインストールされていません
    echo Node.js をインストールしてください: https://nodejs.org/
    exit /b 1
)

for /f "tokens=*" %%i in ('node -v') do set NODE_VERSION=%%i
echo √ Node.js: %NODE_VERSION%

REM npmのバージョンチェック
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo X npm がインストールされていません
    exit /b 1
)

for /f "tokens=*" %%i in ('npm -v') do set NPM_VERSION=%%i
echo √ npm: %NPM_VERSION%

REM Composerのバージョンチェック
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo X Composer がインストールされていません
    echo Composer をインストールしてください: https://getcomposer.org/
    exit /b 1
)

for /f "tokens=*" %%i in ('composer -V') do set COMPOSER_VERSION=%%i
echo √ %COMPOSER_VERSION%

echo.
echo 依存関係をインストールしています...
echo.

REM PHP依存関係のインストール
echo 📦 PHP依存関係をインストール中...
call composer install

REM Node.js依存関係のインストール
echo 📦 Node.js依存関係をインストール中...
call npm install

REM データベースディレクトリの作成
if not exist "database" (
    mkdir database
    echo √ database ディレクトリを作成しました
)

REM .envファイルの作成
if not exist ".env" (
    copy .env.example .env
    echo √ .env ファイルを作成しました
    echo.
    echo ⚠️  重要: .env ファイルを編集して、デポジットウォレットの情報を設定してください
    echo    SYMBOL_DEPOSIT_PRIVATE_KEY=あなたの秘密鍵
    echo    SYMBOL_DEPOSIT_ADDRESS=あなたのアドレス
)

echo.
echo =============================================
echo √ インストールが完了しました！
echo.
echo 次のステップ:
echo 1. .env ファイルを編集してデポジットウォレットの情報を設定
echo 2. サーバーを起動: php -S localhost:8000 index.php
echo 3. ブラウザで開く: http://localhost:8000
echo.
pause

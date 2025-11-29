<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\SymbolWalletService;
use App\Database;

// データベース初期化（SQLiteが利用できない場合はスキップ）
$db = null;
try {
    $db = new Database();
} catch (\Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    // データベースなしで続行
}

// ルーティング
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// 静的ファイルの配信
if ($request_uri === '/' || $request_uri === '/index.html') {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/public/index.html');
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// CORS設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($request_method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ルート: ウォレット作成
if ($request_uri === '/wallet/create' && $request_method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'user_idが必要です']);
        exit;
    }
    
    $walletService = new \App\SymbolWalletService();
    
    try {
        // 新しいウォレットを作成
        $wallet = $walletService->createWallet();
        
        // データベースに保存（利用可能な場合）
        if ($db !== null) {
            $db->saveUserWallet($userId, $wallet);
        }
        
        // 1 XYMを送信
        $sent = $walletService->sendInitialXym($wallet['address']);
        
        if ($sent) {
            echo json_encode([
                'success' => true,
                'message' => 'ウォレットが作成され、1 XYMが送信されました',
                'wallet' => [
                    'address' => $wallet['address'],
                    'public_key' => $wallet['public_key']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'XYMの送信に失敗しました']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ルート: ウォレット情報取得（公開情報のみ）
if (preg_match('/^\/wallet\/(\d+)$/', $request_uri, $matches) && $request_method === 'GET') {
    $userId = $matches[1];
    
    $wallet = null;
    if ($db !== null) {
        $wallet = $db->getUserWallet($userId, false);
    }
    
    if ($wallet) {
        echo json_encode([
            'success' => true,
            'wallet' => [
                'address' => $wallet['address'],
                'public_key' => $wallet['public_key']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'ウォレットが見つかりません']);
    }
    exit;
}

// ルート: ウォレット情報取得（秘密鍵を含む）
if (preg_match('/^\/wallet\/(\d+)\/export$/', $request_uri, $matches) && $request_method === 'GET') {
    $userId = $matches[1];
    
    $wallet = null;
    if ($db !== null) {
        $wallet = $db->getUserWallet($userId, true);
    }
    
    if ($wallet) {
        echo json_encode([
            'success' => true,
            'wallet' => [
                'address' => $wallet['address'],
                'public_key' => $wallet['public_key'],
                'private_key' => $wallet['private_key']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'ウォレットが見つかりません']);
    }
    exit;
}

// 404
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Not Found']);

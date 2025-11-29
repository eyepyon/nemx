<?php

// .envファイルを読み込む
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // コメント行をスキップ
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // KEY=VALUE形式をパース
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // クォートを削除
                $value = trim($value, '"\'');
                
                // 環境変数に設定
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}

// .envファイルを読み込む
loadEnv(__DIR__ . '/.env');

return [
    'symbol' => [
        'network' => $_ENV['SYMBOL_NETWORK'] ?? 'testnet',
        'node_url' => $_ENV['SYMBOL_NODE_URL'] ?? 'https://sym-test-01.opening-line.jp:3001',
        'generation_hash' => $_ENV['SYMBOL_GENERATION_HASH'] ?? '7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836',
        'network_type' => (int)($_ENV['SYMBOL_NETWORK_TYPE'] ?? 152),
        'deposit_wallet' => [
            'private_key' => $_ENV['SYMBOL_DEPOSIT_PRIVATE_KEY'] ?? '',
            'address' => $_ENV['SYMBOL_DEPOSIT_ADDRESS'] ?? '',
        ],
        'initial_amount' => 1000000, // 1 XYM
    ],
    'database' => [
        'path' => __DIR__ . '/database/database.sqlite'
    ]
];

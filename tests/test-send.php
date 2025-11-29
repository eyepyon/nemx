<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\SymbolWalletService;

$service = new SymbolWalletService();

// テスト用のアドレス
$testAddress = 'TBRHCKAHNOUKFSTJGOMUDFKME3S6JIJYE5XYD7A';

echo "Testing XYM send to: {$testAddress}\n";

$result = $service->sendInitialXym($testAddress);

if ($result) {
    echo "✅ Success!\n";
} else {
    echo "❌ Failed!\n";
}

echo "\nCheck error_log for details\n";

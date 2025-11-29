<?php

return [
    'network' => env('SYMBOL_NETWORK', 'testnet'),
    'node_url' => env('SYMBOL_NODE_URL', 'https://sym-test-01.opening-line.jp:3001'),
    'generation_hash' => env('SYMBOL_GENERATION_HASH'),
    'network_type' => env('SYMBOL_NETWORK_TYPE', 152),
    'deposit_wallet' => [
        'private_key' => env('SYMBOL_DEPOSIT_PRIVATE_KEY'),
        'address' => env('SYMBOL_DEPOSIT_ADDRESS'),
    ],
    'initial_amount' => 1000000, // 1 XYM (1,000,000 micro XYM)
];

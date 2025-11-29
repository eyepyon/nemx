<?php

return [
    'symbol' => [
        'network' => 'testnet',
        'node_url' => 'https://sym-test-01.opening-line.jp:3001',
        'generation_hash' => '7FCCD304802016BEBBCD342A332F91FF1F3BB5E902988B352697BE245F48E836',
        'network_type' => 152,
        'deposit_wallet' => [
            'private_key' => getenv('SYMBOL_DEPOSIT_PRIVATE_KEY') ?: 'your_private_key_here',
            'address' => getenv('SYMBOL_DEPOSIT_ADDRESS') ?: 'your_address_here',
        ],
        'initial_amount' => 1000000, // 1 XYM
    ],
    'database' => [
        'path' => __DIR__ . '/database/database.sqlite'
    ]
];

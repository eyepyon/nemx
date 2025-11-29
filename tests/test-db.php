<?php

$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Tables:\n";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

echo "\nUser wallets:\n";
$wallets = $db->query("SELECT * FROM user_wallets")->fetchAll(PDO::FETCH_ASSOC);
print_r($wallets);

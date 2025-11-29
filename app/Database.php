<?php

namespace App;

use PDO;

class Database
{
    private PDO $pdo;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $dbPath = $config['database']['path'];
        
        // データベースディレクトリが存在しない場合は作成
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->createTables();
    }
    
    private function createTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_wallets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL UNIQUE,
                address TEXT NOT NULL UNIQUE,
                public_key TEXT NOT NULL,
                private_key TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    public function saveUserWallet(int $userId, array $wallet): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_wallets (user_id, address, public_key, private_key)
            VALUES (:user_id, :address, :public_key, :private_key)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'address' => $wallet['address'],
            'public_key' => $wallet['public_key'],
            'private_key' => $wallet['private_key']
        ]);
    }
    
    public function getUserWallet(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT address, public_key FROM user_wallets WHERE user_id = :user_id
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}

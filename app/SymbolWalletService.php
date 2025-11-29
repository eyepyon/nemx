<?php

namespace App;

class SymbolWalletService
{
    private string $nodeUrl;
    private string $depositPrivateKey;
    private string $helperScript;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->nodeUrl = $config['symbol']['node_url'];
        $this->depositPrivateKey = $config['symbol']['deposit_wallet']['private_key'];
        $this->helperScript = __DIR__ . '/../symbol-helper.js';
    }

    /**
     * Node.jsヘルパースクリプトを実行
     */
    private function executeHelper(string $command, array $args = []): array
    {
        $cmd = "node " . escapeshellarg($this->helperScript) . " " . escapeshellarg($command);
        
        foreach ($args as $arg) {
            $cmd .= " " . escapeshellarg($arg);
        }
        
        // 環境変数を設定
        putenv("SYMBOL_NODE_URL={$this->nodeUrl}");
        
        $output = shell_exec($cmd . " 2>&1");
        
        if ($output === null) {
            throw new \Exception("Failed to execute helper script");
        }
        
        $result = json_decode(trim($output), true);
        
        if ($result === null) {
            throw new \Exception("Invalid JSON response from helper: " . $output);
        }
        
        return $result;
    }

    /**
     * 新しいウォレットを作成
     */
    public function createWallet(): array
    {
        $result = $this->executeHelper('create');
        
        return [
            'private_key' => $result['privateKey'],
            'public_key' => $result['publicKey'],
            'address' => $result['address'],
        ];
    }

    /**
     * デポジットウォレットから新しいウォレットに1 XYMを送信
     */
    public function sendInitialXym(string $recipientAddress): bool
    {
        try {
            $config = require __DIR__ . '/../config.php';
            $amount = $config['symbol']['initial_amount']; // 1 XYM (micro XYM)
            
            error_log("Attempting to send {$amount} micro XYM to {$recipientAddress}");
            error_log("Using private key: " . substr($this->depositPrivateKey, 0, 10) . "...");
            
            $result = $this->executeHelper('send', [
                $this->depositPrivateKey,
                $recipientAddress,
                (string)$amount
            ]);
            
            error_log("Send result: " . json_encode($result));
            
            if (isset($result['success']) && $result['success']) {
                error_log("XYM sent successfully to {$recipientAddress}. Hash: {$result['hash']}");
                return true;
            }
            
            $errorMsg = isset($result['error']) ? $result['error'] : 'Unknown error';
            $errorStack = isset($result['stack']) ? $result['stack'] : 'No stack trace';
            error_log("Failed to send XYM: {$errorMsg}");
            error_log("Stack trace: {$errorStack}");
            return false;
            
        } catch (\Exception $e) {
            error_log("Exception sending XYM: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * アドレスの残高を取得
     */
    public function getBalance(string $address): ?array
    {
        try {
            $result = $this->executeHelper('balance', [$address]);
            
            if (isset($result['success']) && $result['success']) {
                return [
                    'balance' => $result['balance'],
                    'balance_xym' => $result['balanceXym']
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Error getting balance: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 秘密鍵からアドレスを導出
     */
    public function deriveAddress(string $privateKey): ?array
    {
        try {
            $result = $this->executeHelper('derive', [$privateKey]);
            
            if (isset($result['success']) && $result['success']) {
                return [
                    'private_key' => $result['privateKey'],
                    'public_key' => $result['publicKey'],
                    'address' => $result['address']
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Error deriving address: " . $e->getMessage());
            return null;
        }
    }
}

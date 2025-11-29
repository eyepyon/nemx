<?php

namespace App;

class SymbolWalletService
{
    private string $nodeUrl;
    private string $generationHash;
    private int $networkType;
    private string $depositPrivateKey;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        $this->nodeUrl = $config['symbol']['node_url'];
        $this->generationHash = $config['symbol']['generation_hash'];
        $this->networkType = $config['symbol']['network_type'];
        $this->depositPrivateKey = $config['symbol']['deposit_wallet']['private_key'];
    }

    /**
     * 新しいウォレットを作成
     */
    public function createWallet(): array
    {
        // ランダムな秘密鍵を生成（64文字の16進数）
        $privateKey = bin2hex(random_bytes(32));
        
        // 秘密鍵から公開鍵を生成
        $publicKey = $this->derivePublicKey($privateKey);
        
        // 公開鍵からアドレスを生成
        $address = $this->deriveAddress($publicKey);
        
        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
            'address' => $address,
        ];
    }

    /**
     * デポジットウォレットから新しいウォレットに1 XYMを送信
     */
    public function sendInitialXym(string $recipientAddress): bool
    {
        try {
            $config = require __DIR__ . '/../config.php';
            $amount = $config['symbol']['initial_amount']; // 1 XYM
            
            // トランザクションを作成
            $transaction = $this->createTransferTransaction($recipientAddress, $amount);
            
            // トランザクションに署名
            $signedTransaction = $this->signTransaction($transaction, $this->depositPrivateKey);
            
            // トランザクションをアナウンス
            $ch = curl_init("{$this->nodeUrl}/transactions");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['payload' => $signedTransaction]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                error_log("XYM sent successfully to {$recipientAddress}");
                return true;
            }
            
            error_log("Failed to send XYM: " . $response);
            return false;
            
        } catch (\Exception $e) {
            error_log("Error sending XYM: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 秘密鍵から公開鍵を導出（簡易版）
     */
    private function derivePublicKey(string $privateKey): string
    {
        // 実際の実装ではsymbol-sdkを使用
        // ここでは簡易的な実装
        return hash('sha256', $privateKey);
    }

    /**
     * 公開鍵からアドレスを導出
     */
    private function deriveAddress(string $publicKey): string
    {
        // 実際の実装ではsymbol-sdkを使用
        $hash = hash('sha256', hex2bin($publicKey));
        $ripemd = hash('ripemd160', hex2bin($hash));
        return strtoupper(substr($ripemd, 0, 40));
    }

    /**
     * 転送トランザクションを作成
     */
    private function createTransferTransaction(string $recipientAddress, int $amount): array
    {
        return [
            'type' => 16724, // Transfer transaction
            'network' => $this->networkType,
            'version' => 1,
            'deadline' => $this->getDeadline(),
            'maxFee' => '100000',
            'recipientAddress' => $recipientAddress,
            'mosaics' => [
                [
                    'id' => '6BED913FA20223F8', // XYM mosaic ID
                    'amount' => (string)$amount
                ]
            ],
            'message' => 'Welcome to Symbol!'
        ];
    }

    /**
     * トランザクションに署名
     */
    private function signTransaction(array $transaction, string $privateKey): string
    {
        // 実際の実装ではsymbol-sdkを使用
        // ここでは簡易的な実装
        $payload = json_encode($transaction);
        return bin2hex($payload);
    }

    /**
     * デッドラインを取得（2時間後）
     */
    private function getDeadline(): int
    {
        return (time() - 1615853185) * 1000 + 7200000;
    }
}

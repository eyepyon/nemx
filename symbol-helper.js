#!/usr/bin/env node

/**
 * Symbol SDK Helper for PHP
 * PHPから呼び出されるNode.jsスクリプト
 * 
 * tweetnacl + Symbol REST APIを使用
 */

const nacl = require('tweetnacl');
const { sha3_256 } = require('js-sha3');
const ripemd160 = require('ripemd160');
const crypto = require('crypto');

const args = process.argv.slice(2);
const command = args[0];

// 設定
const NODE_URL = process.env.SYMBOL_NODE_URL || 'https://sym-test-01.opening-line.jp:3001';
const NETWORK_TYPE = 152; // TEST_NET

/**
 * 秘密鍵から公開鍵を導出
 */
function derivePublicKey(privateKeyHex) {
    const privateKey = Buffer.from(privateKeyHex, 'hex');
    const keyPair = nacl.sign.keyPair.fromSeed(privateKey);
    return Buffer.from(keyPair.publicKey).toString('hex').toUpperCase();
}

/**
 * Base32エンコード（Symbol用 - RFC 4648準拠）
 */
function base32Encode(data) {
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    let bits = 0;
    let value = 0;
    let output = '';
    
    for (let i = 0; i < data.length; i++) {
        value = (value << 8) | data[i];
        bits += 8;
        
        while (bits >= 5) {
            output += alphabet[(value >>> (bits - 5)) & 31];
            bits -= 5;
        }
    }
    
    if (bits > 0) {
        output += alphabet[(value << (5 - bits)) & 31];
    }
    
    // Symbolアドレスは39文字（パディングなし）
    // 21バイト（1 network byte + 20 hash bytes）→ 34文字になるはず
    // でも実際は39文字必要...チェックサムを追加
    
    return output;
}

/**
 * 公開鍵からアドレスを導出（Symbol testnet）
 */
function deriveAddress(publicKeyHex) {
    const publicKey = Buffer.from(publicKeyHex, 'hex');
    
    // Step 1: SHA3-256 hash
    const sha3Hash = Buffer.from(sha3_256.arrayBuffer(publicKey));
    
    // Step 2: RIPEMD160 hash
    const ripemdHash = new ripemd160().update(sha3Hash).digest();
    
    // Step 3: Add network byte (testnet = 0x98)
    const networkByte = Buffer.from([0x98]);
    const versionedHash = Buffer.concat([networkByte, ripemdHash]);
    
    // Step 4: Calculate checksum (first 3 bytes of SHA3-256)
    const checksumHash = Buffer.from(sha3_256.arrayBuffer(versionedHash));
    const checksum = checksumHash.slice(0, 3);
    
    // Step 5: Combine versioned hash + checksum
    const addressBytes = Buffer.concat([versionedHash, checksum]);
    
    // Step 6: Base32 encode
    const address = base32Encode(addressBytes);
    
    return address;
}

/**
 * 新しいウォレットを作成
 */
function createWallet() {
    try {
        // ランダムな32バイトの秘密鍵を生成
        const privateKey = crypto.randomBytes(32).toString('hex').toUpperCase();
        
        // 公開鍵を導出
        const publicKey = derivePublicKey(privateKey);
        
        // アドレスを導出
        const address = deriveAddress(publicKey);
        
        const result = {
            privateKey: privateKey,
            publicKey: publicKey,
            address: address
        };
        
        console.log(JSON.stringify(result));
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message }));
        process.exit(1);
    }
}

/**
 * XYMを送信
 * 注意: 完全なトランザクション署名実装が必要です
 */
async function sendXym() {
    try {
        const privateKeyHex = args[1];
        const recipientAddress = args[2];
        const amount = parseInt(args[3]) || 1000000;
        
        if (!privateKeyHex || !recipientAddress) {
            throw new Error('Private key and recipient address are required');
        }
        
        // 公開鍵を導出
        const publicKey = derivePublicKey(privateKeyHex);
        const senderAddress = deriveAddress(publicKey);
        
        // 注意: これは簡易実装です
        // 実際のトランザクション署名と送信には完全なSymbol SDKが必要です
        console.log(JSON.stringify({
            success: false,
            error: 'Transaction signing requires full Symbol SDK integration',
            note: 'Wallet creation works correctly. For sending XYM, please use Symbol Desktop Wallet or implement full SDK integration.',
            sender: senderAddress,
            recipient: recipientAddress,
            amount: amount
        }));
        
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message }));
        process.exit(1);
    }
}

/**
 * アドレスの残高を取得
 */
async function getBalance() {
    try {
        const address = args[1];
        
        if (!address) {
            throw new Error('Address is required');
        }
        
        const response = await fetch(`${NODE_URL}/accounts/${address}`);
        
        if (!response.ok) {
            if (response.status === 404) {
                const result = {
                    success: true,
                    address: address,
                    balance: '0',
                    balanceXym: '0.000000'
                };
                console.log(JSON.stringify(result));
                return;
            }
            throw new Error(`HTTP ${response.status}: ${await response.text()}`);
        }
        
        const accountInfo = await response.json();
        
        // XYM mosaicを探す
        const xymMosaic = accountInfo.account.mosaics.find(m => m.id === '6BED913FA20223F8');
        const balance = xymMosaic ? xymMosaic.amount : '0';
        
        const result = {
            success: true,
            address: address,
            balance: balance,
            balanceXym: (parseInt(balance) / 1000000).toFixed(6)
        };
        
        console.log(JSON.stringify(result));
        
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message }));
        process.exit(1);
    }
}

/**
 * 秘密鍵からアドレスを導出
 */
function deriveAddressCommand() {
    try {
        const privateKeyHex = args[1];
        
        if (!privateKeyHex) {
            throw new Error('Private key is required');
        }
        
        const publicKey = derivePublicKey(privateKeyHex);
        const address = deriveAddress(publicKey);
        
        const result = {
            success: true,
            privateKey: privateKeyHex.toUpperCase(),
            publicKey: publicKey,
            address: address
        };
        
        console.log(JSON.stringify(result));
        
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message }));
        process.exit(1);
    }
}

// コマンド実行
switch (command) {
    case 'create':
        createWallet();
        break;
    case 'send':
        sendXym();
        break;
    case 'balance':
        getBalance();
        break;
    case 'derive':
        deriveAddressCommand();
        break;
    case 'test':
        console.log('Symbol SDK Helper is working!');
        break;
    default:
        console.error('Unknown command. Available: create, send, balance, derive, test');
        process.exit(1);
}

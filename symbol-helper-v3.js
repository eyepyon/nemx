#!/usr/bin/env node

/**
 * Symbol SDK Helper for PHP
 * Symbol SDK v3を使用
 */

const { PrivateKey } = require('symbol-sdk');
const { SymbolFacade, models } = require('symbol-sdk/symbol');
const crypto = require('crypto');

const args = process.argv.slice(2);
const command = args[0];

// 設定
const NODE_URL = process.env.SYMBOL_NODE_URL || 'https://sym-test-01.opening-line.jp:3001';
const NETWORK = 'testnet';

/**
 * 新しいウォレットを作成
 */
function createWallet() {
    try {
        const facade = new SymbolFacade(NETWORK);
        const privateKey = PrivateKey.random();
        const keyPair = new facade.constructor.KeyPair(privateKey);
        const address = facade.network.publicKeyToAddress(keyPair.publicKey);
        
        const result = {
            privateKey: privateKey.toString(),
            publicKey: keyPair.publicKey.toString(),
            address: address.toString()
        };
        
        console.log(JSON.stringify(result));
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message, stack: error.stack }));
        process.exit(1);
    }
}

/**
 * XYMを送信
 */
async function sendXym() {
    try {
        const privateKeyHex = args[1];
        const recipientAddress = args[2];
        const amount = parseInt(args[3]) || 1000000;
        
        if (!privateKeyHex || !recipientAddress) {
            throw new Error('Private key and recipient address are required');
        }
        
        const facade = new SymbolFacade(NETWORK);
        const privateKey = new PrivateKey(privateKeyHex);
        const keyPair = new facade.constructor.KeyPair(privateKey);
        
        // ネットワーク情報を取得
        const nodeInfoResponse = await fetch(`${NODE_URL}/node/info`);
        const nodeInfo = await nodeInfoResponse.json();
        
        const networkPropsResponse = await fetch(`${NODE_URL}/network/properties`);
        const networkProps = await networkPropsResponse.json();
        const epochAdjustment = parseInt(networkProps.network.epochAdjustment.replace(/s$/, ''));
        
        // Deadline (2時間後)
        const deadline = BigInt((Date.now() - epochAdjustment * 1000) + (2 * 60 * 60 * 1000));
        
        // メッセージを作成
        const messageBytes = Buffer.from('Welcome to Symbol!', 'utf8');
        const message = new Uint8Array(messageBytes.length + 1);
        message[0] = 0; // Plain message type
        message.set(messageBytes, 1);
        
        // トランザクションを作成
        const transaction = facade.transactionFactory.create({
            type: 'transfer_transaction_v1',
            signerPublicKey: keyPair.publicKey.toString(),
            fee: 2000000n,
            deadline: deadline,
            recipientAddress: recipientAddress,
            mosaics: [
                { mosaicId: 0x6BED913FA20223F8n, amount: BigInt(amount) }
            ],
            message: message
        });
        
        // 署名
        const signature = facade.signTransaction(keyPair, transaction);
        const jsonPayload = facade.transactionFactory.constructor.attachSignature(transaction, signature);
        
        // ペイロードを作成
        const payload = Buffer.from(jsonPayload).toString('hex').toUpperCase();
        
        // アナウンス
        const announceResponse = await fetch(`${NODE_URL}/transactions`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ payload: payload })
        });
        
        if (announceResponse.ok) {
            // トランザクションハッシュを計算
            const transactionHash = facade.hashTransaction(transaction);
            
            const result = {
                success: true,
                hash: transactionHash.toString(),
                signer: facade.network.publicKeyToAddress(keyPair.publicKey).toString()
            };
            console.log(JSON.stringify(result));
        } else {
            const errorText = await announceResponse.text();
            throw new Error(`Failed to announce transaction: ${errorText}`);
        }
        
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message, stack: error.stack }));
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
        const xymMosaic = accountInfo.account.mosaics.find(m => m.id === '72C0212E67A08BCE');
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
        
        const facade = new SymbolFacade(NETWORK);
        const privateKey = new PrivateKey(privateKeyHex);
        const keyPair = new facade.constructor.KeyPair(privateKey);
        const address = facade.network.publicKeyToAddress(keyPair.publicKey);
        
        const result = {
            success: true,
            privateKey: privateKey.toString(),
            publicKey: keyPair.publicKey.toString(),
            address: address.toString()
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
        console.log('Symbol SDK v3 Helper is working!');
        break;
    default:
        console.error('Unknown command. Available: create, send, balance, derive, test');
        process.exit(1);
}

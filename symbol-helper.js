#!/usr/bin/env node

/**
 * Symbol SDK Helper for PHP
 * PHPから呼び出されるNode.jsスクリプト
 * 
 * Symbol SDK v2を使用
 */

const symbolSdk = require('symbol-sdk');
const crypto = require('crypto');

const args = process.argv.slice(2);
const command = args[0];

// 設定
const NODE_URL = process.env.SYMBOL_NODE_URL || 'https://sym-test-01.opening-line.jp:3001';
const NETWORK_TYPE = symbolSdk.NetworkType.TEST_NET;

/**
 * 新しいウォレットを作成
 */
function createWallet() {
    try {
        const account = symbolSdk.Account.generateNewAccount(NETWORK_TYPE);
        
        const result = {
            privateKey: account.privateKey,
            publicKey: account.publicKey,
            address: account.address.plain()
        };
        
        console.log(JSON.stringify(result));
    } catch (error) {
        console.log(JSON.stringify({ success: false, error: error.message }));
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
        
        // アカウントを作成
        const account = symbolSdk.Account.createFromPrivateKey(privateKeyHex, NETWORK_TYPE);
        
        // レポジトリファクトリーを作成
        const repositoryFactory = new symbolSdk.RepositoryFactoryHttp(NODE_URL);
        
        // ネットワーク情報を取得
        const epochAdjustment = await repositoryFactory.getEpochAdjustment().toPromise();
        const generationHash = await repositoryFactory.getGenerationHash().toPromise();
        const networkCurrencies = await repositoryFactory.getCurrencies().toPromise();
        
        // 受信者アドレス
        const recipientAddr = symbolSdk.Address.createFromRawAddress(recipientAddress);
        
        // トランザクションを作成
        const transferTransaction = symbolSdk.TransferTransaction.create(
            symbolSdk.Deadline.create(epochAdjustment),
            recipientAddr,
            [new symbolSdk.Mosaic(networkCurrencies.currency.mosaicId, symbolSdk.UInt64.fromUint(amount))],
            symbolSdk.PlainMessage.create('Welcome to Symbol!'),
            NETWORK_TYPE,
            symbolSdk.UInt64.fromUint(2000000) // max fee
        );
        
        // 署名
        const signedTransaction = account.sign(transferTransaction, generationHash);
        
        // アナウンス
        const transactionHttp = repositoryFactory.createTransactionRepository();
        await transactionHttp.announce(signedTransaction).toPromise();
        
        const result = {
            success: true,
            hash: signedTransaction.hash,
            signer: account.address.plain()
        };
        
        console.log(JSON.stringify(result));
        
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
        
        const repositoryFactory = new symbolSdk.RepositoryFactoryHttp(NODE_URL);
        const accountHttp = repositoryFactory.createAccountRepository();
        
        try {
            const accountInfo = await accountHttp.getAccountInfo(symbolSdk.Address.createFromRawAddress(address)).toPromise();
            
            // XYM mosaicを探す
            const networkCurrencies = await repositoryFactory.getCurrencies().toPromise();
            const xymMosaic = accountInfo.mosaics.find(m => m.id.toHex() === networkCurrencies.currency.mosaicId.toHex());
            const balance = xymMosaic ? xymMosaic.amount.toString() : '0';
            
            const result = {
                success: true,
                address: address,
                balance: balance,
                balanceXym: (parseInt(balance) / 1000000).toFixed(6)
            };
            
            console.log(JSON.stringify(result));
        } catch (error) {
            if (error.statusCode === 404) {
                const result = {
                    success: true,
                    address: address,
                    balance: '0',
                    balanceXym: '0.000000'
                };
                console.log(JSON.stringify(result));
            } else {
                throw error;
            }
        }
        
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
        
        const account = symbolSdk.Account.createFromPrivateKey(privateKeyHex, NETWORK_TYPE);
        
        const result = {
            success: true,
            privateKey: account.privateKey,
            publicKey: account.publicKey,
            address: account.address.plain()
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

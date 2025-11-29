<?php

namespace App\Http\Controllers;

use App\Services\SymbolWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    private SymbolWalletService $walletService;

    public function __construct(SymbolWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * ユーザー登録時にウォレットを作成
     */
    public function createWalletOnRegistration(Request $request)
    {
        try {
            DB::beginTransaction();

            // 新しいウォレットを作成
            $wallet = $this->walletService->createWallet();

            // ユーザーにウォレット情報を保存
            $user = Auth::user();
            $user->wallet_address = $wallet['address'];
            $user->wallet_private_key = encrypt($wallet['private_key']); // 暗号化して保存
            $user->wallet_public_key = $wallet['public_key'];
            $user->save();

            // デポジットウォレットから1 XYMを送信
            $sent = $this->walletService->sendInitialXym($wallet['address']);

            if (!$sent) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'ウォレットの作成に失敗しました'
                ], 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ウォレットが作成され、1 XYMが送信されました',
                'wallet' => [
                    'address' => $wallet['address'],
                    'public_key' => $wallet['public_key']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ウォレット情報を取得
     */
    public function getWallet()
    {
        $user = Auth::user();

        if (!$user->wallet_address) {
            return response()->json([
                'success' => false,
                'message' => 'ウォレットが作成されていません'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'wallet' => [
                'address' => $user->wallet_address,
                'public_key' => $user->wallet_public_key
            ]
        ]);
    }
}

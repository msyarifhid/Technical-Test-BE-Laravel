<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateWallet;
use App\Models\Transaction;
use App\Models\Wallet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function showDepositForm()
    {
        return view('page.deposit');
    }

    public function showWithdrawForm()
    {
        return view('page.withdraw');
    }

    // Method for deposit
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();

        DB::beginTransaction();

        try {
            // Third-party service integration
            $response = $this->callThirdPartyService($user, $validated['amount'], 'deposit');

            if ($response['status'] == 1) {
                UpdateWallet::dispatch($user->id, $request['amount'], 'deposit');
            } else {
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'type' => 'deposit',
                    'status' => 'failed',
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Deposit successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Deposit failed'], 500);
        }
    }

    // Method for withdrawal
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        if ($wallet->balance < $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();
        try {
            // Third-party service integration
            $response = $this->callThirdPartyService($user, $validated['amount'], 'withdraw');

            if ($response['status'] == 1) {
                UpdateWallet::dispatch($user->id, $validated['amount'], 'withdrawal');
            } else {
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'type' => 'withdrawal',
                    'status' => 'failed',
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Withdrawal successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Withdrawal failed'], 500);
        }
    }

    // Function to call third-party service
    private function callThirdPartyService($user, $amount, $type)
    {
        // $url = 'https://yourdomain.com/' . $type;
        $url = $_SERVER['HTTP_ORIGIN'] . "/" . $type;
        // $url = "http://127.0.0.1:8080/" . $type;
        $timestamp = now()->timestamp;
        $order_id = uniqid();
        $authorization = base64_encode($user->name);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $authorization,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'order_id' => $order_id,
            'amount' => $amount,
            'timestamp' => $timestamp,
        ]);

        return [
            'status' => 1,
            'data' => $response
        ];
    }
}

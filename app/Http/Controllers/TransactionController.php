<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
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
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $orderId = uniqid(); // Generate a unique order ID

        // Call to third-party payment service
        $response = $this->callThirdPartyService($orderId, $amount);

        if ($response['status'] == 1) {
            // Dispatch job to update wallet
            UpdateWallet::dispatch($user->id, $amount, 'deposit');

            return response()->json(['message' => 'Deposit successful'], 200);
        } else {
            return response()->json(['message' => 'Deposit failed'], 400);
        }
    }

    // Method for withdrawal
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        if ($user->balance < $amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $orderId = uniqid(); // Generate a unique order ID

        // Call to third-party payment service
        $response = $this->callThirdPartyService($orderId, $amount);

        if ($response['status'] == 1) {
            // Dispatch job to update wallet
            UpdateWallet::dispatch($user->id, $amount, 'withdrawal');

            return response()->json(['message' => 'Withdrawal successful'], 200);
        } else {
            return response()->json(['message' => 'Withdrawal failed'], 400);
        }
    }

    // Function to call third-party service
    private function callThirdPartyService($orderId, $amount)
    {
        $fullName = "Muhamad Syarif Hidayat";
        $encodedName = base64_encode($fullName);
        $timestamp = now()->toIso8601String();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $encodedName,
        ])->post('https://yourdomain.com/deposit', [
            'order_id' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'timestamp' => $timestamp,
        ]);

        return $response->json();
    }
}

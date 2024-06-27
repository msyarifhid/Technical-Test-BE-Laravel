<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $amount;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $amount, $type)
    {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $user = User::find($this->userId);
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if ($this->type === 'deposit') {
                $wallet->balance += $this->amount;
                $status = 'success';
            } else {
                if ($wallet->balance >= $this->amount) {
                    $wallet->balance -= $this->amount;
                    $status = 'success';
                } else {
                    $status = 'failed';
                }
            }

            $wallet->save();

            Transaction::create([
                'user_id' => $this->userId,
                'amount' => $this->amount,
                'type' => $this->type,
                'status' => $status,
            ]);
        });
    }
}

<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
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
            $user = User::lockForUpdate()->find($this->userId);

            if ($this->type == 'deposit') {
                $user->balance += $this->amount;
            } elseif ($this->type == 'withdrawal') {
                if ($user->balance < $this->amount) {
                    throw new \Exception('Insufficient balance');
                }
                $user->balance -= $this->amount;
            }

            $user->save();

            Transaction::create([
                'user_id' => $this->userId,
                'type' => $this->type,
                'amount' => $this->amount,
                'status' => 'completed',
            ]);
        });
    }
}

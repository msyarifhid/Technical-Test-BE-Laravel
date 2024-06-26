<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => rand(100, 1000),
                'status' => 'completed',
            ]);
        }
    }
}

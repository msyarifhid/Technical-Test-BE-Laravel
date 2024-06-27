<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Method show view
     *
     * @return void
     */
    public function showTransactions()
    {
        $transactions = Transaction::with('user')->orderBy('id', 'asc')->paginate(10);
        return view('page.admin.transction', compact('transactions'));
    }
}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="container">
                        <h2>Transaction History</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Order ID</th>
                                    <th style="width: 25%;">User</th>
                                    <th style="width: 10%;">Type</th>
                                    <th style="width: 15%;">Amount</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: auto;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr style="text-align: center">
                                    <td>{{ $transaction->id }}</td>
                                    <td style="text-align: left">{{ $transaction->user->name }}</td>
                                    <td>{{ $transaction->type }}</td>
                                    <td>{{ $transaction->amount }}</td>
                                    <td>{{ $transaction->status }}</td>
                                    <td>{{ $transaction->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

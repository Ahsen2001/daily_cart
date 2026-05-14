<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function balance(Customer $customer): float
    {
        return (float) $customer->wallet_balance;
    }

    public function topUp(Customer $customer, float $amount): WalletTransaction
    {
        $this->ensurePositive($amount);

        return $this->credit($customer, $amount, 'top_up', 'admin_adjustment', 'Wallet top-up placeholder');
    }

    public function payForOrder(Order $order, Payment $payment): WalletTransaction
    {
        return DB::transaction(function () use ($order, $payment) {
            $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
            $amount = (float) $payment->amount;

            if ((float) $customer->wallet_balance < $amount) {
                throw ValidationException::withMessages(['payment_method' => 'Insufficient wallet balance.']);
            }

            $customer->decrement('wallet_balance', $amount);
            $customer->refresh();

            $transaction = WalletTransaction::create([
                'user_id' => $customer->user_id,
                'transaction_type' => 'payment',
                'type' => 'debit',
                'source' => 'order_payment',
                'amount' => $amount,
                'balance_after' => $customer->wallet_balance,
                'currency' => CurrencyService::CURRENCY,
                'reference' => $order->order_number,
                'description' => 'Wallet payment for order '.$order->order_number,
            ]);

            app(PaymentService::class)->markPaid($payment, 'WALLET-'.$transaction->id);

            return $transaction;
        });
    }

    public function refundToWallet(Customer $customer, float $amount, string $reference, string $description): WalletTransaction
    {
        $this->ensurePositive($amount);

        return $this->credit($customer, $amount, 'refund', 'refund', $description, $reference);
    }

    public function credit(
        Customer $customer,
        float $amount,
        string $transactionType,
        string $source,
        ?string $description = null,
        ?string $reference = null
    ): WalletTransaction {
        $this->ensurePositive($amount);

        return DB::transaction(function () use ($customer, $amount, $transactionType, $source, $description, $reference) {
            $lockedCustomer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $lockedCustomer->increment('wallet_balance', $amount);
            $lockedCustomer->refresh();

            return WalletTransaction::create([
                'user_id' => $lockedCustomer->user_id,
                'transaction_type' => $transactionType,
                'type' => 'credit',
                'source' => $source,
                'amount' => $amount,
                'balance_after' => $lockedCustomer->wallet_balance,
                'currency' => CurrencyService::CURRENCY,
                'reference' => $reference,
                'description' => $description,
            ]);
        });
    }

    private function ensurePositive(float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
        }
    }
}

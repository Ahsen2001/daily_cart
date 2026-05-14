<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminUser() ? true : null;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->customer?->id === $payment->order?->customer_id;
    }

    public function process(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment)
            && in_array($payment->payment_method, ['card', 'bank_transfer'], true)
            && $payment->status === 'pending';
    }
}

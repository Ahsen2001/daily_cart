<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltySetting;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoyaltyPointService
{
    public function setting(): LoyaltySetting
    {
        return LoyaltySetting::query()->where('status', 'active')->latest()->first()
            ?? LoyaltySetting::create(['spend_amount_per_point' => 100, 'redemption_value_per_point' => 1, 'status' => 'active']);
    }

    public function balance(Customer $customer): int
    {
        return $customer->loyaltyBalance();
    }

    public function redemptionValue(int $points): float
    {
        return round($points * (float) $this->setting()->redemption_value_per_point, 2);
    }

    public function validateRedemption(Customer $customer, int $points, float $orderTotal): float
    {
        if ($points <= 0) {
            return 0.0;
        }

        if ($points > $this->balance($customer)) {
            throw ValidationException::withMessages(['loyalty_points' => 'Loyalty points cannot exceed your available balance.']);
        }

        $value = $this->redemptionValue($points);

        if ($value > $orderTotal) {
            throw ValidationException::withMessages(['loyalty_points' => 'Loyalty discount cannot make the order total negative.']);
        }

        return $value;
    }

    public function redeem(Customer $customer, Order $order, int $points): ?LoyaltyPoint
    {
        if ($points <= 0) {
            return null;
        }

        return DB::transaction(function () use ($customer, $order, $points) {
            $lockedCustomer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $balance = $this->balance($lockedCustomer);

            if ($points > $balance) {
                throw ValidationException::withMessages(['loyalty_points' => 'Loyalty point balance cannot become negative.']);
            }

            return LoyaltyPoint::create([
                'customer_id' => $lockedCustomer->id,
                'order_id' => $order->id,
                'points' => -$points,
                'type' => 'redeemed',
                'description' => 'Redeemed for order '.$order->order_number,
                'balance_after' => $balance - $points,
            ]);
        });
    }

    public function earnForOrder(Order $order): ?LoyaltyPoint
    {
        if ($order->order_status !== 'delivered') {
            return null;
        }

        if ($order->customer->loyaltyPoints()->where('order_id', $order->id)->where('type', 'earned')->exists()) {
            return null;
        }

        $setting = $this->setting();
        $points = (int) floor((float) $order->total_amount / $setting->spend_amount_per_point);

        if ($points <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $points) {
            $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
            $balance = $this->balance($customer);

            return LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points' => $points,
                'type' => 'earned',
                'description' => 'Earned from delivered order '.$order->order_number,
                'balance_after' => $balance + $points,
            ]);
        });
    }

    public function reverseForOrder(Order $order, string $description): ?LoyaltyPoint
    {
        $earned = (int) $order->customer->loyaltyPoints()->where('order_id', $order->id)->where('type', 'earned')->sum('points');

        if ($earned <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order, $earned, $description) {
            $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
            $balance = $this->balance($customer);
            $pointsToReverse = min($earned, $balance);

            return LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points' => -$pointsToReverse,
                'type' => 'reversed',
                'description' => $description,
                'balance_after' => $balance - $pointsToReverse,
            ]);
        });
    }
}

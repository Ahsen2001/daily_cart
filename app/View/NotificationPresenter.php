<?php

namespace App\View;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class NotificationPresenter
{
    public const CATEGORIES = [
        'orders' => 'Orders', 'delivery' => 'Delivery', 'payments' => 'Payments',
        'refunds' => 'Refunds', 'support' => 'Support', 'finance' => 'Earnings & payouts',
        'account' => 'Account', 'reviews' => 'Reviews', 'inventory' => 'Inventory', 'system' => 'Other',
    ];

    public static function category(Notification|string $notification): string
    {
        $type = strtolower($notification instanceof Notification ? $notification->type : $notification);

        return match (true) {
            str_contains($type, 'support'), str_contains($type, 'ticket') => 'support',
            str_contains($type, 'refund') => 'refunds',
            str_contains($type, 'payment'), str_contains($type, 'bank_transfer'), str_contains($type, 'invoice') => 'payments',
            str_contains($type, 'earning'), str_contains($type, 'payout') => 'finance',
            str_contains($type, 'rating'), str_contains($type, 'review') => 'reviews',
            str_contains($type, 'stock'), str_contains($type, 'product') => 'inventory',
            str_contains($type, 'account'), str_contains($type, 'registration'), str_contains($type, 'approved'), str_contains($type, 'rejected') => 'account',
            str_contains($type, 'delivery'), str_contains($type, 'rider_assigned'), str_contains($type, 'picked_up'), str_contains($type, 'out_for_delivery') => 'delivery',
            str_contains($type, 'order') => 'orders',
            default => 'system',
        };
    }

    public static function label(Notification $notification): string
    {
        return self::CATEGORIES[self::category($notification)];
    }

    public static function tone(Notification $notification): string
    {
        $text = strtolower($notification->type.' '.$notification->title.' '.($notification->data['status'] ?? ''));

        return match (true) {
            str_contains($text, 'failed'), str_contains($text, 'rejected'), str_contains($text, 'cancelled'), str_contains($text, 'reported') => 'danger',
            str_contains($text, 'pending'), str_contains($text, 'request'), str_contains($text, 'low_stock'), str_contains($text, 'assigned') => 'warning',
            str_contains($text, 'successful'), str_contains($text, 'approved'), str_contains($text, 'delivered'), str_contains($text, 'paid'), str_contains($text, 'resolved'), str_contains($text, 'earning') => 'success',
            default => 'info',
        };
    }

    public static function requiresAction(Notification $notification): bool
    {
        $type = strtolower($notification->type);

        return str_contains($type, 'request') || str_contains($type, 'pending') || str_contains($type, 'failed')
            || str_contains($type, 'reported') || str_contains($type, 'low_stock')
            || in_array($type, ['new_order', 'delivery_assigned', 'support_ticket_assigned'], true);
    }

    public static function isOrderActivity(Notification $notification): bool
    {
        return filled($notification->order_id ?? $notification->data['order_id'] ?? null)
            && in_array(self::category($notification), ['orders', 'delivery', 'payments', 'refunds'], true);
    }

    public static function action(Notification $notification, User $user): ?array
    {
        $data = $notification->data ?? [];
        $role = strtolower((string) ($user->role?->name ?? ''));
        $isAdmin = in_array($role, ['admin', 'super admin'], true);
        $orderId = $notification->order_id ?: ($data['order_id'] ?? null);
        $deliveryId = $data['delivery_id'] ?? null;
        $ticketId = $data['support_ticket_id'] ?? $data['ticket_id'] ?? null;

        if ($ticketId) {
            return ['label' => 'Open ticket', 'url' => $isAdmin ? route('admin.support-tickets.show', $ticketId) : route('support.tickets.show', $ticketId)];
        }
        if (str_contains($notification->type, 'refund')) {
            return ['label' => 'Review refund', 'url' => $isAdmin ? route('admin.refunds.index') : ($role === 'vendor' ? route('vendor.refunds.index') : route('customer.refunds.index'))];
        }
        if (str_contains($notification->type, 'payout') || str_contains($notification->type, 'earning')) {
            $route = match ($role) { 'vendor' => 'vendor.earnings.index', 'rider' => 'rider.earnings.index', default => null };
            return $route ? ['label' => 'View earnings', 'url' => route($route)] : null;
        }
        if (str_contains($notification->type, 'rating') && $role === 'rider') {
            return ['label' => 'View ratings', 'url' => route('rider.ratings.index')];
        }
        if (($data['product_id'] ?? null) && $role === 'vendor') {
            return ['label' => 'Review product', 'url' => route('vendor.products.edit', $data['product_id'])];
        }
        if ($role === 'rider' && $deliveryId) {
            return ['label' => 'Open delivery', 'url' => route('rider.deliveries.show', $deliveryId)];
        }
        if ($orderId) {
            $route = match ($role) {
                'customer' => 'customer.orders.show', 'vendor' => 'vendor.orders.show',
                'admin', 'super admin' => 'admin.orders.show', default => null,
            };
            return $route ? ['label' => $role === 'customer' ? 'Track order' : 'Open order', 'url' => route($route, $orderId)] : null;
        }

        return null;
    }

    public static function applyCategory(Builder|Relation $query, string $category): Builder|Relation
    {
        $patterns = self::patterns($category);
        if ($category === 'system') {
            $known = collect(array_keys(self::CATEGORIES))->reject(fn ($key) => $key === 'system')->flatMap(fn ($key) => self::patterns($key));
            return $query->where(function (Builder $builder) use ($known) {
                foreach ($known as $pattern) {
                    $builder->where('type', 'not like', '%'.$pattern.'%');
                }
            });
        }

        return $query->where(function (Builder $builder) use ($patterns) {
            foreach ($patterns as $index => $pattern) {
                $builder->{$index === 0 ? 'where' : 'orWhere'}('type', 'like', '%'.$pattern.'%');
            }
        });
    }

    public static function applyActionRequired(Builder|Relation $query): Builder|Relation
    {
        return $query->where(function (Builder $builder) {
            foreach (['request', 'pending', 'failed', 'reported', 'low_stock'] as $index => $pattern) {
                $builder->{$index === 0 ? 'where' : 'orWhere'}('type', 'like', '%'.$pattern.'%');
            }

            $builder->orWhereIn('type', ['new_order', 'delivery_assigned', 'support_ticket_assigned']);
        });
    }

    private static function patterns(string $category): array
    {
        return match ($category) {
            'orders' => ['order'], 'delivery' => ['delivery', 'rider_assigned', 'picked_up', 'out_for_delivery'],
            'payments' => ['payment', 'bank_transfer', 'invoice'], 'refunds' => ['refund'],
            'support' => ['support', 'ticket'], 'finance' => ['earning', 'payout'],
            'account' => ['account', 'registration', 'approved', 'rejected'], 'reviews' => ['rating', 'review'],
            'inventory' => ['stock', 'product'], default => [],
        };
    }
}

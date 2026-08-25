<?php

namespace App\Services;

use App\Models\Setting;
use InvalidArgumentException;

class ContentPageService
{
    /**
     * Return the available editable pages.
     */
    public function pages(): array
    {
        return [
            'about' => 'About',
            'contact' => 'Contact',
            'offers' => 'Offers',
        ];
    }

    /**
     * Check whether the requested page is supported.
     */
    public function exists(string $page): bool
    {
        return array_key_exists(
            $page,
            $this->pages()
        );
    }

    /**
     * Return the readable page label.
     */
    public function label(string $page): string
    {
        $this->ensurePageExists($page);

        return $this->pages()[$page];
    }

    /**
     * Return saved content with default fallback values.
     */
    public function content(string $page): array
    {
        return Setting::values(
            $this->defaults($page)
        );
    }

    /**
     * Save validated page settings.
     */
    public function save(array $values): void
    {
        Setting::putMany($values);
    }

    /**
     * Return the default settings for a page.
     */
    public function defaults(string $page): array
    {
        $this->ensurePageExists($page);

        $defaults = [
            'about' => [
                'title' => 'About DailyCart',

                'subtitle' => 'Daily essentials, delivered smart.',

                'body' => "DailyCart is a smart online shopping and delivery platform for groceries, vegetables, fruits, household items, bakery goods, pharmacy products, and daily essentials.\n\nWe connect customers, vendors, riders, and admins in one reliable local delivery workflow built for Sri Lanka and LKR payments.",

                'email' => 'uahsens1@gmail.com',

                'phone' => '+94 75 460 3008',

                'address' => 'Batticaloa, Sri Lanka',

                'cta_label' => 'Browse Categories',

                'cta_url' => '/categories',
            ],

            'contact' => [
                'title' => 'Contact DailyCart',

                'subtitle' => 'Need help with an order, vendor account, rider request, or platform question?',

                'body' => "Reach the DailyCart team for customer support, vendor onboarding, rider coordination, payment help, and general platform questions.\n\nOur team will review your message and guide you to the right support path.",

                'email' => 'uahsens1@gmail.com',

                'phone' => '+94 75 460 3008',

                'address' => 'Batticaloa, Sri Lanka',

                'cta_label' => 'Start Shopping',

                'cta_url' => '/register',
            ],

            'offers' => [
                'title' => 'DailyCart Offers',

                'subtitle' => 'Fresh deals and savings on daily essentials.',

                'body' => "Explore active DailyCart offers, promotions, and savings from approved vendors.\n\nAll offers are subject to availability, product approval, stock, schedule, and offer validity.",

                'email' => 'uahsens1@gmail.com',

                'phone' => '+94 75 460 3008',

                'address' => 'Batticaloa, Sri Lanka',

                'cta_label' => 'View Products',

                'cta_url' => '/products',
            ],
        ];

        return collect($defaults[$page])
            ->mapWithKeys(
                fn($value, $field) => [
                    "page_{$page}_{$field}" => $value,
                ]
            )
            ->toArray();
    }

    /**
     * Stop unsupported page names from being used.
     */
    private function ensurePageExists(string $page): void
    {
        if (! $this->exists($page)) {
            throw new InvalidArgumentException(
                "Unsupported content page: {$page}"
            );
        }
    }
}

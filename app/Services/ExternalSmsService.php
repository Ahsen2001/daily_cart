<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExternalSmsService
{
    public function send(string $phone, string $message): void
    {
        $endpoint = config('services.sms.endpoint');

        if (blank($endpoint)) {
            if (app()->environment(['local', 'testing'])) {
                Log::info('Local SMS delivery', ['phone' => $phone, 'message' => $message]);

                return;
            }

            throw new RuntimeException('SMS_GATEWAY_URL must be configured to send phone verification codes.');
        }

        // Explicitly bypass any stale local development proxy for the external SMS gateway.
        $request = Http::acceptJson()
            ->timeout(10)
            ->withOptions(['proxy' => config('services.sms.proxy', '')]);

        if (config('services.sms.provider') === 'smslenz') {
            $response = $request->post(rtrim($endpoint, '/').'/send-sms', [
                'user_id' => config('services.sms.user_id'),
                'api_key' => config('services.sms.token'),
                'sender_id' => config('services.sms.sender'),
                'contact' => $this->sriLankanPhone($phone),
                'message' => mb_substr($message, 0, 1500),
            ]);

            $response->throw();

            if (! $response->json('success')) {
                throw new RuntimeException($response->json('message') ?: 'SMSlenz rejected the SMS request.');
            }

            return;
        }

        if (filled(config('services.sms.token'))) {
            $request = $request->withToken(config('services.sms.token'));
        }

        $request->post($endpoint, [
            'to' => $phone,
            'sender' => config('services.sms.sender'),
            'message' => $message,
        ])->throw();
    }

    private function sriLankanPhone(string $phone): string
    {
        $number = preg_replace('/[^0-9+]/', '', trim($phone));

        if (str_starts_with($number, '+94')) {
            return $number;
        }

        if (str_starts_with($number, '94')) {
            return '+'.$number;
        }

        return '+94'.ltrim($number, '0');
    }
}

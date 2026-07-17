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

        $request = Http::acceptJson()->timeout(10);

        if (filled(config('services.sms.token'))) {
            $request = $request->withToken(config('services.sms.token'));
        }

        $request->post($endpoint, [
            'to' => $phone,
            'sender' => config('services.sms.sender'),
            'message' => $message,
        ])->throw();
    }
}

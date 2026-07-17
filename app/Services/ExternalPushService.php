<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExternalPushService
{
    public function send(User $user, string $title, string $message): void
    {
        $endpoint = config('services.push.endpoint');

        if (blank($endpoint)) {
            if (app()->environment(['local', 'testing'])) {
                Log::info('Local push delivery', [
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                ]);

                return;
            }

            throw new RuntimeException('PUSH_GATEWAY_URL must be configured to send push notifications.');
        }

        $request = Http::acceptJson()->timeout(10);

        if (filled(config('services.push.token'))) {
            $request = $request->withToken(config('services.push.token'));
        }

        $request->post($endpoint, [
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
        ])->throw();
    }
}

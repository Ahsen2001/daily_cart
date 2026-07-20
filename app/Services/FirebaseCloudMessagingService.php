<?php

namespace App\Services;

use App\Exceptions\InvalidDeviceTokenException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FirebaseCloudMessagingService
{
    public function sendToToken(
        string $token,
        string $title,
        string $message,
        array $data,
        string $channelId
    ): void {
        $this->send([
            'token' => $token,
            'notification' => ['title' => $title, 'body' => $message],
            'data' => $this->stringData($data),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => $channelId,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ],
            'apns' => [
                'payload' => ['aps' => ['sound' => 'default']],
            ],
        ]);
    }

    public function sendToTopic(
        string $topic,
        string $title,
        string $message,
        array $data,
        string $channelId
    ): void {
        $this->send([
            'topic' => $topic,
            'notification' => ['title' => $title, 'body' => $message],
            'data' => $this->stringData($data),
            'android' => [
                'priority' => 'normal',
                'notification' => ['channel_id' => $channelId],
            ],
        ]);
    }

    private function send(array $message): void
    {
        $projectId = (string) config('services.firebase.project_id');

        if ($projectId === '' || blank(config('services.firebase.credentials'))) {
            if (app()->environment(['local', 'testing'])) {
                Log::info('Firebase delivery skipped because credentials are not configured.', [
                    'target' => isset($message['token']) ? 'device' : 'topic',
                    'data' => $message['data'] ?? [],
                ]);

                return;
            }

            throw new RuntimeException('Firebase HTTP v1 credentials are not configured.');
        }

        $response = Http::acceptJson()
            ->withToken($this->accessToken())
            ->timeout(15)
            ->post(
                'https://fcm.googleapis.com/v1/projects/'.rawurlencode($projectId).'/messages:send',
                ['message' => $message],
            );

        if ($this->isInvalidToken($response)) {
            throw new InvalidDeviceTokenException('Firebase rejected the device token.');
        }

        $response->throw();
    }

    private function accessToken(): string
    {
        $cacheKey = 'firebase-http-v1-access-token:'.sha1((string) config('services.firebase.project_id'));

        return Cache::remember($cacheKey, now()->addMinutes(50), function (): string {
            $credentials = $this->credentials();
            $now = time();
            $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $this->base64Url(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsigned = $header.'.'.$claims;
            $signed = openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

            if (! $signed) {
                throw new RuntimeException('Unable to sign the Firebase service account assertion.');
            }

            $response = Http::asForm()->timeout(15)->post(
                $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $unsigned.'.'.$this->base64Url($signature),
                ],
            )->throw();

            return (string) $response->json('access_token');
        });
    }

    private function credentials(): array
    {
        $configured = (string) config('services.firebase.credentials');
        $json = is_file($configured) ? file_get_contents($configured) : $configured;
        $credentials = json_decode((string) $json, true, flags: JSON_THROW_ON_ERROR);

        if (blank($credentials['client_email'] ?? null) || blank($credentials['private_key'] ?? null)) {
            throw new RuntimeException('Firebase credentials must contain client_email and private_key.');
        }

        return $credentials;
    }

    private function isInvalidToken(Response $response): bool
    {
        if (! in_array($response->status(), [400, 404], true)) {
            return false;
        }

        $body = strtoupper($response->body());

        return str_contains($body, 'UNREGISTERED')
            || str_contains($body, 'REGISTRATION_TOKEN_NOT_REGISTERED')
            || str_contains($body, 'INVALID_ARGUMENT');
    }

    private function stringData(array $data): array
    {
        return collect($data)
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR))
            ->all();
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

<?php

namespace App\Services;

use App\Models\PaymentGatewayTransaction;
use App\Models\Refund;
use App\Models\RefundAttemptEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PayHereRefundService
{
    /** @return array{gateway_refund_id:string,attempt_uuid:string} */
    public function refund(Refund $refund): array
    {
        $refund->loadMissing(['payment.gatewayTransactions']);
        $transaction = $refund->payment?->gatewayTransactions
            ->first(fn (PaymentGatewayTransaction $item) => $item->gateway === 'payhere' && filled($item->gateway_payment_id));

        if (! $transaction) {
            throw ValidationException::withMessages(['refund' => 'The PayHere payment identity is unavailable. The refund was not sent.']);
        }
        if (blank(config('payhere.app_id')) || blank(config('payhere.app_secret'))) {
            throw ValidationException::withMessages(['refund' => 'PayHere Refund API credentials are not configured.']);
        }

        $existing = $refund->attemptEvents()->where('event_type', 'succeeded')->latest('id')->first();
        if ($existing) {
            return ['gateway_refund_id' => (string) $existing->gateway_refund_id, 'attempt_uuid' => $existing->attempt_uuid];
        }

        $this->ensurePreviousAttemptIsSafeToRetry($refund);
        $attemptUuid = (string) Str::uuid();
        $request = [
            'payment_id' => (string) $transaction->gateway_payment_id,
            'description' => Str::limit($refund->reason, 240),
            'amount' => number_format((float) $refund->amount, 2, '.', ''),
        ];

        DB::transaction(function () use ($refund, $transaction, $attemptUuid, $request) {
            $locked = Refund::query()->lockForUpdate()->findOrFail($refund->id);
            if ($locked->status !== 'requested') {
                throw ValidationException::withMessages(['refund' => 'Only requested refunds can be sent to PayHere.']);
            }
            $this->record($locked, $transaction, $attemptUuid, 'initiated', requestPayload: $request);
        });

        try {
            $tokenResponse = Http::asForm()
                ->withBasicAuth((string) config('payhere.app_id'), (string) config('payhere.app_secret'))
                ->timeout(20)
                ->post((string) config('payhere.oauth_url'), ['grant_type' => 'client_credentials']);

            if (! $tokenResponse->successful() || blank($tokenResponse->json('access_token'))) {
                $this->record($refund, $transaction, $attemptUuid, 'failed', $tokenResponse->status(), 'oauth_failed', 'PayHere OAuth authentication failed.', $request, $this->safeResponse($tokenResponse->json()));
                throw ValidationException::withMessages(['refund' => 'PayHere authentication failed. No refund was recorded.']);
            }

            $response = Http::withToken((string) $tokenResponse->json('access_token'))
                ->acceptJson()->asJson()->timeout(30)
                ->post((string) config('payhere.refund_url'), $request);
            $payload = $this->safeResponse($response->json());

            if (! $response->successful() || (int) data_get($payload, 'status') !== 1 || blank(data_get($payload, 'data'))) {
                $this->record($refund, $transaction, $attemptUuid, 'failed', $response->status(), (string) data_get($payload, 'status', 'refund_failed'), (string) data_get($payload, 'msg', 'PayHere refund failed.'), $request, $payload);
                throw ValidationException::withMessages(['refund' => (string) data_get($payload, 'msg', 'PayHere did not process the refund.')]);
            }

            $gatewayRefundId = (string) data_get($payload, 'data');
            $this->record($refund, $transaction, $attemptUuid, 'succeeded', $response->status(), null, (string) data_get($payload, 'msg'), $request, $payload, $gatewayRefundId);

            return ['gateway_refund_id' => $gatewayRefundId, 'attempt_uuid' => $attemptUuid];
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (ConnectionException $exception) {
            $this->record($refund, $transaction, $attemptUuid, 'indeterminate', null, 'network_error', 'The gateway result is unknown and requires reconciliation.', $request);
            throw ValidationException::withMessages(['refund' => 'The PayHere response was not received. Verify the gateway before retrying to prevent a duplicate refund.']);
        } catch (Throwable $exception) {
            report($exception);
            $this->record($refund, $transaction, $attemptUuid, 'indeterminate', null, 'unexpected_error', 'The gateway result is unknown and requires reconciliation.', $request);
            throw ValidationException::withMessages(['refund' => 'The PayHere refund result is uncertain. Verify it before retrying.']);
        }
    }

    private function ensurePreviousAttemptIsSafeToRetry(Refund $refund): void
    {
        $latest = $refund->attemptEvents()->latest('id')->first();
        if ($latest && in_array($latest->event_type, ['initiated', 'indeterminate'], true)) {
            throw ValidationException::withMessages(['refund' => 'A previous PayHere attempt has an uncertain result. Reconcile it before retrying.']);
        }
    }

    private function record(
        Refund $refund,
        PaymentGatewayTransaction $transaction,
        string $attemptUuid,
        string $eventType,
        ?int $httpStatus = null,
        ?string $errorCode = null,
        ?string $message = null,
        ?array $requestPayload = null,
        ?array $responsePayload = null,
        ?string $gatewayRefundId = null,
    ): RefundAttemptEvent {
        return RefundAttemptEvent::query()->firstOrCreate(
            ['attempt_uuid' => $attemptUuid, 'event_type' => $eventType],
            [
                'refund_id' => $refund->id,
                'payment_id' => $refund->payment_id,
                'payment_gateway_transaction_id' => $transaction->id,
                'gateway' => 'payhere',
                'amount' => $refund->amount,
                'currency' => $refund->payment?->currency ?? CurrencyService::CURRENCY,
                'gateway_refund_id' => $gatewayRefundId,
                'http_status' => $httpStatus,
                'error_code' => $errorCode,
                'message' => $message,
                'request_payload' => $requestPayload,
                'response_payload' => $responsePayload,
            ],
        );
    }

    private function safeResponse(mixed $payload): array
    {
        return is_array($payload) ? $payload : ['unparseable_response' => true];
    }
}

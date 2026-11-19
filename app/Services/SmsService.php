<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Helpers\MobileNumber;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

class SmsService
{
    public function sendRegistrationOtp(string $phone, string $otp, ?DateTimeInterface $expiresAt = null): array
    {
        return $this->sendWhatsappOtp($phone, $otp, $expiresAt);
    }

    public function sendWhatsappOtp(string $phone, string $otp, ?DateTimeInterface $expiresAt = null): array
    {
        if (! MobileNumber::valid($phone)) {
            return $this->whatsappFailure('invalid_phone');
        }

        // Keep two-argument callers compatible; active flows pass their persisted expiry.
        $expiresAt = $expiresAt ? Carbon::instance($expiresAt) : now()->addMinutes(10);
        if ($expiresAt->lessThanOrEqualTo(now())) {
            return $this->whatsappFailure('otp_expired');
        }

        return $this->requestWhatsappSender('POST', '/api/external/messages/send', [
            'phone' => MobileNumber::normalize($phone),
            'message' => trans('app.whatsapp_otp_message', [
                'otp' => $otp,
                'expires_at' => $expiresAt->copy()->utc()->format('Y-m-d H:i:s').' UTC',
            ]),
        ]);
    }

    /** Internal diagnostics only; wallet balances use the provider's points terminology. */
    public function whatsappWalletStatus(): array
    {
        return $this->requestWhatsappSender('GET', '/api/external/wallet');
    }

    public function send(string $phone, string $message, ?string $smsId = null): array
    {
        if (! MobileNumber::valid($phone)) {
            return ['success' => false, 'error' => 'Invalid mobile number'];
        }
        if (! config('services.sms.endpoint') || ! config('services.sms.username') || ! config('services.sms.password')) {
            return [
                'success' => false,
                'error' => 'SMS service credentials are not configured',
            ];
        }

        $payload = [
            'UserName' => (string) config('services.sms.username'),
            'Password' => (string) config('services.sms.password'),
            'SMSText' => $message,
            'SMSLang' => (string) config('services.sms.lang', 'e'),
            'SMSSender' => (string) config('services.sms.sender'),
            'SMSReceiver' => $this->normalizePhone($phone),
            'SMSID' => $smsId ?? (string) Str::uuid(),
        ];

        try {
            $response = Http::timeout((int) config('services.sms.timeout', 10))
                ->acceptJson()
                ->post((string) config('services.sms.endpoint'), $payload);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'error' => 'SMS provider rejected request',
                    'status' => $response->status(),
                    'response' => $response->body(),
                ];
            }

            return [
                'success' => true,
                'response' => $response->body(),
                'provider_message_id' => $payload['SMSID'],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizePhone(string $phone): string
    {
        return ltrim((string) MobileNumber::normalize($phone), '+');
    }

    private function whatsappFailure(string $error, array $details = []): array
    {
        return array_merge([
            'success' => false,
            'transport_success' => false,
            'provider_accepted' => null,
            'delivery_confirmed' => false,
            'error' => $error,
        ], $details);
    }

    private function requestWhatsappSender(string $method, string $path, array $payload = []): array
    {
        $baseUrl = rtrim(trim((string) config('services.whatsapp_sender.base_url')), '/');
        $token = trim((string) config('services.whatsapp_sender.api_token'));
        $timeout = filter_var(config('services.whatsapp_sender.timeout', 15), FILTER_VALIDATE_INT);
        $url = parse_url($baseUrl);

        if (! $url || empty($url['host']) || ($url['scheme'] ?? null) !== 'https'
            || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])
            || $token === '' || preg_match('/[\r\n]/', $token) || $timeout === false || $timeout < 1) {
            return $this->whatsappFailure('whatsapp_sender_not_configured');
        }

        try {
            $request = Http::withHeaders(['X-API-Token' => $token])
                ->acceptJson()
                ->asJson()
                ->timeout($timeout)
                // Laravel 8 exposes the Guzzle connection timeout via withOptions().
                ->withOptions(['connect_timeout' => min(5, $timeout)])
                ->withoutRedirecting();

            // No automatic POST retry: a timed-out request may already have sent the message.
            $response = $method === 'POST'
                ? $request->post($baseUrl.$path, $payload)
                : $request->get($baseUrl.$path);

            if (! $response->successful()) {
                return $this->whatsappFailure('whatsapp_http_failure', ['status' => $response->status()]);
            }

            return $this->interpretWhatsappResponse($response, $method === 'GET');
        } catch (ConnectionException $e) {
            return $this->whatsappFailure('whatsapp_connection_failed');
        } catch (\Throwable $e) {
            // Exception messages can include the token, recipient or message body.
            return $this->whatsappFailure('whatsapp_request_failed');
        }
    }

    private function interpretWhatsappResponse(Response $response, bool $wallet): array
    {
        $body = json_decode($response->body(), true);
        $details = ['transport_success' => true, 'status' => $response->status()];
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->whatsappFailure('whatsapp_invalid_response', $details);
        }

        if ($wallet) {
            if (! is_array($body) || ! is_bool($body['success'] ?? null)) {
                return $this->whatsappFailure('whatsapp_wallet_schema_unverified', $details);
            }
            if ($body['success'] === false) {
                return $this->whatsappFailure('whatsapp_provider_rejected', $details);
            }
            if (! is_array($body['wallet'] ?? null)
                || ! is_int($body['wallet']['userId'] ?? null) || $body['wallet']['userId'] < 1
                || ! is_int($body['wallet']['walletPoints'] ?? null) || $body['wallet']['walletPoints'] < 0) {
                return $this->whatsappFailure('whatsapp_wallet_invalid_response', $details);
            }

            $fields = [
                'wallet_points' => 'walletPoints',
                'remaining_points' => 'remainingPoints',
                'daily_limit' => 'dailyLimit',
                'sent_today' => 'sentToday',
                'remaining_daily_limit' => 'remainingDailyLimit',
            ];
            $normalized = ['provider_user_id' => $body['wallet']['userId']];
            foreach ($fields as $key => $field) {
                if (! is_int($body[$field] ?? null) || $body[$field] < 0) {
                    return $this->whatsappFailure('whatsapp_wallet_invalid_response', $details);
                }
                $normalized[$key] = $body[$field];
            }
            // Reject conflicting copies of the same walletPoints field, rather than guessing.
            // Do not reconcile remainingPoints against walletPoints or prior send balances.
            if ($normalized['wallet_points'] !== $body['wallet']['walletPoints']) {
                return $this->whatsappFailure('whatsapp_wallet_invalid_response', $details);
            }

            return array_merge($details, [
                'success' => true,
                'provider_accepted' => null,
                'delivery_confirmed' => false,
            ], $normalized);
        }

        if (! is_array($body) || ! is_bool($body['success'] ?? null)) {
            return $this->whatsappFailure('whatsapp_acceptance_unverified', $details);
        }
        if ($body['success'] === false) {
            return $this->whatsappFailure('whatsapp_provider_rejected', array_merge($details, [
                'provider_accepted' => false,
            ]));
        }

        // Confirmed send contract: HTTP 2xx and boolean success=true mean acceptance.
        // Validate metadata independently, without coercion or trusting echoed PII.
        $messageId = $body['messageId'] ?? null;
        $status = $body['status'] ?? null;
        $pointsCharged = $body['pointsCharged'] ?? null;
        $remainingPoints = $body['remainingPoints'] ?? null;

        return array_merge($details, [
            'success' => true,
            'provider_accepted' => true,
            // Even an apparent delivered status is not independently verified delivery.
            'delivery_confirmed' => false,
            'provider_message_id' => is_int($messageId) && $messageId > 0 ? $messageId : null,
            'provider_status_name' => is_string($status) && preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $status) ? $status : null,
            'provider_points_charged' => is_int($pointsCharged) && $pointsCharged >= 0 ? $pointsCharged : null,
            'provider_remaining_points' => is_int($remainingPoints) && $remainingPoints >= 0 ? $remainingPoints : null,
        ]);
    }
}

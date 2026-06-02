<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmsService
{
    public function sendRegistrationOtp(string $phone, string $otp): array
    {

        if ($this->isInfobipWhatsappConfigured()) {
            return $this->sendInfobipWhatsappTemplate(
                phone: $phone,
                templateName: (string) config('services.infobip.whatsapp_template'),
                placeholders: [$otp],
                language: (string) config('services.infobip.whatsapp_lang', 'en')
            );
        }

        return $this->send($phone, 'Your verification code is: '.$otp);
    }

    public function send(string $phone, string $message, ?string $smsId = null): array
    {
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
        $countryCode = (string) config('services.sms.country_code', '2');
        $normalized = ltrim($phone, '+');

        if (! $countryCode || Str::startsWith($normalized, $countryCode)) {
            return $normalized;
        }

        return $countryCode.$normalized;
    }

    private function isInfobipWhatsappConfigured(): bool
    {
        return (bool) (
            config('services.infobip.base_url')
            && config('services.infobip.api_key')
            && config('services.infobip.whatsapp_from')
            && config('services.infobip.whatsapp_template')
        );
    }

    private function sendInfobipWhatsappTemplate(
        string $phone,
        string $templateName,
        array $placeholders,
        string $language = 'en',
        ?string $messageId = null
    ): array {
        $endpoint = rtrim((string) config('services.infobip.base_url'), '/').'/whatsapp/1/message/template';

        $payload = [
            'messages' => [[
                'from' => (string) config('services.infobip.whatsapp_from'),
                'to' => $phone,
                'messageId' => $messageId ?? (string) Str::uuid(),
                'content' => [
                    'templateName' => $templateName,
                    'templateData' => [
                        'body' => [
                            'placeholders' => $placeholders,
                        ],
                    ],
                    'language' => $language,
                ],
            ]],
        ];
        try {
            $response = Http::timeout((int) config('services.infobip.timeout', 10))
                ->withHeaders([
                    'Authorization' => 'App '.(string) config('services.infobip.api_key'),
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Infobip rejected request',
                    'status' => $response->status(),
                    'response' => $response->body(),
                ];
            }

            $responseJson = $response->json();
            $firstMessage = $responseJson['messages'][0] ?? [];
            $status = $firstMessage['status'] ?? [];

            return [
                'success' => true,
                'response' => $response->body(),
                'provider_message_id' => $firstMessage['messageId'] ?? ($payload['messages'][0]['messageId'] ?? null),
                'provider_status_group' => $status['groupName'] ?? null,
                'provider_status_name' => $status['name'] ?? null,
                'provider_status_id' => $status['id'] ?? null,
            ];
        } catch (\Throwable $e) {

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

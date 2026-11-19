<?php

namespace Tests\Unit\Services;

use App\Services\SmsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsappSenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.whatsapp_sender.base_url' => 'https://sender.example.test/',
            'services.whatsapp_sender.api_token' => 'test-secret', 'services.whatsapp_sender.timeout' => 15]);
        Http::swap(new Factory());
        app()->setLocale('en');
    }

    public function test_confirmed_request_contract_and_timeouts_without_claiming_acceptance(): void
    {
        $this->travelTo(now()->startOfSecond());
        $expiry = now()->addMinutes(10);
        $options = null;
        Http::fake(function ($request, $requestOptions) use (&$options) {
            $options = $requestOptions;
            return Http::response('{}', 202);
        });

        $result = (new SmsService())->sendRegistrationOtp('01012345678', '0123', $expiry);

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'https://sender.example.test/api/external/messages/send'
            && $request->hasHeader('X-API-Token', 'test-secret')
            && $request->hasHeader('Accept', 'application/json')
            && $request->hasHeader('Content-Type', 'application/json')
            && $request->data() === ['phone' => '+201012345678',
                'message' => 'Your Mazadat verification code is: *0123*'."\nIt expires at ".
                    $expiry->copy()->utc()->format('Y-m-d H:i:s').' UTC.']);
        $this->assertSame(15, $options['timeout']);
        $this->assertSame(5, $options['connect_timeout']);
        $this->assertFalse($options['allow_redirects']);
        $this->assertTrue($result['transport_success']);
        $this->assertFalse($result['success']);
        $this->assertNull($result['provider_accepted']);
        $this->assertFalse($result['delivery_confirmed']);
        $this->assertSame('whatsapp_acceptance_unverified', $result['error']);
        $this->assertArrayNotHasKey('response', $result);
        Http::assertSentCount(1);
        $this->travelBack();
    }

    /** @dataProvider unacceptedBodies */
    public function test_false_or_missing_boolean_success_is_not_acceptance(array $body, ?bool $accepted, string $error): void
    {
        Http::fake(fn () => Http::response($body));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertFalse($result['success']);
        $this->assertTrue($result['transport_success']);
        $this->assertSame($accepted, $result['provider_accepted']);
        $this->assertSame($error, $result['error']);
        $this->assertFalse($result['delivery_confirmed']);
    }

    public static function unacceptedBodies(): array
    {
        return [
            // Only the success indicator is interpreted; no failure-body schema is assumed.
            'explicit rejection' => [['success' => false], false, 'whatsapp_provider_rejected'],
            'missing indicator' => [['status' => 'pending'], null, 'whatsapp_acceptance_unverified'],
            'string true' => [['success' => 'true'], null, 'whatsapp_acceptance_unverified'],
            'string false' => [['success' => 'false'], null, 'whatsapp_acceptance_unverified'],
            'integer one' => [['success' => 1], null, 'whatsapp_acceptance_unverified'],
            'integer zero' => [['success' => 0], null, 'whatsapp_acceptance_unverified'],
            'null indicator' => [['success' => null], null, 'whatsapp_acceptance_unverified'],
            'array indicator' => [['success' => [true]], null, 'whatsapp_acceptance_unverified'],
        ];
    }

    /** @dataProvider httpFailures */
    public function test_http_failures_are_sanitized_and_never_retried(int $status): void
    {
        Http::fake(fn () => Http::response(['success' => true, 'messageId' => 3358, 'status' => 'pending'], $status));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame('whatsapp_http_failure', $result['error']);
        $this->assertFalse($result['success']);
        $this->assertFalse($result['transport_success']);
        $this->assertSame($status, $result['status']);
        $this->assertArrayNotHasKey('response', $result);
        $this->assertNull($result['provider_accepted']);
        Http::assertSentCount(1);
    }

    /** @dataProvider acceptedHttpStatuses */
    public function test_confirmed_response_means_acceptance_without_delivery_or_sensitive_data(int $httpStatus): void
    {
        Log::spy();
        Http::fake(fn () => Http::response([
            'success' => true, 'message' => 'Message sent successfully',
            'phone' => 'REDACTED-RECIPIENT', 'senderPhone' => 'REDACTED-SENDER',
            'messageId' => 3358, 'status' => 'pending', 'pointsCharged' => 1, 'remainingPoints' => 1419,
        ], $httpStatus));
        $result = (new SmsService())->sendRegistrationOtp('+966512345678', '1234');
        $this->assertSame([
            'transport_success' => true, 'status' => $httpStatus,
            'success' => true, 'provider_accepted' => true, 'delivery_confirmed' => false,
            'provider_message_id' => 3358, 'provider_status_name' => 'pending',
            'provider_points_charged' => 1, 'provider_remaining_points' => 1419,
        ], $result);
        Http::assertSentCount(1);
        Log::shouldNotHaveReceived('info');
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');
    }

    public static function acceptedHttpStatuses(): array
    {
        return [[200], [201], [202]];
    }

    /** @dataProvider invalidMetadata */
    public function test_metadata_is_validated_without_overriding_explicit_acceptance(array $metadata): void
    {
        Http::fake(fn () => Http::response(array_merge(['success' => true], $metadata)));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertTrue($result['success']);
        $this->assertTrue($result['provider_accepted']);
        $this->assertFalse($result['delivery_confirmed']);
        foreach (['provider_message_id', 'provider_status_name', 'provider_points_charged', 'provider_remaining_points'] as $key) {
            $this->assertNull($result[$key]);
        }
    }

    public static function invalidMetadata(): array
    {
        return [
            'missing' => [[]],
            'null' => [['messageId' => null, 'status' => null, 'pointsCharged' => null, 'remainingPoints' => null]],
            'numeric strings' => [['messageId' => '3358', 'status' => 123, 'pointsCharged' => '1', 'remainingPoints' => '1419']],
            'negative' => [['messageId' => -1, 'status' => '', 'pointsCharged' => -1, 'remainingPoints' => -1]],
            'fractions' => [['messageId' => 3358.5, 'status' => "pending\nSECRET", 'pointsCharged' => 0.5, 'remainingPoints' => 1.5]],
            'booleans' => [['messageId' => true, 'status' => false, 'pointsCharged' => true, 'remainingPoints' => false]],
            'arrays' => [['messageId' => [], 'status' => [], 'pointsCharged' => [], 'remainingPoints' => []]],
            'zero id and long status' => [['messageId' => 0, 'status' => str_repeat('a', 65)]],
        ];
    }

    public function test_zero_points_are_valid_and_other_status_text_is_not_delivery_evidence(): void
    {
        Http::fake(fn () => Http::response(['success' => true, 'status' => 'delivered',
            'messageId' => 3358, 'pointsCharged' => 0, 'remainingPoints' => 0]));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertTrue($result['provider_accepted']);
        $this->assertFalse($result['delivery_confirmed']);
        $this->assertSame('delivered', $result['provider_status_name']);
        $this->assertSame(0, $result['provider_points_charged']);
        $this->assertSame(0, $result['provider_remaining_points']);
    }

    public function test_wallet_success_indicator_does_not_use_send_acceptance_semantics(): void
    {
        Http::fake(fn () => Http::response(['success' => true]));
        $result = (new SmsService())->whatsappWalletStatus();
        $this->assertFalse($result['success']);
        $this->assertNull($result['provider_accepted']);
        $this->assertFalse($result['delivery_confirmed']);
        $this->assertSame('whatsapp_wallet_invalid_response', $result['error']);
    }

    public static function httpFailures(): array
    {
        return array_map(fn ($status) => [$status], [301, 401, 403, 402, 422, 429, 500, 503]);
    }

    /** @dataProvider invalidBodies */
    public function test_invalid_json_is_not_success(string $body): void
    {
        Http::fake(fn () => Http::response($body));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame('whatsapp_invalid_response', $result['error']);
        $this->assertFalse($result['success']);
        $this->assertTrue($result['transport_success']);
    }

    public static function invalidBodies(): array
    {
        return array_map(fn ($body) => [$body], ['', '<html>gateway error</html>', '{']);
    }

    /** @dataProvider unknownJsonShapes */
    public function test_valid_json_shape_does_not_imply_acceptance(string $body): void
    {
        Http::fake(fn () => Http::response($body));
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame('whatsapp_acceptance_unverified', $result['error']);
        $this->assertFalse($result['success']);
        $this->assertTrue($result['transport_success']);
        $this->assertNull($result['provider_accepted']);
    }

    public static function unknownJsonShapes(): array
    {
        return array_map(fn ($body) => [$body], ['null', 'true', '[]', '"ok"']);
    }

    /** @dataProvider invalidConfigurations */
    public function test_missing_or_invalid_configuration_never_sends(array $configuration): void
    {
        config($configuration);
        Http::fake();
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame('whatsapp_sender_not_configured', $result['error']);
        Http::assertNothingSent();
    }

    public static function invalidConfigurations(): array
    {
        return [
            [['services.whatsapp_sender.base_url' => null]],
            [['services.whatsapp_sender.api_token' => null]],
            [['services.whatsapp_sender.api_token' => '  ']],
            [['services.whatsapp_sender.api_token' => "token\r\nInjected: value"]],
            [['services.whatsapp_sender.base_url' => 'not a URL']],
            [['services.whatsapp_sender.base_url' => 'http://sender.example.test']],
            [['services.whatsapp_sender.base_url' => 'https://user:secret@sender.example.test']],
            [['services.whatsapp_sender.base_url' => 'https://sender.example.test?api_token=secret']],
            [['services.whatsapp_sender.base_url' => 'https://sender.example.test/#fragment']],
            [['services.whatsapp_sender.timeout' => 0]],
            [['services.whatsapp_sender.timeout' => 'invalid']],
        ];
    }

    /** @dataProvider connectionFailures */
    public function test_exceptions_do_not_leak_details_or_retry(\Throwable $exception, string $error): void
    {
        $attempts = 0;
        Log::spy();
        Http::fake(function () use ($exception, &$attempts) {
            $attempts++;
            throw $exception;
        });
        $result = (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame($error, $result['error']);
        $this->assertFalse($result['transport_success']);
        $this->assertSame(1, $attempts);
        $this->assertStringNotContainsString('test-secret', json_encode($result));
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');
    }

    public static function connectionFailures(): array
    {
        return [
            [new ConnectionException('cURL error 28: timeout test-secret +966512345678 1234'), 'whatsapp_connection_failed'],
            [new \RuntimeException('test-secret +966512345678 1234'), 'whatsapp_request_failed'],
        ];
    }

    public function test_invalid_phone_and_expired_otp_send_nothing(): void
    {
        Http::fake();
        $service = new SmsService();
        $this->assertSame('invalid_phone', $service->sendWhatsappOtp('+96612345678', '1234')['error']);
        $this->assertSame('otp_expired', $service->sendWhatsappOtp('+966512345678', '1234', now())['error']);
        Http::assertNothingSent();
    }

    public function test_arabic_message_uses_the_actual_expiry(): void
    {
        app()->setLocale('ar');
        Http::fake(fn () => Http::response('{}'));
        $expiry = now()->addMinutes(10);
        (new SmsService())->sendWhatsappOtp('+966512345678', '0123', $expiry);
        Http::assertSent(fn ($request) => str_contains($request['message'], 'مزادات')
            && str_contains($request['message'], '*0123*')
            && str_contains($request['message'], $expiry->copy()->utc()->format('Y-m-d H:i:s').' UTC'));
    }

    public function test_wallet_normalizes_the_confirmed_response_without_logging_or_raw_data(): void
    {
        Log::spy();
        Http::fake(fn () => Http::response(self::confirmedWalletResponse()));
        $result = (new SmsService())->whatsappWalletStatus();
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://sender.example.test/api/external/wallet'
            && $request->hasHeader('X-API-Token', 'test-secret')
            && $request->hasHeader('Accept', 'application/json') && $request->body() === '');
        $this->assertSame([
            'transport_success' => true, 'status' => 200, 'success' => true,
            'provider_accepted' => null, 'delivery_confirmed' => false,
            'provider_user_id' => 1, 'wallet_points' => 151413, 'remaining_points' => 151413,
            'daily_limit' => 1000, 'sent_today' => 0, 'remaining_daily_limit' => 1000,
        ], $result);
        Log::shouldNotHaveReceived('info');
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');
        Http::assertSentCount(1);
    }

    private static function confirmedWalletResponse(): array
    {
        return ['success' => true, 'wallet' => ['userId' => 1, 'walletPoints' => 151413],
            'walletPoints' => 151413, 'remainingPoints' => 151413, 'dailyLimit' => 1000,
            'sentToday' => 0, 'remainingDailyLimit' => 1000];
    }

    /** @dataProvider invalidWalletResponses */
    public function test_wallet_rejects_missing_or_invalid_confirmed_fields(array $body, string $error): void
    {
        Http::fake(fn () => Http::response($body));
        $result = (new SmsService())->whatsappWalletStatus();
        $this->assertFalse($result['success']);
        $this->assertTrue($result['transport_success']);
        $this->assertNull($result['provider_accepted']);
        $this->assertSame($error, $result['error']);
        $this->assertArrayNotHasKey('data', $result);
        $this->assertArrayNotHasKey('provider_user_id', $result);
        $this->assertArrayNotHasKey('wallet_points', $result);
    }

    public static function invalidWalletResponses(): array
    {
        $cases = [];
        foreach (['wallet', 'wallet.userId', 'wallet.walletPoints', 'walletPoints', 'remainingPoints',
            'dailyLimit', 'sentToday', 'remainingDailyLimit'] as $field) {
            $body = self::confirmedWalletResponse();
            \Illuminate\Support\Arr::forget($body, $field);
            $cases['missing '.$field] = [$body, 'whatsapp_wallet_invalid_response'];
            foreach ([null, '1', true, -1, 1.5, []] as $index => $invalid) {
                $body = self::confirmedWalletResponse();
                \Illuminate\Support\Arr::set($body, $field, $invalid);
                $cases['invalid '.$field.' '.$index] = [$body, 'whatsapp_wallet_invalid_response'];
            }
        }
        foreach ([null, 'true', 1, [], false] as $index => $indicator) {
            $body = self::confirmedWalletResponse();
            $body['success'] = $indicator;
            $cases['success indicator '.$index] = [$body, $indicator === false
                ? 'whatsapp_provider_rejected' : 'whatsapp_wallet_schema_unverified'];
        }
        $body = self::confirmedWalletResponse();
        unset($body['success']);
        $cases['missing success'] = [$body, 'whatsapp_wallet_schema_unverified'];
        $body = self::confirmedWalletResponse();
        $body['wallet']['userId'] = 0;
        $cases['zero user id'] = [$body, 'whatsapp_wallet_invalid_response'];
        $body = self::confirmedWalletResponse();
        $body['wallet']['walletPoints'] = 7;
        $cases['conflicting walletPoints copies'] = [$body, 'whatsapp_wallet_invalid_response'];
        return $cases;
    }

    public function test_wallet_preserves_independent_point_counts_and_ignores_unconfirmed_fields(): void
    {
        $body = self::confirmedWalletResponse();
        $body['remainingPoints'] = 2;
        $body['dailyLimit'] = $body['sentToday'] = $body['remainingDailyLimit'] = 0;
        $body['unconfirmed_private_field'] = 'SECRET';
        Http::fake(fn () => Http::response($body, 202));
        $result = (new SmsService())->whatsappWalletStatus();
        $this->assertTrue($result['success']);
        $this->assertSame(151413, $result['wallet_points']);
        $this->assertSame(2, $result['remaining_points']);
        $this->assertSame(0, $result['daily_limit']);
        $this->assertSame(0, $result['sent_today']);
        $this->assertSame(0, $result['remaining_daily_limit']);
        $this->assertStringNotContainsString('SECRET', json_encode($result));
    }

    public function test_wallet_and_send_balances_remain_an_unresolved_verification_discrepancy(): void
    {
        // Confirmed observations differ (1419 vs 151413); no cause or reconciliation is assumed.
        Http::fakeSequence()->push(['success' => true, 'messageId' => 3358, 'status' => 'pending',
            'pointsCharged' => 1, 'remainingPoints' => 1419])->push(self::confirmedWalletResponse());
        $service = new SmsService();
        $send = $service->sendWhatsappOtp('+966512345678', '1234');
        $wallet = $service->whatsappWalletStatus();
        $this->assertSame(1419, $send['provider_remaining_points']);
        $this->assertSame(151413, $wallet['remaining_points']);
        $this->assertNotSame($send['provider_remaining_points'], $wallet['remaining_points']);
    }

    /** @dataProvider httpFailures */
    public function test_wallet_requires_http_success_even_with_a_valid_body(int $status): void
    {
        Http::fake(fn () => Http::response(self::confirmedWalletResponse(), $status));
        $result = (new SmsService())->whatsappWalletStatus();
        $this->assertFalse($result['success']);
        $this->assertFalse($result['transport_success']);
        $this->assertSame('whatsapp_http_failure', $result['error']);
        $this->assertArrayNotHasKey('wallet_points', $result);
        Http::assertSentCount(1);
    }

    public function test_wallet_failure_and_malformed_response_are_sanitized(): void
    {
        Http::fakeSequence()->push(['secret' => 'test-secret'], 401)->push('bad json', 200);
        $service = new SmsService();
        $this->assertSame('whatsapp_http_failure', $service->whatsappWalletStatus()['error']);
        $this->assertSame('whatsapp_invalid_response', $service->whatsappWalletStatus()['error']);
    }

    public function test_short_request_timeout_also_bounds_connection_timeout(): void
    {
        config(['services.whatsapp_sender.timeout' => 3]);
        $options = null;
        Http::fake(function ($request, $requestOptions) use (&$options) {
            $options = $requestOptions;
            return Http::response('{}');
        });
        (new SmsService())->sendWhatsappOtp('+966512345678', '1234');
        $this->assertSame(3, $options['timeout']);
        $this->assertSame(3, $options['connect_timeout']);
    }

    public function test_wallet_connection_failure_and_missing_configuration(): void
    {
        Http::fake(fn () => throw new ConnectionException('SECRET'));
        $this->assertSame('whatsapp_connection_failed', (new SmsService())->whatsappWalletStatus()['error']);
        config(['services.whatsapp_sender.api_token' => null]);
        $this->assertSame('whatsapp_sender_not_configured', (new SmsService())->whatsappWalletStatus()['error']);
    }

    public function test_wallet_has_no_public_route(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $this->assertStringNotContainsString('whatsappWalletStatus', $route->getActionName());
            $this->assertStringNotContainsString('api/external/wallet', $route->uri());
        }
    }
}

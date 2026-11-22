<?php

namespace Tests\Unit\Services;

use App\Services\OtpService;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    /** @dataProvider eligibility */
    public function test_environment_and_allowlist_policy($environment, $phones, bool $fixed): void
    {
        app()->instance('env', $environment);
        config(['services.otp.qa_phones' => $phones,
            'services.otp.testing' => true, 'services.otp.password_recovery_testing' => true,
            'services.otp.test_code' => '9999']);
        $service = app(OtpService::class);
        $this->assertSame($fixed, $service->usesFixedCode('+966512345678'));
        $code = $service->generate('00966512345678');
        $this->assertMatchesRegularExpression('/^[1-9][0-9]{3}$/D', $code);
        $this->assertSame($fixed, $code === '1111');
        $this->assertSame($fixed, $service->matches('0512345678', '1111', '1111'));
        $this->assertTrue($service->matches('0512345678', $code, $code));
        $this->assertFalse($service->matches('0512345678', null, '1111'));
        $this->assertFalse($service->matches('0512345678', '1234', ['1234']));
        $this->assertFalse($service->matches('0512345678', '1234', '9999'));
    }

    public static function eligibility(): array
    {
        return [
            ['local', [], true], ['development', [], true], ['testing', [], true],
            ['production', ['0512345678'], true], ['production', ['00966512345678'], true],
            ['production', ['+966 (51) 234-5678'], true],
            ['production', [], false], ['production', null, false],
            ['production', ['+966599999999'], false], ['production', '*', false],
            ['production', ['0512345678', '*'], false], ['production', [true], false],
            ['production', ['phone' => '0512345678'], false], ['staging', ['0512345678'], false],
        ];
    }

    public function test_challenge_has_ten_minute_expiry_and_rejects_boundary_and_malformed_expiry(): void
    {
        $this->travelTo(now()->startOfSecond());
        $service = app(OtpService::class);
        $challenge = $service->challenge('0512345678');
        $this->assertSame('1111', $challenge['otp']);
        $this->assertTrue($challenge['expire_at']->equalTo(now()->addMinutes(10)));
        $this->assertFalse($service->expired($challenge['expire_at']));
        $this->assertTrue($service->expired(now()));
        $this->assertTrue($service->expired(null));
        $this->assertTrue($service->expired('not-a-date'));
        $this->travelBack();
    }
}

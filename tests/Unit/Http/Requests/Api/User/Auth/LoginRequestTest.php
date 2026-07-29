<?php

namespace Tests\Unit\Http\Requests\Api\User\Auth;

use App\Http\Requests\api\User\Auth\LoginRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_authorize_returns_true()
    {
        $this->assertTrue((new LoginRequest())->authorize());
    }

    public function test_rules_requires_phone_password_and_user_type()
    {
        $rules = (new LoginRequest())->rules();

        $this->assertSame('required', $rules['phone']);
        $this->assertSame('required', $rules['password']);
        $this->assertSame('required|in:buyer,vendor,buyer_vendor,seller', $rules['user_type']);
    }

    public function test_messages_contains_user_type_entries()
    {
        $messages = (new LoginRequest())->messages();

        $this->assertArrayHasKey('user_type.required', $messages);
        $this->assertArrayHasKey('user_type.in', $messages);
    }

    public function test_failed_validation_throws_json_response_exception()
    {
        $request = new LoginRequest();
        $validator = Validator::make([], $request->rules(), $request->messages());
        $validator->fails();

        try {
            $request->failedValidation($validator);
            $this->fail('Expected HttpResponseException was not thrown.');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true);

            $this->assertFalse($data['success']);
            $this->assertSame(422, $data['code']);
            $this->assertNotEmpty($data['message']);
        }
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new LoginRequest();
        $validator = Validator::make([
            'phone' => '0123456789',
            'password' => 'secret123',
            'user_type' => 'buyer',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_user_type()
    {
        $request = new LoginRequest();
        $validator = Validator::make([
            'phone' => '0123456789',
            'password' => 'secret123',
            'user_type' => 'not_a_real_type',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
    }
}

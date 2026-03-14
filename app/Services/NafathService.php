<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;

class NafathService
{
    /** Service key for Login (Verification via Biometrics, 60 sec, no verification) */
    public const SERVICE_LOGIN = 'Login';

    private string $APP_ID;
    private string $APP_KEY;
    private string $SERVER_URL;

    private string $request_method;
    private string $request_url;
    private array $request_query = [];
    private array $request_body = [];

    private ?array $successMsg = null;
    private ?string $errorMsg = null;

    public function __construct()
    {
        $this->APP_ID = (string) config('services.nafath.APP_ID');
        $this->APP_KEY = (string) config('services.nafath.APP_KEY');
        $this->SERVER_URL = (string) config('services.nafath.SERVER_URL');
    }


    public function connect(): array
    {
        try {
            $http = new Client();
            
            $response = $http->request(
                $this->request_method,
                $this->request_url,
                $this->requestData()
            );

            $body = json_decode($response->getBody()->__toString(), true);
            $this->successMsg = $body;

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'data' => $body,
                'error' => null,
            ];
        } catch (ClientException $e){
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 400;
            $this->errorMsg = $e->getResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();
        } catch (ServerException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            $this->errorMsg = $e->getMessage();
        } catch (\Throwable $e) {
            $statusCode = 500;
            $this->errorMsg = $e->getMessage();
        }

        return [
            'success' => false,
            'status_code' => $statusCode ?? 500,
            'data' => null,
            'error' => $this->errorMsg,
        ];
    }

    private function header(): array
    {
        return [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'APP-ID' => $this->APP_ID,
            'APP-KEY' => $this->APP_KEY,
        ];
    }

    private function requestData(): array
    {
        $options = [
            RequestOptions::HEADERS => $this->header(),
            RequestOptions::QUERY => $this->request_query,
        ];
        if (strtoupper($this->request_method) !== 'GET' && $this->request_body !== []) {
            $options[RequestOptions::JSON] = $this->request_body;
        }
        return $options;
    }

    public function setData(string $method, string $request_url, array $request_query = [], array $request_body = []): void
    {
        $this->request_method = $method;
        $this->request_url = $request_url;
        $this->request_query = $request_query;
        $this->request_body = $request_body;
    }

    public function getErrorMsg(): ?string
    {
        return $this->errorMsg;
    }

    /**
     * Build full Nafath URL from configured base.
     */
    private function buildUrl(string $path): string
    {
        $base = $this->SERVER_URL;
        if (!preg_match('#^https?://#i', $base)) {
            $base = 'https://' . $base;
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Send a Nafath MFA request for the given national ID and service.
     * requestId: unique identifier for the callback (UUID); generated if not provided.
     * local: "ar" or "en" for JWT preferred language.
     */
    public function sendMfaRequest(string $nationalId,string $service = self::SERVICE_LOGIN,string $locale = 'ar'): array
        {
            $requestId = (string) Str::uuid();

        $this->setData(
            'POST',
            $this->buildUrl('api/v1/mfa/request'),
            [
                'local' => $locale,
                'requestId' => $requestId,
            ],
            [
                'nationalId' => $nationalId,
                'service' => $service,
            ]
        );

        $result = $this->connect();
// dd($result);
        $result['request_id'] = $requestId;

        return $result;
    }

    /**
     * Retrieve request status (polling).
     * Status: WAITING | EXPIRED | REJECTED | COMPLETED.
     */
    public function getRequestStatus(string $nationalId, string $transId, string $random): array
    {
        $this->setData(
            'POST',
            $this->buildUrl('api/v1/mfa/request/status'),
            [],
            [
                'nationalId' => $nationalId,
                'transId' => $transId,
                'random' => $random,
            ]
        );

        return $this->connect();
    }

    /**
     * Retrieve Elm JWK for JWT signature verification.
     */
    public function getJwk(): array
    {
        $this->setData('GET', $this->buildUrl('api/v1/mfa/jwk'), [], []);
        return $this->connect();
    }

    /**
     * Verify Nafath callback JWT and return decoded payload.
     * Payload contains: aud, nbf, transId, iss, exp, iat, status, and user attributes when COMPLETED.
     *
     * @return array{success: bool, payload?: object, error?: string}
     */
    public function verifyCallbackToken(string $token): array
    {
        try {
            $jwkResult = $this->getJwk();
            if (!$jwkResult['success'] || empty($jwkResult['data']['keys'])) {
                return [
                    'success' => false,
                    'error' => $jwkResult['error'] ?? 'Failed to retrieve JWK',
                ];
            }

            $keys = JWK::parseKeySet($jwkResult['data'], 'RS256');
            $decoded = JWT::decode($token, $keys);

            return [
                'success' => true,
                'payload' => $decoded,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

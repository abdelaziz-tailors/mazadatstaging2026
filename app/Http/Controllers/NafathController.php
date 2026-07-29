<?php

namespace App\Http\Controllers;

use App\Models\NafathVerificationRequest;
use App\Models\User\User;
use App\Services\NafathService;
use App\Services\NotificationService;
use App\Helpers\TranslationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NafathController extends Controller
{
    private NafathService $nafathService;

    public function __construct(NafathService $nafathService)
    {
        $this->nafathService = $nafathService;
    }

    /**
     * Send Nafath MFA request.
     * Body: nationalId (required), service (required), local (optional ar|en), requestId (optional UUID).
     */
    public function sendRequest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nationalId' => ['required', 'string'],
            'service' => ['required', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $result = $this->nafathService->sendMfaRequest(
            (string) $data['nationalId'],
            (string) $data['service'],
        );

        if ($result['success']) {
            NafathVerificationRequest::create([
                'request_id' => $result['request_id'],
                'user_id' => $data['user_id'] ?? null,
                'national_id' => $data['nationalId'],
                'status' => 'WAITING',
            ]);

            return response()->json([
                'request_id' => $result['request_id'],
                'transId' => $result['data']['transId'] ?? null,
                'random' => $result['data']['random'] ?? null,
            ], $result['status_code']);
        }

        return response()->json([
            'message' => 'Nafath request failed',
            'error' => $result['error'],
        ], $result['status_code']);
    }

    /**
     * Poll request status (optional; callback is the primary way to get result).
     * Body: nationalId, transId, random (from send request response).
     */
    public function getStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nationalId' => ['required', 'string'],
            'transId' => ['required', 'string'],
            'random' => ['required', 'string'],
        ]);

        $result = $this->nafathService->getRequestStatus(
            $data['nationalId'],
            $data['transId'],
            $data['random']
        );

        if ($result['success']) {
            return response()->json($result['data'], $result['status_code']);
        }

        return response()->json([
            'message' => 'Failed to get status',
            'error' => $result['error'],
        ], $result['status_code']);
    }

    /**
     * Callback endpoint for Nafath/Elm to POST when user accepts or rejects.
     * Body: token (JWT), transId, requestId. Respond with 200 on success.
     */
    public function callback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'transId' => ['required', 'string'],
            'requestId' => ['required', 'string'],
        ]);

        $verify = $this->nafathService->verifyCallbackToken($data['token']);

        if (!$verify['success']) {
            return response()->json([
                'message' => 'Invalid or expired token',
                'error' => $verify['error'],
            ], 400);
        }

        $payload = $verify['payload'];
        $status = $payload->status ?? null; // COMPLETED | REJECTED

        $this->persistVerificationResult($data['requestId'], $status);

        return response()->json([
            'received' => true,
            'requestId' => $data['requestId'],
            'transId' => $data['transId'],
            'status' => $status,
            'payload' => $payload,
        ], 200);
    }

    /**
     * Update the stored request's status and, on the first successful
     * verification for that user, mark them verified and notify them.
     */
    private function persistVerificationResult(string $requestId, ?string $status): void
    {
        $verificationRequest = NafathVerificationRequest::where('request_id', $requestId)->first();

        if (! $verificationRequest) {
            return;
        }

        $verificationRequest->update(['status' => $status]);

        if ($status !== 'COMPLETED' || ! $verificationRequest->user_id) {
            return;
        }

        $user = User::find($verificationRequest->user_id);

        if (! $user || $user->nafath_verified_at) {
            return;
        }

        $user->update(['nafath_verified_at' => now()]);

        NotificationService::notify(
            $user->id,
            'identity_verified',
            TranslationHelper::translate('identity verified title'),
            TranslationHelper::translate('identity verified body')
        );
    }
}

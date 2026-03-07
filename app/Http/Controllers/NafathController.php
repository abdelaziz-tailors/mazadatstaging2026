<?php

namespace App\Http\Controllers;

use App\Services\NafathService;
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
        ]);

        $result = $this->nafathService->sendMfaRequest(
            (string) $data['nationalId'],
            (string) $data['service'],
        );

        if ($result['success']) {
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

        // Optional: persist by requestId, dispatch job, etc.
        return response()->json([
            'received' => true,
            'requestId' => $data['requestId'],
            'transId' => $data['transId'],
            'status' => $status,
            'payload' => $payload,
        ], 200);
    }
}

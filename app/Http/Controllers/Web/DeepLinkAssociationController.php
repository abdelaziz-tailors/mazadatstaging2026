<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DeepLinkAssociationController extends Controller
{
    public function assetLinks(): JsonResponse
    {
        $packageName = (string) config('deep_links.android.package_name');
        $fingerprints = config('deep_links.android.sha256_cert_fingerprints', []);

        if ($packageName === '' || empty($fingerprints)) {
            return response()->json([]);
        }

        return response()->json([
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $packageName,
                    'sha256_cert_fingerprints' => $fingerprints,
                ],
            ],
        ]);
    }

    public function appleAppSiteAssociation(): JsonResponse
    {
        $teamId = (string) config('deep_links.ios.team_id');
        $bundleIds = config('deep_links.ios.bundle_ids', []);
        $paths = config('deep_links.applinks.paths', ['/u/*']);

        $appIds = [];
        if ($teamId !== '') {
            foreach ($bundleIds as $bundleId) {
                if ($bundleId !== '') {
                    $appIds[] = $teamId . '.' . $bundleId;
                }
            }
        }

        $details = [];
        foreach ($appIds as $appId) {
            $details[] = [
                'appID' => $appId,
                'paths' => $paths,
            ];
        }

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => $details,
            ],
        ]);
    }
}

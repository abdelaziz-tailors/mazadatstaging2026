<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DeepLinkAssociationController extends Controller
{
    public function assetLinks(): JsonResponse
    {
        $apps = array_filter((array) config('deep_links.android.apps', []), 'is_array');
        $apps[] = [
            'package_name' => config('deep_links.android.package_name'),
            'sha256_cert_fingerprints' => config('deep_links.android.sha256_cert_fingerprints', []),
        ];
        $statements = config('deep_links.legacy_android', []);
        foreach ($apps as $app) {
            $package = $app['package_name'] ?? '';
            $fingerprints = array_values(array_unique(array_filter(
                (array) ($app['sha256_cert_fingerprints'] ?? []),
                static function ($fingerprint) {
                    return is_string($fingerprint)
                        && preg_match('/^(?:[A-Fa-f0-9]{2}:){31}[A-Fa-f0-9]{2}$/D', $fingerprint);
                }
            )));
            if (!is_string($package) || $package === '' || !$fingerprints) {
                continue;
            }
            // Merge certificates for the same app without dropping legacy signing keys.
            $merged = false;
            foreach ($statements as &$statement) {
                if (($statement['target']['package_name'] ?? null) === $package
                    && ($statement['relation'] ?? []) === ['delegate_permission/common.handle_all_urls']) {
                    $statement['target']['sha256_cert_fingerprints'] = array_values(array_unique(array_merge(
                        $statement['target']['sha256_cert_fingerprints'], $fingerprints
                    )));
                    $merged = true;
                    break;
                }
            }
            unset($statement);
            if (!$merged) {
                $statements[] = [
                    'relation' => ['delegate_permission/common.handle_all_urls'],
                    'target' => [
                        'namespace' => 'android_app', 'package_name' => $package,
                        'sha256_cert_fingerprints' => $fingerprints,
                    ],
                ];
            }
        }

        return response()->json(array_values($statements))
            ->header('Cache-Control', 'no-store')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');
    }

    public function appleAppSiteAssociation(): JsonResponse
    {
        $details = config('deep_links.legacy_ios', []);
        $apps = array_filter((array) config('deep_links.ios.apps', []), 'is_array');
        $teamId = (string) config('deep_links.ios.team_id');
        // Required share routes remain supported even with an older /u/* env override.
        $paths = array_values(array_unique(array_merge(
            config('deep_links.applinks.paths', []), ['/u/*', '/share/auction/*', '/share/lot/*']
        )));
        if ($teamId !== '') {
            foreach (config('deep_links.ios.bundle_ids', []) as $bundleId) {
                if ($bundleId !== '') {
                    $apps[] = ['appID' => $teamId . '.' . $bundleId, 'paths' => $paths];
                }
            }
        }
        foreach ($apps as $app) {
            if (empty($app['appID']) || !is_string($app['appID']) || empty($app['paths']) || !is_array($app['paths'])) {
                continue;
            }
            $merged = false;
            foreach ($details as &$detail) {
                if ($detail['appID'] === $app['appID']) {
                    $detail['paths'] = array_values(array_unique(array_merge($detail['paths'], $app['paths'])));
                    $merged = true;
                    break;
                }
            }
            unset($detail);
            if (!$merged) {
                $details[] = ['appID' => $app['appID'], 'paths' => array_values(array_unique($app['paths']))];
            }
        }

        return response()->json(['applinks' => ['apps' => [], 'details' => array_values($details)]])
            ->header('Cache-Control', 'no-store')
            ->header('X-LiteSpeed-Cache-Control', 'no-cache');
    }
}

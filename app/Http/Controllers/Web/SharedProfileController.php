<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\Request;

class SharedProfileController extends Controller
{
    public function show(Request $request, int $id)
    {
        $user = User::query()
            ->select(['id', 'name', 'user_name', 'image'])
            ->where('id', $id)
            ->firstOrFail();

        $profileUrl = $this->buildShareUrl($user->id);
        $appDeepLink = $this->buildAppDeepLink($user->id);
        $fallbackUrl = $this->buildFallbackUrl($request, $user->id);

        return view('front.pages.shared-profile', [
            'user' => $user,
            'profileUrl' => $profileUrl,
            'appDeepLink' => $appDeepLink,
            'fallbackUrl' => $fallbackUrl,
            'shouldAutoRedirect' => $fallbackUrl !== null,
        ]);
    }

    private function buildAppDeepLink(int $userId): string
    {
        $scheme = rtrim((string) config('deep_links.app_scheme', 'mazadat://'), '/');

        return $scheme . '/u/' . $userId;
    }

    private function buildShareUrl(int $userId): string
    {
        $baseUrl = rtrim((string) config('deep_links.base_url', config('app.url')), '/');

        return $baseUrl . '/u/' . $userId;
    }

    private function buildFallbackUrl(Request $request, int $userId): ?string
    {
        $userAgent = strtolower((string) $request->userAgent());

        $iosStoreUrl = (string) config('deep_links.ios_store_url');
        $androidStoreUrl = (string) config('deep_links.android_store_url');
        $webFallbackTemplate = (string) config('deep_links.fallback_profile_url');

        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return $iosStoreUrl !== '' ? $iosStoreUrl : null;
        }

        if (str_contains($userAgent, 'android')) {
            if ($androidStoreUrl !== '') {
                return $androidStoreUrl;
            }

            $androidPackageName = (string) config('deep_links.android.package_name');
            if ($androidPackageName !== '') {
                return 'https://play.google.com/store/apps/details?id=' . $androidPackageName;
            }

            return null;
        }

        if ($webFallbackTemplate !== '') {
            return str_replace('{id}', (string) $userId, $webFallbackTemplate);
        }

        return null;
    }
}

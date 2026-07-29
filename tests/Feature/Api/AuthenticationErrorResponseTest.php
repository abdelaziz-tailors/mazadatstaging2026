<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

/**
 * Regression guard for a real bug: any unauthenticated request to an
 * "auth:api" route that Laravel doesn't classify as "expectsJson()" (no
 * explicit Accept: application/json header — true of Postman by default, a
 * browser tab opened directly on a download link, or many non-browser HTTP
 * clients) used to 500 instead of getting a clean 401.
 *
 * Root cause: App\Http\Middleware\Authenticate::redirectTo() called
 * route('login') for any non-JSON-expecting request, but this app has no
 * "login" named route (API-only; the admin dashboard uses its own separate
 * AuthAdmin middleware, not this guard) — so it threw
 * Symfony\Component\Routing\Exception\RouteNotFoundException, which isn't an
 * AuthenticationException, so it bypassed the app's own JSON-401 handler
 * entirely and surfaced as a raw 500 Server Error.
 *
 * These deliberately use the plain get()/post() test client (not
 * getJson()/postJson(), which implicitly set Accept: application/json and
 * would have masked this bug completely).
 */
class AuthenticationErrorResponseTest extends TestCase
{
    public function test_unauthenticated_request_without_an_explicit_json_accept_header_gets_a_clean_401_not_a_500()
    {
        $response = $this->get('/api/user/auction/seller-invoice-list', [
            'x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'ar',
            'Accept' => '*/*',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false, 'code' => 401]);
    }

    /**
     * user-invoice/{id}/pdf (the buyer invoice) is still behind auth:api —
     * unlike seller-invoice/{id}/pdf, which was moved to a public signed URL
     * specifically so it can be opened directly without a token (see
     * SellerInvoicePdfControllerTest). This still needs the original fix.
     */
    public function test_a_pdf_download_link_opened_directly_without_a_token_gets_a_clean_401_not_a_500()
    {
        $response = $this->get('/api/user/auction/user-invoice/1/pdf', [
            'x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'ar',
            'Accept' => 'text/html,application/xhtml+xml',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false, 'code' => 401]);
    }

    public function test_unauthenticated_request_that_does_expect_json_still_gets_401_as_before()
    {
        $response = $this->getJson('/api/user/auction/seller-invoice-list', [
            'x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM',
            'Accept-Language' => 'ar',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false, 'code' => 401]);
    }
}

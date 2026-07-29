<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: on small screens the logo box in the header floated
 * full-width and sat awkwardly next to the mobile menu button — hidden here
 * via theme.css, scoped to the same <=991px breakpoint used elsewhere for
 * mobile-only layout. Large screens keep the logo untouched.
 */
class MobileHeaderCssTest extends TestCase
{
    public function test_theme_css_hides_the_header_logo_box_on_small_screens_only()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));

        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression(
            '/@media \(max-width:\s*991px\)\s*\{\s*\.header \.header-left\s*\{[^}]*display:\s*none/s',
            $css,
            'the header logo box should be hidden inside a max-width:991px block, not unconditionally'
        );

        // must not be hidden outside a media query (would also hide it on desktop)
        $unconditional = preg_replace('/@media[^{]*\{.*?\}\s*\}/s', '', $css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.header \.header-left\s*\{[^}]*display:\s*none/s',
            $unconditional,
            'hiding the logo must stay scoped to the mobile breakpoint'
        );
    }
}

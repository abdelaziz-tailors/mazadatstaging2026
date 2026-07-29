<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard for a small-screen-only bug: script.js toggles the
 * ".slide-nav" class + a dark overlay on the mobile hamburger button, but
 * rtl.css hides the sidebar off-canvas at <=991px via margin-right:-225px
 * (an RTL-specific offset). custom.css's ".slide-nav .sidebar" open rule
 * only reset margin-left (an LTR leftover from the original template), so
 * the sidebar never actually slid into view in RTL — only the dark overlay
 * appeared. Large screens were never affected (they don't use this
 * off-canvas mechanism at all).
 */
class MobileSidebarCssTest extends TestCase
{
    public function test_rtl_css_resets_margin_right_when_the_mobile_sidebar_is_open()
    {
        $css = file_get_contents(public_path('dashboard/css/rtl.css'));

        $this->assertNotFalse($css, 'rtl.css should exist');

        $mobileBlockStart = strpos($css, '@media (max-width: 991px)');
        $this->assertNotFalse($mobileBlockStart, 'the mobile breakpoint block should exist in rtl.css');

        $mobileBlock = substr($css, $mobileBlockStart);

        $this->assertMatchesRegularExpression(
            '/\.slide-nav\s+\.sidebar\s*\{[^}]*margin-right:\s*0/s',
            $mobileBlock,
            'opening the mobile sidebar must reset margin-right back to 0, or it stays off-canvas behind the overlay'
        );
    }
}

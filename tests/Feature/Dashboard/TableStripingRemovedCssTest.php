<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: every dashboard list table shares the same Bootstrap
 * ".table-striped" class (39 pages), and theme.css centralized the actual
 * zebra coloring in one rule targeting ":nth-of-type(odd)". Odd rows used to
 * get a light-gray background, which read as inconsistent once the round
 * icon action buttons (with their own light-tinted circular backgrounds)
 * were introduced. Neutralizing that one rule removes striping everywhere
 * at once, without touching any of the 39 individual Blade files.
 */
class TableStripingRemovedCssTest extends TestCase
{
    public function test_striped_odd_rows_use_the_same_white_surface_as_even_rows()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression(
            '/\.table-striped\s*>\s*tbody\s*>\s*tr:nth-of-type\(odd\)\s*>\s*\*\s*\{[^}]*background-color:\s*var\(--md-surface\)\s*!important/s',
            $css,
            'odd rows should use the plain white surface color, not the gray "surface-2" tint'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\.table-striped\s*>\s*tbody\s*>\s*tr:nth-of-type\(odd\)\s*>\s*\*\s*\{[^}]*background-color:\s*var\(--md-surface-2\)/s',
            $css
        );
    }
}

<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: datatables.min.css hardcodes the search input via
 * "div.dataTables_wrapper div.dataTables_filter input{width:auto}" — that
 * div-prefixed selector has higher specificity (0,2,3) than a plain
 * ".dataTables_wrapper .dataTables_filter input" rule (0,2,1), so it beats
 * any width set with the plain selector regardless of stylesheet load
 * order. The override must match the vendor selector's div-prefixed shape
 * (plus !important as a safety margin) to actually take effect, on both
 * the shared 320px rule and the wider ".md-wide-search" opt-in (400px,
 * narrowed down from an original 520px per explicit request — narrow
 * screens are unaffected, handled by a separate <=991px rule).
 */
class SearchBoxWidthCssTest extends TestCase
{
    public function test_search_input_gets_a_wider_explicit_width_on_large_screens()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $start = strpos($css, '@media (min-width: 992px)');
        $this->assertNotFalse($start, 'a >=992px breakpoint block should exist');

        $block = substr($css, $start, 400);
        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter input\s*\{[^}]*width:\s*320px\s*!important/s',
            $block
        );
    }

    public function test_wide_search_opt_in_class_widens_the_box_further_and_beats_vendor_specificity()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));

        $start = strpos($css, '.md-wide-search');
        $this->assertNotFalse($start, 'the .md-wide-search opt-in rule should exist');

        $block = substr($css, $start, 400);
        $this->assertMatchesRegularExpression(
            '/\.md-wide-search div\.dataTables_wrapper div\.dataTables_filter input\s*\{[^}]*width:\s*400px\s*!important/s',
            $block
        );
    }
}

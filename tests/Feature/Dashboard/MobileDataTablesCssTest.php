<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: every dashboard list page shares the same DataTables
 * "dom" layout (34 pages use the exact `<"d-flex justify-content-between"<l><f>>`
 * pattern) — the page-length dropdown and search box sit in one unwrapped
 * flex row with no wrap, so on screens narrower than desktop (<=991px) they
 * collide and the search box gets clipped against the card edge with no
 * margin. Fixed once, here, globally, instead of per-page. Large screens
 * keep both controls untouched.
 *
 * The breakpoint was widened from <=575px to <=991px after a real gap was
 * found: between 576-991px neither this mobile fix nor the >=992px
 * ".md-wide-search" width rule applied, so the search box silently fell
 * back to datatables.min.css's tiny "width:auto" default there. The
 * selectors were also changed to the div-prefixed shape
 * ("div.dataTables_wrapper div.dataTables_filter input") plus !important —
 * matching datatables.min.css's own higher-specificity selector — for the
 * same reason documented on the >=992px ".md-wide-search" rule.
 *
 * A second, deeper issue was found via a real headless-browser measurement
 * (not just reading the CSS): the dt-bootstrap4 adapter wraps
 * ".dataTables_filter" in its own extra, unclassed <div> as a flex item of
 * our custom "<f>" toolbar row. That wrapper never gets a width of its own
 * (shrink-to-fit its content), so "width:100%" on ".dataTables_filter"
 * resolved against that shrink-wrapped ~186px box instead of the real row
 * width — every percentage-based fix above was correct on paper but still
 * rendered small. Confirmed via a real Chrome DevTools Protocol
 * measurement: .dataTables_filter's own getBoundingClientRect() width
 * matched #filter_username's width (~292px) only after adding
 * "display: contents" to that anonymous wrapper (removing it from the box
 * tree entirely, so .dataTables_filter becomes a direct flex child of the
 * real row).
 */
class MobileDataTablesCssTest extends TestCase
{
    private function mobileBlock(string $css): string
    {
        $start = strrpos($css, '@media (max-width: 991px)');
        $this->assertNotFalse($start, 'the small/medium-screen (<=991px) block should exist in theme.css');

        return substr($css, $start);
    }

    public function test_datatables_length_dropdown_is_hidden_on_small_screens_only()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $mobileBlock = $this->mobileBlock($css);
        $this->assertMatchesRegularExpression(
            '/\.dataTables_wrapper \.dataTables_length\s*\{[^}]*display:\s*none/s',
            $mobileBlock,
            'the length dropdown should be hidden inside the <=991px block'
        );

        $unconditional = preg_replace('/@media[^{]*\{.*?\}\s*\}/s', '', $css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.dataTables_wrapper \.dataTables_length\s*\{[^}]*display:\s*none/s',
            $unconditional,
            'hiding the length dropdown must stay scoped to the mobile breakpoint, not apply on desktop'
        );
    }

    public function test_datatables_filter_gets_full_width_and_margin_on_small_screens()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $mobileBlock = $this->mobileBlock($css);

        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter\s*\{[^}]*width:\s*100%\s*!important/s',
            $mobileBlock,
            'the search box should be widened to fill the freed-up row on mobile/tablet'
        );
        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter\s*\{[^}]*margin:/s',
            $mobileBlock,
            'the search box needs its own margin so it does not hug the card edge'
        );
    }

    /**
     * The search <input> itself must also be forced to 100%, matching the
     * width of the other filter fields above it (username/email/etc) — not
     * just its ".dataTables_filter" wrapper, since datatables.min.css's own
     * higher-specificity selector otherwise still wins on the input itself.
     */
    public function test_datatables_filter_input_is_forced_to_full_width_on_small_screens()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $mobileBlock = $this->mobileBlock($css);

        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter input\s*\{[^}]*width:\s*100%\s*!important/s',
            $mobileBlock
        );
    }

    /**
     * DataTables renders "<label>{search label text}<input></label>" — the
     * label's own text shares the row with the input, so even at 100% width
     * the input ends up narrower than the other, label-less filter fields
     * above it (their <label> sits on its own line, not inline with the
     * input). The label text must be visually collapsed (font-size: 0) and
     * the input's own font-size restored, so the input alone fills the row
     * and actually matches the width of the other fields, not just its own
     * wrapper's width.
     */
    public function test_datatables_filter_label_text_is_collapsed_so_input_gets_the_full_row()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $mobileBlock = $this->mobileBlock($css);

        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter label\s*\{[^}]*font-size:\s*0/s',
            $mobileBlock,
            'the inline search label text must be collapsed so it stops eating into the input\'s width'
        );
        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.dataTables_filter input\s*\{[^}]*font-size:\s*1rem/s',
            $mobileBlock,
            'the input must restore its own font-size after the label collapses it to 0'
        );
    }

    /**
     * The dt-bootstrap4 adapter's anonymous, unclassed wrapper <div> around
     * ".dataTables_filter" must be removed from the box tree (display:
     * contents) — otherwise ".dataTables_filter"'s own 100% width resolves
     * against that shrink-wrapped div instead of the real toolbar row. See
     * the class docblock for how this was actually confirmed (a real
     * headless-browser width measurement, not just reading the CSS).
     */
    public function test_the_anonymous_dt_bootstrap4_filter_wrapper_is_removed_from_the_box_tree()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $mobileBlock = $this->mobileBlock($css);

        $this->assertMatchesRegularExpression(
            '/div\.dataTables_wrapper div\.d-flex > div:has\(> div\.dataTables_filter\)\s*\{[^}]*display:\s*contents/s',
            $mobileBlock
        );
    }

    /**
     * The bottom info/pagination row ("dataTables_info"/"dataTables_paginate")
     * must stay exactly as it was — the user explicitly asked for pagination
     * to be left untouched.
     */
    public function test_pagination_and_info_controls_are_not_touched_by_the_mobile_fix()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $mobileBlock = $this->mobileBlock($css);

        $this->assertDoesNotMatchRegularExpression('/\.dataTables_paginate\s*\{/', $mobileBlock);
        $this->assertDoesNotMatchRegularExpression('/\.dataTables_info\s*\{/', $mobileBlock);
    }
}

<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: ".form-control { height: 40px; }" in custom.css was
 * written for single-line inputs, but it also matches every <textarea
 * class="form-control">, clamping them all to a 2-line box no matter what
 * "rows" attribute the Blade view sets (e.g. the About page's description
 * field, meant to be rows="6"). "textarea.form-control" has higher
 * specificity and must override that fixed height with an auto height plus
 * a real minimum, or any "rows" fix on the Blade side is silently undone.
 */
class TextareaHeightCssTest extends TestCase
{
    public function test_textarea_form_control_overrides_the_fixed_input_height()
    {
        $css = file_get_contents(public_path('dashboard/css/custom.css'));
        $this->assertNotFalse($css, 'custom.css should exist');

        $this->assertMatchesRegularExpression(
            '/textarea\.form-control\s*\{[^}]*height:\s*auto/s',
            $css,
            'textarea.form-control should reset the fixed 40px height back to auto'
        );
        $this->assertMatchesRegularExpression(
            '/textarea\.form-control\s*\{[^}]*min-height:\s*\d/s',
            $css,
            'textarea.form-control should still guarantee a real minimum height'
        );
    }

    public function test_generic_form_control_height_rule_is_unchanged_for_inputs()
    {
        $css = file_get_contents(public_path('dashboard/css/custom.css'));

        $this->assertMatchesRegularExpression(
            '/\.form-control\s*\{[^}]*height:\s*40px/s',
            $css,
            'single-line inputs must keep their existing fixed height'
        );
    }
}

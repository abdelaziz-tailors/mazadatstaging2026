<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Regression guard: theme.css's blanket ".dropdown-item{color:...!important}"
 * rule (written for the dark-header dropdown fix) beat Bootstrap's plain
 * ".text-danger" utility class (no !important of its own) on every dropdown
 * across the dashboard — destructive items (deactivate/delete, e.g. on the
 * partners page) rendered in the default text color instead of red, in both
 * the icon and the text, even though "text-danger" was present in the
 * markup. Confirmed via the partners page dropdown appearing black instead
 * of red despite the class being there. Fixed with a matching
 * ".dropdown-item.text-danger" rule plus !important in every state.
 */
class DropdownDangerColorCssTest extends TestCase
{
    public function test_dropdown_item_text_danger_wins_over_the_blanket_dropdown_item_color_rule()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression(
            '/\.dropdown-item\.text-danger\s*,\s*\.dropdown-item\.text-danger:hover\s*,\s*\.dropdown-item\.text-danger:focus\s*\{\s*color:\s*var\(--md-danger\)\s*!important;/s',
            $css
        );
    }

    /**
     * The override must come after the blanket ".dropdown-item" color rule
     * in source order too, as a safety margin alongside !important and the
     * higher specificity of the two-class selector.
     */
    public function test_the_text_danger_override_is_declared_after_the_blanket_dropdown_item_rule()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));

        $blanketPos = strpos($css, '.dropdown-item,');
        $overridePos = strpos($css, '.dropdown-item.text-danger,');

        $this->assertNotFalse($blanketPos);
        $this->assertNotFalse($overridePos);
        $this->assertGreaterThan($blanketPos, $overridePos);
    }
}

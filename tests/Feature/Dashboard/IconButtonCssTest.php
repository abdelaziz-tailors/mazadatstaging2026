<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * ".md-icon-btn" is the round icon-button component used by the Auctions
 * table's row actions (kebab/edit/view). Guards that the base style and its
 * color variants stay defined in theme.css.
 */
class IconButtonCssTest extends TestCase
{
    public function test_icon_button_base_style_and_color_variants_are_defined()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $this->assertMatchesRegularExpression('/\.md-icon-btn\s*\{[^}]*border-radius:\s*50%/s', $css);
        $this->assertStringContainsString('.md-icon-btn.md-icon-btn-info', $css);
        $this->assertStringContainsString('.md-icon-btn.md-icon-btn-success', $css);
        $this->assertStringContainsString('.md-icon-btn.md-icon-btn-danger', $css);
    }

    /**
     * Regression guard: the kebab button's background used to be the
     * light-gray "surface-2" tint, which looked muddy once table rows lost
     * their zebra striping (both were nearly the same gray). It now sits on
     * a plain white surface with a thin border, matching the design
     * reference's "icon inside a white circle" look.
     */
    public function test_icon_button_base_uses_the_white_surface_with_a_border()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));

        $this->assertMatchesRegularExpression(
            '/\.md-icon-btn\s*\{[^}]*background:\s*var\(--md-surface\)\s*;/s',
            $css
        );
        $this->assertMatchesRegularExpression(
            '/\.md-icon-btn\s*\{[^}]*border:\s*1px solid var\(--md-border-soft\)/s',
            $css
        );
    }

    /**
     * Regression guard: the base icon color used to be a lighter muted gray
     * (--md-text-muted). Per explicit request the icons should read as plain
     * dark/black, matching the design reference — the darker --md-text token.
     */
    public function test_icon_button_base_color_is_the_dark_text_token_not_muted_gray()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));

        $this->assertMatchesRegularExpression(
            '/\.md-icon-btn\s*\{[^}]*color:\s*var\(--md-text\)\s*;/s',
            $css
        );
    }
}

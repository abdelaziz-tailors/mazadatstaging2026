<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;

/**
 * Per explicit request: the dashboard home page's row-2 card titles ("إجراءات
 * سريعة" / "أحدث المزادات" / "أحدث المستخدمين") rendered visibly larger than
 * the row-1 chart card titles ("اتجاه المبيعات" / "تسجيلات المستخدمين" /
 * "توزيع المزادات حسب الحالة") — only the chart cards had an explicit
 * ".md-chart-card-header .card-title { font-size: 15px }" override, while
 * plain ".card-header .card-title" cards fell back to Bootstrap's larger
 * default h4 size. Fixed by giving the base ".card-title" rule the same
 * 15px size, so every card title across the dashboard matches.
 */
class CardTitleFontSizeTest extends TestCase
{
    public function test_base_card_title_rule_sets_the_same_font_size_as_the_chart_cards()
    {
        $css = file_get_contents(public_path('dashboard/css/theme.css'));
        $this->assertNotFalse($css, 'theme.css should exist');

        $basePos = strpos($css, '.card-title {');
        $this->assertNotFalse($basePos);
        $baseBlock = substr($css, $basePos, 100);
        $this->assertStringContainsString('font-size: 15px;', $baseBlock);

        $chartHeaderPos = strpos($css, '.md-chart-card-header .card-title {');
        $this->assertNotFalse($chartHeaderPos);
        $chartHeaderBlock = substr($css, $chartHeaderPos, 150);
        $this->assertStringNotContainsString('font-size: 15px;', $chartHeaderBlock, 'the duplicate override should be removed now that the base rule covers it');
    }
}

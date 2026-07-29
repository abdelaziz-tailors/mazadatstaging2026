<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use Tests\TestCase;

/**
 * Regression guard: the Arabic translation for the "actions" key was
 * literally the word for "Settings" (الإعدادات) instead of "Actions"
 * (الإجراءات) — a copy-paste mistake affecting every table across the
 * dashboard that labels its row-actions column with this key (Auctions,
 * Users, Seller Submissions, ...).
 */
class ActionsColumnLabelTranslationTest extends TestCase
{
    public function test_actions_key_translates_to_the_arabic_word_for_actions_not_settings()
    {
        $this->assertEquals('الإجراءات', TranslationHelper::translate('actions', 'ar'));
        $this->assertNotEquals('الإعدادات', TranslationHelper::translate('actions', 'ar'));
    }

    public function test_actions_key_translates_correctly_in_english_too()
    {
        $this->assertEquals('Actions', TranslationHelper::translate('actions', 'en'));
    }
}

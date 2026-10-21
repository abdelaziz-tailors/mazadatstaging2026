<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ArabicValidationTranslationsTest extends TestCase
{
    public function test_arabic_validation_covers_english_rules_and_preserves_placeholders(): void
    {
        $root = dirname(__DIR__, 2);
        $english = require $root.'/resources/lang/en/validation.php';
        $arabic = require $root.'/resources/lang/ar/validation.php';
        unset($english['custom'], $english['attributes']);
        $check = function (array $en, array $ar) use (&$check): void {
            foreach ($en as $key => $value) {
                $this->assertArrayHasKey($key, $ar);
                if (is_array($value)) {
                    $check($value, $ar[$key]);
                    continue;
                }
                $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $ar[$key]);
                $this->assertStringNotContainsString('??', $ar[$key]);
                preg_match_all('/:[a-z_]+/', $value, $placeholders);
                foreach ($placeholders[0] as $placeholder) {
                    $this->assertStringContainsString($placeholder, $ar[$key]);
                }
            }
        };
        $check($english, $arabic);
    }
}

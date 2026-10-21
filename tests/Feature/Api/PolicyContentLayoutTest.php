<?php

namespace Tests\Feature\Api;

use App\Http\Resources\PageResource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PolicyContentLayoutTest extends TestCase
{
    public function test_actual_content_endpoints_preserve_both_languages(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutMiddleware('APISettings');
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description');
            $table->string('image');
        });
        $content = ['en' => "First clause.\n\nLast clause.", 'ar' => "الفقرة الأولى.\n\nالفقرة الأخيرة."];
        foreach ([1 => 'about', 2 => 'terms', 3 => 'privacy'] as $id => $endpoint) {
            DB::table('pages')->insert(['id' => $id, 'name' => json_encode(['en' => $endpoint, 'ar' => $endpoint]),
                'description' => json_encode($content), 'image' => 'pages/policy.jpg']);
            foreach (['en', 'ar'] as $locale) {
                app()->setLocale($locale);
                $this->getJson('/api/'.$endpoint)->assertOk()->assertJsonPath('data.description', $content[$locale]);
            }
        }
    }

    public function test_policy_views_preserve_text_escape_html_and_render_paragraphs(): void
    {
        $description = "First clause.\r\n\r\n\r\nSecond clause.\nContinuation.\n\n<script>alert(1)</script>\n\nآخر فقرة.";
        $page = (object) ['name' => 'Policy', 'description' => $description, 'image' => null];
        foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $direction) {
            app()->setLocale($locale);
            foreach (['about', 'terms', 'privacy'] as $screen) {
                $html = view('front.pages.'.$screen, compact('page'))->render();
                $this->assertStringContainsString('dir="'.$direction.'"', $html);
                $this->assertStringContainsString('<p>First clause.</p>', $html);
                $this->assertStringContainsString('<p>Second clause.<br />', $html);
                $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
                $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
                $this->assertStringContainsString('آخر فقرة.', $html);
            }
        }
        // The mobile API remains lossless and does not silently convert text into HTML.
        $page->image = '';
        $this->assertSame($description, (new PageResource($page))->resolve()['description']);
    }
}

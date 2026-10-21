<?php

namespace Tests\Feature\Dashboard;

use App\Http\Requests\Dashboard\Page\UpdatePageRequest;
use Illuminate\Http\UploadedFile;
use App\Http\Resources\PageResource;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PageImageTest extends TestCase
{
    public function test_page_image_uploads_are_prohibited(): void
    {
        $rules = (new UpdatePageRequest())->rules();
        $data = ['name' => ['ar' => 'Page'], 'description' => ['ar' => 'Content']];

        $this->assertTrue(Validator::make($data, $rules)->passes());
        $data['image_png'] = UploadedFile::fake()->image('page.png');
        $this->assertTrue(Validator::make($data, $rules)->fails());
        $data['image_png'] = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
        $this->assertTrue(Validator::make($data, $rules)->fails());
        $data['image_png'] = UploadedFile::fake()->image('large.jpg')->size(5121);
        $this->assertTrue(Validator::make($data, $rules)->fails());
    }

    public function test_policy_pages_and_api_hide_existing_images(): void
    {
        $page = (object) ['name' => 'Page', 'description' => 'Content', 'image' => 'pages/photo.png'];

        foreach (['about', 'privacy', 'terms'] as $screen) {
            $this->assertStringNotContainsString('<img ', view('front.pages.'.$screen, compact('page'))->render());
        }
        $this->assertNull((new PageResource($page))->resolve()['image']);
    }
}

<?php

namespace Tests\Feature\Bugfixes;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\SellerSubmissionController;
use App\Models\SellerSubmission;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\ViewErrorBag;
use Laravel\Passport\Passport;
use Tests\Support\NotificationTestCase;

/** Uses the existing allow-listed disposable MariaDB harness, never the application DB. */
class OptionalSellerSubmissionPriceTest extends NotificationTestCase
{
    private $uploadPaths = [];
    private $legacySubmission;

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->mysqlConfig) {
            $this->markTestSkipped('Set NOTIFICATIONS_MYSQL_CONFIG to the disposable MariaDB fixture config.');
        }
        if (strpos($this->getName(), 'test_migration_preserves') === 0) {
            $this->legacySubmission = $this->submission($this->user(), $this->user(['user_type' => 'vendor']));
            $this->legacySubmission->update(['expected_price' => '1234.50']);
        }
        (require database_path('migrations/2026_11_18_000000_allow_null_seller_submission_expected_price.php'))->up();
    }

    protected function tearDown(): void
    {
        foreach ($this->uploadPaths as $path) {
            unlink($path);
        }
        parent::tearDown();
    }

    /** @dataProvider prices */
    public function test_creation_retrieval_and_display_preserve_optional_and_explicit_prices(array $price): void
    {
        $seller = $this->user();
        $partner = $this->user(['user_type' => 'vendor']);
        Passport::actingAs($seller);
        // Stub only the filesystem upload: retain actual multipart validation and media records.
        $this->uploadPaths[] = $path = tempnam(sys_get_temp_dir(), 'price');
        $image = \Mockery::mock(UploadedFile::class, [$path, 'sheep.png', 'image/png', null, true])
            ->makePartial();
        $image->shouldReceive('isValid')->andReturn(true);
        $image->shouldReceive('getMimeType')->andReturn('image/png');
        $image->shouldReceive('guessExtension')->andReturn('png');
        $image->shouldReceive('move')->once();
        $response = $this->post('/api/user/seller-submissions', $price + [
            'partner_id' => $partner->id, 'sheep_type' => 'Sheep', 'age' => '2', 'images' => [$image],
        ], ['Accept' => 'application/json'])->assertOk();

        $submission = SellerSubmission::findOrFail($response->json('data.id'));
        $expected = $price['expected_price'] ?? null;
        $expected = $expected === '' ? null : $expected;
        foreach ([$response->json('data.expected_price'), $submission->expected_price] as $actual) {
            $expected === null ? $this->assertNull($actual) : $this->assertSame((float) $expected, (float) $actual);
        }
        $detail = $this->getJson('/api/user/seller-submissions/'.$submission->id)->assertOk();
        if ($expected === null) {
            $detail->assertJsonPath('data.expected_price', null);
        } else {
            $this->assertSame((float) $expected, (float) $detail->json('data.expected_price'));
        }
        $list = $this->getJson('/api/user/seller-submissions/my-list')->assertOk();
        $this->assertSame($detail->json('data.expected_price'), $list->json('data.0.expected_price'));

        $admin = new class extends Admin {
            public function can($abilities, $arguments = []) { return true; }
            public function canAny($abilities, $arguments = []) { return true; }
            public function getRoleAttribute() { return 'Administrator'; }
        };
        $admin->fill(['type' => 'admin', 'name' => 'Reviewer']);
        auth('admin')->setUser($admin);
        // Isolate the detail template from unrelated dashboard notification queries.
        $this->app['events']->forget('composing: dashboard.layouts.app');
        view()->share('errors', new ViewErrorBag());
        $html = app(SellerSubmissionController::class)->show($submission->id)->render();
        $this->assertStringContainsString('Sheep', $html);
        $display = $expected === null ? '-' : preg_quote((string) $submission->expected_price, '/');
        $label = preg_quote(TranslationHelper::translate('expected_price'), '/');
        $this->assertMatchesRegularExpression('/<th>'.$label.'<\/th><td>'.$display.'<\/td>/', $html);
    }

    public static function prices(): array
    {
        return ['missing' => [[]], 'empty' => [['expected_price' => '']], 'null' => [['expected_price' => null]],
            'decimal' => [['expected_price' => '1234.50']], 'explicit zero' => [['expected_price' => '0']]];
    }

    private function user(array $attributes = []): User
    {
        return User::create($attributes + ['name' => 'Fixture seller', 'user_type' => 'seller', 'is_active' => 1]);
    }

    private function submission(User $seller, User $partner, string $status = 'pending'): SellerSubmission
    {
        return SellerSubmission::create(['user_id' => $seller->id, 'partner_id' => $partner->id,
            'sheep_type' => 'Sheep', 'age' => '2', 'expected_price' => 100, 'status' => $status]);
    }

    /** @dataProvider invalidPrices */
    public function test_invalid_prices_and_other_required_fields_still_fail($price): void
    {
        Passport::actingAs($this->user());
        $this->postJson('/api/user/seller-submissions', ['expected_price' => $price])
            ->assertUnprocessable()->assertJsonValidationErrors(['expected_price', 'partner_id', 'sheep_type', 'age', 'images']);
        $this->postJson('/api/user/seller-submissions', [])
            ->assertUnprocessable()->assertJsonMissingValidationErrors('expected_price')
            ->assertJsonValidationErrors(['partner_id', 'sheep_type', 'age', 'images']);
        $this->assertSame(0, SellerSubmission::count());
    }

    public static function invalidPrices(): array { return [[-1], ['invalid'], [[]]]; }

    public function test_edit_can_clear_a_price_and_omission_preserves_it(): void
    {
        $seller = $this->user(); $partner = $this->user(['user_type' => 'vendor']);
        Passport::actingAs($seller);
        foreach ([[], ['expected_price' => null], ['expected_price' => '']] as $payload) {
            $submission = $this->submission($seller, $partner, 'needs edit');
            $this->putJson('/api/user/seller-submissions/'.$submission->id, $payload)->assertOk();
            if ($payload) {
                $this->assertNull($submission->fresh()->expected_price);
            } else {
                $this->assertEquals(100, $submission->fresh()->expected_price);
            }
        }
        $submission = $this->submission($seller, $partner, 'needs edit');
        $submission->update(['expected_price' => null]);
        $this->putJson('/api/user/seller-submissions/'.$submission->id, ['notes' => 'Updated'])->assertOk()
            ->assertJsonPath('data.expected_price', null);
        $this->assertNull($submission->fresh()->expected_price);
    }

    public function test_migration_preserves_existing_prices_and_refuses_lossy_rollback(): void
    {
        $submission = $this->legacySubmission;
        $migration = require database_path('migrations/2026_11_18_000000_allow_null_seller_submission_expected_price.php');
        $migration->up();
        $this->assertSame('1234.50', $submission->fresh()->expected_price);
        $this->expectException(\RuntimeException::class);
        $migration->down();
    }
}

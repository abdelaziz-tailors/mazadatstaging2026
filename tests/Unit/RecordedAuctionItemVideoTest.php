<?php

namespace Tests\Unit;

use App\Http\Requests\api\Video\item\StoreItemRequest;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

/** No application bootstrap, .env, migrations, or persistent database. */
class RecordedAuctionItemVideoTest extends TestCase
{
    private $app;
    private $database;
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository());
        $this->database = new Manager($this->app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        $db = $this->database->getConnection();
        self::assertSame('', $db->selectOne('PRAGMA database_list')->file);
        $db->statement('CREATE TABLE live_videos (id INTEGER PRIMARY KEY, type TEXT)');
        $db->table('live_videos')->insert(['id' => 1, 'type' => 'recorded']);
        $this->validator = new Factory(new Translator(new ArrayLoader(), 'en'), $this->app);
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    private function validateVideos(array $videos = [], array $titles = [0 => 'Animal'], string $type = 'recorded')
    {
        $this->database->getConnection()->table('live_videos')->where('id', 1)->update(['type' => $type]);
        $images = [];
        foreach ($titles as $index => $title) {
            $images[$index] = [UploadedFile::fake()->createWithContent('animal.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
            ))];
        }
        $request = StoreItemRequest::create('/api/live/items/add/1', 'POST', [
            'lineage_title_ar' => $titles,
        ], [], ['image' => $images, 'video' => $videos]);
        $route = new Route('POST', 'api/live/items/add/{id}', function () {});
        $route->bind($request);
        $request->setRouteResolver(function () use ($route) { return $route; });
        $validator = $this->validator->make($request->all(), $request->rules());
        $request->withValidator($validator);

        return [$request, $validator];
    }

    public function test_missing_video_uses_existing_422_error_envelope(): void
    {
        [$request, $validator] = $this->validateVideos();
        self::assertTrue($validator->fails());
        self::assertSame(['video.0'], $validator->errors()->keys());
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, new class {
            public function json($data, $status) { return new JsonResponse($data, $status); }
        });
        $method = new \ReflectionMethod(StoreItemRequest::class, 'failedValidation');
        $method->setAccessible(true);
        try {
            $method->invoke($request, $validator);
            self::fail('Expected validation rejection.');
        } catch (HttpResponseException $e) {
            self::assertSame(422, $e->getResponse()->getStatusCode());
            $body = $e->getResponse()->getData(true);
            self::assertFalse($body['success']);
            self::assertSame(422, $body['code']);
            self::assertArrayHasKey('video.0', $body['errors']);
        }
    }

    public function test_valid_recorded_video_is_accepted(): void
    {
        [, $validator] = $this->validateVideos([UploadedFile::fake()->create('animal.mp4', 10, 'video/mp4')]);
        self::assertTrue($validator->passes(), $validator->errors()->toJson());
    }

    public function test_every_animal_requires_its_own_video_even_with_sparse_indices(): void
    {
        [, $validator] = $this->validateVideos([2 => UploadedFile::fake()->create('animal.mp4', 10, 'video/mp4')], [2 => 'First', 7 => 'Second']);
        self::assertTrue($validator->fails());
        self::assertSame(['video.7'], $validator->errors()->keys());
    }

    public function test_null_and_non_file_videos_are_rejected(): void
    {
        foreach ([null, 'https://example.com/animal.mp4'] as $video) {
            // Text fields must not satisfy the required upload contract.
            [$request] = $this->validateVideos();
            $data = $request->all();
            $data['video'] = [$video];
            $validator = $this->validator->make($data, $request->rules());
            self::assertTrue($validator->fails());
            self::assertTrue($validator->errors()->has('video.0'));
        }
    }

    public function test_other_auction_types_still_accept_missing_video(): void
    {
        foreach (['live', 'photo'] as $type) {
            [, $validator] = $this->validateVideos([], [0 => 'Animal'], $type);
            self::assertTrue($validator->passes(), $validator->errors()->toJson());
        }
    }

    public function test_existing_video_format_and_size_limits_are_preserved(): void
    {
        foreach ([UploadedFile::fake()->create('animal.txt', 10, 'text/plain'), UploadedFile::fake()->create('animal.mp4', 20001, 'video/mp4')] as $video) {
            [, $validator] = $this->validateVideos([$video]);
            self::assertTrue($validator->fails());
            self::assertTrue($validator->errors()->has('video.0'));
        }
    }
}

<?php

namespace Tests\Unit;

use App\Http\Controllers\Dashboard\OrderPieceServiceController;
use App\Http\Controllers\Dashboard\ItemServiceController;
use App\Http\Requests\Dashboard\ItemService\StoreItemServiceRequest;
use App\Http\Requests\Dashboard\ItemService\UpdateItemServiceRequest;
use App\Http\Requests\Dashboard\Order\StoreOrderPieceServiceRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItemService;
use App\Models\ItemService;
use App\Services\OrderService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;

/** No application bootstrap, .env loading, migrations, or persistent database. */
class DashboardServicePriceTest extends TestCase
{
    private $app;
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository(['app' => ['locale' => 'en']]));
        $this->app->instance('events', new Dispatcher($this->app));
        $this->database = new Manager($this->app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $this->app->instance('db', $this->database->getDatabaseManager());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);

        $pdo = $this->database->getConnection()->getPdo();
        self::assertSame('sqlite', $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        self::assertSame('', $pdo->query('PRAGMA database_list')->fetch(\PDO::FETCH_ASSOC)['file']);
        foreach ([
            'users' => 'id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT',
            'live_videos' => 'id INTEGER PRIMARY KEY, admin_id INTEGER, commission_payer TEXT, commission_amount NUMERIC, service_fee NUMERIC',
            'live_video_items' => 'id INTEGER PRIMARY KEY, live_video_id INTEGER, seller_id INTEGER, title TEXT, title_ar TEXT',
            'live_video_item_pieces' => 'id INTEGER PRIMARY KEY, live_video_item_id INTEGER, piece_number INTEGER',
            'orders' => 'id INTEGER PRIMARY KEY, order_number TEXT, live_video_id INTEGER, buyer_id INTEGER, payment_status TEXT, status TEXT, settled_at TEXT, deleted_at TEXT, subtotal NUMERIC, tax_percent NUMERIC, tax_value NUMERIC, commission_percent NUMERIC, commission_payer TEXT, commission_value NUMERIC, service_fee_per_item NUMERIC, service_fee_total NUMERIC, piece_services_total NUMERIC, total NUMERIC, created_at TEXT, updated_at TEXT',
            'order_items' => 'id INTEGER PRIMARY KEY, order_id INTEGER, live_video_item_id INTEGER, seller_id INTEGER, finished_price NUMERIC, settled_at TEXT',
            'item_services' => "id INTEGER PRIMARY KEY, name TEXT, is_active INTEGER, is_default INTEGER, admin_id INTEGER, default_price NUMERIC, cost_pricing_method TEXT DEFAULT 'fixed', sort_order INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT",
            // Deliberately the original schema: no leftover calculator columns or index.
            'order_item_services' => 'id INTEGER PRIMARY KEY AUTOINCREMENT, order_item_id INTEGER, item_service_id INTEGER, custom_name TEXT, price NUMERIC, created_at TEXT, updated_at TEXT',
        ] as $table => $columns) {
            $pdo->exec('CREATE TABLE '.$table.' ('.$columns.')');
        }
        $db = $this->database->getConnection();
        $db->table('users')->insert(['id' => 2, 'name' => 'Test seller']);
        $db->table('live_videos')->insert(['id' => 1, 'admin_id' => 7, 'commission_payer' => 'seller', 'commission_amount' => 5, 'service_fee' => 20]);
        $db->table('live_video_items')->insert(['id' => 1, 'live_video_id' => 1, 'seller_id' => 2, 'title' => 'Test lot']);
        $db->table('orders')->insert(['id' => 1, 'order_number' => 'TEST-1', 'live_video_id' => 1, 'buyer_id' => 3, 'payment_status' => 'unpaid', 'status' => 'pending', 'commission_payer' => 'seller', 'commission_percent' => 5, 'service_fee_per_item' => 20]);
        $db->table('order_items')->insert(['id' => 1, 'order_id' => 1, 'live_video_item_id' => 1, 'seller_id' => 2, 'finished_price' => 1000]);
        $db->table('item_services')->insert(['id' => 1, 'name' => '{"en":"Feeding","ar":"تغذية"}', 'is_active' => 1, 'is_default' => 1, 'default_price' => 3, 'cost_pricing_method' => 'per_animal_per_day']);

        $translator = new Translator(new FileLoader(new Filesystem(), resource_path('lang')), 'en');
        $validator = new Factory($translator, $this->app);
        $validator->setPresenceVerifier(new DatabasePresenceVerifier($this->database->getDatabaseManager()));
        $this->app->instance('translator', $translator);
        $this->app->instance('validator', $validator);

        // Keep the existing partner ownership check active.
        $admin = new Admin(['type' => 'partner']);
        $admin->id = 7;
        $guard = Mockery::mock();
        $guard->shouldReceive('user')->andReturn($admin);
        $guard->shouldReceive('id')->andReturn(7);
        $auth = Mockery::mock();
        $auth->shouldReceive('guard')->with('admin')->andReturn($guard);
        Auth::swap($auth);
        $toast = Mockery::mock();
        $toast->shouldReceive('success')->andReturnNull();
        Toastr::swap($toast);
        $redirect = Mockery::mock();
        $redirect->shouldReceive('route')->with('admin.orders.edit', 1)->andReturn(new RedirectResponse('/test-order'));
        $redirect->shouldReceive('route')->with('admin.item-services.index')->andReturn(new RedirectResponse('/test-catalog'));
        $this->app->instance('redirect', $redirect);
        $localization = Mockery::mock();
        $localization->shouldReceive('getSupportedLocales')->andReturn(['en' => [], 'ar' => []]);
        \Mcamara\LaravelLocalization\Facades\LaravelLocalization::swap($localization);
        // TranslationHelper writes missing keys; ensure this existing key is already present.
        foreach (['en', 'ar'] as $locale) {
            $translations = require resource_path('lang/'.$locale.'/app.php');
            self::assertArrayHasKey('item_service_added_successfully', $translations);
            self::assertArrayHasKey('order_not_editable', $translations);
            self::assertArrayHasKey('item_service_created_successfully', $translations);
            self::assertArrayHasKey('item_service_updated_successfully', $translations);
        }
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    private function request(array $data): StoreOrderPieceServiceRequest
    {
        $request = StoreOrderPieceServiceRequest::createFrom(Request::create('/test-service', 'POST', $data + ['order_item_id' => 1, 'item_service_id' => 1]));
        $request->setContainer($this->app);
        $validator = $this->app['validator']->make($request->all(), $request->rules(), [], $request->attributes());
        $validator->validate();
        $request->setValidator($validator);

        return $request;
    }

    public function test_calculator_ignores_submitted_total_and_uses_existing_persistence_and_invoice_flow(): void
    {
        $request = $this->request(['price_entry' => 'calculated', 'unit_rate' => '999', 'cost_pricing_method' => 'fixed', 'number_of_days' => 5, 'number_of_animals' => 20, 'price' => 'tampered']);
        self::assertSame('300.00', $request->servicePrice());
        (new OrderPieceServiceController())->store($request, 1);
        $service = OrderItemService::firstOrFail();
        self::assertSame(300.0, $service->price);
        self::assertSame(['id', 'order_item_id', 'item_service_id', 'custom_name', 'price', 'created_at', 'updated_at'], array_keys($service->getAttributes()));
        $order = Order::findOrFail(1);
        self::assertSame(300.0, $order->piece_services_total);
        self::assertSame(1000.0, $order->total);
        $invoice = OrderService::sellerInvoiceSummariesForOrder($order)->first();
        self::assertSame(300.0, $invoice['piece_services']);
        self::assertSame(50.0, $invoice['commission']);
        self::assertSame(630.0, $invoice['net']);
    }

    public function test_manual_price_and_catalog_default_keep_original_behavior(): void
    {
        $this->database->getConnection()->table('item_services')->where('id', 1)->update(['default_price' => 12.5]);
        (new OrderPieceServiceController())->store($this->request(['price' => '42.125']), 1);
        (new OrderPieceServiceController())->store($this->request(['price_entry' => 'manual', 'price' => null, 'unit_rate' => 'ignored']), 1);
        self::assertSame([42.13, 12.5], OrderItemService::orderBy('id')->get()->pluck('price')->all());
        self::assertSame(54.63, Order::findOrFail(1)->piece_services_total);
    }

    /** @dataProvider invalidInputs */
    public function test_invalid_calculator_inputs_are_rejected(array $changes): void
    {
        $this->expectException(ValidationException::class);
        if (array_key_exists('catalog_rate', $changes)) {
            $this->database->getConnection()->table('item_services')->where('id', 1)->update(['default_price' => $changes['catalog_rate']]);
            unset($changes['catalog_rate']);
        }
        $request = $this->request(array_merge(['price_entry' => 'calculated', 'unit_rate' => '3', 'number_of_days' => 5, 'number_of_animals' => 20], $changes));
        $request->servicePrice();
    }

    public static function invalidInputs(): array
    {
        return [
            'negative rate' => [['catalog_rate' => '-1']],
            'fractional halala' => [['catalog_rate' => '1.001']],
            'missing rate' => [['catalog_rate' => null]],
            'zero days' => [['number_of_days' => 0]],
            'fractional days' => [['number_of_days' => '1.5']],
            'negative animals' => [['number_of_animals' => -1]],
            'missing animals' => [['number_of_animals' => null]],
            'invalid mode' => [['price_entry' => 'unknown']],
            'total exceeds price column' => [['catalog_rate' => '9999999999.99', 'number_of_days' => 2]],
            'huge quantity' => [['number_of_animals' => '9999999999999999999999']],
        ];
    }

    public function test_decimal_rate_zero_and_maximum_supported_total(): void
    {
        foreach ([['0.29', 3, 7, '6.09'], ['0', 5, 20, '0.00'], ['9999999999.99', 1, 1, '9999999999.99']] as [$daily, $days, $animals, $expected]) {
            $this->database->getConnection()->table('item_services')->where('id', 1)->update(['default_price' => $daily]);
            self::assertSame($expected, $this->request(['price_entry' => 'calculated', 'unit_rate' => $daily, 'number_of_days' => $days, 'number_of_animals' => $animals])->servicePrice());
        }
    }

    public function test_calculator_does_not_bypass_partner_ownership(): void
    {
        $this->database->getConnection()->table('live_videos')->where('id', 1)->update(['admin_id' => 8]);
        try {
            (new OrderPieceServiceController())->store($this->request(['price_entry' => 'calculated', 'unit_rate' => '3', 'number_of_days' => 5, 'number_of_animals' => 20]), 1);
            self::fail('Another partner must not add a charge.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
            self::assertSame(0, OrderItemService::count());
        }
    }

    /** @dataProvider lockedOrders */
    public function test_calculator_keeps_existing_order_edit_restrictions(array $changes): void
    {
        $this->database->getConnection()->table('orders')->where('id', 1)->update($changes);
        try {
            (new OrderPieceServiceController())->store($this->request(['price_entry' => 'calculated', 'unit_rate' => '3', 'number_of_days' => 5, 'number_of_animals' => 20]), 1);
            self::fail('This order must not accept a charge.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            self::assertSame(422, $exception->getStatusCode());
            self::assertSame(0, OrderItemService::count());
        }
    }

    public static function lockedOrders(): array
    {
        return [
            'paid' => [['payment_status' => 'paid']],
            'delivered' => [['status' => 'delivered']],
            'settled' => [['settled_at' => '2026-01-01 00:00:00']],
        ];
    }

    public function test_legacy_catalog_and_fixed_price_ignore_calculator_inputs(): void
    {
        self::assertSame('fixed', (new ItemService())->pricingMethod());
        $db = $this->database->getConnection();
        $db->table('item_services')->insert(['id' => 2, 'default_price' => 150, 'is_active' => 1, 'is_default' => 1]);
        self::assertSame('fixed', ItemService::findOrFail(2)->pricingMethod());
        (new OrderPieceServiceController())->store($this->request(['item_service_id' => 2, 'price_entry' => 'calculated', 'price' => '150', 'unit_rate' => -10, 'number_of_days' => 'invalid', 'number_of_animals' => 0]), 1);
        self::assertSame(150.0, OrderItemService::firstOrFail()->price);
    }

    public function test_per_animal_ignores_days_and_client_rate_and_total(): void
    {
        $this->database->getConnection()->table('item_services')->where('id', 1)->update(['cost_pricing_method' => 'per_animal', 'default_price' => 35]);
        (new OrderPieceServiceController())->store($this->request(['price_entry' => 'calculated', 'unit_rate' => '999', 'number_of_days' => 'irrelevant', 'number_of_animals' => 20, 'price' => '0.01']), 1);
        self::assertSame(700.0, OrderItemService::firstOrFail()->price);
        self::assertSame(700.0, Order::findOrFail(1)->piece_services_total);
        self::assertSame(1000.0, Order::findOrFail(1)->total);
    }

    public function test_catalog_create_and_update_do_not_reprice_existing_charges(): void
    {
        $create = StoreItemServiceRequest::createFrom(Request::create('/catalog', 'POST', ['name' => ['ar' => 'Test catalog service'], 'default_price' => '35', 'cost_pricing_method' => 'per_animal', 'is_active' => 1]));
        $this->app['validator']->make($create->all(), $create->rules())->validate();
        (new ItemServiceController())->store($create);
        $catalog = ItemService::orderByDesc('id')->firstOrFail();
        self::assertSame('per_animal', $catalog->pricingMethod());
        self::assertSame(35.0, $catalog->default_price);
        self::assertSame(7, $catalog->admin_id);
        (new OrderPieceServiceController())->store($this->request(['item_service_id' => $catalog->id, 'price_entry' => 'calculated', 'number_of_animals' => 20]), 1);
        $update = UpdateItemServiceRequest::createFrom(Request::create('/catalog', 'PUT', ['name' => ['ar' => 'Updated name'], 'default_price' => '3', 'cost_pricing_method' => 'per_animal_per_day', 'is_active' => 1]));
        $this->app['validator']->make($update->all(), $update->rules())->validate();
        (new ItemServiceController())->update($update, $catalog->id);
        self::assertSame('per_animal_per_day', $catalog->fresh()->pricingMethod());
        self::assertSame(3.0, $catalog->fresh()->default_price);
        self::assertSame(700.0, OrderItemService::firstOrFail()->price);
        self::assertSame(700.0, Order::findOrFail(1)->piece_services_total);
        self::assertSame(230.0, OrderService::sellerInvoiceSummariesForOrder(Order::findOrFail(1))->first()['net']);
    }

    public function test_catalog_method_validation_and_legacy_default(): void
    {
        $request = StoreItemServiceRequest::createFrom(Request::create('/catalog', 'POST', ['name' => ['ar' => 'Fixed'], 'default_price' => '150']));
        $this->app['validator']->make($request->all(), $request->rules())->validate();
        (new ItemServiceController())->store($request);
        self::assertSame('fixed', ItemService::orderByDesc('id')->firstOrFail()->pricingMethod());
        $request->merge(['cost_pricing_method' => 'unsupported']);
        self::assertFalse($this->app['validator']->make($request->all(), $request->rules())->passes());
    }

    public function test_partner_cannot_change_global_or_another_partners_catalog_method(): void
    {
        foreach ([['is_default' => 1, 'admin_id' => 7], ['is_default' => 0, 'admin_id' => 8]] as $ownership) {
            $this->database->getConnection()->table('item_services')->where('id', 1)->update($ownership);
            $request = UpdateItemServiceRequest::createFrom(Request::create('/catalog', 'PUT', ['name' => ['ar' => 'Not allowed'], 'default_price' => 99, 'cost_pricing_method' => 'fixed']));
            try {
                (new ItemServiceController())->update($request, 1);
                self::fail('Catalog access rules must remain in force.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                self::assertSame(403, $exception->getStatusCode());
                self::assertSame('per_animal_per_day', ItemService::findOrFail(1)->pricingMethod());
                self::assertSame(3.0, ItemService::findOrFail(1)->default_price);
            }
        }
    }

    public function test_legacy_charge_update_requires_no_calculation_details(): void
    {
        $service = \App\Services\PieceServiceService::addToOrderItem(\App\Models\OrderItem::findOrFail(1), ['item_service_id' => 1, 'price' => '150']);
        $this->database->getConnection()->table('item_services')->where('id', 1)->update(['cost_pricing_method' => 'per_animal', 'default_price' => 35]);
        $request = \App\Http\Requests\Dashboard\Order\UpdateOrderPieceServiceRequest::createFrom(Request::create('/charge', 'PUT', ['price' => '175.50']));
        $this->app['validator']->make($request->all(), $request->rules())->validate();
        foreach (['en', 'ar'] as $locale) {
            self::assertArrayHasKey('item_service_updated_successfully', require resource_path('lang/'.$locale.'/app.php'));
        }
        (new OrderPieceServiceController())->update($request, $service->id);
        self::assertSame(175.5, $service->fresh()->price);
        self::assertSame(175.5, Order::findOrFail(1)->piece_services_total);
    }

    public function test_mobile_fixed_price_flow_and_catalog_response_remain_compatible(): void
    {
        $data = ['item_service_id' => 1, 'price' => '42.50'];
        $mobileRequest = new \App\Http\Requests\api\User\Profile\StorePieceServiceRequest();
        $mobileRequest->merge($data + ['order_item_id' => 1]);
        self::assertTrue($this->app['validator']->make($mobileRequest->all(), $mobileRequest->rules())->passes());
        $service = \App\Services\PieceServiceService::addToOrderItem(\App\Models\OrderItem::findOrFail(1), $data);
        self::assertSame(42.5, $service->price);
        $catalog = ItemService::findOrFail(1);
        $resource = (new \App\Http\Resources\User\ItemServiceCatalogResource($catalog))->toArray(new Request());
        self::assertSame(['id', 'name', 'name_en', 'name_ar', 'default_price'], array_keys($resource));
        self::assertArrayNotHasKey('cost_pricing_method', $catalog->toArray());
    }
}

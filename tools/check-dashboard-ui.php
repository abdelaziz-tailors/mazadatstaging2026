<?php
/**
 * Offline UI verification. No migrations, controllers, real accounts, or external
 * connections. Compiles every dashboard Blade view and renders real templates
 * with in-memory presentation fixtures for the companion browser checks.
 * Usage: php tools/check-dashboard-ui.php <temporary-output-directory>
 */
declare(strict_types=1);

$output = $argv[1] ?? sys_get_temp_dir().'/mazadat-dashboard-ui';
if (!is_dir($output) && !mkdir($output, 0777, true)) {
    throw new RuntimeException('Unable to create the preview directory.');
}
$output = realpath($output);
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_CONFIG_CACHE' => $output.'/unused-config.php',
    'APP_ROUTES_CACHE' => $output.'/unused-routes.php',
    'VIEW_COMPILED_PATH' => $output.'/compiled',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
    'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
    'LOG_CHANNEL' => 'stderr', 'TELESCOPE_ENABLED' => 'false',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
if (!is_dir($output.'/compiled')) mkdir($output.'/compiled');
require dirname(__DIR__).'/vendor/autoload.php';

// TranslationHelper normally writes missing keys to app.php. Previewing must
// remain read-only, so use the existing dictionaries without its write fallback.
class DashboardPreviewTranslations
{
    public static function translate($key, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $values = require resource_path('lang/'.$locale.'/app.php');
        $key = str_ireplace(["'", '"', ',', ';', '<', '>', '?', '.', '@', '#', '!', '%', '$', '؟'], ' ', $key);
        $clean = strtolower(str_replace(' ', '_', $key));
        return $values[$clean] ?? str_replace('_', ' ', $key);
    }
}
class_alias(DashboardPreviewTranslations::class, App\Helpers\TranslationHelper::class);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
});
if (!$app->environment('testing') || config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:' || config('database.connections.sqlite.url')) {
    throw new RuntimeException('Preview database isolation failed.');
}
// Fail closed if rendering accidentally attempts any query, even in memory.
Illuminate\Support\Facades\DB::connection()->beforeExecuting(function () {
    throw new RuntimeException('UI preview attempted a database query.');
});
$app['events']->forget('composing: dashboard.layouts.app');
config(['app.url' => 'http://127.0.0.1', 'app.asset_url' => null, 'session.driver' => 'array']);
$app['url']->forceRootUrl('http://127.0.0.1');
$app['session']->driver()->start();
$app['request']->setLaravelSession($app['session']->driver());

$count = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/dashboard'))) as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $compiled = $app['blade.compiler']->compileString(file_get_contents($file->getPathname()));
    try {
        token_get_all($compiled, TOKEN_PARSE);
    } catch (ParseError $error) {
        throw new RuntimeException($file->getPathname().': '.$error->getMessage());
    }
    $count++;
}
echo "PASS: {$count} dashboard Blade templates compile.\n";

$admin = new class extends App\Models\Admin {
    public function can($abilities, $arguments = []) { return true; }
    public function canAny($abilities, $arguments = []) { return true; }
    public function getRoleAttribute() { return $this->type === 'partner' ? 'Partner' : 'Administrator'; }
};
$admin->forceFill(['id' => 1, 'name' => 'Mazadat Admin', 'type' => 'admin', 'email' => 'preview@example.test']);
Illuminate\Support\Facades\Auth::guard('admin')->setUser($admin);
$app['view']->share('headerPendingReviewCount', 0);
$app['view']->share('headerNotifications', collect());

// Source views exercise their actual forms, route helpers, assets and scripts.
// List data is supplied through intercepted DataTables requests in the browser.
$stats = array_fill_keys([
    'total','total_users','active','inactive','total_count','active_count','inactive_count',
    'total_buyers','total_vendors','verified','unverified','total_packages','monthly','annual',
    'max_auctions_limit','total_revenue','total_orders','pending','approved','rejected',
    'total_partners','total_sales','live','recorded','scheduled','ended','new','paid',
    'total_submissions','pending_count','approved_count','rejected_count',
], 12);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/dashboard'))) as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    preg_match_all('/\$stats\[\x27([^\x27]+)\x27\]/', file_get_contents($file->getPathname()), $matches);
    foreach ($matches[1] as $key) $stats[$key] = str_ends_with($key, 'direction') ? 'up' : 12;
}
$base = [
    'stats' => $stats, 'request' => $app['request'], 'data' => null, 'isPartner' => false,
    'isPartnerDashboard' => false, 'currentBalance' => 24850.75,
    'transactions' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
    'subscriptions' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
    'invoices' => new Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
    'days' => 30, 'reports' => [],
    'salesTrend' => ['value' => 248500, 'direction' => 'up', 'pct' => 12.5],
    'registrationsTrend' => ['value' => 245, 'direction' => 'up', 'pct' => 8.2],
    'salesChart' => ['labels' => ['01','05','10','15','20','25','30'], 'values' => [1200,3400,2000,6200,4500,8000,7400]],
    'registrationsChart' => ['labels' => ['01','05','10','15','20','25','30'], 'values' => [4,8,5,12,9,16,14]],
    'statusChart' => ['active' => 24, 'scheduled' => 18, 'ended' => 96],
    'pendingReviewCount' => 6, 'latestAuctions' => collect(), 'latestUsers' => collect(),
    'partners' => collect(), 'providers' => collect(), 'categories' => collect(), 'cities' => collect(),
    'roles' => collect(), 'permissions' => collect(), 'departments' => collect(),
];
$pages = [
    'home' => 'dashboard.home', 'login' => 'dashboard.auth.login',
    'home-empty' => 'dashboard.home',
    'users' => 'dashboard.pages.users.index', 'orders' => 'dashboard.pages.orders.index',
    'packages' => 'dashboard.pages.packages.index', 'invoices' => 'dashboard.pages.partner-finance.invoices',
    'wallet' => 'dashboard.pages.partner-finance.wallet', 'form' => 'dashboard.pages.ages.create',
    'password' => 'dashboard.pages.admin-profile.change_password',
    'auction-form' => 'dashboard.pages.videos.create',
    'actions' => 'dashboard.layouts.app',
    'auctions' => 'dashboard.pages.auctions.index',
    'submissions' => 'dashboard.pages.seller-submissions.index',
    'subscriptions' => 'dashboard.pages.user-subscriptions.index',
    'profile' => 'dashboard.pages.admin-profile.profile',
    'role-form' => 'dashboard.pages.roles.create',
    'vendors' => 'dashboard.pages.vendors.index',
    'partners' => 'dashboard.pages.partners.index',
    'products' => 'dashboard.pages.products.index',
    'submission-detail' => 'dashboard.pages.seller-submissions.show',
    'auction-detail' => 'dashboard.pages.auctions.show',
];
$manifest = [];
foreach (['en', 'ar'] as $locale) {
    $app->setLocale($locale);
    $app['laravellocalization']->setLocale($locale);
    foreach (['admin', 'partner'] as $type) {
        $admin->type = $type;
        $admin->name = $locale === 'ar' ? 'أحمد محمد' : 'Ahmed Mohamed';
        foreach ($pages as $name => $view) {
            $paths = ['home' => '', 'form' => 'ages/create', 'password' => 'profile/change-password', 'auction-form' => 'videos/create', 'wallet' => 'partner-finance/wallet', 'invoices' => 'partner-finance/invoices'];
            $request = Illuminate\Http\Request::create('http://127.0.0.1/'.$locale.'/admin/'.($paths[$name] ?? $name));
            if ($name === 'products') $request->query->set('id', 42);
            $request->setLaravelSession($app['session']->driver());
            $app->instance('request', $request);
            Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
            $app['url']->setRequest($request);
            $data = array_merge($base, ['isPartner' => $type === 'partner', 'isPartnerDashboard' => $type === 'partner']);
            $data['request'] = $request;
            $data['admin'] = $admin;
            if ($name === 'submission-detail') $data['submission'] = (object) [
                'id' => 42, 'user' => (object) ['name' => 'Preview seller', 'phone' => '0500000000'],
                'partner' => (object) ['name' => 'Preview partner'], 'sheep_type' => 'Preview item',
                'expected_price' => 2400, 'description' => 'Presentation fixture', 'notes' => '-',
                'status' => 'pending', 'media' => collect(),
            ];
            if ($name === 'auction-detail') $data['video'] = new class {
                public $title_ar = 'مزاد تجريبي';
                public $title = 'Preview auction';
                public $user_Video = null;
                public $partner = null;
                public $type = 'live';
                public $status = 'start';
                public $date_start_at = '2026-11-09';
                public $time_start_at = '10:00';
                public $date_end_at = '2026-11-10';
                public $time_end_at = '18:00';
                public $start_price = 2400;
                public function video_items() { return collect([1, 2, 3]); }
            };
            if ($name === 'role-form') $data['permissions'] = [
                ['name' => 'Users', 'permissions' => ['view users' => 'view', 'edit user' => 'edit', 'delete user' => 'delete']],
                ['name' => 'Auctions', 'permissions' => ['view videos' => 'view', 'add video' => 'add']],
            ];
            foreach ([['Sales',248500,'fa-solid fa-chart-line','primary'],['Users',1248,'fa-solid fa-users','info'],['Auctions',138,'fa-solid fa-gavel','warning'],['Orders',864,'fa-solid fa-cart-shopping','success'],['Partners',48,'fa-solid fa-handshake','purple'],['Vendors',186,'fa-solid fa-store','primary'],['Pending',6,'fa-solid fa-clock','warning'],['Packages',12,'fa-solid fa-crown','info']] as $report) {
                $data['reports'][] = ['name' => DashboardPreviewTranslations::translate($report[0]), 'value' => $report[1], 'icon' => $report[2], 'color' => $report[3], 'trend' => ['direction' => 'up', 'pct' => 12.5]];
            }
            if ($name === 'home-empty') {
                $data['salesChart']['values'] = array_fill(0, count($data['salesChart']['labels']), 0);
                $data['registrationsChart']['values'] = array_fill(0, count($data['registrationsChart']['labels']), 0);
                $data['reports'] = array_slice($data['reports'], 0, 7);
            }
            $errors = new Illuminate\Support\ViewErrorBag();
            if ($name === 'password') $errors->put('default', new Illuminate\Support\MessageBag(['password' => [$locale === 'ar' ? 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.' : 'The password must be at least 8 characters.']]));
            $app['view']->share('errors', $errors);
            try {
                if ($name === 'actions') {
                    $rows = '';
                    foreach ([1, 2] as $id) {
                        $userActions = $app['view']->make('dashboard.pages.users.actions', ['item' => (object) ['id' => $id]])->render();
                        $lookupActions = $app['view']->make('dashboard.pages.ages.actions', ['item' => (object) ['id' => $id + 10]])->render();
                        $rows .= '<tr><td>Preview record '.$id.'</td><td>'.$userActions.'</td><td>'.$lookupActions.'</td></tr>';
                    }
                    $app['view']->startSection('title', 'UI verification');
                    $app['view']->startSection('content');
                    echo '<div class="page-header"><h1 class="page-title">UI verification</h1></div><div class="alert alert-success" role="status">'.($locale === 'ar' ? 'تم الحفظ بنجاح' : 'Changes saved successfully').'</div><div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Actions</th><th>More</th></tr></thead><tbody>'.$rows.'</tbody></table></div></div></div>';
                    $app['view']->stopSection();
                }
                $html = $app['view']->make($view, $data)->render();
            } catch (Throwable $error) {
                fwrite(STDERR, "FAIL {$locale}/{$type}/{$name}: ".strip_tags($error->getMessage())."\n");
                exit(1);
            }
            // Allow the static local preview server to serve real public assets.
            $html = str_replace('http://127.0.0.1/', '/', $html);
            $filename = "{$locale}-{$type}-{$name}.html";
            file_put_contents($output.'/'.$filename, $html);
            $manifest[] = ['file' => $filename, 'locale' => $locale, 'type' => $type, 'page' => $name];
        }
    }
}
file_put_contents($output.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
echo 'PASS: '.count($manifest)." real-template previews rendered without database queries.\n";
echo "Preview directory: {$output}\n";

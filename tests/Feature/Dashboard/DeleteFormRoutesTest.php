<?php

namespace Tests\Feature\Dashboard;

use App\Http\Middleware\SetDashboardLocale;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class DeleteFormRoutesTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->instance('request', Request::create('http://localhost/ar/admin/users'));
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_delete_forms_target_the_localized_delete_route_without_redirecting()
    {
        $forms = [
            'admins/actions' => 'admins.destroy',
            'animal_pens/actions' => 'animal-pens.destroy',
            'categories/actions' => 'categories.destroy',
            'colors/actions' => 'colors.destroy',
            'gifts/actions' => 'gifts.destroy',
            'notifications/actions' => 'notifications.destroy',
            'packages/actions' => 'packages.destroy',
            'pages/actions' => 'pages.destroy',
            'partners/actions' => 'partners.destroy',
            'products/actions' => 'products.destroy',
            'products/actions-comment' => 'products.comments.delete',
            'report-users/actions' => 'report-users.destroy',
            'reports/actions' => 'reports.destroy',
            'roles/actions' => 'roles.destroy',
            'settings/actions' => 'sounds.destroy',
            'sounds/actions' => 'sounds.destroy',
            'users/actions' => 'users.destroy',
            'vendors/actions' => 'vendors.destroy',
        ];

        Request::enableHttpMethodParameterOverride();

        foreach ($forms as $view => $route) {
            $name = 'admin.'.$route;
            $source = file_get_contents(resource_path('views/dashboard/pages/'.$view.'.blade.php'));
            $this->assertStringContainsString("route('".$name."', \$item->id)", $source, $view);
            $this->assertStringContainsString('method="POST"', $source, $view);
            $this->assertMatchesRegularExpression('/name="_method"[^>]*value="DELETE"/', $source, $view);

            $url = route($name, 106);
            $this->assertStringStartsWith('/ar/admin/', parse_url($url, PHP_URL_PATH), $view);
            $request = Request::create($url, 'POST', ['_method' => 'DELETE']);
            $matched = app('router')->getRoutes()->match($request);
            $this->assertSame($name, $matched->getName(), $view);

            // Stop before authentication/controller execution: no records are deleted.
            $response = (new SetDashboardLocale())->handle($request, function () {
                return new Response('OK');
            });
            $this->assertSame(200, $response->getStatusCode(), $view);
        }
    }
}

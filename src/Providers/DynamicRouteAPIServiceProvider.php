<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DynamicRouteAPIServiceProvider extends ServiceProvider
{
    const CACHE_KEY = 'dynamic_routes';

    // إرجاع الروتات من الكاش
    public static function getRoutes()
    {
        return Cache::get(self::CACHE_KEY);
    }

    // تخزين الروتات في الكاش
    public static function storeRoutes($routes)
    {
        Cache::forever(self::CACHE_KEY, $routes);
    }

    // مسح الكاش
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function boot()
    {
        // تحقق من مسح الكاش إذا كان مطلوبًا
        if (env('CLEAR_CACHE_ON_BOOT', false)) {
            self::clearCache();
        }

        app()->booted(function () {
            // تحميل الروتات من الكاش أو توليدها
            $routes = Cache::rememberForever(self::CACHE_KEY, function () {
                return $this->generateRoutes();
            });
            try {
                foreach ($routes as $route) {
                    $routeInstance = Route::match(
                        $route['methods'],
                        $route['uri'],
                        $route['action']
                    )->middleware($route['middleware']);

                    // إضافة اسم الروت فقط إذا كان موجودًا
                    if (isset($route['name'])) {
                        $routeInstance->name('api.' . $route['name']);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error registering dynamic routes: ' . $e->getMessage());
            }
        });
    }

    private function generateRoutes()
    {
        $routes = [];
        $prefix = env('API_VERSION', '');
        foreach (Route::getRoutes() as $route) {
            if ($route->getAction('uses') instanceof \Closure) {
                continue;
            }
            $api_name = empty(env('API_NAME')) ? 'api' : env('API_NAME');
            $uriWithApi = $prefix ? "/{$api_name}/{$prefix}" . $route->uri() : "/{$api_name}/" . $route->uri();
            $action = $route->getAction();
            // تحقق إذا كانت هناك قائمة `middleware`
            if (isset($action['middleware'])) {
                // تصفية المصفوفة واستبعاد `web`
                $action['middleware'] = array_filter(
                    (array) $action['middleware'],
                    function ($middleware) {
                        return $middleware !== 'web';
                    }
                );
            }
            $actions[] = $action;
            $uri = $uriWithApi; // دمج المتغيرين
            $routeData = [
                'methods' => $route->methods(),
                'uri' => $uri,
                'action' => $action,
                'middleware' => $this->getMiddleware($route->getAction()), // تأكد من إضافة الميدل وير هنا
            ];
            // إضافة بادئة "api." للاسم فقط إذا كان روت API
            if ($route->getName()) {
                $routeData['name'] = 'api.' . $route->getName();
            }
            $routes[] = $routeData;
        }
        return $routes;
    }

    private function getMiddleware($action)
    {
        // استخراج قائمة الميدل وير وإضافة "auth:api" كافتراضي

        $middlewares = (array)($action['middleware'] ?? []);
        if (!in_array(env('AUTH_API', 'auth:sanctum'), $middlewares)) {
            $middlewares[] = env('AUTH_API', 'auth:sanctum');
        }
        foreach ($middlewares as $key => $middleware) {
            if ($middleware == 'web') {
                unset($middlewares[$key]);
            }
        }
        return array_values($middlewares);
    }
}

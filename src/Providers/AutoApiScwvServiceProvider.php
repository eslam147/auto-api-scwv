<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AutoApiScwvServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // نشر الملفات تلقائيًا إذا كانت موجودة

        $this->addEnvSettings();
        $this->addAuthSettings();
        $this->addGitignoreToAuthConfig();
        $this->modifyRouteServiceProvider();
        $this->addProviderToAppConfig();
        $this->publishes([
            __DIR__ . '/../../resources/config' => config_path(),
        ], 'config');
        // نشر الأوامر عند تثبيت الحزمة
        $this->commands([
            \AutoApi\AutoApiScwv\Console\Commands\MakeRequestApiCommand::class,
        ]);
    }
    protected function addAuthSettings()
    {
        $configFile = base_path('config/auth.php');

        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);

            if (strpos($configContent, 'api') === false) {
                $configContent = str_replace(
                    "'guards' => [",
                    "'guards' => [" . PHP_EOL . "        'api' => [" . PHP_EOL . "            'driver' => 'sanctum'," . PHP_EOL . "            'provider' => 'users'," . PHP_EOL . "        ],",
                    $configContent
                );
                file_put_contents($configFile, $configContent);
            }
        }
    }
    protected function addGitignoreToAuthConfig()
    {
        // مسار ملف auth.php
        $configFile = config_path('auth.php');

        if (file_exists($configFile)) {
            // قراءة محتويات الملف
            $configContent = File::get($configFile);

            // التأكد من أن 'gitignore' ليست موجودة بالفعل
            if (strpos($configContent, 'gitignore') === false) {
                // إضافة 'gitignore' خارج الـ guards
                $newContent = preg_replace(
                    '/return\s+\[/',
                    'return ['.
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    PHP_EOL .
                    "    'gitignore' => [" . PHP_EOL .
                    "        'api'," . PHP_EOL .
                    "        'sanctum'," . PHP_EOL .
                    "    ]," . PHP_EOL,
                    $configContent
                );

                // كتابة المحتوى المعدل إلى الملف
                File::put($configFile, $newContent);
            }
        }
    }

    protected function addProviderToAppConfig()
    {
        // مسار ملف app.php
        $configFile = config_path('app.php');

        if (file_exists($configFile)) {
            // قراءة محتويات الملف
            $configContent = File::get($configFile);

            // التحقق من أن DynamicRouteAPIServiceProvider غير موجود
            if (strpos($configContent, '\AutoApi\AutoApiScwv\Providers\DynamicRouteAPIServiceProvider::class') === false) {
                // البحث عن قائمة الـ 'providers' وإضافة السطر داخلها
                $newContent = preg_replace(
                    '/(\'providers\'\s*=>\s*\[)/',
                    "$1\n        \\AutoApi\\AutoApiScwv\\Providers\\DynamicRouteAPIServiceProvider::class,",
                    $configContent
                );

                // كتابة المحتوى المعدل إلى الملف
                File::put($configFile, $newContent);
            }
        }
    }


    protected function modifyRouteServiceProvider()
    {
        // مسار ملف RouteServiceProvider.php
        $configFile = app_path('Providers/RouteServiceProvider.php');

        if (file_exists($configFile)) {
            // قراءة محتويات الملف
            $configContent = File::get($configFile);

            // التحقق من عدم وجود التعديلات
            if (strpos($configContent, 'self::$API_VERSION') === false) {
                // إضافة المتغيرات public static $API_VERSION و public static $API_NAME
                if (strpos($configContent, 'class RouteServiceProvider') !== false) {
                    $configContent = preg_replace(
                        '/class RouteServiceProvider extends ServiceProvider\s*{/',
                        "class RouteServiceProvider extends ServiceProvider {\n\n    public static \$API_VERSION;\n    public static \$API_NAME;\n",
                        $configContent
                    );
                }

                // البحث عن الكود الخاص بـ $this->routes واستبداله
                $configContent = preg_replace_callback(
                    '/\$this->routes\(function\s*\(\)\s*{.*?}\);/s',
                    function ($matches) {
                        $newRoutesLogic = <<<ROUTES
            self::\$API_VERSION = env('API_VERSION'); // قيمة افتراضية إذا لم تكن موجودة
            self::\$API_NAME = empty(env('API_NAME')) ? 'api' : env('API_NAME');
            \$APIName = self::\$API_NAME;
            \$version = !empty(self::\$API_VERSION) ? '/' . self::\$API_VERSION : '';
            \$this->routes(function () use (\$version, \$APIName) {
                \$ApiRoutepath = !empty(\$version) ? base_path('routes/'.\$version.'/api.php') : base_path('routes/api.php');

                Route::prefix(\$APIName.\$version)
                    ->middleware('api')
                    ->namespace(\$this->namespace)
                    ->group(\$ApiRoutepath);

                // باقي الروتات
                Route::middleware('web')
                    ->namespace(\$this->namespace)
                    ->group(base_path('routes/web.php'));
            });
    ROUTES;
                        return $newRoutesLogic;
                    },
                    $configContent
                );

                // كتابة المحتوى المعدل إلى الملف
                File::put($configFile, $configContent);
            }
        }
    }


    protected function addEnvSettings()
    {
        $envFile = base_path('.env');

        if (file_exists($envFile) && strpos(file_get_contents($envFile), '#### Settings for API ####') === false) {
            file_put_contents($envFile, PHP_EOL . '#### Settings for API ####' . PHP_EOL . 'CLEAR_CACHE_ON_BOOT=true' . PHP_EOL . 'API_VERSION=' . PHP_EOL . 'AUTH_API=auth:sanctum' . PHP_EOL . 'API_NAME=' . PHP_EOL, FILE_APPEND);
        }
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

<?php

namespace Laravel\AutoApiScwv\Helpers;

class RouteServiceProviderModifier
{
    public static function modifyRouteServiceProvider()
    {
        $filePath = app_path('Providers/RouteServiceProvider.php');

        // التأكد من وجود الملف
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);

            // إضافة السطور المطلوبة لتهيئة المتغيرات
            if (strpos($content, 'public static $API_VERSION;') === false) {
                $content = str_replace(
                    'class RouteServiceProvider extends ServiceProvider',
                    'class RouteServiceProvider extends ServiceProvider' . PHP_EOL . '    public static $API_VERSION;' . PHP_EOL . '    public static $API_NAME;',
                    $content
                );
            }

            // تعديل دالة map لاحتواء الكود الجديد
            if (strpos($content, 'public function map()') !== false) {
                $content = preg_replace(
                    '/public function map\(\)/',
                    'public function map()
                    {
                        $version = !empty(self::$API_VERSION) ? \'/\' . self::$API_VERSION : \'\';
                        $APIName = self::$API_NAME ?? \'api\';

                        $this->routes(function () use ($version, $APIName) {
                            $ApiRoutepath = !empty($version) ? base_path(\'routes/\' . $version . \'/api.php\') : base_path(\'routes/api.php\');

                            Route::prefix($APIName . $version)
                                ->middleware(\'api\')
                                ->namespace($this->namespace)
                                ->group($ApiRoutepath);
                        });
                    }',
                    $content
                );
            }

            // كتابة المحتوى المعدل إلى الملف
            file_put_contents($filePath, $content);
        }
    }
}

<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class FormRequestServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $formRequestsPath = app_path('Http/FormRequests');
        $file = 'BaseFormRequest.php';
        $sourcePath = __DIR__ . '/src/FormRequests';
        // إنشاء المجلد FormRequests إذا لم يكن موجودًا
        if (!File::exists($formRequestsPath)) {
            File::makeDirectory($formRequestsPath, 0755, true);
        }

        // نسخ الملفات من المسار المصدر
        $this->publishes([
            $sourcePath => $formRequestsPath,
        ], 'form-requests');

        // تحديث الـ namespace للملفات المنسوخة
        $this->updateNamespaces($formRequestsPath, 'App\\Http\\FormRequests');
    }

    /**
     * تحديث الـ namespace لجميع الملفات في مسار معين.
     *
     * @param string $path
     * @param string $namespace
     * @return void
     */
    protected function updateNamespaces($path, $namespace)
    {
        // الحصول على جميع الملفات في المسار
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $content = File::get($file);

            // استبدال السطر الذي يحتوي على namespace إذا وجد
            $updatedContent = preg_replace(
                '/^namespace\s+.+?;/m',
                "namespace {$namespace};",
                $content
            );

            File::put($file, $updatedContent);
        }
    }
}

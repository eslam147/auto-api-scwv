<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class HelperServiceProvider extends ServiceProvider
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
        $helpersPath = app_path('Helpers');
        $helperFile = $helpersPath . '/Helper.php';

        // إنشاء المجلد Helpers إذا لم يكن موجودًا
        if (!File::exists($helpersPath)) {
            File::makeDirectory($helpersPath, 0755, true);
        }

        // إذا كان الملف Helper.php غير موجود، يتم نسخه
        if (!File::exists($helperFile)) {
            File::put($helperFile, File::get(__DIR__ . '/../../stubs/Helpers/Helper.php'));
            $this->updateNamespace($helperFile, 'App\\Helpers');
        } else {
            // إذا كان الملف موجودًا، يتم دمج المحتوى
            $existingContent = File::get($helperFile);
            $newContent = File::get(__DIR__ . '/../../stubs/Helpers/Helper.php');

            // دمج المحتويات مع التحقق من عدم التكرار
            $mergedContent = $existingContent;
            $newLines = explode(PHP_EOL, $newContent);
            foreach ($newLines as $line) {
                if (!str_contains($existingContent, $line)) {
                    $mergedContent .= PHP_EOL . $line;
                }
            }

            File::put($helperFile, $mergedContent);
            $this->updateNamespace($helperFile, 'App\\Helpers');
        }
    }

    /**
     * تحديث الـ namespace في ملف معين.
     *
     * @param string $filePath
     * @param string $namespace
     * @return void
     */
    protected function updateNamespace($filePath, $namespace)
    {
        $content = File::get($filePath);

        // استبدال السطر الذي يحتوي على namespace إذا وجد
        $updatedContent = preg_replace(
            '/^namespace\s+.+?;/m',
            "namespace {$namespace};",
            $content
        );

        File::put($filePath, $updatedContent);
    }
}

<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class MakeRequestApiCommandServiceProvider extends ServiceProvider
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
        $commandsPath = app_path('Console/Commands');
        $sourcePath = __DIR__ . '/../Console/Commands/MakeRequestApiCommand.php';

        if (!File::exists($commandsPath)) {
            File::makeDirectory($commandsPath, 0755, true);
        }

        $destinationPath = $commandsPath . '/MakeRequestApiCommand.php';

        if (!File::exists($destinationPath)) {
            File::copy($sourcePath, $destinationPath);
        } else {
            // إذا كان الملف موجودًا، يتم دمج المحتويات
            $existingContent = File::get($destinationPath);
            $newContent = File::get($sourcePath);

            // التحقق من عدم تكرار المحتوى
            if (strpos($existingContent, $newContent) === false) {
                File::put($destinationPath, $existingContent . PHP_EOL . $newContent);
            }
        }

        // تحديث الـ namespace
        $this->updateNamespace($destinationPath, 'App\\Console\\Commands');
    }

    /**
     * تحديث الـ namespace داخل الملف.
     *
     * @param string $filePath
     * @param string $newNamespace
     * @return void
     */
    protected function updateNamespace($filePath, $newNamespace)
    {
        $content = File::get($filePath);

        // تعديل سطر الـ namespace إذا كان موجودًا
        $updatedContent = preg_replace(
            '/^namespace\s+.+?;/m',
            "namespace {$newNamespace};",
            $content
        );

        File::put($filePath, $updatedContent);
    }
}

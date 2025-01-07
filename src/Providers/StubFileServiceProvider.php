<?php

namespace AutoApi\AutoApiScwv\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class StubFileServiceProvider extends ServiceProvider
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
        // تحديد مسار الملف الجديد في `app/stubs`
        $stubsPath = app_path('stubs');
        $stubFileName = 'request-api.stub';

        // مسار المصدر داخل الباكدج
        $sourcePath = __DIR__ . '/../stubs/' . $stubFileName;

        // مسار الوجهة داخل المشروع
        $destinationPath = $stubsPath . '/' . $stubFileName;

        // إنشاء مجلد `app/stubs` إذا لم يكن موجودًا
        if (!File::exists($stubsPath)) {
            File::makeDirectory($stubsPath, 0755, true);
        }

        // نسخ الملف إلى `app/stubs` إذا لم يكن موجودًا
        if (!File::exists($destinationPath)) {
            File::copy($sourcePath, $destinationPath);
            $this->info("The stub file has been successfully copied to {$destinationPath}");
        } else {
            $this->info("The stub file already exists at {$destinationPath}");
        }
    }
}

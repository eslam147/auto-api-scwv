<?php

namespace AutoApi\AutoApiScwv\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRequestApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:requestapi {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new request class extending BaseFormRequest';

    protected $type = 'Request';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');

        // تحديد المسار الكامل للملف الذي سيتم إنشاؤه
        $path = app_path('Http/Requests/' . $name . '.php');

        // التحقق إذا كان الملف موجود مسبقاً
        if (File::exists($path)) {
            $this->error("The request class already exists!");
            return;
        }

        // جلب محتوى الـ stub
        $stub = $this->getStub();

        // استبدال الأجزاء المتغيرة في الـ stub مثل الاسم والنيم سبيس
        $stub = str_replace('{{ class }}', $name, $stub);
        $stub = str_replace('{{ namespace }}', 'App\Http\Requests', $stub);
        // كتابة الـ stub إلى الملف
        File::put($path, $stub);

        $this->info("Request class $name created successfully at $path");
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return File::get(base_path('app/stubs/request-api.stub'));
    }

    /**
     * Get the default namespace for the request class.
     *
     * @return string
     */
}

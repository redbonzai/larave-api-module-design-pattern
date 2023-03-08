<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;

class GenerateModuleController extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class: php artisan generate:controller MyController --module=MyModule
    ';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/controller.stub');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->ensureStubsDirectoryExists();

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);

        if ($this->alreadyExists($name)) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type . ' created successfully.');

        return true;
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $basePath = $this->option('module')
            ? base_path('app/Modules/' . $this->option('module') . '/Controllers')
            : app_path('Http/Controllers');

        return $basePath . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . ($this->option('module') ? '\Modules\\' . $this->option('module') . '\Http\Controllers' : '\Http\Controllers');
    }

    /**
     * Resolve the path to the given stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return app_path('Console/Commands/stubs' . $stub);
    }

    /**
     * Ensure that the stubs directory exists.
     *
     * @return void
     */
    protected function ensureStubsDirectoryExists()
    {
        $directory = app_path('Console/Commands/stubs');

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['module', 'm', InputOption::VALUE_OPTIONAL, 'The name of the module'],
        ];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateModuleService extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'generate:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a service class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return app_path('Console/Commands/stubs/service.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($module = $this->option('module')) {
            return $rootNamespace . '\Modules\\' . $module . '\Services';
        }

        return $rootNamespace . '\Services';
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


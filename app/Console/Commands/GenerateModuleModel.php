<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class GenerateModuleEntity extends GeneratorCommand
{
    protected $name = 'generate:entity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new entity class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Entity';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string {
        return __DIR__.'/stubs/entity.stub';

//        return $this->option('module')
//            ? __DIR__.'/stubs/module.model.stub'
//            : __DIR__.'/stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace): string {
        return $this->option('module')
            ? $rootNamespace.'\\Modules\\'.$this->option('module').'\\Models'
            : $rootNamespace.'\\Models';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(string &$stub, string $name): self {
        $stub = str_replace('{{namespace}}', $this->getNamespace($name), $stub);

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('{{class}}', $class, $stub);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array {
        return [
            ['module', null, InputOption::VALUE_OPTIONAL, 'Generate the entity in a module.'],
        ];
    }
}

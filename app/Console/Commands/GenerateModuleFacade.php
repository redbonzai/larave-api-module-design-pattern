<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class GenerateModuleFacade extends GeneratorCommand
{
    protected $name = 'generate:module-facade';

    protected $description = 'Generate a new module facade class';

    protected $type = 'Facade';

    protected function getStub()
    {
        return __DIR__ . '/stubs/facade.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Modules\\' . $this->argument('module') . '\\Facades';
    }

    public function handle()
    {
        parent::handle();

        $module = $this->argument('module');
        $service = $this->argument('service');

        $serviceClass = $this->findServiceClass($module, $service);

        if (!$serviceClass) {
            $this->call('generate:module-service', [
                'name' => $service,
                '--module' => $module,
            ]);
            $serviceClass = $this->findServiceClass($module, $service);
        }

        $facadeClass = $this->qualifyClass($this->getNameInput());
        $serviceInstance = "\$this->app->make('{$serviceClass}')";

        if ($this->option('module')) {
            $this->addModuleFacade($module, $facadeClass, $serviceInstance);
        } else {
            $this->addAppFacade($facadeClass, $serviceInstance);
        }
    }

    protected function addAppFacade($facadeClass, $serviceInstance)
    {
        $appProvider = app_path('Providers\AppServiceProvider.php');

        $appProviderContent = file_get_contents($appProvider);

        $search = '// Append new facades here';

        $replace = "{$search}\n\t\t{$facadeClass}::class => {$serviceInstance},";

        $appProviderContent = str_replace($search, $replace, $appProviderContent);

        file_put_contents($appProvider, $appProviderContent);
    }

    protected function addModuleFacade(string $module, string $facadeClass, $serviceInstance) {

        $moduleProviderContent = file_get_contents(app_path("Modules/{$module}/Providers/ModuleServiceProvider.php"));

        $search = '// Append new facades here';

        $replace = "{$search}\n\t\t{$facadeClass}::class => {$serviceInstance},";

        $moduleProviderContent = str_replace($search, $replace, $moduleProviderContent);

        file_put_contents(app_path("Modules/{$module}/Providers/ModuleServiceProvider.php"), $moduleProviderContent);
    }

    protected function findServiceClass($module, $service)
    {
        $appServiceNamespace = $this->rootNamespace() . 'Services\\' . $service;
        $moduleServiceNamespace = $this->rootNamespace() . 'Modules\\' . $module . '\\Services\\' . $service;

        if (class_exists($appServiceNamespace)) {
            return $appServiceNamespace;
        }

        if (class_exists($moduleServiceNamespace)) {
            return $moduleServiceNamespace;
        }

        return null;
    }

    protected function getArguments()
    {
        return [
            ['name', 'n', InputArgument::REQUIRED, 'The name of the facade.'],
            ['service', 's', InputArgument::REQUIRED, 'The name of the service.'],
            ['module', 'm', InputArgument::OPTIONAL, 'The name of the module.'],
        ];
    }
}


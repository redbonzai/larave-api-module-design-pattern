<?php

namespace App\Providers;

use Illuminate\Console\Application;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $modules = config('modules.modules');

        foreach ($modules as $module) {
            $this->loadModuleControllers($module);
            $this->loadModuleServices($module);
            $this->loadModuleRepositories($module);
            $this->loadModuleMigrations($module);
            $this->loadModuleSeeders($module);
            $this->loadModuleFactories($module);
            $this->loadModuleConsoleCommands($module);
            $this->loadModuleModels($module);
            $this->loadModuleTransformers($module);
            $this->loadModuleConfigs($module);
            $this->loadModuleRoutes($module);
            $this->loadModuleProviders($module);
            $this->loadModuleFacades($module);
        }
    }

     /**
     * Load the module's controllers.
     * This implementation uses scandir to iterate over the files in the Http/Controllers
     * directory for the specified module.
     * For each file that is a PHP file and is not the . or .. directories,
     * it creates the fully-qualified class name by appending the file name to the module's
     * namespace and controller directory.
     * Finally, it resolves the controller class from the container using $this->app->make()
     * to ensure that any dependencies are injected properly
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleControllers(string $module): void {
        $path = base_path("app/Modules/{$module}/Http/Controllers");
        if (file_exists($path)) {
            $namespace = "App\\Modules\\{$module}\\Http\\Controllers\\";
            foreach (scandir($path) as $file) {
                if ($file !== '.' && $file !== '..' && is_file("$path/$file")) {
                    $class = pathinfo($file, PATHINFO_FILENAME);
                    $this->app->make("{$namespace}{$class}");
                }
            }
        }
    }

    /**
     * Load the module's services.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleServices(string $module): void {
        $path = base_path("app/Modules/{$module}/Services");
        if (file_exists($path)) {
            $this->app->register("App\Modules\\{$module}\Providers\ServiceProviders");
        }
    }

    /**
     * Load the module's database repositories.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleRepositories(string $module): void
    {
        $path = base_path("app/Modules/{$module}/Repositories");
        if (file_exists($path)) {
            $this->loadMigrationsFrom($path . '/Migrations');
            $this->app->register("App\Modules\\{$module}\Providers\RepositoryProviders");
        }
    }

    /**
     * Load the module's database migrations.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleMigrations(string $module): void
    {
        $path = base_path("app/Modules/{$module}/Database/Migrations");
        if (file_exists($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Load the module's seeders.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleSeeders(string $module): void
    {
        $path = base_path("app/Modules/{$module}/Database/Seeders");
        if (file_exists($path)) {
            $this->loadSeedersFrom($path);
        }
    }

    /**
     * Load the module's factories.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleFactories(string $module): void
    {
        $path = base_path("app/Modules/{$module}/Database/Factories");
        if (file_exists($path)) {
            $this->loadFactoriesFrom($path);
        }
    }

    /**
     * Load the module's console commands.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleConsoleCommands(string $module): void {
        $path = base_path("app/Modules/{$module}/Console/Commands");
        if (file_exists($path)) {
            $this->commands(array_map(function ($command) use ($module) {
                return "App\Modules\\{$module}\Console\Commands\\{$command}";
            }, scandir($path)));
        }
    }

    /**
     * Load the module's models.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleModels(string $module): void {
        $path = base_path("app/Modules/{$module}/Models");
        if (file_exists($path)) {
            $this->loadModelsFrom($path);
        }
    }

    /**
     * Load the module's transformers.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleTransformers(string $module): void {
        $path = base_path("app/Modules/{$module}/Transformers");
        if (file_exists($path)) {
            $this->loadTransformersFrom($path);
        }
    }

    /**
     * Load the module's configs.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleConfigs(string $module): void {
        $path = base_path("app/Modules/{$module}/Config");
        if (file_exists($path)) {
            $this->mergeConfigFrom($path, $module);
        }
    }

    /**
     * Load the module's routes.
     *
     * @param string $module
     * @return void
     */
    protected function loadModuleRoutes(string $module): void {
        $path = base_path("app/Modules/{$module}/Routes/web.php");
        if (file_exists($path)) {
            $this->loadRoutesFrom($path);
        }
    }

    protected function loadModuleProviders(string $module): void {
        $provider = 'Modules\\' . $module . '\\Providers\\' . $module . 'ServiceProvider';

        if (class_exists($provider)) {
            $this->app->register($provider);
        }
    }

    protected function loadModuleFacades(string $module): void {
        $facadesPath = app_path("Modules/{$module}/Facades");

        if (file_exists($facadesPath)) {
            $namespace = "App\\Modules\\{$module}\\Facades";
            $files = array_diff(scandir($facadesPath), ['.', '..']);

            foreach ($files as $file) {
                $facadeName = pathinfo($file, PATHINFO_FILENAME);
                $facadeClass = "{$namespace}\\{$facadeName}";
                $serviceClass = "{$namespace}\\{$facadeName}Service";

                if (class_exists($serviceClass)) {
                    $this->app->singleton($facadeName, function ($app) use ($serviceClass) {
                        return $app->make($serviceClass);
                    });
                } else {
                    $this->app->singleton($facadeName, $facadeClass);
                }
            }
        }
    }

}

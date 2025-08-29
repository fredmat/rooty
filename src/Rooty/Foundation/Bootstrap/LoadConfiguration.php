<?php

namespace Rooty\Foundation\Bootstrap;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Rooty\Contracts\Foundation\Application;
use Symfony\Component\Finder\Finder;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        $items = [];

        $app->instance('config', $config = new Repository($items));

        $this->loadConfigurationFiles($app, $config);
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     *
     * @throws \Exception
     */
    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
    {
        $files = $this->getConfigurationFiles($app);

        foreach ($files as $name => $path) {
            $this->loadConfigurationFile($repository, $name, $path);
        }
    }

    /**
     * Load the given configuration file.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function loadConfigurationFile(RepositoryContract $repository, $name, $path)
    {
        $config = (fn() => require $path)();
        $repository->set($name, $config);
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  \Rooty\Contracts\Foundation\Application  $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];

        $configPath = realpath($app->configPath());

        if (! $configPath) {
            return [];
        }

        foreach (Finder::create()->files()->name('*.php')->in($configPath)->depth(0) as $file) {
            $files[basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }
}

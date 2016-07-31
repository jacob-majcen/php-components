<?php

namespace Mnabialek\LaravelModular\Models;

use Illuminate\Contracts\Foundation\Application;
use Mnabialek\LaravelModular\Services\Config;
use Mnabialek\LaravelModular\Traits\Normalizer;
use Mnabialek\LaravelModular\Traits\Replacer;

class Module
{
    use Normalizer, Replacer;

    /**
     * @var
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Application
     */
    protected $laravel;

    /**
     * Module constructor.
     *
     * @param string $name
     * @param Application $application
     * @param array $options
     */
    public function __construct(
        $name,
        Application $application,
        array $options = []
    ) {
        $this->name = $name;
        $this->options = collect($options);
        $this->laravel = $application;
        $this->config = $application['modular.config'];
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get module seeder class name (with namespace)
     *
     * @param string|null $class
     *
     * @return string
     */
    public function seederClass($class = null)
    {
        return $this->fileClass('seeder', $class);
    }

    /**
     * Get module service provider class
     *
     * @return string
     */
    public function serviceProviderClass()
    {
        return $this->fileClass('serviceProvider');
    }

    /**
     * Get file class
     *
     * @param string $type
     * @param string|null $class
     *
     * @return string
     */
    protected function fileClass($type, $class = null)
    {
        $filename = $type . 'File';
        $namespace = $type . 'Namespace';

        $class = $class ?: basename($this->config->$filename(), '.php');

        return $this->replace($this->config->modulesNamespace() . '\\' .
            $this->name() . '\\' . $this->config->$namespace() . '\\' .
            $class, $this);
    }

    /**
     * Get module directory
     *
     * @return string
     */
    public function directory()
    {
        return $this->normalizePath($this->config->directory()) .
        DIRECTORY_SEPARATOR . $this->name();
    }

    /**
     * Get module migrations path
     *
     * @return string
     */
    public function migrationsPath()
    {
        return $this->directory() . DIRECTORY_SEPARATOR .
        $this->normalizePath($this->config->migrationsPath());
    }

    /**
     * Verify whether module has service provider
     *
     * @return bool
     */
    public function hasProvider()
    {
        return $this->hasFile('provider', 'serviceProviderFilePath');
    }

    /**
     * Verifies whether module has factory
     *
     * @return bool
     */
    public function hasFactory()
    {
        return $this->hasFile('factory', 'factoryFilePath');
    }

    /**
     * Verifies whether module has routes file
     *
     * @return bool
     */
    public function hasRoutes()
    {
        return $this->hasFile('routes', 'routesPath');
    }
    
    /**
     * Verifies whether module has seeder file
     *
     * @return bool
     */
    public function hasSeeder()
    {
        return $this->hasFile('seeder', 'seederFilePath');
    }

    /**
     * Verifies whether module has file of given type either checking config
     * and if it's not exist by checking whether file exists
     *
     * @param string $option
     * @param string $pathFunction
     *
     * @return bool
     */
    protected function hasFile($option, $pathFunction)
    {
        return (bool)($this->options->has($option) ? $this->options->get($option) :
            $this->laravel['files']->exists($this->$pathFunction()));
    }

    /**
     * Get controller namespace for routing
     *
     * @return string
     */
    public function routingControllerNamespace()
    {
        return $this->config->modulesNamespace() . '\\' . $this->name() . '\\' .
        $this->config->routingControllerNamespace();
    }

    /**
     * Get module routes file (with path)
     *
     * @return string
     */
    public function routesFilePath()
    {
        return $this->getPath('routingFile');
    }

    /**
     * Get module factory file path
     *
     * @return string
     */
    public function factoryFilePath()
    {
        return $this->getPath('factoryFile');
    }

    /**
     * Get module factory file path
     *
     * @return string
     */
    public function seederFilePath()
    {
        return $this->getPath('seederFile');
    }

    /**
     * Get module service provider file path
     *
     * @return string
     */
    public function serviceProviderFilePath()
    {
        return $this->getPath('serviceProviderFile');
    }

    /**
     * Get path
     *
     * @param string $configMethod
     *
     * @return string
     */
    protected function getPath($configMethod)
    {
        return $this->directory() . DIRECTORY_SEPARATOR .
        $this->replace($this->config->$configMethod(), $this);
    }

    /**
     * Verifies whether given module is active
     *
     * @return bool
     */
    public function active()
    {
        return $this->options->get('active', true);
    }
}

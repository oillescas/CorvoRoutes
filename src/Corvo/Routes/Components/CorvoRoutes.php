<?php namespace Corvo\Routes\Components;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;


class CorvoRoutes {
    
    /**
     * Path which contain sections
     * 
     * @var string
     */
    protected $_basePath;

    /**
     * Alternative paths which contain sections
     * 
     * @var array
     */
    protected $_alternativePaths = array();

    /**
     * Routes file name
     * 
     * @var string
     */
    protected $_routesFileName;

    /**
     * Views location
     * 
     * @var string
     */
    protected $_viewsFolder;

    /**
     * Config file name
     * 
     * @var string
     */
    protected $_configName = 'corvoroutes';

    /**
     * Load configuration
     * 
     * @return void
     */
    public function loadConfig()
    {
        // Get default config
        $defaultConfig = @include (__DIR__.'/../Config/config.php');
        // Get user config
        $userConfig = Config::get($this->_configName);

        // Merge two arrays.
        // If the user change one or more items of 
        // the configurations his config prevails
        $config = array_merge($defaultConfig, $userConfig);

        // If the var was set not use the configuration file,
        // this step is used for each var
        if (empty($this->_basePath))
        {
            $this->_basePath = $config['base_path'];
        }
        if (empty($this->_routesFileName))
        {
            $this->_routesFileName = $config['routes_filename'];
        }
        if (empty($this->_viewsFolder))
        {
            $this->_viewsFolder = $config['views_folder'];
        }

        array_merge($config['alternative_paths'], $this->_alternativePaths);

        return true;
    }

    /**
     * Load routes files
     * 
     * @return void
     */
    public function load()
    {
        // Contain routes files
        $routeFiles = array();
        // Contain views namespace and path to view folder
        $viewsNamespaces = array();

        // Load configuration
        $this->loadConfig();

        // Open path which contain the content
        $paths = opendir($this->_basePath);

        if ($paths)
        {
            // Iterate folders
            while ($path = readdir($paths))
            {
                if ($path == '.' || $path == '..')
                {
                    continue;
                }

                // Absolute path to routes file
                $file = $this->_basePath.'/'.$path.'/'.$this->_routesFileName;
                // Absolute path to views folder
                $view = $this->_basePath.'/'.$path.'/'.$this->_viewsFolder;

                // Exists routes file?
                if (is_file($file))
                {
                    $routeFiles[] = $file;
                }

                // Exists views folder?
                if (is_dir($view))
                {
                    // To add views namespace we need two items:
                    // 'path' is the absolute path to the view folder
                    // 'name' is the root folder name of the section, and use this name to call the namespace
                    $viewsNamespaces[] = array(
                        'path' => $view,
                        'name' => $path
                    );
                }
            }
        }

        // If has alternative paths add these too
        if (!empty($this->_alternativePaths))
        {
            // Repeat the same method for each alternative path
            foreach($this->_alternativePaths as $path)
            {
                $file = $path.'/'.$this->_routesFileName;
                $view = $path.'/'.$this->_viewsFolder;

                if (is_file($file))
                {
                    $routeFiles[] = $file;
                }

                if (is_dir($view))
                {
                    $viewsNamespaces[] = array(
                        'path' => $view,
                        'name' => $path
                    );
                }
            }
        }
        
        // Include files
        $this->_includeRoutesFiles($routeFiles);
        // Add view namespaces
        $this->_addViewNamespaces($viewsNamespaces);
    }

    /**
     * Include route files
     * 
     * @param  array $files array with absolute path to route files
     * 
     * @return void
     */
    private function _includeRoutesFiles(array $files)
    {
        foreach($files as $file)
        {
            include $file;
        }
    }

    /**
     * Add namespaces in the Illuminate\Support\Facades\View class
     * 
     * @param  array $files array with absolute path to views path and name of the namespace
     * 
     * @return void
     */
    private function _addViewNamespaces($viewsNamespaces)
    {
        foreach($viewsNamespaces as $namespace)
        {
            View::addNamespace(
                $namespace['name'], 
                $namespace['path']
            );
        }
    }

    /**
     * Set base path which contain sections
     * 
     * @param  string $path [description]
     * 
     * @return CorvoRoutes\Components\CorvoRoutes
     */
    public function basePath($path)
    {
        $this->_basePath = $path;

        return $this;
    }

    /**
     * Set the name of the route files
     * 
     * @param  string $fileName name of route files
     * 
     * @return CorvoRoutes\Components\CorvoRoutes
     */
    public function routesFileName($fileName)
    {
        $this->_routesFileName = $fileName;

        return $this;
    }

    /**
     * Set views folder name
     * 
     * @param  string $viewFolder folder name of views
     * 
     * @return Corvo\Routes\Components\CorvoRoutes
     */
    public function viewsFolder($viewFolder)
    {
        $this->_viewsFolder = $viewsFolder;

        return $this;
    }

    /**
     * Set config filename
     * 
     * @param  string $configName config name
     * 
     * @return Corvo\Routes\Components\CorvoRoutes
     */
    public function configName($configName)
    {
        $this->_configName = $configName;

        return $this;    
    }

    /**
     * Add a single alternative path which contain a section
     * 
     * @param  string $path absolute path of the section
     *
     * @return CorvoRoutes\Components\CorvoRoutes
     */
    public function addAlternativePath($path)
    {
        $this->_alternativePaths[] = $path;

        return $this;
    }

    /**
     * Add an array with alternative paths which contain sections
     * 
     * @param  array $paths contains absolute path of the sections
     * 
     * @return CorvoRoutes\Components\CorvoRoutes
     */
    public function alternativePaths(array $paths)
    {
        $this->_alternativePaths = array_merge(
            $this->_alternativePaths, $paths
        );

        return $this;
    }
}

<?php

namespace Proyen\Core\App;

class AppRouter
{

    /**
     * Path to list of routes stored on file.
     */
    const ROUTE_LIST_FILE = \FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles' . DIRECTORY_SEPARATOR . 'routes.json';

    /**
     * List of routes.
     *
     * @var array
     */
    private $routes;

    /**
     * AppRouter constructor.
     */
    public function __construct()
    {
        if (!defined('PYEN_ROUTE')) {
            define('PYEN_ROUTE', '');
        }

        $this->routes = $this->loadFromFile();
    }

    /**
     * Clear the App routes.
     */
    public function clear()
    {
        $this->routes = [];
        $this->save();
    }

    /**
     * Return the especific App controller for any kind of petition.
     *
     * @return App
     */
    public function getApp()
    {
        $uri = $this->getUri();
        if ('/api' === $uri || '/api/' === substr($uri, 0, 5)) {
            return new AppAPI($uri);
        }

        if ('/cron' === $uri) {
            return new AppCron($uri);
        }

        if ('/deploy' === $uri) {
            $this->deploy();
        }

        foreach ($this->routes as $key => $data) {
            if ($uri === $key) {
                return $this->newAppController($uri, $data['controller']);
            }

            if ('*' !== substr($key, -1)) {
                continue;
            }

            if (0 === strncmp($uri, $key, strlen($key) - 1)) {
                return $this->newAppController($uri, $data['controller']);
            }
        }

        return $this->newAppController($uri);
    }

    /**
     * Return true if can output a file, false otherwise.
     *
     * @return bool
     */
    public function getFile(): bool
    {
        $uri = $this->getUri();
        $filePath = \PYEN_FOLDER . $uri;

        /// favicon.ico
        if ('/favicon.ico' == $uri) {
            $filePath = \PYEN_FOLDER . '/Dinamic/Assets/Images/favicon.ico';
            header('Content-Type: ' . $this->getMime($filePath));
            readfile($filePath);
            return true;
        }

     
        if (!is_file($filePath) || !$this->isFileSafe($filePath)) {
            return false;
        }

        
        $allowedFolders = ['node_modules', 'vendor', 'Dinamic', 'Core', 'Plugins'];
        foreach ($allowedFolders as $folder) {
            if ('/' . $folder === substr($uri, 0, 1 + strlen($folder))) {
                header('Content-Type: ' . $this->getMime($filePath));
                readfile($filePath);
                return true;
            }
        }

        return false;
    }

    /**
     * Adds this route to the ap routes.
     *
     * @param string $newRoute
     * @param string $controllerName
     * @param string $optionalId
     */
    public function setRoute(string $newRoute, string $controllerName, string $optionalId = '')
    {
        if (!empty($optionalId)) {
            /// if optionaId, then remove previous items with that data
            foreach ($this->routes as $route => $routeItem) {
                if ($routeItem['controller'] === $controllerName && $routeItem['optionalId'] === $optionalId) {
                    unset($this->routes[$route]);
                }
            }
        }

        $this->routes[$newRoute] = [
            'controller' => $controllerName,
            'optionalId' => $optionalId
        ];

        $this->save();
    }

   
    private function deploy()
    {
        if (!file_exists(\PYEN_FOLDER . DIRECTORY_SEPARATOR . 'Dinamic')) {
            $pluginManager = new \Proyen\Core\Base\PluginManager();
            $pluginManager->deploy();
        }
    }

    /**
     * Return the mime type from given file.
     *
     * @param string $filePath
     *
     * @return string
     */
    private function getMime(string $filePath)
    {
        if (substr($filePath, -4) === '.css') {
            return 'text/css';
        }

        if (substr($filePath, -3) === '.js') {
            return 'application/javascript';
        }

        return mime_content_type($filePath);
    }

    /**
     * Return the uri from the request.
     *
     * @return bool|string
     */
    private function getUri()
    {
        $uri = \filter_input(\INPUT_SERVER, 'REQUEST_URI');
        $uri2 = is_null($uri) ? \filter_var($_SERVER['REQUEST_URI'], \FILTER_SANITIZE_URL) : $uri;
        $uriArray = explode('?', $uri2);

        return substr($uriArray[0], strlen(PYEN_ROUTE));
    }

    /**
     * 
     * @param string $filePath
     *
     * @return bool
     */
    private function isFileSafe(string $filePath): bool
    {
        $parts = explode('.', $filePath);
        $safe = ['css', 'eot', 'gif', 'ico', 'jpg', 'js', 'map', 'png', 'svg', 'ttf', 'woff', 'woff2'];
        return in_array(end($parts), $safe, true);
    }

    /**
     * Returns an array with the list of plugins in the plugin.list file.
     *
     * @return array
     */
    private function loadFromFile(): array
    {
        if (file_exists(self::ROUTE_LIST_FILE)) {
            $content = file_get_contents(self::ROUTE_LIST_FILE);
            if ($content !== false) {
                return json_decode($content, true);
            }
        }

        return [];
    }

    
}

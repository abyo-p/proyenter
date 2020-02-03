<?php
/**

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.

 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
define('PYEN_FOLDER', __DIR__);

/**
 * Preliminary checks
 */
if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'config.php')) {
    if ((int) substr(phpversion(), 0, 1) < 7) {
        die('You need PHP 7<br/>You have PHP ' . phpversion());
    } elseif (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'vendor')) {
        die('<h1>COMPOSER ERROR</h1><p>You need to run: composer install</p><p>You should also run: npm install</p>');
    }

    /**
     * If there is no configuration file, it means the installation hasn't been done,
     * then we load the installer.
     */
    require_once __DIR__ . '/vendor/autoload.php';

    $router = new \Proyenter\Core\App\AppRouter();
    if (!$router->getFile()) {
        $app = new \Proyenter\Core\App\AppInstaller();
    }
    die('');
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

/// Disable 30 seconds PHP limit
@set_time_limit(0);
ignore_user_abort(true);

/// Register error handler
if (PYEN_DEBUG) {
    $whoops = new \Whoops\Run;
    $whoops->prependHandler(new \Whoops\Handler\PlainTextHandler());
    $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
} else {
    $errorHandler = new \Proyenter\Core\Base\Debug\ProductionErrorHandler();
}

/**
 * Initialise the application
 */
$router = new \Proyenter\Core\App\AppRouter();
if (isset($argv[1]) && $argv[1] === '-cron') {
    chdir(__DIR__);
    $app = new \Proyenter\Core\App\AppCron();
    $app->connect();
    $app->run();
    $app->render();
    $app->close();
} elseif (!$router->getFile()) {
    $app = $router->getApp();

    /// Connect to the database, cache, etc.
    $app->connect();

    /// Executes App logic
    $app->run();
    $app->render();

    /// Disconnect from everything
    $app->close();
}
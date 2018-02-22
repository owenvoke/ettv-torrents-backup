<?php

namespace pxgamer\Ettv_Torrents\Routing;

use System\App;
use System\Request;
use System\Route;

/**
 * Class Router
 */
class Router
{
    public function __construct()
    {
        $app = App::instance();
        $app->request = Request::instance();
        $app->route = Route::instance($app->request);
        $route = $app->route;

        // Main
        $route->any('/', ['pxgamer\Ettv_Torrents\Modules\Base\Controller', 'index']);
        $route->any('/search', ['pxgamer\Ettv_Torrents\Modules\Torrents\Controller', 'search']);
        $route->any('/torrent/{id}:([0-9]+)', ['pxgamer\Ettv_Torrents\Modules\Torrents\Controller', 'show']);

        // Cron
        $route->any('/cron', ['pxgamer\Ettv_Torrents\Modules\Torrents\Controller', 'cron']);

        // Route fallback for page not found
        $route->any('/*', ['pxgamer\Ettv_Torrents\Modules\Base\Controller', 'errorNotFound']);

        $route->end();
    }

    public static function redirect($location = '/')
    {
        if (!headers_sent()) {
            header('Location: ' . $location);
        }
    }
}

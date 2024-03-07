<?php

namespace Oooiik\LaravelExportPostman\Services;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

class RouterService
{

    protected $router;

    /** @var Route[] $route */
    public $routes;

    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->initRoutes();
    }

    public function initRoutes()
    {
        $this->routes = $this->router->getRoutes();
    }

    public function initFilter()
    {
        $this->routes = array_filter($this->routes, function (Route $route) {
            return true; // @TODO add filters for routes
        });
    }
}
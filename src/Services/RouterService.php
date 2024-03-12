<?php

namespace Oooiik\LaravelExportPostman\Services;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Oooiik\LaravelExportPostman\Convert\RouteConvert;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;
use Oooiik\LaravelExportPostman\Structure\Structure;

class RouterService
{
    /** @var HelperInterface $helper */
    protected $helper;

    /** @var Router $router */
    protected $router;

    /** @var Structure $structure */
    public $structure;

    /** @var Route[] $routes */
    public $routes;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(Structure $structure)
    {
        $this->router = Container::getInstance()->make(Router::class);
        $this->helper = Container::getInstance()->make(HelperInterface::class);
        $this->structure = $structure;

        $this->initRoutes();
        $this->initFilter();
    }

    public function initRoutes()
    {
        $this->routes = $this->router->getRoutes()->getRoutes();
    }

    public function initFilter()
    {
        $this->routes = array_filter($this->routes, function (Route $route) {
            return $this->isRouteInMiddleware($route);
        });
    }

    /**
     * @param Route $route
     * @return bool
     */
    protected function isRouteInMiddleware(Route $route): bool
    {
        $middlewares = $route->gatherMiddleware();
        foreach ($middlewares as $middleware) {
            if (in_array($middleware, $this->helper->middlewares())) {
                return true;
            }
        }
        return false;
    }

    public function routesToStructure()
    {
        foreach ($this->routes as $route) {
            $routeConvert = new RouteConvert($route);
            if (!$routeConvert->hasReflectionMethod()) {
                continue;
            }
            foreach ($routeConvert->toArrays() as $request) {
                $this->structure->write($routeConvert->path(), $request);
            }
        }
    }
}
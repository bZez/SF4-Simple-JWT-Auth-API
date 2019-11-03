<?php

namespace App\Helper;

use Symfony\Component\Routing\RouterInterface;

class Routes
{
    private $routes = [];

    public function __construct(RouterInterface $router)
    {
        foreach ($router->getRouteCollection()->all() as $route_name => $route) {
            if (!$this->startsWith($route_name, '_'))
                $this->routes[$route_name] = $route->getPath();
        }
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
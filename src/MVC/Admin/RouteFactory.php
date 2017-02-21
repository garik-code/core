<?php namespace Kitrix\MVC\Admin;

use Kitrix\MVC\Router;

final class RouteFactory
{

    /**
     * Make new route and return it
     *
     * @param $url
     * @param $controller
     * @param string $method
     * @param array $defParams
     * @return Route
     */
    public static function makeRoute($url, $controller, $method = "index", $defParams = []) {

        $defParams = (array)$defParams;

        // we can provide class name directly
        // ex. $controller = 'IndexController::class'
        // but route expect only name 'Index' from 'Kitrix\Core\IndexController'

        if (substr_count($controller, '\\') >= 1) {

            $desc = explode('\\', $controller);
            $controller = array_pop($desc);
            $controller = str_replace('Controller', '', $controller);
        }

        $clearDefParams = ['_controller', '_action', Router::ROUTE_KITRIX_NAMESPACE, Router::ROUTE_KITRIX_ID];
        foreach ($clearDefParams as $k) {
            unset($defParams[$k]);
        }

        // default params
        $params = $defParams + [
            "_controller" => $controller,
        ];

        // index action not need to define
        if ($method != 'index') {
            $params["_action"] = $method;
        }

        return new Route($url, $params);
    }
}
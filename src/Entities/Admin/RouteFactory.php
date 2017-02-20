<?php namespace Kitrix\Entities\Admin;

use Kitrix\Common\Kitx;

final class RouteFactory
{

    /**
     * Make new route and return it
     *
     * @param $url
     * @param $controller
     * @param string $method
     * @return Route
     */
    public static function makeRoute($url, $controller, $method = "index") {

        // we can provide class name directly
        // ex. $controller = 'IndexController::class'
        // but route expect only name 'Index' from 'Kitrix\Core\IndexController'

        if (substr_count($controller, '\\') >= 1) {

            $desc = explode('\\', $controller);
            $controller = array_pop($desc);
            $controller = str_replace('Controller', '', $controller);
        }

        // default params
        $params = [
            "_controller" => $controller,
        ];

        // index action not need to define
        if ($method != 'index') {
            $params["_action"] = $method;
        }

        return new Route($url, $params);
    }
}
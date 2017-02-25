<?php
/******************************************************************************
 * Copyright (c) 2017. Kitrix Team                                            *
 * Kitrix is open source project, available under MIT license.                *
 *                                                                            *
 * @author: Konstantin Perov <fe3dback@yandex.ru>                             *
 * Documentation:                                                             *
 * @see https://kitrix-org.github.io/docs                                     *
 *                                                                            *
 *                                                                            *
 ******************************************************************************/

namespace Kitrix\MVC;

use Kitrix\Plugins\Plugin;

final class Context
{
    /** @var array */
    private $routeVars = [];

    /** @var Plugin */
    private $currentPlugin;

    /** @var array */
    private $request = [];

    function __construct($routeVars = [], Plugin $currentPlugin)
    {
        $this->routeVars = $routeVars;
        $this->currentPlugin = $currentPlugin;
        $this->request = $_REQUEST;
    }

    /**
     * @return Plugin
     */
    public function getCurrentPlugin(): Plugin
    {
        return $this->currentPlugin;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getRouteVars(): array
    {
        return $this->routeVars;
    }
}
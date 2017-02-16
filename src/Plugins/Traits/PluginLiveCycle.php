<?php namespace Kitrix\Plugins\Traits;

use Kitrix\Entities\Admin\MenuItem;
use Kitrix\Entities\Admin\Route;

trait PluginLiveCycle
{
    /**
     * Register custom plugin menu items
     * in admin panel
     *
     * This function should return array
     * of AdminMenu classes or empty array
     *
     * @return MenuItem[]
     */
    abstract public function registerMenu() : array;

    /**
     * Register custom controllers(pages)
     * in admin panel
     *
     * This function should return array
     * of AdminRoute classes or empty array
     *
     * @return Route[]
     */
    abstract public function registerRoutes() : array;
}
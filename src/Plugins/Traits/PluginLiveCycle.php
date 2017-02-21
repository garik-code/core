<?php namespace Kitrix\Plugins\Traits;

use Kitrix\Entities\Admin\MenuItem;
use Kitrix\MVC\Admin\Route;
use Kitrix\Entities\Asset;

trait PluginLiveCycle
{
    /**
     * Register custom assets (css/js) for
     * auto loading
     *
     * You should specify relative path for
     * public directory in your plugin:
     *
     * ex. "/css/some.css"
     *
     * for file "/plugin/public/css/some.css"
     *
     * @return Asset[]
     */
    abstract public function registerAssets() : array;

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

    /**
     * This function run automatic, before plugin
     * going to disable state
     *
     * You can't directly cancel disabling, but
     * allow to run some side functions like
     * (clear cache, unregister callbacks, destroy
     * entities, etc..)
     *
     * @return void
     */
    abstract public function onDisableBefore();

    /**
     * This function run automatic, after plugin
     * state changed to disable.
     *
     * At this moment plugin will be unmount and
     * unloaded from kitrix.
     *
     * You can't directly cancel disabling, but
     * allow to run some side functions like
     * (clear cache, unregister callbacks, destroy
     * entities, etc..)
     *
     * @return void
     */
    abstract public function onDisableAfter();

}
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

namespace Kitrix\Plugins\Traits;

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
     */
    abstract public function onDisableAfter();

    /**
     * This function run only once, when kitrix
     * first time install this plugin
     *
     * At this moment you can create databasese,
     * prepare files, move components, etc..
     *
     * All this staff will be run only once!
     *
     * Kitrix will try to catch exceptions,
     * if script throw some error, kitrix
     * skip other install process and return
     * error to user. In this situation,
     * plugin will not be installed
     */
    abstract static public function onInstall();

    /**
     * At this moment, you can cancel uninstall
     * and return some message to user with
     * explanation reason.
     *
     * return true - for allow uninstall
     * return otherwise - for block uninstall process
     *
     * you can specify explanation message, for this
     * simple throw Exception with some message
     *
     * @return bool
     */
    abstract static public function onBeforeUninstall(): bool;

    /**
     * This function run only once, when kitrix
     * try to uninstall plugin.
     *
     * At this moment you can drop custom
     * databases, remove plugin files,
     * clear cache and do other staff like this.
     *
     * You cannot stop or cancel this process,
     * use onBeforeUninstall, for block/cancel
     * uninstall process
     */
    abstract static public function onUninstall();
}
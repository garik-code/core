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

namespace Kitrix;

use Kitrix\Common\Kitx;
use Kitrix\Core\IndexController;
use Kitrix\Core\PluginsController;
use Kitrix\MVC\Admin\RouteFactory;
use Kitrix\Entities\Asset;
use Kitrix\Plugins\Plugin;

final class Core extends Plugin
{
    public function registerAssets(): array
    {
        return [
            new Asset('/styles/admin.css', Asset::CSS),
            new Asset('/js/KitrixCorePlugins.js', Asset::JS),

            new Asset('/vendor/notify/alertify.min.js', Asset::JS),
            new Asset('/vendor/notify/css/alertify.css', Asset::CSS),
            new Asset('/vendor/font-awesome-4.7.0/css/font-awesome.min.css', Asset::CSS),
        ];
    }

    public function registerRoutes(): array
    {
        $routes = [];

        // Index
        // -----------
        $routes[] = RouteFactory::makeRoute('/', IndexController::class, 'about')
            ->setTitle("О Kitrix")
            ->setIcon("fa-file-text-o");

        // Plugins
        // -----------
        $routes[] = RouteFactory::makeRoute('/plugins/', PluginsController::class, 'all')
            ->setTitle("Список плагинов")
            ->setIcon("fa-plug");

        $routes[] = RouteFactory::makeRoute('/plugins/edit/', PluginsController::class, 'edit')
            ->setVisible(false);

        $routes[] = RouteFactory::makeRoute(
            '/plugins/help/{id}/',
            PluginsController::class,
            'help'
        )
            ->setVisible(false);


        return $routes;

    }

    public static function onBeforeUninstall(): bool
    {
        throw new \Exception(Kitx::frmt("
            Ядро Kitrix не должно быть удалено таким образом.
            Для полного удаления сначала отключите все плагины,
            затем удалите все плагины, затем используйте команду
            composer remove kitrix/core.
        ", []));
    }
}